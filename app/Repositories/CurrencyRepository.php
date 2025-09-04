<?php
namespace App\Repositories;

use App\Currency;

class CurrencyRepository
{
    protected array $columnMap = [
        'currencyCode' => 'currency_code',
        'currencyName' => 'currency_name',
        'currencySymbol' => 'currency_symbol',
        'exchangeRateToUsd' => 'exchange_rate_to_usd',
        'enabled' => 'enabled',
        'flag' => 'flag',
        'last_updated' => 'last_updated',
    ];
    public function getEnabled()
    {
        return Currency::where('enabled', true)->get();
    }

    public function paginate($limit = 10, $enabled = null)
    {
        $query = Currency::query();

        if (!is_null($enabled)) {
            $query->where('enabled', $enabled);
        }

        return $query->orderByDesc('last_updated')->paginate($limit);
    }

    public function find($id)
    {
        return Currency::find($id);
    }

 public function create(array $data): Currency
    {
        $currency = new Currency();
        $currency->fill($this->transformToDbColumns($data));
        $currency->save();

        return $currency->fresh();
    }

    public function update(Currency $currency, array $data): Currency
    {
        $data = $this->transformToDbColumns($data);

        foreach ($data as $key => $value) {
            if (!is_null($value)) {
                $currency->{$key} = $value;
            }
        }

        $currency->save();

        return $currency->fresh();
    }

    public function delete(Currency $currency)
    {
        return $currency->delete();
    }

    public function getAllExceptUSD()
    {
        return Currency::where('currency_code', '!=', 'USD')->get();
    }

     protected function transformToDbColumns(array $data): array
{
    $transformed = [];

    foreach ($data as $key => $value) {
        if (isset($this->columnMap[$key])) {
            $transformed[$this->columnMap[$key]] = $value;
        } else {
            $transformed[$key] = $value;
        }
    }

    return $transformed;
}

}