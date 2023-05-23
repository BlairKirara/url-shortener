<?php
/**
 * Url fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Url;

/**
 * Class UrlFixtures.
 */
class UrlFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $url = new Url();
            $url->setShortName($this->faker->url);
            $url->setLongName($this->faker->url);
            $url->setCreateTime(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '100 days'))
            );
            $url->setIsBlocked($this->faker->boolean);
            $url->setBlockTime(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '100 days'))
            );
            $this->manager->persist($url);
        }

        $this->manager->flush();
    }// end loadData()
}// end class
