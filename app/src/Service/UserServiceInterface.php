<?php
/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 *
 * This interface defines the contract for user services.
 */
interface UserServiceInterface
{
    /**
     * Retrieves a paginated list of users.
     *
     * @param int $page The page number
     * @return PaginationInterface The paginated list of users
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Saves a user.
     *
     * @param User $user The user to save
     */
    public function save(User $user): void;
}
