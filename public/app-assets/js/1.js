
$(document).ready(function () {

    'use strict';


    function numberWithComma(number) {
        var parts = number.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }
    $(document).ready(function() {
        $(".commanums").each(function() {
            var num = $(this).text();
            var commaNum = numberWithComma(num);
            $(this).text(commaNum);
        });
    });
    

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
        var cycle = $('.cyclesExcel').val();

        if(cycle==0) {
            alert(messages.exportNotSuccess);
            return;
        }
        alert('Waiting for export to complete...');
        $.ajax({
            url: `/dashboard/export-cyclesComp`,
            type: 'GET',
            data:{cycle:cycle
            },
            success: function(response) {
                //  alert(response.message);
                  window.location.href = `/dashboard/export-cyclesComp?cycle=${cycle}`;
                  window.reload();
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
        // alert(messages.exportSuccess);
		let url = window.location.origin + '/dashboard/export-Tresult?step=' + step + '&location=' + location;
		window.open(url, '_blank');
        return;
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


// $('.lfm').filemanager('file');


$('#founder-form #founder_name').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#founder-form #founder-error1').show();
        $('#founder-form #founder-error2').hide();
    } else if (val && !/^[\p{L}\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#founder-form #founder-error2').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#founder-form #founder-error1').hide();
        $('#founder-form #founder-error2').hide();
    }
});

$('#founder-form #founder_email').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#founder-form #email-error').show();
    } else if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#founder-form #email-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#founder-form #email-error').hide();
    }
});

$('#founder-form #founder_linkedin').on('input', function() {
    const val = $(this).val();
    if (val && !/^https:\/\/www.linkedin.com\/.*/.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#founder-form #linkedin-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#founder-form #linkedin-error').hide();
    }
});

$('#edit-founder-form .founder_name').on('input', function() {
    const val = $(this).val();
    console.log('edit' , val)
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-founder-form #founder-error1').show();
        $('#edit-founder-form #founder-error2').hide();
    } else if (val && !/^[\p{L}\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-founder-form #founder-error2').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-founder-form #founder-error1').hide();
        $('#edit-founder-form #founder-error2').hide();
    }
});

$('#edit-founder-form .founder_email').on('input', function() {
    const val = $(this).val();
    if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-founder-form #email-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-founder-form #email-error').hide();
    }
});

$('#edit-founder-form .founder_linkedin').on('input', function() {
    const val = $(this).val();
    if (val && !/^https:\/\/www.linkedin.com\/.*/.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-founder-form #linkedin-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-founder-form #linkedin-error').hide();
    }
});

$('#shareholder-form #shareholder_name').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#shareholder-form #shareholder-error1').show();
        $('#shareholder-form #shareholder-error2').hide();
    } else if (val && !/^[\p{L}\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#shareholder-form #shareholder-error1').hide();
        $('#shareholder-form #shareholder-error2').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#shareholder-form #shareholder-error1').hide();
        $('#shareholder-form #shareholder-error2').hide();
    }
});

$('#shareholder-form #shareholder_role').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#shareholder-form #role-error').show();
    }
    else if (val && !/^[\p{L}\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#shareholder-form #role-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#shareholder-form #role-error').hide();
    }
});

$('#shareholder-form #shareholder_ownership_percentage').on('input', function() {
    const val = $(this).val();
    if (!val || !/^\d{1,2}(\.\d{1,2})?$/.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#shareholder-form #ownership-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#shareholder-form #ownership-error').hide();
    }
});

$('#edit-shareholder-form .shareholder_name').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-shareholder-form #shareholder-error1').show();
        $('#edit-shareholder-form #shareholder-error2').hide();
    } else if (val && !/^[\p{L}\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-shareholder-form #shareholder-error1').hide();
        $('#edit-shareholder-form #shareholder-error2').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-shareholder-form #shareholder-error1').hide();
        $('#edit-shareholder-form #shareholder-error2').hide();
    }
});

$('#edit-shareholder-form .shareholder_role').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-shareholder-form #role-error').show();
    }
    else if (val && !/^[\p{L}\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-shareholder-form #role-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-shareholder-form #role-error').hide();
    }
});

$('#edit-shareholder-form .shareholder_ownership_percentage').on('input', function() {
    const val = $(this).val();
    if (!val || !/^\d{1,2}(\.\d{1,2})?$/.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-shareholder-form #ownership-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-shareholder-form #ownership-error').hide();
    }
});



$('#investor-form #investor_name').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#investor-form #investor-error1').show();
        $('#investor-form #investor-error2').hide();
    } else if (val && !/^[\p{L}\d\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#investor-form #investor-error2').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#investor-form #investor-error1').hide();
        $('#investor-form #investor-error2').hide();
    }
});

