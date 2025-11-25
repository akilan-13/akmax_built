<?php

namespace App\Http\Controllers\hr_management\hr_enroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyModel;
use App\Models\DepartmentModel;
use App\Models\StaffAttendanceModel;
use App\Models\StaffEsslModel;
use App\Models\StaffModel;
use Illuminate\Support\Facades\Crypt;
use DateTime;
use App\Models\Batch_staff_swap_Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SubErpWebhookModel;
use App\Models\WebhookDispatchModel;
use App\Jobs\SendWebhookJob;
use App\Events\WebhookDispatchedEvent;

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
    $entity_fill = $request->entity_fill ?? '0';
    $search_filter = $request->search_filter ?? '';

    // ------------------ MONTH PARSING ------------------
    $month_filter = $request->get('month_filter', date('M-Y'));
    // return  $month_filter;
    $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
    // $month_filter = $request->get('month_filter', date('m-Y'));  // Default to numeric month (11-2025)
    // $parsedDate = Carbon::createFromFormat('m-Y', $month_filter);

    $month = $parsedDate->month;
    $year  = $parsedDate->year;

    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
    $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
    $today = Carbon::today();

    // Limit end date to today if month is current
    if ($today->between($startDate, $endDate)) {
        $endDate = $today;
    }

    $dayCount = collect(CarbonPeriod::create($startDate, $endDate))
        ->filter(fn($d) => $d->dayOfWeek !== Carbon::SUNDAY)
        ->count();



    

    
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
            if($entity_fill !== ""){
                $query->where('egc_staff.entity_id', $entity_fill);
            }
        }
    }

 
    $total_staff= $query->count();

    // paginated for ajax
    $staffs = $query->paginate($perpage);

    // ------------------ ATTENDANCE RECORDS ------------------
    $attendanceRecords = StaffAttendanceModel::select('egc_staff_attendance.staff_id','egc_staff_attendance.date','egc_staff_attendance.attendance')
       ->join('egc_staff','egc_staff.sno','=','egc_staff_attendance.staff_id')
        ->whereBetween('egc_staff_attendance.date', [$startDate, $endDate]);
        if($company_fill !== ""){
            if($company_fill == 'egc'){
                $attendanceRecords->where('egc_staff.company_type',1);
            } else {
                if($entity_fill !== ""){
                    $attendanceRecords->where('egc_staff.entity_id', $entity_fill);
                }
            }
        }

        $attendanceRecords=$attendanceRecords->get()->groupBy('staff_id');

        $totalPresentCount = $attendanceRecords->flatMap(function($records) {
            return $records->whereIn('attendance', ['P','HD']); // Filter out other attendance types
        })->count();

        $totalAbsentCount = $attendanceRecords->flatMap(function($records) {
            return $records->where('attendance', 'A'); // Filter out other attendance types
        })->count();

        $totalLeaveCount = $attendanceRecords->flatMap(function($records) {
            return $records->where('attendance', 'L'); // Filter out other attendance types
        })->count();

        $totalPRMCount = $attendanceRecords->flatMap(function($records) {
            return $records->where('attendance', 'PR'); // Filter out other attendance types
        })->count();

        $totalODCount = $attendanceRecords->flatMap(function($records) {
            return $records->where('attendance', 'OD'); // Filter out other attendance types
        })->count();
        

        $AvgTotalPresent=$total_staff > 0 ? $totalPresentCount/$total_staff : 0;
        $totalPresentPercentage =$dayCount > 0 ? ($AvgTotalPresent/$dayCount)*100 :0;
        $totalPresentPercentage = $totalPresentPercentage >= 100 ? 100 : round($totalPresentPercentage,1);
        $totalPresentPercentage = $totalPresentPercentage <= 0 ?'00' : round($totalPresentPercentage,1);

        $AvgTotalAbsent=$total_staff > 0 ? $totalAbsentCount/$total_staff : 0;
        $totalAbsentPercentage =$dayCount > 0 ? ($AvgTotalAbsent/$dayCount)*100 :0;
        $totalAbsentPercentage = $totalAbsentPercentage >= 100 ? 100 : round($totalAbsentPercentage,1);
        $totalAbsentPercentage = $totalAbsentPercentage <= 0 ?'00' : round($totalAbsentPercentage,1);

        $AvgTotalLeave=$total_staff > 0 ? $totalLeaveCount/$total_staff : 0;
        $totalLeavePercentage =$dayCount > 0 ? ($AvgTotalLeave/$dayCount)*100 :0;
        $totalLeavePercentage = $totalLeavePercentage >= 100 ? 100 : round($totalLeavePercentage,1);
        $totalLeavePercentage = $totalLeavePercentage <= 0 ?'00' : round($totalLeavePercentage,1);

        $AvgTotalPRM=$total_staff > 0 ? $totalPRMCount/$total_staff : 0;
        $totalPRMPercentage =$dayCount > 0 ? ($AvgTotalPRM/$dayCount)*100 :0;
        $totalPRMPercentage = $totalPRMPercentage >= 100 ? 100 : round($totalPRMPercentage,1);
        $totalPRMPercentage = $totalPRMPercentage <= 0 ?'00' : round($totalPRMPercentage,1);

        $AvgTotalOD=$total_staff > 0 ? $totalODCount/$total_staff : 0;
        $totalODPercentage =$dayCount > 0 ? ($AvgTotalOD/$dayCount)*100 :0;
        $totalODPercentage = $totalODPercentage >= 100 ? 100 : round($totalODPercentage,1);
        $totalODPercentage = $totalODPercentage <= 0 ?'00' : round($totalODPercentage,1);


    // ------------------ AJAX RESPONSE ------------------
    if ($request->ajax()) {

        $data = $staffs->map(function($row) use ($dates, $attendanceRecords,$dayCount){

            $attendanceData = [];
            $totalPresent = 0; 
            $totalAbsent = 0; 
            $totalLeave = 0; 
            foreach($dates as $d){
                $dateKey = $d->format('Y-m-d');
                $isSunday = $d->dayOfWeek === Carbon::SUNDAY;

                $record = optional(
                    $attendanceRecords[$row->staff_id] ?? collect()
                )
                ->where('date', $dateKey)
                ->first();

                if ($isSunday) {
                    $attendanceData[$dateKey] = 'WK';
                } else {
                   $attendance = $record->attendance ?? "-";
                   $attendanceData[$dateKey] = $attendance;

                    // Count the "P", "PR", "HD" (present) attendance
                    if (in_array($attendance, ['P', 'PR', 'HD'])) {
                        $totalPresent++;
                    }
                    if($attendance == 'A'){
                        $totalAbsent++;
                    }
                    if($attendance == 'L'){
                        $totalLeave++;
                    }
                }
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

                'attendance' => $attendanceData,
                'dayCount' => $dayCount,
                'totalPresent' => $totalPresent,
                'totalAbsent' => $totalAbsent,
                'totalLeave' => $totalLeave,
            ];
        });

        return response()->json([
            'data' => $data,
            'totalPresentCount' => $totalPresentCount,
            'totalPresentPercentage' => round($totalPresentPercentage,1),
            'totalODPercentage' => round($totalODPercentage,1),
            'totalLeavePercentage' => round($totalLeavePercentage,1),
            'totalAbsentPercentage' => round($totalAbsentPercentage,1),
            'totalPRMPercentage' => round($totalPRMPercentage,1),
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

public function getStaff(Request $request)
{
    $branchId = $request->user()->branch_id;
    $departmentId = $request->department_id;
    $type = $request->type;
    
    // return $type;
    // $currentDate = now()->format('Y-m-d'); // Get the current date in 'Y-m-d' format
    $currentDate = now()->format('Y-m-d'); // Get the current date in 'Y-m-d' format
    if($type){
        if($type === 'today'){
                $currentDate = now()->format('Y-m-d');
        }elseif ($type === 'tomorrow') {
            $currentDate = now()->modify('+1 day')->format('Y-m-d');
        }elseif ($type === 'custom'){
            $currentDate =  date('Y-m-d', strtotime($request->date));
        }
    }else{
        $currentDate =  date('Y-m-d', strtotime($request->date));
    }
    //   return  $currentDate;
    
    // Get staff members based on branch and department
    $staffQuery = StaffModel::query();
    $staff = $staffQuery
        // ->where('branch_id', $branchId)
        ->where('department_id', $departmentId)
        ->where('status', 0)
        ->where('role_id','>',1)
        ->get(['sno', 'staff_name', 'nick_name']);

    // Get staff IDs who have attendance records for the current date
    $attendanceRecords = StaffAttendanceModel::whereIn('staff_id', $staff->pluck('sno'))
        ->whereDate('date', $currentDate) // Filter by current date
        ->pluck('staff_id'); // Get only the staff IDs

    // Filter out staff members who have attendance records for today and reset keys
    $filteredStaff = $staff->whereNotIn('sno', $attendanceRecords)->values();

    return response()->json(['status' => 200, 'data' => $filteredStaff]);
}

public function Add(Request $request)
{

    
    $request->validate([
        'staff_id' => 'required',
        'entry_select' => 'required',
    ]);

    $branch_id = $request->user()->branch_id;
    $staffIds = $request->input('staff_id', []);
    $attendanceType = $request->input('entry_select');
    $attendanceTypes = ['1' => 'P', '2' => 'A', '3' => 'OD', '4' => 'PR', '5' => 'L', '6' => 'WO'];
    $att = $attendanceTypes[$attendanceType] ?? 'NA';

    if (in_array('all', $staffIds)) {
        $staffIds = StaffModel::where('department_id', $request->department_id)
            ->pluck('sno')
            ->toArray();
    }


    $currentDate =  date('Y-m-d', strtotime($request->date));
    $attendanceDate = '';
    $startTime = null;
    $endTime = null;
    $reason = $request->reason ?? '';

    // OD or PR => With Time
    if (in_array($attendanceType, ['3', '4'])) {
        try {
            $startTime = (new DateTime($request->input('st_time')))->format('H:i:s');
            $endTime = (new DateTime($request->input('end_time')))->format('H:i:s');
            $reason = $request->reason;
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'message' => 'Invalid time format.']);
        }
        $attendanceDate = now()->format('Y-m-d'); // ← Set date!
    }

    // Tomorrow case
    elseif ($request->input('attendance_type_add') === 'tomorrow') {
        $attendanceDate = now()->modify('+1 day')->format('Y-m-d');
    }

    // Custom range
    elseif ($request->input('attendance_type_add') === 'custom') {
    $attendanceDate=    date('Y-m-d', strtotime($request->input('attendance_date_range_add'))) ;
        // $dates = $request->input('attendance_date_range_add', []);
        // if (is_string($dates)) {
        //     $dates = explode(',', $dates);
        // }

        // foreach ($dates as $date) {
        //     $dateTime = DateTime::createFromFormat('Y-m-d', trim($date));
        //     if (!$dateTime) {
        //         return response()->json(['status' => 400, 'message' => 'Invalid date format: ' . $date]);
        //     }

        //     $formattedDate = $dateTime->format('Y-m-d');
        //     $lastStaffAttendanceId = $this->processAttendanceForDate($request, $staffIds, $att, $formattedDate, $startTime, $endTime, $reason);
        // }

        // $staff_attendance_id = Crypt::encryptString($lastStaffAttendanceId);
        // return response()->json([
        //     'status' => 200,
        //     'message' => 'Attendance marked successfully'
        // ]);
    }

    // P or A => Present or Absent
    elseif (in_array($attendanceType, ['1', '2', '6', '3', '4'])) {
        // $attendanceDate = now()->format('Y-m-d');
        $attendanceDate =  date('Y-m-d', strtotime($request->attend_date));
    }

    // Fallback in case none of above sets date
    if (empty($attendanceDate)) {
        $attendanceDate = now()->format('Y-m-d');
        //   $attendanceDate =  date('Y-m-d', strtotime($request->attend_date));
    }

    $lastStaffAttendanceId = $this->processAttendanceForDate($request, $staffIds, $att, $attendanceDate, $startTime, $endTime, $reason);
    $staff_attendance_id = Crypt::encryptString($lastStaffAttendanceId);

    return response()->json([
        'status' => 200,
        'message' => 'Attendance marked successfully',
        'redirectUrl' => url('/hr_enroll/manage_attendance')
    ]);
}

private function processAttendanceForDate(Request $request, array $staffIds, string $attendanceType, string $attendanceDate, $startTime, $endTime, $reason)
{
    $branchId = $request->user()->branch_id;
    $userId = auth()->user()->user_id;
    $category_checks = StaffAttendanceModel::where('status', '!=', 2)->orderBy('sno', 'desc')->first();
    $year = date("Y");
    $month = date("m");
 

    foreach ($staffIds as $staffId) {
        // Check if attendance already exists for the staff, date, and type
        $alreadyExists = StaffAttendanceModel::where([
            ['staff_id', '=', $staffId],
            ['date', '=', $attendanceDate],
            ['attendance', '=', $attendanceType],
            ['status', '!=', 2], // Exclude soft-deleted or inactive records
        ])->exists();

        if ($alreadyExists) {
            continue; // Skip this staff ID if attendance already exists
        }

        // Generate unique staff attendance ID
        $category_check = StaffAttendanceModel::where('status', '!=', 2)->orderBy('sno', 'desc')->first();
        $staff_attendance_id = $this->generateStaffAttendanceId($category_check, $year,$month);
        
        // Save attendance
       $addStaff = StaffAttendanceModel::create([
            'staff_attendance_id' => $staff_attendance_id,
            'branch_id'           => $branchId,
            'staff_id'            => $staffId,
            'date'                => $attendanceDate,
            'attendance'          => $attendanceType,
            'type'                => $request->type_select ?? null,
            'time_start'          => $startTime ?? null,
            'time_end'            => $endTime ?? null,
            'reason'              => $reason ?? null,
            'created_by'          => $userId,
            'updated_by'          => $userId,
        ]);

        if($addStaff){
            $staffData = StaffModel::where('sno', $staffId)
            ->first();
            if($staffData->company_type == 2){
                $payload=[
                    'sno' => $addStaff->sno,
                    'staff_id' => $addStaff->staff_id,
                    'entity_id' => $add_staff->entity_id,
                    'date' => $addStaff->date ?? null,
                    'attendance' => $addStaff->attendance ?? null,
                    'type' => $addStaff->type ?? null,
                    'time_start'    => $addStaff->time_start ?? null,
                    'time_end' => $addStaff->time_end ?? null,
                    'reason' =>  $addStaff->reason ?? null,
                    
                ];
                $this->dispatchUpdateWebhooks($payload, 1,'Attendance_Add_Hook');
            }
            
        }

        
    }

    return $staff_attendance_id;
}
private function generateStaffAttendanceId($category_check, $month, $year)
{
    if (!$category_check) {
        return 'ATT0001/' . $month . '/' . $year;
    }

    // Extract the current staff attendance ID (e.g., "ATT0001/11/2025")
    $data = $category_check->staff_attendance_id;
    $slice = explode("/", $data);

    // Extract the numeric part of the attendance ID (e.g., 0001 from "ATT0001")
    $resultcus = preg_replace('/[^0-9]/', '', $slice[0]);
    $next_number = (int)$resultcus + 1;

    // Return the formatted ID (e.g., "ATT0002/11/2025")
    return 'ATT' . sprintf("%04d", $next_number) . '/' . $month . '/' . $year;
}

    public function UploadEssl(Request $request)
    {
         $userId = auth()->user()->user_id;
        try {

            if (!$request->hasFile('file_upload')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded.'
                ]);
            }

            $file = $request->file('file_upload');
            $rows = Excel::toArray([], $file)[0];

            $companyName = null;      // <== Actually Department
            $attendanceDate = null;
            $headerFound = false;

            foreach ($rows as $row) {

                // -----------------------------
                // DEPARTMENT = CompanyName
                // -----------------------------
                if (isset($row[1]) && $row[1] === "Department") {
                    $companyName = trim($row[4] ?? null);   // "CMP", "EAPL" etc.
                }

                // -----------------------------
                // Attendance Date
                // -----------------------------
                if (isset($row[1]) && $row[1] === "Attendance Date") {
                    $attendanceDate = Carbon::parse($row[4])->format('Y-m-d');
                }

                // -----------------------------
                // Header row of table
                // -----------------------------
                if (isset($row[1]) && $row[1] === "SNo" && isset($row[2]) && $row[2] === "E. Code") {
                    $headerFound = true;
                    continue;
                }

                // -----------------------------
                // Process employee rows
                // -----------------------------
                if ($headerFound) {

                    // empty row means next block
                    if ($row[2] === null) {
                        $headerFound = false;
                        continue;
                    }

                    // -------- Extract Columns --------
                    $employeeCode  = trim($row[2]);
                    $inTime        = $row[7] ?? null;
                    $outTime       = $row[8] ?? null;
                    $workDuration  = $row[10] ?? null;
                    $otDuration    = $row[11] ?? null;
                    $totalDuration = $row[12] ?? null;
                    $status        = $row[13] ?? null;

                    if (!$employeeCode || !$attendanceDate) {
                        continue;
                    }

                    // ---- Insert / Update ESSL Table ----
                    StaffEsslModel::updateOrCreate(
                        [
                            'employee_id' => $employeeCode,
                            'date'        => $attendanceDate
                        ],
                        [
                            'essl_status'   => $status,
                            'staff_id'      => 0,
                            'ot_duration'   => $otDuration,
                            'in_time'       => $inTime,
                            'out_time'      => $outTime,
                            'company'       => $companyName,     // department
                            'work_duration' => $workDuration,
                            'total_duration'=> $totalDuration,
                            'created_by'    => $userId,
                            'updated_by'    => $userId,
                        ]
                    );

                    // ---- Update Main Attendance ----
                    $this->UpdateEsslAttendance($employeeCode, $attendanceDate, $status, $request);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance imported successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ]);
        }
    }


    private function UpdateEsslAttendance($employeeId, $date, $status, Request $request)
    {
        $branchId = $request->user()->branch_id;
        $userId = auth()->user()->user_id;

        // Get staff row from master
        $StaffData = StaffModel::where('staff_id', $employeeId)->first();

        if (!$StaffData) {
            return; // employee not found in master
        }

        $year = date("Y");
        $month = date("m");

        // Check if attendance already exists
        $attendanceRow = StaffAttendanceModel::where([
            ['staff_id', '=', $StaffData->sno],
            ['date', '=', $date],
            ['status', '!=', 2]
        ])->first();

        if ($attendanceRow) {
            // Attendance already exists
            $staff_attendance_id = $attendanceRow->staff_attendance_id;
        } else {
            // Generate new attendance ID
            $category_check = StaffAttendanceModel::where('status', '!=', 2)->orderBy('sno', 'desc')->first();
            $staff_attendance_id = $this->generateStaffAttendanceId($category_check, $month, $year);
        }

        // Check for Absent or Present status
        $attendanceType = null;

        // Check if the status contains 'Absent'
        if (stripos($status, 'Absent') !== false) {
            $attendanceType = 'A'; // Mark as Absent
        }
        // Check if the status contains 'Present'
        elseif (stripos($status, 'Present') !== false) {
            $attendanceType = 'P'; // Mark as Present
        }else{
            return true;
        }

        // If the attendanceType is not set, skip the save process
        if ($attendanceType === null) {
            return true; // Skip saving if status is neither 'Absent' nor 'Present'
        }

        // Insert/Update attendance record
        StaffAttendanceModel::updateOrCreate(
            [
                'staff_id' => $StaffData->sno,
                'date'     => $date
            ],
            [
                'staff_attendance_id' => $staff_attendance_id,
                'attendance'          => $attendanceType,
                'updated_by'          => $userId,
                'branch_id'           => $branchId,
            ]
        );
    }


    function getStaffAttendanceByDate(Request $request){
        $date =  date('Y-m-d', strtotime($request->date));
        $staff_id = $request->staff_id;

        $attendanceRow = StaffAttendanceModel::where([
            ['staff_id', '=', $staff_id],
            ['date', '=', $date],
            ['status', '!=', 2]
        ])->first();
        $wkoff =false;
        if (date('l', strtotime($date)) === 'Sunday') {
            $wkoff = true;
        }
      return response()->json([
          'status' => 200,
          'wkoff' => $wkoff,
          'data' => $attendanceRow
      ]);
  }

