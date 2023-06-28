<?php

namespace App\DataFixtures;

use App\Entity\Tag;


class TagFixtures extends AbstractBaseFixtures
{

    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(10, 'tags', function () {
            $tag = new Tag();
            $tag->setName($this->faker->word);

            return $tag;
        });
        $this->manager->flush();
    }
}
