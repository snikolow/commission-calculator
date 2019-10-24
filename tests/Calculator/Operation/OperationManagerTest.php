<?php

namespace Tests\Calculator\Operation;

use App\Calculator\Operation\OperationManager;
use App\Contract\OperationInterface;
use App\Enum\OperationsEnum;
use PHPUnit\Framework\TestCase;

/**
 * Class OperationManagerTest.
 *
 * @package Tests\Calculator\Operation
 */
class OperationManagerTest extends TestCase
{
    /**
     * The operation manager class.
     *
     * @var \App\Calculator\Operation\OperationManager
     */
    private $operationManager;

    public function setUp(): void
    {
        $this->operationManager = new OperationManager();
    }

    public function testDefaultOperationsAreValid(): void
    {
        $list = [
            OperationsEnum::CASH_IN,
            OperationsEnum::CASH_OUT,
        ];

        foreach ($list as $operation) {
            $this->assertTrue($this->operationManager->isValidOperation($operation));
        }
    }

    public function testNewOperationCanBeAddedAndValidated(): void
    {
        $reference = $this->operationManager->addOperation('transfer');

        $this->assertTrue($this->operationManager->isValidOperation('transfer'));
        $this->assertInstanceOf(OperationInterface::class, $reference);
    }

    public function testInvalidOperationIsNotValidated(): void
    {
        $this->assertFalse($this->operationManager->isValidOperation('transfer'));
    }
}
