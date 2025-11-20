<?php

namespace App\Http\Controllers\hr_management\hr_enroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyModel;
use App\Models\DepartmentModel;
use App\Models\StaffAttendanceModel;
use App\Models\StaffModel;
use Illuminate\Support\Facades\Crypt;
use DateTime;
use App\Models\Batch_staff_swap_Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\View;

class ManageAttendance extends Controller
{
//   public function index(Request $request)
//   {
//     $page = $request->input('page', 1);
//     $perpage = (int) $request->input('sorting_filter', 25);
//     $offset = ($page - 1) * $perpage;
//     $search_filter = $request->search_filter ?? '';
//     $company_fill = $request->company_fill ?? 'egc';
//     $entity_fill = $request->entity_fill ?? '';

//     $month_filter = $request->get('month_filter', date('M-Y'));
//     $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
//     $month = $parsedDate->month; // numeric month (1-12)
//     $year = $parsedDate->year;

//     $month_chk = ($month . $year == date('mY') || $year > date('Y')) ? 0 : 1;

//     // Generate start and end dates for the selected month
//     $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
//     $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

//     // Generate an array of dates for the selected month using CarbonPeriod
//     $dates = \Carbon\CarbonPeriod::create($startDate, $endDate)->toArray();

//     $query = StaffModel::select('egc_staff.sno as staff_id', 'egc_staff.staff_name', 'egc_staff.nick_name', 'egc_staff.gender', 'egc_staff.staff_image', 'egc_staff.mobile_no', 'egc_staff.email_id', 'egc_department.department_name')
//             ->join('egc_department', 'egc_department.sno', '=', 'egc_staff.department_id')
//             // ->where('egc_staff.position_role','!=' ,49)
//             ->where('egc_staff.status', 0)
//             ->where('egc_staff.sno','>', 1);
//         // ->where('egc_staff.department_id', 2);

//         if($company_fill != ''){
//           if($company_fill == 'egc'){
//              $query->where('egc_staff.company_type',1);
//           }else{
//             $query->where('egc_staff.company_id', 'LIKE', $company_fill);
//           }
            
//         }

//         $staffs = $query->get();

        

//         // Determine weekoff days
//         foreach ($staffs as $staff) {
//             if ($staff->shift_sunday == 0) {
//                 switch ($staff->shift_weekoff_day) {
//                     case 1:
//                         $staff->weekoff = 'Mon';
//                         break;
//                     case 2:
//                         $staff->weekoff = 'Tue';
//                         break;
//                     case 3:
//                         $staff->weekoff = 'Wed';
//                         break;
//                     case 4:
//                         $staff->weekoff = 'Thu';
//                         break;
//                     case 5:
//                         $staff->weekoff = 'Fri';
//                         break;
//                     case 6:
//                         $staff->weekoff = 'Sat';
//                         break;
//                     default:
//                         $staff->weekoff = 'Sun';
//                         break;
//                 }
//             } else {
//                 $staff->weekoff = 'Sun';
//             }
//         }

//         // Fetch and group attendance records for the selected month
//         $attendanceRecords = StaffAttendanceModel::select('staff_id', 'date', 'attendance')
//             ->whereBetween('date', [$startDate, $endDate])
//             ->get()
//             ->groupBy('staff_id');

//         // Initialize attendance summary
//         $attendanceSummary = [];
//         $attendanceSummaryMonth = [
//             'present' => 0,
//             'absent' => 0,
//             'leave' => 0,
//             'other' => 0,
//             'permission' => 0,
//             'onduty' => 0,
//             'total_days' => count($dates) * count($staffs), // Total days for all staff combined
//         ];

//         // Calculate attendance summary for each staff member
//         foreach ($staffs as $staff) {
//             $staffAttendance = $attendanceRecords->get($staff->staff_id, collect());

//             // Initialize summary counts for the staff member
//             $summary = [
//                 'present' => 0,
//                 'absent' => 0,
//                 'leave' => 0,
//                 'other' => 0,
//                 'permission' => 0,
//                 'onduty' => 0,
//                 'total_days' => count($dates),
//             ];

//             foreach ($staffAttendance as $record) {
//                 switch ($record->attendance) {
//                     case 'P':
//                         $summary['present']++;
//                         $attendanceSummaryMonth['present']++; // Add to the overall month summary
//                         break;
//                     case 'A':
//                         $summary['absent']++;
//                         $attendanceSummaryMonth['absent']++; // Add to the overall month summary
//                         break;
//                     case 'L':
//                         $summary['leave']++;
//                         $attendanceSummaryMonth['leave']++; // Add to the overall month summary
//                         break;
//                     case 'PR':
//                         $summary['permission']++;
//                         $attendanceSummaryMonth['permission']++; // Add to the overall month summary
//                         break;
//                     case 'OD':
//                         $summary['onduty']++;
//                         $attendanceSummaryMonth['onduty']++; // Add to the overall month summary
//                         break;
//                     default:
//                         $summary['other']++;
//                         $attendanceSummaryMonth['other']++; // Add to the overall month summary
//                         break;
//                 }
//             }

//             // Calculate and round percentages for the staff member
//             $summary['present_percentage'] = $summary['total_days'] > 0 ? round(($summary['present'] / $summary['total_days']) * 100, 2) : 0;
//             $summary['absent_percentage'] = $summary['total_days'] > 0 ? round(($summary['absent'] / $summary['total_days']) * 100, 2) : 0;
//             $summary['leave_percentage'] = $summary['total_days'] > 0 ? round(($summary['leave'] / $summary['total_days']) * 100, 2) : 0;
//             $summary['other_percentage'] = $summary['total_days'] > 0 ? round(($summary['other'] / $summary['total_days']) * 100, 2) : 0;
//             $summary['permission_percentage'] = $summary['total_days'] > 0 ? round(($summary['permission'] / $summary['total_days']) * 100, 2) : 0;
//             $summary['onduty_percentage'] = $summary['total_days'] > 0 ? round(($summary['onduty'] / $summary['total_days']) * 100, 2) : 0;

//             // Add the summary for the staff member
//             $attendanceSummary[$staff->staff_id] = $summary;
//         }

//         // Optionally, calculate percentages for the overall monthly summary
//         $attendanceSummaryMonth['present_percentage'] = $attendanceSummaryMonth['total_days'] > 0 ? round(($attendanceSummaryMonth['present'] / $attendanceSummaryMonth['total_days']) * 100, 2) : 0;
//         $attendanceSummaryMonth['absent_percentage'] = $attendanceSummaryMonth['total_days'] > 0 ? round(($attendanceSummaryMonth['absent'] / $attendanceSummaryMonth['total_days']) * 100, 2) : 0;
//         $attendanceSummaryMonth['leave_percentage'] = $attendanceSummaryMonth['total_days'] > 0 ? round(($attendanceSummaryMonth['leave'] / $attendanceSummaryMonth['total_days']) * 100, 2) : 0;
//         $attendanceSummaryMonth['other_percentage'] = $attendanceSummaryMonth['total_days'] > 0 ? round(($attendanceSummaryMonth['other'] / $attendanceSummaryMonth['total_days']) * 100, 2) : 0;
//         $attendanceSummaryMonth['permission_percentage'] = $attendanceSummaryMonth['total_days'] > 0 ? round(($attendanceSummaryMonth['permission'] / $attendanceSummaryMonth['total_days']) * 100, 2) : 0;
//         $attendanceSummaryMonth['onduty_percentage'] = $attendanceSummaryMonth['total_days'] > 0 ? round(($attendanceSummaryMonth['onduty'] / $attendanceSummaryMonth['total_days']) * 100, 2) : 0;

//         // return $staffs;
//         // Fetch department data
//         $department = DepartmentModel::where('status', 0)->orderBy('sno', 'desc')->get();
//         $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();

//     return view('content.hr_management.hr_enroll.manage_attendance.manage_attendance',[
//       'branch_type_fill' => '',
//             'branch_fill' => '',
//             'franchise_fill' => '',
//             'department_fill' => '',
//             'staff_name_fill' => '',
//             'staff_nick_name_fill' => '',
//             'fdate' => '',
//             'tdate' => '',
//             'dt_fill' => '',
//             'staffs' => $staffs,
//             'company_list' => $company_list,
//             'attendanceRecords' => $attendanceRecords,
//             'attendanceSummary' => $attendanceSummary,
//             'attendanceSummaryMonth' => $attendanceSummaryMonth,
//             'department_list_fill' => $department,
//             'dates' => $dates,
//             'month' => $month,
//             'year' => $year,
//             'month_chk' => $month_chk,
//              'month_filter' => $month_filter, 
//              'search_filter' => $search_filter,
//              'company_fill' => $company_fill,
//             'perpage' => $perpage,
//     ]);
//   }

public function index(Request $request)
{
    $page = $request->input('page', 1);
    $perpage = (int) ($request->sorting_filter ?? 25);

    $company_fill = $request->company_fill ?? 'egc';
    $search_filter = $request->search_filter ?? '';

    // ------------------ MONTH PARSING ------------------
    $month_filter = $request->get('month_filter', date('M-Y'));
    $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);

