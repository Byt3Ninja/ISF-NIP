<?php

namespace App\Enums;

/**
 * The kinds of confidential upload. Each has its own private disk and its own
 * access rule (App\Services\ProtectedFiles\FileAccess).
 */
class ProtectedFileSet
{
    const COMPANY_LEGAL = 'company_legal';           // commercial register, tax card
    const COMPANY_PITCH = 'company_pitch';           // pitch deck, financial/business plan, supporting documents
    const FORM_ANSWER = 'form_answer';               // files attached to a program application
    const MILESTONE_DOCUMENT = 'milestone_document';
    const EMPLOYEE_DOCUMENT = 'employee_document';
    const TASK_ATTACHMENT = 'task_attachment';
    const PROGRAM_GUIDELINE = 'program_guideline';

    /** Sets a judge may open for a startup they were assigned to evaluate. */
    const JUDGE_VISIBLE = [self::COMPANY_PITCH, self::FORM_ANSWER];

    public static function all(): array
    {
        return [
            self::COMPANY_LEGAL,
            self::COMPANY_PITCH,
            self::FORM_ANSWER,
            self::MILESTONE_DOCUMENT,
            self::EMPLOYEE_DOCUMENT,
            self::TASK_ATTACHMENT,
            self::PROGRAM_GUIDELINE,
        ];
    }
}
