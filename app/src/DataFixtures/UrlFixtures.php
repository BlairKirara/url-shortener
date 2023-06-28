<?php

namespace App\DataFixtures;

use App\Entity\GuestUser;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{

    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(60, 'urls', function () {
            $url = new Url();
            $url->setLongName($this->faker->url);
            $url->setShortName($this->faker->regexify('[a-zA-Z0-9]{6}'));
            $url->setCreateTime(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $url->setIsBlocked($this->faker->boolean(20));
            if ($url->isIsBlocked()) {
                $url->setBlockTime(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-1 days', '+100 days')
                    )
                );
            }

            /** @var array<array-key, Tag> $tags */
            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $url->addTag($tag);
            }

            if ($this->faker->boolean(70)) {
                /** @var User $users */
                $users = $this->getRandomReference('users');
                $url->setUsers($users);
            } else {
                /** @var GuestUser $guestUsers */
                $guestUsers = $this->getRandomReference('guestUsers');
                $url->setGuestUser($guestUsers);
            }

            return $url;
        });
        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class, UserFixtures::class, GuestUserFixtures::class,
        ];
    }
}
