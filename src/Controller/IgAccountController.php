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
    public function form(
        Request $request,
        EntityManagerInterface $entityManager,
        IgAccountRepository $igAccountRepository,
    ) : Response {

        $form = $this->createForm(IgAccountType::class, $igAccount = new IgAccount());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $igAccount->setUser($user = $this->getUser());
            $igAccount->setActive(true);

            if (null !== ($oldAccount = $igAccountRepository->findOneBy(['User' => $user, 'active' => true]))) {
                $oldAccount->setActive(false);
            }

            $entityManager->persist($igAccount);

            $appRequest = (new AppRequest())
                ->setAccount($igAccount)
                ->setMessage('check-login-ig');
            $entityManager->persist($appRequest);

            $entityManager->flush();

            return $this->redirectToRoute('app_igaccount_list');
        }

        return $this->render('default/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Dodaj konto IG"
        ]);
    }

    #[Route('/ig-account/{id}/check-connection', name: 'app_igaccount_check_connection')]
    public function checkConnection(
        IgAccountRepository $igAccountRepository,
        int $id
    ) : Response {

        if (null == ($igAccount = $igAccountRepository->find($id)) || $igAccount->getUser()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('error', 'Konto IG nie istnieje');
            return $this->redirectToRoute('app_igaccount_list');
        }

        dd('work in progress...');

        /*
         * TODO: Async do sprawdzania statusu połączenia z IG
         * TODO: Formularz w przypadku kodu sms/email
         * TODO: Komunikacja z workerem w pythonie
         */
    }
}