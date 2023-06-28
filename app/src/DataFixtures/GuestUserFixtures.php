<?php

namespace App\DataFixtures;

use App\Entity\GuestUser;

/**
 * Class GuestUserFixtures.
 */
class GuestUserFixtures extends AbstractBaseFixtures
{
    /**
     * @return void
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(15, 'guestUsers', function () {
            $guestUser = new GuestUser();
            $guestUser->setEmail($this->faker->email);

            return $guestUser;
        });
        $this->manager->flush();
    }
}
