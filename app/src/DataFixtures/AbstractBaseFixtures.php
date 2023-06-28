<?php
/**
 * Base fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;


abstract class AbstractBaseFixtures extends Fixture
{

    protected ?Generator $faker = null;


    protected ?ObjectManager $manager = null;


    private array $referencesIndex = [];


    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData();
    }

    abstract protected function loadData(): void;


    protected function createMany(int $count, string $groupName, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            /** @var object|null $entity */
            $entity = $factory($i);

            if (null === $entity) {
                throw new \LogicException('Did you forget to return the entity object from your callback to BaseFixture::createMany()?');
            }

            $this->manager->persist($entity);

            // store for usage later than groupName_#COUNT#
            $this->addReference(sprintf('%s_%d', $groupName, $i), $entity);
        }
    }


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


    protected function getRandomReferences(string $groupName, int $count): array
    {
        $references = [];
        while (count($references) < $count) {
            $references[] = $this->getRandomReference($groupName);
        }

        return $references;
    }
}
