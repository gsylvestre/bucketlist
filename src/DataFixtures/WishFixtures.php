<?php

namespace App\DataFixtures;

use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WishFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        //crée 100 idées !
        for($i=1; $i<100; $i++){
            //crée une instance vide
            $wish = new Wish();
            //on l'hydrate
            $wish->setTitle($faker->sentence);
            $wish->setDescription($faker->realText(500));
            $wish->setIsPublished($faker->boolean(80));
            $wish->setAuthor($faker->userName);

            $dateCreated = $faker->dateTimeBetween("-1 month", "now");
            $wish->setDateCreated(\DateTimeImmutable::createFromMutable($dateCreated));

            $dateUpdated = $faker->dateTimeBetween($dateCreated, "now");
            $wish->setDateUpdated(\DateTimeImmutable::createFromMutable($dateUpdated));

            //on sauvegarde
            $manager->persist($wish);
        }

        //on exécute !
        $manager->flush();
    }
}
