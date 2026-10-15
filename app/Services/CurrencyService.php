<?php
namespace App\Services;

use App\Repositories\CurrencyRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use App\Currency;

class CurrencyService
{
    protected $repo;

    public function __construct(CurrencyRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getEnabledCurrencies()
    {
        return $this->repo->getEnabled();
    }

    public function paginateCurrencies($limit = 10, $enabled = null)
    {
        return $this->repo->paginate($limit, $enabled);
    }

    public function store(array $data, UploadedFile $flag = null)
    {
        if ($flag) {
            $data['flag'] = $this->uploadFlag($flag);
        }

        return $this->repo->create($data);
    }

    public function update(Currency $currency, array $data, UploadedFile $flag = null)
    {
        if ($flag) {
            $data['flag'] = $this->uploadFlag($flag);
        }

        $data['last_updated'] = now();

        $data = array_merge([
            'currency_code'       => $currency->currency_code,
            'currency_name'       => $currency->currency_name,
            'currency_symbol'     => $currency->currency_symbol,
            'exchange_rate_to_usd'=> $currency->exchange_rate_to_usd,
            'enabled'             => $currency->enabled,
            'flag'                => $currency->flag,
        ], $data);

        return $this->repo->update($currency, $data);
    }

    public function delete(Currency $currency)
    {
        return $this->repo->delete($currency);
    }

    public function updateExchangeRates()
    {
        $response = Http::timeout(10)->get('https://www.floatrates.com/daily/usd.json');

        if (!$response->ok()) {
            return false;
        }

        $data = $response->json();
        $currencies = $this->repo->getAllExceptUSD();

        foreach ($currencies as $currency) {
            $code = strtolower($currency->currency_code);
            if (isset($data[$code])) {
                $currency->exchange_rate_to_usd = $data[$code]['rate'];
                $currency->last_updated = now();
                $currency->save();
            }
        }

        return true;
    }

    protected function uploadFlag(UploadedFile $file): string
    {
        // Never keep the client's filename: validate, then use a server-generated name.
        $filename = app(FileManagerService::class)->validatedSafeName($file, 'image');
        $file->move(public_path('icons/flags'), $filename);
        return 'icons/flags/' . $filename;
    }
}