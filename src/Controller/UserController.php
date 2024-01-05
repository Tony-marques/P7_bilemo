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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function getOneUser(string $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur n'existe pas");
        }

        $this->denyAccessUnlessGranted("USER_READ", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        // if ($user->getClient()->getId() === $security->getUser()->getId()) {
        $view = $this->view($user, Response::HTTP_OK);
        return $this->handleView($view);
        // }


    }
}
