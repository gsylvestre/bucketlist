<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ){

    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("admin@bucket-list.fr");
        $user->setUsername("admin");
        $user->setPassword($this->passwordHasher->hashPassword($user, "123456"));
        $user->setRoles(["ROLE_ADMIN"]);
        $manager->persist($user);

        for($i=1; $i<=10; $i++){
            $user = new User();
            $user->setEmail("user$i@yo.fr");
            $user->setUsername("user$i");
            $user->setPassword($this->passwordHasher->hashPassword($user, "123456"));
            $user->setRoles(["ROLE_USER"]);
            $manager->persist($user);
            $this->addReference("user$i", $user);
        }

        $manager->flush();
    }
}
