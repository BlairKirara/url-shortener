<?php

namespace App\DataFixtures;

use App\Entity\GuestUser;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlFixtures.
 *
 * Loads URL fixtures into the database.
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }

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

            /** @var Tag[] $tags */
            $tags = $this->getRandomReferenceList('tags', Tag::class, $this->faker->numberBetween(0, 2));
            foreach ($tags as $tag) {
                $url->addTag($tag);
            }

            if ($this->faker->boolean(55)) {
                /** @var User $user */
                $user = $this->getRandomReferenceList('users', User::class, 1)[0];
                $url->setUsers($user);
            } else {
                /** @var GuestUser $guestUser */
                $guestUser = $this->getRandomReferenceList('guestUsers', GuestUser::class, 1)[0];
                $url->setGuestUser($guestUser);
            }

            return $url;
        });

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            UserFixtures::class,
            GuestUserFixtures::class,
        ];
    }
}