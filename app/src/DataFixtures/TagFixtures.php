<?php
/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;

/**
 * Class TagFixtures.
 *
 * This class is responsible for loading tag fixtures into the database.
 */
class TagFixtures extends AbstractBaseFixtures
{
    /**
     * Load tag fixtures into the database.
     *
     * This method is called when loading the fixtures and creates multiple tag entities.
     */
    public function loadData(): void
    {
        // Check if the manager and faker objects are set
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        // Create 20 tag entities
        $this->createMany(20, 'tags', function () {
            $tag = new Tag();
            $tag->setName($this->faker->word);

            return $tag;
        });

        // Flush the changes to the database
        $this->manager->flush();
    }
}
