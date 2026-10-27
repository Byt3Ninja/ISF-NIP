{{--
	GV-8357: judges cell shared by the founder dashboard's Screening Sessions and
	Final Session tables.

	A panel's judges belong to ONE meeting row, so they render as a single
	overlapping avatar stack rather than one table row per judge. At most
	$limit avatars are shown; the rest collapse into a "+N" bubble.

	GV-9570: the stack is one button. Hover-capable screens still get a tooltip
	per avatar (and the hidden names on "+N"); a tap or click opens the full
	list in the shared judges panel -- the page must include
	includes.partials.judges-panel once. The judges travel as JSON on the button
	so DataTables Responsive's copied child-row cells open it too.

	@param  iterable  $judges  objects exposing ->name and ->image_url
	@param  int       $limit   avatars shown before overflowing (default 3)
--}}
@php
	$judgesList = collect($judges ?? [])->filter();
	$judgesLimit = $limit ?? 3;
	$visibleJudges = $judgesList->take($judgesLimit);
	$hiddenJudges = $judgesList->slice($judgesLimit);
	$hiddenJudgeNames = $hiddenJudges->pluck('name')->filter()->implode(', ');
	$judgeInitials = fn ($name) => collect(preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY))
		->map(fn ($part) => preg_match('/[\p{L}\p{N}]/u', $part, $letter) ? $letter[0] : '')
		->filter()
		->take(2)
		->implode('');
	$judgesPayload = $judgesList->map(fn ($judge) => [
		'name' => (string) $judge->name,
		'image' => $judge->image_url ?: '/images/user-placeholder.jpeg',
		'initials' => $judgeInitials($judge->name),
	])->values();
@endphp

@if( $judgesList->isEmpty() )
	<span class="text-muted">&mdash;</span>
@else
	<button type="button" class="judges-stack"
		data-judges="{{ $judgesPayload->toJson() }}"
		aria-haspopup="dialog"
		aria-label="{{ __('dashboard.View all judges', ['count' => $judgesList->count()]) }}">
		{{-- GV-9570: flex-nowrap + flex-shrink-0 keep the stack at its designed overlap
			 in narrow (mobile / DataTables child-row) cells instead of squeezing the
			 avatars into each other. --}}
		<span class="d-flex flex-nowrap align-items-center avatar-group" aria-hidden="true">
			@foreach( $visibleJudges as $judge )
				<span class="avatar avatar-sm pull-up flex-shrink-0" data-judge-tip title="{{ $judge->name }}">
					{{-- GV-9570: initials sit under the photo, so a missing image (or a
						 missing placeholder) still reads as a clean bubble. The img keeps
						 alt="" because a broken image otherwise paints its alt text
						 (the judge's name) inside the 32px circle. --}}
					<span class="avatar-initial rounded-circle bg-label-primary fw-semibold">{{ $judgeInitials($judge->name) }}</span>
					<img class="rounded-circle position-relative object-fit-cover"
						src="{{ $judge->image_url ?: '/images/user-placeholder.jpeg' }}"
						onerror="this.onerror=null;this.src='/images/user-placeholder.jpeg'"
						alt="">
				</span>
			@endforeach

			@if( $hiddenJudges->isNotEmpty() )
				<span class="avatar avatar-sm flex-shrink-0">
					<span class="avatar-initial rounded-circle pull-up bg-label-primary fw-semibold"
						data-judge-tip
						title="{{ $hiddenJudgeNames }}">+{{ $hiddenJudges->count() }}</span>
				</span>
			@endif
		</span>
		<i class="ti ti-chevron-right judges-stack__chevron" aria-hidden="true"></i>
	</button>
@endif
