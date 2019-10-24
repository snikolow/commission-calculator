<?php

require_once './vendor/autoload.php';

use App\Calculator\FeeFactory;
use App\Calculator\Currency\CurrencyManager;
use App\Calculator\Operation\OperationManager;
use App\Calculator\Person\PersonManager;
use App\Enum\MessagesEnum;
use Brick\Money\Money;

// Contains the processed entry in the memory in order
// to process metadata like discounts.
$entries = [];

$operationManager = new OperationManager();
$currencyManager = new CurrencyManager();
$personManager = new PersonManager();
$converter = $currencyManager->getCurrencyConverter();

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

    $feeManager = FeeFactory::factoryDefault($row[3], $row[2]);
    $feeManager->setBaseCurrency($money->getCurrency()->getCurrencyCode());
    $feeManager->setCurrencyConverter($converter);
    $feeManager->setDate($row[0]);
    $feeManager->setUserId($row[1]);
    $feeManager->setStaticEntries($entries);

    print $feeManager->calculateFee($money) . "\n";
}
