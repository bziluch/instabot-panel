<?php

namespace App\Controller;

use App\Repository\IgAccountRepository;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LogController extends AbstractController
{
    #[Route('/admin/log/list', name: 'app_log_list')]
    public function list(
        LogRepository $logRepository,
    ) : Response {

        $logs = $logRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('log/index.html.twig', ['logs' => $logs]);
    }
}