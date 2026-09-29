<?php

namespace App\Exports;

use App\Service;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServicesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $user;
    protected $locale;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = auth()->user();
        $this->locale = app()->getLocale();
        
    }

    public function collection()
    {
        $request = $this->request;
        $user = $this->user;
        return Service::with(['serviceCategory', 'provider', 'organizations'])
        ->when($user->hasRole('serviceProvider'), function ($query) use ($user) {
            $provider_id = $user?->service_provider?->id ?? 0;
            $query->where('service_provider_id', $provider_id);
        })

        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $searchTerms = explode(' ', strtolower($request->search));

                foreach ($searchTerms as $term) {
                    $term = '%' . $term . '%';

                    $q->where(function ($subQ) use ($term) {
                        $subQ->whereRaw('LOWER(name_en) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(name_ar) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(description_en) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(description_ar) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(vendor_name) LIKE ?', [$term])
                            ->orWhereHas('serviceCategory', function ($catQ) use ($term) {
                                $catQ->whereRaw('LOWER(name_en) LIKE ?', [$term])
                                    ->orWhereRaw('LOWER(name_ar) LIKE ?', [$term]);
                            });
                    });
                }
            });
        })

        ->when(
            $request->filled('org-id') && $request->{'org-id'} !== 'all',
            function ($query) use ($request) {
                $query->whereHas('organizations', function ($q) use ($request) {
                    $q->where('organizations.id', $request->{'org-id'});
                });
            }
        )

        ->when(
            $request->filled('status') && $request->status !== 'all',
            function ($query) use ($request) {
                $query->where('status', $request->status);
            }
        )
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
            $service->organizations->pluck('name_'.$this->locale)
                                    ->filter()
                                    ->implode(' - ') ?? '',
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
