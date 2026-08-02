<?php

namespace App\Http\Controllers\hr_management\hr_general;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobRequestModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\SubErpWebhookModel;
use App\Models\WebhookDispatchModel;
use App\Jobs\SendWebhookJob;
use App\Models\WebhookDispatchAttemptModel;
use App\Models\InterviewScheduleModel;
use App\Models\InterviewScheduleStageModel;
use App\Models\InterviewCandidateModel;
use App\Models\ApplicantModel;
use App\Models\StaffModel;
use App\Models\InterviewScheduleQuestionModel;
use App\Models\InterviewQuestionModel;
use App\Models\JobQRScannerModel;
use App\Models\ApplicantLogModel;
use App\Services\PayrollPreviewService;
use App\Services\PayrollProcessService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Models\SourceModel;
use App\Models\MajorModel;
use Smalot\PdfParser\Parser as PdfParser;

use App\Services\GoogleDriveService;
use Google\Client;
use App\Mail\EGCMail;
use App\Models\EmailTemplateModel;
use Illuminate\Support\Facades\Mail;
use App\Models\SocialMediaModel;
use App\Models\CompanyModel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollExport;
use App\Exports\Payroll\PayrollWordExport;
use App\Exports\Payroll\PayrollPdfExport;
use App\Exports\PayrollBankExcelExport;
use App\Services\PayrollExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Table;
use NumberFormatter;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Cell;

use App\Exports\PayrollCommonExcelExport;
use App\Exports\PayrollVariableExcelExport;

use Illuminate\Pagination\LengthAwarePaginator;


class ManagePayroll extends Controller
{

