{{-- Judge-scoped screening evaluations export (GV-3340).
     Mirrors the reference workbook's hierarchy: criteria > sub-criteria >
     comment/score/weighted, sub-criteria and criteria totals, then summary.
     One section per step, because criteria belong to a step's evaluation form. --}}
@php
    use App\Services\JudgerScreeningReport;
    $percent = function( $value ) { return round( floatval( $value ) * 100, 2 ) . '%'; };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.My Screening Evaluations') }}</title>
    <style>
        @page { margin: 14px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 6.5px; color: #1a1a1a; }
        h1 { font-size: 12px; margin: 0 0 2px; }
        .meta { font-size: 7px; color: #555; margin-bottom: 8px; }
        .step-title { font-size: 9px; margin: 10px 0 3px; padding: 3px 5px; background: #eef2fa; border-left: 3px solid #4472C4; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 0.5px solid #999; padding: 2px 3px; word-wrap: break-word; overflow-wrap: break-word; vertical-align: middle; }
        thead th { background: #d9e2f3; text-align: center; font-weight: bold; }
        thead tr.lvl1 th { background: #4472C4; color: #fff; font-size: 7px; }
        thead tr.lvl3 th { background: #e2efda; font-weight: normal; font-style: italic; }
        th.total-col { background: #4472C4 !important; color: #fff; }
        td.num { text-align: center; }
        .startup { font-weight: bold; }
        .section { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        .empty { color: #999; font-style: italic; }
    </style>
</head>
<body>

<h1>{{ __('admin.My Screening Evaluations') }}</h1>
<div class="meta">
    {{ __('admin.Judge') }}: <strong>{{ $judgeName }}</strong>
    &nbsp;|&nbsp; {{ __('admin.Generated at') }}: {{ $generatedAt->format('Y-m-d H:i') }}
</div>

@foreach($groups as $group)
    @php
        // Startup columns: Startup, Team name (Hackathon only), Founder, Status.
        $startupCols = $group->is_hackathon ? 4 : 3;
    @endphp
    <div class="section">
        <div class="step-title">
            {{ $group->program->name }} &mdash; {{ $group->step->name }}
            ({{ __('admin.' . $group->step->type) }})
        </div>

        <table>
            <thead>
                {{-- Level 1: startup columns, criteria titles, summary --}}
                <tr class="lvl1">
                    <th rowspan="3">{{ __('admin.Startup Name') }}</th>
                    @if($group->is_hackathon)
                        <th rowspan="3">{{ __('admin.Team Name') }}</th>
                    @endif
                    <th rowspan="3">{{ __('admin.Founder Name') }}</th>
                    <th rowspan="3">{{ __('admin.Status') }}</th>
                    @foreach($group->criteria as $criterion)
                        {{-- 3 cols per sub-criterion + its total, plus the criteria total --}}
                        <th colspan="{{ (count($criterion->details) * 4) + 1 }}">{{ $criterion->name }}</th>
                    @endforeach
                    <th colspan="4">{{ __('admin.Summary') }}</th>
                </tr>

                {{-- Level 2: sub-criteria titles + total columns --}}
                <tr class="lvl2">
                    @foreach($group->criteria as $criterion)
                        @foreach($criterion->details as $sub)
                            <th colspan="3">{{ $sub->name }}</th>
                            <th rowspan="2">{{ __('admin.total') }}</th>
                        @endforeach
                        <th rowspan="2" class="total-col">{{ __('admin.total') }}</th>
                    @endforeach
                    <th rowspan="2">{{ __('admin.Final Comments') }}</th>
                    <th rowspan="2">{{ __('admin.Total Score') }}</th>
                    <th rowspan="2">{{ __('admin.Recommendation') }}</th>
                    <th rowspan="2">{{ __('admin.Timestamp') }}</th>
                </tr>

                {{-- Level 3: the per-sub-criterion answer columns --}}
                <tr class="lvl3">
                    @foreach($group->criteria as $criterion)
                        @foreach($criterion->details as $sub)
                            <th>{{ __('admin.comment') }}</th>
                            <th>{{ __('admin.score') }}</th>
                            <th>{{ __('admin.weighted') }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($group->rows as $row)
                    <tr>
                        <td class="startup">{{ $row->startup }}</td>
                        @if($group->is_hackathon)
                            <td>{{ $row->team_name }}</td>
                        @endif
                        <td>{{ $row->founder }}</td>
                        <td>{{ __('admin.' . $row->status) }}</td>

                        @foreach($row->criteria as $criterion)
                            @foreach($criterion->details as $detail)
                                <td>{{ $detail->comment }}</td>
                                <td class="num">{{ $detail->answered ? $detail->score : '' }}</td>
                                <td class="num">{{ $detail->answered ? $percent($detail->weighted) : '' }}</td>
                                <td class="num">{{ $detail->answered ? $percent($detail->total) : '' }}</td>
                            @endforeach
                            <td class="num">{{ $criterion->total !== null ? $percent($criterion->total) : '' }}</td>
                        @endforeach

                        <td>{{ $row->comment }}</td>
                        <td class="num">{{ $percent($row->total) }}</td>
                        <td class="num">{{ JudgerScreeningReport::recommendationLabel($row->approval) }}</td>
                        <td class="num">{{ $row->timestamp ? \Carbon\Carbon::parse($row->timestamp)->format('Y-m-d H:i') : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

</body>
</html>
