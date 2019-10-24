<?php

namespace Tests\Calculator;

use App\Calculator\Fee\CashIn\CashInDefaultFee;
use App\Calculator\Fee\CashOut\LegalFee;
use App\Calculator\Fee\CashOut\NaturalFee;
use App\Calculator\FeeFactory;
use App\Enum\MessagesEnum;
use App\Enum\OperationsEnum;
use App\Enum\PersonEnum;
use PHPUnit\Framework\TestCase;

/**
 * Class FeeFactoryTest.
 *
 * @package Tests\Calculator
 */
class FeeFactoryTest extends TestCase
{
    public function testFeeFactoryImplementationsAreValid(): void
    {
        $feeManager = FeeFactory::factoryDefault(OperationsEnum::CASH_IN);

        $this->assertInstanceOf(CashInDefaultFee::class, $feeManager);

        $feeManager = FeeFactory::factoryDefault(OperationsEnum::CASH_OUT, PersonEnum::NATURAL);

        $this->assertInstanceOf(NaturalFee::class, $feeManager);

        $feeManager = FeeFactory::factoryDefault(OperationsEnum::CASH_OUT, PersonEnum::LEGAL);

        $this->assertInstanceOf(LegalFee::class, $feeManager);
    }

    public function testExceptionWillBeThrownWithInvalidArguments(): void
    {
        try {
            $feeManager = FeeFactory::factoryDefault('transfer', 'institution');
        } catch(\RuntimeException $ex) {
            $this->assertEquals($ex->getMessage(), MessagesEnum::EXCEPTION_MISSING_IMPLEMENTATION);
        }

        $this->expectException(\RuntimeException::class);
        $feeManager = FeeFactory::factoryDefault('transfer', 'institution');
    }

    public function testExceptionWillBeThrownForCashOutAndMissingPerson()
    {
        try {
            $feeManager = FeeFactory::factoryDefault(OperationsEnum::CASH_OUT);
        } catch(\InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), MessagesEnum::EXCEPTION_MISSING_PERSON);
        }

        $this->expectException(\InvalidArgumentException::class);
        $feeManager = FeeFactory::factoryDefault(OperationsEnum::CASH_OUT);
    }
}
