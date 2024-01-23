<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use App\Entity\Product;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];

        // Clients
        $clients = [];
        for ($i = 0; $i < 2; $i++) {
            $client = new Client();
            $client->setName('Client ' . $i)
                ->setEmail('user' . $i . "@gmail.com")
                ->setRoles(["ROLE_ADMIN"])
                ->setPassword($this->hasher->hashPassword($client, "123"))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($client);
            $clients[] = $client;
        }

        // Users
        for ($i = 2; $i < 6; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . "@gmail.com")
                ->setClient($clients[0])
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($user);
            $users[] = $user;
        }

        // Phones
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setBrand('Brand ' . $i)
                ->setDescription('Description: ' . $i)
                ->setModel("Model: " . $i)
                ->setPrice(rand(0, 100))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($product);
        }

        $manager->flush();
    }
}
