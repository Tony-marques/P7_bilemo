<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: "/api", name: "api_")]
class UserController extends AbstractFOSRestController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Rest\Get('/users', name: 'get_users')]
    public function getUsers(Security $security, ClientRepository $clientRepository): Response
    {
        $clientId = $security->getUser()->getId();

        $users = $clientRepository->findOneBy(["id" => $clientId])->getUsers();

        $view = $this->view($users, Response::HTTP_OK);

        return $this->handleView($view);
    }

    #[Rest\Get('/users/{id}', name: 'get_user', requirements: ["id" => "\d+"])]
    public function getOneUser(User $user, Security $security): Response
    {
        if ($user->getClient()->getId() === $security->getUser()->getId()) {
            $view = $this->view($user, Response::HTTP_OK);
            return $this->handleView($view);
        }

        $errors = [
            "errorMessage" => "Vous n'êtes pas le propriétaire de cet utilisateur"
        ];

        $view = $this->view($errors, Response::HTTP_FORBIDDEN);

        return $this->handleView($view);
    }
}
