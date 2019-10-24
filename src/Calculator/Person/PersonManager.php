<?php

namespace App\Calculator\Person;

use App\Contract\PersonInterface;
use App\Enum\PersonEnum;

/**
 * Class PersonManager.
 *
 * @package App\Calculator\Person
 */
class PersonManager implements PersonInterface
{
    /**
     * Contains a white-list with valid persons.
     *
     * @var array
     */
    private $validPersons = [
        PersonEnum::LEGAL,
        PersonEnum::NATURAL,
    ];

    /**
     * {@inheritdoc}
     */
    public function addPerson(string $person): PersonInterface
    {
        if (!in_array($person, $this->validPersons)) {
            $this->validPersons[] = $person;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidPerson(string $person): bool
    {
        return in_array($person, $this->validPersons);
    }
}
