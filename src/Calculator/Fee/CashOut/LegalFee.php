<?php

namespace App\Calculator\Fee\CashOut;

use App\Calculator\Fee\AbstractFee;
use App\Contract\Fee\LegalFeeInterface;
use App\Enum\CurrencyEnum;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * Class LegalFee.
 *
 * @package App\Calculator\Fee\CashOut
 */
class LegalFee extends AbstractFee implements LegalFeeInterface
{
    /**
     * The commission fee.
     *
     * @var float
     */
    private $commissionFee = 0.3;

    /**
     * The minimum amount to be charged.
     *
     * @var float
     */
    private $minimumAmount = 0.50;

    /**
     * The amount currency code.
     *
     * @var string
     */
    private $minimumAmountCurrency = 'EUR';

    /**
     * {@inheritdoc}
     */
    public function setCommissionFee(float $fee): void
    {
        $this->commissionFee = $fee;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinimumAmount(float $amount): void
    {
        $this->minimumAmount = $amount;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode(string $currency): void
    {
        $this->minimumAmountCurrency = $currency;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Brick\Money\Exception\CurrencyConversionException
     *   If the exchange rate is not available.
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     *   If rounding is necessary and RoundingMode::UNNECESSARY is used.
     * @throws \Brick\Money\Exception\MoneyMismatchException
     *   If the argument is a money in a different currency.
     */
    public function calculateFee(Money $money)
    {
        $money = $this
            ->getMoneyInEuro($money)
            ->multipliedBy($this->commissionFee, RoundingMode::DOWN)
            ->dividedBy(100, RoundingMode::DOWN);

        // Compare the original/converted amount in euro to the one
        // specified as "maximum" amount of 5 euros.
        // If the calculated amount exceeds the limit, return the
        // limit instead.
        if ($money->isLessThan($this->getMinimumMoney())) {
            return $this->getMinimumMoney()
                ->getAmount();
        }

        // Restore the money object to it's original currency,
        // in case it was not euro.
        if (CurrencyEnum::EUR !== $this->baseCurrency) {
            $money = $this->getMoneyInBaseCurrency($money, RoundingMode::UP);
        }

        return $money->getAmount();
    }

    /**
     * Returns the maximum amount in euro that can be applied as a fee.
     *
     * @return \Brick\Money\Money
     *   The money object, along with the currency.
     */
    protected function getMinimumMoney(): Money
    {
        return Money::of($this->minimumAmount, $this->minimumAmountCurrency);
    }
}
