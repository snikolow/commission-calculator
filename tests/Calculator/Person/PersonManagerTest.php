<?php

namespace Tests\Calculator\Person;

use App\Calculator\Person\PersonManager;
use App\Contract\PersonInterface;
use App\Enum\PersonEnum;
use PHPUnit\Framework\TestCase;

/**
 * Class PersonManagerTest.
 *
 * @package Tests\Calculator\Person
 */
class PersonManagerTest extends TestCase
{
    /**
     * @var \App\Calculator\Person\PersonManager
     */
    private $personManager;

    protected function setUp(): void
    {
        $this->personManager = new PersonManager();
    }

    public function testDefaultPersonsAreValid(): void
    {
        $list = [
            PersonEnum::LEGAL,
            PersonEnum::NATURAL,
        ];

        foreach ($list as $person) {
            $this->assertTrue($this->personManager->isValidPerson($person));
        }
    }

    public function testPersonCanBeAddedAndValidated()
    {
        $reference = $this->personManager->addPerson('institution');

        $this->assertTrue($this->personManager->isValidPerson('institution'));
        $this->assertInstanceOf(PersonInterface::class, $reference);
    }

    public function testInvalidPersonIsNotValidated()
    {
        $this->assertFalse($this->personManager->isValidPerson('institution'));
    }
}
