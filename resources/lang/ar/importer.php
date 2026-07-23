<?php

return [

    'errors' => [
        'ext_unsupported'    => 'صيغة الملف غير مدعومة. يرجى رفع ملف CSV صحيح.',
        'empty_file'         => 'الملف المرفوع فارغ.',
        'empty_headers_row'  => 'صف الرؤوس الرئيسية فارغ.',
        'missing_headers'    => 'الأعمدة المطلوبة مفقودة: :headers.',
        'duplicate_headers'  => 'تم العثور على أسماء أعمدة مكررة: :headers.',
        'too_large'          => 'حجم الملف كبير جدًا. الحد الأقصى هو 10 ميغابايت.',
        'too_many_rows'      => 'عدد صفوف الملف كبير جدًا. الحد الأقصى هو 5000 صف.',
        'binary_detected'    => 'يبدو أن الملف ثنائي وليس CSV.',
        'mime_invalid'       => 'نوع MIME للملف ليس ملف CSV صحيح.',
        'encoding_invalid'   => 'ترميز الملف غير مدعوم. يرجى الحفظ بترميز UTF-8.',
        'rate_limited'       => 'عدد محاولات الرفع كبير جدًا. يرجى المحاولة لاحقًا.',
        'session_not_found'  => 'جلسة الاستيراد غير موجودة.',
        'invalid_state_transition' => 'هذا الإجراء غير مسموح في الخطوة الحالية.',
        'organization_required'    => 'الجهة مطلوبة.',
    ],

    'row_errors' => [
        // Program
        'program_name_required'      => 'اسم البرنامج مطلوب.',
        'program_type_required'      => 'نوع البرنامج مطلوب.',
        'program_type_enum'          => 'يجب أن يكون نوع البرنامج General_Program أو Hackathon.',
        'program_start_date_required'=> 'تاريخ بداية البرنامج مطلوب.',
        'program_start_date_invalid' => 'يجب أن يكون تاريخ بداية البرنامج تاريخًا صحيحًا.',
        'program_end_date_required'  => 'تاريخ نهاية البرنامج مطلوب.',
        'program_end_date_invalid'   => 'يجب أن يكون تاريخ نهاية البرنامج تاريخًا صحيحًا.',
        'program_dates_order'        => 'يجب أن يكون تاريخ بداية البرنامج قبل أو مساويًا لتاريخ نهاية البرنامج.',
        'program_exists_different_dates' => 'الصف :row: يوجد برنامج بنفس الاسم والنوع بالفعل، لكن التواريخ مختلفة.',

        // Founder
        'founder_first_name_required'=> 'الاسم الأول للمؤسس مطلوب.',
        'founder_last_name_required' => 'اسم العائلة للمؤسس مطلوب.',
        'founder_email_required'     => 'البريد الإلكتروني للمؤسس مطلوب.',
        'founder_email_invalid'      => 'يرجى إدخال بريد إلكتروني صحيح للمؤسس.',
        'founder_gender_required'    => 'نوع المؤسس مطلوب.',
        'founder_gender_enum'        => 'يجب أن يكون نوع المؤسس Male أو Female.',
        'founder_phone_required'     => 'رقم هاتف المؤسس مطلوب.',
        'founder_phone_invalid'      => 'يرجى إدخال رقم هاتف صحيح للمؤسس.',
        'founder_country_code_required'=> 'رمز الدولة مطلوب.',
        'founder_country_code_invalid' => 'يرجى إدخال رمز دولة صحيح.',
        'founder_working_commitment_enum' => 'يجب أن تكون درجة التفرغ للمؤسس Full-time أو Part-time.',
        'founder_educational_status_enum' => 'يجب أن تكون الحالة التعليمية للمؤسس Graduate أو Student.',
        'founder_dob_invalid'        => 'يجب أن يكون تاريخ ميلاد المؤسس تاريخًا صحيحًا.',
        'founder_dob_not_future'     => 'لا يمكن أن يكون تاريخ ميلاد المؤسس بعد تاريخ اليوم.',
        'founder_join_date_invalid'  => 'يجب أن يكون تاريخ انضمام المؤسس تاريخًا صحيحًا.',

        // Co-Founder
        'cofounder_name_required'    => 'اسم الشريك المؤسس مطلوب عند إدخال بيانات الشريك المؤسس.',
        'cofounder_email_required'   => 'البريد الإلكتروني للشريك المؤسس مطلوب عند إدخال بيانات الشريك المؤسس.',
        'cofounder_email_invalid'    => 'يرجى إدخال بريد إلكتروني صحيح للشريك المؤسس.',
        'cofounder_email_distinct'   => 'يجب أن يكون البريد الإلكتروني للشريك المؤسس مختلفًا عن بريد المؤسس.',
        'cofounder_phone_invalid'    => 'يرجى إدخال رقم هاتف صحيح للشريك المؤسس.',
        'cofounder_working_commitment_enum' => 'يجب أن تكون درجة التفرغ للشريك المؤسس Full-time أو Part-time.',

        // Company
        'startup_name_required'      => 'اسم الشركة الناشئة مطلوب.',
        'country_required'           => 'الدولة مطلوبة.',
        'state_belongs_to_country'   => 'يجب أن تكون المحافظة / الولاية تابعة للدولة المحددة.',
        'establishment_date_invalid' => 'يجب أن يكون تاريخ التأسيس تاريخًا صحيحًا.',
        'target_markets_invalid'     => 'يجب أن تحتوي الأسواق المستهدفة على دول صحيحة فقط.',
        'company_email_invalid'      => 'يرجى إدخال بريد إلكتروني صحيح للشركة.',
        'company_website_invalid'    => 'يرجى إدخال رابط موقع إلكتروني صحيح للشركة.',
        'company_facebook_invalid'   => 'يرجى إدخال رابط صفحة فيسبوك صحيح.',
        'company_logo_invalid'       => 'يجب أن يكون شعار الشركة صورة صحيحة.',
        'is_previously_incubated_enum' => 'يجب أن تكون قيمة سبق الاحتضان Yes أو No.',
        'prev_incubator_requires_flag' => 'لا يمكن إدخال الحاضنة السابقة عندما تكون قيمة سبق الاحتضان No.',
        'product_or_service_enum'    => 'يجب أن تكون قيمة المنتج أو الخدمة هي Products أو Services أو Products and Services.',
        'key_areas_warning'          => 'بعض مجالات الدعم / الإرشاد ليست ضمن القائمة المعتمدة.',
        'mvp_link_invalid'           => 'يرجى إدخال رابط MVP / Prototype صحيح.',

        // Employees
        'employees_non_numeric'      => 'يجب أن تكون حقول الموظفين أرقامًا فقط.',
        'male_employees_gt_total'    => 'لا يمكن أن يكون عدد الموظفين الذكور أكبر من إجمالي عدد الموظفين.',
        'female_employees_gt_total'  => 'لا يمكن أن يكون عدد الموظفات أكبر من إجمالي عدد الموظفين.',
        'male_plus_female_gt_total'  => 'لا يمكن أن يكون مجموع الموظفين الذكور والإناث أكبر من إجمالي عدد الموظفين.',

        // Financials
        'generate_revenue_enum'      => 'يجب أن تكون قيمة هل يحقق النشاط إيرادات هي Yes أو No.',
        'currency_required'          => 'العملة مطلوبة عند إدخال أي قيم مالية.',
        'currency_invalid'           => 'يرجى اختيار عملة صحيحة.',
        'numeric_required'           => 'يجب أن تكون قيمة :field رقمًا صحيحًا.',

        // Generic
        'field_required'             => 'الصف :row: حقل :field مطلوب.',
        'field_invalid'              => 'الصف :row: قيمة :field غير صحيحة.',
    ],

    'warnings' => [
        'founder_email_already_enrolled' => 'الصف :row: يوجد مؤسس بهذا البريد الإلكتروني مسجل بالفعل في برنامج آخر. إذا تابعت، سيتم تخطي تسجيله في هذا البرنامج.',
        'founder_phone_already_enrolled' => 'الصف :row: يوجد مؤسس بهذا الرقم مسجل بالفعل في برنامج آخر. إذا تابعت، سيتم تخطي تسجيله في هذا البرنامج.',
        'founder_email_exists_different_name' => 'الصف :row: هذا البريد الإلكتروني يطابق مؤسسًا موجودًا، لكن الاسم مختلف. إذا تابعت، سيتم تخطي تسجيله في هذا البرنامج.',
        'founder_phone_exists_different_name' => 'الصف :row: رقم الهاتف هذا يطابق مؤسسًا موجودًا، لكن الاسم مختلف. إذا تابعت، سيتم تخطي تسجيله في هذا البرنامج.',
        'founder_email_duplicate_in_upload' => 'الصف :row: هذا البريد الإلكتروني للمؤسس مستخدم بالفعل في صف سابق في هذا الملف. سيتم تخطي الصف المكرر.',
        'founder_phone_duplicate_in_upload' => 'الصف :row: رقم هاتف المؤسس هذا مستخدم بالفعل في صف سابق في هذا الملف. سيتم تخطي الصف المكرر.',
        'program_exists_different_dates' => 'الصف :row: يوجد برنامج بنفس الاسم والنوع بالفعل، لكن التواريخ مختلفة. إذا تابعت، سيتم إنشاء برنامج جديد بالتواريخ الجديدة.',
    ],

    'ui' => [
        'title'      => 'أداة استيراد الشركات الناشئة والمؤسسين',
        'step1'      => 'رفع ملف CSV',
        'step2'      => 'تعيين الأعمدة',
        'step3'      => 'المراجعة والاستيراد',
        'org_label'  => 'المنظمة الافتراضية',
        'org_help'   => 'تُستخدم فقط للصفوف التي لا يتطابق برنامجها مع برنامج موجود. إذا كان برنامج الصف موجودًا بالفعل، فسيتم استخدام منظمة ذلك البرنامج.',
        'mapped'     => 'حقول تم تعيينها',
        'actions'    => [
            'back'         => 'رجوع',
            'next'         => 'التالي',
            'auto_map'     => 'التعيين التلقائي',
            'download'     => 'تنزيل نموذج CSV',
            'replace_file' => 'استبدال الملف',
            'start_import' => 'بدء الاستيراد',
            'accept_next'  => 'قبول والتالي',
            'cancel'       => 'إلغاء',
            'start_new'    => 'بدء استيراد جديد',
        ],
        'dont_import'=> 'عدم الاستيراد',
        'automap_messages' => [
            'matched'   => 'تم ربط الحقول تلقائيًا.',
            'unmatched' => 'لم يتم العثور على رؤوس أعمدة مطابقة للربط التلقائي.',
            'required_missing' => 'لم يتم ربط هذا الحقل الإلزامي تلقائيًا. يرجى ربطه يدويًا.',
            'optional_missing' => 'لم يتم ربط هذا الحقل الاختياري تلقائيًا. يمكنك ربطه يدويًا أو تركه كـ عدم الاستيراد.',
        ],
        'results' => [
            'completed'           => 'تم الاستيراد بنجاح.',
            'completed_with_skipped' => 'اكتمل الاستيراد مع وجود بعض الصفوف التي تم تخطيها.',
            'failed'              => 'فشل الاستيراد. لم يتم استيراد أي صفوف.',
            'fatal'               => 'تعذر إكمال الاستيراد. يرجى المحاولة مرة أخرى.',
            'email_failed'        => 'تم الاستيراد بنجاح، ولكن تعذر إرسال البريد الإلكتروني: :reason.',
        ],
    ],
];