$('#investor-form #investment_type').on('change', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#investor-form #investment-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#investor-form #investment-error').hide();
    }
});
$('#edit-investor-form .investor_name').on('input', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-investor-form #investor-error1').show();
        $('#edit-investor-form #investor-error2').hide();
    } else if (val && !/^[\p{L}\d\s]+$/u.test(val)) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-investor-form #investor-error2').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-investor-form #investor-error1').hide();
        $('#edit-investor-form #investor-error2').hide();
    }
});

$('#edit-investor-form .investment_type').on('change', function() {
    const val = $(this).val();
    if (!val) {
        $(this).addClass('is-invalid').trigger('focus');
        $('#edit-investor-form #investment-error').show();
    } else {
        $(this).removeClass('is-invalid');
        $('#edit-investor-form #investment-error').hide();
    }
});

$('#edit-founder-link, #edit-shareholder-link, #edit-investor-link').on('click', function() {
    console.log('edit');
    $('#edit-founder-form #founder-error1').hide();
    $('#edit-founder-form #founder-error2').hide();
    $('#edit-founder-form #email-error').hide();
    $('#edit-founder-form #linkedin-error').hide();
    $('#edit-shareholder-form #shareholder-error1').hide();
    $('#edit-shareholder-form #shareholder-error2').hide();
    $('#edit-shareholder-form #role-error').hide();
    $('#edit-shareholder-form #ownership-error').hide();
    $('#edit-investor-form #investor-error1').hide();
    $('#edit-investor-form #investor-error2').hide();
    $('#edit-investor-form #investment-error').hide();
});


