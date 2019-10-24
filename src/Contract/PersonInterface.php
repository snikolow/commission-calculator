<?php

namespace App\Contract;

/**
 * Interface PersonInterface.
 *
 * @package App\Contract
 */
interface PersonInterface
{
    /**
     * Adds new person type to the white-list.
     *
     * @param string $person
     *   The person.
     *
     * @return \App\Contract\PersonInterface
     *   The instance of the class.
     */
    public function addPerson(string $person): PersonInterface;

    /**
     * Indicates whether or not given person is valid.
     *
     * @param string $person
     *   The person type.
     *
     * @return boolean
     *   The validation result.
     */
    public function isValidPerson(string $person): bool;
}
