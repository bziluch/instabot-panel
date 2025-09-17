<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(path: '/users', name: 'app_user_list', methods: ['GET'])]
    public function index(
        UserRepository $repository,
    ): Response {

        return $this->render('user/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }
}