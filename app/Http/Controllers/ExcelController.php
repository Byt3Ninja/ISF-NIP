<?php
namespace App\Http\Controllers;
use App\Event;
use App\EventsUser;
use App\User;
use App\Settings;
use App\Company;
use Illuminate\Http\Request;
use DB;
use Redirect;
use Illuminate\Support\Facades\Auth;
/// Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// require '../vendor/autoload.php';

class ExcelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function ExUsers(Request $request)
    {
        // $types = request()->input('type');
        $gender = request()->input('gender');
        $types = $request->type;
        $setting = Settings::findorfail('1'); // Get Title
        if( $types ) { $users = User::leftJoin('states', 'state_id', 'states.id')->Join('model_has_roles', 'users.id', 'model_id')->whereIn('role_id', array_values($types)); }
        else { $users = User::leftJoin('states','state_id','states.id'); } // Get all Users
        if( isset( $gender ) ) { $users = $users->where('gender', $gender); }
        $users = $users->get();
        $num=1;$row=4;
        if (request()->input('lang')=='ar')
        {
            // Load Excel template
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("ExcelReports/UsersAr.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            // Set Title
            $sheet->setCellValue('F1', $setting->ar_title);
            $sheet->setCellValue('A2', 'تقرير بيانات المستخدمين (' . Date('M-Y') . ')');
            // Fill users
            foreach ($users as $user)
            {
                $sheet->setCellValue('A' . $row, $num);
                $sheet->setCellValue('B' . $row, $user->first_name_ar . ' ' . $user->last_name_ar);
                $gender = 'أنثى';
                if ($user->gender == 'Male') { $gender = 'ذكر'; }
                $sheet->setCellValue('C' . $row, $gender);
                $sheet->setCellValue('D' . $row, $user->name_ar);
                $sheet->setCellValue('E' . $row, $user->phone);
                $sheet->setCellValue('F' . $row, $user->email);
                $row++;
                $num++;
            }
        }
        else
        {
            // Load Excel template
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("ExcelReports/UsersEn.xlsx");
                $sheet = $spreadsheet->getActiveSheet();
            // Set Title
                $sheet->setCellValue('F1', $setting->title);
                $sheet->setCellValue('A2', 'Registered Users Report (' . Date('M-Y') . ')');
            // Fill users
            foreach ($users as $user)
            {
                $sheet->setCellValue('A' . $row, $num);
                $sheet->setCellValue('B' . $row, $user->first_name_en . ' ' . $user->last_name_en);
                $sheet->setCellValue('C' . $row, $user->gender);
                $sheet->setCellValue('D' . $row, $user->name_en);
                $sheet->setCellValue('E' . $row, $user->phone);
                $sheet->setCellValue('F' . $row, $user->email);
                $row++;
                $num++;
            }
        }

        // Set Style
	    $row--;
        $sheet->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('f2f2f2');
	    $sheet->getStyle('A4:F'.$row)->getBorders()->applyFromArray(['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['rgb'=>'000000']]]);

        // Set File Name
	    $downloaded=str_replace(' ', '','RegisteredUsers').'-'.Date('M-Y').'.xlsx';
	    // Saving the document as Excel file...
	    $temp_file = tempnam(sys_get_temp_dir(), 'spreadsheet');
	    $objWriter = new Xlsx($spreadsheet);
	    $objWriter->save('tmp/'.$downloaded);
	    unlink($temp_file);
	    ob_end_clean();
        return response()->download(public_path() . '/tmp/' . urldecode($downloaded))->deleteFileAfterSend(true);
    }

    // Companies Report
    public function ExCompanies(Request $request)
    {
        $states = request()->input('state');
        $type = request()->input('type');
        if (!isset($states)) {$states=[];}
        $setting = Settings::findorfail('1'); // Get Title
        if (count($states) > 0)
        {
            $companies = Company::whereIn('state_id',array_values($states));
        }
        else
        {
            $companies = Company::whereNotNull('id'); // Get all Users
        }
        if (isset($type))
        {
            if ($type=='new') {
                $companies = $companies->doesntHave('activeEnrollments');
            } elseif ($type=='cycle') {
                $companies = $companies->has('activeEnrollments');
            } elseif ($type=='approved') {
                $companies = $companies->whereNotNull('approved');
            }
        }
        $companies = $companies->get();
        $num=1;$row=4;
        if (request()->input('lang')=='ar')
        {
            // Load Excel template
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("ExcelReports/CompaniesAr.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            // Set Title
            $sheet->setCellValue('F1', $setting->ar_title);
            $sheet->setCellValue('A2', 'تقرير بيانات الشركات (' . Date('M-Y') . ')');
            // Fill users
            foreach ($companies as $company)
            {
                $sheet->setCellValue('A' . $row, $num);
                $sheet->setCellValue('B' . $row, $company->name_ar);
                $sheet->setCellValue('C' . $row, $company->state->name_ar);
                $sheet->setCellValue('D' . $row, $company->city->name_ar);
                $sheet->setCellValue('E' . $row, $company->phone);
                $sheet->setCellValue('F' . $row, $company->email);
                $row++;
                $num++;
            }
        }
        else
        {
            // Load Excel template
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("ExcelReports/CompaniesEn.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            // Set Title
            $sheet->setCellValue('F1', $setting->title);
            $sheet->setCellValue('A2', 'Registered Companies Report (' . Date('M-Y') . ')');
            // Fill users
            foreach ($companies as $company)
            {
                $sheet->setCellValue('A' . $row, $num);
                $sheet->setCellValue('B' . $row, $company->name_en);
                $sheet->setCellValue('C' . $row, $company->state->name_en);
                $sheet->setCellValue('D' . $row, $company->city->name_en);
                $sheet->setCellValue('E' . $row, $company->phone);
                $sheet->setCellValue('F' . $row, $company->email);
                $row++;
                $num++;
            }
        }
        // Set Style
        $row--;
        $sheet->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('f2f2f2');
        $sheet->getStyle('A4:F'.$row)->getBorders()->applyFromArray(['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['rgb'=>'000000']]]);

        // Set File Name
        $downloaded=str_replace(' ', '','Companies').'-'.Date('M-Y').'.xlsx';
        // Saving the document as Excel file...
        $temp_file = tempnam(sys_get_temp_dir(), 'spreadsheet');
        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('tmp/'.$downloaded);
        unlink($temp_file);
        ob_end_clean();
        return response()->download(public_path() . '/tmp/' . urldecode($downloaded))->deleteFileAfterSend(true);
    }

    // Events report
    public function events($id)
    {
        if (!Auth::user()->hasAnyPermission(['manage_events', 'manage_csr_event'])) {return abort(401);}
        if (!isset($id)) {return abort(500);}
        $setting = Settings::findorfail('1');
        $users = EventsUser::where('event_id',$id)->get();
        if (count($users)==0) { return redirect()->back()->with('message2', 'nousers');}
        $event = Event::where('id',$id)->first('title_ar');
        $num=1;$row=4;
        // Load Excel template
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("ExcelReports/events.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        // Set Title
        $sheet->setCellValue('F1', $setting->ar_title);
        $sheet->setCellValue('A2',  $event->title_ar.' (' . Date('M-Y') . ')');
        // Fill users
        foreach ($users as $user)
        {
            $sheet->setCellValue('A' . $row, $num);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->phone);
            $sheet->setCellValue('E' . $row, $user->employer);
            $sheet->setCellValue('F' . $row, $user->jobtitle);
            $row++;
            $num++;
        }
        // Set Style
        $row--;
        $sheet->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('f2f2f2');
        $sheet->getStyle('A4:F'.$row)->getBorders()->applyFromArray(['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['rgb'=>'000000']]]);
        // Set File Name
        $downloaded=str_replace(' ', '','Events').'-'.Date('M-Y').'.xlsx';
        // Saving the document as Excel file...
        $temp_file = tempnam(sys_get_temp_dir(), 'spreadsheet');
        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('tmp/'.$downloaded);
        unlink($temp_file);
        ob_end_clean();
        return response()->download(public_path() . '/tmp/' . urldecode($downloaded))->deleteFileAfterSend(true);
    }

    public function index()
    {
        return view('admin.reports.dashboard');
    }
    public function auditlog()
    {
        // Load Excel template
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("ExcelReports/AuditLog.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $alllog = \App\AuditLog::all();
        $row = 4;
        // Fill log
        foreach ($alllog as $log)
        {
            $sheet->setCellValue('A' . $row, $log->id);
            $sheet->setCellValue('B' . $row, $log->description);
            $sheet->setCellValue('C' . $row, $log->subject_id);
            $sheet->setCellValue('D' . $row, $log->subject_type);
            $sheet->setCellValue('E' . $row, $log->user_id);
            $sheet->setCellValue('F' . $row, $log->host);
            $sheet->setCellValue('G' . $row, $log->updated_at);
            $sheet->setCellValue('H' . $row, $log->properties);
            $row++;
        }
        // Set Style
        $row--;
        $sheet->getStyle('A3:H3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('f2f2f2');
        $sheet->getStyle('A4:H'.$row)->getBorders()->applyFromArray(['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['rgb'=>'000000']]]);

        // Set File Name
        $downloaded=str_replace(' ', '','VI-LogBackup').'-'.Date('M-Y').'.xlsx';
        // Saving the document as Excel file...
        $temp_file = tempnam(sys_get_temp_dir(), 'spreadsheet');
        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('tmp/'.$downloaded);
        unlink($temp_file);
        ob_end_clean();
        return response()->download(public_path() . '/tmp/' . urldecode($downloaded))->deleteFileAfterSend(true);
    }
}
