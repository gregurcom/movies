<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $ironMan = new Movie();
        $ironMan->setTitle('Iron man');
        $ironMan->setRating(9.5);
        $ironMan->setImage('https://cdn.pixabay.com/photo/2017/04/26/14/39/welding-2262745_1280.jpg');
        $ironMan->setDescription(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation
            ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in
            voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident
            , sunt in culpa qui officia deserunt mollit anim id est laborum.'
        );
        $ironMan->addActor($this->getReference('actor_1'));
        $ironMan->addActor($this->getReference('actor_2'));

        $manager->persist($ironMan);

        $avengers = new Movie();
        $avengers->setTitle('Avengers');
        $avengers->setRating(9.4);
        $avengers->setImage('https://cdn.pixabay.com/photo/2017/04/26/14/39/welding-2262745_1280.jpg');
        $avengers->setDescription(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation
            ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in
            voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident
            , sunt in culpa qui officia deserunt mollit anim id est laborum.'
        );
        $avengers->addActor($this->getReference('actor_3'));

        $manager->persist($avengers);

        $manager->flush();
    }
}
