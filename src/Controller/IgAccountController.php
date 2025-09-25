<?php

namespace App\Controller;

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

        $igAccounts = $igAccountRepository->findBy($this->isGranted('ROLE_ADMIN') ? [] : ['user' => $this->getUser()]);

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
            $entityManager->flush();

            return $this->redirectToRoute('app_igaccount_list');
        }

        return $this->render('default/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Dodaj konto IG"
        ]);
    }
}