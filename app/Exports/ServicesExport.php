<?php

namespace App\Exports;

use App\Service;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServicesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $user;
    protected $locale;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->locale = app()->getLocale();
    }

    public function collection()
    {
        return Service::with(['serviceCategory', 'provider', 'organizations'])
            ->when($this->user->hasRole('serviceProvider'), function ($query) {
                $provider_id = $this->user?->service_provider?->id ?? 0;
                $query->where('service_provider_id', $provider_id);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Organization',
            'Service Name',
            'Provider Name',
            'Price',
            'Description',
            'Vendor Name',
            'Vendor Email',
            'Vendor Phone',
            'Category',
            'Status',
            'Created At',
        ];
    }

    public function map($service): array
    {
        return [
            $service->id,
            $service->organizations->first()?->{'name_'.$this->locale} ?? '',
            $service->{'name_'.$this->locale} ?? '',
            $service->provider
                ? $service->provider->contact_name
                : '',
            $service->price,
            strip_tags($service->{'description_'.$this->locale}) ?? '',
            $service->vendor_name ?? '',
            $service->vendor_email ?? '',
            $service->vendor_phone ?? '',
            $service->serviceCategory?->{'name_'.$this->locale} ?? '',
            $service->status == 1 ? 'Active' : 'Inactive',
            $service->created_at?->format('Y-m-d'),
        ];
    }
}
