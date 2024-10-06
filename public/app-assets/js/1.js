
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

   

    $(document).on('click', '.ExportFinanceTS', function() {
        var hours = $('.hours').val();
        var condition = $('.condition').val();
        var cycle_name = $('.cycle_name').val();
        var user_id = $('.user_id').val();
        var org_id = $('.org_id').val();
        alert(messages.exportSuccess);
        $.ajax({
            url: `/dashboard/export-FinanceTS`,
            type: 'GET',
            data:{hours:hours,
                condition:condition,
                cycle_name:cycle_name,
                user_id:user_id,
                org_id:org_id
            },
            success: function(response) {
                
                //  alert(response.message);
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

   if(meeting == 'Teams'){

        
        $('.MICROSOFT_CLIENT').show();
        $('.ZOOM_CLIENT').hide();

    }else if(meeting == 'Zoom'){

       
        $('.MICROSOFT_CLIENT').hide();
        $('.ZOOM_CLIENT').show();

    }

});



$(document).on('change', '.roleshow', function(){
    var role = $(this).val();
   
   if(role == 'Mentor'){
        $('.ShowInputMentor').show();
    }else{
        $('.ShowInputMentor').hide();
    }
});

$(document).on('click', '.Val0', function(){
    var role = $('.getVal').val('0');
});
$(document).on('click', '.Val1', function(){
    var role = $('.getVal').val('1');
});

$(document).on('click', '.vallevel0', function(){
    var role = $('.getValLevel').val('Beginner');
});
$(document).on('click', '.vallevel1', function(){
    var role = $('.getValLevel').val('Intermediate');
});
$(document).on('click', '.vallevel2', function(){
    var role = $('.getValLevel').val('Advanced');
});

$('.RateShowOrNot').change(function() {
    if ($(this).is(':checked')) {
        // إذا كانت العلامة مفعلة
        $('.RateSelect').show();   // إظهار العنصر
        $('.Search').hide();       // إخفاء العنصر
        $('.Search').val('');
        $('.ExpertiesInput').hide();
        $('.ExpertiesSearch').val('');
    } else {
        // إذا كانت العلامة غير مفعلة
        $('.RateSelect').hide();   // إخفاء العنصر
        $('.rateSearch').val('');
        $('.Search').show();       // إظهار العنصر (أو أي منطق تريده)
        $('.ExpertiesInput').hide(); // إظهار العنصر (أو أي منطق تريده)
        $('.ExpertiesSearch').val('');
    }
});
$('.ExpertiesShowOrNot').change(function() {
    if ($(this).is(':checked')) {
        // إذا كانت العلامة مفعلة
        $('.RateSelect').hide();  // إظهار العنصر
        $('.rateSearch').val('');
        $('.Search').hide();       // إخفاء العنصر
        $('.Search').val('');
        $('.ExpertiesInput').show();
    } else {
        // إذا كانت العلامة غير مفعلة
        $('.RateSelect').hide();   // إخفاء العنصر
        $('.rateSearch').val('');
        $('.Search').show();       // إظهار العنصر (أو أي منطق تريده)
        $('.ExpertiesInput').hide(); // إظهار العنصر (أو أي منطق تريده)
        $('.ExpertiesSearch').val('');
    }
});































});