<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
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

    #[Rest\Delete('/users/{id}', name: 'delete_user', requirements: ["id" => "\d+"])]
    public function deleteUser(string $id, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur n'existe pas");
        }

        $this->denyAccessUnlessGranted("USER_DELETE", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $em->remove($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur supprimé avec succès"
        ];

        $view = $this->view($message, Response::HTTP_OK);
        return $this->handleView($view);
    }

    #[Rest\Put('/users/{id}', name: 'update_user', requirements: ["id" => "\d+"])]
    public function updateUser(string $id, UserRepository $userRepository, EntityManagerInterface $em, Request $request): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur n'existe pas");
        }

        $this->denyAccessUnlessGranted("USER_DELETE", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $content = $request->toArray();
        $email = $content["email"];

        $user->setEmail($email)
            ->setUpdatedAt(new DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur modifié avec succès"
        ];

        $view = $this->view($message, Response::HTTP_CREATED);
        return $this->handleView($view);
    }
}
