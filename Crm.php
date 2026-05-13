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

        CREATE TABLE egc_payroll_employee_structures (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    structure_name VARCHAR(100),
    gross_salary DECIMAL(12,2),
    ctc DECIMAL(12,2),
    effective_from DATE,
    effective_to DATE,
    is_active TINYINT DEFAULT 1,
    created_by BIGINT,
    updated_by BIGINT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

        CREATE TABLE egc_payroll_employee_structure_details (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    structure_id BIGINT,
    component_id BIGINT,
    amount DECIMAL(12,2),
    is_override TINYINT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
        ✅ 2. MODEL
// EmployeeStructureModel.php
class EmployeeStructureModel extends Model
{
    protected $table = 'egc_payroll_employee_structures';

    protected $fillable = [
        'employee_id',
        'structure_name',
        'gross_salary',
        'ctc',
        'effective_from',
        'effective_to',
        'is_active'
    ];

    public function details()
    {
        return $this->hasMany(EmployeeStructureDetailModel::class, 'structure_id');
    }
}
// Detail Model
class EmployeeStructureDetailModel extends Model
{
    protected $table = 'egc_payroll_employee_structure_details';

    protected $fillable = [
        'structure_id',
        'component_id',
        'amount',
        'is_override'
    ];
}
✅ 3. CONTROLLER (PRODUCTION LEVEL)
🔹 INDEX
public function index(Request $request)
{
    $data = EmployeeStructureModel::with('details')
        ->where('is_active', 1)
        ->latest()
        ->paginate(20);

    if ($request->ajax()) {
        return response()->json($data);
    }

    return view('content.payroll.employee_structure.index');
}
🔹 ADD STRUCTURE (CORE LOGIC)
public function Add(Request $request)
{
    DB::beginTransaction();

    try {

        $structure = EmployeeStructureModel::create([
            'employee_id'   => $request->employee_id,
            'structure_name'=> $request->structure_name,
            'gross_salary'  => $request->gross_salary,
            'ctc'           => $request->ctc,
            'effective_from'=> $request->effective_from,
            'is_active'     => 1
        ]);

        foreach ($request->components as $c) {

            EmployeeStructureDetailModel::create([
                'structure_id' => $structure->id,
                'component_id' => $c['component_id'],
                'amount'       => $c['amount'],
                'is_override'  => $c['override'] ?? 0
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message'=> 'Structure Created'
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return response()->json([
            'status'=> false,
            'error' => $e->getMessage()
        ]);
    }
}
🔹 UPDATE
public function Update(Request $request)
{
    DB::beginTransaction();

    $structure = EmployeeStructureModel::find($request->id);

    $structure->update([
        'gross_salary' => $request->gross_salary,
        'ctc' => $request->ctc
    ]);

    EmployeeStructureDetailModel::where('structure_id', $structure->id)->delete();

    foreach ($request->components as $c) {

        EmployeeStructureDetailModel::create([
            'structure_id' => $structure->id,
            'component_id' => $c['component_id'],
            'amount' => $c['amount'],
            'is_override' => $c['override'] ?? 0
        ]);
    }

    DB::commit();

    return response()->json(['status'=>true]);
}
🎨 4. ADVANCED UI (THIS IS WHERE YOU WIN)
🔥 Layout Concept
----------------------------------------
Employee: [ Select ]
Gross Salary: [ ₹ 50000 ]

----------------------------------------
COMPONENT TABLE

| Component | Formula | Amount | Override |
|-----------|--------|--------|----------|
| Basic     | 50%    | 25000  | ☐ |
| HRA       | 40%    | 10000  | ☐ |
| PF        | auto   | 1800   | ☐ |
----------------------------------------
TOTAL = LIVE CALCULATED
✅ 5. BLADE UI
<div class="card">
    <div class="card-header">
        <h5>Employee Salary Structure</h5>
    </div>

    <div class="card-body">

        <select id="employee" class="form-control mb-3"></select>

        <input type="number" id="gross_salary" class="form-control mb-3" placeholder="Gross Salary">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Formula</th>
                    <th>Amount</th>
                    <th>Override</th>
                </tr>
            </thead>

            <tbody id="componentTable"></tbody>
        </table>

        <h5>Total: ₹ <span id="total">0</span></h5>

        <button class="btn btn-primary" onclick="save()">Save</button>

    </div>
</div>
⚡ 6. DYNAMIC JS (LIVE CALCULATION)
function loadComponents(){

    $.get('/formula/list', function(res){

        let html = '';

        res.data.forEach(c => {

            html += `
            <tr>
                <td>${c.name}</td>
                <td>${c.formula_expression}</td>
                <td><input type="number" class="amount" data-id="${c.id}" /></td>
                <td><input type="checkbox" class="override"/></td>
            </tr>`;
        });

        $('#componentTable').html(html);
    });
}
🔥 LIVE TOTAL
$(document).on('keyup', '.amount', function(){

    let total = 0;

    $('.amount').each(function(){
        total += parseFloat($(this).val()) || 0;
    });

    $('#total').text(total);
});
🚀 7. UX UPGRADE (VERY IMPORTANT)
Add:
✅ Sticky total bar
✅ Color coding (earnings vs deductions)
✅ Auto-fill from formula
✅ Lock system components (PF/ESI)
🔥 NEXT LEVEL (RECOMMENDED)

After this module:

👉 You MUST build:

1. Payroll Calculation Engine
2. Payslip Generator
3. Monthly Payroll Run
⚠️ FINAL ADVICE

Right now you're building:

👉 SAP-level payroll system

Do NOT:

Hardcode formulas
Store only totals
Skip breakdown table
👉 If you want next

I can build:

🔥 Auto Formula Calculator Service
🔥 Real-time salary preview
🔥 Full payroll run engine

Just say:

👉 "next payroll engine"
            
    
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
