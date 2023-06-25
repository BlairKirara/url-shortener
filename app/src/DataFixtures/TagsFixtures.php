<?php
/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tags;

/**
 * Class TagFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class TagsFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(10, 'tags', function () {
            $tag = new Tags();
            $tag->setName($this->faker->word);

            return $tag;
        });
        $this->manager->flush();
    }
}