<?php

namespace App\Command;

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'card:play',
    description: 'Add a short description for your command',
)]
class CardPlayCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param mixed $kaartFile
     * @return array
     */
    public function getCards(string $cardFile): array
    {
        $cards = [];
        $file = fopen($cardFile, "r");

        while (($line = fgets($file)) !== false) {
            $cards[] = ["action" => trim($line)];
        }

        fclose($file);

        shuffle($cards);

        return $cards;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $cardFile = $input->getArgument('arg1') ?? 'cards.txt';

        if ($cardFile) {
            $io->note(sprintf('You passed an argument: %s', $cardFile));
        }

        $players = [];
        $numPlayers = $io->ask('How many players (1-4)?', 2, function ($answer) {
            if (!is_numeric($answer) || $answer < 1 || $answer > 4) {
                throw new RuntimeException('Enter a number between 1 and 4.');
            }
            return (int) $answer;
        });

        $rounds = $io->ask('How many rounds?', 3, function ($answer) {
            if (!is_numeric($answer) || $answer < 1) {
                throw new RuntimeException('Enter a positive number.');
            }
            return (int) $answer;
        });


        for ($i = 1; $i <= $numPlayers; $i++) {
            $players[] = $io->ask(sprintf('Hello player %d, what is your name?', $i), sprintf('Player %d', $i));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->note(sprintf('You are playing with %d players.', $numPlayers));

        $io->note('The following players are participating:');
        foreach ($players as $player) {
            $io->note($player);
        }

        $currentPlayer = 0;
//        $currentPlayer = array_rand($players);
        $io->note(sprintf('The first player to start is: %s', $players[$currentPlayer]));

        if (!file_exists($cardFile)) {
            $io->error('The card file does not exist.');
            return Command::FAILURE;
        }

        $cards = $this->getCards($cardFile);

        if (empty($cards)) {
            $io->error('There are no cards in the file. Add cards and try again.');
            return Command::FAILURE;
        }

        $io->note(sprintf('We start with %d cards.', count($cards)));
        $io->note(sprintf('There are a total of %d rounds.', $rounds));

        do {
            $io->note(sprintf('There are %d cards left.', count($cards)));

            $pickCard = random_int(0, count($cards) - 1);
            $io->ask(sprintf('%s, draw a card', $players[$currentPlayer]));

            $io->note($cards[$pickCard]);
            unset($cards[$pickCard]);
            $cards = array_values($cards);

//            $cards = array_splice($cards, $pickCard, 1)[0];

            $currentPlayer++;
            if ($currentPlayer === count($players)) {
                $currentPlayer = 0;
                $rounds--;

                if ($rounds > 0) {
                    $io->note(sprintf('There are %d rounds left.', $rounds));
                }
            }
        } while ($rounds > 0);

        $io->note('Done. All rounds have been played.');

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
