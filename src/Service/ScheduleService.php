<?php

namespace App\Service;


use App\Entity\Schedule;
use App\Entity\IgAccount;
use Doctrine\ORM\EntityManagerInterface;

readonly class ScheduleService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Tworzy harmonogram na następny dzień
     *
     * @param IgAccount $account konto IG, dla którego tworzymy schedule
     * @param int $targetAmount docelowa suma amount (domyślnie 500)
     * @param int $minSchedules minimalna liczba schedule w dobie (np. 15)
     * @param int $maxSchedules maksymalna liczba schedule w dobie (np. 25)
     *
     * @return Schedule[] wygenerowane schedule
     */
    public function generateSchedule(
        IgAccount $account,
        int $targetAmount = 500,
        int $minSchedules = 15,
        int $maxSchedules = 25
    ): array {
        $schedules = [];

        $count = random_int($minSchedules, $maxSchedules);
        $amounts = $this->splitTargetAmount($targetAmount, $count);

        $nextDay = (new \DateTimeImmutable('tomorrow'))->setTime(0, 0, 0);
        $usedTimes = [];

        for ($i = 0; $i < $count; $i++) {
            do {
                $date = $this->getRandomTimeInDay($nextDay);
                $timeKey = $date->format('H:i');
            } while (in_array($timeKey, $usedTimes, true));

            $usedTimes[] = $timeKey;

            $schedule = (new Schedule())
                ->setIgAccount($account)
                ->setDate($date)
                ->setAmount($amounts[$i])
                ->setFulfilled(0)
                ->setStatus(0);

            $this->em->persist($schedule);
            $schedules[] = $schedule;
        }

        $this->em->flush();

        return $schedules;
    }

    private function getRandomTimeInDay(\DateTimeImmutable $day): \DateTimeImmutable
    {
        $minutesInDay = 24 * 60;
        $randomMinute = random_int(0, $minutesInDay / 5 - 1) * 5;

        return $day->setTime(
            intdiv($randomMinute, 60),
            $randomMinute % 60,
            0
        );
    }

    private function splitTargetAmount(int $target, int $count): array
    {
        // losowe wagi
        $weights = [];
        for ($i = 0; $i < $count; $i++) {
            $weights[] = random_int(1, 100);
        }

        $sumWeights = array_sum($weights);

        // oblicz amount proporcjonalnie
        $amounts = [];
        $allocated = 0;
        for ($i = 0; $i < $count; $i++) {
            $value = (int) round($target * ($weights[$i] / $sumWeights));
            $amounts[] = $value;
            $allocated += $value;
        }

        // korekta różnicy (żeby suma == target)
        $diff = $target - $allocated;
        if ($diff !== 0) {
            $amounts[0] += $diff;
        }

        return $amounts;
    }
}