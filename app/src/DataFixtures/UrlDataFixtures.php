<?php
/**
 * Url data fixtures.
 */

namespace App\DataFixtures;

use App\Entity\UrlData;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlDataFixtures.
 *
 * This class is responsible for loading URL data fixtures into the database.
 * It implements the DependentFixtureInterface to define dependencies on other fixtures.
 */
class UrlDataFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load URL data fixtures into the database.
     *
     * This method is called when loading the fixtures and creates multiple URL data entities.
     */
    public function loadData(): void
    {
        // Check if the manager and faker objects are set
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        // Create 70 URL data entities
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

        // Flush the changes to the database
        $this->manager->flush();
    }

    /**
     * Get the dependencies for this fixture.
     *
     * This method defines the dependencies of this fixture on other fixtures.
     * In this case, it depends on the UrlFixtures class.
     *
     * @return string[] An array of fixture class names that this fixture depends on
     */
    public function getDependencies(): array
    {
        return [
            UrlFixtures::class,
        ];
    }
}
