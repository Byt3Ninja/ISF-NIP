<?php

namespace App\Jobs;

use App\Cycle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExportMail;
use App\Organization;
use Symfony\Component\HttpFoundation\File\TemporaryFile;

class ExportCycleData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userEmail;

    public function __construct($userEmail)
    {
        $this->userEmail = $userEmail;
    }

    public function handle()
    {

       
        if(Auth()->user()->hasRole('Admin')){

         $cycles = Cycle::all();
        
        }else{


            
            $Organization = Organization::where('user_id','=', auth()->user()->id)->first();
            
            $cycles = Cycle::where('organization_id','=', $Organization->id)->get();
            


        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Title');
        $sheet->setCellValue('C1', 'Description');
        $sheet->setCellValue('D1', 'Start');
        $sheet->setCellValue('E1', 'End');
        $sheet->setCellValue('F1', 'Private');
        $sheet->setCellValue('G1', 'Created');

        // كتابة بيانات المستخدمين
        $row = 2; // بداية الصف الثاني

            if(Auth()->user()->hasRole('Admin')){
         $cycles = Cycle::all();
         Cycle::chunk(1000, function($cycles) use (&$sheet, &$row) {
            foreach ($cycles as $cycle) {
                $sheet->setCellValue('A' . $row, $cycle->id);
                $sheet->setCellValue('B' . $row, $cycle->title);
                $sheet->setCellValue('C' . $row, $cycle->description);
                $sheet->setCellValue('D' . $row, $cycle->start);
                $sheet->setCellValue('E' . $row, $cycle->end);
                $sheet->setCellValue('F' . $row, $cycle->private);
                $sheet->setCellValue('G' . $row, $cycle->created_at);
                $row++;
            }
        });
        
        }else{
            $Organization = Organization::where('user_id','=', auth()->user()->id)->first();
            Cycle::where('organization_id','=', $Organization->id)->chunk(1000, function($cycles) use (&$sheet, &$row) {
                foreach ($cycles as $cycle) {
                    $sheet->setCellValue('A' . $row, $cycle->id);
                    $sheet->setCellValue('B' . $row, $cycle->title);
                    $sheet->setCellValue('C' . $row, $cycle->description);
                    $sheet->setCellValue('D' . $row, $cycle->start);
                    $sheet->setCellValue('E' . $row, $cycle->end);
                    $sheet->setCellValue('F' . $row, $cycle->private);
                    $sheet->setCellValue('G' . $row, $cycle->created_at);
                    $row++;
                }
            });
        }
       
        $writer = new Xlsx($spreadsheet);

    // إنشاء اسم الملف مع تاريخ
    $fileName = 'Program_' . date('Y-m-d') . '.xlsx';
    $tempFilePath = storage_path('app/' . $fileName);

    // حفظ الملف في مجلد مؤقت
    $writer->save($tempFilePath);

    // إرسال الملف كمرفق عبر البريد الإلكتروني
    Mail::to($this->userEmail)
        ->queue(new ExportMail($tempFilePath, $fileName));

    // حذف الملف المؤقت
    unlink($tempFilePath);

    return response()->json(['message' => 'Email sent successfully']);
    }
}
