<?php

namespace App\Calculator\Fee\CashOut;

use App\Calculator\Fee\AbstractFee;
use App\Contract\Fee\NaturalFeeInterface;
use App\Enum\CurrencyEnum;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\Carbon;

/**
 * Class NaturalFee.
 *
 * @package App\Calculator\Fee\CashOut
 */
class NaturalFee extends AbstractFee implements NaturalFeeInterface
{
    /**
     * Indicates whether or not the calculate operation should be free of charge.
     *
     * @var bool
     */
    private $freeOfCharge = false;

    /**
     * The commission fee.
     *
     * @var float
     */
    private $commissionFee = 0.3;

    /**
     * The maximum discount amount.
     *
     * @var float
     */
    private $discountAmount = 1000.00;

    /**
     * The discount currency code.
     *
     * @var string
     */
    private $discountCurrency = 'EUR';

    /**
     * Maximum operations before starting to apply full charges.
     *
     * @var int
     */
    private $operationsCount = 3;

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
    public function setMaximumDiscountAmount(float $amount): void
    {
        $this->discountAmount = $amount;
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountCurrencyCode(string $currency): void
    {
        $this->discountCurrency = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaximumOperationsCount(int $number): void
    {
        $this->operationsCount = $number;
    }

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
            ->multipliedBy($this->commissionFee, RoundingMode::UP)
            ->dividedBy(100, RoundingMode::UP);

        if (CurrencyEnum::EUR !== $this->baseCurrency) {
            $money = $this->getMoneyInBaseCurrency($money, RoundingMode::DOWN);
        }

        return $money->getAmount();
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
                'remaining_discount' => Money::of($this->discountAmount, $this->discountCurrency),
            ];
        }

        /** @var \Brick\Money\Money $currentRemainingDiscount */
        $currentRemainingDiscount = $this->staticEntries[$entryKey]['remaining_discount'];
        $currentOperationsCount = $this->staticEntries[$entryKey]['operations_count'];

        if ($currentOperationsCount >= $this->operationsCount) {
            // We have exceeded the 3 discount operations,
            // so proceed with default calculations.
            $this->freeOfCharge = false;
        } elseif ($currentOperationsCount < $this->operationsCount
            && $money->isEqualTo($currentRemainingDiscount)
        ) {
            // We have requested the exact amount of money as the one
            // defined in the maximum discount amount. In this case
            // we proceed with no additional fees, but the discount
            // will no longer be active for the entire week as well.
            $currentRemainingDiscount = Money::of('0', 'EUR');
            $this->freeOfCharge = true;
        } elseif ($currentOperationsCount < $this->operationsCount
            && $currentRemainingDiscount->isGreaterThan(0)
            && $currentRemainingDiscount->isLessThan($money)
        ) {
            // We have requested an amount which exceeds the available
            // discount amount. Subtract the remaining discount from
            // the requested amount and calculate the fees on top of it.
            $money = $money->minus($currentRemainingDiscount);
            $currentRemainingDiscount = Money::of('0', 'EUR');
        } elseif ($currentOperationsCount < $this->operationsCount
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
        Carbon::setWeekStartsAt($this->getFirstDayOfWeek());
        Carbon::setWeekEndsAt($this->getLastDayOfWeek());

        /** @var Carbon $carbon */
        $carbon = Carbon::createFromFormat($this->getDateFormatType(), $this->currentDate);

        // Creates a carbon object from given date and then the same object
        // is used to obtain the first and last day of the week for the
        // given date. By doing this, we can create a range of dates,
        // combined with the unique identifier of the user (UserID),
        // we ensure to cover the case of 2 dates on the same week, but
        // with overlapping years.
        return md5(
            sprintf(
                '%s#%s#%s',
                $carbon->startOfWeek()->format($this->getDateFormatType()),
                $carbon->endOfWeek()->format($this->getDateFormatType()),
                $this->currentUserId
            )
        );
    }
}
