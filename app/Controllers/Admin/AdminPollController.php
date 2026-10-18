<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Poll;
use App\Models\PollOption;
use App\Service\ClientSegmentationService;
use App\Service\LotteryAnnouncementService;
use App\Service\PollAnnouncementService;
use App\Service\SmsCampaignService;

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

        $this->renderCreateForm([], [
            'channels' => [],
            'segments' => [LotteryAnnouncementService::ALL],
        ]);
    }

    private function renderCreateForm(array $errors, array $old): void
    {
        $audience = LotteryAnnouncementService::audienceData();
        $counts = array_fill_keys(array_keys(ClientSegmentationService::allDefinitions()), 0);
        foreach ($audience as $client) {
            foreach ($client['s'] as $segment) {
                $counts[$segment]++;
            }
        }

        $this->view('admin/polls/create', [
            'title'      => 'Créer un sondage — Administration Le Commerce',
            'pageTitle'  => 'Créer un nouveau sondage',
            'rewardLabels' => Poll::REWARD_LABELS,
            'errors' => $errors,
            'old'    => $old,
            'announce' => [
                'segmentDefs'   => ClientSegmentationService::definitions(),
                'segmentCounts' => $counts,
                'audience'      => $audience,
                'smsConfigured' => (new SmsCampaignService())->isConfigured(),
            ],
        ], 'admin');
    }

    public function store(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        [$errors, $data] = $this->validate();

        // Annonce aux clients (SMS / e-mail, par segments ou e-mails choisis)
        $channels = array_values(array_intersect(array_keys(LotteryAnnouncementService::CHANNELS), (array) $this->input('channels', [])));
        $segments = array_values(array_intersect(
            array_merge([LotteryAnnouncementService::ALL], array_keys(ClientSegmentationService::allDefinitions())),
            (array) $this->input('segments', [])
        ));
        if (in_array(LotteryAnnouncementService::ALL, $segments, true)) {
            $segments = [LotteryAnnouncementService::ALL];
        }
        $emailRecipients = in_array('email', $channels, true)
            ? array_values(array_unique(array_filter(array_map('intval', (array) $this->input('email_recipients', [])), static fn(int $id) => $id > 0)))
            : [];
        // Les segments ne servent qu'au SMS et à l'e-mail sans sélection manuelle.
        $needsSegments = in_array('sms', $channels, true) || (in_array('email', $channels, true) && $emailRecipients === []);
        if ($data['publish'] && $needsSegments && $segments === []) {
            $errors['segments'] = 'Cochez « Tous les clients » ou au moins un segment à qui envoyer l\'annonce.';
        }

        if ($errors) {
            $this->renderCreateForm($errors, $data + compact('channels', 'segments', 'emailRecipients'));
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

        $flash = 'Le sondage "' . $data['question'] . '" a bien été créé.';
        if ($data['publish'] && $channels !== []) {
            $report = (new PollAnnouncementService())->announce(Poll::find($pollId), $this->siteUrl(), $channels, $segments, $emailRecipients);
            $flash .= ' ' . $this->announcementSummary($report, $channels);
        }

        $this->setFlash('success', $flash);
        $this->redirect('/admin/sondages');
    }

    /** Résumé lisible de l'envoi, pour le message de confirmation. */
    private function announcementSummary(array $report, array $channels): string
    {
        $parts = [];
        if (in_array('sms', $channels, true)) {
            if (!$report['smsConfigured']) {
                $parts[] = 'SMS non envoyé : configurez ALLMYSMS_LOGIN / ALLMYSMS_API_KEY dans .env';
            } elseif ($report['sms'] === null) {
                $parts[] = $report['smsEligible'] === 0 ? 'aucun client ciblé n\'a de numéro pour le SMS' : 'l\'envoi SMS a échoué';
            } elseif (!empty($report['sms']['scheduledAt'])) {
                $parts[] = 'SMS à ' . $report['sms']['nbContacts'] . ' client(s) programmé le ' . date('d/m/Y à H\hi', strtotime($report['sms']['scheduledAt'])) . ' (envoi commercial interdit hors de ce créneau)';
            } else {
                $parts[] = 'SMS envoyé à ' . $report['sms']['nbContacts'] . ' client(s)';
            }
        }
        if (in_array('email', $channels, true)) {
            $parts[] = 'e-mail envoyé à ' . $report['emailSent'] . ' client(s)' . ($report['emailFailed'] > 0 ? ' (' . $report['emailFailed'] . ' échec(s))' : '');
        }
        return 'Annonce : ' . implode(' ; ', $parts) . '.';
    }

    /** Adresse publique du site (liens des SMS et e-mails), depuis l'hôte de la requête. */
    private function siteUrl(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return 'https://' . $host . BASE_PATH;
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
