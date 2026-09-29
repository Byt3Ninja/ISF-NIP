<?php

namespace App\Services\Importer;

use App\Country;
use App\Sector;
use App\State;

/**
 * Pre-loaded reference data for row validation.
 * Build once per validation run — never per row (spec requires no N+1).
 */
class ValidationContext
{
    public readonly array $countryNames;        // lowercased => country id
    public readonly array $stateNamesByCountry; // "country_id:state_name_lower" => state id
    public readonly array $sectorNames;         // lowercased => sector id
    public readonly array $currencyCodes;       // uppercased codes set (values are true)

    public function __construct()
    {
        $this->countryNames = $this->buildCountryMap();
        $this->stateNamesByCountry = $this->buildStateMap();
        $this->sectorNames = $this->buildSectorMap();
        $this->currencyCodes = $this->buildCurrencyMap();
    }

    private function buildCountryMap(): array
    {
        $out = [];
        Country::query()->select(['id', 'name_en', 'name_ar'])->chunk(500, function ($rows) use (&$out) {
            foreach ($rows as $c) {
                if (!empty($c->name_en)) {
                    $out[mb_strtolower(trim($c->name_en), 'UTF-8')] = $c->id;
                }
                if (!empty($c->name_ar)) {
                    $out[mb_strtolower(trim($c->name_ar), 'UTF-8')] = $c->id;
                }
            }
        });
        return $out;
    }

    private function buildStateMap(): array
    {
        $out = [];
        State::query()->select(['id', 'country_id', 'name_en', 'name_ar'])->chunk(1000, function ($rows) use (&$out) {
            foreach ($rows as $s) {
                foreach ([$s->name_en, $s->name_ar] as $nm) {
                    if (empty($nm)) continue;
                    $out[$s->country_id . ':' . mb_strtolower(trim($nm), 'UTF-8')] = $s->id;
                }
            }
        });
        return $out;
    }

    private function buildSectorMap(): array
    {
        $out = [];
        Sector::query()->select(['id', 'name_en', 'name_ar'])->chunk(500, function ($rows) use (&$out) {
            foreach ($rows as $sec) {
                foreach ([$sec->name_en, $sec->name_ar] as $nm) {
                    if (empty($nm)) continue;
                    $out[mb_strtolower(trim($nm), 'UTF-8')] = $sec->id;
                }
            }
        });
        return $out;
    }

    private function buildCurrencyMap(): array
    {
        $codes = ['USD','EUR','GBP','EGP','SAR','AED','KWD','QAR','OMR','BHD','JOD','MAD','TND','DZD','LYD','SYP','LBP','JPY','CNY','INR','PKR','TRY','ZAR'];
        return array_fill_keys($codes, true);
    }

    public function countryIdFor(string $name): ?int
    {
        return $this->countryNames[mb_strtolower(trim($name), 'UTF-8')] ?? null;
    }

    public function stateIdFor(int $countryId, string $stateName): ?int
    {
        $key = $countryId . ':' . mb_strtolower(trim($stateName), 'UTF-8');
        return $this->stateNamesByCountry[$key] ?? null;
    }

    public function isKnownCurrency(string $code): bool
    {
        return isset($this->currencyCodes[strtoupper(trim($code))]);
    }
}
