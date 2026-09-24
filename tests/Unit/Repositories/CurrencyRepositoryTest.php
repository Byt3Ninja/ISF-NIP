<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\CurrencyRepository;
use App\Currency;

class CurrencyRepositoryTest extends TestCase
{

    protected CurrencyRepository $currencyRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencyRepository = new CurrencyRepository();
    }

    /** @test */
    public function it_can_get_enabled_currencies()
    {
        Currency::factory()->count(5)->create(['enabled' => true]);
        Currency::factory()->count(2)->create(['enabled' => false]);

        $result = $this->currencyRepository->getEnabled();

        $this->assertCount(5, $result);
        $result->each(function ($currency) {
            $this->assertTrue($currency->enabled);
        });
    }

    /** @test */
    public function it_can_paginate_currencies()
    {
        Currency::factory()->count(25)->create();

        $result = $this->currencyRepository->paginate(10);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->count());
        $this->assertEquals(25, $result->total());
    }

    /** @test */
    public function it_can_paginate_currencies_by_enabled_status()
    {
        Currency::factory()->count(5)->create(['enabled' => true]);
        Currency::factory()->count(3)->create(['enabled' => false]);

        $result = $this->currencyRepository->paginate(10, true);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $result->each(function ($currency) {
            $this->assertTrue($currency->enabled);
        });
    }

    /** @test */
    public function it_can_find_currency_by_id()
    {
        $currency = Currency::factory()->create();

        $result = $this->currencyRepository->find($currency->id);

        $this->assertInstanceOf(Currency::class, $result);
        $this->assertEquals($currency->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_currency_not_found()
    {
        $result = $this->currencyRepository->find(99999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_create_currency()
    {
        $data = [
            'currencyCode' => 'EUR',
            'currencyName' => 'Euro',
            'currencySymbol' => '€',
            'exchangeRateToUsd' => 1.1,
            'enabled' => true,
        ];

        $result = $this->currencyRepository->create($data);

        $this->assertInstanceOf(Currency::class, $result);
        $this->assertDatabaseHas('currencies', [
            'currency_code' => 'EUR',
            'currency_name' => 'Euro',
        ]);
    }

    /** @test */
    public function it_transforms_column_names_when_creating()
    {
        $data = [
            'currencyCode' => 'GBP',
            'currencyName' => 'British Pound',
            'exchangeRateToUsd' => 1.25,
        ];

        $result = $this->currencyRepository->create($data);

        $this->assertEquals('GBP', $result->currency_code);
        $this->assertEquals('British Pound', $result->currency_name);
        $this->assertEquals(1.25, $result->exchange_rate_to_usd);
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

        $result = $this->currencyRepository->update($currency, $updateData);

        $this->assertInstanceOf(Currency::class, $result);
        $this->assertEquals(1.15, $currency->fresh()->exchange_rate_to_usd);
    }

    /** @test */
    public function it_transforms_column_names_when_updating()
    {
        $currency = Currency::factory()->create();

        $updateData = [
            'currencyName' => 'Updated Name',
            'exchangeRateToUsd' => 1.2,
        ];

        $result = $this->currencyRepository->update($currency, $updateData);

        $this->assertEquals('Updated Name', $currency->fresh()->currency_name);
        $this->assertEquals(1.2, $currency->fresh()->exchange_rate_to_usd);
    }

    /** @test */
    public function it_ignores_null_values_when_updating()
    {
        $currency = Currency::factory()->create([
            'currency_name' => 'Original Name',
            'exchange_rate_to_usd' => 1.0,
        ]);

        $updateData = [
            'currencyName' => 'Updated Name',
            'exchangeRateToUsd' => null,
        ];

        $result = $this->currencyRepository->update($currency, $updateData);

        $this->assertEquals('Updated Name', $currency->fresh()->currency_name);
        $this->assertEquals(1.0, $currency->fresh()->exchange_rate_to_usd); // Unchanged
    }

    /** @test */
    public function it_can_delete_currency()
    {
        $currency = Currency::factory()->create();

        $result = $this->currencyRepository->delete($currency);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('currencies', ['id' => $currency->id]);
    }

    /** @test */
    public function it_can_get_all_currencies_except_usd()
    {
        Currency::factory()->create(['currency_code' => 'USD']);
        Currency::factory()->create(['currency_code' => 'EUR']);
        Currency::factory()->create(['currency_code' => 'GBP']);

        $result = $this->currencyRepository->getAllExceptUSD();

        $this->assertCount(2, $result);
        $result->each(function ($currency) {
            $this->assertNotEquals('USD', $currency->currency_code);
        });
    }
}

