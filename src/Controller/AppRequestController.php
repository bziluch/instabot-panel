<?php

namespace App\Controller;

use App\Entity\AppRequest;
use App\Form\TwoFaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/request/view/{id}', name: 'app_request_view', methods: ['GET', 'POST'])]
    public function view(
        AppRequest $appRequest,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {

        $form = $this->createForm(TwoFaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $twoFaCode = $data['twoFaCode'];

            // zamykamy stary request
            $appRequest->setStatus(AppRequest::STATUS_CLOSED);
            $entityManager->persist($appRequest);

            $newRequest = (new AppRequest())
                ->setAccount($appRequest->getAccount())
                ->setType(AppRequest::TYPE_2FA_CODE)
                ->setDirectory(AppRequest::DIR_PANEL_TO_APP)
                ->setMessage(json_encode(['2faCode' => $twoFaCode]))
                ->setStatus(AppRequest::STATUS_PENDING);

            $entityManager->persist($newRequest);
            $entityManager->flush();

            return $this->redirectToRoute('app_request_view', ['id' => $newRequest->getId()]);
        }

        return $this->render('app-request/status.html.twig', [
            'form' => $form->createView(),
            'appRequest' => $appRequest,
        ]);
    }
}