    protected $googleDriveService;
    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
        date_default_timezone_set('Asia/Kolkata');
    }

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perpage = (int) $request->input('sorting_filter', 25);

        // $monthFilter = $request->get('month_filter', date('M-Y'));
        // $parsedDate = Carbon::createFromFormat('M-Y', $monthFilter);

        // $month = $parsedDate->month;
        // $year  = $parsedDate->year;

        $monthFilter = $request->get('month_filter', now()->format('M-Y'));

        try {
            // Parse month-year safely
            $parsedDate = Carbon::createFromFormat('!M-Y', $monthFilter);
            $month = $parsedDate->month;
            $year  = $parsedDate->year;
        } catch (\Exception $e) {
            $parsedDate = now()->startOfMonth();
            $month = $parsedDate->month;
            $year  = $parsedDate->year;
        }

        $salary_company = $request->salary_company ?? '';
        $company_fill = $request->company_fill ?? '';
        $entity_fill = $request->entity_fill ?? '';
        $department_fill = $request->department_fill ?? '';
        $division_fill = $request->division_fill ?? '';
        $job_role_fill = $request->job_role_fill ?? '';
        $date_filter = $request->dt_fill_issue_rpt ?? '';
        $from_date_filter = $request->to_dt_iss_rpt ?? '';
        $to_date_filter = $request->to_date_fillter_textbox ?? '';
        $search_filter = $request->search_filter ?? '';
        $salary_date_fill = $request->salary_date_fill ?? '';
        $salary_type_fill = $request->salary_type_fill ?? '';
        $exit_staff_fill = $request->exit_staff_fill ? 1 : 0;
        $only_bank_fill = $request->only_bank_fill ? 1 : 0;
        $only_variable_fill = $request->only_variable_fill ? 1 : 0;

        $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));
        $payrollProcess = DB::table('egc_payroll_processes')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();
        $payslipCount = DB::table('egc_staff')->where('status', 0)->where('is_payslip', 1)->count();

        $isLiveMode = !$payrollProcess;

        $currentMonth = Carbon::create($year, $month, 1)
                        ->format('Y-m-01');

        $variableAmountIds = DB::table('egc_payroll_variable_amounts')
                // ->where('employee_sno', $staff->sno)
                ->where('status', 0)
                // ->where('is_expired', 0)
                ->whereDate('start_month','<=',$currentMonth)
                ->whereDate('end_month','>=',$currentMonth)
                ->orderByDesc('sno')
                ->pluck('employee_sno');
        $verification = null;

        if ($payrollProcess) {

            $verification = DB::table('egc_payroll_hr_verifications')
                ->where('payroll_process_sno', $payrollProcess->sno)
                ->where('status', 0)
                ->latest('sno')
                ->first();
        }
        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company as salary_company', 'egc_staff_salary_accounts.salary_company_id', '=', 'salary_company.sno')
            ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
               $q->orWhere(function ($notice) use ($month, $year) {
                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereRaw("
                            ? BETWEEN DATE_FORMAT(s.date_of_joining,'%Y-%m')
                            AND DATE_FORMAT(s.notice_end_date,'%Y-%m')
                        ", [$payrollMonth]);

                    
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $exit->whereIn('s.status',[5,6,7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereNotIn('s.sno',[211,212,94,77])
                        ->whereRaw("
                            ? BETWEEN DATE_FORMAT(s.date_of_joining,'%Y-%m')
                            AND DATE_FORMAT(s.staff_last_date,'%Y-%m')
                        ", [$payrollMonth])
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                    
                })

                ->orWhere(function ($special) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);

                    // $special->whereIn('s.sno',[211,212,94,77])
                    //         ->whereIn('s.status',[5,6,7])
                    //         ->whereNotNull('s.staff_last_date')
                    //         ->whereRaw("
                    //             ? BETWEEN DATE_FORMAT(s.date_of_joining,'%Y-%m')
                    //             AND DATE_FORMAT(s.staff_last_date,'%Y-%m')
                    //         ", [$payrollMonth])
                    //         ->whereRaw(
                    //             'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                    //             [3]
                    //         );

                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            // ->where('s.sno', 200)
            ->select(
                's.*',
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary as basic_salary',
                'egc_staff_salary_accounts.per_day_salary',
                's.company_id',
                's.entity_id',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_type',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.staff_last_date',
                's.notice_start_date',
                's.notice_end_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'salary_company.company_base_color as salary_company_base_color',
                'salary_company.company_name as salary_company_name',
                'egc_company.company_base_color',
                'egc_company.company_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name'
            );
        if ($request->search_filter != '') {
            $search = $request->search_filter;
            $staffQuery->where(function ($q) use ($search) {
                $q->where('s.staff_name', 'LIKE', "%{$search}%")
                    ->orWhere('s.staff_id', 'LIKE', "%{$search}%");
            });
        }
        if ($request->salary_company != '') {
            $staffQuery->where('egc_staff_salary_accounts.salary_company_id', $request->salary_company);
        }
        if ($request->company_fill != '') {
            $staffQuery->where('s.company_id', $request->company_fill);
        }
        
        if ($request->entity_fill != '') {
            $staffQuery->where('s.entity_id', $request->entity_fill);
        }
        if ($request->department_fill != '') {
            $staffQuery->where('s.department_id', $request->department_fill);
        }
         
        if ($exit_staff_fill == 1) {
            $staffQuery->where('s.status', '>', 2 );
        }
        
         if ($only_variable_fill == 1) {
            $staffQuery->whereIn('s.sno', $variableAmountIds);
        }
       
        if ($request->salary_type_fill != '') {
            $staffQuery->where('s.salary_type', $request->salary_type_fill);
        }
        if ($request->salary_date_fill != '') {
            $staffQuery->where('s.salary_date', $request->salary_date_fill);
        }
        if ($request->dt_fill_issue_rpt == 1 && $request->from_dt_iss_rpt != '' && $request->to_dt_iss_rpt != '') {
            $staffQuery->whereBetween(
                's.date_of_joining',
                [
                    $request->from_dt_iss_rpt,
                    $request->to_dt_iss_rpt
                ]
            );
        }
       
        $allStaffIds = (clone $staffQuery)->pluck('s.sno');
        $allStaffAccountIds = (clone $staffQuery)
        ->select('egc_staff_salary_accounts.sno as salary_account_id')
        ->pluck('salary_account_id');
        
        $staff = $staffQuery
            ->orderBy('s.date_of_joining', 'desc')
            ->paginate($perpage);
        $data = [];
        $overallNetSalary = 0;
        $overallGrossSalary = 0;
        $overallFixedGrossSalary = 0;
        $overallDeduction = 0;

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

         $payrollDate = Carbon::create($year, $month, 1)->endOfMonth();
        
    
       $overallFixedGrossSalary = DB::table('egc_payroll_employee_structures')
            ->whereIn('salary_account_id', $allStaffAccountIds)
            ->where('status', 0)
            ->whereDate('effective_from', '<=', $payrollDate)
            ->where(function ($q) use ($payrollDate) {
                $q->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $payrollDate);
            })
            ->sum('gross_salary');

        $earningComponents = [];
        $deductionComponents = [];
        $earningsSummary = [];
        $deductionsSummary = [];
        if ($isLiveMode) {
            $earningComponents = [];
            $deductionComponents = [];
            $allStaffData =  DB::table('egc_staff_salary_accounts')
                ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
                ->select(
                    's.*',
                     'egc_staff_salary_accounts.sno as salary_account_id',
                    'egc_staff_salary_accounts.gross_salary as basic_salary',
                    'egc_staff_salary_accounts.per_day_salary',
                )
                ->where('egc_staff_salary_accounts.status',0)
                // ->whereIn('s.sno', $allStaffIds)
                ->whereIn('egc_staff_salary_accounts.sno', $allStaffAccountIds)
                ->get();
                
            foreach ($allStaffData as $staffItem) {

                $payroll = app(
                    \App\Services\PayrollPreviewService::class
                )->calculateLivePayroll(
                    $staffItem,
                    $month,
                    $year,
                    $only_bank_fill
                );
                //    return $payroll;
                $overallNetSalary += round($payroll['net_salary']) ?? 0;
                $overallGrossSalary += $payroll['gross_salary'] ?? 0;
                $overallDeduction += round($payroll['deductions']) ?? 0;
                foreach ($payroll['components'] as $component) {
                    $name = $component['component'] ?? 'Unknown';
                    $amount = (float)($component['amount'] ?? 0);
                    if (($component['type'] ?? '') === 'earning') {
                        if (!isset($earningComponents[$name])) {
                            $earningComponents[$name] = 0;
                        }
                        $earningComponents[$name] += $amount;
                    }
                    if (($component['type'] ?? '') === 'deduction') {
                        if (!isset($deductionComponents[$name])) {
                            $deductionComponents[$name] = 0;
                        }
                        $deductionComponents[$name] += $amount;
                    }
                }
            }
            $earningsSummary = [];
            $deductionsSummary = [];
            foreach ($earningComponents as $name => $amount) {

                $earningsSummary[] = [
                    'name' => $name,
                    'total' => round($amount)
                ];
            }

            foreach ($deductionComponents as $name => $amount) {

                $deductionsSummary[] = [
                    'name' => $name,
                    'total' => round($amount)
                ];
            }
            foreach ($staff as $item) {
                $payroll = app(
                    \App\Services\PayrollPreviewService::class
                )->calculateLivePayroll(
                    $item,
                    $month,
                    $year,
                    $only_bank_fill
                );
                // return $payroll;
                
                if ($item->company_type == 1) {
                    $relativePath =
                        'staff_images/Management/' .
                        $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }
                $fullPath = public_path($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;
                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }

                $components =collect($payroll['components']);
                $onduty =(float)$components->where('code', 'ONDUTY')->sum('amount');
                $variable =(float)$components->where('code', 'VARIABLE')->sum('amount');
                $incentive =(float)$components->where('code', 'INCENTIVE')->sum('amount');
                $pf =(float)$components->where('code', 'PF')->sum('amount');
                $esi =(float)$components->where('code', 'ESI')->sum('amount');
                $pt =(float)$components->where('code', 'PT')->sum('amount');
                $tax =(float)$components->where('code', 'TAX')->sum('amount');
                $employerPf =(float)$components->where('code', 'EMPLOYER_PF')->sum('amount');
                $employerEsi =(float)$components->where('code', 'EMPLOYER_ESI')->sum('amount');
                $lopAmount =(float)$components->where('code', 'LOP')->sum('amount');

                $data[] = [
                    'sno' => $item->sno,
                    'staff_status' => $item->status,
                    'name' => $item->staff_name,
                    'staff_last_date' => $item->staff_last_date,
                    'notice_start_date' => $item->notice_start_date,
                    'notice_end_date' => $item->notice_end_date,
                    'basic_salary' => $item->basic_salary,
                    'company_base_color' =>
                    $item->company_base_color,
                    'is_saved' => false,
                    'isStaffImage' => $isStaffImage,
                    'data' => [
                        'staff_image' =>$item->staff_image,
                        'company_type' =>$item->company_type,
                        'company_id' =>$item->company_id,
                        'entity_id' => $item->entity_id,
                        'gender' =>$item->gender,
                        'staff_code' =>$item->staff_code,
                        'nick_name' => $item->nick_name,
                        'company_name' =>$item->company_name,
                        'salary_company_name' => $item->salary_company_name,
                        'salary_company_base_color' => $item->salary_company_base_color,
                        'department_name' =>$item->department_name,
                        'job_role_name' =>$item->job_role_name,
                        'date_of_joining' => $item->date_of_joining,
                        'salary_type' => $item->salary_type,
                        'salary_date' => $item->salary_date,
                        'basic_salary' =>round($payroll['basic_salary']),
                    ],
                    'dataParoll' => [
                        'components' => $payroll['components'] ?? [],
                        'rules' => $payroll['rules'] ?? [],
                        'earnings' => round($payroll['earnings']),
                        'deductions' => round($payroll['deductions']),
                        'gross_salary' => round($payroll['gross_salary']),
                        'net' => round($payroll['net_salary']),
                        'employer_contribution' => round($payroll['employer_contribution']),
                        'lop_days' => round($payroll['lop_days'], 2),
                        'onduty' => round($onduty),
                        'variable' => round($variable),
                        'incentive' => round($incentive),
                        'pf' => round($pf),
                        'esi' => round($esi),
                        'pt' => round($pt),
                        'tax' => round($tax),
                        'lopAmount' => round($lopAmount),
                        'employerPf' => round($employerPf),
                        'employerEsi' => round($employerEsi),
                    ],
                    'present_days' => round($payroll['present_days'], 2),
                    'absent_days' => round($payroll['absent_days'], 2),
                    'paidLeave' => round($payroll['paidLeave'], 2),
                    'weekoff_days' => round($payroll['weekoff_days'], 2),
                    'holiday_days' => round($payroll['holiday_days'], 2),
                    'earning_days' => round($payroll['earning_days'], 2),
                    'paidDays' => round($payroll['staffPaidDays'], 2),
                    'late_count' => round($payroll['late_count'], 2),
                    'lop_amount' => round($payroll['lop_amount']),
                    'pf_amount' => round($payroll['pf_amount'] ?? 0),
                    'esi_amount' => round($payroll['esi_amount'] ?? 0),
                    'net_salary' => round($payroll['net_salary']),
                ];
                
            }
        } else {
            $earningComponents = [];
            $deductionComponents = [];
            $employeeIds = $staff->pluck('sno');
            $processedPayrolls = DB::table('egc_payroll_employee_payrolls as ep')
                ->whereIn('ep.staff_id', $employeeIds)
                ->where('ep.payroll_month', $month)
                ->where('ep.payroll_year', $year)
                ->where('ep.status', 0)
                ->get()
                ->keyBy('staff_id');

            $overallPayrolls = DB::table('egc_payroll_employee_payrolls')
                ->whereIn('staff_id', $allStaffIds)
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->where('status', 0)
                ->get();
            $overallNetSalary =
                $overallPayrolls->sum('net_payable');

            $overallGrossSalary =
                $overallPayrolls->sum('gross_earnings');

            // $overallDeduction = $overallPayrolls->sum('gross_deductions');


            $componentSummary = DB::table('egc_payroll_employee_payroll_details')
                ->select(
                    'component_name',
                    'component_category',
                    DB::raw('SUM(actual_amount) as total')
                )
                ->whereIn(
                    'payroll_employee_sno',
                    $overallPayrolls->pluck('sno')
                )
                ->where('status', 0)
                ->groupBy(
                    'component_name',
                    'component_category'
                )
                ->get();

            

            foreach ($componentSummary as $component) {
                if ($component->component_category == 'earning') {
                    $earningsSummary[] = [
                        'name' => $component->component_name,
                        'total' => round($component->total)
                    ];
                }

                if ($component->component_category == 'deduction') {
                    $overallDeduction +=$component->total;
                    $deductionsSummary[] = [
                        'name' => $component->component_name,
                        'total' => round($component->total)
                    ];
                }
            }


            foreach ($staff as $item) {
                $payroll = $processedPayrolls[$item->sno] ?? null;
                if ($item->company_type == 1) {
                    $relativePath = 'staff_images/Management/' . $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }

                $fullPath = public_path($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;

                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }
                $components = DB::table('egc_payroll_employee_payroll_details')
                    ->where('egc_payroll_employee_payroll_details.payroll_employee_sno', $payroll->sno ?? 0)
                    ->join('egc_payroll_components','egc_payroll_components.sno','=','egc_payroll_employee_payroll_details.payroll_component_sno')
                    ->where('egc_payroll_employee_payroll_details.status', 0)
                    ->select(
                        'egc_payroll_employee_payroll_details.*',
                        'egc_payroll_components.component_code',
                    )
                    ->get()
                    ->map(function ($item) {
                        return [
                            'sno' => $item->payroll_component_sno,
                            'component' => $item->component_name,
                            'code' => $item->component_code,
                            'type' => $item->component_category,
                            'calculation_type' => $item->calculation_type,
                            'percentage' => (float)$item->percentage_value,
                            'amount' => round($item->actual_amount, 2)
                        ];
                    })
                    ->toArray();

                $lateCount = $this->getLateCount(
                    $item->sno,
                    $month,
                    $year
                );

                $rules = [];

                $pfAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%PF%')
                    ->sum('actual_amount');

                $esiAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%ESI%')
                    ->sum('actual_amount');
                $payrollSlipData = DB::table('egc_payroll_payslips')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_payslips.payroll_process_sno')
                        ->where( 'egc_payroll_payslips.employee_sno', $item->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->select('egc_payroll_payslips.sno')
                        ->first();
                $payrollAttendance = DB::table('egc_payroll_attendance_summaries')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_attendance_summaries.payroll_process_sno')
                        ->where( 'egc_payroll_attendance_summaries.employee_sno', $item->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->first();

                $details =DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno',$payroll->sno)
                    ->where('status', 0)
                    ->get();

                $componentSum = function ($code) use ($details) {
                    return (float)$details->where('component_code',$code)->sum('actual_amount');
                };

                    $onduty = $componentSum('ONDUTY');
                    $incentive = $componentSum('INCENTIVE');
                    $variable = $componentSum('VARIABLE');
                    $lopAmount =$payroll->lop_amount ?? 0;
                    $pf = $componentSum('PF');
                    $esi = $componentSum('ESI');
                    $pt = $componentSum('PT');
                    $tax = $componentSum('TAX');
                    $employerPf = $componentSum('EMPLOYER_PF');
                    $employerEsi = $componentSum('EMPLOYER_ESI');

                $data[] = [
                    'sno' => $item->sno,
                    'entry_id' => $payrollSlipData->sno ?? 0,
                    'entry_encrypt' => encrypt($payrollSlipData->sno ?? 0),
                    'name' => $item->staff_name,
                    'company_base_color' => $item->company_base_color,
                    'is_saved' => true,
                    'isStaffImage' => $isStaffImage,
                    'staff_last_date' => $item->staff_last_date,
                    'notice_start_date' => $item->notice_start_date,
                    'notice_end_date' => $item->notice_end_date,
                    'staff_status' => $item->status,
                    'data' => [
                        'staff_image' => $item->staff_image,
                        'company_type' => $item->company_type,
                        'company_id' => $item->company_id,
                        'entity_id' => $item->entity_id,
                        'gender' => $item->gender,
                        'staff_code' => $item->staff_code,
                        'nick_name' => $item->nick_name,
                        'company_name' => $item->company_name,
                        'salary_company_name' => $item->salary_company_name,
                        'salary_company_base_color' => $item->salary_company_base_color,
                        'department_name' => $item->department_name,
                        'job_role_name' => $item->job_role_name,
                        'date_of_joining' => $item->date_of_joining,
                        'salary_type' => $item->salary_type,
                        'salary_date' => $item->salary_date,
                        'basic_salary' => round($item->basic_salary ?? 0),
                    ],
                    'dataParoll' => [
                        'components' => $components,
                        'rules' => $rules,
                        'earnings' => round($payroll->gross_earnings ?? 0),
                        'deductions' => round($payroll->gross_deductions ?? 0),
                        'gross_salary' => round($payroll->gross_earnings ?? 0),
                        'net' => round($payroll->net_payable ?? 0),
                        'employer_contribution' => round($payroll->employer_contribution ?? 0),
                        'lop_days' => round($payroll->lop_days ?? 0, 2),
                        'onduty' => round($onduty),
                        'variable' => round($variable),
                        'incentive' => round($incentive),
                        'pf' => round($pf),
                        'esi' => round($esi),
                        'pt' => round($pt),
                        'tax' => round($tax),
                        'lopAmount' => round($lopAmount),
                        'employerPf' => round($employerPf),
                        'employerEsi' => round($employerEsi),

                    ],
                    'present_days' => round($payroll->present_days ?? 0),
                    'absent_days' => round($payroll->absent_days ?? 0),
                    'late_count' => round($payroll->late_count ?? 0),
                    'lop_amount' => round($payroll->lop_amount ?? 0),
                    'pf_amount' => round($pfAmount, 2),
                    'esi_amount' => round($esiAmount, 2),
                    'net_salary' => round($payroll->net_payable ?? 0),
                ];
            }
        }

        $previousDate = Carbon::create($year, $month, 1)->subMonth();
        $previousMonth = $previousDate->month;
        $previousYear  = $previousDate->year;

        $previousGross = 0;
        $previousDeduction = 0;
        $previousNet = 0;

        $previousPayroll = $this->getMonthlySalarySummary($previousMonth, $previousYear ,$isLiveMode);
        
        $previousGross = round($previousPayroll['gross']);
        $previousDeduction = round($previousPayroll['net']);
        $previousNet =  round($previousPayroll['deduction']);

       
        if ($request->ajax()) {
            return response()->json([
                'mode' => $isLiveMode ? 'live' : 'processed',
                'payroll_process_id' => $payrollProcess ? $payrollProcess->sno : null,
                'is_payroll_saved_id' => !$isLiveMode,
                'is_payroll_saved' => !$isLiveMode,
                'data' => $data,
                'total' => $staff->total(),
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'per_page' => $staff->perPage(),
                'total_net_salary' => round($overallNetSalary),
                'total_gross_salary' => round($overallGrossSalary),
                'total_fixed_gross_salary' => round($overallFixedGrossSalary),
                'total_deduction' => round($overallDeduction),
                'earnings_summary' => $earningsSummary ?? [],
                'deductions_summary' => $deductionsSummary ?? [],
                'current' => [
                    'gross' => round($overallGrossSalary),
                    'deduction' => round($overallDeduction),
                    'net' => round($overallNetSalary),
                ],
                'previous' => [
                    'gross' => round($previousGross),
                    'deduction' => round($previousDeduction),
                    'net' => round($previousNet),
                ],
                'workflow' => [
                    'generated' => !$isLiveMode,
                    'hr_verified' => !empty($verification),
                    'frozen' => $payrollProcess  ? ($payrollProcess->payroll_freeze == 1)  : false,
                    'process_status' => $payrollProcess->process_status ?? 'draft',
                    'payroll_process_id' => $payrollProcess->sno ?? null
                ]
            ]);
        }
          $company_list = CompanyModel::where('status', 0)->get();
           
        return view(
            'content.hr_management.hr_general.manage_payroll.payroll_report_list',
            [
                'mode' => $isLiveMode ? 'live' : 'processed',
                'perpage' => $perpage,
                'search_filter' => $search_filter ?? '',
                'payslipCount' => $payslipCount ?? 0,
                'month' => $month,
                'company_list' => $company_list,
                'year' => $year,
            ]
        );
    }


    public function indexNew13(Request $request)
    {

        $page = $request->input('page', 1);
        $perpage = (int) $request->input('sorting_filter', 25);
        $offset = ($page - 1) * $perpage;
        $company_fill = $request->company_fill ?? '';
        $entity_fill = $request->entity_fill ?? '';
        $department_fill = $request->department_fill ?? '';
        $division_fill = $request->division_fill ?? '';
        $job_role_fill = $request->job_role_fill ?? '';
        $date_filter = $request->dt_fill_issue_rpt ?? '';
        $from_date_filter = $request->to_dt_iss_rpt ?? '';
        $to_date_filter = $request->to_date_fillter_textbox ?? '';
        $search_filter = $request->search_filter ?? '';
        $salary_date_fill = $request->salary_date_fill ?? '';
        $salary_type_fill = $request->salary_type_fill ?? '';

        $helper = new \App\Helpers\Helpers();
        $common_date_format = $helper->general_setting_data()->date_format ?? 'd-M-y';

        // ------------------ MONTH PARSING ------------------
        $month_filter = $request->get('month_filter', date('M-Y'));
        // return  $month_filter;
        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);

        // $month_filter = $request->get('month_filter', date('m-Y'));  // Default to numeric month (11-2025)
        // $parsedDate = Carbon::createFromFormat('m-Y', $month_filter);

        $month = $parsedDate->month ?? date('m');

        $year  = $parsedDate->year ?? date('Y');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
        $today = Carbon::today();

        // Limit end date to today if month is current
        if ($today->between($startDate, $endDate)) {
            $endDate = $today;
        }


        $holidays = DB::table('egc_holiday')
            ->where('status', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('hol_date', [$startDate, $endDate])
                    ->orWhereBetween('hol_end_date', [$startDate, $endDate]);
            })->get();

        $holidayDates = collect();
        foreach ($holidays as $hol) {
            $start = Carbon::parse($hol->hol_date);
            $end = $hol->hol_end_date ? Carbon::parse($hol->hol_end_date) : $start;
            foreach (CarbonPeriod::create($start, $end) as $d) {
                $holidayDates->push($d->format('Y-m-d'));
            }
        }
        $holidayDates = $holidayDates->unique();

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
        $today = Carbon::today();
        $pfPercentage = $helper->payroll_setting('pf_percentage');
        $pfDeductionAmount = $helper->payroll_setting('pf_deduction_amount');
        $PTDeductionAmount = $helper->payroll_setting('pt_deduction_amount');
        $esiPercentage = $helper->payroll_setting('esi_percentage');
        $esiLimit = $helper->payroll_setting('esi_salary_limit');

        $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

        $staff = DB::table('egc_staff as s')
            ->leftJoin('egc_staff_attendance as a', function ($join) use ($month, $year) {
                $join->on('a.staff_id', '=', 's.sno')
                    ->whereMonth('a.date', $month)
                    ->whereYear('a.date', $year)
                    ->where('a.status', 0);
            })
            ->leftJoin('egc_company', 's.company_id', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', 'egc_entity.sno')
            ->join('egc_department', 's.department_id', 'egc_department.sno')
            ->join('egc_division', 's.division_id', 'egc_division.sno')
            ->join('egc_job_role', 's.job_role_id', 'egc_job_role.sno')

            ->where('s.role_id', '!=', 1)
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.status', 0)
            ->select(
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                's.basic_salary',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_id',
                's.company_type',
                's.entity_id',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_company.company_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name',
                's.per_day_salary',
                's.casual_leave_count_per_month',
                // DB::raw("SUM(CASE WHEN a.attendance='P' THEN 1 ELSE 0 END) as present_days"),
                DB::raw("SUM(CASE WHEN a.attendance='A' THEN 1 ELSE 0 END) as absent_days"),
                DB::raw("
                    SUM(
                        CASE 
                            WHEN a.attendance IN ('P','OD','PR') THEN 1
                            WHEN a.attendance = 'L' AND a.leave_type IN ('first_half','second_half') THEN 0.5
                            ELSE 0
                        END
                    ) as present_days
                "),

                DB::raw("
                    SUM(
                        CASE 
                            WHEN a.attendance = 'L' AND a.leave_type = 'full' THEN 1
                            WHEN a.attendance = 'L' AND a.leave_type IN ('first_half','second_half') THEN 0.5
                            ELSE 0
                        END
                    ) as leave_days
                "),
                // DB::raw("SUM(CASE WHEN a.attendance='L' THEN 1 ELSE 0 END) as leave_days"),
                DB::raw("SUM(CASE WHEN a.attendance='PR' THEN 1 ELSE 0 END) as permission_days"),
                DB::raw("SUM(CASE WHEN a.attendance='OD' THEN 1 ELSE 0 END) as onduty_days")
            );



        if ($search_filter != '') {
            $staff->where(function ($subquery) use ($search_filter) {
                $subquery->where('s.staff_name', 'LIKE', "%{$search_filter}%")
                    ->orWhere('s.nick_name', 'LIKE', "%{$search_filter}%")
                    ->orWhere('s.mobile_no', 'LIKE', "%{$search_filter}%");
            });
        }

        if ($company_fill != '') {
            if ($company_fill == 'egc') {
                $staff->where('s.company_type', 1);
            } else {
                $staff->where('s.company_id', 'LIKE', $company_fill);
            }
        }

        if ($entity_fill) {
            $staff->where('s.entity_id', $entity_fill);
        }

        if ($salary_date_fill) {
            $staff->where('s.salary_date', $salary_date_fill);
        }
        if ($salary_type_fill) {
            $staff->where('s.salary_type', $salary_type_fill);
        }


        if ($department_fill) {
            $staff->where('s.department_id', $department_fill);
        }

        // if ($division_fill) {
        //     $staff->where('s.division_id', $division_fill);
        // }

        // if ($job_role_fill) {
        //   $staff->where('s.job_role_id', $job_role_fill);
        // }
        $today = date('Y-m-d');
        if ($date_filter == "today") {
            $todayDate = date("Y-m-d");
            $staff->whereDate('s.date_of_joining', $todayDate);
        } elseif ($date_filter == "week") {
            $today = date('l');
            if ($today == "Sunday") {
                $weekFromDate = date('Y-m-d', strtotime("sunday 0 week"));
                $weekToDate = date('Y-m-d', strtotime("saturday 1 week"));
            } else {
                $weekFromDate = date('Y-m-d', strtotime("sunday -1 week"));
                $weekToDate = date('Y-m-d', strtotime("saturday 0 week"));
            }
            $staff->whereBetween('s.date_of_joining', [$weekFromDate, $weekToDate]);
        } elseif ($date_filter == "monthly") {
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');
            $staff->whereBetween('s.date_of_joining', [$firstDayOfMonth, $lastDayOfMonth]);
        } elseif ($date_filter == "custom_date") {
            if ($from_date_filter && $to_date_filter) {
                $fromDate = date('Y-m-d', strtotime($from_date_filter));
                $toDate = date('Y-m-d', strtotime($to_date_filter));
                $staff->whereBetween('s.date_of_joining', [$fromDate, $toDate]);
            } elseif ($from_date_filter) {
                $fromDate = date('Y-m-d', strtotime($from_date_filter));
                $staff->where('s.date_of_joining', '>=', $fromDate);
            } elseif ($to_date_filter) {
                $toDate = date('Y-m-d', strtotime($to_date_filter));
                $staff->where('s.date_of_joining', '<=', $toDate);
            }
        } elseif ($date_filter == "0_2_months") {
            $startDate = date('Y-m-d', strtotime('-2 months'));
            $staff->whereBetween('s.date_of_joining', [$startDate, $today]);
        } elseif ($date_filter == "2_4_months") {
            $startDate = date('Y-m-d', strtotime('-4 months'));
            $endDate = date('Y-m-d', strtotime('-2 months'));
            $staff->whereBetween('s.date_of_joining', [$startDate, $endDate]);
        } elseif ($date_filter == "4_6_months") {
            $startDate = date('Y-m-d', strtotime('-6 months'));
            $endDate = date('Y-m-d', strtotime('-4 months'));
            $staff->whereBetween('s.date_of_joining', [$startDate, $endDate]);
        } elseif ($date_filter == "6_12_months") {
            $startDate = date('Y-m-d', strtotime('-12 months'));
            $endDate = date('Y-m-d', strtotime('-6 months'));
            $staff->whereBetween('s.date_of_joining', [$startDate, $endDate]);
        } elseif ($date_filter == "1_2_years") {
            $startDate = date('Y-m-d', strtotime('-2 years'));
            $endDate = date('Y-m-d', strtotime('-1 year'));
            $staff->whereBetween('s.date_of_joining', [$startDate, $endDate]);
        } elseif ($date_filter == "gt_2_years") {
            $endDate = date('Y-m-d', strtotime('-2 years'));
            $staff->where('s.date_of_joining', '<=', $endDate);
        } elseif ($date_filter == "gt_1_year") {
            $endDate = date('Y-m-d', strtotime('-1 year'));
            $staff->where('s.date_of_joining', '<=', $endDate);
        } elseif ($date_filter == "gt_6_months") {
            $endDate = date('Y-m-d', strtotime('-6 months'));
            $staff->where('s.date_of_joining', '<=', $endDate);
        }

        $staff = $staff->groupBy(
            's.sno',
            's.staff_id',
            's.staff_name',
            's.basic_salary',
            's.nick_name',
            's.mobile_no',
            's.staff_image',
            's.company_id',
            's.company_type',
            's.entity_id',
            's.gender',
            's.salary_type',
            's.salary_date',
            's.date_of_joining',
            'egc_entity.entity_name',
            'egc_entity.entity_short_name',
            'egc_company.company_name',
            'egc_company.company_base_color',
            'egc_department.department_name',
            'egc_division.division_name',
            'egc_job_role.job_position_name',
            's.per_day_salary',
            's.casual_leave_count_per_month',
        )
            ->orderBy('s.date_of_joining', 'desc');
        $allStaffIds = $staff->pluck('s.sno');
        $staffQuery = clone $staff;
        $staff = $staff->paginate($perpage);

        $is_payroll_saved = DB::table('egc_payroll_runs_new')
            ->where('egc_payroll_runs_new.payroll_month', $month)
            ->where('egc_payroll_runs_new.payroll_year', $year)
            ->where('egc_payroll_runs_new.status', 0)
            ->exists();

        // ------------------ SHIFT LOGS & ATTENDANCE ------------------
        $allShiftLogs = DB::table('egc_shift_time_log as stl')
            ->join('egc_shift_day_times as sdt', 'stl.change_shift_id', '=', 'sdt.shift_id')
            ->whereIn('stl.staff_id', $allStaffIds)
            ->where('sdt.status', 0)
            ->select('stl.staff_id', 'stl.start_date', 'stl.end_date', 'sdt.day_name')
            ->orderBy('stl.start_date')
            ->get()
            ->groupBy('staff_id');

        $attendanceRecords = DB::table('egc_staff_attendance')
            ->whereIn('staff_id', $allStaffIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 0)
            ->get()
            ->groupBy('staff_id')
            ->map(fn($items) => $items->keyBy('date'));


        // return $staff;
        $totalGrossSalary = 0;
        $totalDeduction = 0;
        $totalNetSalary = 0;
        $allStaff = $staffQuery->get();
        $overallPayrollData = [];
        $earningsSummary = [];
        $deductionsSummary = [];
        $componentSummary = [];
        foreach ($allStaff as $item) {
            $presentDays = $item->present_days ?? 0;
            $absentDays = $item->absent_days ?? 0;
            $leaveDays = $item->leave_days ?? 0;
            $permissionDays = $item->permission_days ?? 0;
            $ondutyDays = $item->onduty_days ?? 0;

            $holidayCount = 0;
            $weekOffCount = 0;

            $staffShift = $allShiftLogs[$item->sno] ?? collect();
            foreach (CarbonPeriod::create(Carbon::create($year, $month, 1), Carbon::create($year, $month, 1)->endOfMonth()) as $d) {
                $dateKey = $d->format('Y-m-d');

                if ($holidayDates->contains($dateKey)) {
                    $holidayCount++;
                    continue;
                }

                $dayName = strtolower($d->format('D'));
                $shift = $staffShift->firstWhere('day_name', $dayName);
                if (!$shift) $weekOffCount++;
            }
            $item->weekOffCount = $weekOffCount;
            $item->holidayCount = $holidayCount;
            $totalPresentEarningWithoutWK = $presentDays + $permissionDays + $ondutyDays + $holidayCount;
            $totalSalaryEarning = $presentDays + $permissionDays + $ondutyDays + $holidayCount + $weekOffCount;
            $item->salary_earning_days = $totalSalaryEarning;
            $item->salary_earning_days_not_wk = $totalPresentEarningWithoutWK;
            $dataParoll = $this->calculatePayroll($item, $month, $year, $holidayDates, $allShiftLogs, $attendanceRecords);

            $overallPayrollData[] = $dataParoll;

            $totalGrossSalary += $dataParoll['earnings'] ?? 0;
            $totalDeduction += $dataParoll['deductions'] ?? 0;
            $totalNetSalary += $dataParoll['net'] ?? 0;
        }

        foreach ($overallPayrollData as $payroll) {
            if (!isset($payroll['components'])) continue;

            foreach ($payroll['components'] as $component) {
                $name = $component['name'];
                $type = $component['type']; // earning or deduction
                $amount = $component['amount'];

                if (!isset($componentSummary[$name])) {
                    $componentSummary[$name] = [
                        'name' => $name,
                        'type' => $type,
                        'total' => 0
                    ];
                }

                $componentSummary[$name]['total'] += $amount;
            }
        }

        foreach ($componentSummary as $comp) {
            if ($comp['type'] === 'earning') {
                $earningsSummary[] = $comp;
            } else {
                $deductionsSummary[] = $comp;
            }
        }

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

        if ($request->ajax()) {
            $data = $staff->map(function ($item) use ($helper, $month, $year, $pfPercentage, $pfDeductionAmount, $PTDeductionAmount, $esiLimit, $esiPercentage, $general_setting, &$totalGrossSalary, &$totalDeduction, &$totalNetSalary, $holidayDates, $allShiftLogs, $attendanceRecords) {

                // $absentDays = $item->absent_days ?? 0;
                $presentDays = $item->present_days ?? 0;
                $absentDays = $item->absent_days ?? 0;
                $leaveDays = $item->leave_days ?? 0;
                $permissionDays = $item->permission_days ?? 0;
                $ondutyDays = $item->onduty_days ?? 0;

                $holidayCount = 0;
                $weekOffCount = 0;

                $staffShift = $allShiftLogs[$item->sno] ?? collect();
                foreach (CarbonPeriod::create(Carbon::create($year, $month, 1), Carbon::create($year, $month, 1)->endOfMonth()) as $d) {
                    $dateKey = $d->format('Y-m-d');

                    if ($holidayDates->contains($dateKey)) {
                        $holidayCount++;
                        continue;
                    }

                    $dayName = strtolower($d->format('D'));
                    $shift = $staffShift->firstWhere('day_name', $dayName);
                    if (!$shift) $weekOffCount++;
                }
                $item->weekOffCount = $weekOffCount;
                $item->holidayCount = $holidayCount;
                $totalPresentEarningWithoutWK = $presentDays + $permissionDays + $ondutyDays + $holidayCount;
                $totalSalaryEarning = $presentDays + $permissionDays + $ondutyDays + $holidayCount + $weekOffCount;
                $item->salary_earning_days = $totalSalaryEarning;
                $item->salary_earning_days_not_wk = $totalPresentEarningWithoutWK;

                $dataParoll = $this->calculatePayroll($item, $month, $year, $holidayDates, $allShiftLogs, $attendanceRecords);

                // $totalGrossSalary += $dataParoll['earnings'] ?? 0;
                // $totalDeduction += $dataParoll['deductions'] ?? 0;
                // $totalNetSalary += $dataParoll['net'] ?? 0;

                if ($item->company_type == 1) {
                    $filePath = public_path('staff_images/Management/' . $item->staff_image);
                } else {
                    $filePath = public_path('staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image);
                }

                $isStaffImage = $item->staff_image != '' && file_exists($filePath) ? 1 : 0;

                $components = DB::table('egc_staff_salary_components_new as ssc')
                    ->join('egc_salary_components as pc', 'pc.sno', '=', 'ssc.component_id')
                    ->where('ssc.staff_id', $item->sno)
                    ->where('ssc.status', 0)
                    ->select(
                        'pc.component_name',
                        'pc.type as component_type',
                        'pc.calculation_type',
                        'ssc.amount',
                    )
                    ->get();

                $entry = DB::table('egc_payroll_entries_new')
                    ->select('egc_payroll_entries_new.sno')
                    ->join('egc_payroll_runs_new', 'egc_payroll_runs_new.sno', '=', 'egc_payroll_entries_new.payroll_run_sno')
                    ->where('egc_payroll_entries_new.staff_id', $item->sno)
                    ->where('egc_payroll_runs_new.payroll_month', $month)
                    ->where('egc_payroll_runs_new.payroll_year', $year)
                    ->first();

                // $components = DB::table('egc_payroll_entry_components_new as c')
                //     ->join('egc_salary_components as sc','sc.sno','=','c.component_id')
                //     ->where('c.payroll_entry_id',$id)
                //     ->select('sc.component_name as name','c.amount')
                //     ->get();

                $isSaved = $entry ? true : false;
                $entryId = $entry->sno ?? null;

                $lateCount = DB::table('egc_staff_attendance as att')
                    ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')

                    ->join('egc_shift_time_log as stl', function ($join) {
                        $join->on('stl.staff_id', '=', 'att.staff_id');
                    })

                    ->join('egc_shift_day_times as sd', function ($join) {
                        $join->on('sd.shift_id', '=', 'stl.change_shift_id')
                            ->where('sd.status', 0);
                    })
                    ->where('att.staff_id', $item->sno)
                    // 👉 Month filter
                    ->whereMonth('att.date', $month)
                    ->whereYear('att.date', $year)
                    ->where('att.status', 0)
                    ->whereNotNull('att.in_time')
                    // ✅ JOINING DATE CHECK
                    ->whereRaw("DATE(att.date) >= DATE(s.date_of_joining)")
                    // ✅ SHIFT DATE RANGE CHECK
                    ->whereRaw("
                        DATE(att.date) BETWEEN stl.start_date 
                        AND IFNULL(stl.end_date, '9999-12-31')
                    ")
                    // ✅ DAY MATCH (Mon, Tue...)
                    ->whereRaw("
                        LOWER(sd.day_name) = LOWER(DATE_FORMAT(att.date, '%a'))
                    ")
                    // ✅ LATE CHECK
                    // ->whereRaw("
                    //     TIME(att.in_time) > TIME(sd.time_from)
                    // ")
                    ->whereRaw("TIME(att.in_time) > ADDTIME(sd.time_from, '00:11:00')")
                    ->count();
                // return $lateCount;

                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }


                return [
                    'sno' => $item->sno,
                    'name' => $item->staff_name,
                    'isStaffImage' => $isStaffImage,
                    'company_name' => $item->company_name,
                    'company_base_color' => $item->company_base_color,
                    'present_days' => $item->present_days ?? 0,
                    'absent_days' => $absentDays,
                    'leave_days' => $item->leave_days ?? 0,
                    'permission_days' => $item->permission_days ?? 0,
                    'onduty_days' => $item->onduty_days ?? 0,
                    'late_count' => $lateCount,
                    'staff_id' => $item->staff_code,
                    'basic_salary' => $item->basic_salary,
                    'dataParoll' => $dataParoll,
                    'is_saved' => $isSaved,
                    'entry_id' => $entryId,
                    'data' => $item,
                    'encrypted_id' => $helper->encrypt_decrypt($item->sno, 'encrypt'),
                    'base_encrypt' => encrypt($item->sno),
                    'entry_encrypt' => encrypt($entryId),
                    'short_encrypt_id' => base64_encode($item->sno),
                ];
            });

            return response()->json([
                'data' => $data,
                'is_payroll_saved' => $is_payroll_saved,
                'deductions_summary' => $deductionsSummary,
                'earnings_summary' => $earningsSummary,
                'total_gross_salary' => $totalGrossSalary,
                'total_deduction' => $totalDeduction,
                'total_net_salary' => $totalNetSalary,
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'total' => $staff->total(),
            ]);
        }
        $source_list = SourceModel::where('status', 0)->orderBy('sno', 'ASC')->get();
        $company_list = CompanyModel::where('status', 0)->get();

        $payslipCount = DB::table('egc_staff')->where('status', 0)->where('is_payslip', 1)->count();
        return view('content.hr_management.hr_general.manage_payroll.payroll_list_new', [
            'staff' => $staff,
            'perpage' => $perpage,
            'search_filter' => $search_filter,
            'company_list' => $company_list,
            'source_list' => $source_list,
            'company_fill' => $company_fill,
            'payslipCount' => $payslipCount,
        ]);
    }

    public function Payslip($id, Request $request)
    {

        $page = $request->input('page', 1);
        $perpage = (int) $request->input('sorting_filter', 25);
        $offset = ($page - 1) * $perpage;
        $search_filter = $request->search_filter ?? '';
        $job_role_fill = $request->job_role_fill ?? '';
        $closing_date_filt = $request->closing_date_filt ?? '';
        $exp_type_filt = $request->exp_type_filt ?? '';
        $date_filter = $request->dt_fill_issue_rpt ?? '';
        $from_date_filter = $request->to_dt_iss_rpt ?? '';
        $to_date_filter = $request->to_date_fillter_textbox ?? '';


        return view('content.hr_management.hr_general.manage_payroll.payslip_print', []);
    }

    public function create(Request $r)
    {
        DB::table('egc_payroll_runs')
            ->insert([
                'payroll_month' => $r->payroll_month,
                'payroll_year' => $r->payroll_year,
                'run_status' => 'DRAFT',
                'created_by' => $r->user()->user_id ?? 1
            ]);

        return response()->json(['status' => true]);
    }


    public function process($runSno, Request $r)
    {

        DB::transaction(function () use ($runSno, $r) {
            try {
                $run = DB::table('egc_payroll_runs')
                    ->where('sno', $runSno)
                    ->first();


                $employees = DB::table('egc_staff_payroll_profile')
                    ->where('status', '0')
                    ->get();

                foreach ($employees as $emp) {

                    $employeeSno = $emp->staff_id;

                    // get salary structure
                    $mapping = DB::table('egc_staff_salary_mapping')
                        ->where('staff_id', $employeeSno)
                        ->whereNull('effective_to')
                        ->first();

                    if (!$mapping) continue;

                    $components = DB::table('egc_salary_structure_components')
                        ->where(
                            'salary_structure_sno',
                            $mapping->salary_structure_sno
                        )
                        ->get();

                    $gross = 0;
                    $deduction = 0;

                    // create payroll entry

                    $entryId = DB::table('egc_payroll_entries')
                        ->insertGetId([
                            'payroll_run_sno' => $runSno,
                            'staff_id' => $employeeSno,
                            'gross_salary' => 0,
                            'total_deductions' => 0,
                            'net_salary' => 0,
                            'created_by' => $r->user()->user_id ?? 1
                        ]);

                    // return $entryId;

                    foreach ($components as $c) {

                        $amount = $c->value;
                        $type = DB::table('egc_pay_components')
                            ->where('sno', $c->component_sno)
                            ->value('component_type');
                        DB::table('egc_payroll_entry_components')
                            ->insert([
                                'payroll_entry_sno' => $entryId,
                                'component_sno' => $c->component_sno,
                                'amount' => $amount,
                                'created_by' => $r->user()->user_id ?? 1
                            ]);

                        if ($type == 'EARNING') {
                            $gross += $amount;
                        } else {
                            $deduction += $amount;
                        }
                    }
                    $net = $gross - $deduction;
                    DB::table('egc_payroll_entries')
                        ->where('sno', $entryId)
                        ->update([
                            'gross_salary' => $gross,
                            'total_deductions' => $deduction,
                            'net_salary' => $net
                        ]);
                }

                // finalize
                DB::table('egc_payroll_runs')
                    ->where('sno', $runSno)
                    ->update([
                        'run_status' => 'FINALIZED'
                    ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error processing payroll: ' . $e->getMessage()
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Payroll processed successfully'
        ]);
    }


    public function indexPayslip()
    {

        $payslips = DB::table('egc_payroll_entries as e')
            ->leftJoin('egc_staff as emp', 'emp.sno', '=', 'e.staff_id')
            ->leftJoin('egc_payroll_runs as r', 'r.sno', '=', 'e.payroll_run_sno')
            ->select(
                'e.sno',
                'emp.staff_name as employee_name',
                'emp.staff_id as employee_code',
                'r.payroll_month',
                'r.payroll_year',
                'e.gross_salary',
                'e.total_deductions',
                'e.net_salary'
            )
            ->get();

        return view('content.hr_management.hr_general.manage_payroll.payslips', compact('payslips'));
    }


    public function viewpayslip($entrySno)
    {

        $data = $this->getPayslipData($entrySno);

        return view('content.hr_management.hr_general.manage_payroll.payslip_view', $data);
    }


    // public function pdfpayslip($entrySno, Request $r)
    // {

    //     $data = $this->getPayslipData($entrySno);

    //     $pdf = PDF::loadView('content.hr_management.hr_general.manage_payroll.payslip_pdf', $data);

    //     DB::table('egc_payslip_logs')
    //         ->insert([
    //             'payroll_entry_sno' => $entrySno,
    //             'generated_at' => now(),
    //             'generated_by' => $r->user()->user_id ?? 1
    //         ]);

    //     return $pdf->download('payslip.pdf');
    // }


    private function getPayslipData($entrySno)
    {

        $entry = DB::table('egc_payroll_entries as e')
            ->leftJoin('egc_staff as emp', 'emp.sno', '=', 'e.staff_id')
            ->leftJoin('egc_staff_bank_details as b', 'b.staff_id', '=', 'emp.sno')
            ->leftJoin('egc_payroll_runs as r', 'r.sno', '=', 'e.payroll_run_sno')
            ->select(
                'emp.staff_name as name',
                'emp.staff_id as employee_code',
                'b.bank_account_no',
                'b.bank_name',
                'e.gross_salary',
                'e.total_deductions',
                'e.net_salary',
                'r.payroll_month',
                'r.payroll_year'
            )
            ->where('e.sno', $entrySno)
            ->first();


        $components = DB::table('egc_payroll_entry_components as c')
            ->leftJoin(
                'egc_pay_components as p',
                'p.sno',
                '=',
                'c.component_sno'
            )
            ->select(
                'p.component_name',
                'p.component_type',
                'c.amount'
            )
            ->where('c.payroll_entry_sno', $entrySno)
            ->get();

        return [
            'entry' => $entry,
            'components' => $components
        ];
    }


    public function getPreviewData(Request $request)
    {

        $month = $request->month;
        $year = $request->year;
        $helper = new \App\Helpers\Helpers();
        $pfPercentage = $helper->payroll_setting('pf_percentage');
        $esiPercentage = $helper->payroll_setting('esi_percentage');
        $esiLimit = $helper->payroll_setting('esi_salary_limit');
        $pfDeductionAmount = $helper->payroll_setting('pf_deduction_amount');
        $PTDeductionAmount = $helper->payroll_setting('pt_deduction_amount');

        $staff = DB::table('egc_staff as s')

            ->leftJoin('egc_staff_attendance as a', function ($join) use ($month, $year) {

                $join->on('a.staff_id', '=', 's.sno')
                    ->whereMonth('a.date', $month)
                    ->whereYear('a.date', $year)
                    ->where('a.status', 0);
            })

            ->where('s.status', 0)
            ->where('s.role_id', '>', 1)

            ->select(

                's.sno',
                's.staff_name as name',
                's.staff_id as staff_code',
                's.basic_salary',
                's.per_day_salary',
                's.casual_leave_count_per_month',
                DB::raw("SUM(CASE WHEN a.attendance='P' THEN 1 ELSE 0 END) as present_days"),

                DB::raw("SUM(CASE WHEN a.attendance='A' THEN 1 ELSE 0 END) as absent_days"),

                DB::raw("SUM(CASE WHEN a.attendance='L' THEN 1 ELSE 0 END) as leave_days"),

                DB::raw("SUM(CASE WHEN a.attendance='PR' THEN 1 ELSE 0 END) as permission_days"),

                DB::raw("SUM(CASE WHEN a.attendance='OD' THEN 1 ELSE 0 END) as onduty_days")
                // DB::raw("SUM(CASE WHEN a.attendance='P' THEN 1 ELSE 0 END) as present_days"),

                // DB::raw("SUM(CASE WHEN a.attendance='A' THEN 1 ELSE 0 END) as absent_days")

            )

            ->groupBy('s.sno', 's.staff_id', 's.staff_name', 's.basic_salary', 's.per_day_salary', 's.casual_leave_count_per_month',)

            ->get();




        $data = [];

        foreach ($staff as $row) {

            $absentDays = $row->absent_days ?? 0;
            $lateCount = DB::table('egc_staff_attendance')
                ->where('staff_id', $row->sno)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 0)
                ->whereNotNull('in_time')
                ->whereTime('in_time', '>', '09:30:00')
                ->count();

            $allowedLeave = $row->casual_leave_count_per_month ?? 0;
            $lateAllowed = $helper->payroll_setting('late_allowed_per_month');
            $lopPerLate = $helper->payroll_setting('lop_per_late');
            // $lopDays = max(0,$absent - $allowedLeave);

            // absent LOP
            $absentLopDays = max(0, $absentDays - $allowedLeave);


            // late LOP
            $extraLate = max(0, $lateCount - $lateAllowed);

            $lateLopDays = $extraLate * $lopPerLate;


            // total LOP
            $totalLopDays = $absentLopDays + $lateLopDays;


            // LOP amount
            $lopAmount = $totalLopDays * $row->per_day_salary;

            // $lopAmount = $lopDays * $row->per_day_salary;

            // $pfAmount = ($row->basic_salary * $pfPercentage)/100;

            $pfAmount = $pfDeductionAmount ? $pfDeductionAmount : ($row->basic_salary * $pfPercentage) / 100;

            $esiAmount = 0;
            $ptAmount = $PTDeductionAmount ?? 0;
            if ($esiLimit) {
                if ($row->basic_salary <= $esiLimit)
                    $esiAmount = ($row->basic_salary * $esiPercentage) / 100;
            } else {
                $esiAmount = ($row->basic_salary * $esiPercentage) / 100;
            }


            // if($row->basic_salary <= $esiLimit)
            // $esiAmount = ($row->basic_salary * $esiPercentage)/100;

            $netSalary = $row->basic_salary - ($lopAmount + $pfAmount + $esiAmount + $ptAmount);

            $entry = DB::table('egc_payroll_entries')
                ->join('egc_payroll_runs', 'egc_payroll_runs.sno', '=', 'egc_payroll_entries.payroll_run_sno')
                ->where('egc_payroll_entries.staff_id', $row->sno)
                ->where('egc_payroll_runs.payroll_month', $month)
                ->where('egc_payroll_runs.payroll_year', $year)
                ->first();

            $isSaved = $entry ? true : false;

            $entryId = $entry->sno ?? null;

            $data[] = [

                'staff_id' => $row->sno,
                'name' => $row->name,
                'staff_code' => $row->staff_code,

                'basic_salary' => $row->basic_salary,

                'present_days' => $row->present_days ?? 0,

                'absent_days' => $absentDays,

                'leave_days' => $row->leave_days ?? 0,

                'permission_days' => $row->permission_days ?? 0,

                'onduty_days' => $row->onduty_days ?? 0,

                'late_count' => $lateCount,

                'lop_days' => $totalLopDays,

                'lop_amount' => $lopAmount,

                'pf_amount' => $pfAmount,

                'pt_amount' => $ptAmount,

                'esi_amount' => $esiAmount,

                'is_saved' => $isSaved,
                'entry_id' => $entryId,

                'net_salary' => $netSalary

            ];
        }

        return response()->json($data);
    }

    public function savePayroll(Request $request)
    {

        $month_filter = $request->month_filter;
        $month = $request->month;
        $year = $request->year;

        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
        $month = $parsedDate->month ?? date('m');
        $year  = $parsedDate->year ?? date('Y');
        // return $request;

        // prevent duplicate save

        $exists = DB::table('egc_payroll_runs')

            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->exists();

        if ($exists) {

            return response()->json([
                'status' => false,
                'message' => 'Payroll already saved for this month'
            ]);
        }


        DB::beginTransaction();

        try {


            // create payroll run

            $runId = DB::table('egc_payroll_runs')

                ->insertGetId([

                    'payroll_month' => $month,
                    'payroll_year' => $year,
                    'run_type' => 'MANUAL',
                    'created_by' => auth()->id()

                ]);


            // get preview data using same logic

            $preview = $this->getPreviewData($request)->getData();

            // return $preview; 
            foreach ($preview as $row) {


                $entryId = DB::table('egc_payroll_entries')

                    ->insertGetId([

                        'payroll_run_sno' => $runId,

                        'staff_id' => $row->staff_id,

                        'basic_salary' => $row->basic_salary,

                        'per_day_salary' => $row->basic_salary,

                        'present_days' => $row->present_days,

                        'absent_days' => $row->absent_days,

                        'allowed_leave' => 0,

                        'lop_days' => $row->lop_days,

                        'lop_amount' => $row->lop_amount,

                        'pf_amount' => $row->pf_amount,
                        'pt_amount' => $row->pt_amount,

                        'esi_amount' => $row->esi_amount,

                        'incentive_amount' => 0,

                        'net_salary' => $row->net_salary,

                        'created_by' => auth()->id()

                    ]);


                // save components

                // DB::table('egc_payroll_entry_components')
                //     ->insert([
                //         [
                //             'payroll_entry_sno'=>$entryId,
                //             'component_name'=>'Basic Salary',
                //             'component_type'=>'EARNING',
                //             'amount'=>$row->basic_salary
                //         ],
                //         [
                //             'payroll_entry_sno'=>$entryId,
                //             'component_name'=>'LOP',
                //             'component_type'=>'DEDUCTION',
                //             'amount'=>$row->lop_amount
                //         ],
                //         [
                //             'payroll_entry_sno'=>$entryId,
                //             'component_name'=>'PF',
                //             'component_type'=>'DEDUCTION',
                //             'amount'=>$row->pf_amount
                //         ],
                //         [
                //             'payroll_entry_sno'=>$entryId,
                //             'component_name'=>'ESI',
                //             'component_type'=>'DEDUCTION',
                //             'amount'=>$row->esi_amount
                //         ]
                // ]);

                // Get all active components and map by component_code
                $componentMap = DB::table('egc_pay_components')
                    ->where('status', 0)
                    ->pluck('sno', 'component_code');

                $components = [];

                $amountMapping = [
                    'BASIC'     => $row->basic_salary,
                    'LOP'       => $row->lop_amount,
                    'PF'        => $row->pf_amount,
                    'ESI'       => $row->esi_amount,
                    'INCENTIVE' => $row->incentive_amount ?? 0,
                    'PT'        => $row->pt_amount ?? 0, // if exists
                ];

                foreach ($amountMapping as $code => $amount) {

                    if (!empty($amount) && isset($componentMap[$code])) {

                        $components[] = [
                            'payroll_entry_sno' => $entryId,
                            'component_sno'     => $componentMap[$code],
                            'amount'            => $amount,
                            'created_by'        => auth()->id(),
                            'created_at'        => now(),
                            'status'            => 0
                        ];
                    }
                }

                if (!empty($components)) {
                    DB::table('egc_payroll_entry_components')->insert($components);
                }
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payroll saved successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCompanyBank($id, Request $request)
    {
        $entity_id = $id;
        $branch_data = DB::table('egc_company_bank_accounts')->where('status', 0)->where('company_id', $entity_id)->orderBy('sno', 'desc')->get();
        return response([
            'status' => 200,
            'message' => null,
            'error_msg' => null,
            'data' => $branch_data,
        ], 200);
    }
    // public function getSalaryComponents(Request $request, $staff_id = null)
    // {
    //     $query = DB::table('egc_salary_components')->where('status', 1);

    //     if ($staff_id) {
    //         // Only include components assigned to staff (optional)
    //         $staff_components = DB::table('egc_staff_salary_components')
    //                             ->where('staff_id', $staff_id)
    //                             ->pluck('component_id')
    //                             ->toArray();

    //         $query->whereIn('sno', $staff_components);
    //     }

    //     $branch_data = $query->orderBy('sno', 'desc')->get();

    //     return response([
    //         'status' => 200,
    //         'data' => $branch_data,
    //     ]);
    // }
    public function getSalaryComponentsold()
    {
        $components = DB::table('egc_salary_components')
            ->where('status', 0)
            ->orderBy('type')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $components
        ]);
    }

    public function getSalaryComponents()
    {

        $data = DB::table('egc_salary_components')
            ->where('status', 0)
            ->get();

        return response()->json($data);
    }



    public function export(Request $request)
    {
        $monthFilter = $request->month_filter ?? date('M-Y');

        $parsedDate = Carbon::createFromFormat(
            'M-Y',
            $monthFilter
        );

        $month = $parsedDate->month;
        $year  = $parsedDate->year;

         $salary_company = $request->salary_company ?? '';
        $company_fill = $request->company_fill ?? '';
        $entity_fill  = $request->entity_fill ?? '';
        $salary_type_fill = $request->salary_type_fill ?? '';
        $salary_date_fill = $request->salary_date_fill ?? '';
        $only_bank_fill = $request->only_bank_fill ? 1: 0;

        $endOfMonth = date(
            'Y-m-t',
            strtotime("$year-$month-01")
        );

        $payrollProcess = DB::table(
            'egc_payroll_processes'
        )
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();

        $isLiveMode = !$payrollProcess;

        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth(
                            's.notice_end_date',
                            $month
                        )
                        ->whereYear(
                            's.notice_end_date',
                            $year
                        );
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                })

                ->orWhere(function ($special) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);

                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            // ->where('s.sno', 200)
             ->select(
                's.*',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary as basic_salary',
                'egc_staff_salary_accounts.per_day_salary',
                'egc_company.company_name',
                'egc_entity.entity_name',
                'egc_department.department_name'
            );

        if ($salary_company != '') {
            $staffQuery->where(
                'egc_staff_salary_accounts.salary_company_id',
                $salary_company
            );
        }
        if ($company_fill != '') {
            $staffQuery->where(
                's.company_id',
                $company_fill
            );
        }

        if ($entity_fill != '') {
            $staffQuery->where(
                's.entity_id',
                $entity_fill
            );
        }

        if ($salary_type_fill != '') {
            $staffQuery->where(
                's.salary_type',
                $salary_type_fill
            );
        }

        if ($salary_date_fill != '') {
            $staffQuery->where(
                's.salary_date',
                $salary_date_fill
            );
        }

        $staffList = $staffQuery
            ->orderBy('s.date_of_joining')
            ->orderBy('s.sno')
            ->get();

        $rows = [];

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

        foreach ($staffList as $staff) {

                if ($staff->company_type == 1) {
                    $staff->company_name = $general_setting->title;
                }

        

            if ($isLiveMode) {

                $payroll =
                    app(
                        \App\Services\PayrollPreviewService::class
                    )->calculateLivePayroll(
                        $staff,
                        $month,
                        $year,
                        $only_bank_fill
                    );

                $components =
                    collect(
                        $payroll['components']
                    );

                $onduty =
                    (float)$components
                    ->where('code', 'ONDUTY')
                    ->sum('amount');

                $variable =
                    (float)$components
                    ->where('code', 'VARIABLE')
                    ->sum('amount');

                $incentive =
                    (float)$components
                    ->where('code', 'INCENTIVE')
                    ->sum('amount');

                $pf =
                    (float)$components
                    ->where('code', 'PF')
                    ->sum('amount');

                $esi =
                    (float)$components
                    ->where('code', 'ESI')
                    ->sum('amount');

                $pt =
                    (float)$components
                    ->where('code', 'PT')
                    ->sum('amount');

                $tax =
                    (float)$components
                    ->where('code', 'TAX')
                    ->sum('amount');

                $employerPf =
                    (float)$components
                    ->where('code', 'EMPLOYER_PF')
                    ->sum('amount');

                $employerEsi =
                    (float)$components
                    ->where('code', 'EMPLOYER_ESI')
                    ->sum('amount');

                $lopAmount =
                    (float)$components
                    ->where('code', 'LOP')
                    ->sum('amount');

                $rows[] = [

                    $staff->sno,
                    $staff->staff_id,
                    $staff->staff_name,
                    $staff->company_name,
                    $staff->entity_name,
                    $staff->department_name,

                    $staff->basic_salary,
                    $payroll['net_salary'],
                    $payroll['earnings'],
                    $payroll['deductions'],

                    $onduty,
                    $incentive,
                    $variable,

                    $payroll['earning_days'],
                    $payroll['lop_days'],
                    $lopAmount,

                    $pf,
                    $esi,
                    $pt,
                    $tax,

                    $employerPf,
                    $employerEsi,
                ];
            } else {

                $processed =
                    DB::table(
                        'egc_payroll_employee_payrolls'
                    )
                    ->where('staff_id', $staff->sno)
                    ->where(
                        'payroll_month',
                        $month
                    )
                    ->where(
                        'payroll_year',
                        $year
                    )
                    ->where(
                        'status',
                        0
                    )
                    ->first();

                if (!$processed) {
                    continue;
                }

                $details =
                    DB::table(
                        'egc_payroll_employee_payroll_details'
                    )
                    ->where(
                        'payroll_employee_sno',
                        $processed->sno
                    )
                    ->where('status', 0)
                    ->get();

                $componentSum = function ($code) use ($details) {

                    return (float)$details
                        ->where(
                            'component_code',
                            $code
                        )
                        ->sum('actual_amount');
                };

                $rows[] = [

                    $staff->sno,
                    $staff->staff_id,
                    $staff->staff_name,
                    $staff->company_name,
                    $staff->entity_name,
                    $staff->department_name,

                    $processed->gross_earnings,
                    $processed->net_payable,
                    $processed->gross_earnings,
                    $processed->gross_deductions,

                    $componentSum('ONDUTY'),
                    $componentSum('INCENTIVE'),
                    $componentSum('VARIABLE'),

                    $processed->earning_days,
                    $processed->lop_days,
                    $processed->lop_amount,

                    $componentSum('PF'),
                    $componentSum('ESI'),
                    $componentSum('PT'),
                    $componentSum('TAX'),

                    $componentSum('EMPLOYER_PF'),
                    $componentSum('EMPLOYER_ESI'),
                ];
            }
        }

        return Excel::download(
            new PayrollExport($rows),
            'Payroll_'.$month.'_'.$year.'.xlsx'
        );
    }

    public function payrollList(Request $req)
    {
        $month = Carbon::parse($req->month)->month;
        $year  = Carbon::parse($req->month)->year;

        $data = DB::table('egc_payroll_entries_new as e')
            ->join('egc_payroll_runs_new as r', 'r.sno', '=', 'e.payroll_run_sno')
            ->join('egc_staff as s', 's.sno', '=', 'e.staff_id')
            ->where('r.payroll_month', $month)
            ->where('r.payroll_year', $year)
            ->select(
                'e.sno as entry_id',
                's.staff_name as name',
                'e.basic_salary',
                'e.total_earnings',
                'e.total_deductions',
                'e.net_salary',
                'e.lop_days',
                DB::raw("TO_BASE64(e.sno) as entry_encrypt")
            )
            ->get();

        return response()->json($data);
    }


    public function previewPayroll(Request $req)
    {
        $month_filter = $req->get('month_filter', date('M-Y'));
        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
        $month = $parsedDate->month;
        $year  = $parsedDate->year;



        $staffList = DB::table('egc_staff')
            ->where('status', 0)
            ->where('sno', '>', 0)
            ->get();

        $data = [];

        foreach ($staffList as $staff) {

            $data[] = $this->calculatePayroll($staff, $month, $year);
        }

        return response()->json($data);
    }

    public function lockPayroll(Request $req)
    {

        $month_filter = $req->get('month_filter', date('M-Y'));
        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
        $month = $parsedDate->month;
        $year  = $parsedDate->year;

        DB::table('egc_payroll_runs_new')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->update(['status' => 1]);

        return response()->json(['status' => 200]);
    }
    // private function calculatePayroll($staff,$month,$year)
    // {
    //     $basic = $staff->basic_salary;

    //     $components = DB::table('egc_staff_salary_components as sc')
    //         ->join('egc_salary_components as c','c.sno','=','sc.component_id')
    //         ->where('sc.staff_id',$staff->sno)
    //         ->where('sc.status',0)
    //         ->select('sc.*','c.type','c.component_name')
    //         ->get();

    //     $earnings = $basic;
    //     $deductions = 0;

    //     $breakdown = [];

    //     foreach($components as $c){

    //         $amount = $c->amount;

    //         if($c->calculation_type == 'percentage'){
    //             $amount = ($basic * $c->amount)/100;
    //         }

    //         if($c->type == 'earning'){
    //             $earnings += $amount;
    //         }else{
    //             $deductions += $amount;
    //         }

    //         $breakdown[] = [
    //             'name'=>$c->component_name,
    //             'amount'=>$amount,
    //             'type'=>$c->type
    //         ];
    //     }

    //     // 👉 RULE ENGINE
    //     $rules = $this->applyRules($staff,$month,$year,$basic);
    //     return  $rules;

    //     $deductions += $rules['deductions'];

    //     return [
    //         'staff_id'=>$staff->sno,
    //         'name'=>$staff->staff_name,
    //         'basic'=>$basic,
    //         'earnings'=>$earnings,
    //         'deductions'=>$deductions,
    //         'net'=>$earnings - $deductions,
    //         'lop_days'=>$rules['lop_days'],
    //         'lop_amount'=>$rules['lop_amount'],
    //         'components'=>$breakdown
    //     ];
    // }

    private function calculatePayroll($staff, $month, $year, $holidayDates, $allShiftLogs, $attendanceRecords)
    {
        $basic = $staff->basic_salary;

        $components = DB::table('egc_staff_salary_components_new as sc')
            ->join('egc_salary_components as c', 'c.sno', '=', 'sc.component_id')
            ->where('sc.staff_id', $staff->sno)
            ->where('sc.status', 0)
            ->where('sc.is_active', 1)
            ->select('sc.*', 'c.type', 'c.component_name', 'c.rule_type')
            ->get();

        $isDateOfJoiningPast = Carbon::parse($staff->date_of_joining)->lte(Carbon::create($year, $month, 1));




        // $presentDays = ($staff->salary_earning_days ?? 0);25-03-26
        // $perDaySalary = $staff->per_day_salary ?? 0;
        // $basicEarned = $presentDays * $perDaySalary;
        // $earnings = $basicEarned;

        $deductions = 0;
        $breakdown = [];

        // $earnings += $basic;

        // if(!$isDateOfJoiningPast){
        //     $presentDays = ($staff->salary_earning_days_not_wk ?? 0);
        //     $perDaySalary = $staff->per_day_salary ?? 0;
        //     $basicEarned = $presentDays * $perDaySalary;
        //     $earnings = round($basicEarned, 1);

        //      $breakdown[] = [  //25-03-26
        //         'name' => 'Basic Salary',
        //         'amount' => round($basicEarned, 1),
        //         'type' => 'earning'
        //     ];
        // }
        if (!$isDateOfJoiningPast) {
            $perDaySalary = $staff->per_day_salary ?? 0;
            $start = Carbon::parse($staff->date_of_joining);
            $end   = Carbon::create($year, $month, 1)->endOfMonth();
            $earningDays = 0;
            $staffShift = $allShiftLogs[$staff->sno] ?? collect();
            foreach (CarbonPeriod::create($start, $end) as $d) {
                $dateKey = $d->format('Y-m-d');
                // ✅ HOLIDAY → count as present
                if ($holidayDates->contains($dateKey)) {
                    $earningDays++;
                    continue;
                }
                // ✅ CHECK WEEKOFF
                $dayName = strtolower($d->format('D'));
                $shift = $staffShift->firstWhere('day_name', $dayName);
                if (!$shift) {
                    // 👉 NO SHIFT → default Sunday as weekoff
                    if ($d->isSunday()) {
                        $earningDays++;
                        continue;
                    }
                } else {
                    // 👉 SHIFT EXISTS → this day is weekoff
                    $earningDays++;
                    continue;
                }

                // ✅ ATTENDANCE CHECK
                $attendance = $attendanceRecords[$staff->sno][$dateKey] ?? null;
                if ($attendance) {
                    if (in_array($attendance->attendance, ['P', 'PR', 'OD'])) {
                        $earningDays++;
                    }

                    if ($attendance->attendance == 'L') {
                        if ($attendance->leave_type == 'full') {
                            // no add
                        } elseif (in_array($attendance->leave_type, ['first_half', 'second_half'])) {
                            $earningDays += 0.5;
                        }
                    }
                }
            }

            $basicEarned = $earningDays * $perDaySalary;
            $earnings = round($basicEarned, 1);

            $breakdown[] = [
                'name' => 'Basic Salary',
                'amount' => round($basicEarned, 1),
                'type' => 'earning'
            ];
        } else {
            $earnings = $basic;
            $breakdown[] = [
                'name' => 'Basic Salary',
                'amount' => round($basic, 1),
                'type' => 'earning'
            ];
        }



        // $breakdown[] = [25-03-26
        //     'name' => 'Basic Salary',
        //     'amount' => $basicEarned,
        //     'type' => 'earning'
        // ];

        foreach ($components as $c) {
            $amount = $c->amount;

            if ($c->calculation_type == 'percentage' && $c->rule_type != 'esi' && $c->rule_type != 'pf') {
                $amount = ($basic * $c->amount) / 100;
            }

            if ($c->calculation_type == 'fixed' && $c->rule_type != 'esi' && $c->rule_type != 'pf') {
                $amount = $c->amount;
            }

            if ($c->rule_type == 'esi') {
                $esiRule = $this->getRule('esi');

                if ($esiRule) {
                    $limit = $esiRule['condition']['salary_limit'] ?? 0;

                    if ($basic > $limit) {
                        $amount = 0;
                    } else {
                        if ($c->calculation_type == 'percentage') {
                            $amount = ($basic * $c->amount) / 100;
                        } else {
                            $amount = $c->amount;
                        }
                    }
                }
            }

            if ($c->rule_type == 'pf') {
                $pfRule = $this->getRule('pf');

                if ($pfRule) {
                    $max = $pfRule['action']['max_limit'] ?? 0;

                    if ($max > 0) {
                        $amount = min($amount, $max);
                    }
                }
            }

            if ($c->type == 'earning') {
                $earnings += $amount;
            } else {
                $deductions += $amount;
            }

            $breakdown[] = [
                'name' => $c->component_name,
                'amount' => round($amount, 1),
                'type' => $c->type
            ];
        }


        // foreach($components as $c){

        //     //  $amount = 0;

        //         $amount = $c->amount;

        //         if($c->calculation_type == 'percentage' && $c->rule_type != 'esi' && $c->rule_type != 'pf'){
        //             $amount = ($basic * $c->amount)/100;
        //         }

        //         if($c->calculation_type == 'fixed' && $c->rule_type != 'esi' && $c->rule_type != 'pf'){
        //            $amount = $c->amount; 
        //         }



        //         if($c->rule_type == 'esi'){

        //             $esiRule = $this->getRule('esi');

        //             if($esiRule){
        //                 $limit = $esiRule['condition']['salary_limit'] ?? 0;

        //                 if($basic > $limit){
        //                     $amount = 0; 
        //                 }else{
        //                     if($c->calculation_type == 'percentage' ){
        //                         $amount = ($basic * $c->amount)/100;
        //                     }else{
        //                         $amount =  $c->amount;
        //                     }
        //                 }
        //             }
        //         }


        //         if($c->rule_type == 'pf'){

        //             $pfRule = $this->getRule('pf');
        //             // return $amount;

        //             if($pfRule){
        //                 $max = $pfRule['action']['max_limit'] ?? 0;

        //                 if($max > 0){
        //                     $amount = min($amount, $max);
        //                 }
        //             }
        //         }

        //         //  return $amount ;
        //         // return $amount;

        //         // if($c->rule_type == 'ot'){
        //         //     $amount = $this->getOvertime($staff,$month,$year);
        //         // }

        //         // if($c->rule_type == 'incentive'){
        //         //     $amount = $this->getIncentive($staff,$month,$year);
        //         // }

        //         // if($c->rule_type == 'travel'){
        //         //     $amount = $this->getTravel($staff,$month,$year);
        //         // }

        //     if($c->type == 'earning'){
        //         $earnings += $amount;
        //     }else{
        //         $deductions += $amount;
        //     }

        //     $breakdown[] = [
        //         'name'=>$c->component_name,
        //         'amount'=>$amount,
        //         'type'=>$c->type
        //     ];
        // }



        // ✅ APPLY RULE ENGINE
        $rules = $this->applyRules($staff, $month, $year, $basic);

        // foreach ($breakdown as &$comp) {
        //     if ($comp['name'] == 'Loss of Pay') {
        //         $comp['amount'] = round($rules['lop_amount'], 2);
        //     }
        // }
        // unset($comp);

        $lopFound = false;

        foreach ($breakdown as &$comp) {
            if ($comp['name'] == 'Loss of Pay') {
                $comp['amount'] = round($rules['lop_amount'], 2);
                $lopFound = true;
            }
        }
        unset($comp);

        // If not found → add manually
        if (!$lopFound && $rules['lop_amount'] > 0) {
            $breakdown[] = [
                'component_id' => 8,
                'name' => 'Loss of Pay',
                'amount' => round($rules['lop_amount'], 2),
                'type' => 'deduction'
            ];
        }


        // return $rules;

        $deductions += round($rules['total_deductions'], 1);
        $net = $earnings - $deductions;

        // return [
        //     'staff_id'=>$staff->sno,
        //     'name'=>$staff->staff_name,
        //     'basic'=>$basic,
        //     'earnings'=>$earnings,
        //     'deductions'=>$deductions,
        //     'net'=>$earnings - $deductions,
        //     'lop_days'=>$rules['lop_days'],
        //     'lop_amount'=>$rules['lop_amount'],
        //     'rule_breakdown'=>$rules['breakdown'],
        //     'components'=>$breakdown
        // ];

        return [
            'staff_id' => $staff->sno,
            'name' => $staff->staff_name,
            'basic' => $basic,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'net' => $net,
            'lop_days' => $rules['lop_days'],
            'lop_amount' => $rules['lop_amount'],
            'components' => $breakdown,
            'rules' => $rules['breakdown'],

            // ADD THESE 👇
            'present_days' => $present ?? 0,
            'absent_days' => $absent ?? 0,
            'late_count' => $late ?? 0
        ];
    }


    public function runPayroll13(Request $req)
    {
        DB::beginTransaction();
        try {
            $month_filter = $req->get('month_filter', date('M-Y'));
            $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
            $month = $parsedDate->month;
            $year  = $parsedDate->year;


            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
            $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
            $today = Carbon::today();
            if ($today->between($startDate, $endDate)) {
                $endDate = $today;
            }


            $holidays = DB::table('egc_holiday')
                ->where('status', 0)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('hol_date', [$startDate, $endDate])
                        ->orWhereBetween('hol_end_date', [$startDate, $endDate]);
                })->get();

            $holidayDates = collect();
            foreach ($holidays as $hol) {
                $start = Carbon::parse($hol->hol_date);
                $end = $hol->hol_end_date ? Carbon::parse($hol->hol_end_date) : $start;
                foreach (CarbonPeriod::create($start, $end) as $d) {
                    $holidayDates->push($d->format('Y-m-d'));
                }
            }
            $holidayDates = $holidayDates->unique();

            $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

            // $staffList = DB::table('egc_staff')
            //     ->where('status',0)
            //     ->get();

            $staffList = DB::table('egc_staff as s')
                ->leftJoin('egc_staff_attendance as a', function ($join) use ($month, $year) {
                    $join->on('a.staff_id', '=', 's.sno')
                        ->whereMonth('a.date', $month)
                        ->whereYear('a.date', $year)
                        ->where('a.status', 0);
                })
                ->leftJoin('egc_company', 's.company_id', 'egc_company.sno')
                ->leftJoin('egc_entity', 's.entity_id', 'egc_entity.sno')
                ->join('egc_department', 's.department_id', 'egc_department.sno')
                ->join('egc_division', 's.division_id', 'egc_division.sno')
                ->join('egc_job_role', 's.job_role_id', 'egc_job_role.sno')
                ->where('s.role_id', '!=', 1)
                ->whereDate('s.date_of_joining', '<=', $endOfMonth)
                ->where('s.status', 0)
                ->select(
                    's.sno',
                    's.staff_name',
                    's.staff_id as staff_code',
                    's.basic_salary',
                    's.nick_name',
                    's.mobile_no',
                    's.staff_image',
                    's.company_id',
                    's.company_type',
                    's.entity_id',
                    's.gender',
                    's.salary_type',
                    's.salary_date',
                    's.date_of_joining',
                    'egc_entity.entity_name',
                    'egc_entity.entity_short_name',
                    'egc_company.company_name',
                    'egc_company.company_base_color',
                    'egc_department.department_name',
                    'egc_division.division_name',
                    'egc_job_role.job_position_name as job_role_name',
                    's.per_day_salary',
                    's.casual_leave_count_per_month',
                    // DB::raw("SUM(CASE WHEN a.attendance='P' THEN 1 ELSE 0 END) as present_days"),
                    DB::raw("SUM(CASE WHEN a.attendance='A' THEN 1 ELSE 0 END) as absent_days"),
                    DB::raw("
                SUM(
                    CASE 
                        WHEN a.attendance IN ('P','OD','PR') THEN 1
                        WHEN a.attendance = 'L' AND a.leave_type IN ('first_half','second_half') THEN 0.5
                        ELSE 0
                    END
                ) as present_days
            "),

                    DB::raw("
                SUM(
                    CASE 
                        WHEN a.attendance = 'L' AND a.leave_type = 'full' THEN 1
                        WHEN a.attendance = 'L' AND a.leave_type IN ('first_half','second_half') THEN 0.5
                        ELSE 0
                    END
                ) as leave_days
            "),
                    // DB::raw("SUM(CASE WHEN a.attendance='L' THEN 1 ELSE 0 END) as leave_days"),
                    DB::raw("SUM(CASE WHEN a.attendance='PR' THEN 1 ELSE 0 END) as permission_days"),
                    DB::raw("SUM(CASE WHEN a.attendance='OD' THEN 1 ELSE 0 END) as onduty_days")
                )->groupBy(
                    's.sno',
                    's.staff_id',
                    's.staff_name',
                    's.basic_salary',
                    's.nick_name',
                    's.mobile_no',
                    's.staff_image',
                    's.company_id',
                    's.company_type',
                    's.entity_id',
                    's.gender',
                    's.salary_type',
                    's.salary_date',
                    's.date_of_joining',
                    'egc_entity.entity_name',
                    'egc_entity.entity_short_name',
                    'egc_company.company_name',
                    'egc_company.company_base_color',
                    'egc_department.department_name',
                    'egc_division.division_name',
                    'egc_job_role.job_position_name',
                    's.per_day_salary',
                    's.casual_leave_count_per_month',
                );
            $allStaffIds = $staffList->pluck('s.sno');
            $staffList = $staffList->get();

            // create payroll run
            $runId = DB::table('egc_payroll_runs_new')->insertGetId([
                'payroll_month' => $month,
                'payroll_year' => $year,
                'created_at' => now()
            ]);

            $allShiftLogs = DB::table('egc_shift_time_log as stl')
                ->join('egc_shift_day_times as sdt', 'stl.change_shift_id', '=', 'sdt.shift_id')
                ->whereIn('stl.staff_id', $allStaffIds)
                ->where('sdt.status', 0)
                ->select('stl.staff_id', 'stl.start_date', 'stl.end_date', 'sdt.day_name')
                ->orderBy('stl.start_date')
                ->get()
                ->groupBy('staff_id');

            $attendanceRecords = DB::table('egc_staff_attendance')
                ->whereIn('staff_id', $allStaffIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 0)
                ->get()
                ->groupBy('staff_id')
                ->map(fn($items) => $items->keyBy('date'));

            foreach ($staffList as $staff) {

                $presentDays = $staff->present_days ?? 0;
                $absentDays = $staff->absent_days ?? 0;
                $leaveDays = $staff->leave_days ?? 0;
                $permissionDays = $staff->permission_days ?? 0;
                $ondutyDays = $staff->onduty_days ?? 0;

                $holidayCount = 0;
                $weekOffCount = 0;

                $staffShift = $allShiftLogs[$staff->sno] ?? collect();
                foreach (CarbonPeriod::create(Carbon::create($year, $month, 1), Carbon::create($year, $month, 1)->endOfMonth()) as $d) {
                    $dateKey = $d->format('Y-m-d');

                    if ($holidayDates->contains($dateKey)) {
                        $holidayCount++;
                        continue;
                    }

                    $dayName = strtolower($d->format('D'));
                    $shift = $staffShift->firstWhere('day_name', $dayName);
                    if (!$shift) $weekOffCount++;
                }
                $staff->weekOffCount = $weekOffCount;
                $staff->holidayCount = $holidayCount;
                $totalPresentEarningWithoutWK = $presentDays + $permissionDays + $ondutyDays + $holidayCount;
                $totalSalaryEarning = $presentDays + $permissionDays + $ondutyDays + $holidayCount + $weekOffCount;
                $staff->salary_earning_days = $totalSalaryEarning;
                $staff->salary_earning_days_not_wk = $totalPresentEarningWithoutWK;

                $this->processStaffPayroll($staff, $runId, $month, $year, $holidayDates, $allShiftLogs, $attendanceRecords);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Payroll generated successfully'
            ]);
        } catch (\Exception $e) {
            //  ROLLBACK EVERYTHING
            DB::rollBack();

            \Log::error('Payroll Error: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Payroll generation failed',
                'error' => $e->getMessage() // remove in production if needed
            ], 500);
        }
    }




    //  private function applyRules($staff,$month,$year,$basic)
    // {
    //     $helper = new \App\Helpers\Helpers();

    //     $attendance = DB::table('egc_staff_attendance')
    //         ->where('staff_id',$staff->sno)
    //         ->whereMonth('date',$month)
    //         ->whereYear('date',$year)
    //         ->where('status',0)
    //         ->get();

    //     $present = 0;
    //     $absent = 0;
    //     $late = 0;

    //     foreach($attendance as $a){

    //         if($a->attendance == 'P') $present++;
    //         if($a->attendance == 'A') $absent++;

    //         // late logic (simple)
    //         if($a->in_time && $a->in_time > '09:30:00'){
    //             $late++;
    //         }
    //     }

    //     // 👉 SETTINGS
    //     $casualLeave = $staff->casual_leave_count_per_month;
    //     $lateAllowed = $helper->payroll_setting('late_allowed_per_month');
    //     $lopPerLate = $helper->payroll_setting('lop_per_late');

    //     // 👉 LOP CALC
    //     $absentLop = max(0, $absent - $casualLeave);
    //     $extraLate = max(0, $late - $lateAllowed);
    //     $lateLop = $extraLate * $lopPerLate;

    //     $totalLop = $absentLop + $lateLop;

    //     $lopAmount = $totalLop * $staff->per_day_salary;

    //     // 👉 PF
    //     $pf = ($basic * $helper->payroll_setting('pf_percentage'))/100;

    //     // 👉 ESI
    //     $esi = 0;
    //     if($basic <= $helper->payroll_setting('esi_salary_limit')){
    //         $esi = ($basic * $helper->payroll_setting('esi_percentage'))/100;
    //     }

    //     // 👉 PT
    //     $pt = $helper->payroll_setting('pt_amount');

    //     return [
    //         'deductions'=>$lopAmount + $pf + $esi + $pt,
    //         'lop_days'=>$totalLop,
    //         'lop_amount'=>$lopAmount
    //     ];
    // }



    private function applyRules($staff, $month, $year, $basic)
    {

        // return $month;
        // 👉 load rules
        $rules = DB::table('egc_payroll_rules')
            ->where('status', 0)
            ->get();


        // attendance
        $attendance = DB::table('egc_staff_attendance')
            ->where('staff_id', $staff->sno)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 0)
            ->get();

        // return $staff->sno;

        $lateCount = DB::table('egc_staff_attendance as att')
            ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')

            ->join('egc_shift_time_log as stl', function ($join) {
                $join->on('stl.staff_id', '=', 'att.staff_id');
            })

            ->join('egc_shift_day_times as sd', function ($join) {
                $join->on('sd.shift_id', '=', 'stl.change_shift_id')
                    ->where('sd.status', 0);
            })

            ->where('att.staff_id', $staff->sno)

            // 👉 Month filter
            ->whereMonth('att.date', $month)
            ->whereYear('att.date', $year)

            ->where('att.status', 0)
            ->whereNotNull('att.in_time')

            // ✅ JOINING DATE CHECK
            ->whereRaw("DATE(att.date) >= DATE(s.date_of_joining)")

            // ✅ SHIFT DATE RANGE CHECK
            ->whereRaw("
                DATE(att.date) BETWEEN stl.start_date 
                AND IFNULL(stl.end_date, '9999-12-31')
            ")

            // ✅ DAY MATCH (Mon, Tue...)
            ->whereRaw("
                LOWER(sd.day_name) = LOWER(DATE_FORMAT(att.date, '%a'))
            ")

            // ✅ LATE CHECK
            // ->whereRaw("
            //     TIME(att.in_time) > TIME(sd.time_from)
            // ")
            ->whereRaw("TIME(att.in_time) > ADDTIME(sd.time_from, '00:11:00')")

            ->count();

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
        $today = Carbon::today();

        // Limit end date to today if month is current
        if ($today->between($startDate, $endDate)) {
            $endDate = $today;
        }

        $holidays = DB::table('egc_holiday')
            ->where('status', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('hol_date', [$startDate, $endDate])
                    ->orWhereBetween('hol_end_date', [$startDate, $endDate]);
            })
            ->get();

        $holidayDates = collect();

        foreach ($holidays as $hol) {
            $start = Carbon::parse($hol->hol_date);
            $end   = $hol->hol_end_date ? Carbon::parse($hol->hol_end_date) : $start;

            foreach (CarbonPeriod::create($start, $end) as $d) {
                $holidayDates->push($d->format('Y-m-d'));
            }
        }

        $holidayDates = $holidayDates->unique();


        $present = 0;
        $absent = 0;
        $late = $lateCount;


        foreach ($attendance as $a) {
            $dateKey = $a->date;

            if ($holidayDates->contains($dateKey)) {
                continue;
            }
            if ($a->attendance == 'P') $present++;
            if ($a->attendance == 'OD') $present++;
            if ($a->attendance == 'PR') $present++;
            if ($a->attendance == 'A') $absent++;
            if ($a->attendance == 'L') {
                if ($a->leave_type == 'full') {
                    $absent += 1;
                } elseif (in_array($a->leave_type, ['first_half', 'second_half'])) {
                    $absent += 0.5;
                }
            }
            // if($a->attendance == 'L') $absent++; 25-03-26
            // if($a->in_time && $a->in_time > '09:30:00') $late++;
        }



        $totalDeduction = 0;
        $lopDays = 0;
        $lopAmount = 0;

        $ruleBreakdown = [];

        foreach ($rules as $rule) {

            $condition = json_decode($rule->condition_json, true);
            $action = json_decode($rule->action_json, true);

            // =====================
            //  LOP RULE
            // =====================
            if ($rule->rule_type == 'lop') {

                $allowedLeave = $staff->casual_leave_count_per_month ?? 0;

                // return $absent;

                $lopDays = max(0, $absent - $allowedLeave);

                $perDay = $staff->per_day_salary;

                $lopAmount = $lopDays * $perDay;

                $totalDeduction += $lopAmount;

                $ruleBreakdown[] = [
                    'rule' => 'LOP',
                    'days' => $lopDays,
                    'amount' => $lopAmount
                ];
            }

            // =====================
            //  LATE RULE
            // =====================
            if ($rule->rule_type == 'late') {

                $allowed = $condition['allowed_per_month'] ?? 0;
                $lopPerLate = $action['lop_per_late'] ?? 0;



                $extraLate = max(0, $late - $allowed);


                $lateLopDays = $extraLate * $lopPerLate;

                $amount = $lateLopDays * $staff->per_day_salary;

                $totalDeduction += $amount;

                $ruleBreakdown[] = [
                    'rule' => 'Late',
                    'days' => $lateLopDays,
                    'total_late_Count' => $late,
                    'allowed_late_Count' => $allowed,
                    'lopPerLate' => $lopPerLate,
                    'amount' => $amount
                ];
            }

            // =====================
            //  Permission RULE
            // =====================

            if ($rule->rule_type == 'permission') {

                $condition = json_decode($rule->condition_json, true);
                $action = json_decode($rule->action_json, true);

                $allowedHours = $condition['max_hours'] ?? 0;
                $deductionPerDay = $action['deduction'] ?? 0; // usually 0.5 or 1

                // 👉 GET TOTAL PERMISSION MINUTES
                $permissionMinutes = DB::table('egc_staff_attendance')
                    ->where('staff_id', $staff->sno)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('attendance', 'PR')
                    ->where('status', 0)
                    ->selectRaw("
                    SUM(
                        TIMESTAMPDIFF(MINUTE, start_time, end_time)
                    ) as total_minutes
                ")
                    ->value('total_minutes');

                $permissionMinutes = $permissionMinutes ?? 0;

                // 👉 Convert to hours
                $permissionHours = $permissionMinutes / 60;

                // 👉 Calculate excess
                $extraHours = max(0, $permissionHours - $allowedHours);

                // 👉 Convert hours → days
                // assume 8 hours = 1 day (you can make dynamic later)
                $workingHoursPerDay = 8;

                $deductionDays = ($extraHours / $workingHoursPerDay) * $deductionPerDay;

                $amount = $deductionDays * $staff->per_day_salary;

                $totalDeduction += $amount;

                $ruleBreakdown[] = [
                    'rule' => 'Permission',
                    'total_hours' => round($permissionHours, 2),
                    'allowed_hours' => $allowedHours,
                    'extra_hours' => round($extraHours, 2),
                    'deduction_days' => round($deductionDays, 2),
                    'amount' => round($amount, 2)
                ];
            }

            // =====================
            //  ESI RULE
            // =====================
            // if($rule->rule_type == 'esi'){

            //     $limit = $condition['salary_limit'] ?? 0;
            //     $percent = $action['percentage'] ?? 0;

            //     if($basic <= $limit){
            //         $esi = ($basic * $percent)/100;
            //         $totalDeduction += $esi;

            //         $ruleBreakdown[] = [
            //             'rule'=>'ESI',
            //             'amount'=>$esi
            //         ];
            //     }
            // }

            if ($rule->rule_type == 'esi') {

                $limit = $condition['salary_limit'] ?? 0;

                if ($basic > $limit) {

                    $ruleBreakdown[] = [
                        'rule' => 'ESI',
                        'amount' => 0,
                        'note' => 'Not applicable (salary > limit)'
                    ];

                    //  DO NOT CALCULATE HERE
                }
            }

            // =====================
            //  PF RULE
            // =====================
            // if($rule->rule_type == 'pf'){

            //     $percent = $condition['percentage'] ?? 0;
            //     $max = $action['max_limit'] ?? 0;

            //     $pf = ($basic * $percent)/100;

            //     if($max > 0){
            //         $pf = min($pf,$max);
            //     }

            //     $totalDeduction += $pf;

            //     $ruleBreakdown[] = [
            //         'rule'=>'PF',
            //         'amount'=>$pf
            //     ];
            // }

        }

        return [
            'total_deductions' => $totalDeduction,
            'lop_days' => $lopDays,
            'lop_amount' => $lopAmount,
            'breakdown' => $ruleBreakdown
        ];
    }


    private function getOvertime($staff, $month, $year)
    {
        return DB::table('egc_overtime')
            ->where('staff_id', $staff->sno)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }

    private function getIncentive($staff, $month, $year)
    {
        return DB::table('egc_incentive')
            ->where('staff_id', $staff->sno)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }

    private function getTravel($staff, $month, $year)
    {
        return DB::table('egc_travel_claim')
            ->where('staff_id', $staff->sno)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }


    private function processStaffPayroll($staff, $runId, $month, $year, $holidayDates, $allShiftLogs, $attendanceRecords)
    {
        try {

            $basic = $staff->basic_salary;

            // 👉 GET COMPONENTS
            $components = DB::table('egc_staff_salary_components_new as sc')
                ->join('egc_salary_components as c', 'c.sno', '=', 'sc.component_id')
                ->where('sc.staff_id', $staff->sno)
                ->where('sc.status', 0)
                ->select('sc.*', 'c.type', 'c.rule_type', 'c.component_name')
                ->get();

            $earnings = $basic;

            $deductions = 0;
            $compBreak = [];

            $isDateOfJoiningPast = Carbon::parse($staff->date_of_joining)->lte(Carbon::create($year, $month, 1));


            // if(!$isDateOfJoiningPast){
            //     $presentDays = ($staff->salary_earning_days_not_wk ?? 0);
            //     $perDaySalary = $staff->per_day_salary ?? 0;
            //     $basicEarned = $presentDays * $perDaySalary;
            //     $earnings = round($basicEarned, 1);

            //     $compBreak[] = [  //25-03-26
            //         'component_id' => '0',
            //         'name' => 'Basic Salary',
            //         'amount' => round($basicEarned, 1),
            //         'type' => 'earning'
            //     ];
            // }

            if (!$isDateOfJoiningPast) {
                $perDaySalary = $staff->per_day_salary ?? 0;
                $start = Carbon::parse($staff->date_of_joining);
                $end   = Carbon::create($year, $month, 1)->endOfMonth();
                $earningDays = 0;
                $staffShift = $allShiftLogs[$staff->sno] ?? collect();
                foreach (CarbonPeriod::create($start, $end) as $d) {
                    $dateKey = $d->format('Y-m-d');
                    // ✅ HOLIDAY → count as present
                    if ($holidayDates->contains($dateKey)) {
                        $earningDays++;
                        continue;
                    }
                    // ✅ CHECK WEEKOFF
                    $dayName = strtolower($d->format('D'));
                    $shift = $staffShift->firstWhere('day_name', $dayName);
                    if (!$shift) {
                        // 👉 NO SHIFT → default Sunday as weekoff
                        if ($d->isSunday()) {
                            $earningDays++;
                            continue;
                        }
                    } else {
                        // 👉 SHIFT EXISTS → this day is weekoff
                        $earningDays++;
                        continue;
                    }

                    // ✅ ATTENDANCE CHECK
                    $attendance = $attendanceRecords[$staff->sno][$dateKey] ?? null;
                    if ($attendance) {
                        if (in_array($attendance->attendance, ['P', 'PR', 'OD'])) {
                            $earningDays++;
                        }

                        if ($attendance->attendance == 'L') {
                            if ($attendance->leave_type == 'full') {
                                // no add
                            } elseif (in_array($attendance->leave_type, ['first_half', 'second_half'])) {
                                $earningDays += 0.5;
                            }
                        }
                    }
                }

                $basicEarned = $earningDays * $perDaySalary;
                $earnings = round($basicEarned, 1);

                $compBreak[] = [
                    'component_id' => '0',
                    'name' => 'Basic Salary',
                    'amount' => round($basicEarned, 1),
                    'type' => 'earning'
                ];
            } else {
                $earnings = $basic;
                $compBreak[] = [
                    'component_id' => '0',
                    'name' => 'Basic Salary',
                    'amount' => round($basic, 1),
                    'type' => 'earning'
                ];
            }

            foreach ($components as $c) {

                $amount = $c->amount;

                if ($c->calculation_type == 'percentage' && $c->rule_type != 'esi' && $c->rule_type != 'pf') {
                    $amount = ($basic * $c->amount) / 100;
                }

                if ($c->calculation_type == 'fixed' && $c->rule_type != 'esi' && $c->rule_type != 'pf') {
                    $amount = $c->amount;
                }

                if ($c->rule_type == 'esi') {
                    $esiRule = $this->getRule('esi');

                    if ($esiRule) {
                        $limit = $esiRule['condition']['salary_limit'] ?? 0;

                        if ($basic > $limit) {
                            $amount = 0;
                        } else {
                            if ($c->calculation_type == 'percentage') {
                                $amount = ($basic * $c->amount) / 100;
                            } else {
                                $amount = $c->amount;
                            }
                        }
                    }
                }

                if ($c->rule_type == 'pf') {
                    $pfRule = $this->getRule('pf');

                    if ($pfRule) {
                        $max = $pfRule['action']['max_limit'] ?? 0;

                        if ($max > 0) {
                            $amount = min($amount, $max);
                        }
                    }
                }

                // if($c->rule_type == 'ot'){
                //     $amount = $this->getOvertime($staff,$month,$year);
                // }

                // if($c->rule_type == 'incentive'){
                //     $amount = $this->getIncentive($staff,$month,$year);
                // }

                // if($c->rule_type == 'travel'){
                //     $amount = $this->getTravel($staff,$month,$year);
                // }

                if ($c->type == 'earning') {
                    $earnings += $amount;
                } else {
                    $deductions += $amount;
                }

                $compBreak[] = [
                    'component_id' => $c->component_id,
                    'name' => $c->component_name,
                    'amount' => round($amount, 1),
                    'type' => $c->type
                ];
            }

            //  APPLY RULE ENGINE
            $ruleData = $this->applyRules($staff, $month, $year, $basic);

            $lopFound = false;

            foreach ($compBreak as &$comp) {
                if ($comp['name'] == 'Loss of Pay') {
                    $comp['amount'] = round($ruleData['lop_amount'], 2);
                    $lopFound = true;
                }
            }
            unset($comp);

            // If not found → add manually
            if (!$lopFound && $ruleData['lop_amount'] > 0) {
                $compBreak[] = [
                    'component_id' => 8,
                    'name' => 'Loss of Pay',
                    'amount' => round($ruleData['lop_amount'], 2),
                    'type' => 'deduction'
                ];
            }

            $deductions += $ruleData['total_deductions'];

            $lopDays = $ruleData['lop_days'];
            $lopAmount = $ruleData['lop_amount'];


            $net = $earnings - $deductions;

            // SAVE ENTRY
            $entryId = DB::table('egc_payroll_entries_new')->insertGetId([
                'payroll_run_sno' => $runId,
                'staff_id' => $staff->sno,
                'basic_salary' => $basic,
                'total_earnings' => $earnings,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                'lop_days' => $lopDays,
                'lop_amount' => $lopAmount,
                'created_at' => now()
            ]);

            // SAVE COMPONENTS
            foreach ($compBreak as $cb) {

                DB::table('egc_payroll_entry_components_new')->insert([
                    'payroll_entry_id' => $entryId,
                    'component_id' => $cb['component_id'],
                    'amount' => $cb['amount'],
                    'type' => $cb['type']
                ]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getRule($type)
    {
        static $rules = null;

        if ($rules === null) {
            $rules = DB::table('egc_payroll_rules')
                ->where('status', 0)
                ->get()
                ->keyBy('rule_type');
        }

        if (!isset($rules[$type])) return null;

        return [
            'condition' => json_decode($rules[$type]->condition_json, true),
            'action' => json_decode($rules[$type]->action_json, true)
        ];
    }


    public function getEntryold($id)
    {


        return response()->json([
            'components' => $components
        ]);
    }


    public function getEntryv1($id, Request $req)
    {
        $month_filter = $req->get('month_filter', date('M-Y'));
        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
        $month = $parsedDate->month;
        $year  = $parsedDate->year;


        $staff = DB::table('egc_payroll_entries_new as e')
            ->join('egc_staff as s', 's.sno', 'e.staff_id')
            ->leftJoin('egc_staff_attendance as a', function ($join) use ($month, $year) {
                $join->on('a.staff_id', '=', 's.sno')
                    ->whereMonth('a.date', $month)
                    ->whereYear('a.date', $year)
                    ->where('a.status', 0);
            })
            ->leftJoin('egc_company', 's.company_id', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', 'egc_entity.sno')
            ->join('egc_department', 's.department_id', 'egc_department.sno')
            ->join('egc_division', 's.division_id', 'egc_division.sno')
            ->join('egc_job_role', 's.job_role_id', 'egc_job_role.sno')
            ->where('e.sno', $id)
            ->select(
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                'e.basic_salary',
                'e.total_earnings',
                'e.total_deductions',
                'e.net_salary',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_id',
                's.company_type',
                's.entity_id',
                's.gender',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_company.company_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name',
                's.per_day_salary',
                's.casual_leave_count_per_month',
                DB::raw("SUM(CASE WHEN a.attendance='P' THEN 1 ELSE 0 END) as present_days"),
                DB::raw("SUM(CASE WHEN a.attendance='A' THEN 1 ELSE 0 END) as absent_days"),
                DB::raw("SUM(CASE WHEN a.attendance='L' THEN 1 ELSE 0 END) as leave_days"),
                DB::raw("SUM(CASE WHEN a.attendance='PR' THEN 1 ELSE 0 END) as permission_days"),
                DB::raw("SUM(CASE WHEN a.attendance='OD' THEN 1 ELSE 0 END) as onduty_days")
                // DB::raw("SUM(CASE WHEN a.attendance='P' THEN 1 ELSE 0 END) as present_days"),
                // DB::raw("SUM(CASE WHEN a.attendance='A' THEN 1 ELSE 0 END) as absent_days")
            )
            ->groupBy(
                's.sno',
                's.staff_id',
                's.staff_name',
                'e.basic_salary',
                'e.total_earnings',
                'e.total_deductions',
                'e.net_salary',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_id',
                's.company_type',
                's.entity_id',
                's.gender',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_company.company_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name',
                's.per_day_salary',
                's.casual_leave_count_per_month',
            )
            ->first();


        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

        $absentDays = $item->absent_days ?? 0;

        $dataParoll = $this->calculatePayrollEntry($staff, $id);
        // return $dataParoll;

        // return $dataParoll;
        if ($staff->company_type == 1) {
            $filePath = public_path('staff_images/Management/' . $staff->staff_image);
        } else {
            $filePath = public_path('staff_images/Buisness/' . $staff->company_id . '/' . $staff->entity_id . '/' . $staff->staff_image);
        }

        $isStaffImage = $staff->staff_image != '' && file_exists($filePath) ? 1 : 0;

        $components = DB::table('egc_staff_salary_components_new as ssc')
            ->join('egc_salary_components as pc', 'pc.sno', '=', 'ssc.component_id')
            ->where('ssc.staff_id', $staff->sno)
            ->where('ssc.status', 0)
            ->select(
                'pc.component_name',
                'pc.type as component_type',
                'pc.calculation_type',
                'ssc.amount',
            )
            ->get();

        $entry = DB::table('egc_payroll_entries_new')
            ->select('egc_payroll_entries_new.sno')
            ->join('egc_payroll_runs_new', 'egc_payroll_runs_new.sno', '=', 'egc_payroll_entries_new.payroll_run_sno')
            ->where('egc_payroll_entries_new.staff_id', $staff->sno)
            ->where('egc_payroll_runs_new.payroll_month', $month)
            ->where('egc_payroll_runs_new.payroll_year', $year)
            ->first();



        $isSaved = $entry ? true : false;
        $entryId = $entry->sno ?? null;

        $lateCount = DB::table('egc_staff_attendance as att')
            ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')

            ->join('egc_shift_time_log as stl', function ($join) {
                $join->on('stl.staff_id', '=', 'att.staff_id');
            })

            ->join('egc_shift_day_times as sd', function ($join) {
                $join->on('sd.shift_id', '=', 'stl.change_shift_id')
                    ->where('sd.status', 0);
            })
            ->where('att.staff_id', $staff->sno)
            // 👉 Month filter
            ->whereMonth('att.date', $month)
            ->whereYear('att.date', $year)
            ->where('att.status', 0)
            ->whereNotNull('att.in_time')
            // ✅ JOINING DATE CHECK
            ->whereRaw("DATE(att.date) >= DATE(s.date_of_joining)")
            // ✅ SHIFT DATE RANGE CHECK
            ->whereRaw("
                        DATE(att.date) BETWEEN stl.start_date 
                        AND IFNULL(stl.end_date, '9999-12-31')
                    ")
            // ✅ DAY MATCH (Mon, Tue...)
            ->whereRaw("
                        LOWER(sd.day_name) = LOWER(DATE_FORMAT(att.date, '%a'))
                    ")
            // ✅ LATE CHECK
            // ->whereRaw("
            //     TIME(att.in_time) > TIME(sd.time_from)
            // ")
            ->whereRaw("TIME(att.in_time) > ADDTIME(sd.time_from, '00:11:00')")
            ->count();
        // return $lateCount;

        if ($staff->company_type == 1) {
            $staff->company_name = $general_setting->title;
            $staff->company_base_color = '#ab2b22';
        }


        $datastaff =  [
            'sno' => $staff->sno,
            'name' => $staff->staff_name,
            'isStaffImage' => $isStaffImage,
            'company_name' => $staff->company_name,
            'company_base_color' => $staff->company_base_color,
            'present_days' => $staff->present_days ?? 0,
            'absent_days' => $absentDays,
            'leave_days' => $staff->leave_days ?? 0,
            'permission_days' => $staff->permission_days ?? 0,
            'onduty_days' => $staff->onduty_days ?? 0,
            'late_count' => $lateCount,
            'staff_id' => $staff->staff_code,
            'basic_salary' => $staff->basic_salary,
            'dataParoll' => $dataParoll,
            'is_saved' => $isSaved,
            'entry_id' => $entryId,
            'data' => $staff,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $datastaff,
        ]);
    }
    public function getEntry($id, Request $req)
    {
        $month_filter = $req->get('month_filter', date('M-Y'));

        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);

        $month = $parsedDate->month;

        $year  = $parsedDate->year;

        /*
    |--------------------------------------------------------------------------
    | GET PAYROLL ENTRY
    |--------------------------------------------------------------------------
    */

        $payroll = DB::table('egc_payroll_employee_payrolls as ep')

            ->join('egc_staff as s', 's.sno', '=', 'ep.staff_id')

            ->leftJoin(
                'egc_company',
                's.company_id',
                '=',
                'egc_company.sno'
            )

            ->leftJoin(
                'egc_entity',
                's.entity_id',
                '=',
                'egc_entity.sno'
            )

            ->leftJoin(
                'egc_department',
                's.department_id',
                '=',
                'egc_department.sno'
            )

            ->leftJoin(
                'egc_division',
                's.division_id',
                '=',
                'egc_division.sno'
            )

            ->leftJoin(
                'egc_job_role',
                's.job_role_id',
                '=',
                'egc_job_role.sno'
            )

            ->where('ep.id', $id)

            ->where('ep.payroll_month', $month)

            ->where('ep.payroll_year', $year)

            ->select(

                'ep.*',

                's.sno',

                's.staff_name',

                's.staff_id as staff_code',

                's.mobile_no',

                's.staff_image',

                's.company_type',

                's.company_id',

                's.entity_id',

                's.gender',

                's.nick_name',

                's.date_of_joining',

                's.salary_type',

                's.salary_date',

                'egc_company.company_name',

                'egc_company.company_base_color',

                'egc_entity.entity_name',

                'egc_entity.entity_short_name',

                'egc_department.department_name',

                'egc_division.division_name',

                'egc_job_role.job_position_name as job_role_name'
            )

            ->first();

        if (!$payroll) {

            return response()->json([

                'status' => 'error',

                'message' => 'Payroll entry not found'
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | STAFF IMAGE
    |--------------------------------------------------------------------------
    */

        if ($payroll->company_type == 1) {

            $relativePath =
                'staff_images/Management/' .
                $payroll->staff_image;
        } else {

            $relativePath =
                'staff_images/Buisness/' .
                $payroll->company_id .
                '/' .
                $payroll->entity_id .
                '/' .
                $payroll->staff_image;
        }

        $fullPath = public_path($relativePath);

        $isStaffImage =
            (
                $payroll->staff_image &&
                file_exists($fullPath)
            ) ? 1 : 0;

        /*
    |--------------------------------------------------------------------------
    | COMPONENTS
    |--------------------------------------------------------------------------
    */

        $components = [];

        if (!empty($payroll->components)) {

            $components =
                json_decode($payroll->components, true) ?? [];
        }

        /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */

        $rules = [];

        if (!empty($payroll->rules)) {

            $rules =
                json_decode($payroll->rules, true) ?? [];
        }

        /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

        return response()->json([

            'status' => 'success',

            'data' => [

                'sno' => $payroll->sno,

                'entry_id' => $payroll->id,

                'entry_encrypt' =>
                encrypt($payroll->id),

                'name' =>
                $payroll->staff_name,

                'is_saved' => true,

                'isStaffImage' =>
                $isStaffImage,

                'company_base_color' =>
                $payroll->company_base_color,

                'present_days' =>
                round($payroll->present_days ?? 0, 2),

                'absent_days' =>
                round($payroll->absent_days ?? 0, 2),

                'leave_days' =>
                round($payroll->leave_days ?? 0, 2),

                'permission_days' =>
                round($payroll->permission_days ?? 0, 2),

                'onduty_days' =>
                round($payroll->onduty_days ?? 0, 2),

                'late_count' =>
                round($payroll->late_count ?? 0, 2),

                'lop_amount' =>
                round($payroll->lop_amount ?? 0, 2),

                'pf_amount' =>
                round($payroll->pf_amount ?? 0, 2),

                'esi_amount' =>
                round($payroll->esi_amount ?? 0, 2),

                'net_salary' =>
                round($payroll->net_salary ?? 0, 2),

                'data' => [

                    'staff_image' =>
                    $payroll->staff_image,

                    'company_type' =>
                    $payroll->company_type,

                    'company_id' =>
                    $payroll->company_id,

                    'entity_id' =>
                    $payroll->entity_id,

                    'gender' =>
                    $payroll->gender,

                    'staff_code' =>
                    $payroll->staff_code,

                    'nick_name' =>
                    $payroll->nick_name,

                    'mobile_no' =>
                    $payroll->mobile_no,

                    'company_name' =>
                    $payroll->company_name,

                    'department_name' =>
                    $payroll->department_name,

                    'job_role_name' =>
                    $payroll->job_role_name,

                    'date_of_joining' =>
                    $payroll->date_of_joining,

                    'salary_type' =>
                    $payroll->salary_type,

                    'salary_date' =>
                    $payroll->salary_date,

                    'basic_salary' =>
                    round($payroll->basic_salary ?? 0, 2),
                ],

                'dataParoll' => [

                    'earnings' =>
                    round($payroll->total_earnings ?? 0, 2),

                    'deductions' =>
                    round($payroll->total_deductions ?? 0, 2),

                    'gross_salary' =>
                    round($payroll->gross_salary ?? 0, 2),

                    'net' =>
                    round($payroll->net_salary ?? 0, 2),

                    'lop_days' =>
                    round($payroll->lop_days ?? 0, 2),

                    'components' => $components,

                    'rules' => $rules,
                ],
            ]
        ]);
    }

    // private function calculatePayrollEntry($staff,$id)
    // {
    //     $basic = $staff->basic_salary;

    //     $components = DB::table('egc_payroll_entry_components_new as c')
    //         ->join('egc_salary_components as sc','sc.sno','=','c.component_id')
    //         ->join('egc_payroll_entries_new','egc_payroll_entries_new.sno','=','c.payroll_entry_id')
    //         ->where('c.payroll_entry_id',$id)
    //         ->where('c.status',0)
    //         ->select('c.type','sc.component_name','sc.rule_type','c.amount','sc.calculation_type','c.component_id')
    //         ->distinct('c.component_id')
    //         ->get();
    //     // return $components;

    //     $payrollRuns = DB::table('egc_payroll_runs_new')
    //                     ->join('egc_payroll_entries_new','egc_payroll_entries_new.payroll_run_sno','=','egc_payroll_runs_new.sno')
    //                     ->where('egc_payroll_entries_new.sno',$id)
    //                     ->first();
    //     $month = $payrollRuns ? $payrollRuns->payroll_month :date('m');
    //     $year = $payrollRuns ? $payrollRuns->payroll_year :date('m');
    //     $earnings = $basic;
    //     $deductions = 0;
    //     $breakdown = [];

    //     // $earnings += $basic;

    //     $breakdown[] = [
    //         'name' => 'Basic Salary',
    //         'amount' => $basic,
    //         'type' => 'earning'
    //     ];

    //     foreach($components as $c){
    //         $amount = $c->amount;

    //         if($c->calculation_type == 'percentage' && $c->rule_type != 'esi' && $c->rule_type != 'pf'){
    //             $amount = ($basic * $c->amount)/100;
    //         }

    //         if($c->calculation_type == 'fixed' && $c->rule_type != 'esi' && $c->rule_type != 'pf'){
    //             $amount = $c->amount; 
    //         }



    //         if($c->rule_type == 'esi'){
    //             $esiRule = $this->getRule('esi');

    //             if($esiRule){
    //                 $limit = $esiRule['condition']['salary_limit'] ?? 0;

    //                 if($basic > $limit){
    //                     $amount = 0; 
    //                 }else{
    //                     if($c->calculation_type == 'percentage'){
    //                         $amount = ($basic * $c->amount)/100;
    //                     }else{
    //                         $amount = $c->amount;
    //                     }
    //                 }
    //             }
    //         }

    //         if($c->rule_type == 'pf'){
    //             $pfRule = $this->getRule('pf');

    //             if($pfRule){
    //                 $max = $pfRule['action']['max_limit'] ?? 0;

    //                 if($max > 0){
    //                     $amount = min($amount, $max);
    //                 }
    //             }
    //         }

    //         if($c->type == 'earning'){
    //             $earnings += $amount;
    //         }else{
    //             $deductions += $amount;
    //         }

    //         $breakdown[] = [
    //             'name' => $c->component_name,
    //             'amount' => $amount,
    //             'type' => $c->type
    //         ];
    //     }

    //     //  return $breakdown;
    //     // foreach($components as $c){

    //     //     //  $amount = 0;

    //     //         $amount = $c->amount;

    //     //         if($c->calculation_type == 'percentage' && $c->rule_type != 'esi' && $c->rule_type != 'pf'){
    //     //             $amount = ($basic * $c->amount)/100;
    //     //         }

    //     //         if($c->calculation_type == 'fixed' && $c->rule_type != 'esi' && $c->rule_type != 'pf'){
    //     //            $amount = $c->amount; 
    //     //         }



    //     //         if($c->rule_type == 'esi'){

    //     //             $esiRule = $this->getRule('esi');

    //     //             if($esiRule){
    //     //                 $limit = $esiRule['condition']['salary_limit'] ?? 0;

    //     //                 if($basic > $limit){
    //     //                     $amount = 0; 
    //     //                 }else{
    //     //                     if($c->calculation_type == 'percentage' ){
    //     //                         $amount = ($basic * $c->amount)/100;
    //     //                     }else{
    //     //                         $amount =  $c->amount;
    //     //                     }
    //     //                 }
    //     //             }
    //     //         }


    //     //         if($c->rule_type == 'pf'){

    //     //             $pfRule = $this->getRule('pf');
    //     //             // return $amount;

    //     //             if($pfRule){
    //     //                 $max = $pfRule['action']['max_limit'] ?? 0;

    //     //                 if($max > 0){
    //     //                     $amount = min($amount, $max);
    //     //                 }
    //     //             }
    //     //         }

    //     //         //  return $amount ;
    //     //         // return $amount;

    //     //         // if($c->rule_type == 'ot'){
    //     //         //     $amount = $this->getOvertime($staff,$month,$year);
    //     //         // }

    //     //         // if($c->rule_type == 'incentive'){
    //     //         //     $amount = $this->getIncentive($staff,$month,$year);
    //     //         // }

    //     //         // if($c->rule_type == 'travel'){
    //     //         //     $amount = $this->getTravel($staff,$month,$year);
    //     //         // }

    //     //     if($c->type == 'earning'){
    //     //         $earnings += $amount;
    //     //     }else{
    //     //         $deductions += $amount;
    //     //     }

    //     //     $breakdown[] = [
    //     //         'name'=>$c->component_name,
    //     //         'amount'=>$amount,
    //     //         'type'=>$c->type
    //     //     ];
    //     // }



    //     // ✅ APPLY RULE ENGINE
    //     $rules = $this->applyRules($staff,$month,$year,$basic);

    //     // return $rules;

    //     $deductions += $rules['total_deductions'];
    //      $net = $earnings - $deductions;

    //     return [
    //         'staff_id'=>$staff->sno,
    //         'name'=>$staff->staff_name,
    //         'basic'=>$basic,
    //         'earnings'=>$earnings,
    //         'deductions'=>$deductions,
    //         'net'=>$net,
    //         'lop_days'=>$rules['lop_days'],
    //         'lop_amount'=>$rules['lop_amount'],
    //         'components'=>$breakdown,
    //         'rules'=>$rules['breakdown'],

    //         // ADD THESE 👇
    //         'present_days'=>$present ?? 0,
    //         'absent_days'=>$absent ?? 0,
    //         'late_count'=>$late ?? 0
    //     ];
    // }

    private function calculatePayrollEntry($staff, $id)
    {
        // 👉 Get saved entry
        $entry = DB::table('egc_payroll_entries_new')
            ->where('sno', $id)
            ->first();

        if (!$entry) return [];

        // 👉 Get saved components (NO recalculation)
        $components = DB::table('egc_payroll_entry_components_new as c')
            ->join('egc_salary_components as sc', 'sc.sno', '=', 'c.component_id')
            ->where('c.payroll_entry_id', $id)
            ->where('c.status', 0)
            ->select(
                'sc.component_name',
                'c.amount',
                'c.type'
            )
            ->get();

        $breakdown = [];

        // 👉 Basic Salary
        $breakdown[] = [
            'name' => 'Basic Salary',
            'amount' => $entry->basic_salary,
            'type' => 'earning'
        ];

        foreach ($components as $c) {
            $breakdown[] = [
                'name' => $c->component_name,
                'amount' => $c->amount, // ✅ already final
                'type' => $c->type
            ];
        }

        return [
            'staff_id' => $staff->sno,
            'name' => $staff->staff_name,
            'basic' => $entry->basic_salary,
            'earnings' => $entry->total_earnings,
            'deductions' => $entry->total_deductions,
            'net' => $entry->net_salary,
            'lop_days' => $entry->lop_days,
            'lop_amount' => $entry->lop_amount,
            'components' => $breakdown
        ];
    }

   

    // public function indexNew(Request $request)
    // {
    //     $page = $request->input('page', 1);
    //     $perpage = (int) $request->input('per_page', 25);

    //     $monthFilter = $request->get('month_filter', date('M-Y'));

    //     $parsedDate = Carbon::createFromFormat('M-Y', $monthFilter);

    //     $month = $parsedDate->month;
    //     $year  = $parsedDate->year;

    //     $isCurrentMonth =
    //         ($month == date('m') && $year == date('Y'));

    //     $payrollProcess = DB::table('egc_payroll_processes')
    //         ->where('payroll_month', $month)
    //         ->where('payroll_year', $year)
    //         ->where('status', 0)
    //         ->first();

    //     $isLiveMode = false;
    //     if (!$payrollProcess) {
    //         $isLiveMode = true;
    //     }

    //     $staffQuery = DB::table('egc_staff as s')
    //         ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
    //         ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
    //         ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
    //         ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
    //         ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
    //         ->where('s.status', 0)
    //         ->where('s.role_id', '!=', 1)
    //         ->select(
    //             's.sno',
    //             's.staff_name',
    //             's.basic_salary as fixed_gross_salary',
    //             's.per_day_salary',
    //             's.company_id',
    //             's.entity_id',
    //             's.staff_id as staff_code',
    //             's.nick_name',
    //             's.mobile_no',
    //             's.staff_image',
    //             's.company_type',
    //             's.gender',
    //             's.salary_type',
    //             's.salary_date',
    //             's.date_of_joining',
    //             'egc_entity.entity_name',
    //             'egc_entity.entity_short_name',
    //             'egc_company.company_base_color',
    //             'egc_company.company_name',
    //             'egc_department.department_name',
    //             'egc_division.division_name',
    //             'egc_job_role.job_position_name as job_role_name'
    //         );

    //     if ($request->search_filter != '') {
    //         $search = $request->search_filter;
    //         $staffQuery->where(function ($q) use ($search) {
    //             $q->where('s.staff_name', 'LIKE', "%{$search}%")
    //                 ->orWhere('s.staff_id', 'LIKE', "%{$search}%");
    //         });
    //     }

    //     $staff = $staffQuery
    //         ->orderBy('s.staff_name', 'ASC')
    //         ->paginate($perpage);

    //     if ($isLiveMode) {
    //         $data = $staff->map(function ($item) use ($month, $year) {
    //             $payroll = app(\App\Services\PayrollPreviewService::class)
    //                 ->calculateLivePayroll(
    //                     $item,
    //                     $month,
    //                     $year
    //                 );
    //             if ($item->gender == 1) {
    //                 $defaultPath = asset('assets/egc_images/auth/user_2.png');
    //             } else {
    //                 $defaultPath = asset('assets/egc_images/auth/user_7.png');
    //             }
    //             if ($item->company_type == 1) {
    //                 $relativePath = 'staff_images/Management/' . $item->staff_image;
    //             } else {
    //                 $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
    //             }
    //             $fullPath = public_path($relativePath);
    //             $filePath = asset($relativePath);
    //             $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;
    //             $staffImageUrl = $isStaffImage ? $filePath : $defaultPath;

    //             return [
    //                 'staff_id' => $item->staff_id,
    //                 'staff_name' => $item->staff_name,
    //                 'staffImageUrl' => $staffImageUrl,
    //                 'staff_code' => $item->staff_code,
    //                 'nick_name' => $item->nick_name,
    //                 'company_id' => $item->company_id,
    //                 'salary_date' => $item->salary_date,
    //                 'date_of_joining' => $item->date_of_joining,
    //                 'salary_type' => $item->salary_type,
    //                 'mobile_no' => $item->mobile_no,
    //                 'entity_id' => $item->entity_id,
    //                 'department' => $item->department_name,
    //                 'designation' => $item->job_role_name,
    //                 'earning' => round($payroll['earnings'], 2),
    //                 'deduction' => round($payroll['deductions'], 2),
    //                 'gross_salary' => round($payroll['gross_salary'], 2),
    //                 'net_payable' => round($payroll['net_salary'], 2),
    //                 'lop_days' => round($payroll['lop_days'], 2),
    //                 'present_days' => round($payroll['present_days'], 2),
    //                 'absent_days' => round($payroll['absent_days'], 2),
    //                 'late_count' => round($payroll['late_count'], 2),
    //                 'mode' => 'live',
    //                 'payroll_status' => 'Preview',

    //                 'actions' => [
    //                     'view' => true,
    //                     'process' => true,
    //                     'payslip' => false,
    //                     'download' => false,
    //                     'edit' => true,
    //                 ]
    //             ];
    //         });
    //     } else {
    //         $employeeIds = $staff->pluck('sno');
    //         $processedPayrolls = DB::table('egc_payroll_employee_payrolls as ep')
    //             ->whereIn('ep.staff_id', $employeeIds)
    //             ->where('ep.payroll_month', $month)
    //             ->where('ep.payroll_year', $year)
    //             ->where('ep.status', 0)
    //             ->get()
    //             ->keyBy('staff_id');

    //         $data = $staff->map(function ($item) use ($processedPayrolls) {
    //             $payroll = $processedPayrolls[$item->sno] ?? null;
    //             return [
    //                 'staff_id' => $item->staff_id,
    //                 'staff_name' => $item->staff_name,
    //                 'fixed_gross_salary' => $item->fixed_gross_salary,
    //                 'department' => $item->department_name,
    //                 'designation' => $item->job_role_name,
    //                 'earning' => round($payroll->total_earnings ?? 0, 2),
    //                 'deduction' => round($payroll->total_deductions ?? 0, 2),
    //                 'gross_salary' => round($payroll->gross_salary ?? 0, 2),
    //                 'net_payable' => round($payroll->net_salary ?? 0, 2),
    //                 'lop_days' => round($payroll->lop_days ?? 0, 2),
    //                 'present_days' => round($payroll->present_days ?? 0, 2),
    //                 'absent_days' => round($payroll->absent_days ?? 0, 2),
    //                 'late_count' => round($payroll->late_count ?? 0, 2),

    //                 'mode' => 'processed',
    //                 'payroll_status' => $payroll->payroll_status ?? 'Processed',

    //                 'actions' => [
    //                     'view' => true,
    //                     'process' => false,
    //                     'payslip' => true,
    //                     'download' => true,
    //                     'edit' => false,
    //                 ]
    //             ];
    //         });
    //     }

    //     $totalGross = collect($data)->sum('gross_salary');
    //     $totalDeduction = collect($data)->sum('deduction');
    //     $totalNet = collect($data)->sum('net_payable');

    //     if ($request->ajax()) {
    //         return response()->json([
    //             'mode' => $isLiveMode ? 'live' : 'processed',
    //             'data' => $data,
    //             'totals' => [
    //                 'gross_salary' => round($totalGross, 2),
    //                 'deduction' => round($totalDeduction, 2),
    //                 'net_salary' => round($totalNet, 2),
    //             ],
    //             'pagination' => [
    //                 'current_page' => $staff->currentPage(),
    //                 'last_page' => $staff->lastPage(),
    //                 'per_page' => $staff->perPage(),
    //                 'total' => $staff->total(),
    //             ]
    //         ]);
    //     }

    //     return view('content.hr_management.hr_general.manage_payroll.payroll_list_new', [
    //         'mode' => $isLiveMode ? 'live' : 'processed',
    //         'perpage' => $perpage,
    //         'search_filter' => $search_filter ?? '',
    //         'company_list' => $company_list ?? [],
    //         'source_list' => $source_list ?? [],
    //         'company_fill' => $company_fill ?? [],
    //         'payslipCount' => $payslipCount ?? 0,
    //         'month' => $month,
    //         'year' => $year,
    //     ]);
    // }

    public function indexNew(Request $request)
    {
        $page = $request->input('page', 1);
        $perpage = (int) $request->input('sorting_filter', 5);

        $monthFilter = $request->get('month_filter', now()->format('M-Y'));

        try {
            // Parse month-year safely
            $parsedDate = Carbon::createFromFormat('!M-Y', $monthFilter);
            $month = $parsedDate->month;
            $year  = $parsedDate->year;
        } catch (\Exception $e) {
            $parsedDate = now()->startOfMonth();
            $month = $parsedDate->month;
            $year  = $parsedDate->year;
        }

         $salary_company = $request->salary_company ?? '';
        $company_fill = $request->company_fill ?? '';
        $entity_fill = $request->entity_fill ?? '';
        $department_fill = $request->department_fill ?? '';
        $division_fill = $request->division_fill ?? '';
        $job_role_fill = $request->job_role_fill ?? '';
        $date_filter = $request->dt_fill_issue_rpt ?? '';
        $from_date_filter = $request->to_dt_iss_rpt ?? '';
        $to_date_filter = $request->to_date_fillter_textbox ?? '';
        $search_filter = $request->search_filter ?? '';
        $salary_date_fill = $request->salary_date_fill ?? '';
        $salary_type_fill = $request->salary_type_fill ?? '';
        $exit_staff_fill = $request->exit_staff_fill ? 1 : 0;
        $only_bank_fill = $request->only_bank_fill ? 1 : 0;

        $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));
        $payrollProcess = DB::table('egc_payroll_processes')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();
        $payslipCount = DB::table('egc_staff')->where('status', 0)->where('is_payslip', 1)->count();

        $isLiveMode = !$payrollProcess;

        $verification = null;

        if ($payrollProcess) {

            $verification = DB::table('egc_payroll_hr_verifications')
                ->where('payroll_process_sno', $payrollProcess->sno)
                ->where('status', 0)
                ->latest('sno')
                ->first();
        }
        
        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
                $q->orWhere(function ($notice) use ($month, $year) {
                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereRaw("
                            ? BETWEEN DATE_FORMAT(s.date_of_joining,'%Y-%m')
                            AND DATE_FORMAT(s.notice_end_date,'%Y-%m')
                        ", [$payrollMonth]);

                    
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $exit->whereIn('s.status',[5,6,7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereNotIn('s.sno',[211,212,94,77])
                        ->whereRaw("
                            ? BETWEEN DATE_FORMAT(s.date_of_joining,'%Y-%m')
                            AND DATE_FORMAT(s.staff_last_date,'%Y-%m')
                        ", [$payrollMonth])
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                    
                })

                ->orWhere(function ($special) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);

                    // $special->whereIn('s.sno',[211,212,94,77])
                    //         ->whereIn('s.status',[5,6,7])
                    //         ->whereNotNull('s.staff_last_date')
                    //         ->whereRaw("
                    //             ? BETWEEN DATE_FORMAT(s.date_of_joining,'%Y-%m')
                    //             AND DATE_FORMAT(s.staff_last_date,'%Y-%m')
                    //         ", [$payrollMonth])
                    //         ->whereRaw(
                    //             'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                    //             [3]
                    //         );

                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            // ->where('s.sno', 200)
            ->select(
                's.*',
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary as basic_salary',
                'egc_staff_salary_accounts.per_day_salary',
                's.company_id',
                's.entity_id',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_type',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.staff_last_date',
                's.notice_start_date',
                's.notice_end_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_base_color',
                'egc_company.company_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name'
            );
        if ($request->search_filter != '') {
            $search = $request->search_filter;
            $staffQuery->where(function ($q) use ($search) {
                $q->where('s.staff_name', 'LIKE', "%{$search}%")
                    ->orWhere('s.staff_id', 'LIKE', "%{$search}%");
            });
        }
        if ($request->salary_company != '') {
            $staffQuery->where('egc_staff_salary_accounts.salary_company_id', $request->salary_company);
        }
        if ($request->company_fill != '') {
            $staffQuery->where('s.company_id', $request->company_fill);
        }
        
        if ($request->entity_fill != '') {
            $staffQuery->where('s.entity_id', $request->entity_fill);
        }
        if ($request->department_fill != '') {
            $staffQuery->where('s.department_id', $request->department_fill);
        }
         
        if ($exit_staff_fill == 1) {
            $staffQuery->where('s.status', '>', 2 );
        }
        
       
        if ($request->salary_type_fill != '') {
            $staffQuery->where('s.salary_type', $request->salary_type_fill);
        }
        if ($request->salary_date_fill != '') {
            $staffQuery->where('s.salary_date', $request->salary_date_fill);
        }
        if ($request->dt_fill_issue_rpt == 1 && $request->from_dt_iss_rpt != '' && $request->to_dt_iss_rpt != '') {
            $staffQuery->whereBetween(
                's.date_of_joining',
                [
                    $request->from_dt_iss_rpt,
                    $request->to_dt_iss_rpt
                ]
            );
        }
       

        $allStaffIds = (clone $staffQuery)->pluck('s.sno');
         $allStaffAccountIds = (clone $staffQuery)
                ->select('egc_staff_salary_accounts.sno as salary_account_id')
                ->pluck('salary_account_id');
        
        $staff = $staffQuery
            ->orderBy('s.date_of_joining', 'desc')
            ->paginate($perpage);

        
        $data = [];
        $overallNetSalary = 0;
        $overallGrossSalary = 0;
        $overallFixedGrossSalary = 0;
        $overallDeduction = 0;

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

         $payrollDate = Carbon::create($year, $month, 1)->endOfMonth();
        
    
       $overallFixedGrossSalary = DB::table('egc_payroll_employee_structures')
            ->whereIn('salary_account_id', $allStaffAccountIds)
            ->where('status', 0)
            ->whereDate('effective_from', '<=', $payrollDate)
            ->where(function ($q) use ($payrollDate) {
                $q->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $payrollDate);
            })
            ->sum('gross_salary');

        

        $earningComponents = [];
        $deductionComponents = [];
        $earningsSummary = [];
        $deductionsSummary = [];
        if ($isLiveMode) {
            $earningComponents = [];
            $deductionComponents = [];
            $allStaffData =  DB::table('egc_staff_salary_accounts')
                ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
                ->select(
                    's.*',
                     'egc_staff_salary_accounts.sno as salary_account_id',
                    'egc_staff_salary_accounts.gross_salary as basic_salary',
                    'egc_staff_salary_accounts.per_day_salary',
                )
                ->where('egc_staff_salary_accounts.status',0)
                // ->whereIn('s.sno', $allStaffIds)
                ->whereIn('egc_staff_salary_accounts.sno', $allStaffAccountIds)
                ->get();
                
            foreach ($allStaffData as $staffItem) {

                $payroll = app(
                    \App\Services\PayrollPreviewService::class
                )->calculateLivePayroll(
                    $staffItem,
                    $month,
                    $year,
                    $only_bank_fill
                );
                //    return $payroll;
                $overallNetSalary += round($payroll['net_salary']) ?? 0;
                $overallGrossSalary += $payroll['gross_salary'] ?? 0;
                $overallDeduction += round($payroll['deductions']) ?? 0;
                foreach ($payroll['components'] as $component) {
                    $name = $component['component'] ?? 'Unknown';
                    $amount = (float)($component['amount'] ?? 0);
                    if (($component['type'] ?? '') === 'earning') {
                        if (!isset($earningComponents[$name])) {
                            $earningComponents[$name] = 0;
                        }
                        $earningComponents[$name] += $amount;
                    }
                    if (($component['type'] ?? '') === 'deduction') {
                        if (!isset($deductionComponents[$name])) {
                            $deductionComponents[$name] = 0;
                        }
                        $deductionComponents[$name] += $amount;
                    }
                }
            }
            $earningsSummary = [];
            $deductionsSummary = [];
            foreach ($earningComponents as $name => $amount) {

                $earningsSummary[] = [
                    'name' => $name,
                    'total' => round($amount)
                ];
            }

            foreach ($deductionComponents as $name => $amount) {

                $deductionsSummary[] = [
                    'name' => $name,
                    'total' => round($amount)
                ];
            }
            foreach ($staff as $item) {
                $payroll = app(
                    \App\Services\PayrollPreviewService::class
                )->calculateLivePayroll(
                    $item,
                    $month,
                    $year,
                    $only_bank_fill
                );
                
                if ($item->company_type == 1) {
                    $relativePath =
                        'staff_images/Management/' .
                        $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }
                $fullPath = public_path($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;
                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }
                $data[] = [
                    'sno' => $item->sno,
                    'staff_status' => $item->status,
                    'name' => $item->staff_name,
                    'staff_last_date' => $item->staff_last_date,
                    'notice_start_date' => $item->notice_start_date,
                    'notice_end_date' => $item->notice_end_date,
                    'basic_salary' => $item->basic_salary,
                    'company_base_color' =>
                    $item->company_base_color,
                    'is_saved' => false,
                    'isStaffImage' => $isStaffImage,
                    'data' => [

                        'staff_image' =>
                        $item->staff_image,

                        'company_type' =>
                        $item->company_type,

                        'company_id' =>
                        $item->company_id,

                        'entity_id' =>
                        $item->entity_id,

                        'gender' =>
                        $item->gender,

                        'staff_code' =>
                        $item->staff_code,

                        'nick_name' =>
                        $item->nick_name,

                        'company_name' =>
                        $item->company_name,

                        'department_name' =>
                        $item->department_name,

                        'job_role_name' =>
                        $item->job_role_name,

                        'date_of_joining' =>
                        $item->date_of_joining,

                        'salary_type' =>
                        $item->salary_type,

                        'salary_date' =>
                        $item->salary_date,

                        'basic_salary' =>
                        round(
                            $payroll['basic_salary']
                        ),
                    ],
                    'dataParoll' => [
                        'components' => $payroll['components'] ?? [],
                        'rules' => $payroll['rules'] ?? [],
                        'earnings' => round($payroll['earnings']),
                        'deductions' => round($payroll['deductions']),
                        'gross_salary' => round($payroll['gross_salary']),
                        'net' => round($payroll['net_salary']),
                        'employer_contribution' => round($payroll['employer_contribution']),
                        'lop_days' => round($payroll['lop_days'], 2),
                    ],
                    'present_days' => round($payroll['present_days'], 2),
                    'absent_days' => round($payroll['absent_days'], 2),
                    'late_count' => round($payroll['late_count'], 2),
                    'lop_amount' => round($payroll['lop_amount']),
                    'pf_amount' => round($payroll['pf_amount'] ?? 0),
                    'esi_amount' => round($payroll['esi_amount'] ?? 0),
                    'net_salary' => round($payroll['net_salary']),
                ];
                
            }
        } else {
            $earningComponents = [];
            $deductionComponents = [];
            $employeeIds = $staff->pluck('sno');
            $processedPayrolls = DB::table('egc_payroll_employee_payrolls as ep')
                ->whereIn('ep.staff_id', $employeeIds)
                ->where('ep.payroll_month', $month)
                ->where('ep.payroll_year', $year)
                ->where('ep.status', 0)
                ->get()
                ->keyBy('staff_id');

            $overallPayrolls = DB::table('egc_payroll_employee_payrolls')
                ->whereIn('staff_id', $allStaffIds)
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->where('status', 0)
                ->get();
            $overallNetSalary =
                $overallPayrolls->sum('net_payable');

            $overallGrossSalary =
                $overallPayrolls->sum('gross_earnings');

            $overallDeduction =
                $overallPayrolls->sum('gross_deductions');


            $componentSummary = DB::table('egc_payroll_employee_payroll_details')
                ->select(
                    'component_name',
                    'component_category',
                    DB::raw('SUM(actual_amount) as total')
                )
                ->whereIn(
                    'payroll_employee_sno',
                    $overallPayrolls->pluck('sno')
                )
                ->where('status', 0)
                ->groupBy(
                    'component_name',
                    'component_category'
                )
                ->get();
            foreach ($componentSummary as $component) {

                if ($component->component_category == 'earning') {

                    $earningsSummary[] = [
                        'name' => $component->component_name,
                        'total' => round($component->total)
                    ];
                }

                if ($component->component_category == 'deduction') {

                    $deductionsSummary[] = [
                        'name' => $component->component_name,
                        'total' => round($component->total)
                    ];
                }
            }


            foreach ($staff as $item) {
                $payroll = $processedPayrolls[$item->sno] ?? null;
                if ($item->company_type == 1) {
                    $relativePath = 'staff_images/Management/' . $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }

                $fullPath = public_path($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;

                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }
                $components = DB::table('egc_payroll_employee_payroll_details')
                    ->where('egc_payroll_employee_payroll_details.payroll_employee_sno', $payroll->sno ?? 0)
                    ->join('egc_payroll_components','egc_payroll_components.sno','=','egc_payroll_employee_payroll_details.payroll_component_sno')
                    ->where('egc_payroll_employee_payroll_details.status', 0)
                    ->select(
                        'egc_payroll_employee_payroll_details.*',
                        'egc_payroll_components.component_code',
                    )
                    ->get()
                    ->map(function ($item) {
                        return [
                            'sno' => $item->payroll_component_sno,
                            'component' => $item->component_name,
                            'code' => $item->component_code,
                            'type' => $item->component_category,
                            'calculation_type' => $item->calculation_type,
                            'percentage' => (float)$item->percentage_value,
                            'amount' => round($item->actual_amount, 2)
                        ];
                    })
                    ->toArray();

                $lateCount = $this->getLateCount(
                    $item->sno,
                    $month,
                    $year
                );

                $rules = [];

                $pfAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%PF%')
                    ->sum('actual_amount');

                $esiAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%ESI%')
                    ->sum('actual_amount');
                $payrollSlipData = DB::table('egc_payroll_payslips')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_payslips.payroll_process_sno')
                        ->where( 'egc_payroll_payslips.employee_sno', $item->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->select('egc_payroll_payslips.sno')
                        ->first();
                $payrollAttendance = DB::table('egc_payroll_attendance_summaries')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_attendance_summaries.payroll_process_sno')
                        ->where( 'egc_payroll_attendance_summaries.employee_sno', $item->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->first();
                $data[] = [
                    'sno' => $item->sno,
                    'entry_id' => $payrollSlipData->sno ?? 0,
                    'entry_encrypt' => encrypt($payrollSlipData->sno ?? 0),
                    'name' => $item->staff_name,
                    'company_base_color' => $item->company_base_color,
                    'is_saved' => true,
                    'isStaffImage' => $isStaffImage,
                    'staff_last_date' => $item->staff_last_date,
                    'notice_start_date' => $item->notice_start_date,
                    'notice_end_date' => $item->notice_end_date,
                    'staff_status' => $item->status,
                    'data' => [
                        'staff_image' => $item->staff_image,
                        'company_type' => $item->company_type,
                        'company_id' => $item->company_id,
                        'entity_id' => $item->entity_id,
                        'gender' => $item->gender,
                        'staff_code' => $item->staff_code,
                        'nick_name' => $item->nick_name,
                        'company_name' => $item->company_name,
                        'department_name' => $item->department_name,
                        'job_role_name' => $item->job_role_name,
                        'date_of_joining' => $item->date_of_joining,
                        'salary_type' => $item->salary_type,
                        'salary_date' => $item->salary_date,
                        'basic_salary' => round($item->basic_salary ?? 0),
                    ],
                    'dataParoll' => [
                        'components' => $components,
                        'rules' => $rules,
                        'earnings' => round($payroll->gross_earnings ?? 0),
                        'deductions' => round($payroll->gross_deductions ?? 0),
                        'gross_salary' => round($payroll->gross_earnings ?? 0),
                        'net' => round($payroll->net_payable ?? 0),
                        'employer_contribution' => round($payroll->employer_contribution ?? 0),
                        'lop_days' => round($payroll->lop_days ?? 0, 2),

                    ],
                    'present_days' => round($payroll->present_days ?? 0),
                    'absent_days' => round($payroll->absent_days ?? 0),
                    'late_count' => round($payroll->late_count ?? 0),
                    'lop_amount' => round($payroll->lop_amount ?? 0),
                    'pf_amount' => round($pfAmount, 2),
                    'esi_amount' => round($esiAmount, 2),
                    'net_salary' => round($payroll->net_payable ?? 0),
                ];
            }
        }

       
        if ($request->ajax()) {
            return response()->json([
                'mode' => $isLiveMode ? 'live' : 'processed',
                'payroll_process_id' => $payrollProcess ? $payrollProcess->sno : null,
                'is_payroll_saved_id' => !$isLiveMode,
                'is_payroll_saved' => !$isLiveMode,
                'allStaffAccountIds' => $allStaffAccountIds,
                'data' => $data,
                'total' => $staff->total(),
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'per_page' => $staff->perPage(),
                'total_net_salary' => round($overallNetSalary),
                'total_gross_salary' => round($overallGrossSalary),
                'total_fixed_gross_salary' => round($overallFixedGrossSalary),
                'total_deduction' => round($overallDeduction),
                'earnings_summary' => $earningsSummary ?? [],
                'deductions_summary' => $deductionsSummary ?? [],
                'workflow' => [
                    'generated' => !$isLiveMode,
                    'hr_verified' => !empty($verification),
                    'frozen' => $payrollProcess  ? ($payrollProcess->payroll_freeze == 1)  : false,
                    'process_status' => $payrollProcess->process_status ?? 'draft',
                    'payroll_process_id' => $payrollProcess->sno ?? null
                ]
            ]);
        }
          $company_list = CompanyModel::where('status', 0)->get();
           
        return view(
            'content.hr_management.hr_general.manage_payroll.payroll_list_new',
            [
                'mode' => $isLiveMode ? 'live' : 'processed',
                'perpage' => $perpage,
                'search_filter' => $search_filter ?? '',
                'payslipCount' => $payslipCount ?? 0,
                'month' => $month,
                'company_list' => $company_list,
                'year' => $year,
            ]
        );
    }

    public function runPayroll(Request $request)
    {
        try {

            $monthFilter =
                $request->month_filter;
                $user_id = $request->user()->user_id ?? 0;

            $parsed =
                Carbon::createFromFormat(
                    'M-Y',
                    $monthFilter
                );
            $month = $parsed->month;
            $year  = $parsed->year;
            $isFrozen = DB::table('egc_payroll_processes')
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->where('payroll_freeze', 1)
                ->exists();

            if ($isFrozen) {

                return response()->json([
                    'status' => false,
                    'message' =>
                    'Payroll already frozen'
                ], 422);
            }

            $result =
                app(PayrollProcessService::class)
                ->process(
                    $parsed->month,
                    $parsed->year,
                    $user_id
                );

            return response()->json([

                'status' => 200,

                'message' =>
                'Payroll processed successfully',

                'process_id' =>
                $result['process_id']
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status' => 500,

                'message' => $e->getMessage()

            ], 500);
        }
    }


     public function getPayrollProcessDetails(Request $request)
    {

        $process = DB::table('egc_payroll_processes')
            ->where('sno', $request->process_id)
            ->first();

        if (!$process) {

            return response()->json([
                'status' => false,
                'message' => 'Payroll process not found'
            ], 404);
        }

        $year=$process->payroll_year ?? date('Y');
        $month=$process->payroll_month ?? date('m');

         $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));
          $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

        $employees = DB::table('egc_payroll_employee_payrolls as ep')
            ->join('egc_staff as s','s.sno', '=', 'ep.staff_id')
             ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where( 'ep.payroll_process_sno', $process->sno )
            ->select(
                's.staff_name',
                's.staff_id as staff_code',
                's.basic_salary',
                's.per_day_salary',
                's.company_id',
                's.entity_id',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_type',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.staff_last_date',
                's.notice_start_date',
                's.notice_end_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_base_color',
                'egc_company.company_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name',
                'ep.gross_earnings',
                'ep.gross_deductions',
                'ep.net_payable'
            )
            ->where('s.status','!=', 2)
            ->where(function ($q) use ($month, $year) {
                $q->whereIn('s.status', [0, 1]);
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth(
                            's.notice_end_date',
                            $month
                        )
                        ->whereYear(
                            's.notice_end_date',
                            $year
                        );
                });
                 $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                })
                ->orWhere(function ($special) use ($month, $year) {
                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            // ->limit(10)
            ->get();

          foreach($employees as $item ){
                if ($item->gender == 1) {
                    $defaultPath = asset('assets/egc_images/auth/user_2.png');
                } else {
                    $defaultPath = asset('assets/egc_images/auth/user_7.png');
                }
                if ($item->company_type == 1) {
                    $relativePath = 'staff_images/Management/' . $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }
                $fullPath = public_path($relativePath);
                $filePath = asset($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;
                $staffImageUrl = $isStaffImage ? $filePath : $defaultPath;
            
                $item->staffImageUrl =$staffImageUrl;
                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }

          }

           $verification = DB::table('egc_payroll_hr_verifications' )
                ->where( 'payroll_process_sno', $process->sno)
                ->where('status', 0)
                ->latest('sno')
                ->first();

            $verificationChecklist = [
                [
                    'key' => 'attendance_verified',
                    'label' => 'Attendance & Working Days Verified'
                ],
                [
                    'key' => 'lop_verified',
                    'label' => 'LOP Deductions Verified'
                ],
                [
                    'key' => 'earnings_verified',
                    'label' => 'Earnings Components Verified'
                ],
                [
                    'key' => 'deductions_verified',
                    'label' => 'PF / ESI / PT / Tax Verified'
                ],
                [
                    'key' => 'salary_structure_verified',
                    'label' => 'Salary Structure Validated'
                ],
                [
                    'key' => 'incentive_verified',
                    'label' => 'Incentives & Variable Pay Verified'
                ],
                [
                    'key' => 'final_netpay_verified',
                    'label' => 'Final Net Pay Verified'
                ]
            ];

        $proccessedStaffData=  DB::table('egc_staff' )
                ->where('sno',$process->processed_by)
                ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'sno' => $process->sno,
                'payroll_month' => $process->payroll_month,
                'process_status' => $process->process_status,
                'payroll_freeze' => $process->payroll_freeze,
                'total_employees' =>  $process->total_employees,
                'processed_employees' => $process->processed_employees,
                'total_gross' =>  number_format( round($process->total_gross) ),
                'total_deduction' =>  number_format(  round($process->total_deduction) ),
                'total_netpay' => number_format( round($process->total_netpay)),
                'processed_by' => $proccessedStaffData ? $proccessedStaffData->staff_name : 'HR Admin',
                'processed_at' => Carbon::parse(  $process->processed_at)->format('d M Y h:i A'),
                'top_employees' => $employees,
                'checklist' => $verificationChecklist,
                'hr_verified' => $verification ? true : false,
                'verification_data' =>  $verification  ? json_decode( $verification->checklist_json, true ) : [],
                'verification_remarks' => $verification->remarks ?? '',
                'verified_at' => $verification ? Carbon::parse( $verification->verified_at )->format('d M Y h:i A') : '',
                'freeze_validation' => [
                    'payroll_generated' => $process->processed_employees > 0,
                    'all_employees_processed' => $process->processed_employees ==  $process->total_employees,
                    'hr_verified' => $verification ? true : false,
                    'already_frozen' => $process->payroll_freeze == 1
                ]
            ]
        ]);
    }


    public function verifyPayroll(Request $request)
    {
        DB::beginTransaction();
        try {

            $processId = $request->process_id;
            $checklist = [
                'attendance_verified' => $request->attendance_verified,
                'lop_verified' => $request->lop_verified,
                'pf_verified' =>$request->pf_verified,
                'esi_verified' => $request->esi_verified,
                'salary_structure_verified' => $request->salary_structure_verified,
                'incentive_verified' => $request->incentive_verified,
                'deduction_verified' => $request->deduction_verified,
            ];

            DB::table(
                'egc_payroll_hr_verifications'
            )
            ->insert([
                'payroll_process_sno' =>  $processId,
                'checklist_json' =>  $request->checklist_json,
                'remarks' => $request->remarks,
                'verified_by' => $request->user()->user_id ?? 0,
                'verified_at' => now(),
                'created_at' =>  now(),
                'updated_at' =>  now(),
                'status' =>  0
            ]);

            DB::table(
                'egc_payroll_processes'
            )
            ->where('sno', $processId)
            ->update([
                  'hr_verified' => 1,
                    'hr_verified_by' => $request->user()->user_id ?? 0,
                    'hr_verified_at' => now(),
                    'updated_at' => now()
            ]);

            DB::table(
                'egc_payroll_audit_logs'
            )->insert([
                'module_name' =>'Payroll',
                'record_sno' => $processId,
                'action_type' =>'HR_VERIFIED',
                'remarks' =>json_encode([
                        'remarks' =>
                            $request->remarks
                    ]),
                'action_by' =>$request->user()->user_id ?? 0,
                'action_datetime' =>now(),
                'created_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' =>
                    'Payroll verified successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }

    public function freezePayroll(Request $request)
    {
        DB::beginTransaction();

        try {

            $processId = $request->process_id;

            $payroll = DB::table('egc_payroll_processes')
                ->where('sno', $processId)
                ->first();

            if (!$payroll) {
                throw new \Exception(
                    'Payroll process not found'
                );
            }
            $verification = DB::table('egc_payroll_hr_verifications')
                ->where('payroll_process_sno', $processId)
                ->where('status', 0)
                ->first();

            if (!$verification) {
                throw new \Exception(
                    'HR verification must be completed before payroll freeze.'
                );
            }
            if ($payroll->payroll_freeze == 1) {
                throw new \Exception(
                    'Payroll already frozen'
                );
            }


           
            /*
            |--------------------------------------------------------------------------
            | LOCK PAYROLL PROCESS
            |--------------------------------------------------------------------------
            */
            DB::table('egc_payroll_processes')
                ->where('sno', $processId)
                ->update([
                    'process_status' => 'frozen',
                    'payroll_freeze' => 1,
                    'freeze_datetime' => now(),
                    'freeze_by' => session('admin_id'),
                    'updated_at' => now()
                ]);
            /*
            |--------------------------------------------------------------------------
            | LOCK EMPLOYEE PAYROLLS
            |--------------------------------------------------------------------------
            */
            DB::table('egc_payroll_employee_payrolls')
                ->where('payroll_process_sno', $processId)
                ->update([
                    'salary_lock' => 1,
                    'updated_at' => now()
                ]);
            /*
            |--------------------------------------------------------------------------
            | FREEZE LOG
            |--------------------------------------------------------------------------
            */
            DB::table('egc_payroll_freeze_logs')
                ->insert([
                    'payroll_process_sno' => $processId,
                    'payroll_month' => $payroll->payroll_month,
                    'freeze_status' => 'frozen',
                    'freeze_reason' => $request->freeze_reason,
                    'frozen_by' => session('admin_id'),
                    'frozen_at' => now(),
                    'created_at' => now()
                ]);

            /*
            |--------------------------------------------------------------------------
            | AUDIT LOG
            |--------------------------------------------------------------------------
            */
            DB::table('egc_payroll_audit_logs')
                ->insert([
                    'module_name' => 'Payroll Freeze',
                    'record_sno' => $processId,
                    'action_type' => 'FREEZE',
                    'remarks' => $request->freeze_reason,
                    'action_by' => session('admin_id'),
                    'action_datetime' => now(),
                    'created_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' =>'Payroll frozen successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }

     public function ViewPayslipStaffs($month_filter, Request $request)
    {

        // $month_filter = $request->get('month_filter', date('M-Y'));
        $parsedDate = Carbon::createFromFormat('M-Y', $month_filter);
        $month = $parsedDate->month ?? date('m');

        $year  = $parsedDate->year ?? date('Y');
        $monthFull = date('F', mktime(0, 0, 0, $month, 1));

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
        $today = Carbon::today();

        $entry_staff_ids = DB::table('egc_payroll_employee_payrolls')
            ->join('egc_payroll_processes', 'egc_payroll_processes.sno', '=', 'egc_payroll_employee_payrolls.payroll_process_sno')
            ->where('egc_payroll_processes.payroll_month', $month)
            ->where('egc_payroll_processes.payroll_year', $year)
            ->pluck('egc_payroll_employee_payrolls.staff_id');
        // return $entry_staff_ids;

        $data =  DB::table('egc_staff')->where('egc_staff.status', 0)
            ->select(
                'egc_staff.*',
                'egc_payroll_employee_payrolls.sno as payroll_entry_id',
                'egc_payroll_payslips.email_sent as email_send',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_entity.entity_name',
                'egc_entity.entity_base_color',
                'egc_company.company_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_languages.name as mother_tongue',
                'egc_job_role.job_position_name as job_role_name',
            )
            ->join('egc_payroll_employee_payrolls', 'egc_staff.sno', '=', 'egc_payroll_employee_payrolls.staff_id')
            ->join('egc_payroll_payslips', 'egc_staff.sno', '=', 'egc_payroll_payslips.employee_sno')
            ->join('egc_payroll_processes', 'egc_payroll_processes.sno', '=', 'egc_payroll_employee_payrolls.payroll_process_sno')
            ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
            ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
            ->leftJoin('egc_languages', 'egc_staff.mother_tongue', 'egc_languages.sno')
            ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
            ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
            ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
            ->where('egc_payroll_processes.payroll_month', $month)
            ->where('egc_payroll_processes.payroll_year', $year)
            ->whereIn('egc_staff.sno', $entry_staff_ids)
            ->where('egc_staff.is_payslip', 1)
            ->get();

            
            $helper = new \App\Helpers\Helpers();
            $general_setting = $helper->general_setting_data();

            

        if (!$data) {
            return response([
                'success' => false,
                'status' => 404,
                'message' => 'staff not found',
                'error_msg' => 'No record found with the given ID.',
                'data' => null,
            ], 404);
        }

        foreach($data as $item){
                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }
        }
        return response([
            'success' => true,
            'status' => 200,
            'message' => 'Staff fetched successfully',
            'error_msg' => null,
            'data' => $data,
            'year' => $year,
            'month' => $monthFull,
        ], 200);
    }


    public function SendMonthPayslip(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'id'  => 'required',

        ]);
        // If validation fails, return a response with errors
        if ($validator->fails()) {
            return response([
                'status'    => 401,
                'message'   => 'Incorrect format input fields',
                'error_msg' => $validator->messages()->get('*'),
                'data'      => null,
            ], 200);
        }
        $branch_id  = $request->user()->branch_id;
        // $branchData = BranchModel::where('status', 0)->where('sno', $branch_id)->first();

        $id = $request->id;

        $data =  DB::table('egc_staff')->where('egc_staff.status', '!=', 2)
            ->select(
                'egc_staff.*',
                'egc_payroll_entries_new.sno as payroll_entry_id',
                'egc_payroll_entries_new.net_salary',
                'egc_entity.entity_name',
                'egc_payroll_runs_new.payroll_month',
                'egc_payroll_runs_new.payroll_year',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_entity.entity_name',
                'egc_entity.entity_base_color',
                'egc_company.company_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_languages.name as mother_tongue',
                'egc_job_role.job_position_name as job_role_name',
            )
            ->join('egc_payroll_entries_new', 'egc_staff.sno', '=', 'egc_payroll_entries_new.staff_id')
            ->join('egc_payroll_runs_new', 'egc_payroll_runs_new.sno', '=', 'egc_payroll_entries_new.payroll_run_sno')
            ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
            ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
            ->leftJoin('egc_languages', 'egc_staff.mother_tongue', 'egc_languages.sno')
            ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
            ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
            ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
            ->where('egc_payroll_entries_new.sno', $id)
            ->where('egc_staff.is_payslip', 1)
            ->first();

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();
        // $event_date =$event_certificate->event_date ? date($common_date_format, strtotime($event_certificate->event_date)) : '22 May 2025'  ;

        // Extract the validated data
        $staff_mobile           = $data->mobile_no;
        $staff_email           = $data->email_id;
        $staff_name = $data->staff_name;
        $employee_id = $data->staff_id;
        $company_name = $data->company_name;
        $net_pay = $data->net_salary ? '₹ ' . number_format($data->net_salary) : '₹ 0';
        $month = $data->payroll_month;
        $year = $data->payroll_year;

        $monthFull = date('F', mktime(0, 0, 0, $month, 1));

        $month_year = $monthFull . '-' . $year;

        $user_id           = $request->user()->user_id ?? 0;
        $encrypted_values = encrypt($id);


        $Link = url('payroll/payslip_print/' . $encrypted_values);


        // if(!$event_certificate->payslip_link){
        //     $channelId = 12;
        //     $shortUrlData = $this->shortenUrl($Link,$channelId);
        //     $shortUrl = $shortUrlData['shorturl'];
        //     $urlId =$shortUrlData['id'];

        //     if($shortUrl){
        //             DB::table('za_event_participant')
        //             ->where('sno', $id)
        //             ->update(['payslip_link' => $shortUrl]);
        //     }

        // }

        $payrollEntryData = DB::table('egc_payroll_entries_new')->where('egc_payroll_entries_new.sno', $id)->first();
        $shortsendUrl = $payrollEntryData->payslip_link ?? $Link;

        $payslip_link_key = basename($shortsendUrl);


        if ($staff_email) {


            $socialMediaDetails = json_decode($general_setting->social_media_details, true);
            $socialMediaList = SocialMediaModel::where('status', 0)->orderBy('sno', 'ASC')->get();

            $facebook_link = null;
            $instagram_link = null;
            $twitter_link = null;
            $linkedin_link = null;
            $youtube_link = null;
            $pinterest_link = null;

            foreach ($socialMediaList as $socialMedia) {
                $sno = $socialMedia->sno;

                if (isset($socialMediaDetails[$sno])) {
                    $url = $socialMediaDetails[$sno];
                    switch ($socialMedia->social_media_name) {
                        case 'Instagram':
                            $instagram_link = $url;
                            break;
                        case 'Facebook':
                            $facebook_link = $url;
                            break;
                        case 'Twitter':
                            $twitter_link = $url;
                            break;
                        case 'LinkedIn':
                            $linkedin_link = $url;
                            break;
                        case 'YouTube':
                            $youtube_link = $url;
                            break;
                        case 'Pinterest':
                            $pinterest_link = $url;
                            break;
                    }
                }
            }

            $email_template_id = 7; // Event Certificate template
            $emailTemplate = EmailTemplateModel::where('status', 0)->where('sno', $email_template_id)->first();

            $dynamicSubject = $emailTemplate->email_name;

            $dynamicSubject = str_replace('#company_name', $company_name, $dynamicSubject);
            $dynamicSubject = str_replace('#month_year', $month_year, $dynamicSubject);

            $content = $emailTemplate->email_subject;

            $content = str_replace('#company_name', $company_name ?? 'ElysiumGroups', $content);
            $content = str_replace('#name', $staff_name ?? 'Staff', $content);
            $content = str_replace('#month_year', $month_year ?? 'March-2026', $content);
            $content = str_replace('#employee_id',  $employee_id ?? 'EGC003038', $content);
            $content = str_replace('#payslip_link', $shortsendUrl ?? "url", $content);
            $content = str_replace('#net_pay',  $net_pay ?? '₹0', $content);
            $branchNo = $general_setting->hr_head_no;
            $branchemail = $general_setting->hr_head_mail_id;
            $fbLink = $facebook_link ?? 'https://www.facebook.com/PhDiZone/';
            $instaLink = $instagram_link ?? 'https://www.instagram.com/phdizoneresearch/';
            $url = 'www.elysiumgroup.com';

            $mailData = [
                'url'         => $url,
                'subject'     => $dynamicSubject,
                'content'     => $content,
                'branchNo'    => $branchNo,
                'branchemail' => $branchemail, // Use the message content from the request
                'fbLink'      => $fbLink,      // Use the message content from the request
                'instaLink'   => $instaLink,   // Use the message content from the request
                'salesMobile' => $branchNo ?? '8220011465',
            ];

            $to_address = 'vetri4vijayan@gmail.com';
            $from_address = 'elysiumtechnology@elysium.community';
            $from_name =  'Elysium Groups ';

            $emailLogId = DB::table('egc_email_logs')->insertGetId([
                'module' => 'payslip',
                'reference_id' => $id,
                'to_email' => $to_address,
                'to_name' => $staff_name,
                'template_id' => $email_template_id,
                'subject' => $dynamicSubject,
                'body' => $content,
                'status' => 0,
                'sent_by' => $user_id,
                'attempt_count' => 0,
                'created_at' => now()
            ]);

            try {


                Mail::to($to_address)->send(new EGCMail($mailData, $from_address, $from_name));

                DB::table('egc_payroll_entries_new')
                    ->where('sno', $id)
                    ->update(['email_send' => 1]);

                DB::table('egc_email_logs')
                    ->where('sno', $emailLogId)
                    ->update([
                        'status' => 1,
                        'sent_at' => now(),
                        'updated_at' => now()
                    ]);

                return response([
                    'success' => true,
                    'message' => 'Email sent successfully'
                ]);
            } catch (\Exception $e) {

                DB::table('egc_payroll_entries_new')
                    ->where('sno', $id)
                    ->update(['email_send' => 2]);

                DB::table('egc_email_logs')
                    ->where('sno', $emailLogId)
                    ->update([
                        'status' => 2,
                        'error_message' => $e->getMessage(),
                        'attempt_count' => DB::raw('attempt_count + 1'),
                        'updated_at' => now()
                    ]);

                return response([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            // return $mailData;




        }


        return response([
            'success' => true,
            'message' => 'Email sent successfully'
        ]);
    }


     private function getLateCount(
        $staffId,
        $month,
        $year
    ) {

        $lateCount = DB::table('egc_staff_attendance as att')
            ->join('egc_staff as s', 's.sno', '=', 'att.staff_id')

            ->join('egc_shift_time_log as stl', function ($join) {
                $join->on('stl.staff_id', '=', 'att.staff_id');
            })

            ->join('egc_shift_day_times as sd', function ($join) {
                $join->on('sd.shift_id', '=', 'stl.change_shift_id')
                    ->where('sd.status', 0);
            })
            ->where('att.staff_id', $staffId)
            // 👉 Month filter
            ->whereMonth('att.date', $month)
            ->whereYear('att.date', $year)
            ->where('att.status', 0)
            ->whereNotNull('att.in_time')
            // ✅ JOINING DATE CHECK
            ->whereRaw("DATE(att.date) >= DATE(s.date_of_joining)")
            // ✅ SHIFT DATE RANGE CHECK
            ->whereRaw("
                        DATE(att.date) BETWEEN stl.start_date 
                        AND IFNULL(stl.end_date, '9999-12-31')
                    ")
            // ✅ DAY MATCH (Mon, Tue...)
            ->whereRaw("
                        LOWER(sd.day_name) = LOWER(DATE_FORMAT(att.date, '%a'))
                    ")
            // ✅ LATE CHECK
            // ->whereRaw("
            //     TIME(att.in_time) > TIME(sd.time_from)
            // ")
            ->whereRaw("TIME(att.in_time) > ADDTIME(sd.time_from, '00:16:00')")
            ->count();

        return $lateCount;
    }


    // public function customExport(Request $request)
    // {
    //     $format = $request->format;

    //     $payroll = app(PayrollExportService::class)->prepare($request);

    //     switch ($format) {

    //         case 'excel':
    //             return PayrollBankExcelExport::download($payroll);

    //         case 'pdf':
    //             return PayrollPdfExport::download($payroll);

    //         case 'docx':
    //             return PayrollWordExport::download($payroll);
    //     }
    // }

    public function customExport(Request $request)
    {
        $monthFilter = $request->month_filter ?? date('M-Y');

        $parsedDate = Carbon::createFromFormat(
            'M-Y',
            $monthFilter
        );

        $month = $parsedDate->month;
        $year  = $parsedDate->year;

        $salary_company    = $request->salary_company ?? '';
        $company_fill      = $request->company_fill ?? '';
        $entity_fill       = $request->entity_fill ?? '';
        $department_fill   = $request->department_fill ?? '';
        $salary_type_fill  = $request->salary_type_fill ?? '';
        $salary_date_fill  = $request->salary_date_fill ?? '';
        $only_bank_fill    = $request->only_bank_fill ? 1 : 0;
        $exit_staff_fill   = $request->exit_staff_fill ? 1 : 0;
        $cheque_no   = $request->cheque_no ?? '';
        $include_exit_staff_fill   = $request->include_exit_staff_fill ? 1 : 0;
        $exitStaffIds = $request->exit_staff_ids ?? [];
        
        /*
        |--------------------------------------------------------------------------
        | Build Payroll Data
        |--------------------------------------------------------------------------
        */

        $payrollData = $this->buildPayrollData(
            $month,
            $year,
            $salary_company,
            $company_fill,
            $entity_fill,
            $department_fill,
            $salary_type_fill,
            $salary_date_fill,
            $only_bank_fill,
            $include_exit_staff_fill,
            $exitStaffIds,
            $cheque_no
        );

        /*
        |--------------------------------------------------------------------------
        | Export Type
        |--------------------------------------------------------------------------
        */

        switch ($request->format) {

            case 'pdf':

                return $this->exportPayrollPdf(
                    $payrollData,
                    $month,
                    $year
                );

            case 'docx':

                return $this->exportPayrollWord(
                    $payrollData,
                    $month,
                    $year
                );

            default:

                return Excel::download(

                    new PayrollBankExcelExport($payrollData),

                    'Salary Transfer '.$month.'.xlsx'

                );
        }
    }


    private function buildPayrollData(
        $month,
        $year,
        $salary_company,
        $company_fill,
        $entity_fill,
        $department_fill,
        $salary_type_fill,
        $salary_date_fill,
        $only_bank_fill,
        $include_exit_staff_fill,
        $exitStaffIds,
        $cheque_no
    )
    {
        $endOfMonth = date('Y-m-t',strtotime("$year-$month-01"));

        $payrollProcess = DB::table('egc_payroll_processes')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();

        $isLiveMode = !$payrollProcess;

        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company','s.company_id','=','egc_company.sno')
            ->leftJoin('egc_staff_bank_details','s.sno','=','egc_staff_bank_details.staff_id')
            ->leftJoin('egc_entity','s.entity_id', '=','egc_entity.sno' )
            ->leftJoin( 'egc_department','s.department_id','=','egc_department.sno' )
            ->where('egc_staff_salary_accounts.status', 0)
            ->whereDate('s.date_of_joining', '<=', $endOfMonth )
            ->select(
                's.*',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary',
                'egc_staff_salary_accounts.per_day_salary',
                'egc_staff_bank_details.ifsc_code',
                'egc_staff_bank_details.bank_account_no',
                'egc_company.company_name',
                'egc_entity.entity_name',
                'egc_department.department_name'
            );

            if ($salary_company != '') {
                $staffQuery->where('egc_staff_salary_accounts.salary_company_id',$salary_company );
            }

            if ($company_fill != '') {
                $staffQuery->where('s.company_id',$company_fill);
            }

            if ($entity_fill != '') {
                $staffQuery->where('s.entity_id',$entity_fill );
            }

            if ($department_fill != '') {
                $staffQuery->where('s.department_id',$department_fill );
            }

            if ($salary_type_fill != '') {
                $staffQuery->where('s.salary_type',$salary_type_fill);
            }

            if ($salary_date_fill != '') {
                $staffQuery->where('s.salary_date',$salary_date_fill);
            }

            if ($include_exit_staff_fill == 1) {
                $staffQuery->where(function ($q) use ($month, $year) {
                        /*
                        |--------------------------------------------------------------------------
                        | ACTIVE STAFF
                        |--------------------------------------------------------------------------
                        */
                    $q->whereIn('s.status', [0, 1])->whereIn('s.sno', $exitStaffIds);
                        /*
                        |--------------------------------------------------------------------------
                        | NOTICE PERIOD STAFF
                        |--------------------------------------------------------------------------
                        */
                    $q->orWhere(function ($notice) use ($month, $year) {
                        $notice->where('s.status', 4)
                            ->whereIn('s.sno', $exitStaffIds)
                            ->whereNotNull('s.notice_end_date')
                            ->whereMonth(
                                's.notice_end_date',
                                $month
                            )
                            ->whereYear(
                                's.notice_end_date',
                                $year
                            );
                    });
                        /*
                        |--------------------------------------------------------------------------
                        | EXITED STAFF
                        |--------------------------------------------------------------------------
                        */
                    $q->orWhere(function ($exit) use ($month, $year) {
                        $exit->whereIn('s.status', [5, 6, 7])
                            ->whereIn('s.sno', $exitStaffIds)
                            ->whereNotNull('s.staff_last_date')
                            ->whereMonth('s.staff_last_date', $month)
                            ->whereYear('s.staff_last_date', $year)
                            ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                            ->whereRaw(
                                'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                                [3]
                            );
                    })

                    ->orWhere(function ($special) use ($month, $year) {

                        $payrollMonth = sprintf('%04d-%02d', $year, $month);

                        $special->whereIn('s.sno', [211,212,94,77])
                            ->whereIn('s.status', [5, 6, 7])
                            ->whereIn('s.sno', $exitStaffIds)
                            ->whereNotNull('s.staff_last_date')
                            ->whereRaw(
                                "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                                AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                                [$payrollMonth]
                            )
                            ->whereRaw(
                                'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                                [3]
                            );
                    });
                });

            }else{
                $staffQuery->where('s.status', 0);
            }

            $staffList = $staffQuery
                ->orderBy('s.staff_name')
                ->get();

                $rows = [];

        $grandTotal = 0;

        foreach ($staffList as $staff) {
            if ($isLiveMode) {
                $payroll = app(\App\Services\PayrollPreviewService::class)->calculateLivePayroll( $staff,$month,$year,$only_bank_fill );
                $amount = round($payroll['net_salary'],2);
            } else {
                $processed = DB::table('egc_payroll_employee_payrolls')
                    ->where( 'staff_id',$staff->sno )
                    ->where('payroll_month',$month)
                    ->where('payroll_year',$year)
                    ->where('status', 0 )
                    ->first();

                if (!$processed) {
                    continue;
                }

                $amount = round($processed->net_payable,2);
            }

            $rows[] = [

                'staff_id' => $staff->staff_id,
                'staff_name' => $staff->staff_name,
                'department' => $staff->department_name,
                'company' => $staff->company_name,
                'entity' => $staff->entity_name,
                'account_no' => $staff->bank_account_no ?? '-',
                'ifsc' => $staff->ifsc_code ?? '-',
                'amount' => round($amount)
            ];

            $grandTotal += $amount;
        }

        $companyData = DB::table('egc_company')
                        ->join('egc_payslip_template','egc_payslip_template.company_id','=','egc_company.sno')
                        ->where('egc_company.sno',$salary_company)
                        ->select(
                            'egc_company.*',
                            'egc_payslip_template.letter_head_image',
                        )
                        ->first();
        $companyBank = DB::table('egc_payroll_cheque_details')
                        ->join('egc_company_bank_accounts','egc_company_bank_accounts.sno','=','egc_payroll_cheque_details.company_bank_id')
                        ->where('egc_payroll_cheque_details.salary_company_id',$salary_company)
                        ->where('egc_payroll_cheque_details.payroll_month', $month)
                        ->where('egc_payroll_cheque_details.payroll_year', $year)
                        ->select(
                            'egc_payroll_cheque_details.*',
                            'egc_company_bank_accounts.bank_name',
                            'egc_company_bank_accounts.bank_branch',
                            'egc_company_bank_accounts.ifsc_code',
                            'egc_company_bank_accounts.account_holder',
                        )
                        ->first();
            
                    // if ($salary_date_fill == 1) {
                    //     // 5th of the selected month and year
                    //     $salaryDate = Carbon::create($year, $month, 5);
                    // } elseif ($salary_date_fill == 2) {
                    //     // 15th of the selected month and year
                    //     $salaryDate = Carbon::create($year, $month, 15);
                    // } else {
                    //     // Default to today's date
                    //     $salaryDate = now();
                    // }

                    $salaryDate = now();

                    $format_salary_date  = $salaryDate->format('d M Y');   // 05 Jun 2026
                    $default_salary_date = $salaryDate->format('d.m.Y');   // 05.06.2026

            return [
                'month'          => Carbon::create($year,$month)->format('F Y'),
                'date'           => now()->format('d-m-Y'),
                'format_salary_date'           => $format_salary_date,
                'default_salary_date'           => $default_salary_date,
                'company_name'   => $companyData->company_name,
                'company_base_color'   => $companyData->company_base_color,
                'company_logo'   => $companyData->letter_head_image ? public_path('settings/letter_head/').$companyData->letter_head_image : Null,
                'bank_name'      => $companyBank ? $companyBank->bank_name :'' ,
                'bank_branch'    => $companyBank ? $companyBank->bank_branch :'' ,
                'current_account'=> $companyBank ? $companyBank->salary_curtacn :'xxxxxxxxxxxx',
                'cheque_no'      => $companyBank ? $companyBank->month_cheq :'xxxxxxxxxxxx',
                'employee_count' => count($rows),
                'grand_total'    => round($grandTotal),
                'amount_words'   => $this->numberToWords($grandTotal),
                'rows'           => $rows
            ];
    }


private function exportPayrollPdf($payroll)
{
    $pdf = Pdf::loadView(
        'content.hr_management.hr_general.manage_payroll.bank-letter',
        [
            'payroll' => $payroll
        ]
    );

    $pdf->setPaper('A4', 'portrait');

    return $pdf->stream(
        'Payroll_'.$payroll['month'].'.pdf'
    );
    // return $pdf->download(
    //     'Payroll_'.$payroll['month'].'.pdf'
    // );
}

private function numberToWords($amount)
{
    $formatter = new NumberFormatter(
        'en_IN',
        NumberFormatter::SPELLOUT
    );

    return ucwords(
        $formatter->format($amount)
    ).' Only';
}

// private function exportPayrollWord($payroll)
// {

//         $phpWord = new PhpWord();
//         $phpWord->setDefaultFontName('Calibri');
//         $phpWord->setDefaultFontSize(11);
//         $section = $phpWord->addSection([
//             'marginTop' => 800,
//             'marginBottom' => 800,
//             'marginLeft' => 900,
//             'marginRight' => 900
//         ]);

//         if(file_exists($payroll['company_logo']))
//         {
//             $section->addImage(
//                 $payroll['company_logo'],
//                 [
//                     'width' => 90,
//                     'height' => 90,
//                     'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
//                 ]
//             );
//         }

//         $section->addText(
//             $payroll['company_name'],
//             [
//                 'bold'=>true,
//                 'size'=>16
//             ],
//             [
//                 'alignment'=>Jc::CENTER
//             ]
//         );

//         $section->addText(
//             'Date : '.$payroll['date'],
//             [],
//             [
//                 'alignment'=>Jc::END
//             ]
//         );

//         $section->addText("To");
//         $section->addText("The Manager");
//         $section->addText($payroll['bank_name']);
//         $section->addText($payroll['bank_branch']);

//         $section->addTextBreak();

//         $section->addText(
//             'Sub : Salary Transfer - '.$payroll['month'],
//             [
//                 'bold'=>true
//             ]
//         );

//         $section->addTextBreak();
//         $text = $section->addTextRun();

//         $text->addText("Dear Sir,\n\n");

//         $text->addText(
//             "Please debit our Current Account No. ".
//             $payroll['current_account'].
//             " and credit the following employee salary accounts."
//         );

//         $text->addText("\n");

//         $text->addText(
//             "Cheque No : ".$payroll['cheque_no']
//         );

//         $text->addText("\n");

//         $text->addText(
//             "Amount : Rs. ".
//             number_format($payroll['grand_total'],2)
//         );

//         $text->addText("\n");

//         $text->addText(
//             "(".$payroll['amount_words'].")"
//         );
        
//         $section->addTextBreak();

//         $phpWord->addTableStyle(
//         'PayrollTable',
//         [
//             'borderSize'=>6,
//             'borderColor'=>'000000',
//             'cellMargin'=>60
//         ],
//         [
//             'bgColor'=>'D9D9D9'
//         ]
//         );

//     $table = $section->addTable('PayrollTable');
//     $table->addRow();
//     $table->addCell(800)->addText('S.No',['bold'=>true]);
//     $table->addCell(4500)->addText('Employee Name',['bold'=>true]);
//     $table->addCell(3000)->addText('Account Number',['bold'=>true]);
//     $table->addCell(2200)->addText('Amount',['bold'=>true]);
//     $sl = 1;
//     foreach($payroll['rows'] as $row)
//     {

//         $table->addRow();

//         $table->addCell(800)->addText($sl++);

//         $table->addCell(4500)->addText($row['staff_name']);

//         $table->addCell(3000)->addText($row['account_no']);

//         $table->addCell(2200)->addText(
//             number_format($row['amount'],2)
//         );

//     }

//     $table->addRow();

//     $table->addCell(800)->addText('');

//     $table->addCell(4500)->addText(
//         'TOTAL',
//         ['bold' => true]
//     );

//     $table->addCell(3000)->addText('');

//     $table->addCell(2200)->addText(
//         number_format($payroll['grand_total'],2),
//         ['bold' => true]
//     );


//     $section->addTextBreak(3);

//     $section->addText(
//     'Thanking You',
//     [
//     'bold'=>false
//     ]

//     );

//     $section->addTextBreak(2);

//     $section->addText(

//     'Managing Director',

//     [
//     'bold'=>true
//     ]

//     );


//     $fileName =

//     'Payroll_'.$payroll['month'].'.docx';

//     $tempFile = storage_path($fileName);

//     $writer = IOFactory::createWriter(
//         $phpWord,
//         'Word2007'
//     );

//     $writer->save($tempFile);

//     return response()->download(
//         $tempFile
//     )->deleteFileAfterSend(true);
// }

private function exportPayrollWord($payroll)
{
    $phpWord = new PhpWord();

    $phpWord->setDefaultFontName('Calibri');
    $phpWord->setDefaultFontSize(11);

    $section = $phpWord->addSection([
        'marginTop'    => 600,
        'marginBottom' => 600,
        'marginLeft'   => 700,
        'marginRight'  => 700,
    ]);

    if (!empty($payroll['company_logo']) && file_exists($payroll['company_logo'])) {

        $section->addImage(
            $payroll['company_logo'],
            [
                'width' => 500,
                'alignment' => Jc::CENTER
            ]
        );
    }

    $section->addText(
        'Date : '.$payroll['format_salary_date'],
        [],
        [
            'alignment' => Jc::END
        ]
    );

    $section->addTextBreak();

    $section->addText(
        'To',
        [
            'bold'=>true
        ]
    );

    $section->addText('The Manager');
    $section->addText($payroll['bank_name']);
    $section->addText($payroll['bank_branch']);

    $section->addTextBreak(1);


    $section->addText(
        'Reg : Our Current Account No : '.$payroll['current_account'],
        [
            'bold'=>true
        ]
    );

    $section->addTextBreak(1);

    $section->addText(
        'Dear Sir,'
    );

    $section->addTextBreak(1);

    $paragraphStyle = [
        'alignment' => Jc::BOTH,
        'spaceAfter' => 250
    ];

    $paragraphStyle = [
        'alignment' => Jc::BOTH,
        'spaceAfter' => 250,
    ];

    $textRun = $section->addTextRun($paragraphStyle);

    // Normal text
    $textRun->addText(
        'Please find herewith enclosed yourselves cheque for an amount of '
    );

    // Bold Amount
    $textRun->addText(
        'Rs. '.number_format($payroll['grand_total']),
        ['bold' => true]
    );

    // Normal
    $textRun->addText(' (Rupees ');

    // Bold Amount in Words
    $textRun->addText(
        $payroll['amount_words'],
        ['bold' => true]
    );

    // Normal
    $textRun->addText(' only) vide cheque ');

    // Bold Cheque Number
    $textRun->addText(
        $payroll['cheque_no'],
        ['bold' => true]
    );

    // Normal
    $textRun->addText('. ');

    // Bold Salary Date
    $textRun->addText(
        $payroll['default_salary_date'],
        ['bold' => true]
    );

    // Remaining normal text
    $textRun->addText(
        ' for our staff salary transfers purpose. Please arrange to credit in the respective account with you as detailed below towards staff salary.'
    );

    $section->addTextBreak();

    $companyColor = strtoupper(
        ltrim($payroll['company_base_color'] ?? '#AB2B22', '#')
    );

    $phpWord->addTableStyle(
        'PayrollTable',
        [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 70,
            'alignment'   => JcTable::CENTER
        ],
        [
            'bgColor' => $companyColor
        ]
    );

    $table = $section->addTable('PayrollTable');

    $headerFont = [
        'bold'  => true,
        'color' => 'FFFFFF',
        'size'  => 11
    ];

    $headerCell = [
        'valign' => 'center'
    ];

    $center = [
        'alignment' => Jc::CENTER
    ];

    $right = [
        'alignment' => Jc::END
    ];

    $table->addRow(500);

    $table->addCell(700, $headerCell)
        ->addText('Sl', $headerFont, $center);

    $table->addCell(5200, $headerCell)
        ->addText('Employee Name', $headerFont, $center);

    $table->addCell(3200, $headerCell)
        ->addText('Account Number', $headerFont, $center);

    $table->addCell(1800, $headerCell)
        ->addText('Amount', $headerFont, $center);

    $rowFont = [
        'size' => 10
    ];

    $sl = 1;

    foreach ($payroll['rows'] as $row)
    {
        $table->addRow();

        $table->addCell(700)
            ->addText($sl++, $rowFont, $center);

        $table->addCell(5200)
            ->addText($row['staff_name'], $rowFont);

        $table->addCell(3200)
            ->addText($row['account_no'], $rowFont);

        $table->addCell(1800)
            ->addText(
                number_format($row['amount']),
                $rowFont,
                $right
            );
    }

    $table->addRow();

    $table->addCell(
        9100,
        [
            'gridSpan' => 3,
            'valign'   => 'center'
        ]
    )->addText(
        'TOTAL',
        [
            'bold' => true
        ],
        [
            'alignment' => Jc::CENTER
        ]
    );

    $table->addCell(
        1800,
        [
            'valign' => 'center'
        ]
    )->addText(
        number_format($payroll['grand_total']),
        [
            'bold'=>true
        ],
        $right
    );

    $section->addTextBreak(1);

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    $signature = $section->addTextRun();

    $signature->addText(
        'For '.$payroll['company_name'].',',
        [
            'size' => 11
        ]
    );

    // Reduce the gap (1-2 lines is enough)
    $signature->addTextBreak(3);

    $signature->addText(
        'Managing Director',
        [
            'bold' => true,
            'size' => 11
        ]
    );

    $fileName = 'Payroll_'.$payroll['month'].'.docx';

    $tempDir = storage_path('app/temp');

    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $tempFile = $tempDir.'/'.$fileName;



    $writer = IOFactory::createWriter(
        $phpWord,
        'Word2007'
    );

    $writer->save($tempFile);

    return response()->download(
        $tempFile,
        $fileName
    )->deleteFileAfterSend(true);
}



public function getPayrollBankDetails(Request $request)
{
    if (empty($request->salary_company)  || empty($request->month)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid request.'
        ], 422);
    }

    try {
        $date = Carbon::createFromFormat('M-Y',$request->month);
        $month = $date->month;
        $year  = $date->year;

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid month.'
        ], 422);

    }
    /*
    |--------------------------------------------------------------------------
    | Company Bank
    |--------------------------------------------------------------------------
    */
    $banks = DB::table('egc_company_bank_accounts')
        ->where('company_id', $request->salary_company)
        ->where('status', 0)
        ->orderBy('bank_name')
        ->get();

    if ($banks->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Bank account not found.'
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Existing Cheque
    |--------------------------------------------------------------------------
    */
    $cheque = DB::table('egc_payroll_cheque_details')
        ->where('salary_company_id',$request->salary_company)
        ->where('salary_date_id',$request->salary_date)
        ->where('payroll_month',$month)
        ->where('payroll_year',$year)
        ->where('status',0)
        ->first();

    if($cheque){
        $selectedBank=$banks->where('sno',$cheque->company_bank_id)->first();
    }else{
        $selectedBank=$banks->first();
    }

    return response()->json([
        'status'=>true,
        'banks'=>$banks,
        'selected_bank'=>$selectedBank->sno,
        'bank_name'=>$selectedBank->bank_name,
        'bank_branch'=>$selectedBank->bank_branch,
        'account_holder'=>$selectedBank->account_holder,
        'ifsc_code'=>$selectedBank->ifsc_code,
        'current_account'=>$selectedBank->account_number,
        'cheque_no'=>$cheque->month_cheq ?? '',
        'salary_date'=>$cheque->salary_date_id ?? ''
    ]);

}


public function savePayrollChequeDetails(Request $request)
{
    $request->validate([
        'salary_company' => 'required|integer',
        'company_bank_id' => 'required|integer',
        'month' => 'required',
        'salary_date' => 'required',
        'current_acct' => 'required',
        'cheque_no' => 'required|max:100'

    ]);

    try {
        $date = Carbon::createFromFormat('M-Y', $request->month);
        $month = $date->month;
        $year  = $date->year;

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid Month'
        ], 422);
    }
    /*
    |--------------------------------------------------------------------------
    | Existing Record
    |--------------------------------------------------------------------------
    */
    $user_id = $request->user()->user_id ?? 0;
    $record = DB::table('egc_payroll_cheque_details')
        ->where('salary_company_id', $request->salary_company)
        ->where('salary_date_id', $request->salary_date)
        ->where('payroll_month', $month)
        ->where('payroll_year', $year)
        ->first();

    if ($record) {
        DB::table('egc_payroll_cheque_details')
            ->where('sno', $record->sno)
            ->update([
                'company_bank_id' => $request->company_bank_id,
                'salary_curtacn' => $request->current_acct,
                'salary_date_id' => $request->salary_date,
                'month_cheq' => $request->cheque_no,
                'updated_by' => $user_id,
                'updated_at' => now()

            ]);
        return response()->json([
            'status' => true,
            'message' => 'Cheque details updated successfully.'

        ]);

    }

    DB::table('egc_payroll_cheque_details')
        ->insert([
            'payroll_month' => $month,
            'payroll_year' => $year,
            'salary_company_id' => $request->salary_company,
            'company_bank_id' => $request->company_bank_id,
            'salary_curtacn' => $request->current_acct,
            'salary_date_id' => $request->salary_date,
            'month_cheq' => $request->cheque_no,
            'created_by' => $user_id,
            'updated_by' => $user_id,
            'created_at' => now(),
            'updated_at' => now(),
            'status' => 0
        ]);

    return response()->json([
        'status' => true,
        'message' => 'Cheque details saved successfully.'

    ]);

}

    public function commonExport(Request $request)
    {
        $monthFilter = $request->month_filter ?? date('M-Y');

        $parsedDate = Carbon::createFromFormat('M-Y',$monthFilter);

        $month = $parsedDate->month;
        $year  = $parsedDate->year;

        $salary_company    = $request->salary_company ?? '';
        $company_fill      = $request->company_fill ?? '';
        $entity_fill       = $request->entity_fill ?? '';
        $department_fill   = $request->department_fill ?? '';
        $salary_type_fill  = $request->salary_type_fill ?? '';
        $salary_date_fill  = $request->salary_date_fill ?? '';
        $only_bank_fill    = $request->only_bank_fill ? 1 : 0;
        $only_variable_fill    = $request->only_variable_fill ? 1 : 0;
        $exit_staff_fill   = $request->exit_staff_fill ? 1 : 0;
        $cheque_no   = $request->cheque_no ?? '';

        /*
        |--------------------------------------------------------------------------
        | Build Payroll Data
        |--------------------------------------------------------------------------
        */
        if($only_variable_fill == 1){

            $payrollData = $this->buildVariableData(
                $month,
                $year,
                $salary_company,
                $company_fill,
                $entity_fill,
                $department_fill,
                $salary_type_fill,
                $salary_date_fill,
                $only_bank_fill,
                $exit_staff_fill,
                $only_variable_fill
            );

            switch ($request->format) {

                case 'pdf':

                    return $this->exportVariablePayrollPdf(
                        $payrollData,
                        $month,
                        $year
                    );

                case 'docx':

                    return $this->exportVariablePayrollWord(
                        $payrollData,
                        $month,
                        $year
                    );

                default:

                    // return Excel::download(

                    //     new PayrollVariableExcelExport($payrollData),

                    //     'Payroll-'.$month.'.xlsx'

                    // );
            }

        }else{
            $payrollData = $this->buildCommonPayrollData(
                $month,
                $year,
                $salary_company,
                $company_fill,
                $entity_fill,
                $department_fill,
                $salary_type_fill,
                $salary_date_fill,
                $only_bank_fill,
                $exit_staff_fill,
                $only_variable_fill
            );

            /*
            |--------------------------------------------------------------------------
            | Export Type
            |--------------------------------------------------------------------------
            */

            switch ($request->format) {
                case 'full':

                    return $this->exportCommonFullPreview(
                        $payrollData,
                        $month,
                        $year
                    );

                case 'pdf':

                    return $this->exportCommonPayrollPdf(
                        $payrollData,
                        $month,
                        $year
                    );

                case 'docx':

                    return $this->exportCommonPayrollWord(
                        $payrollData,
                        $month,
                        $year
                    );

                default:

                    return Excel::download(

                        new PayrollCommonExcelExport($payrollData),

                        'Payroll-'.$month.'.xlsx'

                    );
            }
        }

       
    }

    private function buildCommonPayrollData(
        $month,
        $year,
        $salary_company,
        $company_fill,
        $entity_fill,
        $department_fill,
        $salary_type_fill,
        $salary_date_fill,
        $only_bank_fill,
        $exit_staff_fill,
        $only_variable_fill
    )
    {
        $endOfMonth = date('Y-m-t',strtotime("$year-$month-01"));

        $payrollProcess = DB::table('egc_payroll_processes')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();

        $isLiveMode = !$payrollProcess;

        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company','s.company_id','=','egc_company.sno')
            ->leftJoin('egc_company as salary_comp','egc_staff_salary_accounts.salary_company_id','=','salary_comp.sno')
            ->leftJoin('egc_staff_bank_details','s.sno','=','egc_staff_bank_details.staff_id')
            ->leftJoin('egc_entity','s.entity_id', '=','egc_entity.sno' )
            ->leftJoin( 'egc_department','s.department_id','=','egc_department.sno' )
            ->where('egc_staff_salary_accounts.status', 0)
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth(
                            's.notice_end_date',
                            $month
                        )
                        ->whereYear(
                            's.notice_end_date',
                            $year
                        );
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                })

                ->orWhere(function ($special) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);

                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            ->select(
                's.*',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary',
                'egc_staff_salary_accounts.per_day_salary',
                'egc_staff_bank_details.ifsc_code',
                'egc_staff_bank_details.bank_account_no',
                'egc_company.company_name',
                'salary_comp.company_name as salary_company_name',
                'egc_entity.entity_name',
                'egc_entity.entity_base_color',
                'egc_department.department_name'
            );

            if ($salary_company != '') {
                $staffQuery->where('egc_staff_salary_accounts.salary_company_id',$salary_company );
            }

            if ($company_fill != '') {
                $staffQuery->where('s.company_id',$company_fill);
            }

            if ($entity_fill != '') {
                $staffQuery->where('s.entity_id',$entity_fill );
            }

            if ($department_fill != '') {
                $staffQuery->where('s.department_id',$department_fill );
            }

            if ($salary_type_fill != '') {
                $staffQuery->where('s.salary_type',$salary_type_fill);
            }

            if ($salary_date_fill != '') {
                $staffQuery->where('s.salary_date',$salary_date_fill);
            }

            // if (!$exit_staff_fill) {
            //     $staffQuery->whereIn('s.status',[0,1] );
            // }

            $staffList = $staffQuery
                ->orderBy('s.staff_name')
                ->get();

                $rows = [];
           

        $grandTotal = 0;
            
        if ($isLiveMode) {
            foreach ($staffList as $staff) {
           
                    $payroll = app(\App\Services\PayrollPreviewService::class)->calculateLivePayroll( $staff,$month,$year,$only_bank_fill );
                    $amount = round($payroll['net_salary'],2);

                    $components =collect($payroll['components']);
                    $onduty =(float)$components->where('code', 'ONDUTY')->sum('amount');
                    $variable =(float)$components->where('code', 'VARIABLE')->sum('amount');
                    $incentive =(float)$components->where('code', 'INCENTIVE')->sum('amount');
                    $pf =(float)$components->where('code', 'PF')->sum('amount');
                    $esi =(float)$components->where('code', 'ESI')->sum('amount');
                    $pt =(float)$components->where('code', 'PT')->sum('amount');
                    $tax =(float)$components->where('code', 'TAX')->sum('amount');
                    $employerPf =(float)$components->where('code', 'EMPLOYER_PF')->sum('amount');
                    $employerEsi =(float)$components->where('code', 'EMPLOYER_ESI')->sum('amount');
                    $lopAmount =(float)$components->where('code', 'LOP')->sum('amount');
                

                $rows[] = [

                    'staff_id' => $staff->staff_id,
                    'staff_name' => $staff->staff_name,
                    'salary_company_name' => $staff->salary_company_name,
                    'department' => $staff->department_name,
                    'company' => $staff->company_name,
                    'entity_name' => $staff->entity_name ?? 'Elysium Groups',
                    'entity_base_color' => $staff->entity_base_color ?? '#ab2b22',
                    'salary_type' => $staff->salary_type,
                    'salary_date' => $staff->salary_date,
                    'account_no' => $staff->bank_account_no ?? '-',
                    'ifsc' => $staff->ifsc_code ?? '-',
                    'amount' => round($amount),
                    'earnings' => round($payroll['earnings']),
                    'deductions' => round($payroll['deductions']),
                    'gross_salary' => round($payroll['gross_salary']),
                    'net' => round($payroll['net_salary']),
                    'employer_contribution' => round($payroll['employer_contribution']),
                    'lop_days' => round($payroll['lop_days'], 2),
                    'onduty' => round($onduty),
                    'variable' => round($variable),
                    'incentive' => round($incentive),
                    'pf' => round($pf),
                    'esi' => round($esi),
                    'pt' => round($pt),
                    'tax' => round($tax),
                    'lopAmount' => round($lopAmount),
                ];

                $grandTotal += $amount;
            }
        }else{
            $employeeIds = $staffList->pluck('s.sno');
            $allStaffAccountIds = $staffList->pluck('egc_staff_salary_accounts.salary_account_id');
            
            $processedPayrolls = DB::table('egc_payroll_employee_payrolls as ep')
                // ->whereIn('ep.staff_id', $employeeIds)
                ->whereIn('ep.salary_account_id', $allStaffAccountIds)
                ->where('ep.payroll_month', $month)
                ->where('ep.payroll_year', $year)
                ->where('ep.status', 0)
                ->get()
                ->keyBy('staff_id');
            

            foreach ($staffList as $staff) {
                $payroll = $processedPayrolls[$staff->sno] ?? null;

                $pfAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%PF%')
                    ->sum('actual_amount');

                $esiAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%ESI%')
                    ->sum('actual_amount');
                $payrollSlipData = DB::table('egc_payroll_payslips')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_payslips.payroll_process_sno')
                        ->where( 'egc_payroll_payslips.employee_sno', $staff->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->select('egc_payroll_payslips.sno')
                        ->first();
                $payrollAttendance = DB::table('egc_payroll_attendance_summaries')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_attendance_summaries.payroll_process_sno')
                        ->where( 'egc_payroll_attendance_summaries.employee_sno', $staff->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->first();

                $details =DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno',$payroll->sno)
                    ->where('status', 0)
                    ->get();

                    $componentSum = function ($code) use ($details) {
                        return (float)$details->where('component_code',$code)->sum('actual_amount');
                    };

                    

                    $amount =  round($payroll->net_payable ?? 0);

                    $onduty =$componentSum('ONDUTY');
                    $variable =$componentSum('VARIABLE');
                    $incentive =$componentSum('INCENTIVE');
                    $pf =$componentSum('PF');
                    $esi =$componentSum('ESI');
                    $pt =$componentSum('PT');
                    $tax =$componentSum('TAX');
                    $employerPf =$componentSum('EMPLOYER_PF');
                    $employerEsi =$componentSum('EMPLOYER_ESI');
                    $lopAmount =$payroll->lop_amount;

                    $rows[] = [

                    'staff_id' => $staff->staff_id,
                    'staff_name' => $staff->staff_name,
                    'salary_company_name' => $staff->salary_company_name,
                    'department' => $staff->department_name,
                    'company' => $staff->company_name,
                    'entity_name' => $staff->entity_name ?? 'Elysium Groups',
                    'entity_base_color' => $staff->entity_base_color ?? '#ab2b22',
                    'salary_type' => $staff->salary_type,
                    'salary_date' => $staff->salary_date,
                    'account_no' => $staff->bank_account_no ?? '-',
                    'ifsc' => $staff->ifsc_code ?? '-',
                    'amount' => round($amount),
                    'deductions' => round($payroll->deductions),
                    'gross_salary' => round($payroll->gross_salary),
                    'net' => round($payroll->net_salary),
                    'lop_days' => round($payroll->lop_days, 2),
                    'onduty' => round($onduty),
                    'variable' => round($variable),
                    'incentive' => round($incentive),
                    'pf' => round($pf),
                    'esi' => round($esi),
                    'pt' => round($pt),
                    'tax' => round($tax),
                    'lopAmount' => round($lopAmount),
                ];

                $grandTotal += $amount;

               
            }
        }
        

        $companyData = DB::table('egc_company')
                        ->join('egc_payslip_template','egc_payslip_template.company_id','=','egc_company.sno')
                        ->where('egc_company.sno',$salary_company)
                        ->select(
                            'egc_company.*',
                            'egc_payslip_template.letter_head_image',
                        )
                        ->first();
        $companyBank = DB::table('egc_payroll_cheque_details')
                        ->join('egc_company_bank_accounts','egc_company_bank_accounts.sno','=','egc_payroll_cheque_details.company_bank_id')
                        ->where('egc_payroll_cheque_details.salary_company_id',$salary_company)
                        ->where('egc_payroll_cheque_details.payroll_month', $month)
                        ->where('egc_payroll_cheque_details.payroll_year', $year)
                        ->select(
                            'egc_payroll_cheque_details.*',
                            'egc_company_bank_accounts.bank_name',
                            'egc_company_bank_accounts.bank_branch',
                            'egc_company_bank_accounts.ifsc_code',
                            'egc_company_bank_accounts.account_holder',
                        )
                        ->first();
            
                    // if ($salary_date_fill == 1) {
                    //     // 5th of the selected month and year
                    //     $salaryDate = Carbon::create($year, $month, 5);
                    // } elseif ($salary_date_fill == 2) {
                    //     // 15th of the selected month and year
                    //     $salaryDate = Carbon::create($year, $month, 15);
                    // } else {
                    //     // Default to today's date
                    //     $salaryDate = now();
                    // }

                    $salaryDate = now();

                    $format_salary_date  = $salaryDate->format('d M Y');   // 05 Jun 2026
                    $default_salary_date = $salaryDate->format('d.m.Y');   // 05.06.2026

            return [
                'month'          => Carbon::create($year,$month)->format('F Y'),
                'date'           => now()->format('d-m-Y'),
                'format_salary_date'           => $format_salary_date,
                'default_salary_date'           => $default_salary_date,
                'company_name'   => $companyData->company_name ?? '',
                'company_base_color'   => $companyData->company_base_color ?? '#ab2b22',
                'company_logo'   => $companyData ? ($companyData->letter_head_image ? public_path('settings/letter_head/').$companyData->letter_head_image : Null) : "",
                'bank_name'      => $companyBank ? $companyBank->bank_name :'' ,
                'bank_branch'    => $companyBank ? $companyBank->bank_branch :'' ,
                'current_account'=> $companyBank ? $companyBank->salary_curtacn :'xxxxxxxxxxxx',
                'cheque_no'      => 'xxxxxxxxxxxx',
                'employee_count' => count($rows),
                'grand_total'    => round($grandTotal),
                'amount_words'   => $this->numberToWords($grandTotal),
                'rows'           => $rows
            ];
    }

    private function exportCommonPayrollPdf($payroll)
    {
        $pdf = Pdf::loadView(
            'content.hr_management.hr_general.manage_payroll.common-letter',
            [
                'payroll' => $payroll
            ]
        );

        // $pdf = PDF::loadView('payroll.export_pdf', $data)
        //   ->setPaper('a4', 'landscape');

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream(
            'Payroll_'.$payroll['month'].'.pdf'
        );
        // return $pdf->download(
        //     'Payroll_'.$payroll['month'].'.pdf'
        // );
    }
    private function exportCommonFullPreview($payroll)
    {
      
        return view('content.hr_management.hr_general.manage_payroll.full_preview_payroll',
                [
                    'payroll' => $payroll
                ]); 
    }

    private function buildVariableData(
        $month,
        $year,
        $salary_company,
        $company_fill,
        $entity_fill,
        $department_fill,
        $salary_type_fill,
        $salary_date_fill,
        $only_bank_fill,
        $exit_staff_fill,
        $only_variable_fill
    )
    {
        $endOfMonth = date('Y-m-t',strtotime("$year-$month-01"));

        $payrollProcess = DB::table('egc_payroll_processes')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();

        $isLiveMode = !$payrollProcess;

        $currentMonth = Carbon::create($year, $month, 1)
                        ->format('Y-m-01');

        $variableAmountIds = DB::table('egc_payroll_variable_amounts')
                // ->where('employee_sno', $staff->sno)
                ->where('status', 0)
                // ->where('is_expired', 0)
                ->whereDate('start_month','<=',$currentMonth)
                ->whereDate('end_month','>=',$currentMonth)
                ->orderByDesc('sno')
                ->pluck('employee_sno');

      

        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company','s.company_id','=','egc_company.sno')
            ->leftJoin('egc_company as salary_comp','egc_staff_salary_accounts.salary_company_id','=','salary_comp.sno')
            ->leftJoin('egc_staff_bank_details','s.sno','=','egc_staff_bank_details.staff_id')
            ->leftJoin('egc_entity','s.entity_id', '=','egc_entity.sno' )
            ->leftJoin( 'egc_department','s.department_id','=','egc_department.sno' )
            ->where('egc_staff_salary_accounts.status', 0)
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth(
                            's.notice_end_date',
                            $month
                        )
                        ->whereYear(
                            's.notice_end_date',
                            $year
                        );
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                })

                ->orWhere(function ($special) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);

                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->whereIn('s.sno', $variableAmountIds)
            ->select(
                's.*',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary',
                'egc_staff_salary_accounts.per_day_salary',
                'egc_staff_bank_details.ifsc_code',
                'egc_staff_bank_details.bank_account_no',
                'egc_company.company_name',
                'salary_comp.company_name as salary_company_name',
                'egc_entity.entity_name',
                'egc_department.department_name'
            );

            if ($salary_company != '') {
                $staffQuery->where('egc_staff_salary_accounts.salary_company_id',$salary_company );
            }

            if ($company_fill != '') {
                $staffQuery->where('s.company_id',$company_fill);
            }

            if ($entity_fill != '') {
                $staffQuery->where('s.entity_id',$entity_fill );
            }

            if ($department_fill != '') {
                $staffQuery->where('s.department_id',$department_fill );
            }

            if ($salary_type_fill != '') {
                $staffQuery->where('s.salary_type',$salary_type_fill);
            }

            if ($salary_date_fill != '') {
                $staffQuery->where('s.salary_date',$salary_date_fill);
            }

            // if (!$exit_staff_fill) {
            //     $staffQuery->whereIn('s.status',[0,1] );
            // }

            $staffList = $staffQuery
                ->orderBy('s.staff_name')
                ->get();

                $rows = [];

        $grandTotal = 0;
         
        if ($isLiveMode) {
            foreach ($staffList as $staff) {
           
                    $payroll = app(\App\Services\PayrollPreviewService::class)->calculateLivePayroll( $staff,$month,$year,$only_bank_fill );
                   

                    $components =collect($payroll['components']);
                     $amount = (float)$components->where('code', 'VARIABLE')->sum('amount');
                    $onduty =(float)$components->where('code', 'ONDUTY')->sum('amount');
                    $variable =(float)$components->where('code', 'VARIABLE')->sum('amount');
                    $incentive =(float)$components->where('code', 'INCENTIVE')->sum('amount');
                    $pf =(float)$components->where('code', 'PF')->sum('amount');
                    $esi =(float)$components->where('code', 'ESI')->sum('amount');
                    $pt =(float)$components->where('code', 'PT')->sum('amount');
                    $tax =(float)$components->where('code', 'TAX')->sum('amount');
                    $employerPf =(float)$components->where('code', 'EMPLOYER_PF')->sum('amount');
                    $employerEsi =(float)$components->where('code', 'EMPLOYER_ESI')->sum('amount');
                    $lopAmount =(float)$components->where('code', 'LOP')->sum('amount');
                

                $rows[] = [

                    'staff_id' => $staff->staff_id,
                    'staff_name' => $staff->staff_name,
                    'salary_company_name' => $staff->salary_company_name,
                    'department' => $staff->department_name,
                    'company' => $staff->company_name,
                    'entity_name' => $staff->entity_name ?? 'Elysium Groups',
                    'account_no' => $staff->bank_account_no ?? '-',
                    'ifsc' => $staff->ifsc_code ?? '-',
                    'amount' => round($amount),
                    'earnings' => round($payroll['earnings']),
                    'deductions' => round($payroll['deductions']),
                    'gross_salary' => round($payroll['gross_salary']),
                    'net' => round($payroll['net_salary']),
                    'employer_contribution' => round($payroll['employer_contribution']),
                    'lop_days' => round($payroll['lop_days'], 2),
                    'onduty' => round($onduty),
                    'variable' => round($variable),
                    'incentive' => round($incentive),
                    'pf' => round($pf),
                    'esi' => round($esi),
                    'pt' => round($pt),
                    'tax' => round($tax),
                    'lopAmount' => round($lopAmount),
                ];

                $grandTotal += $amount;
            }
        }else{
            $employeeIds = $staffList->pluck('s.sno');
            
            $processedPayrolls = DB::table('egc_payroll_employee_payrolls as ep')
                ->whereIn('ep.staff_id', $employeeIds)
                ->where('ep.payroll_month', $month)
                ->where('ep.payroll_year', $year)
                ->where('ep.status', 0)
                ->get()
                ->keyBy('staff_id');

            foreach ($staffList as $staff) {
                $payroll = $processedPayrolls[$staff->sno] ?? null;

                $pfAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%PF%')
                    ->sum('actual_amount');

                $esiAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%ESI%')
                    ->sum('actual_amount');
                $payrollSlipData = DB::table('egc_payroll_payslips')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_payslips.payroll_process_sno')
                        ->where( 'egc_payroll_payslips.employee_sno', $staff->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->select('egc_payroll_payslips.sno')
                        ->first();
                $payrollAttendance = DB::table('egc_payroll_attendance_summaries')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_attendance_summaries.payroll_process_sno')
                        ->where( 'egc_payroll_attendance_summaries.employee_sno', $staff->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->first();

                $details =DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno',$payroll->sno)
                    ->where('status', 0)
                    ->get();

                    $componentSum = function ($code) use ($details) {
                        return (float)$details->where('component_code',$code)->sum('actual_amount');
                    };

                    

                    $amount =  $componentSum('VARIABLE');

                    $onduty =$componentSum('ONDUTY');
                    $variable =$componentSum('VARIABLE');
                    $incentive =$componentSum('INCENTIVE');
                    $pf =$componentSum('PF');
                    $esi =$componentSum('ESI');
                    $pt =$componentSum('PT');
                    $tax =$componentSum('TAX');
                    $employerPf =$componentSum('EMPLOYER_PF');
                    $employerEsi =$componentSum('EMPLOYER_ESI');
                    $lopAmount =$payroll->lop_amount;

                    $rows[] = [

                    'staff_id' => $staff->staff_id,
                    'staff_name' => $staff->staff_name,
                    'salary_company_name' => $staff->salary_company_name,
                    'department' => $staff->department_name,
                    'company' => $staff->company_name,
                    'entity_name' => $staff->entity_name ?? 'Elysium Groups',
                    'account_no' => $staff->bank_account_no ?? '-',
                    'ifsc' => $staff->ifsc_code ?? '-',
                    'amount' => round($amount),
                    'deductions' => round($payroll->deductions),
                    'gross_salary' => round($payroll->gross_salary),
                    'net' => round($payroll->net_salary),
                    'lop_days' => round($payroll->lop_days, 2),
                    'onduty' => round($onduty),
                    'variable' => round($variable),
                    'incentive' => round($incentive),
                    'pf' => round($pf),
                    'esi' => round($esi),
                    'pt' => round($pt),
                    'tax' => round($tax),
                    'lopAmount' => round($lopAmount),
                ];

                $grandTotal += $amount;

               
            }
        }
        

        $companyData = DB::table('egc_company')
                        ->join('egc_payslip_template','egc_payslip_template.company_id','=','egc_company.sno')
                        ->where('egc_company.sno',$salary_company)
                        ->select(
                            'egc_company.*',
                            'egc_payslip_template.letter_head_image',
                        )
                        ->first();
        $companyBank = DB::table('egc_payroll_cheque_details')
                        ->join('egc_company_bank_accounts','egc_company_bank_accounts.sno','=','egc_payroll_cheque_details.company_bank_id')
                        ->where('egc_payroll_cheque_details.salary_company_id',$salary_company)
                        ->where('egc_payroll_cheque_details.payroll_month', $month)
                        ->where('egc_payroll_cheque_details.payroll_year', $year)
                        ->select(
                            'egc_payroll_cheque_details.*',
                            'egc_company_bank_accounts.bank_name',
                            'egc_company_bank_accounts.bank_branch',
                            'egc_company_bank_accounts.ifsc_code',
                            'egc_company_bank_accounts.account_holder',
                        )
                        ->first();
            
                    // if ($salary_date_fill == 1) {
                    //     // 5th of the selected month and year
                    //     $salaryDate = Carbon::create($year, $month, 5);
                    // } elseif ($salary_date_fill == 2) {
                    //     // 15th of the selected month and year
                    //     $salaryDate = Carbon::create($year, $month, 15);
                    // } else {
                    //     // Default to today's date
                    //     $salaryDate = now();
                    // }

                    $salaryDate = now();

                    $format_salary_date  = $salaryDate->format('d M Y');   // 05 Jun 2026
                    $default_salary_date = $salaryDate->format('d.m.Y');   // 05.06.2026

            return [
                'month'          => Carbon::create($year,$month)->format('F Y'),
                'date'           => now()->format('d-m-Y'),
                'format_salary_date'           => $format_salary_date,
                'default_salary_date'           => $default_salary_date,
                'company_name'   => $companyData->company_name ?? '',
                'company_base_color'   => $companyData->company_base_color,
                'company_logo'   => $companyData->letter_head_image ? public_path('settings/letter_head/').$companyData->letter_head_image : Null,
                'bank_name'      => $companyBank ? $companyBank->bank_name :'' ,
                'bank_branch'    => $companyBank ? $companyBank->bank_branch :'' ,
                'current_account'=> $companyBank ? $companyBank->salary_curtacn :'xxxxxxxxxxxx',
                'cheque_no'      => 'xxxxxxxxxxxx',
                'employee_count' => count($rows),
                'grand_total'    => round($grandTotal),
                'amount_words'   => $this->numberToWords($grandTotal),
                'rows'           => $rows
            ];
    }

    private function exportVariablePayrollPdf($payroll)
    {
        $pdf = Pdf::loadView(
            'content.hr_management.hr_general.manage_payroll.variable-letter',
            [
                'payroll' => $payroll
            ]
        );

        // $pdf = PDF::loadView('payroll.export_pdf', $data)
        //   ->setPaper('a4', 'landscape');

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream(
            'Payroll_vaiable_'.$payroll['month'].'.pdf'
        );
        // return $pdf->download(
        //     'Payroll_'.$payroll['month'].'.pdf'
        // );
    }

    public function getMonthExitStaff(Request $request)
{
    if (empty($request->month)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid request.'
        ], 422);
    }

    try {
        $date = Carbon::createFromFormat('M-Y',$request->month);
        $month = $date->month;
        $year  = $date->year;

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid month.'
        ], 422);

    }
    /*
    |--------------------------------------------------------------------------
    | Company Bank
    |--------------------------------------------------------------------------
    */
        $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));
        $salary_company = $request->salary_company ?? '';
        $salary_date_fill = $request->salary_date ?? '';

     $exitStaffList = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company as salary_company', 'egc_staff_salary_accounts.salary_company_id', '=', 'salary_company.sno')
            ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                $q->whereIn('s.status', [0, 1]);
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth( 's.notice_end_date',$month)
                        ->whereYear('s.notice_end_date', $year);
                });
                $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw('DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',[3] );
                })
                ->orWhere(function ($special) use ($month, $year) {
                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            ->select(
                's.*',
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary as basic_salary',
                'egc_staff_salary_accounts.per_day_salary',
                's.company_id',
                's.entity_id',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_type',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.staff_last_date',
                's.notice_start_date',
                's.notice_end_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'salary_company.company_base_color as salary_company_base_color',
                'salary_company.company_name as salary_company_name',
                'egc_company.company_base_color',
                'egc_company.company_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name'
            )->where('s.status', '>', 2 );
            
             if ($request->salary_company != '') {
                $exitStaffList->where('egc_staff_salary_accounts.salary_company_id', $request->salary_company);
            }
            if ($request->salary_type_fill != '') {
                $exitStaffList->where('s.salary_type', $request->salary_type_fill);
            }
            if ($salary_date_fill != '') {
                $exitStaffList->where('s.salary_date', $salary_date_fill);
            }
            $exitStaffList= $exitStaffList->get();

    return response()->json([
        'status'=>true,
        'exit_staff'=>$exitStaffList,
    ]);

}

    public function staffListByEntity(Request $request)
    {
        $staffs = StaffModel::join('egc_job_role','egc_staff.job_role_id','=','egc_job_role.sno')
            ->where('egc_staff.entity_id',$request->entity_id)
            ->where('egc_staff.status',0)
            ->orderBy('egc_staff.staff_name')
            ->select(
                'egc_staff.sno',
                'egc_staff.staff_name',
                'egc_staff.staff_id',
                'egc_job_role.job_position_name'
            )
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $staffs
        ]);
    }

    public function getCTCStructure($staffId)
    {
        $staff = StaffModel::query()
            ->leftJoin('egc_company', 'egc_company.sno', '=', 'egc_staff.company_id')
            ->leftJoin('egc_entity', 'egc_entity.sno', '=', 'egc_staff.entity_id')
            ->leftJoin('egc_department', 'egc_department.sno', '=', 'egc_staff.department_id')
            ->leftJoin('egc_division', 'egc_division.sno', '=', 'egc_staff.division_id')
            ->leftJoin('egc_job_role', 'egc_job_role.sno', '=', 'egc_staff.job_role_id')
            ->where('egc_staff.sno', $staffId)
            ->where('egc_staff.status', '!=', 2)
            ->select(
                'egc_staff.*',
                'egc_company.company_name',
                'egc_company.company_logo',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_entity.entity_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name'
            )
            ->first();

        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found.'
            ]);
        }

        $salaryAccounts = DB::table('egc_staff_salary_accounts')
            ->leftJoin( 'egc_company as salary_company', 'salary_company.sno', '=', 'egc_staff_salary_accounts.salary_company_id')
            ->where('egc_staff_salary_accounts.staff_id', $staffId)
            ->where('egc_staff_salary_accounts.status', 0)
            ->orderByDesc('egc_staff_salary_accounts.is_primary')
            ->select(
                'egc_staff_salary_accounts.*',
                'salary_company.company_name as salary_company_name',
                'salary_company.company_base_color'
            )
            ->get();

        if ($salaryAccounts->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Salary Account not found.'
            ]);
        }


        $gross = 0;
        $deduction = 0;
        $netSalary = 0;
        $monthlyCTC = 0;
        $employerContribution = 0;
        $components = [];

        foreach ($salaryAccounts as $account) {
            $structure = DB::table('egc_payroll_employee_structures')
                ->where('salary_account_id', $account->sno)
                ->where('status', 0)
                ->where('is_current', 1)
                ->first();
            if (!$structure) {
                continue;
            }

           
            $structureDetails = DB::table('egc_payroll_employee_structure_details as d')
                ->join( 'egc_payroll_components as c', 'c.sno', '=','d.payroll_component_sno')
                ->where('d.payroll_employee_structure_sno',$structure->sno)
                ->where('d.status', 0)
                ->orderBy('d.display_order')
                ->select('d.*', 'c.component_name' )
                ->get();
            
            $gross += (float) $structure->gross_salary;
            $deduction += (float) $structure->total_deductions;
            $netSalary += (float) $structure->net_salary;
            $employerContribution += (float) $structure->employer_contribution;
            $monthlyCTC += (float) $structure->ctc_amount;
          
            foreach ($structureDetails as $detail) {
                $category = '';
                switch ($detail->component_type) {
                    case 'earning':
                        $category = 'earning';
                        break;
                    case 'deduction':
                        $category = 'deduction';
                        break;
                    case 'employer_contribution':
                        $category = 'employer_contribution';
                        break;
                    default:
                        $category = $detail->component_type;
                        break;
                }

                $components[] = [
                    'salary_account_id' => $account->sno,
                    'salary_company' => $account->salary_company_name,
                    'salary_company_color' => $account->company_base_color,
                    'structure_id' => $structure->sno,
                    'structure_code' => $structure->structure_code,
                    'structure_name' => $structure->structure_name,
                    'component_id' => $detail->payroll_component_sno,
                    'component' => $detail->component_name,
                    'category' => $category,
                    'calculation_type' => $detail->calculation_type,
                    'calculated_on' => $detail->calculated_on,
                    'monthly' => round((float) $detail->calculated_amount, 2),
                    'annual' => round((float) $detail->calculated_amount * 12, 2),
                    'percentage' => round((float) $detail->percentage_value, 2),
                    'fixed_amount' => round((float) $detail->fixed_amount, 2),
                    'include_in_gross' => (int) $detail->include_in_gross,
                    'include_in_ctc' => (int) $detail->include_in_ctc,
                    'include_in_payslip' => (int) $detail->include_in_payslip,
                    'monthly_variable' => (int) $detail->monthly_variable,
                    'display_order' => (int) $detail->display_order,
                    'remarks' => $detail->remarks
                ];

            }

           

            usort($components, function ($a, $b) {
                if ($a['salary_company'] == $b['salary_company']) {
                    return $a['display_order'] <=> $b['display_order'];
                }
                return strcmp($a['salary_company'], $b['salary_company']);

            });

        }



        $annualCTC = $monthlyCTC * 12;

        return response()->json([
            'status' => true,
            'data' => [
                'staff_id' => $staff->sno,
                'staff_name' => $staff->staff_name,
                'staff_code' => $staff->staff_id,
                'profile_image_url' => $staff->profile_image_url,
                'department_name' => $staff->department_name,
                'division_name' => $staff->division_name,
                'designation' => $staff->job_role_name,
                'entity_name' => $staff->entity_name,
                'entity_base_color' => $staff->entity_base_color,
                'salary_company' => $salaryAccounts
                                        ->pluck('salary_company_name')
                                        ->filter()
                                        ->implode(', '),
                'salary_account_count' => $salaryAccounts->count(),
                'is_multiple_account' => (int)$staff->is_multiple_account,
                'salary_type' => $staff->salary_type == 1
                                    ? 'Cash'
                                    : 'Bank',
                'doj' => $staff->date_of_joining,
                'gross' => round($gross,2),
                'deduction' => round($deduction,2),
                'net_salary' => round($netSalary,2),
                'employer_contribution' => round($employerContribution,2),
                'monthly_ctc' => round($monthlyCTC,2),
                'annual_ctc' => round($annualCTC,2),
                'components' => $components

            ]

        ]);
    }

    public function calculateCTC(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'components' => 'required|array|min:1'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors()->first()
            ]);
        }

        $gross = 0;
        $deduction = 0;
        $employer = 0;

        $components = [];

        foreach($request->components as $row){
            $monthly = (float)($row['monthly'] ?? 0);
            $annual = $monthly * 12;
            $category = strtolower($row['category']);

            switch($category){
                case 'earning':
                    $gross += $monthly;
                break;
                case 'deduction':
                    $deduction += $monthly;
                break;
                case 'employer':
                case 'employer_contribution':
                    $employer += $monthly;
                break;

            }

            $row['monthly'] = round($monthly,2);
            $row['annual'] = round($annual,2);
            $components[] = $row;

        }
        $net = $gross - $deduction;
        $monthlyCTC = $gross + $employer;
        $annualCTC = $monthlyCTC * 12;

        return response()->json([
            'status'=>true,
            'summary'=>[
                'gross'=>round($gross,2),
                'deduction'=>round($deduction,2),
                'employer_contribution'=>round($employer,2),
                'net_salary'=>round($net,2),
                'monthly_ctc'=>round($monthlyCTC,2),
                'annual_ctc'=>round($annualCTC,2)
            ],
            'components'=>$components

        ]);
    }


    private function getPayrollStaffQuery($month, $year)
    {
        $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company as salary_company', 'egc_staff_salary_accounts.salary_company_id', '=', 'salary_company.sno')
            ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth(
                            's.notice_end_date',
                            $month
                        )
                        ->whereYear(
                            's.notice_end_date',
                            $year
                        );
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                })

                ->orWhere(function ($special) use ($month, $year) {
                    $payrollMonth = sprintf('%04d-%02d', $year, $month);
                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            ->select(
                's.*',
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary as basic_salary',
                'egc_staff_salary_accounts.per_day_salary',
                's.company_id',
                's.entity_id',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_type',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.staff_last_date',
                's.notice_start_date',
                's.notice_end_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'salary_company.company_base_color as salary_company_base_color',
                'salary_company.company_name as salary_company_name',
                'egc_company.company_base_color',
                'egc_company.company_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name'
            );

            return $staffQuery;
    }

    private function getMonthlySalarySummary($month, $year, $isLiveMode)
    {
        $staffQuery = $this->getPayrollStaffQuery($month, $year);
        $staff = $staffQuery->get();
        $allStaffAccountIds = $staffQuery->pluck('egc_staff_salary_accounts.sno');
        if ($staff->isEmpty()) {
            return [
                'fixed_gross' => 0,
                'gross' => 0,
                'net' => 0,
                'deduction' => 0,
            ];
        }

        if (!$isLiveMode) {
            $staffIds = $staff->pluck('sno');
            $summary = DB::table('egc_payroll_employee_payrolls')
                ->whereIn('salary_account_id', $allStaffAccountIds)
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->where('status', 0)
                ->selectRaw("
                    COALESCE(SUM(fixed_salary),0) as fixed_gross,
                    COALESCE(SUM(gross_earnings),0) as gross,
                    COALESCE(SUM(net_payable),0) as net,
                    COALESCE(SUM(gross_deductions),0) as deduction
                ")
                ->first();
            return [
                'gross' => (float) $summary->gross,
                'fixed_gross'   => (float) $summary->fixed_gross,
                'net'   => (float) $summary->net,
                'deduction'   => (float) $summary->deduction,
            ];
        }

        $fixed_gross = 0;
        $gross = 0;
        $net = 0;
        $deduction = 0;
        $payrollData[] =[];
        foreach ($staff as $employee) {
            $payroll = app(
                \App\Services\PayrollPreviewService::class
            )->calculateLivePayroll(
                $employee,
                $month,
                $year,
                0
            );
       
            $gross += $payroll['gross_salary'] ?? 0;
            $net += $payroll['net_salary'] ?? 0;
            $deduction += $payroll['deductions'] ?? 0;
        }

        $payrollDate = Carbon::create($year, $month, 1)->endOfMonth();

        $overallFixedGrossSalary = DB::table('egc_payroll_employee_structures')
            ->whereIn('salary_account_id', $allStaffAccountIds)
            ->where('status', 0)
            ->whereDate('effective_from', '<=', $payrollDate)
            ->where(function ($q) use ($payrollDate) {
                $q->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $payrollDate);
            })
            ->sum('gross_salary');

        return [
            'fixed_gross' => round($overallFixedGrossSalary),
            'gross'   => round($gross),
            'net'   => round($net),
            'deduction' => round($deduction),
            'staff_count' => $staff->count(),
        ];
    }

    public function payrollMonthList(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('sorting_filter', 5);
    

        $startMonth = Carbon::create(2024, 1, 1);

        $endMonth = Carbon::now()->startOfMonth();

        $months = collect();

        while ($startMonth <= $endMonth) {
            $months->push([
                'month' => $startMonth->month,
                'year' => $startMonth->year,
                'label' => $startMonth->format('F Y'),
                'month_filter' => $startMonth->format('M-Y'),

            ]);
            $startMonth->addMonth();
        }

        

        $months = $months->reverse()->values();
        $total = $months->count();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        

        $currentDate = Carbon::now();

        $currentMonth = $currentDate->month;
        $currentYear  = $currentDate->year;

        $previousDate = $currentDate->startOfMonth()->subMonth();

        $previousMonth = $previousDate->month;
        $previousYear  = $previousDate->year;

        
       

        $currentDashboard = $this->getDashboardSummary(
            $currentMonth,
            $currentYear,
            $request->only_bank_fill ?? 0
        );

        $previousDashboard = $this->getDashboardSummary(
            $previousMonth,
            $previousYear,
            $request->only_bank_fill ?? 0
        );

        $currentMonths = $months
            ->forPage($page, $perPage)
            ->values();

        $rows = [];

        foreach ($currentMonths as $item) {

            $month = $item['month'];
            $year = $item['year'];

            $process = DB::table('egc_payroll_processes')
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->where('status', 0)
                ->first();

            if ($process) {

                $payrollQuery = DB::table('egc_payroll_employee_payrolls')
                    ->where('payroll_month', $month)
                    ->where('payroll_year', $year)
                    ->where('status', 0);

                 $overallPayrolls = DB::table('egc_payroll_employee_payrolls')
                    ->where('payroll_month', $month)
                    ->where('payroll_year', $year)
                    ->where('status', 0)
                    ->get();

                $overallNetSalary = $overallPayrolls->sum('net_payable');
                $overallGrossSalary = $overallPayrolls->sum('gross_earnings');
                $overallDeduction = $overallPayrolls->sum('gross_deductions');
                $earningsSummary = [];
                $deductionsSummary = [];

                $componentSummary = DB::table('egc_payroll_employee_payroll_details')
                    ->select( 'component_name', 'component_category',  DB::raw('SUM(actual_amount) as total'))
                    ->whereIn('payroll_employee_sno',$overallPayrolls->pluck('sno'))
                    ->where('status', 0)
                    ->groupBy('component_name', 'component_category')
                    ->get();

                foreach ($componentSummary as $component) {
                    if ($component->component_category == 'earning') {
                        $earningsSummary[] = [
                            'name' => $component->component_name,
                            'total' => round($component->total)
                        ];
                    }

                    if ($component->component_category == 'deduction') {
                        $overallDeduction +=$component->total;
                        $deductionsSummary[] = [
                            'name' => $component->component_name,
                            'total' => round($component->total)
                        ];
                    }
                }

                $rows[] = [
                    'month' => $item['label'],
                    'month_int'=>$month,
                    'year_int'=>$year,
                    'month_filter' => $item['month_filter'],
                    'staff_count' => (clone $payrollQuery)->count(),
                    'earnings_summary'=>$earningsSummary,
                    'deductions_summary'=>$deductionsSummary,
                    'fixed_gross_salary' => round((clone $payrollQuery)->sum('fixed_salary')),
                    'gross_salary' => round((clone $payrollQuery)->sum('gross_earnings')),
                    'deduction' => round((clone $payrollQuery)->sum('gross_deductions')),
                    'net_salary' => round((clone $payrollQuery)->sum('net_payable')),
                    'generated' => true,
                    'process_id' => $process->sno,
                ];
            } else {
                $summary = app(\App\Services\PayrollMonthSummaryService::class)
                    ->summary(
                        $month,
                        $year,
                        $request->only_bank_fill ?? 0
                    );

                $rows[]=[
                    'month'=>$item['label'],
                    'month_int'=>$month,
                    'year_int'=>$year,
                    'month_filter'=>$item['month_filter'],
                    'staff_count'=>$summary['staff'],
                    'earnings_summary'=>($summary['earnings_summary']),
                    'deductions_summary'=>($summary['deductions_summary']),
                    'fixed_gross_salary'=>round($summary['fixed_salary']),
                    'gross_salary'=>round($summary['gross']),
                    'deduction'=>round($summary['deduction']),
                    'net_salary'=>round($summary['net']),
                    'generated'=>false,
                    'process_id'=>0

                ];
            }
        }

        return response()->json([
            'data' => $rows,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            'total' => $total,
            'current' => $currentDashboard,
            'previous' => $previousDashboard,
        ]);
    }

    public function companySummary(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        $type = $request->type; // company / normal

        $service = app(\App\Services\PayrollCompanySummaryService::class);

        return response()->json([
            'status'=>true,
            'data'=>$service->summary($month,$year,$type)
        ]);
    }


    private function getDashboardSummary($month,$year,$onlyBank)
    {

        $process = DB::table('egc_payroll_processes')
            ->where('payroll_month',$month)
            ->where('payroll_year',$year)
            ->where('status',0)
            ->exists();

        if($process){
            $summary = DB::table('egc_payroll_employee_payrolls')
                ->where('payroll_month',$month)
                ->where('payroll_year',$year)
                ->where('status',0)
                ->selectRaw('
                    count(sno) total_staff,
                    SUM(fixed_salary) fixed_salary,
                    SUM(gross_earnings) gross,
                    SUM(gross_deductions) deduction,
                    SUM(net_payable) net

                ')
                ->first();
            return [
                'total_staff'=>$summary->total_staff,
                'fixed_gross'=>round($summary->fixed_salary),
                'gross'=>round($summary->gross),
                'deduction'=>round($summary->deduction),
                'net'=>round($summary->net)
            ];
        }

        $summary = app(\App\Services\PayrollMonthSummaryService::class)->summary($month,$year,$onlyBank);

        return [
            'total_staff'=>$summary['staff'],
            'fixed_gross'=>round($summary['fixed_salary']),
            'gross'=>round($summary['gross']),
            'deduction'=>round($summary['deduction']),
            'net'=>round($summary['net'])

        ];

    }


    public function monthlyStaffPayrollList(Request $request)
    {
        $page = $request->input('page', 1);
        $perpage = (int) $request->input('sorting_filter', 25);

        $monthFilter = $request->get('month_filter', now()->format('M-Y'));

        try {
            // Parse month-year safely
            $parsedDate = Carbon::createFromFormat('!M-Y', $monthFilter);
            $month = $parsedDate->month;
            $year  = $parsedDate->year;
        } catch (\Exception $e) {
            $parsedDate = now()->startOfMonth();
            $month = $parsedDate->month;
            $year  = $parsedDate->year;
        }

        $salary_company = $request->salary_company ?? '';
        $company_fill = $request->company_fill ?? '';
        $entity_fill = $request->entity_fill ?? '';
        $department_fill = $request->department_fill ?? '';
        $division_fill = $request->division_fill ?? '';
        $job_role_fill = $request->job_role_fill ?? '';
        $date_filter = $request->dt_fill_issue_rpt ?? '';
        $from_date_filter = $request->to_dt_iss_rpt ?? '';
        $to_date_filter = $request->to_date_fillter_textbox ?? '';
        $search_filter = $request->search_filter ?? '';
        $salary_date_fill = $request->salary_date_fill ?? '';
        $salary_type_fill = $request->salary_type_fill ?? '';
        $exit_staff_fill = $request->exit_staff_fill ? 1 : 0;
        $only_bank_fill = $request->only_bank_fill ? 1 : 0;
        $only_variable_fill = $request->only_variable_fill ? 1 : 0;

        $endOfMonth = date('Y-m-t', strtotime("$year-$month-01"));
        $payrollProcess = DB::table('egc_payroll_processes')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->where('status', 0)
            ->first();
        $payslipCount = DB::table('egc_staff')->where('status', 0)->where('is_payslip', 1)->count();

        $isLiveMode = !$payrollProcess;

        $currentMonth = Carbon::create($year, $month, 1)
                        ->format('Y-m-01');

        $variableAmountIds = DB::table('egc_payroll_variable_amounts')
                // ->where('employee_sno', $staff->sno)
                ->where('status', 0)
                // ->where('is_expired', 0)
                ->whereDate('start_month','<=',$currentMonth)
                ->whereDate('end_month','>=',$currentMonth)
                ->orderByDesc('sno')
                ->pluck('employee_sno');
        $verification = null;

        if ($payrollProcess) {

            $verification = DB::table('egc_payroll_hr_verifications')
                ->where('payroll_process_sno', $payrollProcess->sno)
                ->where('status', 0)
                ->latest('sno')
                ->first();
        }
        $staffQuery = DB::table('egc_staff_salary_accounts')
            ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
            ->leftJoin('egc_company as salary_company', 'egc_staff_salary_accounts.salary_company_id', '=', 'salary_company.sno')
            ->leftJoin('egc_company', 's.company_id', '=', 'egc_company.sno')
            ->leftJoin('egc_entity', 's.entity_id', '=', 'egc_entity.sno')
            ->leftJoin('egc_department', 's.department_id', '=', 'egc_department.sno')
            ->leftJoin('egc_division', 's.division_id', '=', 'egc_division.sno')
            ->leftJoin('egc_job_role', 's.job_role_id', '=', 'egc_job_role.sno')
            ->where('s.status','!=', 2)
            ->where('egc_staff_salary_accounts.status',0)
            ->where(function ($q) use ($month, $year) {
                /*
                |--------------------------------------------------------------------------
                | ACTIVE STAFF
                |--------------------------------------------------------------------------
                */
                $q->whereIn('s.status', [0, 1]);
                /*
                |--------------------------------------------------------------------------
                | NOTICE PERIOD STAFF
                |--------------------------------------------------------------------------
                */
                $q->orWhere(function ($notice) use ($month, $year) {
                    $notice->where('s.status', 4)
                        ->whereNotNull('s.notice_end_date')
                        ->whereMonth(
                            's.notice_end_date',
                            $month
                        )
                        ->whereYear(
                            's.notice_end_date',
                            $year
                        );
                });
                /*
                |--------------------------------------------------------------------------
                | EXITED STAFF
                |--------------------------------------------------------------------------
                */
                 $q->orWhere(function ($exit) use ($month, $year) {
                    $exit->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereMonth('s.staff_last_date', $month)
                        ->whereYear('s.staff_last_date', $year)
                        ->whereNotIn('s.sno', [211,212, 94,77]) // exclude special staff
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                })

                ->orWhere(function ($special) use ($month, $year) {

                    $payrollMonth = sprintf('%04d-%02d', $year, $month);

                    $special->whereIn('s.sno', [211,212,94,77])
                        ->whereIn('s.status', [5, 6, 7])
                        ->whereNotNull('s.staff_last_date')
                        ->whereRaw(
                            "? BETWEEN DATE_FORMAT(s.date_of_joining, '%Y-%m')
                            AND DATE_FORMAT(DATE_SUB(s.staff_last_date, INTERVAL 1 MONTH), '%Y-%m')",
                            [$payrollMonth]
                        )
                        ->whereRaw(
                            'DATEDIFF(s.staff_last_date, s.date_of_joining) > ?',
                            [3]
                        );
                });
            })
            ->whereDate('s.date_of_joining', '<=', $endOfMonth)
            ->where('s.sno', '>', 0)
            // ->where('s.sno', 200)
            ->select(
                's.*',
                's.sno',
                's.staff_name',
                's.staff_id as staff_code',
                'egc_staff_salary_accounts.sno as salary_account_id',
                'egc_staff_salary_accounts.gross_salary as basic_salary',
                'egc_staff_salary_accounts.per_day_salary',
                's.company_id',
                's.entity_id',
                's.nick_name',
                's.mobile_no',
                's.staff_image',
                's.company_type',
                's.gender',
                's.salary_type',
                's.salary_date',
                's.staff_last_date',
                's.notice_start_date',
                's.notice_end_date',
                's.date_of_joining',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'salary_company.company_base_color as salary_company_base_color',
                'salary_company.company_name as salary_company_name',
                'egc_company.company_base_color',
                'egc_company.company_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name'
            );
        if ($request->search_filter != '') {
            $search = $request->search_filter;
            $staffQuery->where(function ($q) use ($search) {
                $q->where('s.staff_name', 'LIKE', "%{$search}%")
                    ->orWhere('s.staff_id', 'LIKE', "%{$search}%");
            });
        }
        if ($request->salary_company != '') {
            $staffQuery->where('egc_staff_salary_accounts.salary_company_id', $request->salary_company);
        }
        if ($request->company_fill != '') {
            $staffQuery->where('s.company_id', $request->company_fill);
        }
        
        if ($request->entity_fill != '') {
            $staffQuery->where('s.entity_id', $request->entity_fill);
        }
        if ($request->department_fill != '') {
            $staffQuery->where('s.department_id', $request->department_fill);
        }
         
        if ($exit_staff_fill == 1) {
            $staffQuery->where('s.status', '>', 2 );
        }
        
         if ($only_variable_fill == 1) {
            $staffQuery->whereIn('s.sno', $variableAmountIds);
        }
       
        if ($request->salary_type_fill != '') {
            $staffQuery->where('s.salary_type', $request->salary_type_fill);
        }
        if ($request->salary_date_fill != '') {
            $staffQuery->where('s.salary_date', $request->salary_date_fill);
        }
        if ($request->dt_fill_issue_rpt == 1 && $request->from_dt_iss_rpt != '' && $request->to_dt_iss_rpt != '') {
            $staffQuery->whereBetween(
                's.date_of_joining',
                [
                    $request->from_dt_iss_rpt,
                    $request->to_dt_iss_rpt
                ]
            );
        }
       
        $allStaffIds = (clone $staffQuery)->pluck('s.sno');
        $allStaffAccountIds = (clone $staffQuery)
        ->select('egc_staff_salary_accounts.sno as salary_account_id')
        ->pluck('salary_account_id');
        
        $staff = $staffQuery
            ->orderBy('s.date_of_joining', 'asc')
            ->paginate($perpage);
        $data = [];
        $overallNetSalary = 0;
        $overallGrossSalary = 0;
        $overallFixedGrossSalary = 0;
        $overallDeduction = 0;

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

         $payrollDate = Carbon::create($year, $month, 1)->endOfMonth();
        
    
       $overallFixedGrossSalary = DB::table('egc_payroll_employee_structures')
            ->whereIn('salary_account_id', $allStaffAccountIds)
            ->where('status', 0)
            ->whereDate('effective_from', '<=', $payrollDate)
            ->where(function ($q) use ($payrollDate) {
                $q->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $payrollDate);
            })
            ->sum('gross_salary');

        $earningComponents = [];
        $deductionComponents = [];
        $earningsSummary = [];
        $deductionsSummary = [];
        if ($isLiveMode) {
            $earningComponents = [];
            $deductionComponents = [];
            $allStaffData =  DB::table('egc_staff_salary_accounts')
                ->join('egc_staff as s','s.sno','=','egc_staff_salary_accounts.staff_id')
                ->select(
                    's.*',
                     'egc_staff_salary_accounts.sno as salary_account_id',
                    'egc_staff_salary_accounts.gross_salary as basic_salary',
                    'egc_staff_salary_accounts.per_day_salary',
                )
                ->where('egc_staff_salary_accounts.status',0)
                // ->whereIn('s.sno', $allStaffIds)
                ->whereIn('egc_staff_salary_accounts.sno', $allStaffAccountIds)
                ->get();
                
            foreach ($allStaffData as $staffItem) {

                $payroll = app(
                    \App\Services\PayrollPreviewService::class
                )->calculateLivePayroll(
                    $staffItem,
                    $month,
                    $year,
                    $only_bank_fill
                );
                //    return $payroll;
                $overallNetSalary += round($payroll['net_salary']) ?? 0;
                $overallGrossSalary += $payroll['gross_salary'] ?? 0;
                $overallDeduction += round($payroll['deductions']) ?? 0;
                foreach ($payroll['components'] as $component) {
                    $name = $component['component'] ?? 'Unknown';
                    $amount = (float)($component['amount'] ?? 0);
                    if (($component['type'] ?? '') === 'earning') {
                        if (!isset($earningComponents[$name])) {
                            $earningComponents[$name] = 0;
                        }
                        $earningComponents[$name] += $amount;
                    }
                    if (($component['type'] ?? '') === 'deduction') {
                        if (!isset($deductionComponents[$name])) {
                            $deductionComponents[$name] = 0;
                        }
                        $deductionComponents[$name] += $amount;
                    }
                }
            }
            $earningsSummary = [];
            $deductionsSummary = [];
            foreach ($earningComponents as $name => $amount) {

                $earningsSummary[] = [
                    'name' => $name,
                    'total' => round($amount)
                ];
            }

            foreach ($deductionComponents as $name => $amount) {

                $deductionsSummary[] = [
                    'name' => $name,
                    'total' => round($amount)
                ];
            }
            foreach ($staff as $item) {
                $payroll = app(
                    \App\Services\PayrollPreviewService::class
                )->calculateLivePayroll(
                    $item,
                    $month,
                    $year,
                    $only_bank_fill
                );
                
                if ($item->company_type == 1) {
                    $relativePath =
                        'staff_images/Management/' .
                        $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }
                $fullPath = public_path($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;
                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }

                $components =collect($payroll['components']);
                $onduty =(float)$components->where('code', 'ONDUTY')->sum('amount');
                $variable =(float)$components->where('code', 'VARIABLE')->sum('amount');
                $incentive =(float)$components->where('code', 'INCENTIVE')->sum('amount');
                $pf =(float)$components->where('code', 'PF')->sum('amount');
                $esi =(float)$components->where('code', 'ESI')->sum('amount');
                $pt =(float)$components->where('code', 'PT')->sum('amount');
                $tax =(float)$components->where('code', 'TAX')->sum('amount');
                $employerPf =(float)$components->where('code', 'EMPLOYER_PF')->sum('amount');
                $employerEsi =(float)$components->where('code', 'EMPLOYER_ESI')->sum('amount');
                $lopAmount =(float)$components->where('code', 'LOP')->sum('amount');

                $data[] = [
                    'sno' => $item->sno,
                    'staff_status' => $item->status,
                    'name' => $item->staff_name,
                    'staff_last_date' => $item->staff_last_date,
                    'notice_start_date' => $item->notice_start_date,
                    'notice_end_date' => $item->notice_end_date,
                    'basic_salary' => $item->basic_salary,
                    'company_base_color' =>
                    $item->company_base_color,
                    'is_saved' => false,
                    'isStaffImage' => $isStaffImage,
                    'data' => [
                        'staff_image' =>$item->staff_image,
                        'company_type' =>$item->company_type,
                        'company_id' =>$item->company_id,
                        'entity_id' => $item->entity_id,
                        'gender' =>$item->gender,
                        'staff_code' =>$item->staff_code,
                        'nick_name' => $item->nick_name,
                        'company_name' =>$item->company_name,
                        'salary_company_name' => $item->salary_company_name,
                        'salary_company_base_color' => $item->salary_company_base_color,
                        'department_name' =>$item->department_name,
                        'job_role_name' =>$item->job_role_name,
                        'date_of_joining' => $item->date_of_joining,
                        'salary_type' => $item->salary_type,
                        'salary_date' => $item->salary_date,
                        'basic_salary' =>round($payroll['basic_salary']),
                    ],
                    'dataParoll' => [
                        'components' => $payroll['components'] ?? [],
                        'rules' => $payroll['rules'] ?? [],
                        'earnings' => round($payroll['earnings']),
                        'deductions' => round($payroll['deductions']),
                        'gross_salary' => round($payroll['gross_salary']),
                        'net' => round($payroll['net_salary']),
                        'employer_contribution' => round($payroll['employer_contribution']),
                        'lop_days' => round($payroll['lop_days'], 2),
                        'onduty' => round($onduty),
                        'variable' => round($variable),
                        'incentive' => round($incentive),
                        'pf' => round($pf),
                        'esi' => round($esi),
                        'pt' => round($pt),
                        'tax' => round($tax),
                        'lopAmount' => round($lopAmount),
                        'employerPf' => round($employerPf),
                        'employerEsi' => round($employerEsi),
                    ],
                    'present_days' => round($payroll['present_days'], 2),
                    'absent_days' => round($payroll['absent_days'], 2),
                    'late_count' => round($payroll['late_count'], 2),
                    'lop_amount' => round($payroll['lop_amount']),
                    'pf_amount' => round($payroll['pf_amount'] ?? 0),
                    'esi_amount' => round($payroll['esi_amount'] ?? 0),
                    'net_salary' => round($payroll['net_salary']),
                ];
                
            }
        } else {
            $earningComponents = [];
            $deductionComponents = [];
            $employeeIds = $staff->pluck('sno');
            $processedPayrolls = DB::table('egc_payroll_employee_payrolls as ep')
                ->whereIn('ep.staff_id', $employeeIds)
                ->where('ep.payroll_month', $month)
                ->where('ep.payroll_year', $year)
                ->where('ep.status', 0)
                ->get()
                ->keyBy('staff_id');

            $overallPayrolls = DB::table('egc_payroll_employee_payrolls')
                ->whereIn('staff_id', $allStaffIds)
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->where('status', 0)
                ->get();
            $overallNetSalary =
                $overallPayrolls->sum('net_payable');

            $overallGrossSalary =
                $overallPayrolls->sum('gross_earnings');

            // $overallDeduction = $overallPayrolls->sum('gross_deductions');


            $componentSummary = DB::table('egc_payroll_employee_payroll_details')
                ->select(
                    'component_name',
                    'component_category',
                    DB::raw('SUM(actual_amount) as total')
                )
                ->whereIn(
                    'payroll_employee_sno',
                    $overallPayrolls->pluck('sno')
                )
                ->where('status', 0)
                ->groupBy(
                    'component_name',
                    'component_category'
                )
                ->get();

            

            foreach ($componentSummary as $component) {
                if ($component->component_category == 'earning') {
                    $earningsSummary[] = [
                        'name' => $component->component_name,
                        'total' => round($component->total)
                    ];
                }

                if ($component->component_category == 'deduction') {
                    $overallDeduction +=$component->total;
                    $deductionsSummary[] = [
                        'name' => $component->component_name,
                        'total' => round($component->total)
                    ];
                }
            }


            foreach ($staff as $item) {
                $payroll = $processedPayrolls[$item->sno] ?? null;
                if ($item->company_type == 1) {
                    $relativePath = 'staff_images/Management/' . $item->staff_image;
                } else {
                    $relativePath = 'staff_images/Buisness/' . $item->company_id . '/' . $item->entity_id . '/' . $item->staff_image;
                }

                $fullPath = public_path($relativePath);
                $isStaffImage = ($item->staff_image && file_exists($fullPath)) ? 1 : 0;

                if ($item->company_type == 1) {
                    $item->company_name = $general_setting->title;
                    $item->company_base_color = '#ab2b22';
                }
                $components = DB::table('egc_payroll_employee_payroll_details')
                    ->where('egc_payroll_employee_payroll_details.payroll_employee_sno', $payroll->sno ?? 0)
                    ->join('egc_payroll_components','egc_payroll_components.sno','=','egc_payroll_employee_payroll_details.payroll_component_sno')
                    ->where('egc_payroll_employee_payroll_details.status', 0)
                    ->select(
                        'egc_payroll_employee_payroll_details.*',
                        'egc_payroll_components.component_code',
                    )
                    ->get()
                    ->map(function ($item) {
                        return [
                            'sno' => $item->payroll_component_sno,
                            'component' => $item->component_name,
                            'code' => $item->component_code,
                            'type' => $item->component_category,
                            'calculation_type' => $item->calculation_type,
                            'percentage' => (float)$item->percentage_value,
                            'amount' => round($item->actual_amount, 2)
                        ];
                    })
                    ->toArray();

                $lateCount = $this->getLateCount(
                    $item->sno,
                    $month,
                    $year
                );

                $rules = [];

                $pfAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%PF%')
                    ->sum('actual_amount');

                $esiAmount = DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno', $payroll->sno ?? 0)
                    ->where('component_name', 'LIKE', '%ESI%')
                    ->sum('actual_amount');
                $payrollSlipData = DB::table('egc_payroll_payslips')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_payslips.payroll_process_sno')
                        ->where( 'egc_payroll_payslips.employee_sno', $item->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->select('egc_payroll_payslips.sno')
                        ->first();
                $payrollAttendance = DB::table('egc_payroll_attendance_summaries')
                        ->join('egc_payroll_processes','egc_payroll_processes.sno', '=',  'egc_payroll_attendance_summaries.payroll_process_sno')
                        ->where( 'egc_payroll_attendance_summaries.employee_sno', $item->sno)
                        ->where('egc_payroll_processes.payroll_month', $month)
                        ->where( 'egc_payroll_processes.payroll_year', $year)
                        ->first();

                $details =DB::table('egc_payroll_employee_payroll_details')
                    ->where('payroll_employee_sno',$payroll->sno)
                    ->where('status', 0)
                    ->get();

                $componentSum = function ($code) use ($details) {
                    return (float)$details->where('component_code',$code)->sum('actual_amount');
                };

                    $onduty = $componentSum('ONDUTY');
                    $incentive = $componentSum('INCENTIVE');
                    $variable = $componentSum('VARIABLE');
                    $lopAmount =$payroll->lop_amount ?? 0;
                    $pf = $componentSum('PF');
                    $esi = $componentSum('ESI');
                    $pt = $componentSum('PT');
                    $tax = $componentSum('TAX');
                    $employerPf = $componentSum('EMPLOYER_PF');
                    $employerEsi = $componentSum('EMPLOYER_ESI');

                $data[] = [
                    'sno' => $item->sno,
                    'entry_id' => $payrollSlipData->sno ?? 0,
                    'entry_encrypt' => encrypt($payrollSlipData->sno ?? 0),
                    'name' => $item->staff_name,
                    'company_base_color' => $item->company_base_color,
                    'is_saved' => true,
                    'isStaffImage' => $isStaffImage,
                    'staff_last_date' => $item->staff_last_date,
                    'notice_start_date' => $item->notice_start_date,
                    'notice_end_date' => $item->notice_end_date,
                    'staff_status' => $item->status,
                    'data' => [
                        'staff_image' => $item->staff_image,
                        'company_type' => $item->company_type,
                        'company_id' => $item->company_id,
                        'entity_id' => $item->entity_id,
                        'gender' => $item->gender,
                        'staff_code' => $item->staff_code,
                        'nick_name' => $item->nick_name,
                        'company_name' => $item->company_name,
                        'salary_company_name' => $item->salary_company_name,
                        'salary_company_base_color' => $item->salary_company_base_color,
                        'department_name' => $item->department_name,
                        'job_role_name' => $item->job_role_name,
                        'date_of_joining' => $item->date_of_joining,
                        'salary_type' => $item->salary_type,
                        'salary_date' => $item->salary_date,
                        'basic_salary' => round($item->basic_salary ?? 0),
                    ],
                    'dataParoll' => [
                        'components' => $components,
                        'rules' => $rules,
                        'earnings' => round($payroll->gross_earnings ?? 0),
                        'deductions' => round($payroll->gross_deductions ?? 0),
                        'gross_salary' => round($payroll->gross_earnings ?? 0),
                        'net' => round($payroll->net_payable ?? 0),
                        'employer_contribution' => round($payroll->employer_contribution ?? 0),
                        'lop_days' => round($payroll->lop_days ?? 0, 2),
                        'onduty' => round($onduty),
                        'variable' => round($variable),
                        'incentive' => round($incentive),
                        'pf' => round($pf),
                        'esi' => round($esi),
                        'pt' => round($pt),
                        'tax' => round($tax),
                        'lopAmount' => round($lopAmount),
                        'employerPf' => round($employerPf),
                        'employerEsi' => round($employerEsi),

                    ],
                    'present_days' => round($payroll->present_days ?? 0),
                    'absent_days' => round($payroll->absent_days ?? 0),
                    'late_count' => round($payroll->late_count ?? 0),
                    'lop_amount' => round($payroll->lop_amount ?? 0),
                    'pf_amount' => round($pfAmount, 2),
                    'esi_amount' => round($esiAmount, 2),
                    'net_salary' => round($payroll->net_payable ?? 0),
                ];
            }
        }

        $previousDate = Carbon::create($year, $month, 1)->subMonth();
        $previousMonth = $previousDate->month;
        $previousYear  = $previousDate->year;

        $previousGross = 0;
        $previousDeduction = 0;
        $previousNet = 0;

        $previousPayroll = $this->getMonthlySalarySummary($previousMonth, $previousYear ,$isLiveMode);
        
        $previousGross = round($previousPayroll['gross']);
        $previousDeduction = round($previousPayroll['net']);
        $previousNet =  round($previousPayroll['deduction']);

       
        if ($request->ajax()) {
            return response()->json([
                'mode' => $isLiveMode ? 'live' : 'processed',
                'payroll_process_id' => $payrollProcess ? $payrollProcess->sno : null,
                'is_payroll_saved_id' => !$isLiveMode,
                'is_payroll_saved' => !$isLiveMode,
                'data' => $data,
                'total' => $staff->total(),
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'per_page' => $staff->perPage(),
                'total_net_salary' => round($overallNetSalary),
                'total_gross_salary' => round($overallGrossSalary),
                'total_fixed_gross_salary' => round($overallFixedGrossSalary),
                'total_deduction' => round($overallDeduction),
                'earnings_summary' => $earningsSummary ?? [],
                'deductions_summary' => $deductionsSummary ?? [],
                'current' => [
                    'gross' => round($overallGrossSalary),
                    'deduction' => round($overallDeduction),
                    'net' => round($overallNetSalary),
                ],
                'previous' => [
                    'gross' => round($previousGross),
                    'deduction' => round($previousDeduction),
                    'net' => round($previousNet),
                ],
                'workflow' => [
                    'generated' => !$isLiveMode,
                    'hr_verified' => !empty($verification),
                    'frozen' => $payrollProcess  ? ($payrollProcess->payroll_freeze == 1)  : false,
                    'process_status' => $payrollProcess->process_status ?? 'draft',
                    'payroll_process_id' => $payrollProcess->sno ?? null
                ]
            ]);
        }
          $company_list = CompanyModel::where('status', 0)->get();
           
        return view(
            'content.hr_management.hr_general.manage_payroll.payroll_report_list',
            [
                'mode' => $isLiveMode ? 'live' : 'processed',
                'perpage' => $perpage,
                'search_filter' => $search_filter ?? '',
                'payslipCount' => $payslipCount ?? 0,
                'month' => $month,
                'company_list' => $company_list,
                'year' => $year,
            ]
        );
    }


    public function GetDashboardMonthSummary(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('sorting_filter', 5);
    
        
       $monthFilter = $request->get('month_filter', now()->format('M-Y'));

        try {
            // Parse selected month
            $parsedDate = Carbon::createFromFormat('!M-Y', $monthFilter);
        } catch (\Exception $e) {
            $parsedDate = now()->startOfMonth();
        }

        $currentMonth = $parsedDate->month;
        $currentYear  = $parsedDate->year;

        $currentDate = Carbon::today();

$isCurrentMonth =
    $currentMonth == $currentDate->month &&
    $currentYear == $currentDate->year;

        $compareTillDate = null;

if ($isCurrentMonth) {

    $compareTillDate = $currentDate->day;

}

        // Previous month based on selected payroll month
        $previousDate = $parsedDate->copy()->subMonth();

        $previousMonth = $previousDate->month;
        $previousYear  = $previousDate->year;

        
        $currentDashboard = $this->getDashboardSummary(
            $currentMonth,
            $currentYear,
            $request->only_bank_fill ?? 0
        );

        $previousDashboard = $this->getDashboardSummary(
            $previousMonth,
            $previousYear,
            $request->only_bank_fill ?? 0
        );

        return response()->json([
            'current' => $currentDashboard,
            'previous' => $previousDashboard,
        ]);
    }
}

Saturday 12:43 AM
Pasted code(41).php
File
Pasted code (2)(9).php
File
here in payroll for staff joinine data or exit last_date or notice_end_date in the $month , $year if month is 30 day working correct but 31 days lop day and lop amount and net is wrong so what can i do for production

I reviewed your payroll calculation logic. The issue is not actually because the month has 31 days. The problem is that your code is mixing a 30-day payroll policy with calendar dates, which causes one extra day to become an LOP when an employee joins or exits in a 31-day month.

From your service:

You correctly set payroll start/end based on joining, notice, or exit date.
But later you hardcode the payroll month end as 30th day:
$monthStart = Carbon::create($year, $month, 1)->startOfMonth();
$monthEnd   = Carbon::create($year, $month, 1)->startOfMonth()->addDays(29);

This means every month is treated as 30 payroll days, even when attendance is collected until the real month end.

Then you calculate:

$nonAttendDays += $payrollEnd->diffInDays($monthEnd);

and later

$lopDays = max(0, $absentDays - $allowedLeave) + $nonAttendDays;

Example of the bug

Suppose:

Salary = ₹30,000
Per day = ₹1,000 (30-day payroll)
Employee exits on 30 July
July has 31 days

Current logic:

payrollEnd = 30 Jul
monthEnd = 30 Jul
nonAttendDays = 0

This is correct.

But if attendance or date calculations still include 31 July somewhere (attendance range, earning days, etc.), that extra calendar day can be interpreted as an absence, causing:

LOP = 1
Net Salary = Wrong
Bigger production issue

Right now the system mixes two different concepts:

Attendance is based on actual calendar dates (28/29/30/31).
Salary calculation is based on 30 payroll days.

Those two should never be mixed.

Production-standard approach

Instead of counting "missing calendar dates", calculate only the employee's payable period.

For each employee:

Payroll Period

Start =
max(
    first day of month,
    joining date
)

End =
min(
    last working date,
    notice end date,
    month end
)

Then compute:

Payroll Days =
inclusiveDays(start,end)

Examples:

Joined on 16th
Payroll days

16-30

=15 days

Salary

15 × perDaySalary
Exit on 20th
Payroll Days

1-20

=20

Salary

20 × perDaySalary
Full month
Payroll Days

30

Salary

30 × perDaySalary

No artificial LOP.

Do not use nonAttendDays

This variable is the root cause.

Instead of

$lopDays =
max(0,$absentDays-$allowedLeave)
+
$nonAttendDays;

use

$workingPayrollDays =
$payrollStart->diffInDays($payrollEnd) + 1;

$expectedPayrollDays = 30;

$joiningExitLoss =
max(0, $expectedPayrollDays - $workingPayrollDays);

$lopDays =
max(0,$absentDays-$allowedLeave)
+
$joiningExitLoss;

Now:

Employee joined on 16th

workingPayrollDays = 15

joiningExitLoss = 15

LOP = Attendance LOP + 15

Employee exited on 30th

workingPayrollDays = 30

joiningExitLoss = 0

Employee exited on 31st

workingPayrollDays = 31

joiningExitLoss = max(0,30-31)=0

No extra LOP.

Even better for production

Most payroll software (SAP, Oracle, Zoho Payroll, GreytHR, Darwinbox, etc.) does not add joining/exit days into LOP.

Instead, they keep them separate:

Absent LOP
Joining Proration
Exit Proration
Late Deduction
Leave Deduction

Then

Net Salary =
Gross
-
Attendance LOP
-
Late LOP
-
Other Deductions

Joining/exit affects prorated earnings, not LOP.

This gives a clean payslip:

Gross Salary      30,000

Prorated Earnings 20,000

LOP               2 days

PF

ESI

Net

instead of showing:

LOP = 12 days

when 10 of those days were simply before joining.

My recommendation for production

I would refactor this calculation to:

Separate attendance LOP from joining/exit proration.
Remove $nonAttendDays entirely.
Prorate earnings based on payable days.
Calculate LOP only from attendance (absent days after allowed leave).
Continue using your 30-day salary policy, regardless of whether the calendar month has 28, 29, 30, or 31 days.

That approach is much easier to audit, matches enterprise payroll systems, and avoids the 31-day edge-case you're seeing.

Today 1:36 AM
Pasted code(42).php
File
public function GetDashboardMonthSummary(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('sorting_filter', 5);
    
        
       $monthFilter = $request->get('month_filter', now()->format('M-Y'));

        try {
            // Parse selected month
            $parsedDate = Carbon::createFromFormat('!M-Y', $monthFilter);
        } catch (\Exception $e) {
            $parsedDate = now()->startOfMonth();
        }

        $currentMonth = $parsedDate->month;
        $currentYear  = $parsedDate->year;

        // Previous month based on selected payroll month
        $previousDate = $parsedDate->copy()->subMonth();

        $previousMonth = $previousDate->month;
        $previousYear  = $previousDate->year;

        
        $currentDashboard = $this->getDashboardSummary(
            $currentMonth,
            $currentYear,
            $request->only_bank_fill ?? 0
        );

        $previousDashboard = $this->getDashboardSummary(
            $previousMonth,
            $previousYear,
            $request->only_bank_fill ?? 0
        );

        return response()->json([
            'current' => $currentDashboard,
            'previous' => $previousDashboard,
        ]);
    }<script>
<div class="row payroll-dashboard">
            <!-- Workforce -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="payroll-card warning  workforce-card border border-warning">
                    <div class="card-top-border bg-success"></div>
                    <div class="card-body-custom">
                        <div class="d-flex justify-content-center mt-2"><span class="text-center fw-bold fs-7 py-1 text-black" id="dash_employee_month">{{date('M-Y')}}</span></div>
                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">Total Employees</div>
                                <div class="trend-badge" id="staffVariationBadge">
                                    <i class="mdi mdi-trending-up"></i>
                                    <span id="staffVariation">0%</span>
                                </div>
                            </div>
                            <div class="card-value card-value-purple">
                                <span id="totalEmployees">0</span>
                            </div>
                            
                            <hr>
                            <div class="mini-row">
                                <span>Previous Month</span>
                                <strong id="previousstaff">0</strong>
                            </div>
                            <div class="mini-row">
                                <span> Difference</span>
                                <strong id="staffDifference">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Gross Payroll -->
            <div class="col-xl-3 col-lg-6 col-md-6 position-relative attendance-badge">
                <div class="payroll-card info gross-card border border-info">
                    <div class="card-top-border primary"></div>
                    <div class="card-body-custom">
                        <div class="d-flex justify-content-center mt-2"><span class="text-center fw-bold fs-7 py-1 text-black" id="dash_gross_month">{{date('M-Y')}}</span></div>
                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-center">
                                
                                    <div class="fw-semibold">Gross Payroll</div>
                                    <div class="trend-badge" id="grossVariationBadge">
                                        <i class="mdi mdi-trending-up"></i>
                                        <span id="grossVariation">0%</span>
                                    </div>
                                
                            </div>
                            
                            <div class="card-value card-value-primary " id="totalGrossSalary">₹0</div>
                            
                            <hr>
                            <div class="mini-row">
                                <span>Previous Month</span>
                                <strong id="previousgross">₹0</strong>
                            </div>

                            <div class="mini-row">
                                <span>Difference</span>
                                <strong id="grossDifference">₹0</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
            </div>
            <!-- Payroll Deductions -->
            <div class="col-xl-3 col-lg-6 col-md-6 position-relative attendance-badge">
                <div class="payroll-card danger deduction-card border border-danger">
                    <div class="card-top-border bg-danger"></div>
                    <div class="card-body-custom">
                        <div class="d-flex justify-content-center mt-2"><span class="text-center fw-bold fs-7 py-1 text-black" id="dash_deduction_month">{{date('M-Y')}}</span></div>
                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">Deductions</div>
                                <div class="trend-badge" id="deductionVariationBadge">
                                    <i class="mdi mdi-trending-down"></i>
                                    <span id="deductionVariation">0%</span>
                                </div>
                            </div>
                            
                            <div class="card-value card-value-danger" id="totalDeduction"> ₹0</div>
                            
                            <hr>
                            <div class="mini-row">
                                <span>Previous Month</span>
                                <strong id="previousdeduction">₹0</strong>
                            </div>

                            <div class="mini-row">
                                <span> Difference</span>
                                <strong id="deductionDifference"> ₹0</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <!-- Net Payroll -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="payroll-card success net-card border border-success">
                    <div class="card-top-border bg-purple"></div>
                    <div class="card-body-custom">
                        <div class="d-flex justify-content-center mt-2"><span class="text-center fw-bold fs-7 py-1 text-black" id="dash_net_month">{{date('M-Y')}}</span></div>
                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-center">
                              
                                <div class="fw-semibold">Net Payroll</div>
                                <div class="trend-badge" id="netVariationBadge">
                                    <i class="mdi mdi-trending-up"></i>
                                    <span id="netVariation">0%</span>
                                </div>
                            </div>
                            
                            <div class="card-value card-value-success" id="totalNetSalary">₹0</div>
                            
                            
                            <hr>
                            <div class="mini-row">
                                <span>Previous Month</span>
                                <strong id="previousnet">₹0</strong>
                            </div>
                            <div class="mini-row">
                                <span>Difference</span>
                                <strong id="netDifference">₹0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>private function getDashboardSummary($month,$year,$onlyBank) 
    {

        $process = DB::table('egc_payroll_processes')
            ->where('payroll_month',$month)
            ->where('payroll_year',$year)
            ->where('status',0)
            ->exists();

        if($process){
            $summary = DB::table('egc_payroll_employee_payrolls')
                ->where('payroll_month',$month)
                ->where('payroll_year',$year)
                ->where('status',0)
                ->selectRaw('
                    count(sno) total_staff,
                    SUM(fixed_salary) fixed_salary,
                    SUM(gross_earnings) gross,
                    SUM(gross_deductions) deduction,
                    SUM(net_payable) net

                ')
                ->first();
            return [
                'total_staff'=>$summary->total_staff,
                'fixed_gross'=>round($summary->fixed_salary),
                'gross'=>round($summary->gross),
                'deduction'=>round($summary->deduction),
                'net'=>round($summary->net)
            ];
        }

        $summary = app(\App\Services\PayrollMonthSummaryService::class)->summary($month,$year,$onlyBank);

        return [
            'total_staff'=>$summary['staff'],
            'fixed_gross'=>round($summary['fixed_salary']),
            'gross'=>round($summary['gross']),
            'deduction'=>round($summary['deduction']),
            'net'=>round($summary['net'])

        ];

    } 
    
    let isLoadingDashboard = false;
    let abortControllerDashBoard = new AbortController();

    function dashboardChange() {

        const monthFilter = $('#dash_month_picker').val();

        $('#dash_filter_month').text(monthFilter);
        $('#dash_gross_month').text(monthFilter);
        $('#dash_deduction_month').text(monthFilter);
        $('#dash_net_month').text(monthFilter);

        const url = /hr_general/payroll/month-dahsboard?month_filter=${monthFilter};

        // Show skeleton loader and clear old data before fetching new data
        isLoadingDashboard = true;
        let totalSalary = 0;
        let totalDeduction = 0;
       

        $('#totalEmployees').text(0);
        $('#staffVariation').text('0%');
        $('#previousstaff').text(0);
        $('#staffDifference').text(0);

        $('#totalNetSalary').text("₹0");
        $('#netVariation').text("0%");
        $('#previousnet').text("₹0");
        $('#netDifference').text("₹0");

        $('#deductionVariation').text("0%");
        $('#totalDeduction').text("₹0");
        $('#previousdeduction').text("₹0");
        $('#deductionDifference').text("₹0");

        $('#totalGrossSalary').text("₹0");
        $('#grossVariation').text("0%");
        $('#previousgross').text("₹0");
        $('#grossDifference').text("₹0");

        $('#totalFixedGrossSalary').text("₹0");

        // $('#totalFixedGrossSalary').text("₹0");
        

        if (abortControllerDashBoard.signal) {
            abortControllerDashBoard.abort(); // Abort the previous request
        }
        abortControllerDashBoard = new AbortController();

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: abortControllerDashBoard.signal
            })
            .then(res => res.json())
            .then(res => {

                animateValue(document.getElementById("totalEmployees"), 0, res.current.total_staff,false);
                animateValue( document.getElementById("totalGrossSalary"), 0, res.current.fixed_gross, true );
                // animateValue( document.getElementById("totalFixedGrossSalary"), 0, res.total_fixed_gross_salary, true);
                animateValue(document.getElementById("totalDeduction"), 0,res.current.deduction,true );
                animateValue(document.getElementById("totalNetSalary"), 0, res.current.net, true );

                updateTrendCard( "staff", res.current.total_staff, res.previous.total_staff ,'count');
                updateTrendCard( "gross", res.current.fixed_gross, res.previous.fixed_gross ,'amount');
                updateTrendCard( "gross", res.current.fixed_gross, res.previous.fixed_gross ,'amount');
                updateTrendCard("deduction", res.current.deduction, res.previous.deduction ,'amount');
                updateTrendCard("net",res.current.net,res.previous.net ,'amount');

              
                isLoadingDashboard = false;
            })
            .catch(error => {
                if (error.name !== 'AbortError') { // Only handle abort error
                    console.error('Error loading data:', error);
                }
                isLoadingDashboard = false;
            });
    }
