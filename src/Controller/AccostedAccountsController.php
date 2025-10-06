<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AccostedAccountsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccostedAccountsController extends AbstractController
{
    #[Route('/accosted/stats', name: 'app_accosted_stats')]
    public function stats(AccostedAccountsRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $today = new \DateTimeImmutable('today');
        $thisMonth = new \DateTimeImmutable('first day of this month');
        $previousMonth = (new \DateTimeImmutable('first day of last month'));

        $todayCount = $repository->countByDay($user, $today);
        $thisMonthCount = $repository->countByMonth($user, $thisMonth);
        $previousMonthCount = $repository->countByMonth($user, $previousMonth);
        $totalCount = $repository->countTotal($user);

        return $this->render('accosted/stats.html.twig', [
            'todayCount' => $todayCount,
            'thisMonthCount' => $thisMonthCount,
            'previousMonthCount' => $previousMonthCount,
            'totalCount' => $totalCount,
        ]);
    }
}