$('.AddTeams').click(function(event) {
    event.preventDefault();
    var TeamsNo = $(this).attr('do');
    var company = $(this).attr('company');

    var founder_name = $('#founder_name').val();
    var founder_email = $('#founder_email').val();
    var founder_linkedin = $('#founder_linkedin').val();
    var shareholder_name = $('#shareholder_name').val();
    var shareholder_role = $('#shareholder_role').val();
    var shareholder_ownership_percentage = $('#shareholder_ownership_percentage').val();
    var investor_name = $('#investor_name').val();
    var investment_type = $('#investment_type').val();

    // Validate inputs based on the form type
    if (TeamsNo === "Founders") {
        if (!founder_name) {
            $('#founder_name').addClass('is-invalid').trigger('focus');
            $('#founder-form #founder-error1').show();
            return;
        } else if (founder_name && !/^[\p{L}\s]+$/u.test(founder_name)) {
            $('#founder_name').addClass('is-invalid').trigger('focus');
            $('#founder-form #founder-error2').show();
            return;
        } else {
            $('#founder_name').removeClass('is-invalid');
            $('#founder-form #founder-error1').hide();
            $('#founder-form #founder-error2').hide();
        }

        if (!founder_email) {
            $('#founder_email').addClass('is-invalid').trigger('focus');
            $('#founder-form #email-error').show();
            return;
        } else if (founder_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(founder_email)) {
            $('#founder_email').addClass('is-invalid').trigger('focus');
            $('#founder-form #email-error').show();
            return;
        } else {
            $('#founder_email').removeClass('is-invalid');
            $('#founder-form #email-error').hide();
        }

        if (founder_linkedin && !/^https:\/\/www.linkedin.com\/.*/.test(founder_linkedin)) {
            $('#founder_linkedin').addClass('is-invalid').trigger('focus');
            $('#founder-form #linkedin-error').show();
            return;
        } else {
            $('#founder_linkedin').removeClass('is-invalid');
            $('#founder-form #linkedin-error').hide();
        }
    }
    if (TeamsNo === "Shareholders") {
        if (!shareholder_name) {
            $('#shareholder_name').addClass('is-invalid').trigger('focus');
            $('#shareholder-form #shareholder-error1').show();
            return;
        } else if (shareholder_name && !/^[\p{L}\s]+$/u.test(shareholder_name)) {
            $('#shareholder_name').addClass('is-invalid').trigger('focus');
            $('#shareholder-form #shareholder-error2').show();
            return;
        } else if (!shareholder_role || !/^[\p{L}\s]+$/u.test(shareholder_role)) {
            $('#shareholder_role').addClass('is-invalid').trigger('focus');
            $('#shareholder-form #role-error').show();
            return;
        } else if (!shareholder_ownership_percentage || !/^\d{1,2}(\.\d{1,2})?$/.test(shareholder_ownership_percentage)) {
            $('#shareholder_ownership_percentage').addClass('is-invalid').trigger('focus');
            $('#shareholder-form #ownership-error').show();
            return;
        } else {
            $('#shareholder_name').removeClass('is-invalid');
            $('#shareholder-form #shareholder-error1').hide();
            $('#shareholder-form #shareholder-error2').hide();
        }
    }
    if (TeamsNo === "Investors") {
        if (!investor_name) {
            $('#investor_name').addClass('is-invalid').trigger('focus');
            $('#investor-form #investor-error1').show();
            return;
        } else if (investor_name && !/^[\p{L}\d\s]+$/u.test(investor_name)) {
            $('#investor_name').addClass('is-invalid').trigger('focus');
            $('#investor-form #investor-error2').show();
            return;
        } else if (!investment_type) {
            $('#investment_type').addClass('is-invalid').trigger('focus');
            $('#investor-form #investment-error').show();
            return;
        } else {
            $('#investor_name').removeClass('is-invalid');
            $('#investor-form #investor-error1').hide();
            $('#investor-form #investor-error2').hide();
        }
    }

    $.ajax({
        url: '/dashboard/AddCompanyTeams/' + company,
        method: 'post',
        data: {
            'TeamsNo': TeamsNo,
            'founder_name': founder_name,
            'founder_email': founder_email,
            'founder_linkedin': founder_linkedin,
            'shareholder_name': shareholder_name,
            'shareholder_role': shareholder_role,
            'shareholder_ownership_percentage': shareholder_ownership_percentage,
            'investor_name': investor_name,
            'investment_type': investment_type,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.state) {
                // alert(response.state);
                if (TeamsNo == "Founders") {
                    $('#founder_name').val('');
                    $('#founder_email').val('');
                    $('#founder_linkedin').val('');
                    Swal.fire({
                        title: 'Success!',
                        text: response.state,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.ShowTableFounders').load(location.href + ' .ShowTableFounders');
                        }
                    });
                    
                }
                if (TeamsNo == "Shareholders") {
                    $('#shareholder_name').val('');
                    $('#shareholder_role').val('');
                    $('#shareholder_ownership_percentage').val('');
                    Swal.fire({
                        title: 'Success!',
                        text: response.state,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.ShowTableShareholders').load(location.href + ' .ShowTableShareholders');
                        }
                    });
                    
                }
                if (TeamsNo == "Investors") {
                    $('#investor_name').val('');
                    $('#investment_type').val('');
                    Swal.fire({
                        title: 'Success!',
                        text: response.state,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('.ShowTableInvestors').load(location.href + ' .ShowTableInvestors');
                        }
                    });
                }
            } else {
                alert(errors);
            }
        },
        error: function(error) {
            if (error.responseJSON && error.responseJSON.errors) {
                var errors = error.responseJSON.errors;

                if (errors.founder_name) {
                    alert(errors.founder_name[0]);
                }
                if (errors.founder_email) {
                    alert(errors.founder_email[0]);
                }
                if (errors.founder_linkedin) {
                    alert(errors.founder_linkedin[0]);
                }
                if (errors.shareholder_name) {
                    alert(errors.shareholder_name[0]);
                }
                if (errors.shareholder_role) {
                    alert(errors.shareholder_role[0]);
                }
                if (errors.shareholder_ownership_percentage) {
                    alert(errors.shareholder_ownership_percentage[0]);
                }
                if (errors.investor_name) {
                    alert(errors.investor_name[0]);
                }
                if (errors.investment_type) {
                    alert(errors.investment_type[0]);
                }
            }
        }
    });
});


