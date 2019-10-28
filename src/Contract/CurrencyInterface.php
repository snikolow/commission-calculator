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
     * Registers new currency rate.
     *
     * @param string $fromCurrency
     *   The source currency.
     * @param string $toCurrency
     *   The target currency.
     * @param float $rate
     *   The rate amount.
     */
    public function addCurrencyRate(string $fromCurrency, string $toCurrency, float $rate): void;

    /**
     * Creates a currency converter manager.
     *
     * @return \Brick\Money\CurrencyConverter
     *   The converter class.
     */
    public function getCurrencyConverter(): CurrencyConverter;
}
