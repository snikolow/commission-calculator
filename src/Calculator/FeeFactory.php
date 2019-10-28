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
     * @param array $configuration
     *   Contains different configuration entries for each fee plugin type.
     * @param string $operation
     *   The operation type, like "cash_in" or "cash_out".
     * @param string|null $personType
     *   The person type.
     *
     * @return \App\Contract\FeeInterface
     *   The fee instance based on the met conditions.
     */
    public static function factoryDefault(
        array $configuration,
        string $operation,
        string $personType = null
    ): FeeInterface {
        switch ($operation) {
            case OperationsEnum::CASH_IN:
                return static::createDefaultCashInPlugin($configuration);

            case OperationsEnum::CASH_OUT:
                if (empty($personType)) {
                    throw new \InvalidArgumentException(MessagesEnum::EXCEPTION_MISSING_PERSON);
                }

                // Can be simplified with declaring separate method for handling
                // all cash-out implementations.
                if (PersonEnum::LEGAL === $personType) {
                    return static::createLegalCashOutPlugin($configuration);
                } elseif (PersonEnum::NATURAL === $personType) {
                    return static::createNaturalCashOutPlugin($configuration);
                }
        }

        throw new \RuntimeException(MessagesEnum::EXCEPTION_MISSING_IMPLEMENTATION);
    }

    /**
     * Creates a plugin for calculating cash-in fees.
     *
     * @param array $configuration
     *   The configuration containing all settings required by the plugin.
     *
     * @return \App\Contract\FeeInterface
     *   The plugin instance.
     */
    protected static function createDefaultCashInPlugin(array $configuration): FeeInterface
    {
        $values = $configuration[OperationsEnum::CASH_IN];
        $plugin = new CashInDefaultFee();

        $plugin->setCommissionFee($values['commission_fee']);
        $plugin->setMaximumAmount($values['maximum_money']['amount']);
        $plugin->setCurrencyCode($values['maximum_money']['currency']);

        return $plugin;
    }

    /**
     * Creates a plugin for calculating cash-out legal fees.
     *
     * @param array $configuration
     *   The configuration containing all settings required by the plugin.
     *
     * @return \App\Contract\FeeInterface
     *   The plugin instance.
     */
    protected static function createLegalCashOutPlugin(array $configuration): FeeInterface
    {
        $values = $configuration[OperationsEnum::CASH_OUT][PersonEnum::LEGAL];
        $plugin = new LegalFee();

        $plugin->setCommissionFee($values['commission_fee']);
        $plugin->setMinimumAmount($values['minimum_money']['amount']);
        $plugin->setCurrencyCode($values['minimum_money']['currency']);

        return $plugin;
    }

    /**
     * Creates a plugin for calculating cash-out natural fees.
     *
     * @param array $configuration
     *   The configuration containing all settings required by the plugin.
     *
     * @return \App\Contract\FeeInterface
     *   The plugin instance.
     */
    protected static function createNaturalCashOutPlugin(array $configuration): FeeInterface
    {
        $values = $configuration[OperationsEnum::CASH_OUT][PersonEnum::NATURAL];
        $plugin = new NaturalFee();

        $plugin->setCommissionFee($values['commission_fee']);
        $plugin->setMaximumOperationsCount($values['maximum_discount_operations']);
        $plugin->setMaximumDiscountAmount($values['maximum_discount_money']['amount']);
        $plugin->setDiscountCurrencyCode($values['maximum_discount_money']['currency']);

        return $plugin;
    }
}
