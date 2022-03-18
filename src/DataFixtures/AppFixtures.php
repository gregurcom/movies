<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Movie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $this->loadCategories($manager);
        $this->loadUsers($manager);
        $this->loadActors($manager);
        $this->loadMovies($manager);
    }

    private function loadCategories(ObjectManager $manager): void
    {
        foreach ($this->getCategories() as $title) {
            $category = new Category();
            $category->setTitle($title);
            $slug = str_replace(' ', '-', $title);
            $category->setSlug($slug);

            $manager->persist($category);
            $this->addReference($title, $category);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            $user->setName($fullname);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);

            $manager->persist($user);
            $this->addReference($username, $user);
        }

        $manager->flush();
    }

    private function loadMovies(ObjectManager $manager): void
    {
        foreach ($this->getMovieData() as [$title, $rating, $description]) {
            $movie = new Movie();
            $movie->setTitle($title);
            $movie->setRating($rating);
            $movie->setDescription($description);
            $movie->addActor($this->getReference($this->getActors()[rand(0, count($this->getActors()) - 1)]));
            $movie->setCategory($this->getReference($this->getCategories()[rand(0, count($this->getCategories()) - 1)]));

            $manager->persist($movie);
        }

        $manager->flush();
    }

    public function loadActors(ObjectManager $manager): void
    {
        foreach ($this->getActors() as $name) {
            $actor = new Actor();
            $actor->setName($name);
            $manager->persist($actor);
            $this->setReference($name, $actor);
        }

        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
            ['Jane Doe', 'jane_admin', 'kitten', 'jane_admin@symfony.com', ['ROLE_ADMIN']],
            ['Tom Doe', 'tom_admin', 'kitten', 'tom_admin@symfony.com', ['ROLE_ADMIN']],
            ['John Doe', 'john_user', 'kitten', 'john_user@symfony.com', ['ROLE_USER']],
        ];
    }

    private function getMovieData(): array
    {
        $movies = [];
        foreach ($this->getPhrases() as $i => $title) {
            $movies[] = [
                $title,
                rand(0, 10),
                $this->getMovieDescription(),
                $this->getReference(['jane_admin', 'tom_admin'][0 === $i ? 0 : random_int(0, 1)]),
            ];
        }

        return $movies;
    }

    private function getPhrases(): array
    {
        return [
            'Lorem ipsum dolor sit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis',
            'Eros diam',
            'In hac habitasse',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere',
            'Ut eleifend mauris et',
            'Aliquam sodales odio id eleifend',
            'Urna nisl sollicitudin',
            'Nulla porta lobortis',
            'Curabitur aliquam euismod',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien',
            'Ubi est barbatus nix',
            'Abnobas sunt hilotaes',
            'Ubi est audax amicitia',
            'Eposs sunt solems',
            'Vae humani generis',
            'Diatrias tolerare',
            'Teres talis saepe tractare',
            'Silva de secundus galatae demitto quadra',
            'Sunt accentores vitare salvus flavum parses',
            'Potus sensim ad ferox abnoba',
            'Sunt seculaes transferre',
            'Era brevis ratione est',
            'Sunt torquises imitari velox',
            'Mineralis persuadere omnes finises desiderium',
            'Bassus fatalis classiss',
        ];
    }

    private function getActors()
    {
        return [
            'John Doe',
            'Tsh Asd',
            'Aduba Loam',
            'Hunk Lib',
            'Tom Joe',
            'Michael Yuan',
            'Poj Kand',
            'Ibr Ugany',
            'Pag Hjsc',
        ];
    }

    public function getCategories()
    {
        return [
            'Action',
            'Comedy',
            'Drama',
            'Fantasy',
            'Horror',
            'Mystery',
            'Romance',
            'Thriller',
            'Western',
        ];
    }

    public function getComments()
    {
        return [
            'Lorem ipsum dolor sit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis',
            'Eros diam',
            'In hac habitasse',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere',
            'Ut eleifend mauris et',
            'Aliquam sodales odio id eleifend',
            'Urna nisl sollicitudin',
            'Nulla porta lobortis',
            'Curabitur aliquam euismod',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien',
            'Ubi est barbatus nix',
            'Abnobas sunt hilotaes',
            'Ubi est audax amicitia',
            'Eposs sunt solems',
            'Vae humani generis',
            'Diatrias tolerare',
            'Teres talis saepe tractare',
            'Silva de secundus galatae demitto quadra',
            'Sunt accentores vitare salvus flavum parses',
            'Potus sensim ad ferox abnoba',
            'Sunt seculaes transferre',
            'Era brevis ratione est',
            'Sunt torquises imitari velox',
            'Mineralis persuadere omnes finises desiderium',
            'Bassus fatalis classiss',
        ];
    }

    private function getMovieDescription(): string
    {
        return <<<'MARKDOWN'
            Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
            reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
            deserunt mollit anim id est laborum.
            MARKDOWN;
    }
}

