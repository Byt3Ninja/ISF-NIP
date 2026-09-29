<?php

return [
    'hackathon_invite' => [
        'whatsapp' => 'تمت دعوتك للانضمام إلى فريق الهاكاثون ":team" بواسطة :leader. تحقق من بريدك للمزيد من التفاصيل.',
        'broadcast' => 'تمت دعوتك للانضمام إلى فريق الهاكاثون ":team".',
    ],

    'hackathon_status' => [
        'accepted' => 'قبل :member دعوتك للانضمام إلى فريق الهاكاثون ":team".',
        'rejected' => 'رفض :member دعوتك للانضمام إلى فريق الهاكاثون ":team".',
        'cancelled' => 'تم إلغاء الدعوة للانضمام إلى فريق الهاكاثون ":team".',
    ],

    'startup_join' => [
        'whatsapp' => 'تهانينا! لقد انضممت إلى :cycle. أهلاً بك من :app.',
        'broadcast' => 'لقد انضممت إلى :cycle. أهلاً بك!',
    ],

    'welcome_judger' => [
        'subject'   => 'مرحباً بك كحكم!',
        'database'  => 'مرحباً بك في منصتنا كحكم!',
        'broadcast' => 'مرحباً بك في منصتنا كحكم! يسعدنا انضمامك.',
        'whatsapp'  => 'مرحباً، :name! تم إعداد حسابك كحكم بنجاح.',
    ],

    'welcome' => [
        'subject'   => 'مرحباً!',
        'database'  => 'مرحباً بك في منصتنا!',
        'broadcast' => 'مرحباً بك في منصتنا! يسعدنا انضمامك.',
        'whatsapp'  => 'مرحباً، :name! تم إعداد حسابك بنجاح.',
    ],

    'organization_manager_welcome' => [
        'database'  => 'مرحباً بك! تم إنشاء حسابك بصفتك مديراً للمنظمة :organization_name.',
        'broadcast' => 'مرحباً بك! تم إنشاء حسابك بصفتك مديراً للمنظمة :organization_name.',
    ],

    'organization_assigned' => [
        'database'  => 'لقد تم تعيينك في :organization_name.',
        'broadcast' => 'لقد تم تعيينك في :organization_name.',
    ],

    'mentorship_request' => [
        'whatsapp'  => 'لديك طلب إرشاد جديد من :startup.',
        'broadcast' => 'تم استلام طلب إرشاد جديد من :startup.',
        'database' => 'طلب :startup_name جلسة إرشاد جديدة مع :mentor_name بتاريخ :session_date من :start_time إلى :end_time. يرجى الموافقة على هذا الطلب أو رفضه.'
    ],

    'change_cycle' => [
        'subject'   => 'تحديث تقدّم شركتك - انتقال المرحلة',
        'database'  => 'تم تغيير المرحلة لـ :company من :from إلى :to بواسطة :org.',
        'broadcast' => ':company انتقلت إلى مرحلة :to.',
        'whatsapp'  => 'تحديث: :company انتقلت من :from إلى :to.',
    ],

    'new_training' => [
        'broadcast' => 'تم نشر تدريب جديد ":title".',
        'whatsapp'  => 'تدريب جديد متاح: :title.',
    ],

    'task' => [
        'broadcast'          => 'تحديث مهمة: :message',
        'whatsapp'           => 'تحديث مهمة: :message',
        'subject'            => 'تحديث مهمة',
        'subject_add'        => 'تمت إضافة مهمة',
        'subject_update'     => 'تم تحديث المهمة',
        'subject_archive'    => 'تمت أرشفة المهمة',
        'subject_reopen'     => 'تمت إعادة فتح المهمة',
        'subject_feedback'   => 'تحديث مراجعة المهمة',
    ],

    'mentorship_session' => [
        'broadcast' => 'جلسة إرشاد: :title',
        'whatsapp'  => ':greeting :line',
        'database' => 'إشعار جلسة إرشاد جديدة',
        'subject' => 'تحديث جلسة الإرشاد',
        'founder_booking' => 'لديك جلسة إرشاد جديدة مع :mentor_name بتاريخ :session_date في :start_time – :end_time (AM/PM).',
    ],

    'custom_email' => [
        'subject' => 'بيانات الدخول لمنصة نمو السياحة',
    ],

    'task_assigned' => [
        'subject' => 'مهمة مخصصة',
        'database' => 'تم تعيين مهمة جديدة لك',
        'broadcast' => 'مهمة جديدة مخصصة: :title',
        'whatsapp' => 'مهمة جديدة مخصصة: :title',
    ],

    'task_updated' => [
        'subject' => 'تم تحديث المهمة',
        'database' => 'تم تحديث مهمة',
        'broadcast' => 'تم تحديث المهمة: :title',
        'whatsapp' => 'تم تحديث المهمة: :title',
    ],

    'confirm_email_update' => [
        'subject'   => 'تأكيد تغيير البريد الإلكتروني',
        'broadcast' => 'برجاء تأكيد تغيير بريدك الإلكتروني.',
        'whatsapp'  => 'أكد تغيير بريدك الإلكتروني من خلال هذا الرابط: :url',
        'database'  => 'تم إرسال رسالة تأكيد تغيير البريد الإلكتروني.',
    ],
];