</script>
while month_filter is not current month is above code is correct if month_filter is current month means i want deduction and net i want difference and perviuos data come upto current date like priouvious

Yes. For current month, your dashboard comparison is not correct.

Right now you compare:

Current Month (1 → Today)
vs
Previous Month (1 → End of Month)

Example (today = Aug 3)

Current
Aug 1 - Aug 3

Previous
Jul 1 - Jul 31

Naturally,

Gross
Deduction
Net

will always be much smaller than previous month, so the difference and percentage become misleading.

Production Standard

When selected month is the current month, compare only the same number of days in the previous month.

Example

Today = Aug 3

Current
Aug 1 → Aug 3

Previous
Jul 1 → Jul 3

If today is Aug 18

Current
Aug 1 → Aug 18

Previous
Jul 1 → Jul 18

If today is Aug 31

Current
Aug 1 → Aug 31

Previous
Jul 1 → Jul 31

This is how most HRMS dashboards work.

Controller

In your controller determine whether the selected month is the current month.

$isCurrentMonth =
    $currentMonth == now()->month &&
    $currentYear == now()->year;

$compareTillDay = null;

if ($isCurrentMonth) {
    $compareTillDay = now()->day;
}

Then pass it.

$currentDashboard = $this->getDashboardSummary(
    $currentMonth,
    $currentYear,
    $request->only_bank_fill ?? 0,
    $compareTillDay
);

