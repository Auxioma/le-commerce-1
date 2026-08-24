<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;

class PollController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();
        $polls = Poll::activeForClients();

        // Marque chaque sondage comme "déjà voté" ou non pour l'affichage
        foreach ($polls as &$poll) {
            $poll['has_voted'] = PollVote::hasVoted((int) $poll['id'], (int) $user['id']);
        }
        unset($poll);

        $this->view('polls/index', [
            'title' => 'Sondages & Votes — Le Commerce',
            'user'  => $user,
            'polls' => $polls,
        ], 'client');
    }

    public function show(int $id): void
    {
        Middleware::requireRole('client');

        $user = Middleware::user();
        $poll = Poll::find($id);

        if (!$poll) {
            http_response_code(404);
            echo \App\Core\View::render('errors/404');
            return;
        }

        $options = PollOption::forPoll($id);
        $hasVoted = PollVote::hasVoted($id, (int) $user['id']);
        $votedOptionId = $hasVoted ? PollVote::optionVotedFor($id, (int) $user['id']) : null;
        $total = array_sum(array_column($options, 'votes_count'));

        $this->view('polls/show', [
            'title'         => $poll['question'] . ' — Le Commerce',
            'user'          => $user,
            'poll'          => $poll,
            'options'       => $options,
            'hasVoted'      => $hasVoted,
            'votedOptionId' => $votedOptionId,
            'total'         => $total,
            'rewardLabels'  => Poll::REWARD_LABELS,
        ], 'client');
    }

    public function vote(int $id): void
    {
        Middleware::requireRole('client');
        $this->verifyCsrf();

        $user = Middleware::user();
        $poll = Poll::find($id);

        if (!$poll || $poll['status'] !== 'actif' || strtotime($poll['ends_at']) < strtotime('today')) {
            $this->setFlash('error', 'Ce sondage n\'est plus disponible.');
            $this->redirect('/mon-compte/sondages');
            return;
        }

        $optionId = (int) $this->input('option_id', 0);
        $validOptionIds = array_column(PollOption::forPoll($id), 'id');

        if (!in_array($optionId, $validOptionIds, true)) {
            $this->setFlash('error', 'Merci de sélectionner une réponse.');
            $this->redirect('/mon-compte/sondages/' . $id);
            return;
        }

        $result = PollVote::castVote($id, $optionId, (int) $user['id']);

        if (!$result['success']) {
            $message = $result['reason'] === 'deja_vote'
                ? 'Vous avez déjà participé à ce sondage.'
                : 'Votre vote n\'a pas pu être enregistré, merci de réessayer.';
            $this->setFlash('error', $message);
            $this->redirect('/mon-compte/sondages/' . $id);
            return;
        }

        $rewardMessages = [
            'points'      => fn ($v) => 'Merci pour votre participation ! +' . (int) $v . ' points fidélité crédités.',
            'credit'      => fn ($v) => 'Merci pour votre participation ! +' . number_format((float) $v, 2, ',', ' ') . ' € crédités sur votre portefeuille.',
            'tirage_sort' => fn ($v) => 'Merci pour votre participation ! Vous êtes inscrit(e) au tirage au sort.',
        ];

        $message = 'Merci pour votre participation !';
        if ($result['reward'] && isset($rewardMessages[$result['reward']['type']])) {
            $message = $rewardMessages[$result['reward']['type']]($result['reward']['value']);
        }

        $this->setFlash('success', $message);
        $this->redirect('/mon-compte/sondages/' . $id);
    }
}
