<?php

namespace App\Calculator\Currency;

use App\Contract\CurrencyInterface;
use App\Enum\CurrencyEnum;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;

/**
 * Class CurrencyManager.
 *
 * @package App\Calculator\Currency
 */
class CurrencyManager implements CurrencyInterface
{
    /**
     * Contains a white-list with valid currencies.
     *
     * @var array
     */
    private $validCurrencies = [
        CurrencyEnum::EUR,
        CurrencyEnum::USD,
        CurrencyEnum::JPY,
    ];

    /**
     * Contains base definition for exchange rates.
     *
     * @var array
     */
    private $rates = [];

    /**
     * {@inheritdoc}
     */
    public function addCurrency(string $currencyCode): CurrencyInterface
    {
        $currencyCode = strtoupper($currencyCode);

        if (!in_array($currencyCode, $this->validCurrencies)) {
            $this->validCurrencies[] = $currencyCode;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidCurrency(string $currencyCode): bool
    {
        $currencyCode = strtoupper($currencyCode);

        return in_array($currencyCode, $this->validCurrencies);
    }

    /**
     * {@inheritdoc}
     */
    public function addCurrencyRate(string $fromCurrency, string $toCurrency, float $rate): void
    {
        $this->rates[] = [
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'rate' => $rate,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyConverter(): CurrencyConverter
    {
        $provider = new ConfigurableProvider();

        foreach ($this->rates as $entry) {
            // Create base pair.
            $provider->setExchangeRate($entry['from'], $entry['to'], $entry['rate']);
            // Create reverse pair.
            $provider->setExchangeRate(
                $entry['to'],
                $entry['from'],
                (1 / $entry['rate'])
            );
        }

        return new CurrencyConverter($provider);
    }
}