$previousDashboard = $this->getDashboardSummary(
    $previousMonth,
    $previousYear,
    $request->only_bank_fill ?? 0,
    $compareTillDay
);
Update method
private function getDashboardSummary(
    $month,
    $year,
    $onlyBank,
    $uptoDay = null
)
If payroll already processed

Instead of

->where('payroll_month',$month)
->where('payroll_year',$year)

also add

if ($uptoDay) {

    $endDate = Carbon::create(
        $year,
        $month,
        min(
            $uptoDay,
            Carbon::create($year,$month,1)->daysInMonth
        )
    );

    ->whereDate('created_at','<=',$endDate);
}

If your processed payroll is monthly (one record per employee), then don't use created_at because processed payroll represents the whole month. In that case, leave processed payroll unchanged.

Live Payroll

Your PayrollMonthSummaryService::summary() should accept one more parameter.

summary(
    $month,
    $year,
    $onlyBank,
    $uptoDay = null
)

Then while calculating payroll, if

$uptoDay != null

change

$payrollEnd = Carbon::create($year,$month,1)
            ->endOfMonth();

to

$payrollEnd = Carbon::create(
    $year,
    $month,
    min(
        $uptoDay,
        Carbon::create($year,$month,1)->daysInMonth
    )
);

This way,

Current Month

