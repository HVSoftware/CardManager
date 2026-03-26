<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    private const CARDS_DIR = 'cards';
    private const DEFAULT_CARDS_FILE = 'cards.txt';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        $session = $this->requestStack->getSession();
        $gameStarted = $session->get('game_started', false);
        $cardFiles = $this->getAvailableCardFiles();
        $selectedFile = $session->get('card_file', self::DEFAULT_CARDS_FILE);

        return $this->render('game/index.html.twig', [
            'game_started' => $gameStarted,
            'card_files' => $cardFiles,
            'selected_file' => $selectedFile,
        ]);
    }

    #[Route('/game/start', name: 'app_game_start', methods: ['POST'])]
    public function start(Request $request): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        
        $numPlayers = (int) $request->request->get('numPlayers', 2);
        $numRounds = (int) $request->request->get('numRounds', 3);
        $cardFile = $request->request->get('cardFile', self::DEFAULT_CARDS_FILE);

        $session->set('num_players', min(max($numPlayers, 1), 4));
        $session->set('num_rounds', max($numRounds, 1));
        $session->set('card_file', $cardFile);
        $session->set('game_started', true);
        $session->set('current_round', 1);
        $session->set('current_player', 0);

        $cards = $this->loadCards($cardFile);
        $session->set('cards', $cards);

        return $this->redirectToRoute('app_game');
    }

    #[Route('/game/draw', name: 'app_game_draw', methods: ['POST'])]
    public function draw(): Response
    {
        $session = $this->requestStack->getSession();
        
        if (!$session->get('game_started')) {
            return $this->redirectToRoute('app_game');
        }

        $cards = $session->get('cards', []);
        
        if (empty($cards)) {
            $session->set('game_started', false);
            $this->addFlash('warning', 'No cards left!');
            return $this->redirectToRoute('app_game');
        }

        $randomIndex = array_rand($cards);
        $drawnCard = $cards[$randomIndex];
        unset($cards[$randomIndex]);
        $cards = array_values($cards);
        
        $session->set('cards', $cards);
        $session->set('last_drawn_card', $drawnCard);

        $numPlayers = $session->get('num_players', 2);
        $currentPlayer = $session->get('current_player', 0);
        $currentPlayer = ($currentPlayer + 1) % $numPlayers;
        $session->set('current_player', $currentPlayer);

        $currentRound = $session->get('current_round', 1);
        $numRounds = $session->get('num_rounds', 3);
        
        if ($currentPlayer === 0) {
            $currentRound++;
            $session->set('current_round', $currentRound);
            
            if ($currentRound > $numRounds) {
                $session->set('game_started', false);
                $this->addFlash('success', 'Game over!');
            }
        }

        return $this->redirectToRoute('app_game');
    }

    #[Route('/game/reset', name: 'app_game_reset', methods: ['POST'])]
    public function reset(): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $session->set('game_started', false);
        $session->remove('num_players');
        $session->remove('num_rounds');
        $session->remove('cards');
        $session->remove('current_round');
        $session->remove('current_player');
        $session->remove('last_drawn_card');

        return $this->redirectToRoute('app_game');
    }

    private function getAvailableCardFiles(): array
    {
        $files = [];
        $dir = self::CARDS_DIR;

        if (!is_dir($dir)) {
            return [self::DEFAULT_CARDS_FILE];
        }

        $handle = opendir($dir);
        if ($handle === false) {
            return [self::DEFAULT_CARDS_FILE];
        }

        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                $files[] = $file;
            }
        }
        closedir($handle);

        sort($files);

        return empty($files) ? [self::DEFAULT_CARDS_FILE] : $files;
    }

    private function loadCards(string $filename): array
    {
        $cards = [];
        $filePath = self::CARDS_DIR . '/' . $filename;

        if (!file_exists($filePath)) {
            return $cards;
        }

        $file = fopen($filePath, 'r');

        if ($file === false) {
            return $cards;
        }

        while (($line = fgets($file)) !== false) {
            $cards[] = trim($line);
        }

        fclose($file);
        shuffle($cards);

        return $cards;
    }
}
