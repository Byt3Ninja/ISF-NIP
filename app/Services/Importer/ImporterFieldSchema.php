<?php

namespace App\Services\Importer;

/**
 * Single source of truth for all importable fields.
 *
 * Every consumer (template generator, mapping UI, auto-mapper, row validator,
 * row importer, generated docs) reads from this class. Adding a field is a
 * one-file edit.
 *
 * Field entry shape:
 *   [
 *     'key'              => 'founder_email',
 *     'label_en'         => 'Founder Email',
 *     'label_ar'         => 'البريد الإلكتروني للمؤسس',
 *     'section'          => 'founders',
 *     'required'         => true,
 *     'auto_map_aliases' => ['E-Mail', 'Founder Email', 'Email'],
 *     'validator'        => 'email',
 *     'allowed_values'   => null,  // for enum validators only
 *     'target'           => ['table' => 'users', 'column' => 'email'],
 *     'template_sample'  => 'founder1@example.com',
 *     'notes'            => null,
 *   ]
 */
class ImporterFieldSchema
{
    public const SECTION_PROGRAM     = 'program';
    public const SECTION_FOUNDERS    = 'founders';
    public const SECTION_CO_FOUNDERS = 'co_founders';
    public const SECTION_COMPANY     = 'company';
    public const SECTION_EMPLOYEES   = 'employees';
    public const SECTION_FINANCIALS  = 'financials';

    public const SECTION_ORDER = [
        self::SECTION_PROGRAM,
        self::SECTION_FOUNDERS,
        self::SECTION_CO_FOUNDERS,
        self::SECTION_COMPANY,
        self::SECTION_EMPLOYEES,
        self::SECTION_FINANCIALS,
    ];

