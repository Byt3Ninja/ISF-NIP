
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

$('#CoursCategories').select2({
    templateResult: formatState,
    templateSelection: formatState
});


// دالة لتخصيص الخيارات بالألوان
function formatState (state) {
if (!state.id) {
    return state.text; // Return the default text for placeholders
}

// تخصيص كل خيار حسب الفئة
var colorClass = $(state.element).attr('class');
var $state = $(
    '<span class="' + colorClass + '">' + state.text + '</span>'
);
return $state;
}


$('.lfm').filemanager('file');





$('.AddFounders').click(function() {
        
    var founder_name = $('.founder_name').val();
    var founder_email = $('.founder_email ').val();
    var founder_linkedin = $('.founder_linkedin').val();
 
        $.ajax({
            url: '/dashboard/AddCompanyTeams', 
            method: 'post',
            data: {
                'founder_name': founder_name,
                'founder_email': founder_email,
                'founder_linkedin': founder_linkedin,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                
                if (response.state) {
                    alert(response.state);
                    
                    // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                     $('.ShowTableResults').load(location.href + ' .ShowTableResults');
                }else{
                    alert(errors);
                }
            },
            error: function(error) {
                if (error.responseJSON && error.responseJSON.errors) {
                    var errors = error.responseJSON.errors;
    
                    // عرض رسائل الأخطاء بناءً على الحقول المفقودة أو غير الصحيحة
                    if (errors.founder_name) {
                        alert(errors.founder_name[0]); // عرض رسالة الخطأ الخاصة بالاسم
                    }
                    if (errors.founder_email) {
                        alert(errors.founder_email[0]); // عرض رسالة الخطأ الخاصة بالبريد الإلكتروني
                    }
                    if (errors.founder_linkedin) {
                        alert(errors.founder_linkedin[0]); // عرض رسالة الخطأ الخاصة بالرابط
                    }
                }
            }
        });
    
});

$('.AddSherHolders').click(function() {
    var shareholder_name = $('.shareholder_name').val();
    var shareholder_role = $('.shareholder_role').val();
    var shareholder_ownership_percentage = $('.shareholder_ownership_percentage').val();
        $.ajax({
            url: '/dashboard/AddCompanyShereholder', 
            method: 'post',
            data: {
                'shareholder_name': shareholder_name,
                'shareholder_role': shareholder_role,
                'shareholder_ownership_percentage': shareholder_ownership_percentage,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                
                if (response.state) {
                    alert(response.state);
                    // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                  $('.ShowTableResults').load(location.href + ' .ShowTableResults');
                }else{
                    alert(errors);
                }
            },
            error: function(error) {
                if (error.responseJSON && error.responseJSON.errors) {
                    var errors = error.responseJSON.errors;
    
                    // عرض رسائل الأخطاء بناءً على الحقول المفقودة أو غير الصحيحة
                    if (errors.shareholder_name) {
                        alert(errors.shareholder_name[0]); // عرض رسالة الخطأ الخاصة بالاسم
                    }
                    if (errors.shareholder_role) {
                        alert(errors.shareholder_role[0]); 
                    }
                    if (errors.shareholder_ownership_percentage) {
                        alert(errors.shareholder_ownership_percentage[0]); 
                    }
                    
                }
            }
        });
    
});

$('.AddInvistor').click(function() {
    var investor_name = $('.investor_name').val();
    var investment_type = $('.investment_type').val();
        $.ajax({
            url: '/dashboard/AddCompanyInvistor', 
            method: 'post',
            data: {
                'investor_name': investor_name,
                'investment_type': investment_type,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                
                if (response.state) {
                    alert(response.state);
                    // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                      $('.ShowTableResults').load(location.href + ' .ShowTableResults');
                }else{
                    alert(errors);
                }
            },
            error: function(error) {
                if (error.responseJSON && error.responseJSON.errors) {
                    var errors = error.responseJSON.errors;
    
                    // عرض رسائل الأخطاء بناءً على الحقول المفقودة أو غير الصحيحة
                    if (errors.investor_name) {
                        alert(errors.investor_name[0]); // عرض رسالة الخطأ الخاصة بالاسم
                    }
                    if (errors.investment_type) {
                        alert(errors.investment_type[0]); 
                    }
                    
                }
            }
        });
    
});

$('.AddMileStone').click(function() {
    var achieved_milestone_description = $('.achieved_milestone_description').val();
    var achieved_milestone_name = $('.achieved_milestone_name').val();
    var achieved_milestones = $('.achieved_milestones').val();
        $.ajax({
            url: '/dashboard/AddCompanyMileStone', 
            method: 'post',
            data: {
                'achieved_milestone_description': achieved_milestone_description,
                'achieved_milestone_name': achieved_milestone_name,
                'achieved_milestones': achieved_milestones,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                
                if (response.state) {
                    alert(response.state);
                    // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                      $('.ShowTableResultsMil').load(location.href + ' .ShowTableResultsMil');
                }else{
                    alert(errors);
                }
            },
            error: function(error) {
                if (error.responseJSON && error.responseJSON.errors) {
                    var errors = error.responseJSON.errors;
    
                    // عرض رسائل الأخطاء بناءً على الحقول المفقودة أو غير الصحيحة
                    if (errors.achieved_milestone_name) {
                        alert(errors.achieved_milestone_name[0]); // عرض رسالة الخطأ الخاصة بالاسم
                    }
                    if (errors.achieved_milestones) {
                        alert(errors.achieved_milestones[0]); 
                    }
                    if (errors.achieved_milestone_description) {
                        alert(errors.achieved_milestone_description[0]); 
                    }
                    
                }
            }
        });
    
});

















});