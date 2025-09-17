<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordUpdateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(path: '/admin/users', name: 'app_user_list', methods: ['GET'])]
    public function index(
        UserRepository $repository,
    ): Response {

        return $this->render('user/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }

    #[Route(path: '/change-password', name: 'app_update_password', methods: ['GET', 'POST'])]
    public function updatePassword(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {

        /** @var User $user */
        $form = $this->createForm(UserPasswordUpdateType::class, $user = $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();

            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash('success', 'Hasło zostało zmienione.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/change-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}