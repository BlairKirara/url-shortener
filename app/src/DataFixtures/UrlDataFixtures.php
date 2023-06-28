<?php

namespace App\DataFixtures;

use App\Entity\UrlData;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlDataFixtures.
 */
class UrlDataFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * @return void
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(70, 'urlData', function () {
            $urlData = new UrlData();
            $urlData->setUrl($this->getRandomReference('urls'));
            $urlData->setVisitTime(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $urlData;
        });
        $this->manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            UrlFixtures::class,
        ];
    }
}
