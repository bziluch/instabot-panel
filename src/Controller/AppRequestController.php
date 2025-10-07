<?php

namespace App\Controller;

use App\Entity\AppRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppRequestController extends AbstractController
{
    #[Route('/request/status/{id}', name: 'app_request_status', methods: ['GET'])]
    public function status(AppRequest $appRequest): JsonResponse
    {
        return new JsonResponse([
            'id' => $appRequest->getId(),
            'status' => $appRequest->getStatus(),
            'response' => $appRequest->getResponse(),
        ]);
    }

    #[Route('/request/view/{id}', name: 'app_request_view', methods: ['GET'])]
    public function view(AppRequest $appRequest): Response
    {
        return $this->render('app-request/status.html.twig', [
            'appRequest' => $appRequest,
        ]);
    }
}