Attendance
1 → Today

Deduction
1 → Today

Net
1 → Today

Previous Month

Attendance
1 → Same Day

Deduction
1 → Same Day

Net
1 → Same Day
Final Result
Selected Month	Current	Previous
May-2026	Full Month	Full Month
Jun-2026	Full Month	Full Month
Jul-2026	Full Month	Full Month
Current Month (Aug-2026)	1 → Today	1 → Same Day Last Month

This is the production approach used in dashboards because it compares equivalent time periods rather than a partial month against a completed month.

no 
i expect
Current
Aug 1 - Aug 3

Previous
Jul 1 - Jul 3

That's exactly what I recommend as well.

For the current month only, you should compare Month-to-Date (MTD) with the same period of the previous month.

Example:

Today = 03-Aug-2026

Current	Previous
01-Aug → 03-Aug	01-Jul → 03-Jul

If today is 18-Aug

Current	Previous
01-Aug → 18-Aug	01-Jul → 18-Jul

This gives a fair comparison.

Change your controller
$currentDate = Carbon::today();

$isCurrentMonth =
    $currentMonth == $currentDate->month &&
    $currentYear == $currentDate->year;

$compareTillDate = null;

if ($isCurrentMonth) {

    $compareTillDate = $currentDate->day;

}

Then