$('.EditTeams').click(function(event) {
    event.preventDefault();
    var id = $(this).attr('do');
    var TeamsNo = $(this).attr('data-id');
    var company = $(this).attr('company');
    
    var founder_name = $('#founder_name'+id).val();
    var founder_email = $('#founder_email'+id).val();
    var founder_linkedin = $('#founder_linkedin'+id).val();
    var shareholder_name = $('#shareholder_name'+id).val();
    var shareholder_role = $('#shareholder_role'+id).val();
    var shareholder_ownership_percentage = $('#shareholder_ownership_percentage'+id).val();
    var investor_name = $('#investor_name'+id).val();
    var investment_type = $('#investment_type'+id).val();

     // Validate inputs based on the form type
     if (TeamsNo === "Founders") {
        if (!founder_name) {
            $('#edit-founder-form .founder_name').addClass('is-invalid').trigger('focus');
            $('#edit-founder-form #founder-error1').show();
            return;
        } else if (founder_name && !/^[\p{L}\s]+$/u.test(founder_name)) {
            $('#edit-founder-form .founder_name').addClass('is-invalid').trigger('focus');
            $('#edit-founder-form #founder-error2').show();
            return;
        } else {
            $('#edit-founder-form .founder_name').removeClass('is-invalid');
            $('#edit-founder-form #founder-error1').hide();
            $('#edit-founder-form #founder-error2').hide();
        }
        if (!founder_email) {
            $('#edit-founder-form .founder_email').addClass('is-invalid').trigger('focus');
            $('#edit-founder-form #email-error').show();
            return
        } else if (founder_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(founder_email)) {
            $('#edit-founder-form .founder_email').addClass('is-invalid').trigger('focus');
            $('#edit-founder-form #email-error').show();
            return;
        } else {
            $('#edit-founder-form .founder_email').removeClass('is-invalid');
            $('#edit-founder-form #email-error').hide();
        }

        if (founder_linkedin && !/^https:\/\/www.linkedin.com\/.*/.test(founder_linkedin)) {
            $('#edit-founder-form .founder_linkedin').addClass('is-invalid').trigger('focus');
            $('#edit-founder-form #linkedin-error').show();
            return;
        } else {
            $('#edit-founder-form .founder_linkedin').removeClass('is-invalid');
            $('#edit-founder-form #linkedin-error').hide();
        }
    }
    if (TeamsNo === "Shareholders") {
        if (!shareholder_name) {
            $('#edit-shareholder-form .shareholder_name').addClass('is-invalid').trigger('focus');
            $('#edit-shareholder-form #shareholder-error1').show();
            return;
        } else if (shareholder_name && !/^[\p{L}\s]+$/u.test(shareholder_name)) {
            $('#edit-shareholder-form .shareholder_name').addClass('is-invalid').trigger('focus');
            $('#edit-shareholder-form #shareholder-error2').show();
            return;
        } else if (!shareholder_role || !/^[\p{L}\s]+$/u.test(shareholder_role)) {
            $('#edit-shareholder-form .shareholder_role').addClass('is-invalid').trigger('focus');
            $('#edit-shareholder-form #role-error').show();
            return;
        } else if (!shareholder_ownership_percentage || !/^\d{1,2}(\.\d{1,2})?$/.test(shareholder_ownership_percentage)) {
            $('#edit-shareholder-form .shareholder_ownership_percentage').addClass('is-invalid').trigger('focus');
            $('#edit-shareholder-form #ownership-error').show();
            return;
        } else {
            $('#edit-shareholder-form .shareholder_name').removeClass('is-invalid');
            $('#edit-shareholder-form #shareholder-error1').hide();
            $('#edit-shareholder-form #shareholder-error2').hide();
        }
    }
    if (TeamsNo === "Investors") {
        if (!investor_name) {
            $('#edit-investor-form .investor_name').addClass('is-invalid').trigger('focus');
            $('#edit-investor-form #investor-error1').show();
            return;
        } else if (investor_name && !/^[\p{L}\d\s]+$/u.test(investor_name)) {
            $('#edit-investor-form .investor_name').addClass('is-invalid').trigger('focus');
            $('#edit-investor-form #investor-error2').show();
            return;
        } else if (!investment_type) {
            $('#edit-investor-form .investment_type').addClass('is-invalid').trigger('focus');
            $('#edit-investor-form #investment-error').show();
            return;
        } else {
            $('#edit-investor-form .investor_name').removeClass('is-invalid');
            $('#edit-investor-form #investor-error1').hide();
            $('#edit-investor-form #investor-error2').hide();
        }
    }

      $.ajax({
          url: '/dashboard/AddCompanyEdit/'+company, 
          method: 'post',
          data: {
              'id': id,
              'founder_name': founder_name,
              'founder_email': founder_email,
              'founder_linkedin': founder_linkedin,
              'shareholder_name': shareholder_name,
              'shareholder_role': shareholder_role,
              'shareholder_ownership_percentage': shareholder_ownership_percentage,
              'investor_name': investor_name,
              'investment_type': investment_type,
              _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
              if (response.state) {
                Swal.fire({
                    title: 'Success!',
                    text: response.state,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
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

                  if (errors.shareholder_name) {
                    alert(errors.shareholder_name[0]); // عرض رسالة الخطأ الخاصة بالاسم
                }
                if (errors.shareholder_role) {
                    alert(errors.shareholder_role[0]); 
                }
                if (errors.shareholder_ownership_percentage) {
                    alert(errors.shareholder_ownership_percentage[0]); 
                }

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

    var Plan = $(this).attr('do');
    var company = $(this).attr('company');
    
    if(Plan == "Achieved_Milestones"){
    var achieved_milestone_description = $('.achieved_milestone_description').val();
    var achieved_milestone_name = $('.achieved_milestone_name').val();
  
    }else if(Plan == "Planned_Milestones"){
        var planned_milestone_description = $('.planned_milestone_description').val();
        var planned_milestone_name = $('.planned_milestone_name').val();
       
    }
    else if(Plan == "Archived_Milestones"){
        var Archived_milestone_description = $('.Archived_milestone_description').val();
        var Archived_milestone_name = $('.Archived_milestone_name').val();
        
    }
  

        $.ajax({
            url: '/dashboard/AddCompanyMileStone/'+company, 
            method: 'post',
            data: {
                'achieved_milestone_description': achieved_milestone_description,
                'achieved_milestone_name': achieved_milestone_name,
                'Plan': Plan,
                'planned_milestone_description': planned_milestone_description,
                'planned_milestone_name': planned_milestone_name,
                'Archived_milestone_description': Archived_milestone_description,
                'Archived_milestone_name': Archived_milestone_name,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                
                if (response.state) {
                    alert(response.state);
                    if(Plan == "Achieved_Milestones"){
                        // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                      $('.ShowTableAchieved').load(location.href + ' .ShowTableAchieved');
                      
                        }else if(Plan == "Planned_Milestones"){
                            // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                      $('.ShowTablePlanned').load(location.href + ' .ShowTablePlanned');
                           
                        }
                        else if(Plan == "Archived_Milestones"){
                            // تحميل البيانات في div فقط بدون إعادة تحميل الصفحة
                      $('.ShowTableArchive').load(location.href + ' .ShowTableArchive');
                            
                        }
                    
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


$('.EditMilestone').click(function() {
    var id = $(this).attr('do');
    var company = $(this).attr('company');
    
  var Achieved_name = $('.Achieved_name'+id).val();
  var Achieved_desc = $('.Achieved_desc'+id).val();
  var Planned_name = $('.Planned_name'+id).val();
  var Planned_desc = $('.Planned_desc'+id).val();
  var Archived_name = $('.Archived_name'+id).val();
  var Archived_desc = $('.Archived_desc'+id).val();



      $.ajax({
          url: '/dashboard/EditCompanyMileStone/'+company, 
          method: 'post',
          data: {
              'id': id,
              'Achieved_name': Achieved_name,
              'Achieved_desc': Achieved_desc,
              'Planned_name': Planned_name,
              'Planned_desc': Planned_desc,
              'Archived_name': Archived_name,
              'Archived_desc': Archived_desc,
              _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
              
              if (response.state) {
                  alert(response.state);
                 
                  
                  location.reload();


              }else{
                  alert(errors);
              }
          },
          error: function(error) {
              if (error.responseJSON && error.responseJSON.errors) {
                  var errors = error.responseJSON.errors;
  
                  // عرض رسائل الأخطاء بناءً على الحقول المفقودة أو غير الصحيحة
                  if (errors.Achieved_name) {
                      alert(errors.Achieved_name[0]); // عرض رسالة الخطأ الخاصة بالاسم
                  }
                  if (errors.Achieved_desc) {
                      alert(errors.Achieved_desc[0]); // عرض رسالة الخطأ الخاصة بالبريد الإلكتروني
                  }
                  if (errors.Planned_name) {
                      alert(errors.Planned_name[0]); // عرض رسالة الخطأ الخاصة بالرابط
                  }

                  if (errors.Planned_desc) {
                    alert(errors.Planned_desc[0]); // عرض رسالة الخطأ الخاصة بالاسم
                }
                if (errors.Archived_name) {
                    alert(errors.Archived_name[0]); 
                }
                if (errors.Archived_desc) {
                    alert(errors.Archived_desc[0]); 
                }

              }
          }
      });
  
  
});

$(".CreatedTax").change(function(){
    var Create = $(this).val();
    if(Create == 1){
        $('.ShowCreatedTax').show();
        $('.Country_Register').attr('required', 'required');
        $('.Commercial_Register').attr('required', 'required');
        $('.Tax_Card').attr('required', 'required');
    }else{
        $('.ShowCreatedTax').hide();
        $('.Country_Register').removeAttr('required');
        $('.Commercial_Register').removeAttr('required');
        $('.Tax_Card').removeAttr('required');
    }
});

$(".business_model").change(function(){
    
    var business_model = $(this).val();
    
    if(business_model == 'Other'){
        $('.IfOther').show();
        $('.other_business_model').attr('required', 'required');
    }else{
        $('.IfOther').hide();
        $('.other_business_model').removeAttr('required');
        $('.other_business_model').val('');
    }
});












});