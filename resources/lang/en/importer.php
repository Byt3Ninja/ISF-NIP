<?php

return [

    'errors' => [
        'ext_unsupported'    => 'Unsupported file format. Please upload a valid CSV file.',
        'empty_file'         => 'The uploaded file is empty.',
        'empty_headers_row'  => 'Main Header row is empty.',
        'missing_headers'    => 'Missing required column(s): :headers.',
        'duplicate_headers'  => 'Duplicate column name(s) found: :headers.',
        'too_large'          => 'File is too large. Maximum size is 10 MB.',
        'too_many_rows'      => 'File has too many rows. Maximum is 5,000 rows.',
        'binary_detected'    => 'File appears to be binary, not a CSV.',
        'mime_invalid'       => 'File MIME type is not a valid CSV.',
        'encoding_invalid'   => 'File encoding is not supported. Please save as UTF-8.',
        'rate_limited'       => 'Too many upload attempts. Please try again later.',
        'session_not_found'  => 'Importer session not found.',
        'invalid_state_transition' => 'This action is not allowed in the current step.',
        'organization_required'    => 'Organization is required.',
    ],

    'row_errors' => [
        // Program
        'program_name_required'      => 'Program name is required.',
        'program_type_required'      => 'Program type is required.',
        'program_type_enum'          => 'Program type must be General_Program or Hackathon.',
        'program_start_date_required'=> 'Program start date is required.',
        'program_start_date_invalid' => 'Program start date must be a valid date.',
        'program_end_date_required'  => 'Program end date is required.',
        'program_end_date_invalid'   => 'Program end date must be a valid date.',
        'program_dates_order'        => 'Program start date must be before or equal to program end date.',
        'program_exists_different_dates' => 'Row :row: A program with the same name and type already exists, but the dates are different.',

        // Founder
        'founder_first_name_required'=> 'Founder first name is required.',
        'founder_last_name_required' => 'Founder last name is required.',
        'founder_email_required'     => 'Founder email is required.',
        'founder_email_invalid'      => 'Enter a valid founder email address.',
        'founder_gender_required'    => 'Founder gender is required.',
        'founder_gender_enum'        => 'Founder gender must be Male or Female.',
        'founder_phone_required'     => 'Founder phone number is required.',
        'founder_phone_invalid'      => 'Enter a valid founder phone number.',
        'founder_country_code_required'=> 'Country code is required.',
        'founder_country_code_invalid' => 'Enter a valid country code.',
        'founder_working_commitment_enum' => 'Founder working commitment must be Full-time or Part-time.',
        'founder_educational_status_enum' => 'Founder educational status must be Graduate or Student.',
        'founder_dob_invalid'        => 'Founder date of birth must be a valid date.',
        'founder_dob_not_future'     => 'Founder date of birth cannot be later than today.',
        'founder_join_date_invalid'  => 'Founder join date must be a valid date.',

        // Co-Founder
        'cofounder_name_required'    => 'Co-founder name is required when co-founder details are entered.',
        'cofounder_email_required'   => 'Co-founder email is required when co-founder details are entered.',
        'cofounder_email_invalid'    => 'Enter a valid co-founder email address.',
        'cofounder_email_distinct'   => 'Co-founder email must be different from founder email.',
        'cofounder_phone_invalid'    => 'Enter a valid co-founder phone number.',
        'cofounder_working_commitment_enum' => 'Co-founder working commitment must be Full-time or Part-time.',

        // Company
        'startup_name_required'      => 'Startup name is required.',
        'country_required'           => 'Country is required.',
        'state_belongs_to_country'   => 'Governorate/State must belong to the selected country.',
        'establishment_date_invalid' => 'Establishment date must be a valid date.',
        'target_markets_invalid'     => 'Target markets must contain valid countries only.',
        'company_email_invalid'      => 'Enter a valid company email address.',
        'company_website_invalid'    => 'Enter a valid company website URL.',
        'company_facebook_invalid'   => 'Enter a valid Facebook page URL.',
        'company_logo_invalid'       => 'Company logo must be a valid image.',
        'is_previously_incubated_enum' => 'Is Previously Incubated must be Yes or No.',
        'prev_incubator_requires_flag' => 'Previous incubator cannot be entered when Is Previously Incubated is No.',
        'product_or_service_enum'    => 'Product or Service must be Products, Services, or Products and Services.',
        'key_areas_warning'          => 'Some support/mentorship areas are not in the allowed list.',
        'mvp_link_invalid'           => 'Enter a valid MVP/Prototype link.',

        // Employees
        'employees_non_numeric'      => 'Employee fields must be numeric values only.',
        'male_employees_gt_total'    => 'Male employees cannot be greater than total employees.',
        'female_employees_gt_total'  => 'Female employees cannot be greater than total employees.',
        'male_plus_female_gt_total'  => 'Male and female employees total cannot be greater than total employees.',

        // Financials
        'generate_revenue_enum'      => 'Does Business Generate Revenue must be Yes or No.',
        'currency_required'          => 'Currency is required when financial amounts are entered.',
        'currency_invalid'           => 'Select a valid currency.',
        'numeric_required'           => ':field must be a numeric value.',

        // Generic
        'field_required'             => 'Row :row: :field is required.',
        'field_invalid'              => 'Row :row: :field is invalid.',
    ],

    'warnings' => [
        'founder_email_already_enrolled' => 'Row :row: A founder with this email is already enrolled in a program. If you continue, this founder will be skipped from enrollment in this program.',
        'founder_phone_already_enrolled' => 'Row :row: A founder with this phone number is already enrolled in a program. If you continue, this founder will be skipped from enrollment in this program.',
        'founder_email_exists_different_name' => 'Row :row: This email matches an existing founder, but the name is different. If you continue, this founder will be skipped from enrollment in this program.',
        'founder_phone_exists_different_name' => 'Row :row: This phone number matches an existing founder, but the name is different. If you continue, this founder will be skipped from enrollment in this program.',
        'founder_email_duplicate_in_upload' => 'Row :row: This founder email is already used in an earlier row of this file. The duplicate row will be skipped.',
        'founder_phone_duplicate_in_upload' => 'Row :row: This founder phone number is already used in an earlier row of this file. The duplicate row will be skipped.',
        'program_exists_different_dates' => 'Row :row: A program with the same name and type already exists, but the dates are different. If you continue, a new program will be created with the new dates.',
    ],

    'ui' => [
        'title'      => 'Advanced Startup & Founder Importer',
        'step1'      => 'Upload CSV File',
        'step2'      => 'Map Columns',
        'step3'      => 'Review & Import',
        'org_label'  => 'Default Organization',
        'org_help'   => 'Used only for rows whose program does not match an existing one. If a row\'s program already exists, that program\'s organization is used.',
        'mapped'     => 'fields mapped',
        'actions'    => [
            'back'         => 'Back',
            'next'         => 'Next',
            'auto_map'     => 'Auto-map',
            'download'     => 'Download CSV template',
            'replace_file' => 'Replace file',
            'start_import' => 'Start Import',
            'accept_next'  => 'Accept and next',
            'cancel'       => 'Cancel',
            'start_new'    => 'Start new import',
        ],
        'dont_import'=> "Don't Import",
        'automap_messages' => [
            'matched'   => 'Fields were mapped automatically.',
            'unmatched' => 'No matching headers were found for auto mapping.',
            'required_missing' => 'This required field was not mapped automatically. Please map it manually.',
            'optional_missing' => 'This optional field was not mapped automatically. You can map it manually or leave it as Don\'t Import.',
        ],
        'results' => [
            'completed'           => 'Import completed successfully.',
            'completed_with_skipped' => 'Import completed with some skipped rows.',
            'failed'              => 'Import failed. No rows were imported.',
            'fatal'               => 'Import could not be completed. Please try again.',
            'email_failed'        => 'Imported successfully, but email could not be sent: :reason.',
        ],
    ],
];
