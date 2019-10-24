<?php

namespace App\Contract;

use Brick\Money\CurrencyConverter;

/**
 * Interface CurrencyInterface.
 *
 * @package App\Contract
 */
interface CurrencyInterface
{
    /**
     * Adds new currency type to the white-list.
     *
     * @param string $currencyCode
     *   The currency type.
     *
     * @return \App\Contract\CurrencyInterface
     *   The instance of the class.
     */
    public function addCurrency(string $currencyCode): CurrencyInterface;

    /**
     * Indicates whether or not given currency is valid.
     *
     * @param string $currencyCode
     *   The currency type.
     *
     * @return boolean
     *   The validation result.
     */
    public function isValidCurrency(string $currencyCode): bool;

    /**
     * Creates a currency converter manager.
     *
     * @return \Brick\Money\CurrencyConverter
     *   The converter class.
     */
    public function getCurrencyConverter(): CurrencyConverter;
}
