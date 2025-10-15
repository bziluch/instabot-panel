<?php

namespace App\Command;

use App\Entity\IgAccount;
use App\Repository\IgAccountRepository;
use App\Repository\ScheduleRepository;
use App\Service\ScheduleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-schedules',
    description: 'Generuje harmonogramy dla kont Instagramowych (wszystkich lub konkretnego użytkownika)',
)]
class GenerateSchedulesCommand extends Command
{
    public function __construct(
        private readonly ScheduleService $scheduleService,
        private readonly IgAccountRepository $igAccountRepository,
        private readonly ScheduleRepository $scheduleRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'ID konta IG (jeśli podane, generuje tylko dla tego konta)')
            ->addOption('time', 't', InputOption::VALUE_OPTIONAL, 'Dzień generowania: today lub tomorrow (domyślnie tomorrow)');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getOption('user');
        $day = $input->getOption('time') ?? 'tomorrow';

        if (!in_array($day, ['today', 'tomorrow'], true)) {
            $io->error("Niepoprawna wartość argumentu <time>. Użyj 'today' lub 'tomorrow'.");
            return Command::FAILURE;
        }

        $date = new \DateTimeImmutable($day);
        $dateFormatted = $date->format('Y-m-d');

        // Pobieramy konta
        if ($userId) {
            $account = $this->igAccountRepository->find($userId);
            if (!$account) {
                $io->error("Nie znaleziono konta o ID: {$userId}");
                return Command::FAILURE;
            }
            $igAccounts = [$account];
            $io->info("Generowanie schedule dla użytkownika ID {$userId} na dzień {$dateFormatted}");
        } else {
            $igAccounts = $this->igAccountRepository->findAll();
            $io->info("Generowanie schedule dla wszystkich (" . count($igAccounts) . " kont) na dzień {$dateFormatted}");
        }

        if (empty($igAccounts)) {
            $io->warning('Brak kont do przetworzenia.');
            return Command::SUCCESS;
        }

        $progressBar = new ProgressBar($output, count($igAccounts));
        $progressBar->start();

        /** @var IgAccount $igAccount */
        foreach ($igAccounts as $igAccount) {
            // Sprawdzamy, czy dany dzień ma już schedule
            if ($this->scheduleRepository->existsForDay($date, $igAccount)) {
                $progressBar->advance();
                $io->warning($igAccount->getUsername() . " już ma schedule dla {$dateFormatted} – pomijam");
                continue;
            }

            // Generujemy schedule
            $this->scheduleService->generateSchedule($igAccount, day: $day);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);
        $io->success("Schedule wygenerowane pomyślnie dla dnia {$dateFormatted}");

        return Command::SUCCESS;
    }
}
