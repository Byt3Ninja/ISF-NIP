{{--  <div class="card border shadow-sm p-0">
 	<div class="card-header d-flex justify-content-between align-items-center">
 		<h5 class="mb-0 fw-bold card-title text-dark">
			🏆 {{  $l == 'ar' ? 'نتائج التحكيم واختيار الفائز' : 'Judging Results & Winner Selection' }}
		</h5>
 	</div>
 	<div class="card-body">
 		<div class="table-responsive text-nowrap rounded">
 			<table class="table rounded">
 				<thead class="table-light">
 					<tr>
 						<th>{{ $l == 'ar' ? 'اسم الفريق' : 'Team Name' }}</th>
 						<th>{{ $l == 'ar' ? 'اسم المشروع' : 'Project Name' }}</th>
 						<th>{{ $l == 'ar' ? 'النقاط' : 'Score' }}</th>
 						<th>{{ $l == 'ar' ? 'ترتيب الفائز' : 'Winner Rank' }}</th>
 					</tr>
 				</thead>
 				<tbody>
 					@foreach( $results as $team )
 					<tr @if(in_array($team->rank, [1, 2, 3])) class="bg-{{ $team->rank == 1 ? 'first' : ($team->rank == 2 ? 'second' : 'third') }}" @endif>
 						<td>{{ $team->name }}</td>
 						<td>{{ $team->project }}</td>
 						<td>{{ $team->score }}</td>
 						<td>
 							<select class="form-select form-select-sm bg-white">
 								<option value="0">None</option>
 								<option value="1">🥇 First Place</option>
 								<option value="2">🥈 Second Place</option>
 								<option value="3">🥉 Third Place</option>
 							</select>
 						</td>
 					</tr>
 					@endforeach
 				</tbody>
 			</table>
 		</div>
 	</div>
</div>  --}}
