
$(document).ready(function () {

    'use strict';

    $('.checkAll').on('click', function() {
        $('.chboxcyclesReg').prop('checked', this.checked);
    });

    $('.ChangeAllBox').change(function() {
        
        var checkget = $(this).val();
            
            var compid = [];
            $('.chboxcyclesReg:checked').each(function() {
                compid.push($(this).attr('do'));
            });

            if(compid==""){
                alert("You Didn't Choose any Company");
                location.reload();
            }
 
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


    // Result Training
    $('.checkAllTR').on('click', function() {
        $('.chboxTresult').prop('checked', this.checked);
    });

    $('.ChangeAllBoxTR').change(function() {
        
        var checkget = $(this).val();
            
            var compid = [];
            $('.chboxTresult:checked').each(function() {
                compid.push($(this).attr('do'));
                
            });

            if(compid==""){
                alert("You Didn't Choose any Company");
                location.reload();
            }

            $.ajax({
                url: '/dashboard/trainings/change/Tresult', 
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


    $(document).on('click', '.ExportCycle', function() {

        alert(messages.exportSuccess);
        $.ajax({
            url: `/dashboard/export-Prog`,
            type: 'GET',
            success: function(data) {
                // alert(data.message);
            },
            error: function(error) {
                console.error('Error:', error);

            }
        });

    });


    $(document).on('click', '.ExportExcelScreesRes', function() {
        var step = $('.stepsExcel').val();
        var formid = $('.formid').val();
        var exportcstep = $('.exportcurrentstep').val();

        if(step ==0 || !formid || !exportcstep) {
            alert(messages.exportNotSuccess);
            return;
        }

        alert(messages.exportSuccess);

        $.ajax({
            url: `/dashboard/export-Result`,
            type: 'GET',
            data:{step:step,
                formid:formid,
                exportcstep:exportcstep
            },
            success: function(response) {
                
                // alert(response.message);
            },
            error: function(error) {
                console.error('Error:', error);

            }
        });

    });

    $(document).on('click', '.ExportCycleComp', function() {
        var step = $('.cyclesExcel').val();

        if(step==0) {
            alert(messages.exportNotSuccess);
            return;
        }

alert(messages.exportSuccess);

        $.ajax({
            url: `/dashboard/export-cyclesComp`,
            type: 'GET',
            data:{step:step
            },
            success: function(response) {
                // alert(response.message);
            },
            error: function(error) {
                console.error('Error:', error);

            }
        });

    });

    $(document).on('click', '.ExportExcelTresult', function() {
        var step = $('.step_id').val();
        var location = $('.location').val();

        if(!step) {
            alert('Please make sure all required fields are filled.');
            return;
        }
        alert(messages.exportSuccess);
        $.ajax({
            url: `/dashboard/export-Tresult`,
            type: 'GET',
            data:{step:step,
                location:location
            },
            success: function(response) {
                // alert(response.message);
            },
            error: function(error) {
                console.error('Error:', error);

            }
        });

    });

    $(document).on('click', '.ExportExcelMentor', function() {
        var request = $('.Request').val();
        alert(messages.exportSuccess);
        $.ajax({
            url: `/dashboard/export-mentorship`,
            type: 'GET',
            data:{request:request
            },
            success: function(response) {
                // alert(response.message);
            },
            error: function(error) {
                console.error('Error:', error);

            }
        });

    });

    

    if ($("#result-list-datatable").length > 0) {
        usersTable = $("#result-list-datatable").DataTable({
            "oSearch": { "sSearch": searchString },
            responsive: true,
            'columnDefs': [
                {
                    "orderable": false,
                    "targets": [5] // تعيين الأعمدة التي لا يمكن فرزها
                }
            ],
            "order": [0, 'asc'], // تعيين ترتيب افتراضي، مثال: ترتيب تصاعدي للعمود الأول
            "language": tablelang,
            "paging": true, // تفعيل الترقيم الصفحات
            "searching": true, // تفعيل البحث
            "ordering": true, // تفعيل الفرز
            "info": true, // تفعيل عرض معلومات الجدول
            "lengthChange": true, // السماح بتغيير عدد العناصر المعروضة
        });
    };

    $(document).on("click", "#result-list-datatable tr", function () {
        $(this).find("td").each(function () {
            usersDataArray.push($(this).text().trim())
        })
        localStorage.setItem("usersId", usersDataArray[0]);
        localStorage.setItem("usersUsername", usersDataArray[1]);
        localStorage.setItem("usersName", usersDataArray[2]);
        localStorage.setItem("usersVerified", usersDataArray[4]);
        localStorage.setItem("usersRole", usersDataArray[5]);
        localStorage.setItem("usersStatus", usersDataArray[6]);
    })
    // render stored local storage data on page named page-users-view
    if (localStorage.usersId !== undefined) {
        $(".users-view-id").html(localStorage.getItem("usersId"));
        $(".users-view-username").html(localStorage.getItem("usersUsername"));
        $(".users-view-name").html(localStorage.getItem("usersName"));
        $(".users-view-verified").html(localStorage.getItem("usersVerified"));
        $(".users-view-role").html(localStorage.getItem("usersRole"));
        $(".users-view-status").html(localStorage.getItem("usersStatus"));
        // update badge color on status change
        if ($(".users-view-status").text() === "Banned") {
            $(".users-view-status").toggleClass("badge-light-success badge-light-danger")
        }
        // update badge color on status change
        if ($(".users-view-status").text() === "Close") {
            $(".users-view-status").toggleClass("badge-light-success badge-light-warning")
        }
    }



$(document).on('change', '.Meeting', function(){
    var meeting = $(this).val();

    if(meeting == 'Google'){
$('.GOOGLE_CLIENT_ID').show();
$('.MICROSOFT_CLIENT').hide();
$('.ZOOM_CLIENT').hide();


    }else if(meeting == 'Microsoft'){

        $('.GOOGLE_CLIENT_ID').hide();
        $('.MICROSOFT_CLIENT').show();
        $('.ZOOM_CLIENT').hide();

    }else if(meeting == 'Zoom'){

        $('.GOOGLE_CLIENT_ID').hide();
        $('.MICROSOFT_CLIENT').hide();
        $('.ZOOM_CLIENT').show();

    }




});



  







































});