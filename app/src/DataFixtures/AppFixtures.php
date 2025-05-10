<?php

/**
 * App fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AppFixtures.
 *
 * This class is responsible for loading fixtures into the database.
 */
class AppFixtures extends Fixture
{
    /**
     * Load fixtures into the database.
     *
     * @param ObjectManager $manager The object manager
     */
    public function load(ObjectManager $manager): void
    {
        // Uncomment the following lines to create and persist entities
        // $product = new Product();
        // $manager->persist($product);

        // Flush the changes to the database
        $manager->flush();
    }
}
