<?php

namespace App\Command;

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
        $kaartFile = $input->getArgument('arg1') ?? 'kaarten.txt';

        if ($kaartFile) {
            $io->note(sprintf('You passed an argument: %s', $kaartFile));
        }

        $spelers = [];
        $aantalSpelers = $io->ask('Met hoeveel spelers wil je spelen (1-4)', 2);

        for ($i = 1; $i <= $aantalSpelers; $i++) {
            $spelers[] = $io->ask(sprintf('Hallo speler %d, wat is je naam?', $i), sprintf('Speler %d', $i));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->note(sprintf('Je speelt met het volgend aantal spelers: %s', $aantalSpelers));

        $io->note('De volgende spelers doen mee:');
        foreach ($spelers as $speler) {
            $io->note($speler);
        }

        $huidigeSpeler = 0;
        $io->note(sprintf('De volgende spelers mag beginnen: %s',  $spelers[$huidigeSpeler]));

        $kaarten = [];
        $bestand = fopen($kaartFile, "r");

        while (($lijn = fgets($bestand)) !== false) {
            $kaarten[] = ["actie" => trim($lijn)];
        }

        fclose($bestand);

        $io->note(sprintf('We beginnen met %d kaarten.', count($kaarten)));

        do {
            $io->note(sprintf('Er zijn nog %d kaarten over.', count($kaarten)));

            $pickKaart = random_int(0, count($kaarten) - 1);
            $io->ask(sprintf('%s, trek een kaart', $spelers[$huidigeSpeler]));

            $io->note($kaarten[$pickKaart]);
            unset($kaarten[$pickKaart]);
            $kaarten = array_values($kaarten);

            $huidigeSpeler++;
            if ($huidigeSpeler === count($spelers)) {
                $huidigeSpeler = 0;
            }
        } while (count($kaarten) > 0);

        $io->note('Er zijn geen kaarten meer over.');

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
