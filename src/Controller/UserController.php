<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordUpdateType;
use App\Form\UserType;
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

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Zmień hasło"
        ]);
    }

    #[Route(path: '/admin/user/add', name: 'app_add_user', methods: ['GET', 'POST'])]
    public function add(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {

        $form = $this->createForm(UserType::class, $user = new User());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, '123456');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Dodaj użytkownika',
        ]);
    }
}
