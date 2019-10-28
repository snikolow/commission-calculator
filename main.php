<?php

require_once './vendor/autoload.php';

use App\Calculator\FeeFactory;
use App\Calculator\Currency\CurrencyManager;
use App\Calculator\Operation\OperationManager;
use App\Calculator\Person\PersonManager;
use App\Enum\CurrencyEnum;
use App\Enum\MessagesEnum;
use App\Enum\OperationsEnum;
use App\Enum\PersonEnum;
use Brick\Money\Money;

// Contains the processed entry in the memory in order
// to process metadata like discounts.
$entries = [];

$operationManager = new OperationManager();
$personManager = new PersonManager();

$currencyManager = new CurrencyManager();
$currencyManager->addCurrencyRate(CurrencyEnum::EUR, CurrencyEnum::USD, 1.1497);
$currencyManager->addCurrencyRate(CurrencyEnum::EUR, CurrencyEnum::JPY, 129.53);

$converter = $currencyManager->getCurrencyConverter();

$configuration = [
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
            'maximum_discount_operations' => 1,
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

if (!isset($argc) || empty($argc) || $argc < 2) {
    print "Missing arguments!\n";

    return;
}

if (!file_exists($argv[1])) {
    print "Unable to find the source file!\n";

    return;
}

$table = array_map('str_getcsv', file($argv[1]));

foreach ($table as $row) {
    if (!$operationManager->isValidOperation($row[3])) {
        throw new \InvalidArgumentException(sprintf(MessagesEnum::INVALID_OPERATION, $row[3]));
    }

    if (!$personManager->isValidPerson($row[2])) {
        throw new \InvalidArgumentException(sprintf(MessagesEnum::INVALID_PERSON, $row[2]));
    }

    $money = Money::of($row[4], $row[5]);

    if (!$currencyManager->isValidCurrency($money->getCurrency()->getCurrencyCode())) {
        throw new \InvalidArgumentException(
            sprintf(MessagesEnum::INVALID_CURRENCY, $money->getCurrency()->getCurrencyCode())
        );
    }

    $feeManager = FeeFactory::factoryDefault($configuration, $row[3], $row[2]);
    $feeManager->setBaseCurrency($money->getCurrency()->getCurrencyCode());
    $feeManager->setCurrencyConverter($converter);
    $feeManager->setDate($row[0]);
    $feeManager->setUserId($row[1]);
    $feeManager->setStaticEntries($entries);

    print $feeManager->calculateFee($money) . "\n";
}
