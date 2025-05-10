<?php

/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;

/**
 * Class TagFixtures.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagFixtures extends AbstractBaseFixtures
{
    private $generatedTagNames = [];

    /**
     * Load tag fixtures into the database.
     *
     * This method is called when loading the fixtures and creates multiple tag entities.
     */
    public function loadData(): void
    {
        // Check if the manager and faker objects are set
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }

        $this->generatedTagNames = [];

        // Create 20 tag entities
        $this->createMany(20, 'tags', function () {
            $tag = new Tag();
            $tagName = $this->generateUniqueTagName();
            $tag->setName($tagName);

            return $tag;
        });

        // Flush the changes to the database
        $this->manager->flush();
    }

    /**
     * Generate a unique tag name.
     *
     * @return string The generated unique tag name
     */
    private function generateUniqueTagName(): string
    {
        $tagName = $this->faker->word;

        // Check if the generated tag name is already used
        while (in_array($tagName, $this->generatedTagNames)) {
            $tagName = $this->faker->word;
        }

        // Add the generated tag name to the list of used names
        $this->generatedTagNames[] = $tagName;

        return $tagName;
    }
}
