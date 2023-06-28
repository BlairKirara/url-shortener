<?php

namespace App\DataFixtures;

use App\Entity\UrlData;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class UrlVisitedFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{

    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(70, 'urlVisited', function () {
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

    public function getDependencies(): array
    {
        return [
            UrlFixtures::class,
        ];
    }
}
