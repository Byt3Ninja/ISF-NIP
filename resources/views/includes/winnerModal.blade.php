<script>
$(document).ready(function() {
	var winners = @json( $winners );
	$(document).on('click', '.open-details-modal', function(e) {
	e.preventDefault();
	let id = $(this).data('id');
	let winner = winners.find(winner => winner.id == id);
	if( typeof( winner ) !== 'undefined' )
	{
		$("#td-team").html(winner.teamName);
		$("#td-project").html(winner.projectTitle);
		$("#td-description").html(winner.description);

		$("#solutionDetailsModal").modal('show');
	}
	});
});
</script>