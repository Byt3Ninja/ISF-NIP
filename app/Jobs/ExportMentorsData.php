<?php

namespace App\Jobs;

use App\Mail\ExportMail;
use App\MentorshipRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportMentorsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data4;

    /**
     * Create a new job instance.
     */
    public function __construct($data4)
    {
        $this->data4 = $data4;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $request = $this->data4['request'];
    
        $organization_id=auth()->user()->organization_id;


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Company');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'User');
        $sheet->setCellValue('E1', 'Mentor');
        $sheet->setCellValue('F1', 'Comment');
        $sheet->setCellValue('G1', 'Rate');
        $sheet->setCellValue('H1', 'Date/Time');
        $sheet->setCellValue('I1', 'Approved');
        $sheet->setCellValue('J1', 'Done');
        $sheet->setCellValue('K1', 'Created_At');

        // كتابة بيانات المستخدمين
        $row = 2; // بداية الصف الثاني

        if(!Auth()->user()->hasRole('Admin')){

        if ($request == 'all') {
            
            MentorshipRequest::chunk(1000, function($sessions) use (&$sheet, &$row) {
                foreach ($sessions as $session) {
                    $sheet->setCellValue('A' . $row, $session->id);
                    $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                    $sheet->setCellValue('C' . $row, $session->user->title_en);
                    $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                    $sheet->setCellValue('E' . $row, $session->mentor->name);
                    $sheet->setCellValue('F' . $row, $session->comment);
                    $sheet->setCellValue('G' . $row, $session->rate_comment);
                    $sheet->setCellValue('H' . $row, $session->period);
                    $sheet->setCellValue('I' . $row, $session->approved);
                    $sheet->setCellValue('J' . $row, $session->done);
                    $sheet->setCellValue('K' . $row, $session->created_at);
                    $row++;
                }
            });
        } elseif ($request == 'approved')  {
            $sessions = MentorshipRequest::when(!is_null($organization_id), function($q) use($organization_id) {
                return $q;
            })->whereNotNull('start_url')->get();
            
            MentorshipRequest::when(!is_null($organization_id), function($q) use($organization_id) {
                return $q;
            })->whereNotNull('start_url')->chunk(1000, function($sessions) use (&$sheet, &$row) {
                foreach ($sessions as $session) {
                    $sheet->setCellValue('A' . $row, $session->id);
                    $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                    $sheet->setCellValue('C' . $row, $session->user->title_en);
                    $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                    $sheet->setCellValue('E' . $row, $session->mentor->name);
                    $sheet->setCellValue('F' . $row, $session->comment);
                    $sheet->setCellValue('G' . $row, $session->rate_comment);
                    $sheet->setCellValue('H' . $row, $session->period);
                    $sheet->setCellValue('I' . $row, $session->approved);
                    $sheet->setCellValue('J' . $row, $session->done);
                    $sheet->setCellValue('K' . $row, $session->created_at);
                    $row++;
                }
            });
        } elseif ($request == 'disapproved')  {
            $sessions = MentorshipRequest::when(!is_null($organization_id), function($q) use($organization_id) {
                return $q;
            })->where('approved', 'No')->get();
            
            MentorshipRequest::when(!is_null($organization_id), function($q) use($organization_id) {
                return $q;
            })->where('approved', 'No')->chunk(1000, function($sessions) use (&$sheet, &$row) {
                foreach ($sessions as $session) {
                    $sheet->setCellValue('A' . $row, $session->id);
                    $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                    $sheet->setCellValue('C' . $row, $session->user->title_en);
                    $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                    $sheet->setCellValue('E' . $row, $session->mentor->name);
                    $sheet->setCellValue('F' . $row, $session->comment);
                    $sheet->setCellValue('G' . $row, $session->rate_comment);
                    $sheet->setCellValue('H' . $row, $session->period);
                    $sheet->setCellValue('I' . $row, $session->approved);
                    $sheet->setCellValue('J' . $row, $session->done);
                    $sheet->setCellValue('K' . $row, $session->created_at);
                    $row++;
                }
            });

        } else {

           $sessions = MentorshipRequest::when(!is_null($organization_id), function($q) use($organization_id) {
                return $q->where('organization_id', $organization_id);
            })->whereNull('approved')->get();

MentorshipRequest::when(!is_null($organization_id), function($q) use($organization_id) {
                return $q->where('organization_id', $organization_id);
            })->whereNull('approved')->chunk(1000, function($sessions) use (&$sheet, &$row) {
                foreach ($sessions as $session) {
                    $sheet->setCellValue('A' . $row, $session->id);
                    $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                    $sheet->setCellValue('C' . $row, $session->user->title_en);
                    $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                    $sheet->setCellValue('E' . $row, $session->mentor->name);
                    $sheet->setCellValue('F' . $row, $session->comment);
                    $sheet->setCellValue('G' . $row, $session->rate_comment);
                    $sheet->setCellValue('H' . $row, $session->period);
                    $sheet->setCellValue('I' . $row, $session->approved);
                    $sheet->setCellValue('J' . $row, $session->done);
                    $sheet->setCellValue('K' . $row, $session->created_at);
                    $row++;
                }
            });
        }
       
        }else{

            if ($request == 'all') {
                $sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية الجلسات بناءً على organization_id
                })->get();
                
                // تقسيم البيانات ومعالجتها على دفعات لتجنب مشاكل الذاكرة عند التعامل مع كميات كبيرة من البيانات
                MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية الجلسات في الاستعلام الرئيسي أيضًا
                })->chunk(1000, function($sessions) use (&$sheet, &$row) {
                    foreach ($sessions as $session) {
                        $sheet->setCellValue('A' . $row, $session->id);
                        $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                        $sheet->setCellValue('C' . $row, $session->user->title_en);
                        $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                        $sheet->setCellValue('E' . $row, $session->mentor->name);
                        $sheet->setCellValue('F' . $row, $session->comment);
                        $sheet->setCellValue('G' . $row, $session->rate_comment);
                        $sheet->setCellValue('H' . $row, $session->period);
                        $sheet->setCellValue('I' . $row, $session->approved);
                        $sheet->setCellValue('J' . $row, $session->done);
                        $sheet->setCellValue('K' . $row, $session->created_at);
                        $row++;
                    }
                });
            } elseif ($request == 'approved')  {
                $sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية الجلسات بناءً على organization_id
                })->whereNotNull('start_url') // تصفية الجلسات التي تحتوي على start_url غير فارغ
                  ->get();
                
                // تقسيم البيانات ومعالجتها على دفعات
                MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية الجلسات بناءً على organization_id
                })->whereNotNull('start_url') // تصفية الجلسات التي تحتوي على start_url غير فارغ
                  ->chunk(1000, function($sessions) use (&$sheet, &$row) {
                    foreach ($sessions as $session) {
                        $sheet->setCellValue('A' . $row, $session->id);
                        $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                        $sheet->setCellValue('C' . $row, $session->user->title_en);
                        $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                        $sheet->setCellValue('E' . $row, $session->mentor->name);
                        $sheet->setCellValue('F' . $row, $session->comment);
                        $sheet->setCellValue('G' . $row, $session->rate_comment);
                        $sheet->setCellValue('H' . $row, $session->period);
                        $sheet->setCellValue('I' . $row, $session->approved);
                        $sheet->setCellValue('J' . $row, $session->done);
                        $sheet->setCellValue('K' . $row, $session->created_at);
                        $row++;
                    }
                });
            } elseif ($request == 'disapproved')  {
                $sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية الجلسات بناءً على organization_id
                })->where('approved', 'No') // تصفية الجلسات التي لم يتم الموافقة عليها
                  ->get();
                
                // تقسيم البيانات ومعالجتها على دفعات
                MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية الجلسات بناءً على organization_id
                })->where('approved', 'No') // تصفية الجلسات التي لم يتم الموافقة عليها
                  ->chunk(1000, function($sessions) use (&$sheet, &$row) {
                    foreach ($sessions as $session) {
                        $sheet->setCellValue('A' . $row, $session->id);
                        $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                        $sheet->setCellValue('C' . $row, $session->user->title_en);
                        $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                        $sheet->setCellValue('E' . $row, $session->mentor->name);
                        $sheet->setCellValue('F' . $row, $session->comment);
                        $sheet->setCellValue('G' . $row, $session->rate_comment);
                        $sheet->setCellValue('H' . $row, $session->period);
                        $sheet->setCellValue('I' . $row, $session->approved);
                        $sheet->setCellValue('J' . $row, $session->done);
                        $sheet->setCellValue('K' . $row, $session->created_at);
                        $row++;
                    }
                });
    
            } else {
                $sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية بناءً على organization_id
                })->whereNull('approved') // تصفية الجلسات التي لم يتم الموافقة عليها (approved is NULL)
                  ->get();
                
                // تقسيم البيانات ومعالجتها على دفعات بناءً على نفس الشروط
                MentorshipRequest::when(!is_null($organization_id), function ($q) use($organization_id) {
                    return $q->where('organization_id', $organization_id); // تصفية بناءً على organization_id
                })->whereNull('approved') // تصفية الجلسات التي لم يتم الموافقة عليها (approved is NULL)
                  ->chunk(1000, function($sessions) use (&$sheet, &$row) {

                    foreach ($sessions as $session) {
                        $sheet->setCellValue('A' . $row, $session->id);
                        $sheet->setCellValue('B' . $row, $session->user->company->name_en);
                        $sheet->setCellValue('C' . $row, $session->user->title_en);
                        $sheet->setCellValue('D' . $row, $session->user->first_name_en.' '.$session->user->last_name_en);
                        $sheet->setCellValue('E' . $row, $session->mentor->name);
                        $sheet->setCellValue('F' . $row, $session->comment);
                        $sheet->setCellValue('G' . $row, $session->rate_comment);
                        $sheet->setCellValue('H' . $row, $session->period);
                        $sheet->setCellValue('I' . $row, $session->approved);
                        $sheet->setCellValue('J' . $row, $session->done);
                        $sheet->setCellValue('K' . $row, $session->created_at);
                        $row++;
                    }
                });
            }

        }

        $writer = new Xlsx($spreadsheet);

    // إنشاء اسم الملف مع تاريخ
    $fileName = 'Mentors_' . date('Y-m-d') . '.xlsx';
    $tempFilePath = storage_path('app/' . $fileName);

    // حفظ الملف في مجلد مؤقت
    $writer->save($tempFilePath);

    // إرسال الملف كمرفق عبر البريد الإلكتروني
    Mail::to(auth()->user()->email)
        ->send(new ExportMail($tempFilePath, $fileName));

    // حذف الملف المؤقت
    unlink($tempFilePath);

    return response()->json(['message' => 'Email sent successfully']);
    }

}
