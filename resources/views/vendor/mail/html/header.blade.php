@props(['url'])
{{-- Same header logo as emails.layout.master (App\Services\EmailBranding); the app name when there is no logo at all. --}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (!empty($email_logo['url']))
<img src="{{ $email_logo['url'] }}" alt="{{ $org_name ?? $slot }}" style="height: auto; width: auto; max-height: 80px; max-width: 100%; border: 0;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
