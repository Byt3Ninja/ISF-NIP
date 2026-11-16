<?php

namespace App\Jobs;

use App\Company;
 
use App\TrainingContent;
use App\TrainingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportTresultData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data2;

    /**
     * Create a new job instance.
     */
    public function __construct($data2)
    {
        $this->data2 = $data2;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $step = $this->data2['step'];
        $location= $this->data2['location'];
        $userEmail = $this->data2['userEmail'];
        $Authuser = $this->data2['Authuser'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Company');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'User');
        $sheet->setCellValue('E1', 'Completed');
        $sheet->setCellValue('G1', 'Current Step');
        $sheet->setCellValue('H1', 'Time');

        // كتابة بيانات المستخدمين
        $row = 2; // بداية الصف الثاني

        if($Authuser->hasRole('Admin')){

            Company::with('user')->chunk(1000, function($companies) use (&$sheet, &$row, $location) {
                foreach ($companies as $companie) {
                    
                    $sheet->setCellValue('A' . $row, $companie->id);
                    $sheet->setCellValue('B' . $row, $companie->name);
                    $sheet->setCellValue('C' . $row, $companie->cycle->title);
                    $sheet->setCellValue('D' . $row, $companie->user->name);
                    $contents1 = TrainingContent::where('training_id', $location)->count();
                    if ($contents1 != 0) {$contents = $contents1;}else{$contents = 0;}
                    $cc = \App\TrainingCompleted::where('user_id',$companie->user->id)->where('course_id',$location)->count();
                    $per = $contents? $cc/$contents*100 :'0';
                    $sheet->setCellValue('E' . $row, (int)$per.'%');
                    $sheet->setCellValue('F' . $row, $companie->created_at);
                    $row++;
                    }
            });


        }else{

        Company::enrolledOnStep($step)->chunk(1000, function($companies) use (&$sheet, &$row, $location) {
        foreach ($companies as $companie) {
            
                    $sheet->setCellValue('A' . $row, $companie->id);
                    $sheet->setCellValue('B' . $row, $companie->name);
                    $sheet->setCellValue('C' . $row, $companie->user->title);
                    $sheet->setCellValue('D' . $row, $companie->user->name);
                    $contents1 = TrainingContent::where('training_id', $location)->count();
                    if ($contents1 != 0) {$contents = $contents1;}else{$contents = 0;}
                    $cc = \App\TrainingCompleted::where('user_id',$companie->user->id)->where('course_id',$location)->count();
                    $per = $contents? $cc/$contents*100 :'0';
                    $sheet->setCellValue('E' . $row, (int)$per.'%');
                    $sheet->setCellValue('F' . $row, $companie->created_at);
                    $row++;
                    }
    });

        }





        $writer = new Xlsx($spreadsheet);

    // إنشاء اسم الملف مع تاريخ
    $fileName = 'Training_Result_' . date('Y-m-d') . '.xlsx';
    $tempFilePath = storage_path('app/' . $fileName);

    // حفظ الملف في مجلد مؤقت
    $writer->save($tempFilePath);

    // إرسال الملف كمرفق عبر البريد الإلكتروني
    \Illuminate\Support\Facades\Notification::route('mail', auth()->user()->email)
        ->notify(new \App\Notifications\ExportNotification($tempFilePath, $fileName));

    // حذف الملف المؤقت
    unlink($tempFilePath);

    return response()->json(['message' => 'Email sent successfully']);
    }
}
