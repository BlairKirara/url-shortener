<?php
/**
 * Url Visited fixtures.
 */

namespace App\DataFixtures;

use App\Entity\UrlData;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlVisitedFixtures.
 */
class UrlVisitedFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
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
        $this->createMany(50, 'urlVisited', function () {
            $urlVisited = new UrlData();
            $urlVisited->setUrl($this->getRandomReference('urls'));
            $urlVisited->setVisitTime(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $urlVisited;
        });
        $this->manager->flush();
    }

    /**
     * Get dependencies.
     *
     * @return array<int, string>
     */
    public function getDependencies(): array
    {
        return [
            UrlFixtures::class,
        ];
    }
}
