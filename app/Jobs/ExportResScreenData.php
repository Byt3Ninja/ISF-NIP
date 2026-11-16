<?php

namespace App\Jobs;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
 
use App\Company;


class ExportResScreenData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data1;

    /**
     * Create a new job instance.
     */
    public function __construct($data1)
    {
        $this->data1 = $data1;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $step = $this->data1['step'];
        $formid = $this->data1['formid'];
        $exportcstep = $this->data1['exportcstep'];
        $userEmail = $this->data1['userEmail'];
    
        

        $companies = Company::enrolledOnStep($step)->get();
       
        

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Company');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'User');
        $sheet->setCellValue('E1', 'Judges');
        $sheet->setCellValue('F1', 'AVG degree');
        $sheet->setCellValue('G1', 'Current Step');
        $sheet->setCellValue('H1', 'Time');

        // كتابة بيانات المستخدمين
        $row = 2; // بداية الصف الثاني

        if(Auth()->user()->hasRole('Admin')){

            Company::enrolledOnStep($step)->chunk(1000, function($companies) use (&$sheet, &$row, $formid, $exportcstep) {
                foreach ($companies as $companie) {
                    
                    $sheet->setCellValue('A' . $row, $companie->id);
                    $sheet->setCellValue('B' . $row, $companie->name);
                    $sheet->setCellValue('C' . $row, $companie->user->title);
                    $sheet->setCellValue('D' . $row, $companie->user->name);
                foreach(\App\User::whereIn('id',$companie->judges($companie->id,$formid))->get() as $judger){
                    $sheet->setCellValue('E' . $row, $judger->name);
                }
                $weight=$companie->companyWeight($companie->id,$formid??'',0);
                    $sheet->setCellValue('F' . $row, $weight.'%');
        
            $step = \App\Step::where('id',$exportcstep)->first();
                    $sheet->setCellValue('G' . $row, $step->title);
            
                    $sheet->setCellValue('H' . $row, $companie->created_at);
                    $row++;
                }
            });


        }else{

        Company::enrolledOnStep($step)->chunk(1000, function($companies) use (&$sheet, &$row, $formid, $exportcstep) {
        foreach ($companies as $companie) {
            
            $sheet->setCellValue('A' . $row, $companie->id);
            $sheet->setCellValue('B' . $row, $companie->name);
            $sheet->setCellValue('C' . $row, $companie->user->title);
            $sheet->setCellValue('D' . $row, $companie->user->name);
        foreach(\App\User::whereIn('id',$companie->judges($companie->id,$formid))->get() as $judger){
            $sheet->setCellValue('E' . $row, $judger->name);
        }
        $weight=$companie->companyWeight($companie->id,$formid??'',0);
            $sheet->setCellValue('F' . $row, $weight.'%');

    $step = \App\Step::where('id',$exportcstep)->first();
            $sheet->setCellValue('G' . $row, $step->title);
    
            $sheet->setCellValue('H' . $row, $companie->created_at);
            $row++;
        }
    });

        }





        $writer = new Xlsx($spreadsheet);

    // إنشاء اسم الملف مع تاريخ
    $fileName = 'Result_' . date('Y-m-d') . '.xlsx';
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