    $month = $parsedDate->month;
    $year  = $parsedDate->year;

    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

    $dates = CarbonPeriod::create($startDate, $endDate)->toArray();

    // ------------------ STAFF QUERY ------------------
    $query = StaffModel::select(
        'egc_staff.sno as staff_id',
        'egc_staff.staff_name',
        'egc_staff.nick_name',
        'egc_staff.gender',
        'egc_staff.company_type',
        'egc_staff.company_id',
        'egc_staff.entity_id',
        'egc_staff.staff_image',
        'egc_department.department_name'
    )
    ->join('egc_department','egc_department.sno','=','egc_staff.department_id')
    ->where('egc_staff.status',0)
    ->where('egc_staff.sno','>',1);

    if($company_fill !== ""){
        if($company_fill == 'egc'){
            $query->where('egc_staff.company_type',1);
        } else {
            $query->where('egc_staff.company_id', $company_fill);
        }
    }

    // paginated for ajax
    $staffs = $query->paginate($perpage);

    // ------------------ ATTENDANCE RECORDS ------------------
    $attendanceRecords = StaffAttendanceModel::select('staff_id','date','attendance')
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->groupBy('staff_id');

    // ------------------ AJAX RESPONSE ------------------
    if ($request->ajax()) {

        $data = $staffs->map(function($row) use ($dates, $attendanceRecords){

            $attendanceData = [];

            foreach($dates as $d){
                $dateKey = $d->format('Y-m-d');

                $record = optional(
                    $attendanceRecords[$row->staff_id] ?? collect()
                )
                ->where('date', $dateKey)
                ->first();

                $attendanceData[$dateKey] = $record->attendance ?? "-";
            }

            return [
                'staff_id' => $row->staff_id,
                'staff_name' => $row->staff_name,
                'nick_name' => $row->nick_name,
                'department_name' => $row->department_name,
                'gender' => $row->gender,
                'staff_image' => $row->staff_image,
                'company_id' => $row->company_id,
                'company_type' => $row->company_type,
                'entity_id' => $row->entity_id,

                'attendance' => $attendanceData
            ];
        });

        return response()->json([
            'data' => $data,
            'dates' => array_map(fn($d)=>$d->format('Y-m-d'), $dates),
            'current_page' => $staffs->currentPage(),
            'last_page' => $staffs->lastPage(),
            'total' => $staffs->total(),
        ]);
    }

    // ------------------ NORMAL (NON-AJAX) VIEW ------------------
    $company_list = CompanyModel::where('status',0)->get();

    return view('content.hr_management.hr_enroll.manage_attendance.manage_attendance',[
        'staffs' => $staffs,
        'company_list' => $company_list,
        'dates' => $dates,
        'month' => $month,
        'year' => $year,
        'month_filter' => $month_filter,
        'company_fill' => $company_fill,
        'perpage' => $perpage
    ]);
}


}
