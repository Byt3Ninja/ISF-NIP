<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CurrencyService;
use App\Repositories\CurrencyRepository;
use App\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Mockery;

class CurrencyServiceTest extends TestCase
{

    protected CurrencyService $currencyService;
    protected $currencyRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->currencyRepoMock = Mockery::mock(CurrencyRepository::class);
        $this->currencyService = new CurrencyService($this->currencyRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_enabled_currencies()
    {
        $currencies = Currency::factory()->count(5)->create(['enabled' => true]);
        Currency::factory()->count(2)->create(['enabled' => false]);

        $this->currencyRepoMock
            ->shouldReceive('getEnabled')
            ->once()
            ->andReturn($currencies);

        $result = $this->currencyService->getEnabledCurrencies();

        $this->assertCount(5, $result);
    }

    /** @test */
    public function it_can_paginate_currencies()
    {
        $paginated = Currency::factory()->count(15)->create();

        $this->currencyRepoMock
            ->shouldReceive('paginate')
            ->once()
            ->with(10, null)
            ->andReturn($paginated);

        $result = $this->currencyService->paginateCurrencies(10);

        $this->assertIsIterable($result);
    }

    /** @test */
    public function it_can_paginate_currencies_by_enabled_status()
    {
        $enabledCurrencies = Currency::factory()->count(5)->create(['enabled' => true]);

        $this->currencyRepoMock
            ->shouldReceive('paginate')
            ->once()
            ->with(10, true)
            ->andReturn($enabledCurrencies);

        $result = $this->currencyService->paginateCurrencies(10, true);

        $this->assertIsIterable($result);
    }

    /** @test */
    public function it_can_store_currency_with_flag()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('flag.jpg');
        $currencyData = [
            'currencyCode' => 'EUR',
            'currencyName' => 'Euro',
            'currencySymbol' => '€',
            'exchangeRateToUsd' => 1.1,
            'enabled' => true,
        ];

        $createdCurrency = Currency::factory()->make($currencyData);
        $createdCurrency->id = 1;

        $this->currencyRepoMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($currencyData) {
                return $data['currencyCode'] === $currencyData['currencyCode'] 
                    && isset($data['flag']);
            }))
            ->andReturn($createdCurrency);

        $result = $this->currencyService->store($currencyData, $file);

        $this->assertInstanceOf(Currency::class, $result);
    }

    /** @test */
    public function it_can_store_currency_without_flag()
    {
        $currencyData = [
            'currencyCode' => 'GBP',
            'currencyName' => 'British Pound',
            'currencySymbol' => '£',
            'exchangeRateToUsd' => 1.25,
            'enabled' => true,
        ];

        $createdCurrency = Currency::factory()->make($currencyData);

        $this->currencyRepoMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return !isset($data['flag']);
            }))
            ->andReturn($createdCurrency);

        $result = $this->currencyService->store($currencyData, null);

        $this->assertInstanceOf(Currency::class, $result);
    }

    /** @test */
    public function it_can_update_currency()
    {
        $currency = Currency::factory()->create([
            'currency_code' => 'EUR',
            'exchange_rate_to_usd' => 1.0,
        ]);

        $updateData = [
            'exchangeRateToUsd' => 1.15,
        ];

        $updatedCurrency = $currency->replicate();
        $updatedCurrency->exchange_rate_to_usd = 1.15;

        $this->currencyRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($currency, Mockery::on(function ($data) {
                return isset($data['last_updated']) 
                    && $data['exchangeRateToUsd'] === 1.15;
            }))
            ->andReturn($updatedCurrency);

        $result = $this->currencyService->update($currency, $updateData, null);

        $this->assertInstanceOf(Currency::class, $result);
    }

    /** @test */
    public function it_can_update_currency_with_new_flag()
    {
        Storage::fake('public');
        
        $currency = Currency::factory()->create(['flag' => 'old-flag.jpg']);
        $file = UploadedFile::fake()->image('new-flag.jpg');

        $updateData = ['currencyName' => 'Updated Name'];

        $updatedCurrency = $currency->replicate();
        $updatedCurrency->flag = 'icons/flags/new-flag.jpg';

        $this->currencyRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($currency, Mockery::on(function ($data) {
                return isset($data['flag']) && isset($data['last_updated']);
            }))
            ->andReturn($updatedCurrency);

        $result = $this->currencyService->update($currency, $updateData, $file);

        $this->assertInstanceOf(Currency::class, $result);
    }

    /** @test */
    public function it_can_delete_currency()
    {
        $currency = Currency::factory()->create();

        $this->currencyRepoMock
            ->shouldReceive('delete')
            ->once()
            ->with($currency)
            ->andReturn(true);

        $result = $this->currencyService->delete($currency);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_update_exchange_rates()
    {
        Http::fake([
            'floatrates.com/*' => Http::response([
                'eur' => ['rate' => 0.85],
                'gbp' => ['rate' => 0.75],
            ], 200),
        ]);

        $eur = Currency::factory()->create(['currency_code' => 'EUR', 'exchange_rate_to_usd' => 1.0]);
        $gbp = Currency::factory()->create(['currency_code' => 'GBP', 'exchange_rate_to_usd' => 1.0]);
        Currency::factory()->create(['currency_code' => 'USD', 'exchange_rate_to_usd' => 1.0]);

        $this->currencyRepoMock
            ->shouldReceive('getAllExceptUSD')
            ->once()
            ->andReturn(collect([$eur, $gbp]));

        $result = $this->currencyService->updateExchangeRates();

        $this->assertTrue($result);
        $this->assertEquals(0.85, $eur->fresh()->exchange_rate_to_usd);
        $this->assertEquals(0.75, $gbp->fresh()->exchange_rate_to_usd);
    }

    /** @test */
    public function it_handles_exchange_rate_api_failure()
    {
        Http::fake([
            'floatrates.com/*' => Http::response([], 500),
        ]);

        $result = $this->currencyService->updateExchangeRates();

        $this->assertFalse($result);
    }

    /** @test */
    public function it_uploads_flag_correctly()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('test-flag.png');
        
        $reflection = new \ReflectionClass($this->currencyService);
        $method = $reflection->getMethod('uploadFlag');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->currencyService, $file);

        $this->assertStringContainsString('icons/flags/', $result);
        $this->assertStringContainsString('.png', $result);
    }
}