public function Update(Request $request)
{
    // Validate the input fields
    $request->validate([
        'staff_id' => 'required',
        'entry_select' => 'required',
        'attendance_date_edit' => 'required|date_format:d-m-Y', // Ensure date format is d-m-Y
    ]);

    // Get input data
    $branch_id = $request->user()->branch_id;
    $staffIds = $request->input('staff_id');
    $attendanceType = $request->input('entry_select');
    $attendanceTypes = [
        '1' => 'P',   // Present
        '2' => 'A',   // Absent
        '3' => 'OD',  // On Duty
        '4' => 'PR',  // Permission
        '5' => 'L',   // Leave
        '6' => 'WO',  // Weekly Off
    ];
    $att = $attendanceTypes[$attendanceType] ?? 'NA'; // Default to 'NA' if not found

    // Convert the date format from 'DD-MM-YYYY' to 'YYYY-MM-DD'
    $attendanceDate = \DateTime::createFromFormat('d-m-Y', $request->input('attendance_date_edit'))->format('Y-m-d');

    $startTime = null;
    $endTime = null;
    $reason = $request->reason ?? '';

    // Handle On Duty (OD) or Permission (PR) with time
    if (in_array($attendanceType, ['3', '4'])) {
        try {
            $startTime = (new DateTime($request->input('st_time')))->format('H:i:s');
            $endTime = (new DateTime($request->input('end_time')))->format('H:i:s');
            $reason = $request->reason;
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'message' => 'Invalid time format.']);
        }
        // Set attendance date to today for OD/PR
        $attendanceDate = now()->format('Y-m-d');
    }

    // Handle custom range or tomorrow case
    if ($request->input('attendance_type_add') === 'tomorrow') {
        $attendanceDate = now()->modify('+1 day')->format('Y-m-d');
    } elseif ($request->input('attendance_type_add') === 'custom') {
        $attendanceDate = date('Y-m-d', strtotime($request->input('attendance_date_range_add')));
    }

    // Set default date if not set
    if (empty($attendanceDate)) {
        $attendanceDate = now()->format('Y-m-d');
    }

    // Process the attendance
    $lastStaffAttendanceId = $this->processAttendance($request, $staffIds, $att, $attendanceDate, $startTime, $endTime, $reason);

    // Encrypt the staff attendance ID before returning it in the response
    $staff_attendance_id = Crypt::encryptString($lastStaffAttendanceId);

    // Return success response
    return response()->json([
        'status' => 200,
        'message' => 'Attendance marked successfully',
        'redirectUrl' => url('/hr_enroll/manage_attendance'),
    ]);
}

