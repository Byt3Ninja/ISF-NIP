<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Cycle;
use App\Organization;
use App\TimeSheet;
use App\Category;
use App\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
 

class ExportFTSData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $Tsheet;

    /**
     * Create a new job instance.
     */
    public function __construct($Tsheet)
    {
        $this->Tsheet = $Tsheet;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        
        $hours1 = $this->Tsheet['hours'];
        $condition1 = $this->Tsheet['condition'];
        $cycle_name = $this->Tsheet['cycle_name'];
        $user_id = $this->Tsheet['user_id'];
        $org_id = $this->Tsheet['org_id'];
        $userEmail = $this->Tsheet['userEmail'];
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'User');
        $sheet->setCellValue('C1', 'Total Count');
        $sheet->setCellValue('D1', 'Total Payment');

        // كتابة بيانات المستخدمين
        $row = 2; // بداية الصف الثاني
        $c = 1; // عداد الأسطر
        $l = app()->getLocale();


        $timeSheets = collect();
        $orgnization = collect();
        $authUser = auth()->user();

        if ($authUser->hasRole('Admin')) {
            $users = User::all();
            $timeSheets = TimeSheet::all();
            $cycles = Cycle::all();
            $orgnization = Organization::all();
        } elseif ($authUser->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $authUser->organizations_ids;
            $orgnization = Organization::whereIn('id', $orgIds)->get();
            $cycles = Cycle::whereIn('organization_id', $orgIds)->get();
            $users = User::whereIn('organization_id', $orgIds)->role(['manager', 'coordinator'])->get();
            $timeSheets = TimeSheet::whereIn('user_id', $users->pluck('id'))->get();
        } else {
            $timeSheets = TimeSheet::where('user_id', $authUser->id)->get();

            if ($authUser->hasRole('manager')) {
                $organization = Organization::where('user_id', $authUser->id)->first();
                if ($organization) {
                    $cycles = Cycle::where('organization_id', $organization->id)->get();
                    $users = User::where('organization_id', $organization->id)->role('coordinator')->get();
                    $orgnization = Organization::where('id', $organization->id)->get();
                }
            } elseif ($authUser->hasRole('coordinator')) {
                $users = collect();
            }
        }

        // فلترة حسب user_id
        if ($user_id && $user_id != null) {
            $timeSheets = $timeSheets->filter(function($timesheet) use ($user_id) {
                return $timesheet->user_id == $user_id;
            });
        }

        // فلترة حسب org_id
        if ($org_id && $org_id != null) {
            $userOrg = User::where('organization_id', $org_id)->pluck('id');
            $timeSheets = $timeSheets->filter(function($timesheet) use ($userOrg) {
                return $userOrg->contains($timesheet->user_id);
            });
        }

        // فلترة حسب اسم الدورة
        if ($cycle_name && $cycle_name != null) {
            $timeSheets = $timeSheets->where('program_id', $cycle_name);
        }

        $groupedTimeSheets = $timeSheets->load('user')->groupBy('user_id')->map(function ($timesheets) {
            return [
                'user' => $timesheets->first()->user,
                'total_hours' => $timesheets->sum('hours_worked'),
            ];
        });

        $groupedTimeSheets = $timeSheets->groupBy('user_id'); 
        if (!empty($hours1) || !empty($condition1)) {
            $hours = $hours1;
            $condition = $condition1;
        } else {
            $hours = 9999999;
            $condition = "<";
        }

        foreach ($groupedTimeSheets as $userId => $timesheets) {
            $user = $timesheets->first()->user;
            $totalHours = $timesheets->sum('hours_worked');
            $totalPayment = $user->houre_rate * $totalHours;
            
            if (
                ($condition == '>' && $totalPayment > $hours) ||
                ($condition == '<' && $totalPayment < $hours) ||
                ($condition == '=' && $totalPayment == $hours) ||
                ($condition == '>=' && $totalPayment >= $hours) ||
                ($condition == '<=' && $totalPayment <= $hours)
            ) {
                $sheet->setCellValue('A' . $row, $c++);
                $sheet->setCellValue('B' . $row, $user->{'first_name_'.$l} . " " . $user->{'last_name_'.$l});
                $sheet->setCellValue('C' . $row, $totalHours);
                $sheet->setCellValue('D' . $row, $totalPayment);
                $row++;
            }
        }

        // حفظ الملف
        $writer = new Xlsx($spreadsheet);

    // إنشاء اسم الملف مع تاريخ
    $fileName = 'Result_' . date('Y-m-d') . '.xlsx';
    $tempFilePath = storage_path('app/' . $fileName);

    // حفظ الملف في مجلد مؤقت
    $writer->save($tempFilePath);

    // إرسال الملف كمرفق عبر البريد الإلكتروني
    \Illuminate\Support\Facades\Notification::route('mail', $userEmail)
        ->notify(new \App\Notifications\ExportNotification($tempFilePath, $fileName));

    // حذف الملف المؤقت
    unlink($tempFilePath);

    return response()->json(['message' => 'Email sent successfully']);

    }
}
