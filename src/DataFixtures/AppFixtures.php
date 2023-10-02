<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user = new User();
        $user->setEmail('user@bookapi.com');
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail('admin@bookapi.com');
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));
        $manager->persist($admin);

        $listAuthor = [];
        for ($i=0; $i < 20; $i++) { 
            $author = new Author();
            $author->setFirstName($faker->firstName);
            $author->setLastName($faker->lastName);
            $manager->persist($author);

            $listAuthor[] = $author;
        }

        for ($i=0; $i < 20; $i++) { 
            $book = new Book();
            $book->setTitle($faker->sentence);
            $book->setCoverText($faker->paragraph);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $book->setComment($faker->paragraph);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
