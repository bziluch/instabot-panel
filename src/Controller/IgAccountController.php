<?php

namespace App\Controller;

use App\Entity\AppRequest;
use App\Entity\IgAccount;
use App\Form\IgAccountType;
use App\Repository\IgAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IgAccountController extends AbstractController
{
    #[Route('/ig-account/list', name: 'app_igaccount_list')]
    public function list(
        IgAccountRepository $igAccountRepository,
    ) : Response {

        $igAccounts = $igAccountRepository->findBy($this->isGranted('ROLE_ADMIN') ? [] : ['User' => $this->getUser()]);

        return $this->render('ig-account/index.html.twig', ['accounts' => $igAccounts]);
    }

    #[Route('/ig-account/add', name: 'app_igaccount_add')]
    #[Route('/ig-account/edit/{id}', name: 'app_igaccount_edit')]
    public function form(
        Request $request,
        EntityManagerInterface $entityManager,
        IgAccountRepository $igAccountRepository,
        int $id = null
    ) : Response {

        if ($id) {
            $igAccount = $igAccountRepository->find($id);
            if (!$this->isGranted('ROLE_ADMIN') && $igAccount->getUser()->getId() !== $this->getUser()->getId()) {
                $this->addFlash('error', 'Brak uprawnień do edycji konta');
                return $this->redirectToRoute('app_igaccount_list');
            }
        } else {
            $igAccount = new IgAccount();
        }

        $form = $this->createForm(IgAccountType::class, $igAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            if ($id) {
//                $igAccount->notifyPasswordChange();
//            }

            $igAccount->setUser($user = $this->getUser());

            if (null !== ($oldAccount = $igAccountRepository->findOneBy(['User' => $user, 'active' => true]))
                && $oldAccount->getId() !== $igAccount->getId()
            ) {
                $oldAccount->setActive(false);
                $igAccount->setActive(true);
            }

            $entityManager->persist($igAccount);

            $appRequest = (new AppRequest())
                ->setAccount($igAccount)
                ->setMessage('check-login-ig');
            $entityManager->persist($appRequest);

            $entityManager->flush();

            return $this->redirectToRoute('app_request_view', ['id' => $appRequest->getId()]);
        }

        return $this->render('default/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Dodaj konto IG"
        ]);
    }
}