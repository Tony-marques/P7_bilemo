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
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: "/api", name: "api_")]
#[OA\Tag("user")]
#[OA\Response(
    response: 401,
    description: "Token invalide"
)]
#[OA\Response(
    response: 403,
    description: "Accès non autorisé"
)]
#[OA\Response(
    response: 500,
    description: "Problème serveur"
)]
class UserController extends AbstractFOSRestController
{
    public function __construct(private TagAwareCacheInterface $cache)
    {
    }

    #[Rest\Get('/users', name: 'get_users', )]
    #[View(serializerGroups: ["user:read"])]
    #[OA\Parameter(
        name:"page",
        in:"query",
        description:"Page que l'on veut récupérer",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: "Récupération de la ressource",
        content: new MediaType(
            mediaType: "application/json",
            schema: new Schema(
                type: "array",
                items: new Items(ref: "#/components/schemas/User")
            )
        )
    )]
    public function getUsers(Security $security, PaginatorInterface $paginator, Request $request): PaginationInterface
    {
        /** @var Client $client */
        $client = $security->getUser();

        $page = $request->query->get("page", 1);
        $pagination = $paginator->paginate($client->getUsers()->toArray(), $page, 2);

        $cache = $this->cache->get("users . $page", function (ItemInterface $item) use ($pagination) {
            $item->expiresAfter(3600);
            $item->tag('users');

            return $pagination;
        });

        return $cache;
    }

    #[Rest\Get('/users/{id}', name: 'get_user', requirements: ["id" => "\d+"])]
    #[View(serializerGroups: ["user:read"])]
    #[OA\Response(
        response: 200,
        description: "Récupération de la ressource",
        content: new Model(type: User::class, groups: ['user:read'], )
    )]
    #[OA\Response(
        response: 404,
        description: "La ressource n'existe pas"
    )]
    public function getOneUser(User $user): User
    {
        $this->denyAccessUnlessGranted("USER_READ", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $cache = $this->cache->get("user{$user->getId()}", function (ItemInterface $item) use ($user) {
            $item->expiresAfter(3600);

            return $user;
        });

        return $cache;
    }

    #[Rest\Delete('/users/{id}', name: 'delete_user', requirements: ["id" => "\d+"])]
    #[View()]
    #[OA\Response(
        response: 204,
        description: "Ressource supprimée"
    )]
    #[OA\Response(
        response: 404,
        description: "La ressource n'existe pas"
    )]
    public function deleteUser(User $user, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted("USER_DELETE", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $this->cache->invalidateTags(["users"]);
        $this->cache->delete("user" . $user->getId());

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Rest\Put('/users/{id}', name: 'update_user', requirements: ["id" => "\d+"])]
    #[View()]
    #[OA\Response(
        response: 200,
        description: "La ressource a été mise à jour"
    )]
    #[OA\Response(
        response: 400,
        description: "Bad request"
    )]
    #[OA\Response(
        response: 404,
        description: "La ressource n'existe pas"
    )]
    #[OA\RequestBody(
        required:true,
        description: "Modification d'un utilisateur",
        content: [new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(properties: [
            new OA\Property(property: "email", type: "string")
        ]))]
    )]
    public function updateUser(User $user, EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted("USER_DELETE", $user, "Vous n'êtes pas propriétaire de cet utilisateur");

        $this->cache->invalidateTags(["users"]);
        $this->cache->delete("user{$user->getId()}");

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
    #[OA\Response(
        response: 201,
        description: "La ressource a été créée",
    )]
    #[OA\Response(
        response: 400,
        description: "Bad request"
    )]
    #[OA\RequestBody(
        required:true,
        description: "Création d'un utilisateur",
        content: [new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(properties: [
            new OA\Property(property: "email", type: "string")
        ]))]
    )]
    public function createUser(Security $security, EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        $this->cache->invalidateTags(["users"]);

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

        return new JsonResponse($message, 201);
    }
}
