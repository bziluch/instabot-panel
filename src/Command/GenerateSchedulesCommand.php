<?php

namespace App\Command;

use App\Entity\IgAccount;
use App\Repository\IgAccountRepository;
use App\Repository\ScheduleRepository;
use App\Service\ScheduleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-schedules',
    description: 'Add a short description for your command',
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateTomorrow = new \DateTimeImmutable('tomorrow');

        $igAccounts = $this->igAccountRepository->findAll();
        $io->info("Found ".count($igAccounts)." accounts");

        $progressBar = new ProgressBar($output, count($igAccounts));
        $progressBar->start();

        /** @var IgAccount $igAccount */
        foreach ($igAccounts as $igAccount) {

            if (null != $schedule = $this->scheduleRepository->existsForDay($dateTomorrow, $igAccount)) {

                $progressBar->advance();
                $io->warning($igAccount->getUsername()." already has schedule for ".$dateTomorrow->format('Y-m-d')." - skipping");
                continue;
            }

            $this->scheduleService->generateSchedule($igAccount);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->success('Schedules generated successfully!');
        return Command::SUCCESS;
    }
}
