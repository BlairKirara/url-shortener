<?php

/**
 * Guest user fixtures.
 */

namespace App\DataFixtures;

use App\Entity\GuestUser;

/**
 * Class GuestUserFixtures.
 *
 * This class is responsible for loading guest user fixtures into the database.
 */
class GuestUserFixtures extends AbstractBaseFixtures
{
    /**
     * Load guest user fixtures into the database.
     *
     * This method is called when loading the fixtures and creates multiple guest user entities.
     */
    public function loadData(): void
    {
        // Check if the manager and faker objects are set
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }

        // Create 15 guest user entities
        $this->createMany(15, 'guestUsers', function () {
            $guestUser = new GuestUser();
            $guestUser->setEmail($this->faker->email);

            return $guestUser;
        });

        // Flush the changes to the database
        $this->manager->flush();
    }
}
