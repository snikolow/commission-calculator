<?php

namespace Tests\Calculator;

use App\Calculator\Currency\CurrencyManager;
use App\Calculator\Fee\CashIn\CashInDefaultFee;
use App\Calculator\Fee\CashOut\LegalFee;
use App\Calculator\Fee\CashOut\NaturalFee;
use App\Calculator\FeeFactory;
use App\Enum\MessagesEnum;
use App\Enum\OperationsEnum;
use App\Enum\PersonEnum;
use Brick\Money\Money;
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

    public function testFeeWontBeCalculatedForSingleOperation()
    {
        $money = Money::of('1000.00', 'EUR');
        $feeManager = $this->createFeeManager($money, OperationsEnum::CASH_OUT, PersonEnum::NATURAL);

        $feeManager->setUserId(1);
        $feeManager->setDate('2019-01-01');

        $this->assertEquals(0, $feeManager->calculateFee($money));
    }

    public function testFeeWontBeCalculatedForTwoOperationsInEuro()
    {
        $entries = [];
        $mapping = [
            ['2019-01-01', 1, OperationsEnum::CASH_OUT, PersonEnum::NATURAL, '250.00', 'EUR'],
            ['2019-01-02', 1, OperationsEnum::CASH_OUT, PersonEnum::NATURAL, '500.00', 'EUR'],
            ['2019-01-03', 1, OperationsEnum::CASH_OUT, PersonEnum::NATURAL, '250.00', 'EUR'],
        ];

        foreach ($mapping as $entry) {
            $money = Money::of($entry[4], $entry[5]);
            $feeManager = $this->createFeeManager($money, $entry[2], $entry[3]);
            $feeManager->setStaticEntries($entries);

            $this->assertEquals(0, $feeManager->calculateFee($money));
        }
    }

    public function testFeeWillBeCalculatedWithMaximumOperationsCount()
    {
        $entries = [];
        $mapping = [
            ['2019-01-01', 1, OperationsEnum::CASH_OUT, PersonEnum::NATURAL, '200.00', 'EUR'],
            ['2019-01-02', 1, OperationsEnum::CASH_OUT, PersonEnum::NATURAL, '200.00', 'EUR'],
            ['2019-01-03', 1, OperationsEnum::CASH_OUT, PersonEnum::NATURAL, '200.00', 'EUR'],
        ];

        foreach ($mapping as $entry) {
            $money = Money::of($entry[4], $entry[5]);
            $feeManager = $this->createFeeManager($money, $entry[2], $entry[3]);
            $feeManager->setUserId($entry[1]);
            $feeManager->setDate($entry[0]);
            $feeManager->setStaticEntries($entries);

            $this->assertEquals(0, $feeManager->calculateFee($money));
        }

        $money = Money::of('250.00', 'EUR');
        $feeManager = $this->createFeeManager($money, OperationsEnum::CASH_OUT, PersonEnum::NATURAL);
        $feeManager->setUserId(1);
        $feeManager->setDate('2019-01-04');
        $feeManager->setStaticEntries($entries);

        $this->assertEquals(
            '0.75',
            (string) $feeManager->calculateFee($money)
        );
    }

    public function testFeeWillBeCalculatedWhenAmountIsExceeded()
    {
        $entries = [];
        $money = Money::of('1250', 'EUR');
        $feeManager = $this->createFeeManager($money, OperationsEnum::CASH_OUT, PersonEnum::NATURAL);

        $feeManager->setUserId(1);
        $feeManager->setDate('2019-01-01');
        $feeManager->setStaticEntries($entries);

        $this->assertEquals(
            '0.75',
            (string) $feeManager->calculateFee($money)
        );
    }

    protected function createFeeManager(Money $money, $operation, $personType)
    {
        $feeManager = FeeFactory::factoryDefault($operation, $personType);
        $currencyManager = new CurrencyManager();
        $converter = $currencyManager->getCurrencyConverter();

        $feeManager->setBaseCurrency($money->getCurrency()->getCurrencyCode());
        $feeManager->setCurrencyConverter($converter);

        return $feeManager;
    }
}
