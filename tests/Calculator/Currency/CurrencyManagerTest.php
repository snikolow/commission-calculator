<?php

namespace Tests\Calculator\Currency;

use App\Calculator\Currency\CurrencyManager;
use App\Contract\CurrencyInterface;
use App\Enum\CurrencyEnum;
use PHPUnit\Framework\TestCase;

/**
 * Class CurrencyManagerTest.
 *
 * @package Tests\Calculator\Currency
 */
class CurrencyManagerTest extends TestCase
{
    /**
     * The currency manager class.
     *
     * @var \App\Calculator\Currency\CurrencyManager
     */
    private $currencyManager;

    protected function setUp(): void
    {
        $this->currencyManager = new CurrencyManager();
    }

    public function testDefaultCurrenciesAreValid(): void
    {
        $list = [
            CurrencyEnum::EUR,
            CurrencyEnum::JPY,
            CurrencyEnum::USD,
        ];

        foreach ($list as $currencyCode) {
            $this->assertTrue($this->currencyManager->isValidCurrency($currencyCode));
        }
    }

    public function testNewCurrencyCanBeAddedAndValidated(): void
    {
        $reference = $this->currencyManager->addCurrency('BGN');

        $this->assertTrue($this->currencyManager->isValidCurrency('BGN'));
        $this->assertInstanceOf(CurrencyInterface::class, $reference);
    }

    public function testInvalidCurrencyIsNotValidated(): void
    {
        $this->assertFalse($this->currencyManager->isValidCurrency('BGN'));
    }
}
