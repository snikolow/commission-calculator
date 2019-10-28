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
    private $configuration = [];

    protected function setUp(): void
    {
        $this->configuration = [
            OperationsEnum::CASH_IN => [
                'commission_fee' => 0.03,
                'maximum_money' => [
                    'amount' => 5.00,
                    'currency' => 'EUR',
                ],
            ],
            OperationsEnum::CASH_OUT => [
                PersonEnum::NATURAL => [
                    'commission_fee' => 0.3,
                    'maximum_discount_money' => [
                        'amount' => 1000.00,
                        'currency' => 'EUR',
                    ],
                    'maximum_discount_operations' => 3,
                ],
                PersonEnum::LEGAL => [
                    'commission_fee' => 0.3,
                    'minimum_money' => [
                        'amount' => 0.50,
                        'currency' => 'EUR',
                    ],
                ],
            ],
        ];
    }

    public function testFeeFactoryImplementationsAreValid(): void
    {
        $feeManager = FeeFactory::factoryDefault($this->configuration, OperationsEnum::CASH_IN);

        $this->assertInstanceOf(CashInDefaultFee::class, $feeManager);

        $feeManager = FeeFactory::factoryDefault($this->configuration, OperationsEnum::CASH_OUT, PersonEnum::NATURAL);

        $this->assertInstanceOf(NaturalFee::class, $feeManager);

        $feeManager = FeeFactory::factoryDefault($this->configuration, OperationsEnum::CASH_OUT, PersonEnum::LEGAL);

        $this->assertInstanceOf(LegalFee::class, $feeManager);
    }

    public function testExceptionWillBeThrownWithInvalidArguments(): void
    {
        try {
            $feeManager = FeeFactory::factoryDefault($this->configuration, 'transfer', 'institution');
        } catch(\RuntimeException $ex) {
            $this->assertEquals($ex->getMessage(), MessagesEnum::EXCEPTION_MISSING_IMPLEMENTATION);
        }

        $this->expectException(\RuntimeException::class);
        $feeManager = FeeFactory::factoryDefault($this->configuration, 'transfer', 'institution');
    }

    public function testExceptionWillBeThrownForCashOutAndMissingPerson()
    {
        try {
            $feeManager = FeeFactory::factoryDefault($this->configuration, OperationsEnum::CASH_OUT);
        } catch(\InvalidArgumentException $ex) {
            $this->assertEquals($ex->getMessage(), MessagesEnum::EXCEPTION_MISSING_PERSON);
        }

        $this->expectException(\InvalidArgumentException::class);
        $feeManager = FeeFactory::factoryDefault($this->configuration, OperationsEnum::CASH_OUT);
    }

    public function testFeeWontBeCalculatedForSingleOperation()
    {
        $money = Money::of('1000.00', 'EUR');
        $feeManager = $this->createFeeManager($money, OperationsEnum::CASH_OUT, PersonEnum::NATURAL);

        $feeManager->setUserId(1);
        $feeManager->setDate('2019-01-01');

        $this->assertEquals(0, $feeManager->calculateFee($money));
    }

    public function testFeeWontBeCalculatedForThreeOperationsInEuro()
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
            $feeManager->setUserId($entry[1]);
            $feeManager->setDate($entry[0]);

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
        $feeManager = FeeFactory::factoryDefault($this->configuration, $operation, $personType);
        $currencyManager = new CurrencyManager();
        $converter = $currencyManager->getCurrencyConverter();

        $feeManager->setBaseCurrency($money->getCurrency()->getCurrencyCode());
        $feeManager->setCurrencyConverter($converter);

        return $feeManager;
    }
}