private function processAttendance(Request $request, $staffId, $attendanceType, $attendanceDate, $startTime, $endTime, $reason)
{
    $branchId = $request->user()->branch_id;
    $userId = auth()->user()->user_id;
    $category_checks = StaffAttendanceModel::where('status', '!=', 2)->orderBy('sno', 'desc')->first();
    $year = date("Y");
    $month = date("m");

    // Check if attendance already exists for the staff on this date
    $alreadyExists = StaffAttendanceModel::where([
        ['staff_id', '=', $staffId],
        ['date', '=', $attendanceDate],
        ['status', '!=', 2], // Exclude soft-deleted or inactive records
    ])->first();

    // If attendance already exists, update it
    if ($alreadyExists) {
        $alreadyExists->attendance = $attendanceType;
        $alreadyExists->time_start = $startTime ?? null;
        $alreadyExists->time_end = $endTime ?? null;
        $alreadyExists->reason = $reason ?? null;
        $alreadyExists->update();
        if($alreadyExists){
            $staffData = StaffModel::where('sno', $alreadyExists->staff_id)
            ->first();
            if($staffData->company_type == 2){
                $payload=[
                    'sno' => $alreadyExists->sno,
                    'staff_id' => $alreadyExists->staff_id,
                    'entity_id' => $add_staff->entity_id,
                    'date' => $alreadyExists->date ?? null,
                    'attendance' => $alreadyExists->attendance ?? null,
                    'type' => $alreadyExists->type ?? null,
                    'time_start'    => $alreadyExists->time_start ?? null,
                    'time_end' => $alreadyExists->time_end ?? null,
                    'reason' =>  $alreadyExists->reason ?? null,
                    
                ];
                $this->dispatchUpdateWebhooks($payload, 1,'Attendance_Add_Hook');
            }
        }
    } else {
        // Generate a new staff attendance ID
        $category_check = StaffAttendanceModel::where('status', '!=', 2)->orderBy('sno', 'desc')->first();
        $staff_attendance_id = $this->generateStaffAttendanceId($category_check, $year, $month);

        // Save new attendance record
       $addStaff= StaffAttendanceModel::create([
            'staff_attendance_id' => $staff_attendance_id,
            'branch_id'           => $branchId,
            'staff_id'            => $staffId,
            'date'                => $attendanceDate,
            'attendance'          => $attendanceType,
            'type'                => $request->type_select ?? null,
            'time_start'          => $startTime ?? null,
            'time_end'            => $endTime ?? null,
            'reason'              => $reason ?? null,
            'created_by'          => $userId,
            'updated_by'          => $userId,
        ]);
        if($addStaff){
            $staffData = StaffModel::where('sno', $staffId)
            ->first();
            if($staffData->company_type == 2){
                $payload=[
                    'sno' => $addStaff->sno,
                    'staff_id' => $addStaff->staff_id,
                    'entity_id' => $add_staff->entity_id,
                    'date' => $addStaff->date ?? null,
                    'attendance' => $addStaff->attendance ?? null,
                    'type' => $addStaff->type ?? null,
                    'time_start'    => $addStaff->time_start ?? null,
                    'time_end' => $addStaff->time_end ?? null,
                    'reason' =>  $addStaff->reason ?? null,
                    
                ];
                $this->dispatchUpdateWebhooks($payload, 1,'Attendance_Add_Hook');
            }
        }
    }

    // Return the staff ID
    return $staffId;
}

