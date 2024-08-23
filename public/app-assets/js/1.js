
$(document).ready(function () {

    $('.checkAll').on('click', function() {
        $('.chboxcyclesReg').prop('checked', this.checked);
    });

    $('.ChangeAllBox').change(function() {
        
        var checkget = $(this).val();
            
            var compid = [];
            $('.chboxcyclesReg:checked').each(function() {
                compid.push($(this).attr('do'));
            });

 
            $.ajax({
                url: '/dashboard/change/cyc', 
                method: 'post',
                data: {
                    'compid': compid,
                    'checkget': checkget,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    
                    alert(response.state);
                    location.reload();
                },
                error: function(error) {
                    console.error(error.responseText);
                }
            });
        
    });



});