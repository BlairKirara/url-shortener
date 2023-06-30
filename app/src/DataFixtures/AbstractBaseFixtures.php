<?php
/**
 * Base fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Abstract base fixtures class.
 */
abstract class AbstractBaseFixtures extends Fixture
{
    protected ?Generator $faker = null;

    protected ?ObjectManager $manager = null;

    private array $referencesIndex = [];

    /**
     * Load the fixtures data.
     *
     * @param ObjectManager $manager The object manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData();
    }

    /**
     * Load the data for the fixtures.
     */
    abstract protected function loadData(): void;

    /**
     * Create multiple entities using a factory callback.
     *
     * @param int      $count     The number of entities to create
     * @param string   $groupName The name of the group for the references
     * @param callable $factory   The factory callback function
     *
     * @throws \LogicException If the entity object is not returned from the callback
     */
    protected function createMany(int $count, string $groupName, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            /** @var object|null $entity */
            $entity = $factory($i);

            if (null === $entity) {
                throw new \LogicException('Did you forget to return the entity object from your callback to BaseFixture::createMany()?');
            }

            $this->manager->persist($entity);

            // Store for later usage as groupName_#COUNT#
            $this->addReference(sprintf('%s_%d', $groupName, $i), $entity);
        }
    }

    /**
     * Get a random reference from the specified group.
     *
     * @param string $groupName The name of the group
     *
     * @return object The randomly selected reference
     *
     * @throws \InvalidArgumentException If no references are found for the given group
     */
    protected function getRandomReference(string $groupName): object
    {
        if (!isset($this->referencesIndex[$groupName])) {
            $this->referencesIndex[$groupName] = [];

            foreach ($this->referenceRepository->getReferences() as $key => $reference) {
                if (str_starts_with((string) $key, $groupName.'_')) {
                    $this->referencesIndex[$groupName][] = $key;
                }
            }
        }

        if (empty($this->referencesIndex[$groupName])) {
            throw new \InvalidArgumentException(sprintf('Did not find any references saved with the group name "%s"', $groupName));
        }

        $randomReferenceKey = (string) $this->faker->randomElement($this->referencesIndex[$groupName]);

        return $this->getReference($randomReferenceKey);
    }

    /**
     * Get multiple random references from the specified group.
     *
     * @param string $groupName The name of the group
     * @param int    $count     The number of references to retrieve
     *
     * @return array The array of randomly selected references
     */
    protected function getRandomReferences(string $groupName, int $count): array
    {
        $references = [];
        while (count($references) < $count) {
            $references[] = $this->getRandomReference($groupName);
        }

        return $references;
    }
}
