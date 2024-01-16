<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: "/api", name: "api_")]
class UserController extends AbstractFOSRestController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Rest\Get('/users', name: 'get_users', )]
    #[IsGranted("IS_AUTHENTICATED_FULLY", null, "test")]
    public function getUsers(Security $security, ClientRepository $clientRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $client = $security->getUser();

        $users = $clientRepository->findOneBy(["id" => $client])->getUsers();

        $page = $request->query->get("page", 1);
        $pagination = $paginator->paginate($users, $page, 2);

        $view = $this->view($pagination, Response::HTTP_OK);
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

        $view = $this->view($user, Response::HTTP_OK);
        return $this->handleView($view);
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
    public function updateUser(string $id, UserRepository $userRepository, EntityManagerInterface $em, Request $request, ValidatorInterface $validator): Response
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

        $errors = $validator->validate($user, null, groups: ["user:update"]);

        if ($errors->count() > 0) {
            $view = $this->view($errors, Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $em->persist($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur modifié avec succès"
        ];

        $view = $this->view($message, Response::HTTP_CREATED);
        return $this->handleView($view);
    }

    #[Rest\Post('/users', name: 'create_user')]
    public function createUser(Security $security, EntityManagerInterface $em, Request $request, ValidatorInterface $validator): Response
    {
        $client = $security->getUser();

        $content = $request->toArray();
        $email = $content["email"];

        $user = new User();

        $user->setEmail($email)
            ->setCreatedAt(new DateTimeImmutable())
            ->setClient($client);

        $errors = $validator->validate($user, null, groups: ["user:create"]);
        if ($errors->count() > 0) {
            $view = $this->view($errors, Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $em->persist($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur créé avec succès"
        ];

        $view = $this->view($message, Response::HTTP_CREATED);
        return $this->handleView($view);
    }
}
