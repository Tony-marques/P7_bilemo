<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ErrorException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use FOS\RestBundle\Controller\Annotations\View;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: "/api", name: "api_")]
class UserController extends AbstractFOSRestController
{
    #[Rest\Get('/users', name: 'get_users', )]
    #[View(serializerGroups: ["user:read"])]
    public function getUsers(Security $security, PaginatorInterface $paginator, Request $request): PaginationInterface
    {
        /** @var Client $client */
        $client = $security->getUser();

        $page = $request->query->get("page", 1);
        $pagination = $paginator->paginate($client->getUsers()->toArray(), $page, 2);

        return $pagination;
    }

    #[Rest\Get('/users/{id}', name: 'get_user', requirements: ["id" => "\d+"])]
    #[View(serializerGroups: ["user:read"])]
    public function getOneUser(User $user): User
    {
        $this->denyAccessUnlessGranted("USER_READ", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        return $user;
    }

    #[Rest\Delete('/users/{id}', name: 'delete_user', requirements: ["id" => "\d+"])]
    #[View()]
    public function deleteUser(User $user, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted("USER_DELETE", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $em->remove($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur supprimé avec succès"
        ];

        return $message;
    }

    #[Rest\Put('/users/{id}', name: 'update_user', requirements: ["id" => "\d+"])]
    #[View()]
    public function updateUser(User $user, EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted("USER_DELETE", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $content = $request->toArray();
        
        if(!isset($content["email"])) {
            throw new ErrorException("Merci de renseigner un e-mail");
        }

        $email = $content["email"];
        
        $user->setEmail($email)
            ->setUpdatedAt(new DateTimeImmutable());

        $errors = $validator->validate($user, null, groups: ["user:update"]);

        if ($errors->count() > 0) {
            $errorsList = [];
            foreach($errors as $error) {
                $errorsList[] = ["message" => $error->getMessage()];
            }

            foreach($errorsList as $key => $value) {
                throw new ErrorException($errorsList[$key]["message"]);
            }
        }

        $em->persist($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur modifié avec succès"
        ];

        return $message;
    }

    #[Rest\Post('/users', name: 'create_user')]
    #[View()]
    public function createUser(Security $security, EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        $client = $security->getUser();

        $content = $request->toArray();

        if(!isset($content["email"])) {
            throw new ErrorException("Merci de renseigner un e-mail");
        }

        $email = $content["email"];

        $user = new User();

        $user->setEmail($email)
            ->setCreatedAt(new DateTimeImmutable())
            ->setClient($client);

        $errors = $validator->validate($user, null, groups: ["user:create"]);
        if ($errors->count() > 0) {
            $errorsList = [];
            foreach($errors as $error) {
                $errorsList[] = ["message" => $error->getMessage()];
            }
            foreach($errorsList as $key => $value) {
                throw new ErrorException($errorsList[$key]["message"]);
            }
        }
        
        $em->persist($user);
        $em->flush();

        $message = [
            "success" => "Utilisateur créé avec succès"
        ];

        return $message;
    }
}
