<?php

namespace App\Calculator\Fee;

use App\Contract\FeeInterface;
use App\Enum\CurrencyEnum;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\Money;

/**
 * Class AbstractFee.
 *
 * @package App\Calculator\Fee
 */
abstract class AbstractFee implements FeeInterface
{
    /**
     * The currency converter manager.
     *
     * @var \Brick\Money\CurrencyConverter
     */
    protected $currencyConverter;

    /**
     * The currency code of the initial amount.
     *
     * @var string
     */
    protected $baseCurrency;

    /**
     * The date of the currently processed item.
     *
     * @var string
     */
    protected $currentDate;

    /**
     * The user ID of the currently processed item.
     *
     * @var int
     */
    protected $currentUserId;

    /**
     * Contains processed metadata for natural person types.
     *
     * @var array
     */
    protected $staticEntries = [];

    /**
     * {@inheritdoc}
     */
    public function setCurrencyConverter(CurrencyConverter $currencyConverter): void
    {
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCurrency(string $currencyCode): void
    {
        $this->baseCurrency = $currencyCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setDate(string $date): void
    {
        $this->currentDate = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId(string $id): void
    {
        $this->currentUserId = (int) $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setStaticEntries(array &$entries): void
    {
        $this->staticEntries =& $entries;
    }

    /**
     * Returns the money object in Euro.
     *
     * @param \Brick\Money\Money $money
     *   The money object, along with the currency.
     * @param int $roundingMode
     *   The amount rounding mode.
     *
     * @return \Brick\Money\Money
     *   Either the converted object or the original one.
     *
     * @throws \Brick\Money\Exception\CurrencyConversionException
     *   If rounding is necessary and RoundingMode::UNNECESSARY is used.
     */
    protected function getMoneyInEuro(Money $money, int $roundingMode = RoundingMode::DOWN): Money
    {
        if (CurrencyEnum::EUR !== $this->baseCurrency) {
            $money = $this->currencyConverter
                ->convert($money, CurrencyEnum::EUR, $roundingMode);
        }

        return $money;
    }

    /**
     * Returns the money object in original currency.
     *
     * @param \Brick\Money\Money $money
     *   The money object, along with the currency.
     * @param int $roundingMode
     *   The amount rounding mode.
     *
     * @return \Brick\Money\Money
     *   Either the converted object or the original one.
     *
     * @throws \Brick\Money\Exception\CurrencyConversionException
     *   If rounding is necessary and RoundingMode::UNNECESSARY is used.
     */
    protected function getMoneyInBaseCurrency(Money $money, int $roundingMode = RoundingMode::DOWN): Money
    {
        return $this->currencyConverter
            ->convert($money, $this->baseCurrency, $roundingMode);
    }
}
