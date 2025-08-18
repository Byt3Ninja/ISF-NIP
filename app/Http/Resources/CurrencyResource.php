<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
          return [
            'id' => $this->id,
            'currencyCode' => $this->currency_code,
            'currencyName' => $this->currency_name,
            'currencySymbol' => $this->currency_symbol,
            'exchangeRateToUsd' => $this->exchange_rate_to_usd,
            'enabled' => (bool) $this->enabled,
            'flag' => $this->flag ? asset($this->flag) : null,
        ];
    }
}
