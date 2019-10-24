<?php

namespace App\Calculator;

use App\Calculator\Fee\CashIn\CashInDefaultFee;
use App\Calculator\Fee\CashOut\LegalFee;
use App\Calculator\Fee\CashOut\NaturalFee;
use App\Contract\FeeInterface;
use App\Enum\MessagesEnum;
use App\Enum\OperationsEnum;
use App\Enum\PersonEnum;

/**
 * Class FeeFactory.
 *
 * @package App\Calculator
 */
abstract class FeeFactory
{
    /**
     * Default factory implementation.
     *
     * @param string $operation
     *   The operation type, like "cash_in" or "cash_out".
     * @param string|null $personType
     *   The person type.
     *
     * @return \App\Contract\FeeInterface
     *   The fee instance based on the met conditions.
     */
    public static function factoryDefault(string $operation, string $personType = null): FeeInterface
    {
        switch ($operation) {
            case OperationsEnum::CASH_IN:
                return new CashInDefaultFee();

            case OperationsEnum::CASH_OUT:
                if (empty($personType)) {
                    throw new \InvalidArgumentException(MessagesEnum::EXCEPTION_MISSING_PERSON);
                }

                // Can be simplified with declaring separate method for handling
                // all cash-out implementations.
                if (PersonEnum::LEGAL === $personType) {
                    return new LegalFee();
                } elseif (PersonEnum::NATURAL === $personType) {
                    return new NaturalFee();
                }
        }

        throw new \RuntimeException(MessagesEnum::EXCEPTION_MISSING_IMPLEMENTATION);
    }
}
