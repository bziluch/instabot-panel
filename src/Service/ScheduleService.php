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
     * Tworzy harmonogram na dany dzień (dzisiaj lub jutro)
     *
     * @param IgAccount $account konto IG, dla którego tworzymy schedule
     * @param int $targetAmount docelowa suma amount (domyślnie 300)
     * @param int $minSchedules minimalna liczba schedule w dobie (domyślnie 6)
     * @param int $maxSchedules maksymalna liczba schedule w dobie (domyślnie 8)
     * @param string $day określa dzień: 'today' lub 'tomorrow' (domyślnie 'tomorrow')
     *
     * @return Schedule[] wygenerowane schedule
     */
    public function generateSchedule(
        IgAccount $account,
        int $targetAmount = 300,
        int $minSchedules = 6,
        int $maxSchedules = 8,
        string $day = 'tomorrow'
    ): array {
        $schedules = [];

        $count = random_int($minSchedules, $maxSchedules);
        $amounts = $this->splitTargetAmount($targetAmount, $count);

        $baseDay = new \DateTimeImmutable($day === 'today' ? 'today' : 'tomorrow');
        $baseDay = $baseDay->setTime(0, 0, 0);

        $usedTimes = [];

        for ($i = 0; $i < $count; $i++) {
            $date = $this->getRandomTimeWithSpacing($baseDay, $usedTimes, 120); // 120 minut = 2h
            $usedTimes[] = $date;

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

    /**
     * Losuje godzinę w obrębie dnia z minimalnym odstępem między losowaniami
     */
    private function getRandomTimeWithSpacing(
        \DateTimeImmutable $day,
        array $usedTimes,
        int $minSpacingMinutes
    ): \DateTimeImmutable {
        $attempts = 0;
        do {
            $attempts++;
            $minutesInDay = 24 * 60;
            $randomMinute = random_int(0, $minutesInDay / 5 - 1) * 5;
            $candidate = $day->setTime(intdiv($randomMinute, 60), $randomMinute % 60);

            $tooClose = false;
            foreach ($usedTimes as $used) {
                if (abs($candidate->getTimestamp() - $used->getTimestamp()) < ($minSpacingMinutes * 60)) {
                    $tooClose = true;
                    break;
                }
            }
        } while ($tooClose && $attempts < 200);

        return $candidate;
    }

    private function splitTargetAmount(int $target, int $count): array
    {
        $weights = [];
        for ($i = 0; $i < $count; $i++) {
            $weights[] = random_int(1, 100);
        }

        $sumWeights = array_sum($weights);

        $amounts = [];
        $allocated = 0;
        for ($i = 0; $i < $count; $i++) {
            $value = (int) round($target * ($weights[$i] / $sumWeights));
            $amounts[] = $value;
            $allocated += $value;
        }

        $diff = $target - $allocated;
        if ($diff !== 0) {
            $amounts[0] += $diff;
        }

        return $amounts;
    }
}