    /** @return array<string, array> */
    public static function all(): array
    {
        static $fields = null;
        if ($fields !== null) { return $fields; }

        $fields = [
            // =====================================================================
            // PROGRAM section (4 fields, all required)
            // =====================================================================
            'program_name' => [
                'key' => 'program_name',
                'label_en' => 'Program Name',
                'label_ar' => 'اسم البرنامج',
                'section' => self::SECTION_PROGRAM,
                'required' => true,
                'auto_map_aliases' => ['Program Name', 'program name'],
                'validator' => 'required_string',
                'target' => ['table' => 'cycles', 'column' => 'title'],
                'template_sample' => 'Accelerator A',
                'notes' => 'Matched case-insensitively against existing cycles together with type + organization.',
            ],
            'program_type' => [
                'key' => 'program_type',
                'label_en' => 'Program Type',
                'label_ar' => 'نوع البرنامج',
                'section' => self::SECTION_PROGRAM,
                'required' => true,
                'auto_map_aliases' => ['Program Type', 'Type', 'type'],
                'validator' => 'program_type_enum',
                'allowed_values' => ['General_Program', 'Hackathon'],
                'target' => ['table' => 'cycles', 'column' => 'type'],
                'template_sample' => 'General_Program',
                'notes' => null,
            ],
            'program_start_date' => [
                'key' => 'program_start_date',
                'label_en' => 'Program Start Date',
                'label_ar' => 'تاريخ بداية البرنامج',
                'section' => self::SECTION_PROGRAM,
                'required' => true,
                'auto_map_aliases' => ['Program Start Date', 'Start Date'],
                'validator' => 'date_mdy',
                'target' => ['table' => 'cycles', 'column' => 'start'],
                'template_sample' => '4/1/2026',
                'notes' => 'Format: M/D/YYYY (per spec GV-2075).',
            ],
            'program_end_date' => [
                'key' => 'program_end_date',
                'label_en' => 'Program End Date',
                'label_ar' => 'تاريخ نهاية البرنامج',
                'section' => self::SECTION_PROGRAM,
                'required' => true,
                'auto_map_aliases' => ['Program End Date', 'End Date'],
                'validator' => 'date_mdy',
                'target' => ['table' => 'cycles', 'column' => 'end'],
                'template_sample' => '7/31/2026',
                'notes' => null,
            ],

            // =====================================================================
            // FOUNDERS section (10 fields, 6 required, 4 optional)
            // =====================================================================
            'founder_first_name' => [
                'key' => 'founder_first_name',
                'label_en' => 'Founder First Name',
                'label_ar' => 'الاسم الأول للمؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => true,
                'auto_map_aliases' => ['Founder First Name', 'Founder Name', 'First Name'],
                'validator' => 'required_string',
                'target' => ['table' => 'users', 'column' => 'first_name_en'],
                'template_sample' => 'Alex',
                'notes' => 'Spec auto-map treats "Founder Name" and "Founder First Name" as aliases.',
            ],
            'founder_last_name' => [
                'key' => 'founder_last_name',
                'label_en' => 'Founder Last Name',
                'label_ar' => 'اسم العائلة للمؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => true,
                'auto_map_aliases' => ['Founder Last Name', 'Founder\'s Last Name', 'Last Name'],
                'validator' => 'required_string',
                'target' => ['table' => 'users', 'column' => 'last_name_en'],
                'template_sample' => 'Morgan',
                'notes' => null,
            ],
            'founder_email' => [
                'key' => 'founder_email',
                'label_en' => 'Founder Email',
                'label_ar' => 'البريد الإلكتروني للمؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => true,
                'auto_map_aliases' => ['Founder Email', 'E-Mail', 'Email'],
                'validator' => 'email',
                'target' => ['table' => 'users', 'column' => 'email'],
                'template_sample' => 'alex.morgan@example.com',
                'notes' => null,
            ],
            'founder_gender' => [
                'key' => 'founder_gender',
                'label_en' => 'Founder Gender',
                'label_ar' => 'نوع المؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => true,
                'auto_map_aliases' => ['Founder Gender', 'Gender'],
                'validator' => 'gender_enum',
                'allowed_values' => ['Male', 'Female'],
                'target' => ['table' => 'users', 'column' => 'gender'],
                'template_sample' => 'Male',
                'notes' => null,
            ],
            'founder_phone' => [
                'key' => 'founder_phone',
                'label_en' => 'Founder Phone',
                'label_ar' => 'رقم هاتف المؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => true,
                'auto_map_aliases' => ['Founder Phone', 'Phone'],
                'validator' => 'phone',
                'target' => ['table' => 'users', 'column' => 'phone'],
                'template_sample' => '1188376532',
                'notes' => null,
            ],
            'founder_country_code' => [
                'key' => 'founder_country_code',
                'label_en' => 'Country Code',
                'label_ar' => 'رمز الدولة',
                'section' => self::SECTION_FOUNDERS,
                'required' => true,
                'auto_map_aliases' => ['Country Code', 'country code'],
                'validator' => 'country_code',
                'target' => ['table' => 'users', 'column' => 'country_code'],
                'template_sample' => '20',
                'notes' => null,
            ],
            'founder_working_commitment' => [
                'key' => 'founder_working_commitment',
                'label_en' => 'Founder Working Commitment',
                'label_ar' => 'درجة التفرغ للمؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Founder Working Commitment'],
                'validator' => 'working_commitment_enum',
                'allowed_values' => ['Full-time', 'Part-time'],
                'target' => ['table' => 'team_members', 'column' => 'working_commitment', 'team_member' => 'Founder'],
                'template_sample' => 'Full-time',
                'notes' => null,
            ],
            'founder_national_id' => [
                'key' => 'founder_national_id',
                'label_en' => 'Founder National ID',
                'label_ar' => 'الرقم القومي للمؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Founder National ID', 'National ID'],
                'validator' => 'string',
                'target' => ['table' => 'team_members', 'column' => 'national_id', 'team_member' => 'Founder'],
                'template_sample' => '29001011234567',
                'notes' => 'Column added in PR 7 migration if not already present.',
            ],
            'founder_educational_status' => [
                'key' => 'founder_educational_status',
                'label_en' => 'Founder Educational Status',
                'label_ar' => 'الحالة التعليمية للمؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Founder Educational Status', "Founder's Educational Status"],
                'validator' => 'educational_status_enum',
                'allowed_values' => ['Graduate', 'Student'],
                'target' => ['table' => 'team_members', 'column' => 'educational_status', 'team_member' => 'Founder'],
                'template_sample' => 'Graduate',
                'notes' => null,
            ],
            'founder_date_of_birth' => [
                'key' => 'founder_date_of_birth',
                'label_en' => 'Founder Date of Birth',
                'label_ar' => 'تاريخ ميلاد المؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Founder Date of Birth', 'Date of Birth'],
                'validator' => 'date_mdy_past',
                'target' => ['table' => 'team_members', 'column' => 'date_of_birth', 'team_member' => 'Founder'],
                'template_sample' => '5/20/1990',
                'notes' => 'Must not be in the future.',
            ],
            'founder_join_date' => [
                'key' => 'founder_join_date',
                'label_en' => "Founder's Join Date",
                'label_ar' => 'تاريخ انضمام المؤسس',
                'section' => self::SECTION_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ["Founder's Join Date", 'Founder Join Date', 'Join Date'],
                'validator' => 'date_mdy',
                'target' => ['table' => 'team_members', 'column' => 'join_date', 'team_member' => 'Founder'],
                'template_sample' => '1/15/2024',
                'notes' => null,
            ],

            // =====================================================================
            // CO-FOUNDERS section (5 fields, conditionally required)
            // =====================================================================
            'cofounder_name' => [
                'key' => 'cofounder_name',
                'label_en' => 'Co-Founder Name',
                'label_ar' => 'اسم الشريك المؤسس',
                'section' => self::SECTION_CO_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Co-Founder Name', 'CoFounder Name'],
                'validator' => 'string',
                'target' => ['table' => 'team_members', 'column' => 'Name', 'team_member' => 'Co-founder'],
                'template_sample' => 'Sam Rivera',
                'notes' => 'Required when any other co-founder field is populated.',
            ],
            'cofounder_email' => [
                'key' => 'cofounder_email',
                'label_en' => 'Co-Founder Email',
                'label_ar' => 'البريد الإلكتروني للشريك المؤسس',
                'section' => self::SECTION_CO_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Co-Founder Email'],
                'validator' => 'email',
                'target' => ['table' => 'team_members', 'column' => 'Email', 'team_member' => 'Co-founder'],
                'template_sample' => 'sam.rivera@example.com',
                'notes' => 'Required when any other co-founder field is populated. Must differ from founder email.',
            ],
            'cofounder_phone' => [
                'key' => 'cofounder_phone',
                'label_en' => 'Co-Founder Phone Number',
                'label_ar' => 'رقم هاتف الشريك المؤسس',
                'section' => self::SECTION_CO_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Co-Founder Phone Number', 'Co-Founder Phone'],
                'validator' => 'phone',
                'target' => ['table' => 'team_members', 'column' => 'phone', 'team_member' => 'Co-founder'],
                'template_sample' => '1058774436',
                'notes' => null,
            ],
            'cofounder_working_commitment' => [
                'key' => 'cofounder_working_commitment',
                'label_en' => 'Co-Founder Working Commitment',
                'label_ar' => 'درجة التفرغ للشريك المؤسس',
                'section' => self::SECTION_CO_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Co-Founder Working Commitment'],
                'validator' => 'working_commitment_enum',
                'allowed_values' => ['Full-time', 'Part-time'],
                'target' => ['table' => 'team_members', 'column' => 'working_commitment', 'team_member' => 'Co-founder'],
                'template_sample' => 'Part-time',
                'notes' => null,
            ],
            'cofounder_educational_status' => [
                'key' => 'cofounder_educational_status',
                'label_en' => 'Co-Founder Educational Status',
                'label_ar' => 'الحالة التعليمية للشريك المؤسس',
                'section' => self::SECTION_CO_FOUNDERS,
                'required' => false,
                'auto_map_aliases' => ['Co-Founder Educational Status'],
                'validator' => 'educational_status_enum',
                'allowed_values' => ['Graduate', 'Student'],
                'target' => ['table' => 'team_members', 'column' => 'educational_status', 'team_member' => 'Co-founder'],
                'template_sample' => 'Graduate',
                'notes' => null,
            ],

            // =====================================================================
            // COMPANY section (24 fields, 2 required)
            // =====================================================================
            'startup_name' => [
                'key' => 'startup_name',
                'label_en' => 'Startup Name',
                'label_ar' => 'اسم الشركة الناشئة',
                'section' => self::SECTION_COMPANY,
                'required' => true,
                'auto_map_aliases' => ['Startup Name', 'Company Name'],
                'validator' => 'required_string',
                'target' => ['table' => 'companies', 'column' => 'name_en'],
                'template_sample' => 'Acme Robotics',
                'notes' => null,
            ],
            'country' => [
                'key' => 'country',
                'label_en' => 'Country',
                'label_ar' => 'الدولة',
                'section' => self::SECTION_COMPANY,
                'required' => true,
                'auto_map_aliases' => ['Country'],
                'validator' => 'country_name',
                'target' => ['table' => 'companies', 'column' => 'country_id'],
                'template_sample' => 'Egypt',
                'notes' => 'Looked up by name_en in countries; new entries auto-created.',
            ],
            'governorate' => [
                'key' => 'governorate',
                'label_en' => 'Governorate/State',
                'label_ar' => 'المحافظة/الولاية',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Governorate', 'Governorate/State', 'State'],
                'validator' => 'state_name_in_country',
                'target' => ['table' => 'companies', 'column' => 'state_id'],
                'template_sample' => 'Cairo',
                'notes' => 'Must belong to the Country value on the same row.',
            ],
            'industry' => [
                'key' => 'industry',
                'label_en' => 'Industry',
                'label_ar' => 'الصناعة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Industry', 'industry', 'industries'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'industries'],
                'template_sample' => 'Technology',
                'notes' => null,
            ],
            'sector' => [
                'key' => 'sector',
                'label_en' => 'Sector',
                'label_ar' => 'القطاع',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Sector'],
                'validator' => 'sector_name',
                'target' => ['table' => 'companies', 'column' => 'sector'],
                'template_sample' => 'Software',
                'notes' => null,
            ],
            'address' => [
                'key' => 'address',
                'label_en' => 'Address',
                'label_ar' => 'العنوان',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Address'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'address_en'],
                'template_sample' => '123 Main St',
                'notes' => null,
            ],
            'establishment_date' => [
                'key' => 'establishment_date',
                'label_en' => 'Establishment Date',
                'label_ar' => 'تاريخ التأسيس',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Establishment Date', 'Establishment date'],
                'validator' => 'date_mdy',
                'target' => ['table' => 'companies', 'column' => 'est_date'],
                'template_sample' => '1/15/2020',
                'notes' => null,
            ],
            'years_of_operations' => [
                'key' => 'years_of_operations',
                'label_en' => 'Years of Operations',
                'label_ar' => 'سنوات التشغيل',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Years of Operations'],
                'validator' => 'years_bucket_enum',
                'allowed_values' => ['0-2', '2-5', '5-10', '10+'],
                'target' => ['table' => 'companies', 'column' => 'years_of_operations'],
                'template_sample' => '2-5',
                'notes' => 'Accepts a bucket (0-2, 2-5, 5-10, 10+) or a plain number that will be bucketed automatically.',
            ],
            'target_markets' => [
                'key' => 'target_markets',
                'label_en' => 'Target Markets',
                'label_ar' => 'الأسواق المستهدفة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Target Markets'],
                'validator' => 'target_markets',
                'target' => ['table' => 'companies', 'column' => 'targeted_market'],
                'template_sample' => 'Egypt,Saudi Arabia',
                'notes' => 'Comma-separated list of country names.',
            ],
            'company_registration_type' => [
                'key' => 'company_registration_type',
                'label_en' => 'Company Registration Type',
                'label_ar' => 'نوع تسجيل الشركة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Company Registration Type'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'company_registration_type'],
                'template_sample' => 'Limited Liability Company (LLC)',
                'notes' => null,
            ],
            'company_email' => [
                'key' => 'company_email',
                'label_en' => 'Company Email',
                'label_ar' => 'البريد الإلكتروني للشركة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Company Email', 'Company email'],
                'validator' => 'email',
                'target' => ['table' => 'companies', 'column' => 'email'],
                'template_sample' => 'contact@acme.example',
                'notes' => null,
            ],
            'company_website' => [
                'key' => 'company_website',
                'label_en' => 'Company Website',
                'label_ar' => 'موقع الشركة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Company Website', 'Company website'],
                'validator' => 'url',
                'target' => ['table' => 'companies', 'column' => 'website'],
                'template_sample' => 'https://acme.example',
                'notes' => null,
            ],
            'company_facebook' => [
                'key' => 'company_facebook',
                'label_en' => 'Company Facebook Page',
                'label_ar' => 'صفحة فيسبوك للشركة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Company Facebook Page', 'Company Facebook page'],
                'validator' => 'url',
                'target' => ['table' => 'companies', 'column' => 'facebook'],
                'template_sample' => 'https://facebook.com/acme',
                'notes' => null,
            ],
            'company_logo' => [
                'key' => 'company_logo',
                'label_en' => 'Company Logo',
                'label_ar' => 'شعار الشركة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Company Logo', 'Company logo'],
                'validator' => 'image_url',
                'target' => ['table' => 'companies', 'column' => 'logo'],
                'template_sample' => 'https://acme.example/logo.png',
                'notes' => 'URL only — actual upload is out of scope for CSV import.',
            ],
            'is_previously_incubated' => [
                'key' => 'is_previously_incubated',
                'label_en' => 'Is Previously Incubated',
                'label_ar' => 'سبق الاحتضان',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Is Previously Incubated'],
                'validator' => 'yes_no',
                'allowed_values' => ['Yes', 'No'],
                'target' => ['table' => 'companies', 'column' => 'previous_incubation'],
                'template_sample' => 'No',
                'notes' => 'Stored as 0/1; parser converts Yes/No.',
            ],
            'previous_incubator' => [
                'key' => 'previous_incubator',
                'label_en' => 'Previous Incubator',
                'label_ar' => 'الحاضنة السابقة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Previous Incubator'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'previous_incubator'],
                'template_sample' => '',
                'notes' => 'Must be empty when Is Previously Incubated = No (cross-field rule).',
            ],
            'achievements' => [
                'key' => 'achievements',
                'label_en' => 'Achievements',
                'label_ar' => 'الإنجازات',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Achievements'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'achievements'],
                'template_sample' => 'Launched beta with 500 users.',
                'notes' => null,
            ],
            'mission' => [
                'key' => 'mission',
                'label_en' => 'Mission',
                'label_ar' => 'الرسالة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Mission'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'mission'],
                'template_sample' => 'Automate warehouse logistics.',
                'notes' => null,
            ],
            'product_or_service' => [
                'key' => 'product_or_service',
                'label_en' => 'Product or Service',
                'label_ar' => 'المنتج أو الخدمة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Product or Service'],
                'validator' => 'products_services_enum',
                'allowed_values' => ['Product', 'Service', 'Product + Service'],
                'target' => ['table' => 'companies', 'column' => 'product_or_service'],
                'template_sample' => 'Product',
                'notes' => null,
            ],
            'product_service_description' => [
                'key' => 'product_service_description',
                'label_en' => 'Product/Service Description',
                'label_ar' => 'وصف المنتج/الخدمة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Product/Service Description'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'products_services_description'],
                'template_sample' => 'Autonomous picking robots.',
                'notes' => null,
            ],
            'why_this_startup' => [
                'key' => 'why_this_startup',
                'label_en' => 'Why This Startup',
                'label_ar' => 'لماذا هذه الشركة الناشئة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Why This Startup'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'why_this_startup'],
                'template_sample' => 'Deep warehouse domain expertise.',
                'notes' => null,
            ],
            'startup_expertise' => [
                'key' => 'startup_expertise',
                'label_en' => 'Startup Expertise',
                'label_ar' => 'خبرة الشركة الناشئة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Startup Expertise', 'Startup expertise', 'Expertise'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'startup_expertise'],
                'template_sample' => 'Robotics, ML',
                'notes' => null,
            ],
            'key_areas' => [
                'key' => 'key_areas',
                'label_en' => 'Key Areas Needing Support/Mentorship Help',
                'label_ar' => 'مجالات الدعم/الإرشاد المطلوبة',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['Key Areas Needing Support/Mentorship Help'],
                'validator' => 'string',
                'target' => ['table' => 'companies', 'column' => 'hear_about_us'],
                'template_sample' => 'Marketing, Fundraising',
                'notes' => 'Warning-only when value not in allowed list (warning, not block).',
            ],
            'mvp_link' => [
                'key' => 'mvp_link',
                'label_en' => 'MVP/Prototype Link',
                'label_ar' => 'رابط النموذج الأولي',
                'section' => self::SECTION_COMPANY,
                'required' => false,
                'auto_map_aliases' => ['MVP/Prototype Link', 'MVP Link', 'Prototype Link'],
                'validator' => 'url',
                'target' => ['table' => 'companies', 'column' => 'mvp_prototype_link'],
                'template_sample' => 'https://acme.example/demo',
                'notes' => null,
            ],

            // =====================================================================
            // EMPLOYEES section (3 fields, all optional, cross-field validated)
            // =====================================================================
            'total_employees' => [
                'key' => 'total_employees',
                'label_en' => 'Total Employees',
                'label_ar' => 'إجمالي الموظفين',
                'section' => self::SECTION_EMPLOYEES,
                'required' => false,
                'auto_map_aliases' => ['Total Employees'],
                'validator' => 'integer',
                'target' => ['table' => 'companies', 'column' => 'total_employees'],
                'template_sample' => '12',
                'notes' => null,
            ],
            'male_employees' => [
                'key' => 'male_employees',
                'label_en' => 'Male Employees',
                'label_ar' => 'الموظفون الذكور',
                'section' => self::SECTION_EMPLOYEES,
                'required' => false,
                'auto_map_aliases' => ['Male Employees'],
                'validator' => 'integer',
                'target' => ['table' => 'companies', 'column' => 'male_employees'],
                'template_sample' => '8',
                'notes' => 'Cross-field: ≤ Total Employees.',
            ],
            'female_employees' => [
                'key' => 'female_employees',
                'label_en' => 'Female Employees',
                'label_ar' => 'الموظفات الإناث',
                'section' => self::SECTION_EMPLOYEES,
                'required' => false,
                'auto_map_aliases' => ['Female Employees'],
                'validator' => 'integer',
                'target' => ['table' => 'companies', 'column' => 'female_employees'],
                'template_sample' => '4',
                'notes' => 'Cross-field: ≤ Total Employees; Male+Female ≤ Total.',
            ],

            // =====================================================================
            // FINANCIALS section (11 fields, all optional, cross-field validated)
            // =====================================================================
            'currency' => [
                'key' => 'currency',
                'label_en' => 'Currency',
                'label_ar' => 'العملة',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Currency'],
                'validator' => 'currency_code',
                'target' => ['table' => 'financial_projections', 'column' => 'currency', 'year_offset' => 0],
                'template_sample' => 'USD',
                'notes' => 'Required when any financial amount is entered (cross-field).',
            ],
            'generate_revenue' => [
                'key' => 'generate_revenue',
                'label_en' => 'Does Business Generate Revenue',
                'label_ar' => 'هل يحقق النشاط إيرادات',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Does Business Generate Revenue'],
                'validator' => 'yes_no',
                'allowed_values' => ['Yes', 'No'],
                'target' => ['table' => 'companies', 'column' => 'generate_revenue'],
                'template_sample' => 'Yes',
                'notes' => null,
            ],
            'average_annual_revenue' => [
                'key' => 'average_annual_revenue',
                'label_en' => 'Average Annual Revenue',
                'label_ar' => 'متوسط الإيرادات السنوية',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Average Annual Revenue'],
                'validator' => 'numeric',
                'target' => ['table' => 'companies', 'column' => 'average_annual_revenues'],
                'template_sample' => '120000',
                'notes' => null,
            ],
            'current_operating_income' => [
                'key' => 'current_operating_income',
                'label_en' => 'Current Operating Income (EBIT)',
                'label_ar' => 'الدخل التشغيلي الحالي',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Current Operating Income (EBIT)', 'Current Operating Income'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'ebit', 'year_offset' => 0],
                'template_sample' => '25000',
                'notes' => null,
            ],
            'forecast_operating_income_y1' => [
                'key' => 'forecast_operating_income_y1',
                'label_en' => 'Forecast Operating Income Year 1 (EBIT)',
                'label_ar' => 'توقع الدخل التشغيلي للسنة 1',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Forecast Operating Income Year 1 (EBIT)', 'Forecast Operating Income Year 1'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'ebit', 'year_offset' => 1],
                'template_sample' => '45000',
                'notes' => null,
            ],
            'forecast_operating_income_y2' => [
                'key' => 'forecast_operating_income_y2',
                'label_en' => 'Forecast Operating Income Year 2 (EBIT)',
                'label_ar' => 'توقع الدخل التشغيلي للسنة 2',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Forecast Operating Income Year 2 (EBIT)', 'Forecast Operating Income Year 2'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'ebit', 'year_offset' => 2],
                'template_sample' => '80000',
                'notes' => null,
            ],
            'forecast_operating_income_y3' => [
                'key' => 'forecast_operating_income_y3',
                'label_en' => 'Forecast Operating Income Year 3 (EBIT)',
                'label_ar' => 'توقع الدخل التشغيلي للسنة 3',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Forecast Operating Income Year 3 (EBIT)', 'Forecast Operating Income Year 3'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'ebit', 'year_offset' => 3],
                'template_sample' => '120000',
                'notes' => null,
            ],
            'current_revenue' => [
                'key' => 'current_revenue',
                'label_en' => 'Current Revenue',
                'label_ar' => 'الإيرادات الحالية',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Current Revenue'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'revenues', 'year_offset' => 0],
                'template_sample' => '100000',
                'notes' => null,
            ],
            'forecast_revenue_y1' => [
                'key' => 'forecast_revenue_y1',
                'label_en' => 'Forecast Revenue Year 1',
                'label_ar' => 'توقع الإيرادات للسنة 1',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Forecast Revenue Year 1'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'revenues', 'year_offset' => 1],
                'template_sample' => '150000',
                'notes' => null,
            ],
            'forecast_revenue_y2' => [
                'key' => 'forecast_revenue_y2',
                'label_en' => 'Forecast Revenue Year 2',
                'label_ar' => 'توقع الإيرادات للسنة 2',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Forecast Revenue Year 2'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'revenues', 'year_offset' => 2],
                'template_sample' => '200000',
                'notes' => null,
            ],
            'forecast_revenue_y3' => [
                'key' => 'forecast_revenue_y3',
                'label_en' => 'Forecast Revenue Year 3',
                'label_ar' => 'توقع الإيرادات للسنة 3',
                'section' => self::SECTION_FINANCIALS,
                'required' => false,
                'auto_map_aliases' => ['Forecast Revenue Year 3'],
                'validator' => 'numeric',
                'target' => ['table' => 'financial_projections', 'column' => 'revenues', 'year_offset' => 3],
                'template_sample' => '280000',
                'notes' => null,
            ],
        ];

        return $fields;
    }

    /** @return array<string, array> */
    public static function required(): array
    {
        return array_filter(self::all(), fn($f) => $f['required'] === true);
    }

    /** @return array<string, array<string, array>> */
    public static function bySection(): array
    {
        $grouped = array_fill_keys(self::SECTION_ORDER, []);
        foreach (self::all() as $key => $field) {
            $grouped[$field['section']][$key] = $field;
        }
        return $grouped;
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** @return array<string, string> lowercased_alias => field_key */
    public static function allAliases(): array
    {
        $aliases = [];
        foreach (self::all() as $key => $field) {
            $candidates = array_merge(
                [$field['label_en'], $field['label_ar']],
                $field['auto_map_aliases'] ?? []
            );
            foreach ($candidates as $alias) {
                $normalised = self::normaliseAlias($alias);
                if ($normalised === '') {
                    continue;
                }
                // First-wins on collision (order of fields in all() determines precedence)
                if (!isset($aliases[$normalised])) {
                    $aliases[$normalised] = $key;
                }
            }
        }
        return $aliases;
    }

    public static function crossFieldRules(): array
    {
        return [
            'program_dates_order' => [
                'fields' => ['program_start_date', 'program_end_date'],
                'description' => 'program start date must be <= program end date',
            ],
            'cofounder_requires_name' => [
                'fields' => ['cofounder_email', 'cofounder_phone', 'cofounder_working_commitment', 'cofounder_educational_status', 'cofounder_name'],
                'description' => 'if any co-founder field is populated, cofounder_name must be present',
            ],
            'cofounder_requires_email' => [
                'fields' => ['cofounder_name', 'cofounder_phone', 'cofounder_working_commitment', 'cofounder_educational_status', 'cofounder_email'],
                'description' => 'if any co-founder field is populated, cofounder_email must be present',
            ],
            'cofounder_email_distinct' => [
                'fields' => ['founder_email', 'cofounder_email'],
                'description' => 'co-founder email must differ from founder email',
            ],
            'state_belongs_to_country' => [
                'fields' => ['country', 'governorate'],
                'description' => 'governorate must belong to the selected country',
            ],
            'prev_incubator_requires_flag' => [
                'fields' => ['is_previously_incubated', 'previous_incubator'],
                'description' => 'previous_incubator must be empty when is_previously_incubated = No',
            ],
            'male_employees_le_total' => [
                'fields' => ['total_employees', 'male_employees'],
                'description' => 'male_employees <= total_employees',
            ],
            'female_employees_le_total' => [
                'fields' => ['total_employees', 'female_employees'],
                'description' => 'female_employees <= total_employees',
            ],
            'male_plus_female_le_total' => [
                'fields' => ['total_employees', 'male_employees', 'female_employees'],
                'description' => 'male+female <= total_employees',
            ],
            'financials_require_currency' => [
                'fields' => [
                    'average_annual_revenue', 'current_operating_income',
                    'forecast_operating_income_y1', 'forecast_operating_income_y2', 'forecast_operating_income_y3',
                    'current_revenue', 'forecast_revenue_y1', 'forecast_revenue_y2', 'forecast_revenue_y3',
                    'currency',
                ],
                'description' => 'currency is required when any financial amount is entered',
            ],
            'founder_dob_not_future' => [
                'fields' => ['founder_date_of_birth'],
                'description' => 'founder date of birth cannot be later than today',
            ],
        ];
    }

    /** @return string[] ordered CSV header row for the downloadable template */
    public static function templateHeaders(): array
    {
        return array_values(array_map(fn($f) => $f['label_en'], self::all()));
    }

    /** @return array<int, array<int, string>> two example data rows */
    public static function templateSampleRows(): array
    {
        // Row 1: General_Program. Row 2: Hackathon with co-founder.
        $row1 = [];
        $row2 = [];
        foreach (self::all() as $key => $field) {
            $row1[] = $field['template_sample'];
            $row2[] = $field['template_sample'];
        }

        // Index by field key for targeted overrides
        $keys = array_keys(self::all());
        $idx = array_flip($keys);

        // Row 2 overrides (Hackathon + different founder + co-founder populated)
        $row2[$idx['program_name']]        = 'Hackathon Spring 2026';
        $row2[$idx['program_type']]        = 'Hackathon';
        $row2[$idx['program_start_date']]  = '5/1/2026';
        $row2[$idx['program_end_date']]    = '5/3/2026';
        $row2[$idx['founder_first_name']]  = 'Jordan';
        $row2[$idx['founder_last_name']]   = 'Lee';
        $row2[$idx['founder_email']]       = 'jordan.lee@example.com';
        $row2[$idx['founder_gender']]      = 'Female';
        $row2[$idx['founder_phone']]       = '1058774436';
        $row2[$idx['startup_name']]        = 'Helix Health';
        $row2[$idx['country']]             = 'Saudi Arabia';
        $row2[$idx['governorate']]         = 'Riyadh';
        $row2[$idx['industry']]            = 'Healthcare';
        $row2[$idx['sector']]              = 'MedTech';
        // Row 1 (General_Program) clears co-founder fields; row 2 (Hackathon) keeps them as sample demo
        $row1[$idx['cofounder_name']]      = '';
        $row1[$idx['cofounder_email']]     = '';
        $row1[$idx['cofounder_phone']]     = '';
        $row1[$idx['cofounder_working_commitment']]  = '';
        $row1[$idx['cofounder_educational_status']]  = '';

        return [$row1, $row2];
    }

    public static function normaliseAlias(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($s));
        $s = mb_strtolower($s, 'UTF-8');
        return preg_replace('/[^a-z0-9\p{Arabic}\s]/u', '', $s);
    }
}
