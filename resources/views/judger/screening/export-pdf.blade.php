{{-- Judge-scoped screening evaluations export (GV-3340).
     Mirrors the reference workbook's hierarchy: criteria > sub-criteria >
     comment/score/weighted, sub-criteria and criteria totals, then summary.
     One section per step, because criteria belong to a step's evaluation form. --}}
@php
    use App\Services\JudgerScreeningReport;
    use App\Services\PdfArabicText;
    $percent = function( $value ) { return round( floatval( $value ) * 100, 2 ) . '%'; };
    // dompdf does no Arabic shaping or bidi reordering, so every piece of text
    // that may be Arabic -- labels and stored content alike -- is shaped here
    // before it reaches the document. (GV-9497)
    $ar = function( $value ) { return PdfArabicText::shape( $value ); };
    // Narrow header cells wrap, and wrapping must happen before shaping,
    // so these emit one block per line. (GV-9497)
    $arh = function( $value ) { return PdfArabicText::shapeBlock( $value ); };
    $rtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html @if($rtl) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.My Screening Evaluations') }}</title>
    <style>
        @page { margin: 14px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 6.5px; color: #1a1a1a; @if($rtl) direction: rtl; @endif }
        h1 { font-size: 12px; margin: 0 0 2px; }
        .meta { font-size: 7px; color: #555; margin-bottom: 8px; }
        .step-title { font-size: 9px; margin: 10px 0 3px; padding: 3px 5px; background: #eef2fa; border-left: 3px solid #4472C4; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 0.5px solid #999; padding: 2px 3px; word-wrap: break-word; overflow-wrap: break-word; vertical-align: middle; }
        thead th { background: #d9e2f3; text-align: center; font-weight: bold; }
        thead tr.lvl1 th { background: #4472C4; color: #fff; font-size: 7px; }
        {{-- Arabic is italicised nowhere: DejaVuSans-Oblique is the one face in the
             family without the Arabic Presentation Forms, so an italic Arabic cell
             falls back to a font with no Arabic at all and renders as boxes.
             Regular and Bold both carry the block. (GV-9497) --}}
        thead tr.lvl3 th { background: #e2efda; font-weight: normal; @if(!$rtl) font-style: italic; @endif }
        th.total-col { background: #4472C4 !important; color: #fff; }
        td.num { text-align: center; }
        .startup { font-weight: bold; }
        .section { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        .empty { color: #999; font-style: italic; }
    </style>
</head>
<body>

<h1>{{ $ar(__('admin.My Screening Evaluations')) }}</h1>
<div class="meta">
    {{ $ar(__('admin.Judge')) }}: <strong>{{ $ar($judgeName) }}</strong>
    &nbsp;|&nbsp; {{ $ar(__('admin.Generated at')) }}: {{ $generatedAt->format('Y-m-d H:i') }}
</div>

@foreach($groups as $group)
    @php
        // Startup columns: Startup, Team name (Hackathon only), Founder, Status.
        $startupCols = $group->is_hackathon ? 4 : 3;
    @endphp
    <div class="section">
        <div class="step-title">
            {{ $ar($group->program->name) }} &mdash; {{ $ar($group->step->name) }}
            ({{ $ar(__('admin.' . $group->step->type)) }})
        </div>

        <table>
            <thead>
                {{-- Level 1: startup columns, criteria titles, summary --}}
                <tr class="lvl1">
                    <th rowspan="3">{!! $arh(__('admin.Startup Name')) !!}</th>
                    @if($group->is_hackathon)
                        <th rowspan="3">{!! $arh(__('admin.Team Name')) !!}</th>
                    @endif
                    <th rowspan="3">{!! $arh(__('admin.Founder Name')) !!}</th>
                    <th rowspan="3">{!! $arh(__('admin.Status')) !!}</th>
                    @foreach($group->criteria as $criterion)
                        {{-- 3 cols per sub-criterion + its total, plus the criteria total --}}
                        <th colspan="{{ (count($criterion->details) * 4) + 1 }}">{{ $ar($criterion->name) }}</th>
                    @endforeach
                    <th colspan="4">{!! $arh(__('admin.Summary')) !!}</th>
                </tr>

                {{-- Level 2: sub-criteria titles + total columns --}}
                <tr class="lvl2">
                    @foreach($group->criteria as $criterion)
                        @foreach($criterion->details as $sub)
                            <th colspan="3">{{ $ar($sub->name) }}</th>
                            <th rowspan="2">{!! $arh(__('admin.Sub Criteria Total')) !!}</th>
                        @endforeach
                        <th rowspan="2" class="total-col">{!! $arh(__('admin.Criteria Total')) !!}</th>
                    @endforeach
                    <th rowspan="2">{!! $arh(__('admin.Final Comments')) !!}</th>
                    <th rowspan="2">{!! $arh(__('admin.Total Score')) !!}</th>
                    <th rowspan="2">{!! $arh(__('admin.Recommendation')) !!}</th>
                    <th rowspan="2">{!! $arh(__('admin.Timestamp')) !!}</th>
                </tr>

                {{-- Level 3: the per-sub-criterion answer columns --}}
                <tr class="lvl3">
                    @foreach($group->criteria as $criterion)
                        @foreach($criterion->details as $sub)
                            <th>{!! $arh(__('admin.comment')) !!}</th>
                            <th>{!! $arh(__('admin.score')) !!}</th>
                            <th>{!! $arh(__('admin.weighted')) !!}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($group->rows as $row)
                    <tr>
                        <td class="startup">{{ $ar($row->startup) }}</td>
                        @if($group->is_hackathon)
                            <td>{{ $ar($row->team_name) }}</td>
                        @endif
                        <td>{{ $ar($row->founder) }}</td>
                        <td>{{ $ar(__('admin.' . $row->status)) }}</td>

                        @foreach($row->criteria as $criterion)
                            @foreach($criterion->details as $detail)
                                <td>{{ $ar($detail->comment) }}</td>
                                <td class="num">{{ $detail->answered ? $detail->score : '' }}</td>
                                <td class="num">{{ $detail->answered ? $percent($detail->weighted) : '' }}</td>
                                <td class="num">{{ $detail->answered ? $percent($detail->total) : '' }}</td>
                            @endforeach
                            <td class="num">{{ $criterion->total !== null ? $percent($criterion->total) : '' }}</td>
                        @endforeach

                        <td>{{ $ar($row->comment) }}</td>
                        <td class="num">{{ $percent($row->total) }}</td>
                        <td class="num">{{ $ar(JudgerScreeningReport::recommendationLabel($row->approval)) }}</td>
                        <td class="num">{{ $row->timestamp ? \Carbon\Carbon::parse($row->timestamp)->format('Y-m-d H:i') : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

</body>
</html>
