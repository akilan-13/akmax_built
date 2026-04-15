<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StaffModel;

class Crm extends Controller
{
    public function index(){
            $user = Auth::user();
            
    
      return view('content.dashboard.hr_ultra',[
         'companies' => DB::table('egc_company')->where('status',0)->get(),
      ]);
    //   return view('content.dashboard.dashboards-crm');
    }


    public function getDashboardData(Request $request)
{
    try {

        $today = now()->toDateString();
        $company = $request->company_id ?? '';
        $entity = $request->entity_id ?? '';
        $department = $request->department_id ?? '';
        // 👨‍💼 STAFF
        // $totalStaff = DB::table('egc_staff')->where('status',0)->count() ?? 0;

        $staffQuery = DB::table('egc_staff')->where('status',0);

        if($company != ''){
          if($company == 'egc'){
             $staffQuery->where('egc_staff.company_type',1);
          }else{
            $staffQuery->where('egc_staff.company_id', 'LIKE', $company);
          }
            
        }
        if ($entity) {
          $staffQuery->where('egc_staff.entity_id', $entity);
        }

        
        if ($department) {
            $staffQuery->where('egc_staff.department_id', $department);
        }

       
        $staffIds = $staffQuery->pluck('sno');

        // ✅ PRESENT
        $present = DB::table('egc_staff_attendance')
            ->whereDate('date', $today)
            ->whereIn('staff_id', $staffIds)
            ->where('attendance', 'P')
            ->where('status',0)
            ->count() ?? 0;

     
        $lateQuery  = DB::table('egc_staff_attendance as att')
                ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')
                ->join('egc_shift_time_log as stl', function ($join) {
                    $join->on('stl.staff_id', '=', 'att.staff_id');
                })
                ->join('egc_shift_day_times as sd', function ($join) {
                    $join->on('sd.shift_id', '=', 'stl.change_shift_id')
                        ->where('sd.status', 0);
                })
                ->whereDate('att.date', $today)
                ->whereIn('att.staff_id', $staffIds)
                ->where('att.status', 0)
          
                ->whereRaw("DATE(att.date) BETWEEN stl.start_date AND IFNULL(stl.end_date, '9999-12-31')")
                // ✅ Match day (Mon, Tue...)
                ->whereRaw("LOWER(sd.day_name) = LOWER(DATE_FORMAT(att.date, '%a'))")
                // ✅ Late condition
                ->whereRaw("TIME(att.in_time) > TIME(sd.time_from)")
                ->whereRaw("
                    TIME_FORMAT(att.in_time, '%H:%i') > 
                    TIME_FORMAT(ADDTIME(sd.time_from, '00:15:00'), '%H:%i')
                ");
                // ->whereRaw("
                //     TIME_FORMAT(att.in_time, '%H:%i') > TIME_FORMAT(sd.time_from, '%H:%i')
                // ")
               $late = $lateQuery->count();

        $absentQury  = DB::table('egc_staff_attendance as att')
        ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')
            ->whereDate('att.date', $today)
            ->whereIn('att.staff_id', $staffIds)
            ->whereIn('att.attendance', ['L','A'])
              ->where('att.status',0);
           $absent = $absentQury->count();
            $absentDetails = $absentQury
            ->select('s.staff_name')
            ->limit(10)
            ->get();
         // ✅ LATE DETAILS
        $lateDetails = $lateQuery
            ->select('s.staff_name', 'att.in_time')
            ->limit(10)
            ->get();

        $permissionQuery  = DB::table('egc_staff_attendance as att')
        ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')
            ->whereDate('att.date', $today)
            ->where('att.attendance', 'PR')
            ->whereIn('att.staff_id', $staffIds)
              ->where('att.status',0);
          $permission = $permissionQuery->count();

        $permissionDetails = $permissionQuery
            ->select('s.staff_name')
            ->limit(10)
            ->get();

        $totalStaff = $staffIds->count();

            // ✅ ALERTS
            $alerts = [];

            if ($late > 10) {
                $alerts[] = [
                    'title' => '⚠️ High Late Attendance',
                    'time' => now()->format('H:i')
                ];
            }

            if ($absent > ($totalStaff * 0.2)) {
                $alerts[] = [
                    'title' => '🚨 High Absenteeism',
                    'time' => now()->format('H:i')
                ];
            }

         // ✅ LIVE ACTIVITY
        $activities = DB::table('egc_staff_attendance as att')
            ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')
            ->whereDate('att.date', $today)
            ->latest('att.updated_at')
            ->limit(10)
            ->get()
            ->map(fn($a) => $a->staff_name . ' marked ' . $a->attendance);


        // 👥 EMPLOYEES (SAFE FIELDS)
            $employees = DB::table('egc_staff')->where('egc_staff.status', '!=', 2)
                ->select('egc_staff.*',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_company.company_base_color',
                'egc_company.company_logo',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_shift_time.shift_name',
                'egc_job_role.job_position_name as job_role_name',
                )
                ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
                ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
                ->leftJoin('egc_shift_time', 'egc_staff.shift_time_id', 'egc_shift_time.sno')
                ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
                ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
                ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
                ->whereIn('egc_staff.status', [0,1])
                ->latest()
                ->limit(5)
                ->get();
             $helper = new \App\Helpers\Helpers();
            $general_setting=$helper->general_setting_data();
         
            $entityCounts = StaffModel::select(
                DB::raw('COUNT(*) as total'),
                'egc_staff.company_id',
                'egc_entity.entity_short_name',
                'egc_entity.entity_name',
                'egc_company.company_short_name',
                DB::raw('CASE 
                    WHEN egc_staff.company_type = 1 THEN "'.$general_setting->title.'" 
                    ELSE egc_company.company_name 
                END as company_name'),
                DB::raw('CASE 
                    WHEN egc_staff.company_type = 1 THEN "'.$general_setting->logo.'" 
                    ELSE egc_company.company_logo 
                END as company_logo'),
                DB::raw('CASE 
                    WHEN egc_staff.company_type = 1 THEN "#ab2b22" 
                    ELSE egc_company.company_base_color 
                END as company_base_color'),
                 DB::raw('CASE 
                    WHEN egc_staff.company_type = 1 THEN "#ab2b22" 
                    ELSE egc_entity.entity_base_color 
                END as entity_base_color')
            )
            ->leftJoin('egc_company','egc_staff.company_id','=','egc_company.sno')
            ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
            ->where('egc_staff.status','!=',2)
            ->where('egc_staff.sno', '>', 1)
            ->whereIn('egc_staff.status',[0,1])
            ->groupBy('company_name','company_logo','company_base_color','entity_base_color','egc_staff.company_type','egc_staff.company_id','egc_company.company_short_name','egc_entity.entity_short_name','egc_entity.entity_name')
            ->orderBy('egc_staff.company_id','asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => compact(
                'totalStaff',
                'present',
                'late',
                'permission',
                'absent',
                'lateDetails',
                'entityCounts',
                'absentDetails',
                'permissionDetails',
                'alerts',
                'activities'
            )
        ]);

    } catch (\Exception $e) {

        \Log::error('Dashboard Error: '.$e->getMessage());

        return response()->json([
            'status' => false,
            'data' => [],
            'message' => 'Something went wrong'.$e->getMessage()
        ]);
    }
}
}