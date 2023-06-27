<?php

namespace App\DataFixtures;

use App\Entity\GuestUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GuestUserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $guestUser = new GuestUser();
            $guestUser->setEmail($faker->email);

            $manager->persist($guestUser);
        }

        $manager->flush();
    }
}
