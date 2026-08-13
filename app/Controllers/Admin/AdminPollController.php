<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Poll;
use App\Models\PollOption;

class AdminPollController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $statusFilter = (string) $this->input('statut', 'actifs');
        $statusMap = ['actifs' => 'actif', 'programmes' => 'programme', 'termines' => 'termine', 'tous' => null];
        $status = $statusMap[$statusFilter] ?? 'actif';

        $featured = Poll::featured();
        $featuredOptions = $featured ? PollOption::forPoll($featured['id']) : [];
        $featuredTotal = array_sum(array_column($featuredOptions, 'votes_count'));

        $this->view('admin/polls/index', [
            'title'     => 'Sondages & Votes — Administration Le Commerce',
            'pageTitle' => 'Sondages & Votes',

            'polls'      => Poll::listWithStats($status),
            'activeTab'  => $statusFilter,

            'pollsActive'   => Poll::countByStatus('actif'),
            'pollsDelta'    => Poll::countCreatedThisMonth(),
            'totalParticipations' => Poll::totalParticipations(),
            'participationRate'  => Poll::averageParticipationRate(),
            'rewardsGiven'  => Poll::totalRewardsGiven(),

            'featured'        => $featured,
            'featuredOptions' => $featuredOptions,
            'featuredTotal'   => $featuredTotal,

            'rewardLabels' => Poll::REWARD_LABELS,
        ], 'admin');
    }

    public function create(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/polls/create', [
            'title'      => 'Créer un sondage — Administration Le Commerce',
            'pageTitle'  => 'Créer un nouveau sondage',
            'rewardLabels' => Poll::REWARD_LABELS,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->view('admin/polls/create', [
                'title'      => 'Créer un sondage — Administration Le Commerce',
                'pageTitle'  => 'Créer un nouveau sondage',
                'rewardLabels' => Poll::REWARD_LABELS,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        $pollId = Poll::create([
            'question'     => $data['question'],
            'description'  => $data['description'] ?: null,
            'ends_at'      => date('Y-m-d', strtotime($data['endsAt'])),
            'status'       => $data['publish'] ? 'actif' : 'programme',
            'reward_type'  => $data['rewardType'],
            'reward_value' => $data['rewardType'] === 'aucune' ? null : (float) ($data['rewardValue'] ?: 0),
        ]);

        PollOption::createMany($pollId, array_column($data['options'], 'label'));

        $this->setFlash('success', 'Le sondage "' . $data['question'] . '" a bien été créé.');
        $this->redirect('/admin/sondages');
    }

    public function edit(int $id): void
    {
        Middleware::requireRole('admin');

        $poll = Poll::find($id);
        if (!$poll) {
            $this->setFlash('error', 'Sondage introuvable.');
            $this->redirect('/admin/sondages');
            return;
        }

        $this->view('admin/polls/edit', [
            'title'      => 'Modifier un sondage — Administration Le Commerce',
            'pageTitle'  => 'Modifier « ' . $poll['question'] . ' »',
            'poll'         => $poll,
            'options'      => PollOption::forPoll($id),
            'rewardLabels' => Poll::REWARD_LABELS,
            'errors' => [],
            'old'    => [],
        ], 'admin');
    }

    public function update(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $poll = Poll::find($id);
        if (!$poll) {
            $this->setFlash('error', 'Sondage introuvable.');
            $this->redirect('/admin/sondages');
            return;
        }

        [$errors, $data] = $this->validate();

        if ($errors) {
            $this->view('admin/polls/edit', [
                'title'      => 'Modifier un sondage — Administration Le Commerce',
                'pageTitle'  => 'Modifier « ' . $poll['question'] . ' »',
                'poll'         => $poll,
                'options'      => PollOption::forPoll($id),
                'rewardLabels' => Poll::REWARD_LABELS,
                'errors' => $errors,
                'old'    => $data,
            ], 'admin');
            return;
        }

        Poll::update($id, [
            'question'     => $data['question'],
            'description'  => $data['description'] ?: null,
            'ends_at'      => date('Y-m-d', strtotime($data['endsAt'])),
            'status'       => $data['publish'] ? 'actif' : 'programme',
            'reward_type'  => $data['rewardType'],
            'reward_value' => $data['rewardType'] === 'aucune' ? null : (float) ($data['rewardValue'] ?: 0),
        ]);

        // Options : mise à jour des libellés existants (préserve les votes déjà comptabilisés)
        // + ajout des nouvelles options envoyées sans identifiant.
        $existingIds = array_column(PollOption::forPoll($id), 'id');
        foreach ($data['options'] as $option) {
            $optionId = (int) ($option['id'] ?? 0);
            if ($optionId && in_array($optionId, $existingIds, true)) {
                PollOption::update($optionId, ['label' => $option['label']]);
            } else {
                PollOption::create(['poll_id' => $id, 'label' => $option['label'], 'votes_count' => 0]);
            }
        }

        $this->setFlash('success', 'Le sondage "' . $data['question'] . '" a bien été mis à jour.');
        $this->redirect('/admin/sondages');
    }

    public function destroy(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $poll = Poll::find($id);
        if (!$poll) {
            $this->setFlash('error', 'Sondage introuvable.');
            $this->redirect('/admin/sondages');
            return;
        }

        Poll::delete($id);

        $this->setFlash('success', 'Le sondage "' . $poll['question'] . '" a été supprimé.');
        $this->redirect('/admin/sondages');
    }

    /**
     * @return array{0: array<string,string>, 1: array<string,mixed>} [errors, data]
     */
    private function validate(): array
    {
        $question    = trim((string) $this->input('question', ''));
        $description = trim((string) $this->input('description', ''));
        $endsAt      = (string) $this->input('ends_at', '');
        $rewardType  = (string) $this->input('reward_type', 'aucune');
        $rewardValue = $this->input('reward_value', '');
        $publish     = (bool) $this->input('publish');

        // Chaque option est soumise sous la forme options[i][label] / options[i][id]
        // (id vide = nouvelle option), ce qui évite tout désalignement d'index
        // entre labels et identifiants lors de l'édition.
        $rawOptions = (array) $this->input('options', []);
        $options = [];
        foreach ($rawOptions as $opt) {
            $label = trim((string) ($opt['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $options[] = ['id' => (int) ($opt['id'] ?? 0), 'label' => $label];
        }

        $errors = [];
        if ($question === '' || mb_strlen($question) > 180) {
            $errors['question'] = 'La question est obligatoire (180 caractères maximum).';
        }
        if (count($options) < 2) {
            $errors['options'] = 'Merci de proposer au moins 2 options de réponse.';
        }
        if ($endsAt === '' || strtotime($endsAt) === false) {
            $errors['ends_at'] = 'La date de fin est invalide.';
        }
        if (!array_key_exists($rewardType, Poll::REWARD_LABELS)) {
            $errors['reward_type'] = 'Type de récompense invalide.';
        }
        if ($rewardType !== 'aucune' && $rewardType !== 'tirage_sort' && (!is_numeric($rewardValue) || (float) $rewardValue < 0)) {
            $errors['reward_value'] = 'Merci d\'indiquer une valeur de récompense valide.';
        }

        return [$errors, compact('question', 'description', 'endsAt', 'rewardType', 'rewardValue', 'options', 'publish')];
    }

    public function results(int $id): void
    {
        Middleware::requireRole('admin');

        $poll = Poll::find($id);
        if (!$poll) {
            $this->setFlash('error', 'Sondage introuvable.');
            $this->redirect('/admin/sondages');
            return;
        }

        $options = PollOption::forPoll($id);
        $total = array_sum(array_column($options, 'votes_count'));

        $this->view('admin/polls/results', [
            'title'     => 'Résultats — ' . $poll['question'],
            'pageTitle' => 'Résultats du sondage',
            'poll'      => $poll,
            'options'   => $options,
            'total'     => $total,
        ], 'admin');
    }

    public function toggleStatus(int $id): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        $poll = Poll::find($id);
        if (!$poll) {
            $this->setFlash('error', 'Sondage introuvable.');
            $this->redirect('/admin/sondages');
            return;
        }

        $newStatus = $poll['status'] === 'actif' ? 'termine' : 'actif';
        Poll::update($id, ['status' => $newStatus]);

        $this->setFlash('success', 'Statut du sondage mis à jour.');
        $this->redirect('/admin/sondages');
    }
}