$currentDashboard = $this->getDashboardSummary(
    $currentMonth,
    $currentYear,
    $request->only_bank_fill ?? 0,
    $compareTillDate
);

$previousDashboard = $this->getDashboardSummary(
    $previousMonth,
    $previousYear,
    $request->only_bank_fill ?? 0,
    $compareTillDate
);
Update method
private function getDashboardSummary(
    $month,
    $year,
    $onlyBank,
    $uptoDay = null
)
Inside PayrollMonthSummaryService

Instead of calculating the full month, determine the payroll end date like this:

$payrollStart = Carbon::create($year, $month, 1);

if ($uptoDay) {

    $payrollEnd = Carbon::create(
        $year,
        $month,
        min($uptoDay, $payrollStart->daysInMonth)
    );

} else {

    $payrollEnd = $payrollStart->copy()->endOfMonth();

}

Now every attendance query, payroll calculation, deduction, and net salary should use:

whereBetween('date', [
    $payrollStart,
    $payrollEnd
]);

instead of the full month.

Previous month

If the selected month is August and today is Aug 3:

$payrollStart = Carbon::create(2026, 7, 1);

$payrollEnd = Carbon::create(
    2026,
    7,
    min(3, Carbon::create(2026,7,1)->daysInMonth)
);

Result:

01-Jul → 03-Jul
One important thing

This logic should only be applied when payroll is not yet processed (live mode).

Once payroll is processed for a month, always return the full month's saved values from egc_payroll_employee_payrolls, because those records represent finalized payroll for the entire month.

So the flow should be:

Current month (Live) → 01 Current Month → Today
Previous month (for comparison) → 01 Previous Month → Same Day
Any completed month → Full month vs Full previous month

This is the approach used in production dashboards and gives users a true month-to-date comparison.
