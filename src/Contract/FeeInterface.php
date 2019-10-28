<?php

namespace App\Contract;

use Brick\Money\CurrencyConverter;
use Brick\Money\Money;
use Carbon\Carbon;

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
     * Sets the first day of the week.
     *
     * Used when formatting the date object in order to return
     * the first day for given week, based on a date input.
     *
     * @see \Carbon\Carbon::setWeekStartsAt() for more information
     * although the method is marked as deprecated, but compliant.
     *
     * @param int $day
     *   The week day.
     */
    public function setFirstDayOfWeek(int $day = Carbon::MONDAY): void;

    /**
     * Sets the last day of the week.
     *
     * Used when formatting the date object in order to return
     * the last day for given week, based on a date input.
     *
     * @see \Carbon\Carbon::setWeekEndsAt() for more information
     * although the method is marked as deprecated, but compliant.
     *
     * @param int $day
     *   The week day.
     */
    public function setLastDayOfWeek(int $day = Carbon::SUNDAY): void;

    /**
     * Sets the type of date formatting we will use when dealing with dates.
     *
     * @param string $format
     *   The date format.
     */
    public function setDateFormatType(string $format = 'Y-m-d'): void;

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
