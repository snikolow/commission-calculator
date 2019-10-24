<?php

namespace App\Contract;

use Brick\Money\CurrencyConverter;
use Brick\Money\Money;

/**
 * Interface FeeInterface.
 *
 * @package App\Contract
 */
interface FeeInterface
{
    /**
     * Calculates the fee based on the provided amount.
     *
     * @param \Brick\Money\Money $money
     *   The money object, along with the currency.
     *
     * @return mixed
     *   The calculated fee.
     */
    public function calculateFee(Money $money);

    /**
     * Sets the currency converter.
     *
     * @param \Brick\Money\CurrencyConverter $currencyConverter
     *   The currency converter manager.
     */
    public function setCurrencyConverter(CurrencyConverter $currencyConverter): void;

    /**
     * Sets the base currency code, used for final conversion if needed.
     *
     * @param string $currencyCode
     *   The currency code for the initial amount.
     */
    public function setBaseCurrency(string $currencyCode): void;

    /**
     * Sets the date of the currently processed item.
     *
     * @param string $date
     *   The date of the entry.
     */
    public function setDate(string $date): void;

    /**
     * Sets the user ID of the currently processed item.
     *
     * @param string $id
     *   The user id of the entry.
     */
    public function setUserId(string $id): void;

    /**
     * Sets the reference for already processed entries.
     *
     * @param array $entries
     *   Contains metadata for processed entries.
     */
    public function setStaticEntries(array &$entries): void;
}
