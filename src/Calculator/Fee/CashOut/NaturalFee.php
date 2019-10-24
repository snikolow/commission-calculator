<?php

namespace App\Calculator\Fee\CashOut;

use App\Calculator\Fee\AbstractFee;
use App\Enum\CurrencyEnum;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * Class NaturalFee.
 *
 * @package App\Calculator\Fee\CashOut
 */
class NaturalFee extends AbstractFee
{
    private $freeOfCharge = false;

    /**
     * {@inheritdoc}
     *
     * @throws \Brick\Money\Exception\CurrencyConversionException
     *   If the exchange rate is not available.
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     *   If rounding is necessary and RoundingMode::UNNECESSARY is used.
     * @throws \Exception
     *   Emits Exception in case of an error.
     */
    public function calculateFee(Money $money)
    {
        // Convert the money object to one with currency of euro,
        // if the specified currency is different.
        $money = $this->getMoneyInEuro($money);
        // Process the custom logic for fees applied to "natural" person types
        // based on the additional metadata specified.
        $money = $this->processDiscounts($money);

        if ($this->freeOfCharge) {
            return 0;
        }

        $money = $money
            ->multipliedBy($this->getCommissionFee(), RoundingMode::UP)
            ->dividedBy(100, RoundingMode::UP);

        if (CurrencyEnum::EUR !== $this->baseCurrency) {
            $money = $this->getMoneyInBaseCurrency($money, RoundingMode::DOWN);
        }

        return $money->getAmount();
    }

    /**
     * Returns the applied commission fee percentage
     *
     * @return float
     *   The applied percentage.
     */
    protected function getCommissionFee(): float
    {
        return 0.3;
    }

    /**
     * Returns the maximum applicable discount amount.
     *
     * The "money" class is flexible enough to work well
     * with strings, decimals, big numbers and so on.
     *
     * @return string
     *   The discount amount.
     */
    protected function getMaximumDiscountAmount(): string
    {
        return '1000.00';
    }

    /**
     * Returns the amount of operations before denying any further discounts.
     *
     * @return int
     *   The amount of operations.
     */
    protected function getMaximumOperationsCount(): int
    {
        return 3;
    }

    /**
     * Applies discounts if certain conditions are met.
     *
     * @param \Brick\Money\Money $money
     *   The money object, along with the currency converted in euro.
     *
     * @return \Brick\Money\Money
     *   The money object with conditional discounts applied.
     *
     * @throws \Exception
     *   Emits Exception in case of an error.
     */
    protected function processDiscounts(Money $money): Money
    {
        $entryKey = $this->generateEntryKey();

        if (!isset($this->staticEntries[$entryKey])) {
            $this->staticEntries[$entryKey] = [
                'operations_count' => 0,
                'remaining_discount' => Money::of($this->getMaximumDiscountAmount(), 'EUR'),
            ];
        }

        /** @var \Brick\Money\Money $currentRemainingDiscount */
        $currentRemainingDiscount = $this->staticEntries[$entryKey]['remaining_discount'];
        $currentOperationsCount = $this->staticEntries[$entryKey]['operations_count'];

        if ($currentOperationsCount >= $this->getMaximumOperationsCount()) {
            // We have exceeded the 3 discount operations,
            // so proceed with default calculations.
            $this->freeOfCharge = false;
        } elseif ($currentOperationsCount < $this->getMaximumOperationsCount()
            && $money->isEqualTo($currentRemainingDiscount)
        ) {
            // We have requested the exact amount of money as the one
            // defined in the maximum discount amount. In this case
            // we proceed with no additional fees, but the discount
            // will no longer be active for the entire week as well.
            $currentRemainingDiscount = Money::of('0', 'EUR');
            $this->freeOfCharge = true;
        } elseif ($currentOperationsCount < $this->getMaximumOperationsCount()
            && $currentRemainingDiscount->isGreaterThan(0)
            && $currentRemainingDiscount->isLessThan($money)
        ) {
            // We have requested an amount which exceeds the available
            // discount amount. Subtract the remaining discount from
            // the requested amount and calculate the fees on top of it.
            $money = $money->minus($currentRemainingDiscount);
            $currentRemainingDiscount = Money::of('0', 'EUR');
        } elseif ($currentOperationsCount < $this->getMaximumOperationsCount()
            && $currentRemainingDiscount->isGreaterThan(0)
            && $currentRemainingDiscount->isGreaterThan($money)
        ) {
            // We have requested an amount which can be covered by the
            // remaining discount amount. Subtract the amount from the
            // discount and proceed with no fee charges.
            $currentRemainingDiscount = $currentRemainingDiscount->minus($money);
            $this->freeOfCharge = true;
        }

        $this->staticEntries[$entryKey]['operations_count'] += 1;
        $this->staticEntries[$entryKey]['remaining_discount'] = $currentRemainingDiscount;

        return $money;
    }

    /**
     * Generates a key used to store metadata for the current user.
     *
     * @return string
     *   The generated key.
     *
     * @throws \Exception
     *   Emits Exception in case of an error.
     */
    protected function generateEntryKey(): string
    {
        $dateTime = new \DateTime($this->currentDate);
        $weekNumber = $dateTime->format('W');
        $year = $dateTime->format('Y');

        return md5(sprintf('%s-%s-%s', $year, $weekNumber, $this->currentUserId));
    }
}
