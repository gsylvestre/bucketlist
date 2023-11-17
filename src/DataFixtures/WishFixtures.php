<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WishFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        $categoryRepository = $manager->getRepository(Category::class);
        $allCategories = $categoryRepository->findAll();

        //crée 100 idées !
        for($i=1; $i<100; $i++){
            //crée une instance vide
            $wish = new Wish();
            //on l'hydrate
            $wish->setTitle($faker->sentence);
            $wish->setDescription($faker->realText(500));
            $wish->setIsPublished($faker->boolean(80));

            //voir UserFixtures pour la création des références
            $wish->setCreator($this->getReference("user".$faker->numberBetween(1,10), User::class));

            $dateCreated = $faker->dateTimeBetween("-1 month", "now");
            $wish->setDateCreated(\DateTimeImmutable::createFromMutable($dateCreated));

            $dateUpdated = $faker->dateTimeBetween($dateCreated, "now");
            $wish->setDateUpdated(\DateTimeImmutable::createFromMutable($dateUpdated));

            $category = $faker->optional(0.9)->randomElement($allCategories);
            $wish->setCategory($category);

            //on sauvegarde
            $manager->persist($wish);
        }

        //on exécute !
        $manager->flush();
    }

    public function getDependencies()
    {
        return [CategoryFixtures::class, UserFixtures::class];
    }
}
