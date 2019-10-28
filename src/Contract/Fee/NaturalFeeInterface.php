<?php

namespace App\Contract\Fee;

use App\Contract\FeeInterface;

/**
 * Interface NaturalFeeInterface.
 *
 * @package App\Contract\Fee
 */
interface NaturalFeeInterface extends FeeInterface
{
    /**
     * Sets the commission fee to be used when calculating.
     *
     * @param float $fee
     *   The fee amount.
     */
    public function setCommissionFee(float $fee): void;

    /**
     * Sets the maximum amount of discount to be used before calculating fees.
     *
     * @param float $amount
     *   The amount.
     */
    public function setMaximumDiscountAmount(float $amount): void;

    /**
     * Sets the currency code when creating the money object for discounts.
     *
     * @param string $currency
     *   The currency code.
     */
    public function setDiscountCurrencyCode(string $currency): void;

    /**
     * Sets the amount of operation before starting to apply full fee charges.
     *
     * @param int $number
     *   The number of allowed operations.
     */
    public function setMaximumOperationsCount(int $number): void;
}
