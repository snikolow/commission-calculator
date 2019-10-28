<?php

namespace App\Contract\Fee;

use App\Contract\FeeInterface;

/**
 * Interface LegalFeeInterface.
 *
 * @package App\Contract\Fee
 */
interface LegalFeeInterface extends FeeInterface
{
    /**
     * Sets the commission fee to be used when calculating.
     *
     * @param float $fee
     *   The fee amount.
     */
    public function setCommissionFee(float $fee): void;

    /**
     * Sets the minimum amount to be charged.
     *
     * @param float $amount
     *   The amount.
     */
    public function setMinimumAmount(float $amount): void;

    /**
     * Sets the currency code when creating the money object.
     *
     * @param string $currency
     *   The currency code.
     */
    public function setCurrencyCode(string $currency): void;
}
