<?php

namespace App\Jobs;

use App\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;


class ExportCycleCompData implements ShouldQueue
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
        $userEmail = $this->data2['userEmail'];

        $companies = Company::enrolledIn($step)->get();
       
        

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Company');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'User');
        $sheet->setCellValue('E1', 'Mobile');
        $sheet->setCellValue('F1', 'E-Mail');
        $sheet->setCellValue('G1', 'Current Step');
        $sheet->setCellValue('H1', 'Created_At');

        // كتابة بيانات المستخدمين
        $row = 2; // بداية الصف الثاني

        if(auth()->user()->hasRole('Admin')) {

            Company::enrolledIn($step)->chunk(1000, function($companies) use (&$sheet, &$row) {
                foreach ($companies as $companie) {
                    
                    $sheet->setCellValue('A' . $row, $companie->id);
                    $sheet->setCellValue('B' . $row, $companie->name);
                    $sheet->setCellValue('C' . $row, $companie->user->title);
                    $sheet->setCellValue('D' . $row, $companie->user->name);
                    $sheet->setCellValue('E' . $row, $companie->user->phone);
                    $sheet->setCellValue('F' . $row, $companie->user->email);
                    foreach(\App\Step::where('cycle_id',$companie->cycle)->orderBy('arr')->get() as $step){
                        $sheet->setCellValue('G' . $row, $step->title);
                    }
                    $sheet->setCellValue('H' . $row, $companie->created_at);
                    $row++;
                }
            });


        }else{
        Company::enrolledIn($step)->chunk(1000, function($companies) use (&$sheet, &$row) {
        foreach ($companies as $companie) {
            
            $sheet->setCellValue('A' . $row, $companie->id);
            $sheet->setCellValue('B' . $row, $companie->name);
            $sheet->setCellValue('C' . $row, $companie->user->title);
            $sheet->setCellValue('D' . $row, $companie->user->name);
            $sheet->setCellValue('E' . $row, $companie->user->phone);
            $sheet->setCellValue('F' . $row, $companie->user->email);
            foreach(\App\Step::where('cycle_id',$companie->cycle)->orderBy('arr')->get() as $step){
                $sheet->setCellValue('G' . $row, $step->title);
            }
            $sheet->setCellValue('H' . $row, $companie->created_at);
            $row++;
        }
    });

        }





        $writer = new Xlsx($spreadsheet);

    // إنشاء اسم الملف مع تاريخ
    $fileName = 'Company_' . date('Y-m-d') . '.xlsx';
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