public function getMonthStaffAttendanceById(Request $request)
{
    $staff_id = $request->staff_id;

        $staff = StaffModel::select(
            'egc_staff.*',
            'egc_entity.entity_name',
            'egc_entity.entity_short_name',
            'egc_company.company_name',
            'egc_company.company_base_color',
            'egc_department.department_name',
            'egc_division.division_name',
            'egc_job_role.job_position_name as job_role_name'
        )
        ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
        ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
        ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
        ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
        ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
        ->where('egc_staff.sno', $staff_id)
        ->first();
      
        $month_filter = $request->get('month_filter', date('M-Y'));
        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);

        $month = $parsedDate->month;
        $year  = $parsedDate->year;
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
        $today = Carbon::today();

        // Limit end date to today if month is current
        if ($today->between($startDate, $endDate)) {
            $endDate = $today;
        }

        $dayCount = collect(CarbonPeriod::create($startDate, $endDate))
            ->filter(fn($d) => $d->dayOfWeek !== Carbon::SUNDAY)
            ->count();

        $attendanceRecords = StaffAttendanceModel::select(
            'egc_staff_attendance.staff_id',
            'egc_staff_attendance.date',
            'egc_staff_attendance.attendance',
            'egc_staff_attendance.time_start',
            'egc_staff_attendance.time_end',
            'egc_staff_attendance.reason'
        )
        ->where('egc_staff_attendance.staff_id', $staff_id)
        ->whereYear('egc_staff_attendance.date', $year)
        ->whereMonth('egc_staff_attendance.date', $month)
        ->orderBy('egc_staff_attendance.date', 'desc')
        ->get();
 
         $totalPresentCount = StaffAttendanceModel::select('egc_staff_attendance.sno')
        ->where('egc_staff_attendance.staff_id', $staff_id)
        ->whereYear('egc_staff_attendance.date', $year)
        ->whereMonth('egc_staff_attendance.date', $month)
        ->whereIn('egc_staff_attendance.attendance', ['P','PR','HD'])
        ->orderBy('egc_staff_attendance.date', 'asc')
        ->count();

        $overAllPercentage=0;
  
       $overAllPercentage = $dayCount > 0 ? ($totalPresentCount / $dayCount )*100 : 0;
      
       $overAllPercentage = $overAllPercentage >100 ? 100 :round($overAllPercentage,1);
        
       $overAllPercentage = $overAllPercentage < 0 ? 0 :round($overAllPercentage,1);
       

    $staff->overAllPercentage = $overAllPercentage;
    
    
    $helper = new \App\Helpers\Helpers();
    $common_date_format = $helper->general_setting_data()->date_format ?? 'd-M-y';

    foreach ($attendanceRecords as $attend) {
        $attend->day = date('l', strtotime($attend->date));
        $attend->date = date($common_date_format, strtotime($attend->date));
        $attend->time_start = $attend->time_start ? date("h:i A", strtotime($attend->time_start)) : null;
        $attend->time_end   = $attend->time_end   ? date("h:i A", strtotime($attend->time_end))   : null;
    }

    return response()->json([
        'status' => 200,
        'message' => 'Attendance loaded successfully',
        'staff' => $staff,
        'attendance' => $attendanceRecords,
    ]);
}



 protected function dispatchUpdateWebhooks( $broadcast, $userId = 1,$dispatchHook)
  {
      $webhook = SubErpWebhookModel::where('status', 0)->where('webhook_module',$dispatchHook)->where('entity_id',$broadcast['entity_id'])->first();
          if($webhook){
              $dispatch = WebhookDispatchModel::create([
                'sub_erp_webhook_sno' => $webhook->sno,
                'dispatchable_type' => 'App\Models\StaffAttendanceModel',
                'dispatchable_id' => $broadcast['sno'],
                'message_uuid' => $broadcast['sno'],
                'payload' => json_encode($broadcast),
                'status' => 0,
                'attempts' => 0,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // broadcast creation once
            // broadcast(new WebhookDispatchedEvent($dispatch));

            // enqueue the job
            SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
            
          }
     
  }

}
