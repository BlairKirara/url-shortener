<?php
/**
 * Url fixtures.
 */

namespace App\DataFixtures;

use App\Entity\GuestUser;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlFixtures.
 *
 * This class is responsible for loading URL fixtures into the database.
 * It implements the DependentFixtureInterface to define dependencies on other fixtures.
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load URL fixtures into the database.
     *
     * This method is called when loading the fixtures and creates multiple URL entities.
     */
    public function loadData(): void
    {
        // Check if the manager and faker objects are set
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        // Create 90 URL entities
        $this->createMany(90, 'urls', function () {
            $url = new Url();
            $url->setLongName($this->faker->url);
            $url->setShortName($this->faker->regexify('[a-zA-Z0-9]{6}'));
            $url->setCreateTime(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $url->setIsBlocked($this->faker->boolean(15));

            if ($url->isIsBlocked()) {
                $url->setBlockTime(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-1 days', '+100 days')
                    )
                );
            }

            // Get random tags and add them to the URL entity
            /** @var array<array-key, Tag> $tags */
            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 2));
            foreach ($tags as $tag) {
                $url->addTag($tag);
            }

            if ($this->faker->boolean(55)) {
                // Get a random user and set it as the URL's owner
                /** @var User $users */
                $users = $this->getRandomReference('users');
                $url->setUsers($users);
            } else {
                // Get a random guest user and set it as the URL's owner
                /** @var GuestUser $guestUsers */
                $guestUsers = $this->getRandomReference('guestUsers');
                $url->setGuestUser($guestUsers);
            }

            return $url;
        });

        // Flush the changes to the database
        $this->manager->flush();
    }

    /**
     * Get the dependencies for this fixture.
     *
     * This method defines the dependencies of this fixture on other fixtures.
     * In this case, it depends on the TagFixtures, UserFixtures, and GuestUserFixtures classes.
     *
     * @return string[] An array of fixture class names that this fixture depends on
     */
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            UserFixtures::class,
            GuestUserFixtures::class,
        ];
    }
}
