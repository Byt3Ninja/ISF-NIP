{{--
	GV-8357: judges cell shared by the founder dashboard's Screening Sessions and
	Final Session tables.

	A panel's judges belong to ONE meeting row, so they render as a single
	overlapping avatar stack rather than one table row per judge. At most
	$limit avatars are shown; the rest collapse into a "+N" bubble whose
	tooltip lists the hidden names.

	@param  iterable  $judges  objects exposing ->name and ->image_url
	@param  int       $limit   avatars shown before overflowing (default 3)
--}}
@php
	$judgesList = collect($judges ?? [])->filter();
	$judgesLimit = $limit ?? 3;
	$visibleJudges = $judgesList->take($judgesLimit);
	$hiddenJudges = $judgesList->slice($judgesLimit);
	$hiddenJudgeNames = $hiddenJudges->pluck('name')->filter()->implode(', ');
@endphp

@if( $judgesList->isEmpty() )
	<span class="text-muted">&mdash;</span>
@else
	<ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
		@foreach( $visibleJudges as $judge )
			<li class="avatar avatar-sm pull-up"
				data-bs-toggle="tooltip"
				data-bs-placement="top"
				title="{{ $judge->name }}">
				<img class="rounded-circle"
					src="{{ $judge->image_url ?: '/images/user-placeholder.jpeg' }}"
					onerror="this.src='/images/user-placeholder.jpeg'"
					alt="{{ $judge->name }}">
			</li>
		@endforeach

		@if( $hiddenJudges->isNotEmpty() )
			<li class="avatar avatar-sm">
				<span class="avatar-initial rounded-circle pull-up bg-label-primary fw-semibold"
					data-bs-toggle="tooltip"
					data-bs-placement="top"
					title="{{ $hiddenJudgeNames }}">+{{ $hiddenJudges->count() }}</span>
			</li>
		@endif
	</ul>
@endif
