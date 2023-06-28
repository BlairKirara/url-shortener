<?php

namespace App\DataFixtures;

use App\Entity\Tag;

/**
 * Class TagFixtures.
 */
class TagFixtures extends AbstractBaseFixtures
{
    /**
     * @return void
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(20, 'tags', function () {
            $tag = new Tag();
            $tag->setName($this->faker->word);

            return $tag;
        });
        $this->manager->flush();
    }
}
