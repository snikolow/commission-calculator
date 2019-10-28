<?php

namespace App\Contract\Fee;

use App\Contract\FeeInterface;

/**
 * Interface CashInFeeInterface.
 *
 * @package App\Contract\Fee
 */
interface CashInFeeInterface extends FeeInterface
{
    /**
     * Sets the commission fee to be used when calculating.
     *
     * @param float $fee
     *   The fee amount.
     */
    public function setCommissionFee(float $fee): void;

    /**
     * Sets the maximum amount to be charged.
     *
     * @param float $amount
     *   The amount.
     */
    public function setMaximumAmount(float $amount): void;

    /**
     * Sets the currency code when creating the money object.
     *
     * @param string $currency
     *   The currency code.
     */
    public function setCurrencyCode(string $currency): void;
}
