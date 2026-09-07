<?php

namespace App\Http\Controllers\hr_management\hr_enroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialMediaModel;
use App\Models\CourseTagModel;
use App\Models\SkillTagModel;
use App\Models\DepartmentModel;
use App\Models\QualificationModel;
use App\Models\LanguageModel;
use App\Models\HobbyModel;
use App\Models\RelationshipModel;
use App\Models\StaffTimestampModel;
use App\Models\StaffFamilyModel;
use App\Models\JobpositionModel;
use App\Models\SourceModel;
use App\Models\CompanyModel;
use App\Models\ShiftTimeModel;
use App\Models\DocumentModel;
use App\Models\DocumentCheckListModel;
use App\Models\StaffAttachmentModel;
use App\Models\StaffModel;
use App\Models\UserRoleModel;
use App\Models\ManageEntityModel;
use App\Models\User;
use Carbon\Carbon;
use App\Models\WhatsappTemplateLogModel;
use App\Models\CredentialModel;
use App\Models\StaffWorkInfoModel;
use App\Models\StaffEducationInfoModel;
use App\Models\StaffCredentialModel;
use App\Models\HrQuestionnaireModel;
use App\Models\HrQuestionDependsModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\SubErpWebhookModel;
use App\Models\WebhookDispatchModel;
use App\Jobs\SendWebhookJob;
use App\Models\WebhookDispatchAttemptModel;
use App\Events\WebhookDispatchedEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\EGCMail;
use App\Models\EmailTemplateModel;
use Illuminate\Support\Facades\Mail;
use App\Models\StaffBankDetailsModel;
use App\Models\StaffPayrollProfileModel;
use App\Models\StaffStatutoryDetailsModel;
use App\Models\StaffSalaryComponentModel;
use App\Models\StaffExistReasonModel;
use App\Models\StaffTransferLogModel;
use App\Models\StaffSalaryAccountModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

use App\Models\PayrollStaffStructureModel;
use App\Models\PayrollStaffStructureDetailModel;
use App\Models\StaffSalaryAppraisalHistoryModel;

use App\Services\StaffService\StaffProfileCompletionService;

class ManageStaff extends Controller
{

  protected StaffProfileCompletionService $profileCompletion;

  public function __construct(
      StaffProfileCompletionService $profileCompletion
  ) {
      $this->profileCompletion = $profileCompletion;
  }

  public function index(Request $request)
  {
    $startTime = microtime(true);
    $page = $request->input('page', 1);
    $perpage = (int) $request->input('sorting_filter', 25);
    $offset = ($page - 1) * $perpage;
    $search_filter = $request->search_filter ?? '';
    $company_fill = $request->company_fill ?? '';
    $entity_fill = $request->entity_fill ?? '';
    $department_fill = $request->department_fill ?? '';
    $division_fill = $request->division_fill ?? '';
    $job_role_fill = $request->job_role_fill ?? '';
    $date_filter = $request->dt_fill_issue_rpt ?? '';
    $from_date_filter = $request->to_dt_iss_rpt ?? '';
    $to_date_filter = $request->to_date_fillter_textbox ?? '';


    $helper = new \App\Helpers\Helpers();
    $general_setting = $helper->general_setting_data();

    $staffData = StaffModel::where('egc_staff.status', '!=', 2)
      ->select(
        'egc_staff.*',
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
      ->where('egc_staff.sno', '>', 0)
      ->whereIn('egc_staff.status', [0, 1]);
      // ->where('egc_staff.status', '!=',2);
    if ($search_filter != '') {
      $staffData->where(function ($subquery) use ($search_filter) {
        $subquery->where('egc_staff.staff_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_staff.nick_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_staff.mobile_no', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_entity.entity_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_company.company_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_entity.entity_short_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_department.department_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_division.division_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_job_role.job_position_name', 'LIKE', "%{$search_filter}%");
      });
    }

    if ($company_fill != '') {
      if ($company_fill == 'egc') {
        $staffData->where('egc_staff.company_type', 1);
      } else {
        $staffData->where('egc_staff.company_id', 'LIKE', $company_fill);
      }
    }

    if ($entity_fill) {
      $staffData->where('egc_staff.entity_id', $entity_fill);
    }


    if ($department_fill) {
      $staffData->where('egc_staff.department_id', $department_fill);
    }

    if ($division_fill) {
      $staffData->where('egc_staff.division_id', $division_fill);
    }

    if ($job_role_fill) {
      $staffData->where('egc_staff.job_role_id', $job_role_fill);
    }
    $today = date('Y-m-d');
    if ($date_filter == "today") {
      $todayDate = date("Y-m-d");
      $staffData->whereDate('egc_staff.date_of_joining', $todayDate);
    } elseif ($date_filter == "week") {
      $today = date('l');
      if ($today == "Sunday") {
        $weekFromDate = date('Y-m-d', strtotime("sunday 0 week"));
        $weekToDate = date('Y-m-d', strtotime("saturday 1 week"));
      } else {
        $weekFromDate = date('Y-m-d', strtotime("sunday -1 week"));
        $weekToDate = date('Y-m-d', strtotime("saturday 0 week"));
      }
      $staffData->whereBetween('egc_staff.date_of_joining', [$weekFromDate, $weekToDate]);
    } elseif ($date_filter == "monthly") {
      $firstDayOfMonth = date('Y-m-01');
      $lastDayOfMonth = date('Y-m-t');
      $staffData->whereBetween('egc_staff.date_of_joining', [$firstDayOfMonth, $lastDayOfMonth]);
    } elseif ($date_filter == "custom_date") {
      if ($from_date_filter && $to_date_filter) {
        $fromDate = date('Y-m-d', strtotime($from_date_filter));
        $toDate = date('Y-m-d', strtotime($to_date_filter));
        $staffData->whereBetween('egc_staff.date_of_joining', [$fromDate, $toDate]);
      } elseif ($from_date_filter) {
        $fromDate = date('Y-m-d', strtotime($from_date_filter));
        $staffData->where('egc_staff.date_of_joining', '>=', $fromDate);
      } elseif ($to_date_filter) {
        $toDate = date('Y-m-d', strtotime($to_date_filter));
        $staffData->where('egc_staff.date_of_joining', '<=', $toDate);
      }
    } elseif ($date_filter == "0_2_months") {
      $startDate = date('Y-m-d', strtotime('-2 months'));
      $staffData->whereBetween('egc_staff.date_of_joining', [$startDate, $today]);
    } elseif ($date_filter == "2_4_months") {
      $startDate = date('Y-m-d', strtotime('-4 months'));
      $endDate = date('Y-m-d', strtotime('-2 months'));
      $staffData->whereBetween('egc_staff.date_of_joining', [$startDate, $endDate]);
    } elseif ($date_filter == "4_6_months") {
      $startDate = date('Y-m-d', strtotime('-6 months'));
      $endDate = date('Y-m-d', strtotime('-4 months'));
      $staffData->whereBetween('egc_staff.date_of_joining', [$startDate, $endDate]);
    } elseif ($date_filter == "6_12_months") {
      $startDate = date('Y-m-d', strtotime('-12 months'));
      $endDate = date('Y-m-d', strtotime('-6 months'));
      $staffData->whereBetween('egc_staff.date_of_joining', [$startDate, $endDate]);
    } elseif ($date_filter == "1_2_years") {
      $startDate = date('Y-m-d', strtotime('-2 years'));
      $endDate = date('Y-m-d', strtotime('-1 year'));
      $staffData->whereBetween('egc_staff.date_of_joining', [$startDate, $endDate]);
    } elseif ($date_filter == "gt_2_years") {
      $endDate = date('Y-m-d', strtotime('-2 years'));
      $staffData->where('egc_staff.date_of_joining', '<=', $endDate);
    } elseif ($date_filter == "gt_1_year") {
      $endDate = date('Y-m-d', strtotime('-1 year'));
      $staffData->where('egc_staff.date_of_joining', '<=', $endDate);
    } elseif ($date_filter == "gt_6_months") {
      $endDate = date('Y-m-d', strtotime('-6 months'));
      $staffData->where('egc_staff.date_of_joining', '<=', $endDate);
    }

    $staffData = $staffData->orderBy('egc_staff.date_of_joining', 'desc')->paginate($perpage);

    foreach ($staffData as $staff) {
      if ($staff->company_type == 1) {
        $staff->company_name = $general_setting->title;
        $staff->company_base_color = '#ab2b22';
        $staff->company_logo = $general_setting->logo;
      }

      $staff->staffProfile = $staff->profile_image_url;

      $educations = DB::table('egc_staff_education_info')
        ->select('egc_education.education')
        ->join('egc_education', 'egc_education.sno', '=', 'egc_staff_education_info.qualification_type')
        ->where('egc_staff_education_info.staff_id', $staff->sno)
        ->where('egc_staff_education_info.status', 0)
        ->pluck('egc_education.education');
      $staff->education = $educations;
    }



    if ($request->ajax()) {
      $data = $staffData->map(function ($item) use ($helper) {

       
        return [
          'sno' => $item->sno,
          'status' => $item->status,
          'staff_name' => $item->staff_name,
          'staffProfile' => $item->staffProfile,
          'nick_name' => $item->nick_name,
          'gender' => $item->gender,
          'company_id' => $item->company_id,
          'entity_id' => $item->entity_id,
          'company_type' => $item->company_type,
          'department_name' => $item->department_name,
          'division_name' => $item->division_name,
          'job_role_name' => $item->job_role_name,
          'exp_type' => $item->exp_type,
          'basic_salary' => $item->basic_salary,
          'completion_percentage' => $item->completion_percentage,
          'company_base_color' => $item->company_base_color,
          'company_name' => $item->company_name,
          'entity_name' => $item->entity_name,
          'department_desc' => $item->department_desc,
          'data' => $item,
          'encrypted_id' => $helper->encrypt_decrypt($item->sno, 'encrypt'),
        ];
      });

      $companyCounts = StaffModel::select(
        DB::raw('COUNT(*) as total'),
        'egc_staff.company_id',
        'egc_entity.entity_short_name',
        'egc_company.company_short_name',
        DB::raw('CASE 
                    WHEN egc_staff.company_type = 1 THEN "' . $general_setting->title . '" 
                    ELSE egc_company.company_name 
                END as company_name'),
        DB::raw('CASE 
                    WHEN egc_staff.company_type = 1 THEN "' . $general_setting->logo . '" 
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
        ->leftJoin('egc_company', 'egc_staff.company_id', '=', 'egc_company.sno')
        ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
        ->where('egc_staff.status', '!=', 2)
        ->where('egc_staff.sno', '>', 1)
        ->whereIn('egc_staff.status', [0, 1])
        ->groupBy('company_name', 'company_logo', 'company_base_color', 'entity_base_color', 'egc_staff.company_type', 'egc_staff.company_id', 'egc_company.company_short_name', 'egc_entity.entity_short_name')
        ->orderBy('egc_staff.company_id', 'asc')
        ->get();

      return response()->json([
        'data' => $data,
        'current_page' => $staffData->currentPage(),
        'last_page' => $staffData->lastPage(),
        'total' => $staffData->total(),
        'company_counts' => $companyCounts
      ]);
    }

    $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();
    $shifts = ShiftTimeModel::where('status', 0)->orderBy('sno', 'ASC')->get();
    $exitReason = StaffExistReasonModel::where('status', 0)->orderBy('sno', 'ASC')->get();
   

    $salaryTemplates = DB::table('egc_payroll_salary_templates')
      ->where('status', 0)
      ->orderBy('template_name')
      ->get();
    $components  = DB::table('egc_payroll_components')
      ->where('status', 1)
      ->orderBy('display_order')
      ->get();

        //   $endTime       = microtime(true);
        // $executionTime = $endTime - $startTime;
        // return($executionTime);

    return view('content.hr_management.hr_enroll.manage_staff.staff_list', [
      'shifts' => $shifts,
      'company_list' => $company_list,
      'exitReason' => $exitReason,
      'salaryTemplates' => $salaryTemplates,
      'components' => $components,
      'perpage' => $perpage,
      'search_filter' => $search_filter,
      'company_fill' => $company_fill,
      'date_filter' => $date_filter,
      'job_role_fill' => $job_role_fill,
      'division_fill' => $division_fill,
      'department_fill' => $department_fill,
      'entity_fill' => $entity_fill,
      'structures' => [],
    ]);
  }

  public function AddOldStaff(Request $request)
  {

    // return $request;
    // Validate incoming request
    $helper = new \App\Helpers\Helpers();
    $validator = Validator::make($request->all(), [
      'staff_company_name' => 'required',
      'staff_entity_name' => 'required',
      'staff_branch_name' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 401,
        'message' => 'Incorrect format input fields',
        'error_msg' => $validator->errors()->all(),
        'data' => null,
      ], 200);
    }

    $user_id = $request->user()->user_id ?? 1;

    $company_type = 2;
    $company_id = $request->staff_company_name;
    $entity_id = $request->staff_entity_name;
    $branch_id = $request->staff_branch_name;
    $staffData = json_decode($request->staff_data_payload, true);

    $entity_url = ManageEntityModel::where('sno', $entity_id)->value('entity_base_url');

    if (!$entity_url) {
      return response()->json([
        'status'  => 404,
        'message' => 'Entity URL not found for the given entity ID.',
      ], 200);
    }

    // 🧩 Step 3: Call external API using Http facade
    $verify_key = 'egcsecret2030datagetapierp';
    $api_url = rtrim($entity_url, '/') . '/api/staff_data_get';

    try {
      $response = Http::get($api_url, ['auth_key' => $verify_key]);

      if (!$response->successful()) {
        return response()->json([
          'status'  => 500,
          'message' => 'Failed to fetch Staff from entity API.',
          'error'   => $response->body(),
        ], 200);
      }

      $StaffData = $response->json()['data'] ?? [];
      $filteredStaffData = array_filter($StaffData, function ($staff) {
        return $staff['external_uuid'] == null;
      });
      $filteredStaffData = array_values($filteredStaffData);

      //   return $filteredStaffData;

      // foreach ($StaffData as $staff) {

      //               $staff_name = $staff['staff_name'] ?? null;
      //               if (!$staff_name) continue;

      //               $staff_mobile = $staff['mobile_no'] ?? null;
      //               if (!$staff_mobile) continue;
      //               $company_type = 2;
      //               $erp_staff_id = $staff['sno'] ?? 0;
      //               $egc_staff_id = $helper->get_sno_by_erpId($erp_staff_id, 'egc_staff', 'erp_staff_id',$entity_id);
      //               $egc_staff_id = $egc_staff_id ?? 0;

      //               if ($egc_staff_id == 0) continue;



      //               // return $egc_staff_id;

      //               if (isset($staff['staff_education_info']) && is_array($staff['staff_education_info'])) {
      //                   foreach ($staff['staff_education_info'] as $educt) {
      //                       StaffEducationInfoModel::create([
      //                           // 'staff_id' => $add_staff->sno,
      //                           'staff_id' => $egc_staff_id,
      //                           'qualification_type' => $educt['qualification_type'],
      //                           'degree_name' => $educt['degree_name'] ?? null,
      //                           'major' => $educt['ECE'] ?? 0,
      //                           'university_name' => $educt['university_name'] ?? null,
      //                           'year' =>  null,
      //                           'created_by' => $request->user()->user_id ?? 1,
      //                           'updated_by' => $request->user()->user_id ?? 1,
      //                       ]);
      //                   }
      //               }
      //           }
      //           return 'sucess';


      if (empty($filteredStaffData)) {
        return response()->json([
          'status'  => 404,
          'message' => 'No user Staff found in API response.',
        ], 200);
      }



      // 🧩 Step 4: Loop and insert roles
      foreach ($filteredStaffData as $staff) {

        $staff_name = $staff['staff_name'] ?? null;
        if (!$staff_name) continue;

        $staff_mobile = $staff['mobile_no'] ?? null;
        if (!$staff_mobile) continue;

        $company_type = 2;

        // 🔍 Check if role already exists
        $chk = StaffModel::where('mobile_no', $staff_mobile)
          ->where('status', '!=', 2)
          ->first();

        if ($chk) continue; // skip duplicates

        $erp_staff_id = $staff['sno'] ?? 0;
        $alternative_no = $staff['alternative_no'] ?? NULL;
        $email_id = $staff['email_id'] ?? NULL;
        $work_exp_type = $staff['exp_type'] ?? 1;
        $gender = $staff['gender'] ?? 1;
        $dob = $staff['dob'] ?? NULL;
        $date_of_joining = $staff['date_of_joining'] ?? NULL;
        $contact_person_name = [$staff['contact_person_name']] ?? NULL;
        $contact_person_name = $contact_person_name ? json_encode($contact_person_name) : NULL;
        $contact_person_no = [$staff['contact_person_no']] ?? NULL;
        $contact_person_no = $contact_person_no ? json_encode($contact_person_no) : NULL;
        $marital_status = $staff['martial_status'] ?? 2;
        $address = $staff['address'] ?? NULL;
        $nick_name = $staff['nick_name'] ?? NULL;
        $description = $staff['description'] ?? NULL;
        $basic_salary = $staff['basic_salary'] ?? 0;
        $per_hr_cost = $staff['per_hour_cost'] ?? 0;
        $user_name = $staff['user_name'] ?? NULL;
        $password = $staff['password'] ?? NULL;

        $erp_department_id = $staff['department_id'] ?? 0;
        $erp_division_id = $staff['sub_department_id'] ?? 0;
        $erp_role_id = $staff['role_id'] ?? 0;
        $erp_job_role_id = $staff['position_role'] ?? 0;

        $role_id = $helper->get_sno_by_erpId($erp_role_id, 'egc_user_role', 'erp_role_id', $entity_id);
        $role_id = $role_id ?? 0;

        $department_id = $helper->get_sno_by_erpId($erp_department_id, 'egc_department', 'erp_department_id', $entity_id);
        $department_id = $department_id ?? 0;

        $division_id = $helper->get_sno_by_erpId($erp_division_id, 'egc_division', 'erp_division_id', $entity_id);
        $division_id = $division_id ?? 0;

        $job_role_id = $helper->get_sno_by_erpId($erp_job_role_id, 'egc_job_role', 'erp_job_role_id', $entity_id);
        $job_role_id = $job_role_id ?? 0;

        // Handle staff image upload
        $staff_image_url = $staff['staff_image'] ?? null;
        $staff_image = null;

        if ($staff_image_url) {
          // Determine file extension
          $ext = pathinfo(parse_url($staff_image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
          if (!$ext) {
            $ext = 'jpg';
          }

          // Define target folder
          $folderPath = public_path("staff_images/Buisness/{$company_id}/{$entity_id}");

          // Ensure the folder exists
          if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0777, true, true);
          }
          // ✅ Find the next available image number in this folder
          $existingFiles = File::files($folderPath);
          $maxNumber = 0;

          foreach ($existingFiles as $file) {
            // Match filenames like staff_image_5.jpg
            if (preg_match('/staff_image_(\d+)\./', $file->getFilename(), $matches)) {
              $num = (int) $matches[1];
              if ($num > $maxNumber) {
                $maxNumber = $num;
              }
            }
          }

          // Next available number
          $nextNumber = $maxNumber + 1;

          // Create new filename
          $staff_image_name = "staff_image_{$nextNumber}.{$ext}";
          $savePath = $folderPath . '/' . $staff_image_name;

          // Download and save the image
          try {
            $imageData = @file_get_contents($staff_image_url);
            if ($imageData !== false) {
              file_put_contents($savePath, $imageData);
              $staff_image = $staff_image_name;
            } else {
              $staff_image = null;
            }
          } catch (Exception $e) {
            $staff_image = null;
          }
        }

        // 🧩 Step 5: Generate next Staff_id
        $staff_company_check = StaffModel::where('company_id', $company_id)->where('status', '!=', 2)->orderBy('sno', 'desc')->first();
        $company_sno = $staff_company_check ? $staff_company_check->sno + 1 : 1;
        $prefix_num = 1 + (int)$company_id; // company_id=1→2, company_id=2→3
        $staff_id = sprintf("EGC%02d%03d", $prefix_num, $company_sno);

        $add_staff = new StaffModel();
        $add_staff->staff_id = $staff_id;
        $add_staff->company_type = $company_type;
        $add_staff->company_id = $company_id;
        $add_staff->erp_staff_id    = $erp_staff_id;
        $add_staff->entity_id    = $entity_id;
        $add_staff->branch_id    = $branch_id;
        $add_staff->division_id = $division_id ?? 0;
        $add_staff->department_id = $department_id ?? 0;
        $add_staff->role_id = $role_id;
        $add_staff->job_role_id = $job_role_id;
        $add_staff->staff_name = $staff_name;
        $add_staff->mobile_no = $staff_mobile;
        $add_staff->alternative_no = $alternative_no;
        $add_staff->email_id = $email_id;
        $add_staff->exp_type = $work_exp_type;
        $add_staff->total_company_shift = 0;
        $add_staff->total_experience = 0;
        $add_staff->gender = $gender;
        $add_staff->dob = $dob;
        $add_staff->date_of_joining = $date_of_joining;
        $add_staff->contact_person_name = $contact_person_name;
        $add_staff->contact_person_no = $contact_person_no;
        $add_staff->martial_status = $marital_status ?? null;
        $add_staff->address = $address ?? null;
        $add_staff->staff_image = $staff_image ?? null;
        $add_staff->attachment = null;
        $add_staff->nick_name = $nick_name ?? null;
        $add_staff->description = $description ?? null;
        $add_staff->basic_salary = $basic_salary;
        $add_staff->per_hour_cost = $per_hr_cost;
        $add_staff->user_name = $user_name;
        $add_staff->password  = $password;
        $add_staff->created_by = $request->user()->user_id;
        $add_staff->updated_by = $request->user()->user_id;

        $add_staff->save();

        if ($add_staff) {

          // Add User for login credentials
          User::create([
            'user_id' => $add_staff->sno,
            'company_type' => $add_staff->company_type,
            'company_id' => $add_staff->company_id,
            'entity_id' => $add_staff->entity_id,
            'role_id' => $add_staff->role_id ?? 0,
            'branch_id' =>  $add_staff->branch_id,
            'name' => $user_name,
            'password' => Hash::make($password),
            'email' => $add_staff->email_id,
            'created_by' => $request->user()->user_id ?? 1,
            'updated_by' => $request->user()->user_id ?? 1,
          ]);

          // Handle work information
          if ($work_exp_type == 2 && isset($staff['staff_work_info']) && is_array($staff['staff_work_info'])) {
            foreach ($staff['staff_work_info'] as $work) {
              StaffWorkInfoModel::create([
                'staff_id' => $add_staff->sno,
                'staff_type' => $work_exp_type,
                'position' => $work['position'] ?? null, // Default to null if 'position' is not set
                'year_of_experience' => $work['year_of_experience'] ?? 0, // Default to 0 if 'year_of_experience' is not set
                'company_name' => $work['company_name'] ?? null, // Default to null if 'company_name' is not set
                'start_date' => isset($work['work_start_date']) ? date('Y-m-d', strtotime($work['work_start_date'])) : null, // Handle missing 'work_start_date'
                'end_date' => isset($work['work_end_date']) ? date('Y-m-d', strtotime($work['work_end_date'])) : null, // Handle missing 'work_end_date'
                'created_by' => $request->user()->user_id ?? 1,
                'updated_by' => $request->user()->user_id ?? 1,
              ]);
            }
          }

          // Handle education information


          if (isset($staff['staff_education_info']) && is_array($staff['staff_education_info'])) {
            foreach ($staff['staff_education_info'] as $educt) {
              StaffEducationInfoModel::create([
                'staff_id' => $add_staff->sno,
                'qualification_type' => $educt['qualification_type'],
                'degree_name' => $educt['degree_name'] ?? null,
                'major' => $educt['ECE'] ?? 0,
                'university_name' => $educt['university_name'] ?? null,
                'year' =>  null,
                'created_by' => $request->user()->user_id ?? 1,
                'updated_by' => $request->user()->user_id ?? 1,
              ]);
            }
          }


          // // Handle staff credentials
          // if ($credential_check == 1 && $request->has('credential')) {
          //     foreach ($request->credential as $credential_id => $data) {
          //         // Skip if username is empty
          //         if (!empty($data['username'])) {
          //             StaffCredentialModel::create([
          //                 'staff_id'      => $add_staff->sno,
          //                 'credential_id' => $credential_id,
          //                 'user_name'     => $data['username'],
          //                 'password'      => $data['password'] ?? null,
          //                 'url_link'      => $data['url'] ?? null,
          //                 'description'   => $data['description'] ?? null,
          //                 'created_by'    => $request->user()->user_id,
          //                 'updated_by'    => $request->user()->user_id,
          //             ]);
          //         }
          //     }
          // }

          $this->dispatchUpdateWebhooks($add_staff, $user_id = 1, 'Update_Staff_uuid');
        }
      }

      return response()->json([
        'status'  => 200,
        'message' => 'Business Staff imported successfully.',
      ]);
    } catch (\Throwable $e) {
      // \Log::error('BusinessAdd API Error: ' . $e->getMessage());
      return response()->json([
        'status'  => 500,
        'message' => 'Something went wrong while fetching Department.',
        'error'   => $e->getMessage(),
      ], 200);
    }
  }


 


   public function staff_add()
  {
      

      $firstLanguages = ['Hindi','Malayalam','English','Tamil'];
      $fieldList = "'" . implode("','", $firstLanguages) . "'";
      $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $staff_list = StaffModel::where('egc_staff.status', 0)
      ->select('egc_staff.*','egc_entity.entity_name','egc_entity.entity_short_name','egc_job_role.job_position_name as job_role_name')
      ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
      ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
      ->where('egc_staff.sno','>',1)
      ->orderBy('egc_staff.sno', 'ASC')
      ->get();

      $CourseTag = CourseTagModel::where('status', '!=', 2)->pluck('course_tag_name');
      $skillTagList = SkillTagModel::where('status', '!=', 2)->pluck('skill_tag_name');
      $hobbyList = HobbyModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $relationshipList = RelationshipModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $source_list = SourceModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $jobPositionlist = JobpositionModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $documentTypeList = DocumentModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $documentCheckList = DocumentCheckListModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $qualificationList = QualificationModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $languageList = LanguageModel::where('status', 0)
          ->orderByRaw("FIELD(name, $fieldList) DESC") 
          ->orderBy('name', 'ASC')
          ->get();
     $credential_list = CredentialModel::where('status', 0)->orderBy('sno', 'ASC')->get(); 
     $management_department = DepartmentModel::where('status', 0)->where('company_type',1)->orderBy('sno', 'ASC')->get();
     $management_user_role = UserRoleModel::where('status', 0)->where('company_type',1)->orderBy('sno', 'ASC')->get();
     $social_media_list = SocialMediaModel::where('status', 0)->orderBy('sno', 'ASC')->get();
     $questions = HrQuestionnaireModel::with(['depends' => function($q) {
        $q->where('status', 0);
    }])->where('status', 0)->get();
    $unique_id = 'Staff_' . Str::uuid();
    $bloodGroupList = DB::table('egc_blood_group')->where('egc_blood_group.status',0)->get();
    $nationalityList = DB::table('egc_nationality')
        ->where('status', 0)
        ->orderByRaw("
            CASE
                WHEN nationality_name = 'Indian' THEN 0
                ELSE 1
            END
        ")
        ->orderBy('nationality_name', 'ASC')
        ->get();

        $religionList = DB::table('egc_religion')->where('status',0)->orderBy('sno','asc')->get();
        $communityList = DB::table('egc_community')->where('status',0)->orderBy('sno','asc')->get();

        $salaryTemplates = DB::table('egc_payroll_salary_templates')
          ->where('status', 0)
          ->orderBy('template_name')
          ->get();
        $components  = DB::table('egc_payroll_components')
          ->where('status', 1)
          ->orderBy('display_order')
          ->get();
      $shifts = ShiftTimeModel::where('status', 0)->orderBy('sno', 'ASC')->get();

    return view('content.hr_management.hr_enroll.manage_staff.add_staff',[
        'social_media_list' => $social_media_list,
        'company_list' => $company_list,
        'salaryTemplates' => $salaryTemplates,
        'components' => $components,
        'bloodGroupList' => $bloodGroupList,
        'nationalityList' => $nationalityList,
        'religionList' => $religionList,
        'communityList' => $communityList,
        'CourseTag' => $CourseTag,
        'skillTagList' => $skillTagList,
        'source_list' => $source_list,
        'documentTypeList' => $documentTypeList,
        'jobPositionlist' => $jobPositionlist,
        'qualificationList' => $qualificationList,
        'languageList' => $languageList,
        'management_department' => $management_department,
        'management_user_role' => $management_user_role,
        'documentCheckList' => $documentCheckList,
        'credential_list' => $credential_list,
        'hobbyList' => $hobbyList,
        'relationshipList' => $relationshipList,
        'questions' => $questions,
        'unique_id' => $unique_id,
        'staff_list' => $staff_list,
        'shifts' => $shifts,
        ]);
  }

  public function Add(Request $request)
  {

    // return $request;
    // Validate incoming request
    $validator = Validator::make($request->all(), [
      // 'staff_name' => 'required|max:255'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 401,
        'message' => 'Incorrect format input fields',
        'error_msg' => $validator->errors()->all(),
        'data' => null,
      ], 200);
    }

    $user_id = $request->user()->user_id;
    // return $request;
    // Determine staff serial number
    

      $company_type = $request->company ?? 1;
      $company_id   = $request->staff_company_name ?? 0;
      $entity_id    = $request->entity_name ?? 0;
      $branch_id    = $request->branch_id ?? 0;
       $staff_check = StaffModel::where('company_type',1)->where('status', '!=', 2)->orderBy('sno', 'desc')->first();
       $sno = $staff_check ? $staff_check->sno + 1 : 1;
    
      // Generate staff ID
      if ($company_type == 1) {
          // Example: EGC01001, EGC01002 ...
          $staff_id = sprintf("EGC01%03d", $sno);
      } elseif ($company_type == 2) {
         $staff_company_check = StaffModel::where('company_id',$company_id)->where('status', '!=', 2)->orderBy('sno', 'desc')->first();
           $company_sno =$staff_company_check ? $staff_company_check->sno + 1 : 1;
          $prefix_num = 1 + (int)$company_id; // company_id=1→2, company_id=2→3
          $staff_id = sprintf("EGC%02d%03d", $prefix_num, $company_sno);
      } else {
          // Default / fallback
          $staff_id = sprintf("EGC01%03d", $sno);
      }

      // return $request;
    // Stage 1 Base Details
    // Handle staff image upload
    $staff_image = '';
    if ($request->hasFile('staff_add_icon')) {
        $image = $request->file('staff_add_icon');
        $extension = $image->extension();

       if ($company_type == 1) {
          $folderPath = public_path('staff_images/Management');
      } else {
          $folderPath = public_path("staff_images/Buisness/{$company_id}/{$entity_id}");
      }
       // Create directory if not exists
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0777, true, true);
        }
          // Build file name
            $staff_imageName = 'staff_' . $sno . '.' . $extension;
          // Move uploaded image
            $image->move($folderPath, $staff_imageName);
             $staff_image = $staff_imageName;
    }

    $staff_name = $request->staff_name;
    $completion_percentage = $request->completion_percentage;
    $stepProgress = json_decode($request->step_progress, true);
    $mobile_no = $request->mobile_no;
    $gender = $request->gender ?? 1;
    $dob = $request->dob ? date('Y-m-d', strtotime($request->dob)) : NULL;
    $email_id = $request->email_id ?? NULL;
    $mother_tongue = $request->mother_tongue ?? NULL;
    $languages = $request->Languages ? json_encode($request->Languages) : NULL;
    $hobby = $request->hobby ? json_encode($request->hobby) : NULL;
    $description = $request->description ?? NULL;
    // Stage 1 New 
    $birth_place = $request->birth_place ?? NULL;
    $blood_group = $request->blood_group ?? NULL;
    $height = $request->height ?? NULL;
    $weight = $request->weight ?? NULL;
    $nationality = $request->nationality ?? NULL;
    $religion = $request->religion ?? NULL;
    $community = $request->community ?? NULL;
    $caste = $request->caste ?? NULL;
    $identification_mark = $request->identification_mark ?? NULL;

    $vehicle_check = $request->vehicle_check ? 1: 0;
    $license_expiry_date = $request->license_expiry ? date('Y-m-d', strtotime($request->license_expiry)) : NULL;

    $driving_license_no = $vehicle_check ==1 ? $request->driving_license_no: NULL;
    $vehicle_register_no = $vehicle_check ==1 ? $request->vehicle_register_no: NULL;
    $license_expiry = $vehicle_check == 1 ? $license_expiry_date: NULL;


    // Stage 2 Family Details
      $father_name = $request->father_name;
      $father_occup = $request->father_occup;
      $mother_name = $request->mother_name ?? NULL;
      $mother_occup = $request->mother_occup ?? NULL;
      $marital_status = $request->marital_status ?? 2;
      $anniversary_date = $request->anniversary_date ? date('Y-m-d', strtotime($request->anniversary_date)) : NULL;
      $spouse_name = $request->spouse_name ?? NULL;
      $spouse_mobile = $request->spouse_mobile ?? NULL;
      $spouse_is_working = $request->is_working ?? 'No';
      $spouse_dob = $request->spouse_dob ? date('Y-m-d', strtotime($request->spouse_dob)) : NULL;
      $spouse_designation = $request->spouse_designation ?? NULL;
      $spouse_company_name = $request->spouse_company_name ?? NULL;
      $spouse_salary = $request->spouse_salary ?? NULL;
      $has_children = $request->has_children ?? NULL;
      $childrenCount = $request->childrenCount ?? 0;
      $child_name = $request->child_name ??  NULL;
      $child_dob = $request->child_dob ??  NULL;
      $child_std = $request->child_std ??  NULL;
      $child_year = $request->child_year ?? NULL;
      $has_Siblings = $request->has_Siblings ?? 0;

      

      // Handle children details
      $children_details = null;
      if (!empty($childrenCount) && $childrenCount > 0) {
          $children_details_array = [];

          for ($i = 0; $i < $childrenCount; $i++) {
              $children_details_array[] = [
                  'child_name' => $child_name[$i] ?? null,
                  'child_dob' => isset($child_dob[$i]) ? date('Y-m-d', strtotime($child_dob[$i])) : null,
                  'child_std' => $child_std[$i] ?? null,
                  'child_year' => $child_year[$i] ?? null,
              ];
          }

          $children_details = json_encode($children_details_array);
      }

      $siblingsCount = $request->siblingsCount ?? 0;
      $sibling_name = $request->sibling_name ??  NULL;
      $sibling_type = $request->sibling_type ??  NULL;
      $sibling_std = $request->sibling_std ??  NULL;
      $sibling_income = $request->sibling_income ?? NULL;

        $siblings_detail = Null;

      if (!empty($siblingsCount) && $siblingsCount > 0) {
          $sibling_details_array = [];

          for ($i = 0; $i < $siblingsCount; $i++) {
              $sibling_details_array[] = [
                  'sibling_name' => $sibling_name[$i] ?? null,
                  'sibling_type' => $sibling_type[$i] ?? null,
                  'sibling_std' => $sibling_std[$i] ?? null,
                  'sibling_income' => $sibling_income[$i] ?? null,
              ];
          }

          $siblings_detail = json_encode($sibling_details_array);
      }
     

    


    // Stage 3 Contact Details
      $permanent_address =$request->permanent_address ?? Null;
      $residential_address = $request->residential_address ?? NULL;
      $staff_location_url = $request->staff_location_url ?? NULL;
      $staff_latitude = $request->staff_latitude ?? NULL;
      $staff_longitude = $request->staff_longitude ?? NULL;
      $contact_person_name = $request->contact_person_name ? json_encode($request->contact_person_name) : NULL;
      $contact_person_no = $request->contact_person_no ? json_encode($request->contact_person_no) : NULL;
      $contact_person_relation = $request->contact_person_relation ? json_encode($request->contact_person_relation) : NULL;

      // foreach ($request->contact_person_name as $index => $file) {

      // }
    
      // Stage 4 social Media Detils
      $socialMediaData = $request->social_media;
         $socialMediaData = array_filter($socialMediaData, function($value) {
            return !is_null($value) && $value !== '';
        });
      $socialMediaData = $socialMediaData ? json_encode($socialMediaData) : Null;

      // stage 5 Educational Details 
        $qualification_type = $request->qualification_type ?? [];
        $degree = $request->degree ?? [];
        $major = $request->major ?? [];
        $univ_name = $request->univ_name ?? [];
        $pass_year = $request->pass_year ?? [];
        $is_Course = $request->is_Course ?? NULL;
        $course_tag = $request->course_tag ?? NULL;

      // stage 6 Work Exp Details
        $work_exp_type = $request->work_type ?? 1;
        $total_company_shift = $request->total_company_shift ?? 1;
        $total_experience = $request->total_experience ?? 1;
        $work_company_name = $request->company_name ?? [];
        $work_position = $request->position ?? [];
        $work_exp_yrs = $request->exp_yrs ?? [];
        $work_salary = $request->salary ?? [];
        $work_st_date = $request->work_st_date ?? [];
        $work_end_date = $request->work_end_date ?? [];
        $exit_reason = $request->ExitReason ?? [];

      
        // ✅ Stage 6: Handle attachments properly
          
        
       // stage 7 Company Details
          if($company_type == 1){
            $department_id = $request->management_depart ?? 0;
            $division_id = $request->management_division ?? 0;
            $role_id = $request->management_user_role ?? 0;
            $job_role_id = $request->management_job_role ?? 0;
          }else{
            $department_id = $request->business_depart ?? 0;
            $division_id = $request->business_division ?? 0;
            $role_id = $request->business_user_role ?? 0;
            $job_role_id = $request->business_job_role ?? 0;
          }

            $erp_branch_id = $request->erp_branch_id ?? 0;
            $erp_department_id = $request->erp_department_id ?? 0;
            $erp_division_id = $request->erp_division_id ?? 0;
            $erp_job_role_id = $request->erp_job_role_id ?? 0;
            $erp_role_id = $request->erp_role_id ?? 0;
            $erp_under_role_id = $request->erp_under_role_id ?? 0;
          
            $employee_id = $request->employee_id ?? NULL;
          $nick_name = $request->pseudo_name ?? NULL;
          $doj = $request->doj ? date('Y-m-d', strtotime($request->doj)) : NULL;
          $basic_salary = $request->basic_salary ?? 0;
          $per_hr_cost = $request->per_hr_cost ?? 0;
          $per_day_salary = $request->per_day_salary ?? 0;
          $skill_tag = $request->skill_tag ? json_encode($request->skill_tag) : NULL;

          $login_access = $request->login_access ?? 0;
          $loginuser_name =$login_access == 1 ? $request->loginuser_name : Null;
          $loginpassword = $login_access == 1 ? $request->loginpassword : Null;
          $credential_check = $request->other_access ?? 0;

          $shiftId   = $request->shift_time_id;

          // salary 
          $isMultiple = $request->is_multiple_account ?? 0;
        // Other credential

      // stage 8 Application Details

          $applied_position = $request->applied_position ? json_encode($request->applied_position) : NULL;
          $interview_company = $request->interview_company ? json_encode($request->interview_company) : NULL;
          $source_id = $request->source_id;
          $source_details = $request->source_details ?? Null;

          $data = $request->all();
          // initialize arrays
          $questions = [];
          $dependents = [];
          // loop through request inputs
          foreach ($data as $key => $value) {
              // 🎯 Handle main question inputs (like q_1, q_2)
              if (preg_match('/^q_(\d+)$/', $key, $matches)) {
                  $questionId = $matches[1];
                  $questions[$questionId] = $value ?? null;
              }
              // 🎯 Handle dependent question inputs (like depend_1, depend_2)
              if (preg_match('/^depend_(\d+)$/', $key, $matches)) {
                  $dependId = $matches[1];
                  $dependents[$dependId] = $value ?? null;
              }
          }
          // Combine both into one structured JSON
          $application_details = [
              'questions' => (object) $questions,
              'dependents' => (object) $dependents,
          ];

      // return $application_details;


      // stage 8 Document Checklist Details
          $document_checked =$request->document_checked ? json_encode($request->document_checked) : NULL;

        // return response()->json([
        //       'status' => 500,
        //       'data' => $details,
        //       'message' => 'Could not add the Staff!',
        //   ]);
   
        // Create new staff record
        $add_staff = new StaffModel();
        $add_staff->staff_id = $employee_id;
        $add_staff->company_type = $company_type;
        $add_staff->company_id = $company_id;
        $add_staff->entity_id    = $entity_id;
        $add_staff->entity_id    = $entity_id;
        $add_staff->branch_id    = $branch_id;
        $add_staff->division_id = $division_id ?? 0;
        $add_staff->department_id = $department_id ?? 0;
        $add_staff->role_id = $role_id;
        $add_staff->job_role_id = $job_role_id;
        $add_staff->staff_name = $request->staff_name;
        $add_staff->mobile_no = $request->mobile_no;
        $add_staff->login_access = $login_access;
        $add_staff->email_id = $request->email_id;
        $add_staff->exp_type = $work_exp_type;
        $add_staff->total_company_shift = $total_company_shift;
        $add_staff->total_experience = $total_experience;
        $add_staff->gender = $request->gender;
        $add_staff->hobby = $hobby ;
        $add_staff->mother_tongue = $mother_tongue;
        $add_staff->languages = $languages;
        $add_staff->birth_place = $birth_place;
        $add_staff->blood_group = $blood_group;
        $add_staff->height = $height;
        $add_staff->weight = $weight;
        $add_staff->nationality_id = $nationality;
        $add_staff->religion_id = $religion;
        $add_staff->community_id = $community;
        $add_staff->caste_id = $caste;
        $add_staff->identification_mark = $identification_mark;
        $add_staff->vehicle_check = $vehicle_check;
        $add_staff->license_expiry = $license_expiry;
        $add_staff->vehicle_register_no = $vehicle_register_no;
        $add_staff->driving_license_no = $driving_license_no;
        $add_staff->dob = date('Y-m-d', strtotime($request->dob));
        $add_staff->date_of_joining = $doj;
        $add_staff->contact_person_name = $contact_person_name;
        $add_staff->contact_person_no = $contact_person_no;
        $add_staff->contact_person_relation = $contact_person_relation;
        $add_staff->martial_status = $marital_status ?? null;
        $add_staff->address = $permanent_address ?? null;
        $add_staff->residential_address = $residential_address ?? null;
        $add_staff->location_url = $staff_location_url ?? null;
        $add_staff->latitude = $staff_latitude ?? null;
        $add_staff->longitude = $staff_longitude ?? null;
        $add_staff->staff_image = $staff_image ?? null;
        $add_staff->nick_name = $nick_name ?? null;
        $add_staff->applied_position = $applied_position ?? null;
        $add_staff->source_id = $source_id ?? null;
        $add_staff->source_details = $source_details ?? null;
        $add_staff->applied_company_ids = $interview_company ?? null;
        // $add_staff->description = $description ?? null;
        $add_staff->basic_salary = $basic_salary;
        $add_staff->per_hour_cost = $per_hr_cost;
        $add_staff->per_day_salary = $per_day_salary;
        $add_staff->casual_leave_count_per_month = $request->casual_leave_count ?? 0;
        $add_staff->salary_type = $request->salary_type ?? 1;
        $add_staff->salary_date = $request->salary_date ?? 0;
        $add_staff->salary_company_id = $request->salary_company_id ?? 0;
        $add_staff->salary_bank_id = $request->salary_bank_id ?? null;
        $add_staff->is_payslip = $request->is_payslip ?? 0;
        $add_staff->is_multiple_account =$isMultiple;
        $add_staff->shift_time_id = $shiftId;
        $add_staff->knowledge_tag = $skill_tag;
        $add_staff->credential = $credential_check;
        $add_staff->user_name = $loginuser_name;
        $add_staff->password  = $loginpassword;
        $add_staff->completion_percentage  = $completion_percentage;
        $add_staff->step_progress  = json_encode($stepProgress);
        $add_staff->social_media_details  = $socialMediaData;
        $add_staff->document_checklist  = $document_checked;
        $add_staff->application_details = json_encode($application_details, JSON_PRETTY_PRINT);
        $add_staff->created_by = $request->user()->user_id;
        $add_staff->updated_by = $request->user()->user_id;

        $add_staff->save();
    // return $add_staff->sno;
    if ($add_staff) {

      // Save family details
      $add_family = new StaffFamilyModel();
      $add_family->staff_id = $add_staff->sno;
      $add_family->father_name = $father_name;
      $add_family->father_occup = $father_occup;
      $add_family->mother_name = $mother_name;
      $add_family->mother_occup = $mother_occup;
      $add_family->marital_status = $marital_status;
      $add_family->anniversary_date = $anniversary_date;
      $add_family->spouse_name = $spouse_name;
      $add_family->spouse_mobile = $spouse_mobile;
      $add_family->spouse_dob = $spouse_dob;
      $add_family->spouse_working = $spouse_is_working;
      $add_family->spouse_designation = $spouse_designation;
      $add_family->spouse_company_name = $spouse_company_name;
      $add_family->spouse_salary = $spouse_salary ?? 0;
      $add_family->has_children = $has_children;
      $add_family->children_count = $childrenCount;
      $add_family->sibling_count = $siblingsCount;
      $add_family->children_details = $children_details;
      $add_family->has_siblings = $has_Siblings;
      $add_family->has_siblings = $has_Siblings;
      $add_family->siblings_detail = $siblings_detail;
      $add_family->created_by = $request->user()->user_id ?? 1;
      $add_family->updated_by = $request->user()->user_id ?? 1;
      $add_family->save();

      $update_staff = StaffModel::where('sno', $add_staff->sno)->first();
      $doc_types = $request->doc_type ?? [];  // Default to empty array if doc_type is not set in the request
      $attachments = [];  // Array to hold all attachment names
      $attachments_url = [];  // Array to hold URLs of the uploaded files
      if($doc_types){
        // Check if uploaded_files is an array or a JSON string
        $uploadedFiles = $request->input('uploaded_files');

        // If JSON string → decode it
        if (is_string($uploadedFiles)) {
            $uploadedFiles = json_decode($uploadedFiles, true);
        }
        // After decode, it MUST be array
        if (!is_array($uploadedFiles)) {
        }
        // Ensure doc_type is an array, in case it's a string
        $docType = is_array($request->input('doc_type')) ? $request->input('doc_type') : [];

        // Check for mismatch between the count of document types and uploaded files
        if (count($docType) !== count($uploadedFiles)) {
        }
        foreach ($uploadedFiles as $index => $files) {
            $docTypeId = $docType[$index] ?? null;  // Get the corresponding doc type for this set of files
            
            if (!$docTypeId) {
                continue;
            }
            if (is_string($files)) {
                $files = json_decode($files, true);  // Decode the string to an actual array
            }

            // Ensure there are files to process
            if (is_array($files) && count($files) > 0) {
                foreach ($files as $filename) {
                    
                    // Define the temp and final paths
                    $tempPath = public_path("staff_attachments/temp/$filename");
                    
                    // Modify final path to include staff_id and doc_type_id
                    $finalPath = public_path("staff_attachments/$add_staff->sno/$docTypeId/");

                    if (!file_exists($finalPath)) {
                        mkdir($finalPath, 0777, true);  
                    }

                    // Move the file from the temp folder to the permanent location
                    if (file_exists($tempPath)) {
                        rename($tempPath, $finalPath . $filename);  // Move the file
                        // Log::info("Moved file from $tempPath to $finalPath");

                        // Append the file name to the attachment array
                        $attachments[] = $filename;

                        // Generate the relative URL for the file and append to the URL array
                        $attachments_url[] = asset("staff_attachments/$add_staff->sno/$docTypeId/$filename");  
                        

                        $staffAttachment = StaffAttachmentModel::updateOrCreate(
                          [
                              'staff_id' => $add_staff->sno,
                              'document_id' => $docTypeId,  
                          ],
                          [
                              'attachment_name' => json_encode($attachments), 
                              'created_by' => $user_id, 
                              'updated_by' => $user_id, 
                          ]
                      );
                    } 
                }
            }
        }

        if (count($attachments) > 0) {
            $update_staff->attachment = json_encode($attachments);  
            $update_staff->update();
        } 
      }

      // Add User for login credentials
      if($login_access == 1){
        User::create([
          'user_id' => $add_staff->sno,
          'company_type' => $add_staff->company_type,
          'company_id' => $add_staff->company_id,
          'entity_id' => $add_staff->entity_id,
          'role_id' => $add_staff->role_id ?? 0,
          'branch_id' =>  $add_staff->branch_id,
          'name' => $loginuser_name,
          'password' => Hash::make($loginpassword),
          'email' => $request->email_id,
          'created_by' => $request->user()->user_id ?? 1,
          'updated_by' => $request->user()->user_id ?? 1,
        ]);
      }
       
      
      
      // Handle education information
      
        foreach ($qualification_type as $key => $qualification) {
          StaffEducationInfoModel::create([
            'staff_id' => $add_staff->sno,
            'qualification_type' => $qualification,
            'major' => $major[$key],
            'university_name' => $univ_name[$key],
            'year' => $pass_year[$key],
            'created_by' => $request->user()->user_id,
            'updated_by' => $request->user()->user_id,
          ]);
        }
      //  return $request;
      // Handle work information
      if ($work_exp_type == 2) {
        foreach ($work_company_name as $key => $company) {
          StaffWorkInfoModel::create([
            'staff_id' => $add_staff->sno,
            'staff_type' => $work_exp_type,
            'position' => $work_position[$key],
            'year_of_experience' => $work_exp_yrs[$key] ?? 0,
            'company_name' => $company,
            'salary' => $work_salary[$key],
            'exit_reason' => $exit_reason[$key],
            'start_date' => $work_st_date[$key] ? date('Y-m-d', strtotime($work_st_date[$key])) : null,
            'end_date' => $work_end_date[$key] ? date('Y-m-d', strtotime($work_end_date[$key])) : null,
            'created_by' => $request->user()->user_id,
            'updated_by' => $request->user()->user_id,
          ]);
        }
      }


      // Handle staff credentials
      if ($credential_check == 1 && $request->has('credential')) {
          foreach ($request->credential as $credential_id => $data) {
              // Skip if username is empty
              if (!empty($data['username'])) {
                  StaffCredentialModel::create([
                      'staff_id'      => $add_staff->sno,
                      'credential_id' => $credential_id,
                      'user_name'     => $data['username'],
                      'password'      => $data['password'] ?? null,
                      'url_link'      => $data['url'] ?? null,
                      'description'   => $data['description'] ?? null,
                      'created_by'    => $request->user()->user_id,
                      'updated_by'    => $request->user()->user_id,
                  ]);
              }
          }
      }


      // Assign Shift Start
        $staffId   = $add_staff->sno;
        $shiftStartDate = $doj;
     
        DB::table('egc_shift_time_log')->insert([
            'staff_id'         => $staffId,
            'start_date'       => $shiftStartDate,
            'end_date'         => null,
            'current_shift_id' =>  0,
            'change_shift_id'  => $shiftId,
            'created_at'       => now(),
            'created_by'       => $request->user()->user_id ?? 0,
            'updated_at'       => now(),
            'updated_by'       => $request->user()->user_id ?? 0,
            'status'           => 0
        ]);

      // Assign Shift End

      // update Bank Account 
        // BANK DETAILS
          StaffBankDetailsModel::updateOrCreate(
            ['staff_id' => $staffId],
            [
              'bank_account_no' => $request->staff_acc_no,
              'account_holder'  => $request->staff_acc_holder,
              'bank_name'       => $request->staff_bank_name,
              'bank_branch'     => $request->staff_bank_branch,
              'ifsc_code'       => $request->ifsc_code,
              'created_by'      => $user_id,
              'updated_by'      => $user_id
            ]
          );

        // STATUTORY DETAILS
          StaffStatutoryDetailsModel::updateOrCreate(
            ['staff_id' => $staffId],
            [
              'uan_no'     => $request->uan_no,
              'esi_no'     => $request->esi_no,
              'pan_no'     => $request->pan_no,
              'aadhar_no'  => $request->aadhar_no,
              'created_by' => $user_id,
              'updated_by' => $user_id
            ]
          );
          

          $accounts = [];
          $existingIds = [];

          // if($isMultiple == 0){
          $grossSalary = (float)$request->basic_salary;
          $perDay = round($grossSalary / 30, 2);
          $perHour = round($grossSalary / (30 * 8), 2);
          
          $salaryAccountCreate =  StaffSalaryAccountModel::create([
                'staff_id' => $staffId,
                'salary_company_id' => $request->salary_company_id,
                'salary_bank_id' => $request->salary_bank_id,
                'gross_salary' => $grossSalary,
                'salary_type' =>$request->salary_type ?? 1,
                'per_day_salary' =>$request->per_day_salary ?? 0,
                'per_hour_cost' =>$request->per_hr_cost ?? 0,
                'effective_from' =>$doj,
                'is_primary' => 1,
                'is_payslip' =>$request->is_payslip ?? 0,
                'status' => 0,
                'created_by' => $user_id,
                'updated_by' => $user_id
            ]);
          // }

          // if($isMultiple == 1){
          //     $accounts = json_decode($request->salary_accounts,true) ?? [];
          //     $totalSalary = 0;
          //     $totalPerDay = 0;
          //     $totalPerHour = 0;
          //     $primaryCompany = null;
          //     $primaryBank = null;

          //     $salaryBankId = !empty($row['salary_bank_id']) ? (int)$row['salary_bank_id'] : null;

          //     foreach($accounts as $row){
          //             $account = StaffSalaryAccountModel::create([
          //                 'staff_id' =>$staffId,
          //                 'salary_company_id' => $row['salary_company_id'],
          //                 'salary_bank_id' => $salaryBankId,
          //                 'gross_salary' => $row['gross_salary'],
          //                 'salary_type' =>$request->salary_type ?? 1,
          //                 'per_day_salary' => $row['per_day_salary'],
          //                 'per_hour_cost' => $row['per_hour_cost'],
          //                 'is_primary' => $row['is_primary'],
          //                 'status' => 0,
          //                 'created_by' => $user_id,
          //                 'updated_by' => $user_id
          //             ]);

          //             $existingIds[] = $account->sno;
                  

          //         $totalSalary += (float)$row['gross_salary'];
          //         $totalPerDay += (float)$row['per_day_salary'];
          //         $totalPerHour += (float)$row['per_hour_cost'];

          //         if($row['is_primary'] == 1){
          //             $primaryCompany = $row['salary_company_id'];
          //             $primaryBank = !empty($row['salary_bank_id']) ? (int)$row['salary_bank_id'] : null;
          //         }
          //     }

          //     StaffSalaryAccountModel::where('staff_id',$sno)
          //     ->whereNotIn('sno',$existingIds )
          //     ->update([
          //         'status' => 2,
          //         'updated_by' => $user_id
          //     ]);

          //     $staff->basic_salary =$totalSalary;
          //     $staff->per_day_salary =$totalPerDay;
          //     $staff->per_hour_cost =$totalPerHour;
          //     $staff->salary_company_id =$primaryCompany;
          //     $staff->salary_bank_id =$primaryBank;
          //     $staff->save();
          // }

      // Save Payroll Structure
        if($salaryAccountCreate){
          $payroll_template_sno = $request->payroll_template_sno;

          $details = $request->payrolDetails ?? [];

          if (is_string($details)) {
              $details = json_decode($details, true);
          }

          $savePayroll = $this->saveStaffPayrollStructure($staffId ,$salaryAccountCreate->sno , $grossSalary , $doj , $details , $payroll_template_sno, $user_id);
        }
        

        
      if($company_type == 2){
           $contact_person_name_first=$request->contact_person_name[0];
           $contact_person_no_first=$request->contact_person_no[0];
           if($add_staff->staff_image){
              $staff_image_url=url("staff_images/Buisness/{$add_staff->company_id}/{$add_staff->entity_id}/{$add_staff->staff_image}");
           }else{
                $staff_image_url=NULL;
           }

          $payload=[
            'sno' => $add_staff->sno,
            'entity_id' => $add_staff->entity_id,
            'staff_id' => $staff_id,
            'branch_id' => 1,
            'shift_time_id' => 1,
            'multi_branch_access'    => '',
            'sub_department_id' => $erp_division_id ?? 0,
            'department_id' => $erp_department_id ?? 0,
            'role_id' => $erp_role_id,
            'under_role_id' => $erp_under_role_id,
            'exp_type' => $work_exp_type,
            'staff_name' => $add_staff->staff_name,
            'mobile_no' => $add_staff->mobile_no,
            'alternative_no' => NULL,
            'email_id' => $add_staff->email_id,
            'gender' => $add_staff->gender,
            'dob' => $add_staff->dob,
            'date_of_joining' => $add_staff->date_of_joining,
            'contact_person_name' => $contact_person_name_first,
            'contact_person_no' => $contact_person_no_first,
            'martial_status' => $marital_status ?? null,
            'address' => $add_staff->address ?? null,
            'staff_image' => $staff_image_url ?? null,
            'attachment' =>  $attachments_url ?? null,
            'nick_name' => $add_staff->nick_name ?? null,
            'position_role' => $erp_job_role_id ?? null,
            'description' => $add_staff->description ?? null,
            'basic_salary' => $add_staff->basic_salary ?? null,
            'per_hour_cost' => $add_staff->per_hour_cost ?? null,
            'employee_skill_id' => 0,
            'login_access' => $login_access,
            'user_name' => $add_staff->user_name,
            'password'  => $add_staff->password,
            'work_company_name'  => $work_company_name,
            'work_position'  => $work_position,
            'work_exp_yrs'  => $work_exp_yrs,
            'work_salary'  => $work_salary,
            'exit_reason'  => $exit_reason,
            'work_st_date'  => $work_st_date,
            'work_end_date'  => $work_end_date,
            'credentials'  => $request->credential,
            'qualification_type'  => $qualification_type,
            'degree'  => $degree,
            'major'  => $major,
            'univ_name'  => $univ_name,
            
          ];
            $this->dispatchWebhooks($payload, 1);
      }

        $response = [
            'status' => 200,
            'message' => 'Staff added successfully',
            'staff_id' => $staff_id,
            'attachments' => $attachments_url,
        ];

        if ($request->ajax()) {
            return response()->json($response);
        }

        session()->flash('toastr', [
            'type' => 'success',
            'message' => 'Staff added Successfully!'
        ]);
        return redirect('hr_enroll/manage_staff');
    } else {
      if ($request->ajax()) {
            return response()->json([
                'status' => 500,
                'message' => 'Could not add the Staff!',
            ]);
        }

        session()->flash('toastr', [
            'type' => 'error',
            'message' => 'Could not add the Staff !'
        ]);
        return redirect('hr_enroll/manage_staff');
    }
   
  }

  // unq Check add
  public function checkunique_user_name()
  {
    $username = request()->input('value');
    $staff = StaffModel::where('user_name', $username)
      ->where('status', '!=', 2)
      ->first();

    if ($staff) {
      return response()->json([
        'message' => 'Staff username already assigned!',
        'data' => 1,
      ], 200);
    } else {
      return response()->json([
        'message' => 'Username available!',
        'data' => 0,
      ], 200);
    }
  }

  public function checkStaffMobileExists(Request $request)
  {
    $staff_mobile = $request->input('mobile');

    // Assuming you have a Lead model with a mobile field
    // $exists = StaffModel::where('mobile_no', $staff_mobile)->exists();
    $staff = StaffModel::where('mobile_no', $staff_mobile)
      ->where('status', '!=', 2)
      ->first();

    if ($staff) {
      return response()->json([
        'message' => 'Staff Mobile Number already assigned!',
        'data'    => 1,
      ], 200);
    } else {
      return response()->json([
        'message' => 'mobile no available!',
        'data'    => 0,
      ], 200);
    }
  }



  public function edit($id,Request $request)
  {
      $helper = new \App\Helpers\Helpers();
      $decryptedValue = $helper->encrypt_decrypt($id, 'decrypt');

      // Check if decryption failed
      if ($decryptedValue === false) {
        return redirect()->back()->with('error', 'Invalid Entry');
      }

      $staffId=$decryptedValue;

      $staffData = StaffModel::where('egc_staff.status', '!=', 2)->where('egc_staff.sno', $staffId)->first();
      $staffFamily = StaffFamilyModel::where('status', '!=', 2)->where('staff_id', $staffId)->first();
      $staffEducation = StaffEducationInfoModel::where('status', '!=', 2)->where('staff_id', $staffId)->get();
      $staffWork = StaffWorkInfoModel::where('staff_id', $staffId)->where('status', '!=', 2)->get();
      $attachments = StaffAttachmentModel::where('staff_id', $staffId)->where('status', '!=', 2)->get();
      $staffCredntial = StaffCredentialModel::where('staff_id', $staffId)->where('status', '!=', 2)->get();

      $CourseTag = CourseTagModel::where('status', '!=', 2)->pluck('course_tag_name');
      $skillTagList = SkillTagModel::where('status', '!=', 2)->pluck('skill_tag_name');
     
      
    $firstLanguages = ['Hindi','Malayalam','English','Tamil'];
      $fieldList = "'" . implode("','", $firstLanguages) . "'";
      $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $hobbyList = HobbyModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $relationshipList = RelationshipModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $source_list = SourceModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $jobPositionlist = JobpositionModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $documentTypeList = DocumentModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $documentCheckList = DocumentCheckListModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $qualificationList = QualificationModel::where('status', 0)->orderBy('sno', 'ASC')->get();
      $languageList = LanguageModel::where('status', 0)
          ->orderByRaw("FIELD(name, $fieldList) DESC") 
          ->orderBy('name', 'ASC')
          ->get();
     $credential_list = CredentialModel::where('status', 0)->orderBy('sno', 'ASC')->get(); 
     $management_department = DepartmentModel::where('status', 0)->where('company_type',1)->orderBy('sno', 'ASC')->get();
     $management_user_role = UserRoleModel::where('status', 0)->where('company_type',1)->orderBy('sno', 'ASC')->get();
     $social_media_list = SocialMediaModel::where('status', 0)->orderBy('sno', 'ASC')->get();
     $questions = HrQuestionnaireModel::with(['depends' => function($q) {
        $q->where('status', 0);
    }])->where('status', 0)->get();

      $staffBank = StaffBankDetailsModel::where('staff_id', $staffId)->first();

      $staffStatutory = StaffStatutoryDetailsModel::where('staff_id', $staffId)->first();
      $singleSalaryAccount = StaffSalaryAccountModel::where('staff_id',$staffId)
      ->where('status',0)
      ->where('is_primary',1)
      ->first();
    
      $existingStructure =NULL;
      if($singleSalaryAccount){
        $existingStructure = DB::table('egc_payroll_employee_structures')
        ->where('salary_account_id', $singleSalaryAccount->sno)
        ->where('employee_sno', $staffId)
        ->where('is_current', 1)
        ->first();
      }

       
     
      $staffBankCompany=DB::table('egc_company_bank_accounts')->where('status',0)->where('sno',$staffData->salary_bank_id)->first();

      $staffData->staff_bank_company_id = $staffBankCompany ?$staffBankCompany->company_id : NULL;

      $salaryComponents = StaffSalaryComponentModel::where('staff_id', $staffId)->get();

      $bloodGroupList = DB::table('egc_blood_group')->where('egc_blood_group.status',0)->get();
        $nationalityList = DB::table('egc_nationality')
        ->where('status', 0)
        ->orderByRaw("
            CASE
                WHEN nationality_name = 'Indian' THEN 0
                ELSE 1
            END
        ")
        ->orderBy('nationality_name', 'ASC')
        ->get();

        $religionList = DB::table('egc_religion')->where('status',0)->orderBy('sno','asc')->get();
        $communityList = DB::table('egc_community')->where('status',0)->orderBy('sno','asc')->get();

        $salaryTemplates = DB::table('egc_payroll_salary_templates')
          ->where('status', 0)
          ->orderBy('template_name')
          ->get();
        $components  = DB::table('egc_payroll_components')
          ->where('status', 1)
          ->orderBy('display_order')
          ->get();
      $shifts = ShiftTimeModel::where('status', 0)->orderBy('sno', 'ASC')->get();

      // $payrollRules = DB::table('egc_payroll_rules')
      // ->where('status', 0)
      // ->get()
      // ->map(function($rule){
      //     return [
      //         'rule_type' => $rule->rule_type,
      //         'condition' => json_decode($rule->condition_json, true),
      //         'action' => json_decode($rule->action_json, true),
      //     ];
      // });
    return view('content.hr_management.hr_enroll.manage_staff.edit_staff',[
      'staffData' => $staffData,
      'staffFamily' => $staffFamily,
      'salaryTemplates' => $salaryTemplates,
        'components' => $components,
        'bloodGroupList' => $bloodGroupList,
        'nationalityList' => $nationalityList,
        'religionList' => $religionList,
        'communityList' => $communityList,
        'shifts' => $shifts,
      'staffBank' => $staffBank,
      'payrollRules' => $payrollRules ?? [],
      'staffStatutory' => $staffStatutory,
      'salaryComponents' => $salaryComponents,
      'staffWork' => $staffWork,
      'attachments' => $attachments,
      'existingStructure' => $existingStructure,
      'singleSalaryAccount' => $singleSalaryAccount,
      'staffEducation' => $staffEducation,
      'staffEducation' => $staffEducation,
      'staffCredntial' => $staffCredntial,
      'CourseTag' => $CourseTag,
       'skillTagList' => $skillTagList,
      'social_media_list' => $social_media_list,
        'company_list' => $company_list,
        'source_list' => $source_list,
        'documentTypeList' => $documentTypeList,
        'jobPositionlist' => $jobPositionlist,
        'qualificationList' => $qualificationList,
        'languageList' => $languageList,
        'management_department' => $management_department,
        'management_user_role' => $management_user_role,
        'documentCheckList' => $documentCheckList,
        'credential_list' => $credential_list,
        'hobbyList' => $hobbyList,
        'relationshipList' => $relationshipList,
        'questions' => $questions
    ]);
  }

  // unq esit chk username
  public function checkunique_user_name_edit()
  {
    $username = request()->input('value');
    $id = request()->input('id');
    // return $id;
    $staff = StaffModel::where('user_name', $username)
      ->where('status', '!=', 2)
      ->where('sno', '!=', $id)
      ->first();

    if ($staff) {
      return response()->json([
        'message' => 'Staff username already assigned!',
        'data' => $staff,
      ], 200);
    } else {
      return response()->json([
        'message' => 'Username available!',
        'data' => 0,
      ], 200);
    }
  }
  // unq esit chk username
  public function checkStaffMobileExists_edit()
  {
    $staff_mobile = request()->input('mobile');
    $id           = request()->input('id');
    // return $id;
    $staff = StaffModel::where('mobile_no', $staff_mobile)
      ->where('status', '!=', 2)
      ->where('sno', '!=', $id)
      ->first();

    if ($staff) {
      return response()->json([
        'message' => 'Staff Mobile Number already assigned!',
        'data'    => $staff,
      ], 200);
    } else {
      return response()->json([
        'message' => 'Mobile Number available!',
        'data'    => 0,
      ], 200);
    }
  }

  // exist Staff
  public function Departure_staff(Request $request)
  {
    // return $request;
    $id = $request->exist_staff_id;
    $user_id = $request->user()->user_id ?? 0;
    // Retrieve the staff record
    $staff = StaffModel::where('sno', $id)->first();

    if (!$staff) {
      session()->flash('toastr', [
        'type' => 'error',
        'message' => 'Invalid Staff!',
      ]);
    }


    $staff->status = $request->exist_sts_change_id;

    switch ($request->exist_sts_change_id) {
      case 4:
        $staff->notice_start_date = date('Y-m-d', strtotime($request->notice_start_date));
        $staff->notice_end_date   = date('Y-m-d', strtotime($request->notice_end_date));
        $staff->staff_last_date = date('Y-m-d', strtotime($request->notice_end_date));
        $staff->dep_reason      = $request->dep_reason;
        break;

      case 5:
        $staff->staff_last_date = date('Y-m-d', strtotime($request->staff_last_date));
        $staff->dep_reason = $request->dep_reason;
        break;

      case 6:
        $staff->staff_last_date = date('Y-m-d', strtotime($request->staff_last_date));
        $staff->dep_reason = $request->dep_reason;
        break;

      case 7:
        $staff->staff_last_date = date('Y-m-d', strtotime($request->staff_last_date));
        $staff->dep_reason = $request->dep_reason;
        break;

      default:
        session()->flash('toastr', [
          'type' => 'error',
          'message' => 'Could not add the Exit for the staff!',
        ]);
    }
    $staff->updated_by = $user_id;
    // Save the updated staff data
    if ($staff->save()) {
      if ($staff->company_type == 2) {
        $payload = [
          'sno' => $staff->sno,
          'staff_id' => $id,
          'entity_id' => $staff->entity_id,
          'dep_reason'    => $staff->dep_reason ?? null,
          'notice_start_date' => $staff->notice_start_date ?? null,
          'notice_end_date' =>  $staff->notice_end_date ?? null,
          'staff_last_date' =>  $staff->staff_last_date ?? null,
          'status' =>  $staff->status ?? 1,

        ];
        $this->dispatchUpdateExistStaffWebhooks($payload, $user_id, 'Update_Exist_Staff');
      }

      session()->flash('toastr', [
        'type' => 'success',
        'message' => 'Staff Exit Status Successfully Updated!',
      ]);
    } else {
      session()->flash('toastr', [
        'type' => 'error',
        'message' => 'Could not add the Exit for the staff!',
      ]);
    }
    return redirect('hr_enroll/exit_staff');
  }


  public function exit_staff(Request $request)
  {
    $page = $request->input('page', 1);
    $perpage = (int) $request->input('sorting_filter', 25);
    $offset = ($page - 1) * $perpage;
    $search_filter = $request->search_filter ?? '';
    $company_fill = $request->company_fill ?? '';
    $entity_fill = $request->entity_fill ?? '';
    $department_fill = $request->department_fill ?? '';
    $division_fill = $request->division_fill ?? '';
    $job_role_fill = $request->job_role_fill ?? '';
    $date_filter = $request->dt_fill_issue_rpt ?? '';
    $from_date_filter = $request->to_dt_iss_rpt ?? '';
    $to_date_filter = $request->to_date_fillter_textbox ?? '';

    $helper = new \App\Helpers\Helpers();
    $general_setting = $helper->general_setting_data();

    $staffData = StaffModel::where('egc_staff.status', '!=', 2)
      ->select(
        'egc_staff.*',
        'egc_entity.entity_name',
        'egc_entity.entity_short_name',
        'egc_company.company_name',
        'egc_company.company_base_color',
        'egc_department.department_name',
        'egc_division.division_name',
        'egc_job_role.job_position_name as job_role_name',
      )
      ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
      ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
      ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
      ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
      ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
      ->where('egc_staff.sno', '>', 1)
      ->where('egc_staff.status', '>', 3);
    if ($search_filter != '') {
      $staffData->where(function ($subquery) use ($search_filter) {
        $subquery->where('egc_staff.staff_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_staff.nick_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_staff.mobile_no', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_entity.entity_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_company.company_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_entity.entity_short_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_department.department_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_division.division_name', 'LIKE', "%{$search_filter}%")
          ->orWhere('egc_job_role.job_position_name', 'LIKE', "%{$search_filter}%");
      });
    }

    if ($company_fill != '') {
      if ($company_fill == 'egc') {
        $staffData->where('egc_staff.company_type', 1);
      } else {
        $staffData->where('egc_staff.company_id', 'LIKE', $company_fill);
      }
    }

    if ($entity_fill) {
      $staffData->where('egc_staff.entity_id', $entity_fill);
    }


    if ($department_fill) {
      $staffData->where('egc_staff.department_id', $department_fill);
    }

    if ($division_fill) {
      $staffData->where('egc_staff.division_id', $division_fill);
    }

    if ($job_role_fill) {
      $staffData->where('egc_staff.job_role_id', $job_role_fill);
    }

    if ($date_filter == "today") {
      $todayDate = date("Y-m-d");
      $staffData->whereDate('egc_staff.date_of_joining', $todayDate);
    } elseif ($date_filter == "week") {
      $today = date('l');
      if ($today == "Sunday") {
        $weekFromDate = date('Y-m-d', strtotime("sunday 0 week"));
        $weekToDate = date('Y-m-d', strtotime("saturday 1 week"));
      } else {
        $weekFromDate = date('Y-m-d', strtotime("sunday -1 week"));
        $weekToDate = date('Y-m-d', strtotime("saturday 0 week"));
      }
      $staffData->whereBetween('egc_staff.date_of_joining', [$weekFromDate, $weekToDate]);
    } elseif ($date_filter == "monthly") {
      $firstDayOfMonth = date('Y-m-01');
      $lastDayOfMonth = date('Y-m-t');
      $staffData->whereBetween('egc_staff.date_of_joining', [$firstDayOfMonth, $lastDayOfMonth]);
    } elseif ($date_filter == "custom_date") {
      if ($from_date_filter && $to_date_filter) {
        $fromDate = date('Y-m-d', strtotime($from_date_filter));
        $toDate = date('Y-m-d', strtotime($to_date_filter));
        $staffData->whereBetween('egc_staff.date_of_joining', [$fromDate, $toDate]);
      } elseif ($from_date_filter) {
        $fromDate = date('Y-m-d', strtotime($from_date_filter));
        $staffData->where('egc_staff.date_of_joining', '>=', $fromDate);
      } elseif ($to_date_filter) {
        $toDate = date('Y-m-d', strtotime($to_date_filter));
        $staffData->where('egc_staff.date_of_joining', '<=', $toDate);
      }
    }

    $staffData = $staffData->orderBy('egc_staff.sno', 'desc')->paginate($perpage);

    foreach ($staffData as $staff) {
      if ($staff->company_type == 1) {
        $staff->company_name = $general_setting->title;
        $staff->company_base_color = '#ab2b22';

        $educations = DB::table('egc_staff_education_info')
          ->select('egc_education.education')
          ->join('egc_education', 'egc_education.sno', '=', 'egc_staff_education_info.qualification_type')
          ->where('egc_staff_education_info.staff_id', $staff->sno)
          ->where('egc_staff_education_info.status', 0)
          ->pluck('egc_education.education');
        $staff->education = $educations;
      }
    }



    if ($request->ajax()) {
      $data = $staffData->map(function ($item) use ($helper) {
        return [
          'sno' => $item->sno,
          'status' => $item->status,
          'staff_name' => $item->staff_name,
          'nick_name' => $item->nick_name,
          'gender' => $item->gender,
          'company_id' => $item->company_id,
          'entity_id' => $item->entity_id,
          'company_type' => $item->company_type,
          'department_name' => $item->department_name,
          'division_name' => $item->division_name,
          'job_role_name' => $item->job_role_name,
          'exp_type' => $item->exp_type,
          'basic_salary' => $item->basic_salary,
          'completion_percentage' => $item->completion_percentage,
          'company_base_color' => $item->company_base_color,
          'company_name' => $item->company_name,
          'entity_name' => $item->entity_name,
          'department_desc' => $item->department_desc,
          'data' => $item,
          'encrypted_id' => $helper->encrypt_decrypt($item->sno, 'encrypt'),
        ];
      });

      return response()->json([
        'data' => $data,
        'current_page' => $staffData->currentPage(),
        'last_page' => $staffData->lastPage(),
        'total' => $staffData->total(),
      ]);
    }

    $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();

    session()->forget('branch_id_ses');
    session()->forget('branch_type_ses');
    session()->forget('Add_staff_by_branch_id');
    // return $staff;
    return view('content.hr_management.hr_enroll.manage_staff.exit_staff_list', [
      'company_list' => $company_list,
      'perpage' => $perpage,
      'search_filter' => $search_filter,
      'company_fill' => $company_fill,
      'date_filter' => $date_filter,
      'job_role_fill' => $job_role_fill,
      'division_fill' => $division_fill,
      'department_fill' => $department_fill,
      'entity_fill' => $entity_fill,
    ]);
  }

  public function Status($id, Request $request)
  {

    $staff =  StaffModel::where('sno', $id)->first();
    $staff->status = $request->input('status', 0);
    $staff->update();
    if ($staff) {
      return response([
        'status'    => 200,
        'message'   => 'Staff  Status Successfully Updated!',
        'error_msg' => 'Could not, update  Staff  Status!',
        'data'      => null,
      ], 200);
    } else {
      return response([
        'status'    => 200,
        'message'   => 'Could not update Staff  Status!',
        'error_msg' => 'Could not, update  Staff  Status!',
        'data'      => null,
      ], 200);
    }
  }

  public function Delete($id)
  {
    $upd_StaffModel = StaffModel::where('sno', $id)->first();
    $upd_StaffModel->status = 2;
    $upd_StaffModel->Update();

    if ($upd_StaffModel) {
      return response([
        'status'    => 200,
        'message'   => 'Staff Deleted Successfully..!',
        'error_msg' => null,
        'data'      => null,
      ], 200);
    } else {
      return response([
        'status'    => 200,
        'message'   => 'Could not delete Staff ..!',
        'error_msg' => null,
        'data'      => null,
      ], 200);
    }
  }


  public function uploadTempDocument(Request $request)
  {
    try {
      // ✅ Validate the upload
      $request->validate([
        'file' => 'required|file|max:20480', // 20 MB max
      ]);

      $file = $request->file('file');
      $originalName = preg_replace('/\s+/', '_', $file->getClientOriginalName());
      $filename = time() . '_' . $originalName;

      $destinationPath = public_path('staff_attachments/temp');

      if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0777, true);
      }

      // ✅ Move uploaded file
      $file->move($destinationPath, $filename);

      // ✅ Respond with a clear success JSON
      return response()->json([
        'status'   => true,
        'message'  => 'File uploaded successfully',
        'filename' => $filename,
        'path'     => asset("staff_attachments/temp/{$filename}"),
      ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'status'  => false,
        'message' => $e->validator->errors()->first(),
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'status'  => false,
        'message' => 'Server error: ' . $e->getMessage(),
      ], 500);
    }
  }

  public function deleteTempDocument(Request $request)
  {
    try {
      $filename = $request->input('filename');

      if (!$filename) {
        return response()->json([
          'status' => false,
          'message' => 'Missing filename parameter'
        ], 400);
      }

      $filePath = public_path("staff_attachments/temp/{$filename}");

      if (file_exists($filePath)) {
        unlink($filePath);
        return response()->json([
          'status' => true,
          'message' => 'File deleted successfully'
        ]);
      }

      return response()->json([
        'status' => false,
        'message' => 'File not found'
      ], 404);
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
      ], 500);
    }
  }




   public function UpdateStaffStage(Request $request,StaffProfileCompletionService $completionService)
  {

    //  return $request;
    // Validate incoming request
    $validator = Validator::make($request->all(), [
      'stage' => 'required|max:255'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 401,
        'message' => 'Incorrect format input fields',
        'error_msg' => $validator->errors()->all(),
        'data' => null,
      ], 401);
    }

    $user_id = $request->user()->user_id;
    $stage = $request->stage;
    // return $request;
    // Determine staff serial number

    // 
    $edit_id = $request->edit_id;
    $company_type = $request->company ?? 1;
    $company_id   = $request->staff_company_name ?? 0;
    $entity_id    = $request->entity_name ?? 0;
    $branch_id    = $request->branch_id ?? 0;
    $sno=$edit_id;
    

    $OldStaffData = StaffModel::where('sno', $edit_id)->first();
    if($stage){
        $edit_id = $request->edit_id;
        $update_staff = StaffModel::where('sno', $edit_id)->first();
        if($update_staff){
          if($stage == 1){
          
            $mobile_no = $request->mobile_no;
            // Stage 1 Base Details
            $staff_name = $request->staff_name;
            
            
            $gender = $request->gender ?? 1;
            $dob = $request->dob ? date('Y-m-d', strtotime($request->dob)) : NULL;
            $email_id = $request->email_id ?? NULL;
            $mother_tongue = $request->mother_tongue ?? NULL;
            $languages = $request->Languages ? json_encode($request->Languages) : NULL;
            $hobby = $request->hobby ? json_encode($request->hobby) : NULL;
            $description = $request->description ?? NULL;

            // Stage 1 New 
            $birth_place = $request->birth_place ?? NULL;
            $blood_group = $request->blood_group ?? NULL;
            $height = $request->height ?? NULL;
            $weight = $request->weight ?? NULL;
            $nationality = $request->nationality ?? NULL;
            $religion = $request->religion ?? NULL;
            $community = $request->community ?? NULL;
            $caste = $request->caste ?? NULL;
            $identification_mark = $request->identification_mark ?? NULL;

            $vehicle_check = $request->vehicle_check ? 1: 0;
            $license_expiry_date = $request->license_expiry ? date('Y-m-d', strtotime($request->license_expiry)) : NULL;

            $driving_license_no = $vehicle_check ==1 ? $request->driving_license_no: NULL;
            $vehicle_register_no = $vehicle_check ==1 ? $request->vehicle_register_no: NULL;
            $license_expiry = $vehicle_check == 1 ? $license_expiry_date: NULL;
            // Handle staff image upload
            $staff_image = '';
            if ($request->hasFile('staff_add_icon')) {
                $image = $request->file('staff_add_icon');
                $extension = $image->extension();

              if ($company_type == 1) {
                  $folderPath = public_path('staff_images/Management');
              } else {
                  $folderPath = public_path("staff_images/Buisness/{$company_id}/{$entity_id}");
              }
              // Create directory if not exists
                if (!File::exists($folderPath)) {
                    File::makeDirectory($folderPath, 0777, true, true);
                }
                  // Build file name
                    $staff_imageName = 'staff_' . $sno . '.' . $extension;
                  // Move uploaded image
                    $image->move($folderPath, $staff_imageName);
                    $staff_image = $staff_imageName;
            } else {
              $staff_image = $request->old_staff_image;
            }

              $update_staff->staff_name = $request->staff_name;
              $update_staff->mobile_no = $request->mobile_no;
              $update_staff->email_id = $request->email_id;
              $update_staff->gender = $request->gender;

              $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

              $update_staff->hobby = $hobby ;
              $update_staff->mother_tongue = $mother_tongue;
              $update_staff->languages = $languages;
              $update_staff->birth_place = $birth_place;
              $update_staff->blood_group = $blood_group;
              $update_staff->height = $height;
              $update_staff->weight = $weight;
              $update_staff->nationality_id = $nationality;
              $update_staff->religion_id = $religion;
              $update_staff->community_id = $community;
              $update_staff->caste_id = $caste;
              $update_staff->identification_mark = $identification_mark;
              $update_staff->vehicle_check = $vehicle_check;
              $update_staff->license_expiry = $license_expiry;
              $update_staff->vehicle_register_no = $vehicle_register_no;
              $update_staff->driving_license_no = $driving_license_no;
              $update_staff->staff_image = $staff_image ?? null;
              $update_staff->dob = date('Y-m-d', strtotime($request->dob));
              $update_staff->update();

              $staff_statutory =StaffStatutoryDetailsModel::updateOrCreate(
                ['staff_id'=>$sno],
                [
                  'pan_no'=>$request->pan_no,
                  'aadhar_no'=>$request->aadhar_no,  
                  'updated_by'=>$user_id
                ]
              );

              
              if($update_staff){
                return response()->json([
                    'status' => 200,
                    'message' => 'Staff Stage '.$stage.' Updated successfully',
                    'error_msg' => null,
                    'stage' => $stage,
                  ], 200);
              }
          }elseif($stage == 2){

             // Stage 2 Family Details
              $father_name = $request->father_name;
              $father_occup = $request->father_occup;
              $mother_name = $request->mother_name ?? NULL;
              $mother_occup = $request->mother_occup ?? NULL;
              $marital_status = $request->marital_status ?? 2;
              $anniversary_date = $request->anniversary_date ? date('Y-m-d', strtotime($request->anniversary_date)) : NULL;
              $spouse_name = $request->spouse_name ?? NULL;
              $spouse_mobile = $request->spouse_mobile ?? NULL;
              $spouse_is_working = $request->is_working ?? 'No';
              $spouse_dob = $request->spouse_dob ? date('Y-m-d', strtotime($request->spouse_dob)) : NULL;
              $spouse_designation = $request->spouse_designation ?? NULL;
              $spouse_company_name = $request->spouse_company_name ?? NULL;
              $spouse_salary = $request->spouse_salary ?? NULL;
              $has_children = $request->has_children ?? NULL;
              $childrenCount = $request->childrenCount ?? 0;
              $child_name = $request->child_name ??  NULL;
              $child_dob = $request->child_dob ??  NULL;
              $child_std = $request->child_std ??  NULL;
              $child_year = $request->child_year ?? NULL;
              $has_Siblings = $request->has_Siblings ?? 0;

              $siblingsCount = $request->siblingsCount ?? 0;
              $sibling_name = $request->sibling_name ??  NULL;
              $sibling_type = $request->sibling_type ??  NULL;
              $sibling_std = $request->sibling_std ??  NULL;
              $sibling_income = $request->sibling_income ?? NULL;

              $siblings_detail = Null;

              if (!empty($siblingsCount) && $siblingsCount > 0) {
                  $sibling_details_array = [];

                  for ($i = 0; $i < $siblingsCount; $i++) {
                      $sibling_details_array[] = [
                          'sibling_name' => $sibling_name[$i] ?? null,
                          'sibling_type' => $sibling_type[$i] ?? null,
                          'sibling_std' => $sibling_std[$i] ?? null,
                          'sibling_income' => $sibling_income[$i] ?? null,
                      ];
                  }

                  $siblings_detail = json_encode($sibling_details_array);
              }

              // Handle children details
              $children_details = null;
              if (!empty($childrenCount) && $childrenCount > 0) {
                  $children_details_array = [];

                  for ($i = 0; $i < $childrenCount; $i++) {
                      $children_details_array[] = [
                          'child_name' => $child_name[$i] ?? null,
                          'child_dob' => isset($child_dob[$i]) ? date('Y-m-d', strtotime($child_dob[$i])) : null,
                          'child_std' => $child_std[$i] ?? null,
                          'child_year' => $child_year[$i] ?? null,
                      ];
                  }

                  $children_details = json_encode($children_details_array);
              }
              $update_staffFamily = StaffFamilyModel::where('staff_id', $edit_id)->first();
              if($update_staffFamily){

                  $completionPayload = $completionService->calculate((int) $edit_id);
                $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
                $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

                  $update_staff->martial_status = $marital_status ?? null;
                  $update_staff->update();


                  $update_staffFamily->father_name = $father_name;
                  $update_staffFamily->father_occup = $father_occup;
                  $update_staffFamily->mother_name = $mother_name;
                  $update_staffFamily->mother_occup = $mother_occup;
                  $update_staffFamily->marital_status = $marital_status;
                  $update_staffFamily->anniversary_date = $anniversary_date;
                  $update_staffFamily->spouse_name = $spouse_name;
                  $update_staffFamily->spouse_mobile = $spouse_mobile;
                  $update_staffFamily->spouse_dob = $spouse_dob;
                  $update_staffFamily->spouse_working = $spouse_is_working;
                  $update_staffFamily->spouse_designation = $spouse_designation;
                  $update_staffFamily->spouse_company_name = $spouse_company_name;
                  $update_staffFamily->spouse_salary = $spouse_salary ?? 0;
                  $update_staffFamily->has_children = $has_children;
                  $update_staffFamily->children_count = $childrenCount;
                  $update_staffFamily->sibling_count = $siblingsCount;
                  $update_staffFamily->children_details = $children_details;
                  $update_staffFamily->has_siblings = $has_Siblings;
                  $update_staffFamily->siblings_detail = $siblings_detail;
                  $update_staffFamily->updated_by = $user_id ?? 1;
                  $update_staffFamily->update();

                  if($update_staffFamily){
                    return response()->json([
                        'status' => 200,
                        'message' => 'Staff Stage '.$stage.' Updated successfully',
                        'error_msg' => null,
                        'stage' => $stage,
                      ], 200);
                  }

              }else{
                $update_staff->martial_status = $marital_status ?? null;
                $update_staff->update();

                $add_family = new StaffFamilyModel();
                $add_family->staff_id = $edit_id;
                $add_family->father_name = $father_name;
                $add_family->father_occup = $father_occup;
                $add_family->mother_name = $mother_name;
                $add_family->mother_occup = $mother_occup;
                $add_family->marital_status = $marital_status;
                $add_family->anniversary_date = $anniversary_date;
                $add_family->spouse_name = $spouse_name;
                $add_family->spouse_mobile = $spouse_mobile;
                $add_family->spouse_dob = $spouse_dob;
                $add_family->spouse_working = $spouse_is_working;
                $add_family->spouse_designation = $spouse_designation;
                $add_family->spouse_company_name = $spouse_company_name;
                $add_family->spouse_salary = $spouse_salary ?? 0;
                $add_family->has_children = $has_children;
                $add_family->children_count = $childrenCount;
                $add_family->children_details = $children_details;
                $add_family->has_siblings = $has_Siblings;
                $add_family->siblings_detail = $siblings_detail;
                $add_family->created_by = $request->user()->user_id ?? 1;
                $add_family->updated_by = $request->user()->user_id ?? 1;
                $add_family->save();

                if($add_family){
                    return response()->json([
                        'status' => 200,
                        'message' => 'Staff Stage '.$stage.' Updated successfully',
                        'error_msg' => null,
                        'stage' => $stage,
                      ], 200);
                  }
              }

          }elseif($stage == 3){
            $permanent_address =$request->permanent_address ?? Null;
            $residential_address = $request->residential_address ?? NULL;
            $staff_location_url = $request->staff_location_url ?? NULL;
            $staff_latitude = $request->staff_latitude ?? NULL;
            $staff_longitude = $request->staff_longitude ?? NULL;
            $contact_person_name = $request->contact_person_name ? json_encode($request->contact_person_name) : NULL;
            $contact_person_no = $request->contact_person_no ? json_encode($request->contact_person_no) : NULL;
            $contact_person_relation = $request->contact_person_relation ? json_encode($request->contact_person_relation) : NULL;


              $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

              $update_staff->address = $permanent_address ?? null;
              $update_staff->residential_address = $residential_address ?? null;
              $update_staff->location_url = $staff_location_url ?? null;
              $update_staff->latitude = $staff_latitude ?? null;
              $update_staff->longitude = $staff_longitude ?? null;
              $update_staff->contact_person_name = $contact_person_name;
              $update_staff->contact_person_no = $contact_person_no;
              $update_staff->contact_person_relation = $contact_person_relation;
              $update_staff->update();

              if($update_staff){
                return response()->json([
                    'status' => 200,
                    'message' => 'Staff Stage '.$stage.' Updated successfully',
                    'error_msg' => null,
                    'stage' => $stage,
                  ], 200);
              }

          }elseif($stage == 4){
            // Stage 4 social Media Detils
            $socialMediaData = $request->social_media;
              $socialMediaData = array_filter($socialMediaData, function($value) {
                  return !is_null($value) && $value !== '';
              });
            $socialMediaData = $socialMediaData ? json_encode($socialMediaData) : Null;

            $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

            $update_staff->social_media_details  = $socialMediaData;
            $update_staff->update();

              if($update_staff){
                return response()->json([
                    'status' => 200,
                    'message' => 'Staff Stage '.$stage.' Updated successfully',
                    'error_msg' => null,
                    'stage' => $stage,
                  ], 200);
              }

          }elseif($stage == 5){
              $qualification_type = $request->qualification_type ?? [];
              $degree = $request->degree ?? [];
              $major = $request->major ?? [];
              $univ_name = $request->univ_name ?? [];
              $pass_year = $request->pass_year ?? [];
              $is_Course = $request->is_Course ?? NULL;
              if($is_Course == 'Yes'){
                $course_tag = $request->course_tag ? json_encode($request->course_tag) : NULL;
              }else{
                $course_tag = NULL;
              }
              
              $existingQualification = StaffEducationInfoModel::where('staff_id', $edit_id)->get();
              $existingqualifyIds = $existingQualification->pluck('qualification_type')->toArray();
              $newQualifyId = $qualification_type;
              $removedQualifyId = array_diff($existingqualifyIds, $newQualifyId);
              foreach ($removedQualifyId as $qualifyId) {
                  $qualifyToRemove = StaffEducationInfoModel::where('staff_id', $edit_id)
                      ->where('qualification_type', $qualifyId)
                      ->first();

                  if ($qualifyToRemove) {
                      $qualifyToRemove->status = 2;
                      $qualifyToRemove->save();
                  }
              }

              foreach ($qualification_type as $key => $qualification) {
                StaffEducationInfoModel::updateOrCreate(
                  [
                      'staff_id' => $edit_id,
                      'qualification_type' => $qualification, 
                  ],
                  [
                    
                    'major' => $major[$key],
                    'university_name' => $univ_name[$key],
                    'year' => $pass_year[$key],
                    'created_by' => $request->user()->user_id,
                    'updated_by' => $request->user()->user_id,
                  ]
                );
              }

              
              $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

              $update_staff->is_Course=$is_Course;
              $update_staff->course_tag=$course_tag;
              $update_staff->update();

              if($update_staff){
                  return response()->json([
                    'status' => 200,
                    'message' => 'Staff Stage '.$stage.' Updated successfully',
                    'error_msg' => null,
                    'stage' => $stage,
                  ], 200);
              }
              


          }elseif($stage == 6){

            // stage 6 Work Exp Details
            $work_exp_type = $request->work_type ?? 1;
            $total_company_shift = $request->total_company_shift ?? 1;
            $total_experience = $request->total_experience ?? 1;
            $edit_exp_id = $request->edit_exp_id ?? [];
            $work_company_name = $request->company_name ?? [];
            $work_position = $request->position ?? [];
            $work_exp_yrs = $request->exp_yrs ?? [];
            $work_salary = $request->salary ?? [];
            $work_st_date = $request->work_st_date ?? [];
            $work_end_date = $request->work_end_date ?? [];
            $exit_reason = $request->ExitReason ?? [];

             $edit_exp_idFilter = array_filter($edit_exp_id, function($value) {
                  return !is_null($value) && $value !== '';
              });
              $existingWorkInfo = StaffWorkInfoModel::where('staff_id', $edit_id)->get();
              $existingWorkIds = $existingWorkInfo->pluck('sno')->toArray();
              $newWorkId = $edit_exp_idFilter;
              $removedWorkId = array_diff($existingWorkIds, $newWorkId);
              foreach ($removedWorkId as $workId) {
                  $workToRemove = StaffWorkInfoModel::where('sno', $workId)
                      ->first();

                  if ($workToRemove) {
                      $workToRemove->status = 2;
                      $workToRemove->save();
                  }
              }

             

              if ($work_exp_type == 2) {
                foreach ($request->edit_exp_id as $key => $expId) {
                    if ($expId) {
                        // UPDATE EXISTING
                        StaffWorkInfoModel::where('sno', $expId)->update([
                            'staff_type' => $work_exp_type,
                            'company_name' => $request->company_name[$key],
                            'position' => $request->position[$key],
                            'year_of_experience' => $request->exp_yrs[$key],
                            'salary' => $request->salary[$key],
                            'start_date' => $request->work_st_date[$key] ? date('Y-m-d', strtotime($request->work_st_date[$key])) : null,
                            'end_date' => $request->work_end_date[$key] ? date('Y-m-d', strtotime($request->work_end_date[$key])) : null,
                            'exit_reason' => $request->ExitReason[$key],
                            'updated_by' => $request->user()->user_id ?? 1,
                        ]);
                    } else {
                        // CREATE NEW
                        StaffWorkInfoModel::create([
                            'staff_id' => $edit_id,
                            'staff_type' => $work_exp_type,
                            'company_name' => $request->company_name[$key],
                            'position' => $request->position[$key],
                            'year_of_experience' => $request->exp_yrs[$key],
                            'salary' => $request->salary[$key],
                            'start_date' => $request->work_st_date[$key],
                            'end_date' => $request->work_end_date[$key],
                            'exit_reason' => $request->ExitReason[$key],
                            'created_by' => $request->user()->user_id ?? 1,
                            'updated_by' => $request->user()->user_id ?? 1,
                        ]);
                    }
                }
              }

           
            $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

            $update_staff->exp_type = $work_exp_type;
            $update_staff->total_company_shift = $total_company_shift;
            $update_staff->total_experience = $total_experience;
            $update_staff->update();

            // Initialize doc_types from the request
            $doc_types = $request->doc_type ?? [];
            $uploadedFiles = $request->input('uploaded_files');

            // Decode uploaded_files if JSON
            if (is_string($uploadedFiles)) {
                $uploadedFiles = json_decode($uploadedFiles, true);
            }

            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [];
            }

            $docType = is_array($doc_types) ? $doc_types : [];

            foreach ($uploadedFiles as $index => $files) {

                $docTypeId = $docType[$index] ?? null;
                if (!$docTypeId) continue;

                // Decode file list if still JSON
                if (is_string($files)) {
                    $files = json_decode($files, true);
                }

                if (!is_array($files) || count($files) == 0) continue;

                /*  
                --------------------------------------------------------------
                  FIX 1: Reset attachments per DOCUMENT (not globally)
                --------------------------------------------------------------
                */
                $attachments = [];

                $finalPath = public_path("staff_attachments/$edit_id/$docTypeId/");
                if (!file_exists($finalPath)) {
                    mkdir($finalPath, 0777, true);
                }

                foreach ($files as $filename) {

                    $tempPath = public_path("staff_attachments/temp/$filename");

                    if (!file_exists($tempPath)) {
                        Log::error("File not found in temp: $tempPath");
                        continue;
                    }

                    // Move file
                    rename($tempPath, $finalPath . $filename);

                    $attachments[] = $filename; // Only THIS doc type's files
                }

                /*
                --------------------------------------------------------------
                  FIX 2: Save DB row ONCE per document type
                --------------------------------------------------------------
                */
                StaffAttachmentModel::updateOrCreate(
                    [
                        'staff_id'    => $edit_id,
                        'document_id' => $docTypeId
                    ],
                    [
                        'attachment_name' => json_encode($attachments),
                        'created_by'      => $user_id,
                        'updated_by'      => $user_id,
                    ]
                );
            }

            /*
            --------------------------------------------------------------
              FIX 3: Mark removed document types as inactive
            --------------------------------------------------------------
            */
            $existingDocIds = StaffAttachmentModel::where('staff_id', $edit_id)
                ->pluck('document_id')
                ->toArray();

            $newDocIds = $docType;
            $removedDocIds = array_diff($existingDocIds, $newDocIds);

            foreach ($removedDocIds as $docId) {
                StaffAttachmentModel::where('staff_id', $edit_id)
                    ->where('document_id', $docId)
                    ->update(['status' => 2]);
            }

            /*
            --------------------------------------------------------------
              FIX 4: Save global attachment if needed
            --------------------------------------------------------------
            */
            $update_staff->attachment = json_encode($uploadedFiles);
            $update_staff->update();

            return response()->json([
                'status'  => 200,
                'message' => 'Staff Stage ' . $stage . ' Updated successfully'
            ], 200);
          }elseif($stage == 7){

              $company_type = $request->company ?? 1;
              $company_id   = $request->staff_company_name ?? 0;
              $entity_id    = $request->entity_name ?? 0;
              $branch_id    = $request->branch_id ?? 0;

            if($company_type == 1){
              $department_id = $request->management_depart ?? 0;
              $division_id = $request->management_division ?? 0;
              $role_id = $request->management_user_role ?? 0;
              $job_role_id = $request->management_job_role ?? 0;
            }else{
              $department_id = $request->business_depart ?? 0;
              $division_id = $request->business_division ?? 0;
              $role_id = $request->business_user_role ?? 0;
              $job_role_id = $request->business_job_role ?? 0;
            }

              $erp_branch_id = $request->erp_branch_id ?? 0;
              $erp_department_id = $request->erp_department_id ?? 0;
              $erp_division_id = $request->erp_division_id ?? 0;
              $erp_job_role_id = $request->erp_job_role_id ?? 0;
              $erp_role_id = $request->erp_role_id ?? 0;
              $erp_under_role_id = $request->erp_under_role_id ?? 0;
            
              $nick_name = $request->pseudo_name ?? NULL;
              $doj = $request->doj ? date('Y-m-d', strtotime($request->doj)) : NULL;
              $employee_id = $request->employee_id ??NULL;
             
              $skill_tag = $request->skill_tag ? json_encode($request->skill_tag) : NULL;
              $login_access = $request->login_access ?? 0;
              $loginuser_name =$login_access == 1 ? $request->loginuser_name : Null;
              $loginpassword = $login_access == 1 ? $request->loginpassword : Null;
              $credential_check = $request->other_access ?? 0;

              $update_staff->company_type = $company_type;
              $update_staff->company_id = $company_id;
              $update_staff->entity_id    = $entity_id;
              $update_staff->entity_id    = $entity_id;
              $update_staff->branch_id    = $branch_id;
              $update_staff->division_id = $division_id ?? 0;
              $update_staff->department_id = $department_id ?? 0;
              $update_staff->role_id = $role_id;
              $update_staff->job_role_id = $job_role_id;
              $update_staff->date_of_joining = $doj;
              $update_staff->nick_name = $nick_name ?? null;

              $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

              $update_staff->staff_id = $employee_id;
              $update_staff->knowledge_tag = $skill_tag;
              $update_staff->credential = $credential_check;
              $update_staff->login_access = $login_access;
              $update_staff->user_name = $loginuser_name;
              $update_staff->password  = $loginpassword;
              $update_staff->update();

              if($login_access == 1){
                  User::updateOrCreate(
                  [
                    'user_id' => $edit_id,
                  ],
                  [
                  'company_type' => $update_staff->company_type,
                  'company_id' => $update_staff->company_id,
                  'entity_id' => $update_staff->entity_id,
                  'role_id' => $update_staff->role_id ?? 0,
                  'branch_id' =>  $update_staff->branch_id,
                  'name' => $loginuser_name,
                  'password' => Hash::make($loginpassword),
                  'email' => $update_staff->email_id,
                  'created_by' => $request->user()->user_id ?? 1,
                  'updated_by' => $request->user()->user_id ?? 1,
                ]);
              }

              

              if ($credential_check == 1 && $request->has('credential')) {
                     $existingCrendential = StaffCredentialModel::where('staff_id', $edit_id)
                          ->pluck('credential_id')
                          ->toArray();

                      $newCrendIds = $request->credential;
                      $removedCredential = array_diff($existingCrendential, $newCrendIds);

                      foreach ($removedCredential as $credenId) {
                          StaffCredentialModel::where('staff_id', $edit_id)
                              ->where('credential_id', $credenId)
                              ->update(['status' => 2]);
                      }
                      
                  foreach ($request->credential as $credential_id => $data) {
                    
                      // Skip if username is empty
                      if (!empty($data['username'])) {
                          StaffCredentialModel::updateOrCreate(
                            [
                              'staff_id'      => $edit_id,
                              'credential_id' => $credential_id,
                            ],
                            [
                             
                              'user_name'     => $data['username'],
                              'password'      => $data['password'] ?? null,
                              'url_link'      => $data['url'] ?? null,
                              'description'   => $data['description'] ?? null,
                              'created_by'    => $request->user()->user_id,
                              'updated_by'    => $request->user()->user_id,
                          ]);
                      }
                  }
              }

              return response()->json([
                'status'  => 200,
                'message' => 'Staff Stage ' . $stage . ' Updated successfully'
            ], 200);

          }elseif($stage == 8){

              $basic_salary = $request->basic_salary ?? 0;
              $per_hr_cost = $request->per_hr_cost ?? 0;
              $casual_leave_count = $request->casual_leave_count ?? 0;
              $per_day_salary = $request->per_day_salary ?? 0;
              $salary_type = $request->salary_type ?? 0;
              $salary_date = $request->salary_date ?? 0;
              $salary_company_id = $request->salary_company_id ?? 0;
              $salary_bank_id = $request->salary_bank_id ?? 0;
              $is_payslip = $request->is_payslip ?? 0;

              $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

              $update_staff->basic_salary = $basic_salary;
              $update_staff->per_hour_cost = $per_hr_cost;
              $update_staff->casual_leave_count_per_month = $casual_leave_count;
              $update_staff->per_day_salary = $per_day_salary;
              $update_staff->salary_type = $salary_type;
              $update_staff->salary_date = $salary_date;
              $update_staff->salary_company_id = $salary_company_id;
              $update_staff->salary_bank_id = $salary_bank_id;
              $update_staff->is_payslip = $is_payslip;

              // $update_staff->update();


               $staff_bank =StaffBankDetailsModel::updateOrCreate(
                ['staff_id'=>$sno],
                [
                  'bank_account_no'=>$request->staff_acc_no,
                  'account_holder'=>$request->staff_acc_holder,
                  'bank_name'=>$request->staff_bank_name,
                  'bank_branch'=>$request->staff_bank_branch,
                  'ifsc_code'=>$request->ifsc_code,
                  'created_by'=>$user_id,
                  'updated_by'=>$user_id
                ]
              );

            // statutory
            $staff_statutory =StaffStatutoryDetailsModel::updateOrCreate(
                ['staff_id'=>$sno],
                [
                  'uan_no'=>$request->uan_no,
                  'esi_no'=>$request->esi_no,
                  'created_by'=>$user_id,   
                  'updated_by'=>$user_id
                ]
              );

           
              return response()->json([
                  'status'  => 200,
                  'message' => 'Staff Stage ' . $stage . ' Updated successfully'
              ], 200);


          }elseif($stage == 9){
            
            $applied_position = $request->applied_position ? json_encode($request->applied_position) : NULL;
            $interview_company = $request->interview_company ? json_encode($request->interview_company) : NULL;
            $source_id = $request->source_id;
            $source_details = $request->source_details ?? Null;

            $data = $request->all();
            // initialize arrays
            $questions = [];
            $dependents = [];
            // loop through request inputs
            foreach ($data as $key => $value) {
                // 🎯 Handle main question inputs (like q_1, q_2)
                if (preg_match('/^q_(\d+)$/', $key, $matches)) {
                    $questionId = $matches[1];
                    $questions[$questionId] = $value ?? null;
                }
                // 🎯 Handle dependent question inputs (like depend_1, depend_2)
                if (preg_match('/^depend_(\d+)$/', $key, $matches)) {
                    $dependId = $matches[1];
                    $dependents[$dependId] = $value ?? null;
                }
            }
            // Combine both into one structured JSON
            $application_details = [
                'questions' => (object) $questions,
                'dependents' => (object) $dependents,
            ];

            $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

            $update_staff->applied_position = $applied_position ?? null;
            $update_staff->source_id = $source_id ?? null;
            $update_staff->source_details = $source_details ?? null;
            $update_staff->applied_company_ids = $interview_company ?? null;
            $update_staff->application_details = !empty($application_details) ? json_encode($application_details, JSON_PRETTY_PRINT) : null; 
            $update_staff->update();

            return response()->json([
                'status'  => 200,
                'message' => 'Staff Stage ' . $stage . ' Updated successfully'
            ], 200);
          }elseif($stage == 10){
            $document_checked =$request->document_checked ? json_encode($request->document_checked) : NULL;

            $completionPayload = $completionService->calculate((int) $edit_id);
              $update_staff->completion_percentage =  $completionPayload['overall']['percentage'];
              $update_staff->step_progress = json_encode(  $completionPayload, JSON_UNESCAPED_UNICODE);

            $update_staff->document_checklist = $document_checked; 
            $update_staff->update();

            return response()->json([
                'status'  => 200,
                'message' => 'Staff Stage ' . $stage . ' Updated successfully'
            ], 200);
          }

            // if($company_type == 2){
            //     $contact_person_name_first=$update_staff->contact_person_name ? $update_staff->contact_person_name[0] : NULL;
            //     $contact_person_no_first=$update_staff->contact_person_no ? $update_staff->contact_person_no[0] :NULL;
                
            //     if($add_staff->staff_image){
            //         $staff_image_url=url("staff_images/Buisness/{$update_staff->company_id}/{$update_staff->entity_id}/{$update_staff->staff_image}");
            //     }else{
            //           $staff_image_url=NULL;
            //     }

            //     $payload=[
            //       'sno' => $add_staff->sno,
            //       'entity_id' => $add_staff->entity_id,
            //       'staff_id' => $staff_id,
            //       'branch_id' => 1,
            //       'shift_time_id' => 1,
            //       'multi_branch_access'    => '',
            //       'sub_department_id' => $erp_division_id ?? 0,
            //       'department_id' => $erp_department_id ?? 0,
            //       'role_id' => $erp_role_id,
            //       'under_role_id' => $erp_under_role_id,
            //       'exp_type' => $work_exp_type,
            //       'staff_name' => $add_staff->staff_name,
            //       'mobile_no' => $add_staff->mobile_no,
            //       'alternative_no' => NULL,
            //       'email_id' => $add_staff->email_id,
            //       'gender' => $add_staff->gender,
            //       'dob' => $add_staff->dob,
            //       'date_of_joining' => $add_staff->date_of_joining,
            //       'contact_person_name' => $contact_person_name_first,
            //       'contact_person_no' => $contact_person_no_first,
            //       'martial_status' => $marital_status ?? null,
            //       'address' => $add_staff->address ?? null,
            //       'staff_image' => $staff_image_url ?? null,
            //       'attachment' =>  $attachments_url ?? null,
            //       'nick_name' => $add_staff->nick_name ?? null,
            //       'position_role' => $erp_job_role_id ?? null,
            //       'description' => null,
            //       'basic_salary' => $add_staff->basic_salary ?? null,
            //       'per_hour_cost' => $add_staff->per_hour_cost ?? null,
            //       'employee_skill_id' => 0,
            //       'login_access' => $login_access,
            //       'user_name' => $add_staff->user_name,
            //       'password'  => $add_staff->password,
            //       'work_company_name'  => $work_company_name,
            //       'work_position'  => $work_position,
            //       'work_exp_yrs'  => $work_exp_yrs,
            //       'work_salary'  => $work_salary,
            //       'exit_reason'  => $exit_reason,
            //       'work_st_date'  => $work_st_date,
            //       'work_end_date'  => $work_end_date,
            //       'credentials'  => $request->credential,
            //       'qualification_type'  => $qualification_type,
            //       'degree'  => $degree,
            //       'major'  => $major,
            //       'year'  => $pass_year,
            //       'university_name'  => $univ_name,
                  
            //     ];
            //       $this->dispatchWebhooks($payload, 1);
            // }


        }else{
          return response()->json([
            'status' => 401,
            'message' => 'Staff Not Found',
            'error_msg' => 'Staff Not Found',
            'data' => null,
          ], 401);
        }
      
    }else{
       return response()->json([
        'status' => 401,
        'message' => 'Something is Wrong Please Try Again',
        'error_msg' => 'Stage is Required',
        'data' => null,
      ], 401);
    }
    
    // return $add_staff->sno;
   
   
  }

  // dispatch webhook
  protected function dispatchWebhooks($broadcast, $userId = 1)
  {
    $webhook = SubErpWebhookModel::where('status', 0)->where('webhook_module', 'Staff_Add_Hook')->where('entity_id', $broadcast['entity_id'])->first();
    if ($webhook) {
      $dispatch = WebhookDispatchModel::create([
        'sub_erp_webhook_sno' => $webhook->sno,
        'dispatchable_type' => 'App\Models\StaffModel',
        'dispatchable_id' => $broadcast['sno'],
        'message_uuid' => $broadcast['sno'],
        'payload' => json_encode($broadcast),
        'status' => 0,
        'attempts' => 0,
        'created_by' => $userId,
        'updated_by' => $userId,
      ]);

      // broadcast creation once
      broadcast(new WebhookDispatchedEvent($dispatch));

      // enqueue the job
      try {
        $result = $this->sendWebhookNow($dispatch, $webhook);
        \Log::info("send result : " . json_encode($result));

        if (!$result['success']) {
          // If fails, dispatch to queue
          SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        }
      } catch (\Throwable $e) {
        // On any exception, fallback to queue
        SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        \Log::error("Webhook fallback to queue: " . $e->getMessage());
      }
    }
  }
  protected function dispatchUpdateTransferStaffWebhooks($broadcast, $userId = 1,$dispatchHook)
  {
    $webhook = SubErpWebhookModel::where('status', 0)->where('webhook_module', $dispatchHook)->where('entity_id', $broadcast['entity_id'])->first();
    if ($webhook) {
      $dispatch = WebhookDispatchModel::create([
        'sub_erp_webhook_sno' => $webhook->sno,
        'dispatchable_type' => 'App\Models\StaffModel',
        'dispatchable_id' => $broadcast['sno'],
        'message_uuid' => $broadcast['sno'],
        'payload' => json_encode($broadcast),
        'status' => 0,
        'attempts' => 0,
        'created_by' => $userId,
        'updated_by' => $userId,
      ]);

      // broadcast creation once
      broadcast(new WebhookDispatchedEvent($dispatch));

      // enqueue the job
      try {
        $result = $this->sendWebhookNow($dispatch, $webhook);
        \Log::info("send result : " . json_encode($result));

        if (!$result['success']) {
          // If fails, dispatch to queue
          SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        }
      } catch (\Throwable $e) {
        // On any exception, fallback to queue
        SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        \Log::error("Webhook fallback to queue: " . $e->getMessage());
      }
    }
  }

  protected function dispatchUpdateWebhooks(StaffModel $broadcast, $userId = 1, $dispatchHook)
  {
    $webhook = SubErpWebhookModel::where('status', 0)->where('webhook_module', $dispatchHook)->where('entity_id', $broadcast->entity_id)->first();
    if ($webhook) {
      $dispatch = WebhookDispatchModel::create([
        'sub_erp_webhook_sno' => $webhook->sno,
        'dispatchable_type' => get_class($broadcast),
        'dispatchable_id' => $broadcast->sno,
        'message_uuid' => $broadcast->sno,
        'payload' => $broadcast->toArray(),
        'status' => 0,
        'attempts' => 0,
        'created_by' => $userId,
        'updated_by' => $userId,
      ]);

      // broadcast creation once
      // broadcast(new WebhookDispatchedEvent($dispatch));

      // enqueue the job
      try {
        $result = $this->sendWebhookNow($dispatch, $webhook);
        \Log::info("send result : " . json_encode($result));

        if (!$result['success']) {
          // If fails, dispatch to queue
          SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        }
      } catch (\Throwable $e) {
        // On any exception, fallback to queue
        SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        \Log::error("Webhook fallback to queue: " . $e->getMessage());
      }
    }
  }

  protected function dispatchUpdateExistStaffWebhooks($broadcast, $userId = 1, $dispatchHook)
  {
    $webhook = SubErpWebhookModel::where('status', 0)->where('webhook_module', $dispatchHook)->where('entity_id', $broadcast['entity_id'])->first();
    if ($webhook) {
      $dispatch = WebhookDispatchModel::create([
        'sub_erp_webhook_sno' => $webhook->sno,
        'dispatchable_type' => 'App\Models\StaffModel',
        'dispatchable_id' => $broadcast['sno'],
        'message_uuid' => $broadcast['sno'],
        'payload' => json_encode($broadcast),
        // 'payload' => json_encode($broadcast, JSON_UNESCAPED_UNICODE),
        'status' => 0,
        'attempts' => 0,
        'created_by' => $userId,
        'updated_by' => $userId,
      ]);

      // broadcast creation once
      // broadcast(new WebhookDispatchedEvent($dispatch));

      // enqueue the job
      try {
        $result = $this->sendWebhookNow($dispatch, $webhook);
        \Log::info("send result : " . json_encode($result));

        // if (!$result['success']) {
        //     // If fails, dispatch to queue
        //     SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        // }
      } catch (\Throwable $e) {
        // On any exception, fallback to queue
        SendWebhookJob::dispatch($dispatch->sno)->onQueue('webhooks');
        \Log::error("Webhook fallback to queue: " . $e->getMessage());
      }
    }
  }

  public function getCompanyStaff(Request $request)
  {
    $companyId = $request->company_id;

    $staff = StaffModel::where('egc_staff.company_id', $companyId)
      ->where('egc_staff.status', '!=', 2)
      ->select(
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
      ->where('egc_staff.sno', '>', 1)
      ->orderBy('egc_staff.staff_name', 'ASC')
      ->get();

    $html = view('content.hr_management.hr_enroll.manage_staff.company_staff_list_table', compact('staff'))->render();

    return response()->json([
      'staff_html' => $html
    ]);
  }

  public function bulkUpdateStaffId(Request $request)
  {
    foreach ($request->updates as $row) {
      $alreadyExist = StaffModel::where([
        ['staff_id', '=', $row['staff_id']],
        ['sno', '!=', $row['sno']],
      ])->first();

      $alreadyTimeChampExist = StaffModel::where([
        ['timechamp_id', '=', $row['timechamp_id']],
        ['sno', '!=', $row['sno']],
      ])->first();

      if ($alreadyExist) {
        // already exists
        $already_staff_id = $alreadyExist->sno;
        StaffModel::where('sno', $already_staff_id)
          ->update(['staff_id' => Null]);
      }
      if ($alreadyTimeChampExist) {
        // already exists
        $already_time_staff_id = $alreadyTimeChampExist->sno;
        StaffModel::where('sno', $already_time_staff_id)
          ->update(['timechamp_id' => Null]);
      }
      StaffModel::where('sno', $row['sno'])
        ->update([
          'staff_id' => $row['staff_id'],
          'timechamp_id' => $row['timechamp_id'],
        ]);
    }

    return response()->json(['success' => true]);
  }

  function staffDataById(Request $request)
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

    return response()->json([
      'status' => 200,
      'data' => $staff
    ]);
  }

  public function UpdateWelcomeStatus(Request $request)
  {
    $staff_id = $request->staff_id;

    $newStaff =  StaffModel::where('sno', $staff_id)->first();

    if ($newStaff) {
      $newStaff->welcome_status = 1;
      $newStaff->update();
    }
    return response()->json(['success' => true]);
  }

  protected function sendWebhookNow($dispatch, $hook)
  {
    $payload = $dispatch->payload ?? [];
    $bodyString = json_encode($payload);

    $dispatch->increment('attempts');
    $dispatch->update(['status' => 1, 'last_attempt_at' => now()]);
    broadcast(new WebhookDispatchedEvent($dispatch));

    $timestamp = now()->getTimestamp();
    $signature = $hook->secret ? hash_hmac('sha256', $timestamp . '.' . $bodyString, $hook->secret) : null;

    $headers = array_merge(
      is_array($hook->headers) ? $hook->headers : json_decode($hook->headers ?? '[]', true),
      [
        'X-WEBHOOK-TIMESTAMP' => $timestamp,
        'X-WEBHOOK-SIGNATURE' => $signature,
        'X-IDEMPOTENCY-KEY' => $dispatch->message_uuid,
        'Accept' => 'application/json',
      ]
    );

    try {
      $response = Http::withHeaders($headers)
        ->timeout(15)
        ->post($hook->url, $payload);

      WebhookDispatchAttemptModel::create([
        'webhook_dispatch_sno' => $dispatch->sno,
        'http_status' => $response->status(),
        'request_headers' => json_encode($headers),
        'request_body' => $bodyString,
        'response_body' => $response->body(),
      ]);

      if ($response->successful()) {
        $dispatch->update([
          'status' => 2,
          'http_status' => $response->status(),
          'last_response' => $response->body(),
          'next_attempt_at' => null
        ]);
        broadcast(new WebhookDispatchedEvent($dispatch));
        return ['success' => true];
      } else {
        $dispatch->update([
          'status' => 3,
          'last_response' => 'Webhook failed. Will retry automatically.'
        ]);
        broadcast(new WebhookDispatchedEvent($dispatch));
        return ['success' => false];
      }
    } catch (\Throwable $e) {
      \Log::error("Immediate webhook send failed: " . $e->getMessage());
      $dispatch->update([
        'status' => 3,
        'last_response' => 'Webhook failed. Will retry automatically.'
      ]);
      broadcast(new WebhookDispatchedEvent($dispatch));
      return ['success' => false];
    }
  }


  public function TestMessageSend(Request $request)
  {

    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
      'email'    => 'boolean',
      'sms'     => 'boolean',
      'whatsapp' => 'boolean'
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

    $helper = new \App\Helpers\Helpers();
    $branch = $helper->general_setting_data();


    $email     = $request->email;
    $sms      = $request->sms;
    $whatsapp = $request->whatsapp;
    $lead_mobile = '9677482114';

    $whatsappApi = DB::table('egc_api_integration')->where('api_key', 'egc_whatsapp_meta_api_1')->where('status', 0)->orderBy('sno', 'desc')->first();
    if ($whatsappApi) {
      $apiFields = json_decode($whatsappApi->api_fields, true);
      $WhatsAppBusinessAccountId = '';
      $accessToken = '';
      $fromPhoneNumberId = '';
      foreach ($apiFields as $field) {
        if ($field['key'] === 'waba_id') {
          $WhatsAppBusinessAccountId = $field['value'];
        } elseif ($field['key'] === 'access_token') {
          $accessToken = $field['value'];
        } elseif ($field['key'] === 'phone_number_id') {
          $fromPhoneNumberId = $field['value'];
        }
      }
    } else {
      $WhatsAppBusinessAccountId = '';
      $accessToken = '';
      $fromPhoneNumberId = '';
    }


    // return response([
    //   'status'    => 401,
    //   'message'   => 'data',
    //   'error_msg' => null,
    //   'data'      => $WhatsAppBusinessAccountId,
    // ], 404);

    // sms send
    // if ($sms == 1) {

    //     $result_sms = SmsTemplateModel::where('status', 0)->where('template_name', '4_PhDiZone_Proposal_Template')->first();
    //     $authkey = $result_sms ? $result_sms->authkey : '';
    //     $sender_id = $result_sms ? $result_sms->sender_id : '';
    //     $template_id = $result_sms ? $result_sms->template_id : '';
    //     $country_code = $result_sms ? $result_sms->country_code : '';
    //     $message_content = $result_sms ? $result_sms->sms_template_messagecontent : '';
    //     $mobile_number = $country_code . $lead_mobile;

    //     if ($lead_email_id && $lead_mobile) {
    //           $message_contentEmail = "Link sent to Email & WhatsApp";
    //       } elseif ($lead_email_id) {
    //           $message_contentEmail = "Link sent to your Email";
    //       } elseif ($lead_mobile) {
    //           $message_contentEmail = "Link sent to your WhatsApp";
    //       } else {
    //           $message_contentEmail = "Link ready. Please contact us";
    //       }
    //     $proposal_sms_id=$proposalData->proposal_id ;

    //     $proposal_Content= $proposal_sms_id.', '.$message_contentEmail;

    //     // Replace placeholders with actual values
    //     $replacement_values = [$lead_name,$proposal_Content,$cre_mobile];
    //     $message_content = preg_replace_callback(
    //       '/\{#var#\}/',
    //       function () use (&$replacement_values) {
    //         return array_shift($replacement_values);
    //       },
    //       $message_content
    //     );
    //   // return $message_content;
    //     $route = "default";
    //     $postData = array(
    //       'authkey' => $authkey,
    //       'mobiles' => $mobile_number,
    //       'message' => $message_content,
    //       'sender' => $sender_id,
    //       'route' => $route,
    //     );

    //     // API URL
    //     $url = "https://api.msg91.com/api/sendhttp.php?authkey=$authkey&sender=$sender_id&route=$route&message=" . urlencode($message_content) . "&mobiles=$mobile_number&DLT_TE_ID=$template_id";

    //     // Initialize the resource
    //     $ch = curl_init();
    //     curl_setopt_array($ch, array(
    //       CURLOPT_URL => $url,
    //       CURLOPT_RETURNTRANSFER => true,
    //       CURLOPT_POST => true,
    //       CURLOPT_POSTFIELDS => $postData,
    //       CURLOPT_SSL_VERIFYHOST => 0,
    //       CURLOPT_SSL_VERIFYPEER => 0,
    //     ));

    //     // Get response
    //     $response = curl_exec($ch);
    //     // return $response;
    //     $err = curl_error($ch);
    //     curl_close($ch);
    // }


    //   Send email 

    if ($email == 1) {
      $emailTemplate_id = 1;
      $emailTemplate = EmailTemplateModel::where('status', 0)->where('sno', $emailTemplate_id)->first();

      // Gender condition
      $genderPrefix = 'Mr/Ms'; // Default value
      // if ($lead->lead_gender == 1) {
      //   $genderPrefix = 'Mr';
      // } elseif ($lead->lead_gender == 2) {
      //   $genderPrefix = 'Ms';
      // }




      $baseURL = url('/');
      $content = $emailTemplate->email_subject;

      $socialMediaDetails = json_decode($branch->social_media_details, true);
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

      $dynamicSubject = str_replace('#company_name', 'Elysium Groups', $emailTemplate->email_name);

      $content = str_replace('#employee_name', $staff_name ?? 'Ak Max', $content);
      $content = str_replace('#staffId', $StaffId ?? 'EGC04063', $content);
      $content = str_replace('#username', $user_name ?? 'test', $content);
      $content = str_replace('#password',  $password ?? 'test@123', $content);
      $content = str_replace('#login_link', $baseURL ?? "url", $content);
      $content = str_replace('#hr_contact',  $cre_mobile ?? '8220011465', $content);
      $content = str_replace('#hr_email',  $cre_mobile ?? 'email_id', $content);
      $branchNo = $branch->hr_head_no;
      $branchemail = $branch->hr_head_mail_id;
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
        'salesMobile' => $branchData->cre_mobile ?? '8220011465',
      ];

      $to_address = 'vetri4vijayan@gmail.com';
      $from_address = 'elysiumtechnology@elysium.community';
      $from_name =  'Elysium Groups ';

      // return $mailData;
      Mail::to($to_address)->send(new EGCMail($mailData, $from_address, $from_name));
    }


    //  Whatsapp  Send
    if ($whatsapp == 1 && ($WhatsAppBusinessAccountId && $accessToken && $fromPhoneNumberId)) {


      //   $templateName  = "hello_world";
      $templateName  = "welcome_all_test";

      $hasHeader  = false; // Example flag: set based on template
      $hasBody    = false; // Example flag: set based on template
      $hasFooter  = false; // Example flag: set based on template
      $hasButton  = false;
      $components = [];
      $couponCode = " ";
      date_default_timezone_set('Asia/Kolkata'); // Set your timezone (e.g., 'Asia/Kolkata')
      $currentHour = (int) date('H');            // Get current hour in 24-hour format as an integer
      // $currentHour = date('H'); // Get current hour in 24-hour format
      if ($currentHour >= 5 && $currentHour < 12) {
        $greeting = 'Good Morning';
      } elseif ($currentHour >= 12 && $currentHour < 18) {
        $greeting = 'Good Afternoon';
      } else {
        $greeting = 'Good Evening';
      }

      if ($templateName == 'welcome_lead') {
        $headerImage = 'https://erp.elysiumtechnologies.com/public/assets/phdizone_images/OnBoard.jpg';
        // $headerImage =  url('public/assets/phdizone_images/OnBoard.jpg');
        $couponCode  = 'NOOFFER';
        $buttonIndex = 1;
        $bodyText    = [
          [
            'type'           => 'text',
            'text'           => 'testing',
            'parameter_name' => 'lead_name',
          ],
          [
            'type'           => 'text',
            'text'           => 'testing',
            'parameter_name' => 'sales_name',
          ],
          [
            'type'           => 'text',
            'text'           => 'testing',
            'parameter_name' => 'contact_name',
          ]
        ];
        $hasHeader = true;
        $hasBody   = true;
        $hasButton = false;
      } else {
        // $templateName  = "hello_world";
        $bodyText = [];
        $hasHeader = false;
        $hasBody = false;
        $hasFooter = false;
        $hasButton = false;
      }



      // Add Header if required
      if ($hasHeader) {
        $components[] = [
          'type'       => 'header',
          'parameters' => [
            [
              'type'  => 'image',
              'image' => [
                'link' => $headerImage, // Dynamically set header image
              ],
            ],
          ],
        ];
      }

      // Add Body if required
      if ($hasBody) {
        $components[] = [
          'type'       => 'body',
          'parameters' => $bodyText,
        ];
      }

      // Add Footer if required
      if ($hasButton) {
        $components[] = [
          'type'       => 'button',
          'sub_type'   => 'url',
          'index'      => 0,
          'parameters' => [
            [
              'type' => 'text',
              'text' => $proposal_key,
            ],
          ],
        ];
      }


      $whatsappApi = DB::table('egc_whatsapp_api_configure')->orderBy('sno', 'desc')->first();
      $dynamicParameters         = $templateParameters[$templateName] ?? [];
      // $WhatsAppBusinessAccountId = $whatsappApi->waba_id ?? '';
      // $accessToken               = $whatsappApi->access_tokken ?? '';
      // $fromPhoneNumberId         = $whatsappApi->phonenumber_id ?? '';
      //  return $dynamicParameters;
      $apiUri = 'https://graph.facebook.com/v21.0/' . $fromPhoneNumberId . '/messages';

      $countryCode  =  '+91';
      $to           = $lead_mobile;
      $languageCode = 'en';
      //   $languageCode = 'en_us';


      if (strpos($to, $countryCode) !== 0) {
        $to = $countryCode . $to;
      }

      $message = 'test~message';
      if (empty($components)) {
        $payload = [
          'messaging_product' => 'whatsapp',
          'to'                => $to,
          'type'              => 'template',
          'template'          => [
            'name'     => $templateName,
            'language' => [
              'code' => $languageCode,
            ],
          ],
        ];
      } else {
        $payload = [
          'messaging_product' => 'whatsapp',
          'to'                => $to,
          'type'              => 'template',
          'template'          => [
            'name'       => $templateName,
            'language'   => [
              'code' => $languageCode,
            ],
            'components' => $components,
          ],
        ];
      }
      // return $accessToken;
      $response =  $this->sendRequestToWhatsApp($apiUri, $accessToken, $payload);
      if ($response['status'] == 200) {
        $log_branch_id = 0;
        $unique_id = 0;
        $source = 'Testing';
        $module_name = 'Manage Staff';
        $log_phone_no = $to;
        $log_template_name = $templateName;

        $save_log = $this->WhatsappSendLog(
          $response,
          $payload,
          $log_phone_no,
          $log_template_name,
          0,
          $log_branch_id,
          $unique_id,
          $source,
          $module_name
        );
      } else {
        return response([
          'status' => 404,
          'message' => 'Failed to send message',
          'error_msg' => $response['error'],
        ], 400);
      }
    }

    // If the operation was successful, return a success response
    return response([
      'status'    => 200,
      'message'   => "Message sent successfully",
      'error_msg' => null,
      'data'      => null,
    ], 200);
  }

  private function sendRequestToWhatsApp($url, $token, $data)
  {
    try {
      $client = new \GuzzleHttp\Client();
      $response = $client->post($url, [
        'headers' => [
          'Authorization' => 'Bearer ' . $token,
          'Content-Type' => 'application/json',
        ],
        'json' => $data,
      ]);

      return [
        'status' => $response->getStatusCode(),
        'data' => json_decode($response->getBody(), true),
      ];
    } catch (\Exception $e) {
      return [
        'status' => 400,
        'error' => $e->getMessage(),
      ];
    }
  }


  public function updateTimestampReport(Request $request)
  {
    // Validate incoming request
    $validator = Validator::make($request->all(), [
      'date' => 'required|date', // Ensure the date is a valid date format
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 401,
        'message' => 'Incorrect format input fields',
        'error_msg' => $validator->errors()->all(),
        'data' => null,
      ], 200);
    }

    // Get user_id from the request or default to 1 if not available
    $user_id = $request->user()->user_id ?? 1;

    // Get pagination parameters or default values
    $pageNumber = $request->pageNumber ?? 1;
    $perPage = $request->perPage ?? 500;
    $employee_id = $request->employee_id ?? '';
    $timestampDate = $request->date ? date('Y-m-d', strtotime($request->date)) : null;

    // Ensure timestampDate is not null or invalid
    if (is_null($timestampDate)) {
      return response()->json([
        'status' => 400,
        'message' => 'Invalid date format provided.',
      ], 200);
    }

    // Construct the API URL
    $api_url = 'https://elysiumgroups.sites2.timechamp.io/swagger/api/activity/getDailyTrackingSummaryData?DateFrom=' . $timestampDate . '&DateTo=' . $timestampDate . '&EmployeeIds=' . $employee_id . '&PageNumber=' . $pageNumber . '&PageSize=' . $perPage . '&Offset';

    try {
      // Replace with your actual bearer token
      $bearerToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1laWQiOiI1ZjQ3MzQ2Mi00YmNmLTRkOTUtYTIzNy02YTBhNDI0MjNmMDEiLCJDb21wYW55IjoiZjFhZjcyMDctNDZjYS00ZGZjLWE5ZmQtOGZkNGE2OTU0MGRiIiwibmJmIjoxNzY0Mzg0NTM4LCJleHAiOjIxOTYzODQ1MzgsImlhdCI6MTc2NDM4NDUzOH0.piEX7hwYuoZIIKSksFiyGcWKK09JDrYmm-h5ffvLGbk';

      // Send the GET request with Bearer token in Authorization header
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $bearerToken,
      ])->get($api_url);

      if (!$response->successful()) {
        return response()->json([
          'status' => 500,
          'message' => 'Failed to fetch data from external API.',
          'error' => $response->body(),
        ], 200);
      }

      // Check if the response contains data
      $erpData = $response->json()['data'] ?? [];
      if (empty($erpData)) {
        return response()->json([
          'status' => 404,
          'message' => 'No data found in API response.',
        ], 200);
      }

      // Update or create the timestamp record
      StaffTimestampModel::updateOrCreate(
        ['time_sheet_date' => $timestampDate],
        [
          'staff_timestamp_reports' => json_encode($erpData),
          'created_by' => $user_id,
          'updated_by' => $user_id,
        ]
      );

      return response()->json([
        'status' => 200,
        'message' => 'Staff timestamp updated successfully.',
      ]);
    } catch (\Throwable $e) {
      // Log the error for debugging
      // \Log::error('Error fetching staff timestamp: ' . $e->getMessage());

      return response()->json([
        'status' => 500,
        'message' => 'Something went wrong while fetching staff timestamp.',
        'error' => $e->getMessage(),
      ], 200);
    }
  }
  private function WhatsappSendLog(
    array $response,
    array $payload,
    string $phone_no,
    string $template_name,
    int $user_id,
    int $branch_id = 0,
    int $unique_id = 0,
    ?string $source = null,
    ?string $module_name = null,
  ) {
    $messageId = $response['data']['messages'][0]['id'] ?? null;

    return WhatsappTemplateLogModel::create([
      'phone_no' => $phone_no,
      'entity_id' => $branch_id,
      'unique_id' => $unique_id,
      'id_source' => $source,
      'module_name' => $module_name,
      'template_name' => $template_name,
      'message_date_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
      'type' => 'template',
      'from' => 1,
      'wama_id' => $messageId,
      'payload' => json_encode($payload),
      'created_by' => $user_id,
      'updated_by' => $user_id,
      'message_status' => 'sent',
    ]);
  }

  public function editPayrollDetails($employeeSno)
  {
    $data = DB::table('egc_staff')
      ->where('egc_staff.sno', $employeeSno)
      ->first();

    $staffBank = StaffBankDetailsModel::where('staff_id', $employeeSno)->first();

    $staffStatutory = StaffStatutoryDetailsModel::where('staff_id', $employeeSno)->first();

    $staffBankCompany = DB::table('egc_company_bank_accounts')->where('status', 0)->where('sno', $data->salary_bank_id)->first();
    $salaryAccounts =
      DB::table('egc_staff_salary_accounts')
      ->where('staff_id', $employeeSno)
      ->where('status', 0)
      ->get();
    $singleSalaryAccount =
      StaffSalaryAccountModel::where('staff_id',$employeeSno)
      ->where('status',0)
      ->where('is_primary',1)
      ->first();

      

    $data->staff_bank_company_id = $staffBankCompany ? $staffBankCompany->company_id : NULL;

    

    return response()->json([
      'data' => $data,
      'staffBank' => $staffBank,
      // 'payrollRules' => $payrollRules,
      'staffStatutory' => $staffStatutory,
      'salaryAccounts' => $salaryAccounts,
      'singleSalaryAccount' => $singleSalaryAccount
    ]);
  }

 

  public function savePayroll(Request $request)
  {
    $request->validate([
      'employee_sno' => 'required',
      'salary_type'  => 'required|in:1,2',
       'is_multiple_account' => 'required|in:0,1'
    ]);

    $user_id = $request->user()->user_id ?? 1;

    DB::transaction(function () use ($request, $user_id) {

      $sno = $request->employee_sno;
      $isMultiple = $request->is_multiple_account ?? 0;

      // ===============================
      // STAFF DETAILS 
      // ================================
      $staff = StaffModel::where('sno', $sno)->first();

      // ================================
      // UPDATE STAFF 
      // ================================
      $staff->basic_salary = $request->basic_salary ?? 0;
      $staff->per_hour_cost = $request->per_hr_cost ?? 0;
      $staff->per_day_salary = $request->per_day_salary ?? 0;
      $staff->casual_leave_count_per_month = $request->casual_leave_count ?? 0;
      $staff->salary_type = $request->salary_type ?? 1;
      $staff->salary_date = $request->salary_date ?? 0;
      $staff->salary_company_id = $request->salary_company_id ?? 0;
      $staff->salary_bank_id = $request->salary_bank_id ?? null;
      $staff->is_payslip = $request->is_payslip ?? 0;
      $staff->is_multiple_account =$isMultiple;
      $staff->updated_by = $user_id;
      $staff->save();

      // ================================
      // ONLY IF SALARY TYPE = BANK
      // ================================
      if ($request->salary_type == 2) {

        // BANK DETAILS
        StaffBankDetailsModel::updateOrCreate(
          ['staff_id' => $sno],
          [
            'bank_account_no' => $request->staff_acc_no,
            'account_holder'  => $request->staff_acc_holder,
            'bank_name'       => $request->staff_bank_name,
            'bank_branch'     => $request->staff_bank_branch,
            'ifsc_code'       => $request->ifsc_code,
            'created_by'      => $user_id,
            'updated_by'      => $user_id
          ]
        );

        // STATUTORY DETAILS
        StaffStatutoryDetailsModel::updateOrCreate(
          ['staff_id' => $sno],
          [
            'uan_no'     => $request->uan_no,
            'esi_no'     => $request->esi_no,
            'pan_no'     => $request->pan_no,
            'aadhar_no'  => $request->aadhar_no,
            'created_by' => $user_id,
            'updated_by' => $user_id
          ]
        );
      } else {

        // StaffBankDetailsModel::where('staff_id', $sno)->delete();
        // StaffStatutoryDetailsModel::where('staff_id', $sno)->delete();
        // StaffBankDetailsModel::where('staff_id', $sno)->update(['status' => 2]);
        // StaffStatutoryDetailsModel::where('staff_id', $sno)->update(['status' => 2]);
      }

      $accounts = [];

      $existingIds = [];


      if($isMultiple == 0)
      {
        
          $grossSalary = (float)$request->basic_salary;

          $perDay =
              round($grossSalary / 30, 2);

          $perHour =
              round($grossSalary / (30 * 8), 2);

          $accountSno = $request->single_salary_account_sno;

        if($accountSno)
        {
            StaffSalaryAccountModel::where('sno',$accountSno)
                ->update([
                    'salary_company_id' =>
                        $request->salary_company_id,

                    'salary_bank_id' => $request->salary_bank_id ?? NULL,

                    'gross_salary' =>
                        $grossSalary,

                    'salary_type' =>$request->salary_type ?? 1,
                    'per_day_salary' =>$request->per_day_salary ?? 0,
                    'per_hour_cost' =>$request->per_hr_cost ?? 0,

                    'updated_by' =>
                        $user_id
                ]);
        }else{

            StaffSalaryAccountModel::create([

                'staff_id' => $sno,

                'salary_company_id' =>
                    $request->salary_company_id,

                'salary_bank_id' =>
                    $request->salary_bank_id,

                'gross_salary' =>
                    $grossSalary,

                'salary_type' =>$request->salary_type ?? 1,
                'per_day_salary' =>$request->per_day_salary ?? 0,
                'per_hour_cost' =>$request->per_hr_cost ?? 0,

                'is_primary' => 1,

                'is_payslip' =>
                    $request->is_payslip ?? 0,

                'status' => 0,

                'created_by' => $user_id,
                'updated_by' => $user_id
            ]);
        }
      }

      if($isMultiple == 1)
      {
          $accounts = json_decode($request->salary_accounts,true) ?? [];

          $totalSalary = 0;
          $totalPerDay = 0;
          $totalPerHour = 0;

          $primaryCompany = null;
          $primaryBank = null;

          $salaryBankId =
        !empty($row['salary_bank_id'])
            ? (int)$row['salary_bank_id']
            : null;

          foreach($accounts as $row){
              if(!empty($row['sno'])){
                  $account =StaffSalaryAccountModel::where('sno',$row['sno'])->first();
                  if($account){
                      $account->update([
                          'salary_company_id' =>$row['salary_company_id'],
                          'salary_bank_id' =>$salaryBankId,
                          'gross_salary' => $row['gross_salary'],
                          'salary_type' =>$request->salary_type ?? 1,
                          'per_day_salary' =>$row['per_day_salary'],
                          'per_hour_cost' => $row['per_hour_cost'],
                          'is_primary' =>$row['is_primary'],
                          'status' => 0,
                          'updated_by' => $user_id
                      ]);
                      $existingIds[] = $account->sno;
                  }
              }else{
                  $account = StaffSalaryAccountModel::create([
                      'staff_id' =>$sno,
                      'salary_company_id' => $row['salary_company_id'],
                      'salary_bank_id' => $salaryBankId,
                      'gross_salary' => $row['gross_salary'],
                      'salary_type' =>$request->salary_type ?? 1,
                      'per_day_salary' => $row['per_day_salary'],
                      'per_hour_cost' => $row['per_hour_cost'],
                      'is_primary' => $row['is_primary'],
                      'status' => 0,
                      'created_by' => $user_id,
                      'updated_by' => $user_id
                  ]);

                  $existingIds[] = $account->sno;
              }

              $totalSalary += (float)$row['gross_salary'];
              $totalPerDay += (float)$row['per_day_salary'];
              $totalPerHour += (float)$row['per_hour_cost'];

              if($row['is_primary'] == 1)
              {
                  $primaryCompany = $row['salary_company_id'];
                  $primaryBank = !empty($row['salary_bank_id'])
                      ? (int)$row['salary_bank_id']
                      : null;
              }
          }

          StaffSalaryAccountModel::where('staff_id',$sno)
          ->whereNotIn('sno',$existingIds )
          ->update([
              'status' => 2,
              'updated_by' => $user_id
          ]);

          $staff->basic_salary =$totalSalary;
          $staff->per_day_salary =$totalPerDay;
          $staff->per_hour_cost =$totalPerHour;
          $staff->salary_company_id =$primaryCompany;
          $staff->salary_bank_id =$primaryBank;
          $staff->save();
      }

    });

    return response()->json([
      'status' => true,
      'message' => 'Payroll updated successfully'
    ]);
  }


  public function birthdayList(Request $request)
  {


    $query = StaffModel::where('egc_staff.status', '!=', 2)
      ->select(
        'egc_staff.*',
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
      ->where('egc_staff.sno', '>', 0)
      ->whereIn('egc_staff.status', [0, 1])
      ->orderByRaw('MONTH(egc_staff.dob) ASC, DAY(egc_staff.dob) ASC');

    // if ($request->date) {
    //     $query->whereMonth('egc_staff.dob', date('m', strtotime($request->date)));
    // }

    if ($request->date) {
      $month = \Carbon\Carbon::createFromFormat('M-Y', $request->date)->format('m');
      $query->whereMonth('egc_staff.dob', $month);
    }



    $employees = $query->get()->map(function ($emp) {

      if ($emp->gender == 1) {
        $defaultPath = asset('assets/egc_images/auth/user_2.png');
      } else {
        $defaultPath = asset('assets/egc_images/auth/user_7.png');
      }

      if ($emp->company_type == 1) {
        $relativePath = 'staff_images/Management/' . $emp->staff_image;
      } else {
        $relativePath = 'staff_images/Buisness/' . $emp->company_id . '/' . $emp->entity_id . '/' . $emp->staff_image;
      }

      //  Correct physical path
      $fullPath = public_path($relativePath);

      //  Correct URL
      $filePath = asset($relativePath);

      //  Proper check
      $isStaffImage = ($emp->staff_image && file_exists($fullPath)) ? 1 : 0;
      $helper = new \App\Helpers\Helpers();
      $general_setting = $helper->general_setting_data();

      return [
        'name' => $emp->staff_name,
        'nickname' => $emp->nick_name,
        'job_role' => $emp->job_role_name,
        'department' => $emp->department_name,
        'company' => $emp->company_name ?? $general_setting->title,
        'company_color' => $emp->company_base_color ?? '#ab2b22',
        'profile_image' => $isStaffImage ? $filePath : $defaultPath,
        'isStaffImage' => $isStaffImage,
        'filePath' => $filePath,
        'date_of_birth' => date('d M', strtotime($emp->dob)),
        'dob_raw' => date('m-d', strtotime($emp->dob)),
      ];
    });

    return response()->json($employees);
  }

  public function getEmployeeStructure($employee_id)
  {
    try {

      // EMPLOYEE DETAILS
      $employee = StaffModel::where('egc_staff.status', '!=', 2)
        ->select(
          'egc_staff.*',
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
        ->where('egc_staff.sno', $employee_id)
        ->first();

      if (!$employee) {

        return response()->json([
          'status' => false,
          'message' => 'Employee not found'
        ], 404);
      }

      // PAYROLL COMPONENTS
      $components = DB::table('egc_payroll_components')
        ->where('status', 0)
        ->orderBy('display_order', 'ASC')
        ->get();

      // EXISTING STRUCTURE
      $existingStructure = DB::table('egc_payroll_employee_structures')
        ->where('employee_id', $employee_id)
        ->where('is_active', 1)
        ->first();

      $structureDetails = [];

      if ($existingStructure) {
        $structureDetails = DB::table(
          'egc_payroll_employee_structure_details'
        )
          ->where(
            'structure_id',
            $existingStructure->id
          )
          ->get()
          ->keyBy('component_id');
      }

      // MAP COMPONENT AMOUNTS
      $components = $components->map(function ($component) use (
        $structureDetails
      ) {

        $detail = $structureDetails[$component->id] ?? null;
        $component->amount =
          $detail->amount ?? 0;
        $component->is_override =
          $detail->is_override ?? 0;
        return $component;
      });

      return response()->json([
        'status' => true,
        'employee' => $employee,
        'structure' => $existingStructure,
        'components' => $components

      ]);
    } catch (\Exception $e) {

      return response()->json([

        'status' => false,

        'message' => $e->getMessage()

      ], 500);
    }
  }

  public function transferStaff(Request $request)
  {
      DB::beginTransaction();

      try {

        //  return response()->json([
        //       'status' => false,
        //       'message' => $request
        //   ]);

          $staff = StaffModel::findOrFail(
              $request->staff_sno
          );

          $oldData = $staff->toArray();
          $oldCompanyType = $oldData['company_type'];
          $oldEntity = $oldData['entity_id'];
          $companyType = $request->company_type;
           $user_id = $request->user()->user_id ?? 0;
          
          if($request->branch_id == $oldData['branch_id']){
                 return response()->json([
                    'status' => false,
                    'message' => "Can't Transfer this Staff in same Branch "
                ]);
          }

          $companyId = 0;
          $entityId = 0;
          $branchId = 0;

          if ($companyType == 2) {
              $companyId = $request->company_id;
              $entityId = $request->entity_id;
              $branchId = $request->branch_id;
          }

          $departmentId = $request->department_id;

          $divisionId =$request->division_id;

          $jobRoleId =$request->job_role_id;
          $userRoleId =$request->role_id;
          $reason_id =$request->reason_id;
          $reason_comment =$request->reason_comment;

          $oldImage = $staff->staff_image;

          if ($oldImage) {
              if ($staff->company_type == 1) {
                  $oldPath = public_path('staff_images/Management/'.$oldImage);
              } else {
                $oldPath =public_path('staff_images/Buisness/'.$staff->company_id.'/'.$staff->entity_id.'/'.$oldImage );
              }

              if ($companyType == 1) {
                  $newFolder = public_path('staff_images/Management');
              } else {
                  $newFolder = public_path('staff_images/Buisness/'.$companyId.'/'.$entityId);
              }

              if (!File::exists($newFolder)) {
                  File::makeDirectory($newFolder, 0777,true);
              }

              if (File::exists($oldPath)) {
                  File::move($oldPath,$newFolder.'/'.$oldImage);
              }
          }

          
        

            $staff->company_type = $companyType;
            $staff->company_id = $companyId;
            $staff->entity_id = $entityId;
            $staff->branch_id = $branchId;

            $staff->department_id = $departmentId;
            $staff->division_id = $divisionId;
            $staff->job_role_id = $jobRoleId;
            $staff->role_id = $userRoleId;

            $staff->transfer_status = 1;
            $staff->updated_by = $user_id;

            $staff->save();
            
            StaffTransferLogModel::create([
              'staff_id' => $staff->sno,
              'old_company_type' => $oldData['company_type'],
              'old_role_id' => $oldData['role_id'] ?? 0,
              'old_company_id' => $oldData['company_id'],
              'old_entity_id' => $oldData['entity_id'],
              'old_branch_id' => $oldData['branch_id'],
              'old_department_id' => $oldData['department_id'],
              'old_division_id' => $oldData['division_id'],
              'old_job_role_id' => $oldData['job_role_id'],
              'new_company_type' => $companyType,
              'new_company_id' => $companyId,
              'new_entity_id' => $entityId,
              'new_branch_id' =>$branchId,
              'new_department_id' => $departmentId,
              'new_division_id' =>$divisionId,
              'new_job_role_id' => $jobRoleId,
              'new_role_id' => $userRoleId,
              'transfer_date' => now()->toDateString(),
              'transfer_reason_id' => $reason_id,
              'transfer_reason_comment' => $reason_comment,
              'transferred_by' =>$user_id,
              'created_by' =>$user_id,
              'updated_by' =>$user_id,
            ]);

            DB::commit();

            if ($companyType == 2) {
              $contct_person_decode =$staff->contact_person_name ? json_decode($staff->contact_person_name) : null;
              $contct_person_mob_decode =$staff->contact_person_no ? json_decode($staff->contact_person_no) : null;
              $contact_person_name_first = $contct_person_decode ? $contct_person_decode[0] : NULL;
              $contact_person_no_first = $contct_person_mob_decode ? $contct_person_mob_decode[0] : NULL;
              if ($staff->staff_image) {
                $staff_image_url = url("staff_images/Buisness/{$staff->company_id}/{$staff->entity_id}/{$staff->staff_image}");
              } else {
                $staff_image_url = NULL;
              }

              $branchData= DB::table('egc_branch')->where('status',0)->where('sno',$staff->branch_id)->first();

              $roleData= DB::table('egc_user_role')->where('sno',$staff->role_id)->first();
              $erp_role_id =$roleData ? $roleData->erp_role_id : 0;

              $departData= DB::table('egc_department')->where('sno',$staff->department_id)->first();
              $erp_department_id =$departData ? $departData->erp_department_id : 0;
              
              $divisionData= DB::table('egc_division')->where('sno',$staff->division_id)->first();
              $erp_division_id =$divisionData ? $divisionData->erp_division_id : 0;
              
              $jobRoleData= DB::table('egc_job_role')->where('sno',$staff->job_role_id)->first();
              $erp_job_role_id =$jobRoleData ? $jobRoleData->erp_job_role_id : 0;

              $work_company_name = DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('company_name') ?? [];
              $work_position =DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('position') ?? [];
              $work_exp_yrs =DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('year_of_experience') ?? [];
              $work_salary =DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('salary') ?? [];
              $exit_reason =DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('exit_reason') ?? [];
              $work_end_date =DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('start_date') ?? [];
              $work_st_date =DB::table('egc_staff_work_info')->where('status',0)->where('sno',$staff->sno)->pluck('end_date') ?? [];

              $qualification_type =DB::table('egc_staff_education_info')->where('status',0)->where('sno',$staff->sno)->pluck('qualification_type') ?? [];
              $degree =DB::table('egc_staff_education_info')->where('status',0)->where('sno',$staff->sno)->pluck('degree_name') ?? [];
              $major =DB::table('egc_staff_education_info')->where('status',0)->where('sno',$staff->sno)->pluck('major') ?? [];
              $pass_year =DB::table('egc_staff_education_info')->where('status',0)->where('sno',$staff->sno)->pluck('year') ?? [];
              $univ_name =DB::table('egc_staff_education_info')->where('status',0)->where('sno',$staff->sno)->pluck('university_name') ?? [];
              

              $payload = [
                'sno' => $staff->sno,
                'entity_id' => $staff->entity_id,
                'staff_id' => $staff->staff_id,
                'branch_id' => $branchData ? $branchData->erp_branch_id : 1,
                'shift_time_id' => 1,
                'multi_branch_access'    => '',
                'sub_department_id' => $erp_division_id ?? 0,
                'department_id' => $erp_department_id ?? 0,
                'role_id' => $erp_role_id,
                'under_role_id' => null,
                'exp_type' => $staff->exp_type,
                'staff_name' => $staff->staff_name,
                'mobile_no' => $staff->mobile_no,
                'alternative_no' => NULL,
                'email_id' => $staff->email_id,
                'gender' => $staff->gender,
                'dob' => $staff->dob,
                'date_of_joining' => $staff->date_of_joining,
                'contact_person_name' => $contact_person_name_first,
                'contact_person_no' => $contact_person_no_first,
                'martial_status' => $staff->martial_status ?? null,
                'address' => $staff->address ?? null,
                'staff_image' => $staff_image_url ?? null,
                'attachment' =>   null,
                'nick_name' => $staff->nick_name ?? null,
                'position_role' => $erp_job_role_id ?? null,
                'description' => $staff->description ?? null,
                'basic_salary' => $staff->basic_salary ?? null,
                'per_hour_cost' => $staff->per_hour_cost ?? null,
                'employee_skill_id' => 0,
                'login_access' => $staff->login_access,
                'user_name' => $staff->user_name,
                'password'  => $staff->password,
                'work_company_name'  => $work_company_name,
                'work_position'  => $work_position,
                'work_exp_yrs'  => $work_exp_yrs,
                'work_salary'  => $work_salary,
                'exit_reason'  => $exit_reason,
                'work_st_date'  => $work_st_date,
                'work_end_date'  => $work_end_date,
                'credentials'  => $credential ?? null,
                'qualification_type'  => $qualification_type,
                'degree'  => $degree,
                'major'  => $major,
                'year'  => $pass_year,
                'university_name'  => $univ_name,

              ];
              // $this->dispatchWebhooks($payload, 1);
              $this->dispatchUpdateTransferStaffWebhooks($payload, $user_id, 'Add_Transfer_Staff');

               
            }

            if($oldCompanyType == 2){
                 $payloadExist = [
                'sno' => $staff->sno,
                'staff_id' => $staff->sno,
                'entity_id' => $oldEntity,
                'dep_reason'    => 'Internal Transfer',
                'notice_start_date' =>  null,
                'notice_end_date' =>   null,
                'staff_last_date' =>  date('Y-m-d'),
                'status' =>  8,

              ];
              $this->dispatchUpdateExistStaffWebhooks($payloadExist, $user_id, 'Update_Exist_Staff');
            }

              return response()->json([
                  'status' => true,
                  'message' => 'Staff transferred successfully'
              ]);

      } catch (\Exception $e) {

          DB::rollback();

          return response()->json([
              'status' => false,
              'message' => $e->getMessage()
          ]);
      }
  }

  
 public function getTransferData(Request $request)
{
    try {

        $staff = StaffModel::select(
            'egc_staff.sno',
            'egc_staff.staff_id',
            'egc_staff.staff_name',
            'egc_staff.mobile_no',
            'egc_staff.email_id',
            'egc_staff.company_type',
            'egc_staff.company_id',
            'egc_staff.entity_id',
            'egc_staff.branch_id',
            'egc_staff.department_id',
            'egc_staff.division_id',
            'egc_staff.job_role_id',
            'egc_staff.role_id',
            'egc_staff.staff_image',
            'egc_company.company_name',
            'egc_entity.entity_name',
            'egc_branch.branch_name',
            'egc_department.department_name',
            'egc_division.division_name',
            'egc_job_role.job_position_name as job_role_name',
            'egc_user_role.role_name'
        )
        ->leftJoin( 'egc_company', 'egc_company.sno', '=', 'egc_staff.company_id')
        ->leftJoin('egc_entity', 'egc_entity.sno','=', 'egc_staff.entity_id')
        ->leftJoin( 'egc_branch','egc_branch.sno', '=','egc_staff.branch_id' )
        ->join( 'egc_department','egc_department.sno', '=', 'egc_staff.department_id' )
        ->join('egc_division', 'egc_division.sno', '=', 'egc_staff.division_id')
        ->join('egc_job_role','egc_job_role.sno', '=', 'egc_staff.job_role_id' )
        ->leftJoin('egc_user_role','egc_user_role.sno', '=', 'egc_staff.role_id')
        ->where('egc_staff.sno', $request->staff_sno)
        ->first();

        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Staff not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'sno' => $staff->sno,
            'staff_id' => $staff->staff_id,
            'staff_name' => $staff->staff_name,
            'mobile_no' => $staff->mobile_no,
            'email_id' => $staff->email_id,
            'profile_image_url' => $staff->profile_image_url,
            'company_type' => $staff->company_type,
            'company_id' => $staff->company_id,
            'entity_id' => $staff->entity_id,
            'branch_id' => $staff->branch_id,
            'department_id' => $staff->department_id,
            'division_id' => $staff->division_id,
            'job_role_id' => $staff->job_role_id,
            'role_id' => $staff->role_id,
            'company_name' => $staff->company_name ?? '-',
            'entity_name' => $staff->entity_name ?? '-',
            'branch_name' => $staff->branch_name ?? '-',
            'department_name' => $staff->department_name ?? '-',
            'division_name' => $staff->division_name ?? '-',
            'job_role_name' => $staff->job_role_name ?? '-',
            'role_name' => $staff->role_name ?? '-',
        ]);

    } catch (\Exception $e) {
        \Log::error('Staff Transfer Data Error', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong'.$e->getMessage()
        ], 500);
    }
}


public function pendingExitStaff(Request $request)
{
    $cacheKey = 'exit_staff_pending_all';

    $result = Cache::remember($cacheKey, now()->addMinutes(5), function () {

        $entities = DB::table('egc_entity')
            ->where('status', 0)
            ->whereNotNull('entity_base_url')
            ->where('entity_base_url', '!=', '')
            ->select(
                'sno',
                'entity_name',
                'entity_base_url'
            )
            ->get();

        if ($entities->isEmpty()) {
            return [
                'total_pending' => 0,
                'staff' => []
            ];
        }

        $staff = [];

        foreach ($entities as $entity) {

            try {

                $url = rtrim($entity->entity_base_url, '/') . '/api/exist-staff-list';

                $response = Http::acceptJson()
                    ->timeout(15)
                    ->retry(2, 1000)
                    ->get($url);

                if (!$response->successful()) {

                    Log::warning('Exit Staff API returned non-success response.', [
                        'entity_id' => $entity->sno,
                        'entity_name' => $entity->entity_name,
                        'status' => $response->status(),
                        'url' => $url,
                    ]);

                    continue;
                }

                $rows = $response->json();

                if (!is_array($rows)) {
                    continue;
                }

                foreach ($rows as $row) {

                    if (empty($row['external_uuid'])) {
                        continue;
                    }

                    $externalUuid = trim($row['external_uuid']);

                    // Avoid duplicates if the same staff exists in multiple ERPs
                    if (isset($staff[$externalUuid])) {
                        continue;
                    }

                    $staff[$externalUuid] = [
                        'external_uuid'      => $externalUuid,
                        'entity_id'          => $entity->sno,
                        'entity_name'        => $entity->entity_name,
                        'staff_name'         => $row['staff_name'] ?? '',
                        'staff_last_date'    => $row['staff_last_date'] ?? '',
                        'notice_start_date'  => $row['notice_start_date'] ?? '',
                        'notice_end_date'    => $row['notice_end_date'] ?? '',
                        'department_name'    => $row['department_name'] ?? '',
                        'job_position_name'  => $row['job_position_name'] ?? '',
                        'staff_image'        => $row['staff_image'] ?? '',
                        'is_exist_egc'       => $row['is_exist_egc'] ?? 0,
                    ];
                }

            } catch (\Throwable $e) {

                Log::error('Exit Staff API Connection Failed', [
                    'entity_id'   => $entity->sno,
                    'entity_name' => $entity->entity_name,
                    'url'         => $entity->entity_base_url,
                    'message'     => $e->getMessage(),
                ]);

                continue;
            }
        }

        return [
            'total_pending' => count($staff),
            'staff' => array_values($staff),
        ];
    });

    return response()->json([
        'status' => true,
        'total_pending' => $result['total_pending'],
        'staff' => $result['staff'],
        'generated_at' => now()->format('Y-m-d H:i:s'),
    ]);
}

public function checkStaffIdExists(Request $request)
  {
    $employee_id = $request->input('employee_id');

    // Assuming you have a Lead model with a mobile field
    // $exists = StaffModel::where('mobile_no', $staff_mobile)->exists();
    $staff = StaffModel::where('staff_id', $employee_id)
      ->where('status', '!=', 2)
      ->first();

    if ($staff) {
      return response()->json([
        'message' => 'Staff Id already assigned!',
        'data'    => 1,
        'staff'   => $staff ,
      ], 200);
    } else {
      return response()->json([
        'message' => 'Staff Id available!',
        'data'    => 0,
        'staff'   => null, 
      ], 200);
    }
  }

public function checkStaffIdExistsEdit(Request $request)
  {
    $employee_id = $request->input('employee_id');
    $staff_id = $request->input('id');

    // Assuming you have a Lead model with a mobile field
    // $exists = StaffModel::where('mobile_no', $staff_mobile)->exists();
    $staff = StaffModel::where('staff_id', $employee_id)
      ->where('status', '!=', 2)
      ->whereNot('sno', $staff_id)
      ->first();

    if ($staff) {
      return response()->json([
        'message' => 'Staff Id already assigned!',
        'data'    => 1,
        'staff'   => $staff ,
      ], 200);
    } else {
      return response()->json([
        'message' => 'Staff Id available!',
        'data'    => 0,
        'staff'   => null, 
      ], 200);
    }
  }

  public function casteListByCommunity(Request $request){

      $religion_id = $request->religion_id;
      $community_id = $request->community_id;
      $caste = DB::table('egc_caste')->where('status', 0)->where('religion_id', $religion_id)->where('community_id', $community_id)->orderBy('caste_name', 'asc')->get();
      return response([
        'status' => 200,
        'message' => null,
        'error_msg' => null,
        'data' => $caste,
      ], 200);

  }


  public function AddCasteSetting (Request $request)
{
    $validator = Validator::make($request->all(), [
        'religion_id'  => 'required|integer|exists:egc_religion,sno',
        'community_id' => 'required|integer|exists:egc_community,sno',
        'caste_name'   => 'required|string|max:150',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 422,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors()
        ], 422);
    }

    try {

        DB::beginTransaction();

        $religionId  = (int) $request->religion_id;
        $communityId = (int) $request->community_id;
        $casteName   = trim($request->caste_name);

        $user_id = $request->user()->user_id ?? 0;
        // Duplicate check
        $exists = DB::table('egc_caste')
            ->where('religion_id', $religionId)
            ->where('community_id', $communityId)
            ->where('status', '!=',2)
            ->whereRaw('LOWER(caste_name)=?', [Str::lower($casteName)])
            ->exists();

        if ($exists) {
            DB::rollBack();
            return response()->json([
                'status'  => 409,
                'message' => 'Caste already exists for this Religion and Community.'
            ], 409);
        }

        $id = DB::table('egc_caste')->insertGetId([
            'religion_id' => $religionId,
            'community_id'=> $communityId,
            'caste_name'  => $casteName,
            'created_by'   => $user_id,
            'updated_by'   => $user_id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Caste added successfully.',
            'data' => [
                'id' => $id,
                'caste_name' => $casteName
            ]
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'status'  => 500,
            'message' => 'Internal server error.'
        ], 500);
    }
}

  public function AddRecuirmentRolesSetting (Request $request)
{
    $validator = Validator::make($request->all(), [
        'job_role_name'   => 'required|string|max:150',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 422,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors()
        ], 422);
    }

    try {

        DB::beginTransaction();

        $jobrole_name   = trim($request->job_role_name);

        $user_id = $request->user()->user_id ?? 0;
        // Duplicate check
        $exists = DB::table('egc_recruitment_roles')
            ->where('status', '!=',2)
            ->whereRaw('LOWER(jobrole_name)=?', [Str::lower($jobrole_name)])
            ->exists();

        if ($exists) {
            DB::rollBack();
            return response()->json([
                'status'  => 409,
                'message' => 'Job Role already exist.'
            ], 409);
        }

        $id = DB::table('egc_recruitment_roles')->insertGetId([
            'jobrole_name'  => $jobrole_name,
            'created_by'   => $user_id,
            'updated_by'   => $user_id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Job Role added successfully.',
            'data' => [
                'id' => $id,
                'jobrole_name' => $jobrole_name
            ]
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 500,
            'message' => 'Internal server error.'
        ], 500);
    }
}

Private function saveStaffPayrollStructure($staff_id ,$salary_account_id,$gross_salary,$effective_from,$details,$payroll_template_sno,$user_id)
{


    try {

        $effectiveDate = $effective_from;

        $earnings = 0;
        $deductions = 0;
        $employerContribution = 0;

        foreach ($details as $detail) {
            $amount = (float) ($detail['calculated_amount'] ?? 0);
            if ($detail['component_type'] == 'earning') {
                $earnings += $amount;
            } elseif ($detail['component_type'] == 'deduction') {
                $deductions += $amount;
            } elseif ($detail['component_type'] == 'employer_contribution') {
                $employerContribution += $amount;
            }
        }

        $netSalary = $earnings - $deductions;
        $ctcAmount = $earnings + $employerContribution;

        $structure = PayrollStaffStructureModel::where('employee_sno',$staff_id)
                    ->where('salary_account_id',$salary_account_id)
                    ->whereDate('effective_from', $effectiveDate)
                    ->where('status', 0)
                    ->first();

        $fixedGrossSalary = $gross_salary;
        $workingDays = 30;
        $perDaySalary = $fixedGrossSalary / $workingDays;
        $perDaySalary = ($perDaySalary * 100) / 100;
        $perDaySalaryCost = round($perDaySalary,2);
        
        $workingHours = 8;
        $perHour = $fixedGrossSalary / (30 * $workingHours);
        $perHour = ($perHour * 100) / 100;
        $perHourCost = round($perHour,2);
        

        if ($structure) {
            $structure->update([
                'payroll_template_sno' => $payroll_template_sno,
                'gross_salary' => $gross_salary,
                'per_day_salary' => $perDaySalaryCost,
                'per_hour_cost' => $perHourCost,
                'total_earnings' => round($earnings, 2),
                'total_deductions' => round($deductions, 2),
                'net_salary' => round($netSalary, 2),
                'employer_contribution' => round($employerContribution, 2),
                'ctc_amount' => round($ctcAmount, 2),
                'revision_reason' => 'Joining Salary',
                'updated_by' => $user_id ?? 0,
            ]);
        } else {

            PayrollStaffStructureModel::where('employee_sno',$staff_id)
            ->where('salary_account_id', $salary_account_id)
            ->where('is_current', 1)
            ->update([
                'is_current' => 0,
                'effective_to' => Carbon::parse($effectiveDate)->subDay()->format('Y-m-d'),
            ]);

            $structure = PayrollStaffStructureModel::create([
                            'employee_sno' => $staff_id,
                            'salary_account_id' =>$salary_account_id,
                            'payroll_template_sno' => $payroll_template_sno,
                            'structure_code' =>'SAL-' . time() . rand(100, 999),
                            'structure_name' => 'Employee Salary Structure',
                            'gross_salary' =>  $gross_salary,
                            'per_day_salary' => $perDaySalaryCost,
                            'per_hour_cost' => $perHourCost,
                            'total_earnings' => round($earnings, 2),
                            'total_deductions' =>round($deductions, 2),
                            'net_salary' => round($netSalary, 2),
                            'employer_contribution' => round($employerContribution, 2),
                            'ctc_amount' => round($ctcAmount, 2),
                            'revision_reason' => 'Joining Salary',
                            'effective_from' => $effectiveDate,
                            'is_current' => 1,
                            'status' => 0,
                            'created_by' => $user_id ?? 0,
            ]);
        }

        $existingDetails =PayrollStaffStructureDetailModel::where('payroll_employee_structure_sno',$structure->sno)
                            ->where('status', 0)
                            ->get()
                            ->keyBy('payroll_component_sno');

        $currentComponentIds = [];

        foreach ($details as $index => $detail) {
            $componentId = $detail['payroll_component_sno'];
            $currentComponentIds[] = $componentId;

            $detailData = [
                'payroll_rule_sno' =>  $detail['payroll_rule_sno'] !== '' ? $detail['payroll_rule_sno']  : null,
                'component_type' => $detail['component_type'],
                'calculation_type' => $detail['calculation_type'],
                'calculated_on' =>NULL,
                'percentage_value' => $detail['percentage_value'] ?? 0,
                'fixed_amount' => $detail['fixed_amount'] ?? $detail['calculated_amount'],
                'calculated_amount' => $detail['calculated_amount'],
                'monthly_variable' => $detail['monthly_variable'] ?? 0,
                'include_in_gross' => $detail['include_in_gross'] ?? 0,
                'include_in_ctc' => $detail['include_in_ctc'] ?? 1,
                'include_in_payslip' => $detail['include_in_payslip'] ?? 1,
                'display_order' => $detail['display_order'] ?? ($index + 1),
                'remarks' => NULL,
                'updated_by' =>$user_id ?? 0,
            ];

            if (isset($existingDetails[$componentId])) {
                $existingDetails[$componentId]->update($detailData);
            } else {
                PayrollStaffStructureDetailModel::create(
                    array_merge(
                        $detailData,
                        [
                            'payroll_employee_structure_sno' => $structure->sno,
                            'payroll_component_sno' => $componentId,
                            'status' => 0,
                            'created_by' => $user_id ?? 0,
                        ]
                    )
                );
            }
        }

        PayrollStaffStructureDetailModel::where('payroll_employee_structure_sno',$structure->sno)
          ->where('status', 0)
          ->whereNotIn('payroll_component_sno',$currentComponentIds)
          ->update([
              'status' => 2,
              'updated_by' => $user_id ?? 0,
          ]);


        // log
          StaffSalaryAppraisalHistoryModel::create([
              'employee_sno'=>$staff_id,
              'salary_account_id'=>$salary_account_id,
              'payroll_template_sno'=>$payroll_template_sno,
              'effective_from'=>Carbon::parse($effective_from)->startOfMonth(),
              'gross_salary'=>$gross_salary,
              'appraisal_type'=> 0,
              'appraisal_unit'=>'Rs',
              'appraisal_value'=>NULL,
              'appraisal_reason'=>'Initial',
              'variable_amount'=>0,
              'variable_months'=>0,
              'processed'=>1,
              'status'=>0,
              'created_by'=>$user_id,
              'updated_by'=>$user_id

          ]);

  

        return $structure;

    } catch (\Exception $e) {
          throw $e;
    }
}

// view Staff 
 public function View($id)
  {
    $data =  StaffModel::where('egc_staff.status', '!=', 2)
      ->select(
        'egc_staff.*',
        'egc_entity.entity_name',
        'egc_entity.entity_short_name',
        'egc_company.company_name',
        'egc_company.company_base_color',
        'egc_department.department_name',
        'egc_division.division_name',
        'egc_user_role.role_name',
        'egc_languages.name as mother_tongue',
        'egc_job_role.job_position_name as job_role_name',
      )
      ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
      ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
      ->leftJoin('egc_user_role', 'egc_staff.role_id', 'egc_user_role.sno')
      ->leftJoin('egc_languages', 'egc_staff.mother_tongue', 'egc_languages.sno')
      ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
      ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
      ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
      ->where('egc_staff.sno', $id)
      ->first();
    $languages_ids = $data->languages ? json_decode($data->languages) : [];
    $hobby_ids = $data->hobby ? json_decode($data->hobby) : [];
    $language_known = DB::table('egc_languages')->whereIn('sno', $languages_ids)->pluck('name');
    $hobbies = DB::table('egc_hobbies')->whereIn('sno', $hobby_ids)->pluck('hobby_name');
    $staff_family = DB::table('egc_staff_family')->where('staff_id', $id)->where('status', '!=', 2)->first();
    $data->language_known = $language_known;
    $data->hobbies = $hobbies;
    // family details
    $data->father_name = $staff_family ? $staff_family->father_name : '';
    $data->father_occup = $staff_family ? $staff_family->father_occup : '';
    $data->mother_name = $staff_family ? $staff_family->mother_name : '';
    $data->mother_occup = $staff_family ? $staff_family->mother_occup : '';
    $data->has_children = $staff_family ? $staff_family->has_children : '';
    $data->children_count = $staff_family ? $staff_family->children_count : '';
    $data->has_siblings = $staff_family ? $staff_family->has_siblings : '';

    $other_credential = DB::table('egc_staff_credential')
      ->select('egc_staff_credential.*', 'egc_credential.credential_name')
      ->join('egc_credential', 'egc_staff_credential.credential_id', 'egc_credential.sno')
      ->where('egc_staff_credential.staff_id', $id)
      ->where('egc_staff_credential.status', '!=', 2)
      ->get();
    $data->other_credential = $other_credential;
    $data->contact_person_name = $data->contact_person_name ? json_decode($data->contact_person_name) : [];
    $data->contact_person_no = $data->contact_person_no ? json_decode($data->contact_person_no) : [];
    $contact_person_relation_ids = $data->contact_person_relation ? json_decode($data->contact_person_relation) : [];
    $relation = DB::table('egc_relationship_type')->whereIn('sno', $contact_person_relation_ids)->pluck('relationship_name');
    $data->relationship = $relation;

    $work_details = DB::table('egc_staff_work_info')
      ->select('egc_staff_work_info.*')
      ->where('egc_staff_work_info.staff_id', $id)
      ->where('egc_staff_work_info.status', '!=', 2)
      ->orderBy('egc_staff_work_info.sno', 'asc')
      ->get();

    $data->work_details = $work_details;

    $documents = DB::table('egc_staff_attachment')
      ->where('egc_staff_attachment.staff_id', $id)
      ->select('egc_staff_attachment.*', 'egc_documents.document_name')
      ->where('egc_staff_attachment.status', '!=', 2)
      ->join('egc_documents', 'egc_staff_attachment.document_id', '=', 'egc_documents.sno')
      ->get();
    $documentData = [];
    foreach ($documents as $doc) {
      $fileNames = json_decode($doc->attachment_name); // Decode the JSON file names
      $documentData[] = [
        'document_name' => $doc->document_name,
        'files' => array_map(function ($fileName) use ($doc) {
          return asset("staff_attachments/{$doc->staff_id}/{$doc->document_id}/{$fileName}");
        }, $fileNames)
      ];
    }

    // $qualification = DB::table('egc_staff_education_info')
    // ->select('egc_staff_education_info.*','egc_education.education as qualification_name')
    // ->where('egc_staff_education_info.status', '!=', 2)
    // ->where('egc_staff_education_info.staff_id', $id)
    // ->join('egc_education', 'egc_staff_education_info.qualification_type', '=', 'egc_education.sno')
    // ->get();

    $qualification = DB::table('egc_staff_education_info')
      ->select('egc_staff_education_info.*', 'egc_qualification_level.qualification_level_name as qualification_name', 'egc_major.major_name')
      ->where('egc_staff_education_info.status', '!=', 2)
      ->where('egc_staff_education_info.staff_id', $id)
      // ->join('egc_education', 'egc_staff_education_info.qualification_type', '=', 'egc_education.sno')
      ->join('egc_qualification_level', 'egc_staff_education_info.qualification_type', '=', 'egc_qualification_level.sno')
      ->leftJoin('egc_major', 'egc_staff_education_info.major', '=', 'egc_major.sno')
      ->get();

    $document_checkList = DB::table('egc_document_checklist')
      ->select('egc_document_checklist.*')
      ->where('egc_document_checklist.status', '!=', 2)
      ->get();
    $document_checklist_snos = $data->document_checklist ? json_decode($data->document_checklist) : [];

    foreach ($document_checkList as $document) {
      if (in_array($document->sno, $document_checklist_snos)) {
        $document->is_checked = 1;
      } else {
        $document->is_checked = 0;
      }
    }
    $data->documents = $documentData;
    $data->qualification = $qualification;
    $data->document_checkList = $document_checkList;
    if (!$data) {
      return response([
        'status' => 404,
        'message' => 'staff not found',
        'error_msg' => 'No record found with the given ID.',
        'data' => null,
      ], 404);
    }

    return response([
      'status' => 200,
      'message' => 'Staff fetched successfully',
      'error_msg' => null,
      'data' => $data,
    ], 200);
  }

 public function ViewDetailStaff($id)
{
    try {

        $data = StaffModel::where('egc_staff.status', '!=', 2)
            ->where('egc_staff.sno', $id)
            ->select(
                'egc_staff.*',
                'egc_entity.entity_name',
                'egc_entity.entity_short_name',
                'egc_company.company_name',
                'egc_company.company_base_color',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_user_role.role_name',
                'egc_languages.name as mother_tongue',
                'egc_blood_group.blood_group as blood_group_name',
                'egc_nationality.nationality_name',
                'egc_religion.religion_name',
                'egc_community.community_name',
                'egc_community.community_code',
                'egc_caste.caste_name as caste',
                'egc_staff.vehicle_check as has_vehicle',
                'egc_job_role.job_position_name as job_role_name'
            )
            ->leftJoin( 'egc_company','egc_staff.company_id','=','egc_company.sno')
            ->leftJoin('egc_entity','egc_staff.entity_id','=','egc_entity.sno')
            ->leftJoin('egc_user_role','egc_staff.role_id','=','egc_user_role.sno')
            ->leftJoin('egc_languages','egc_staff.mother_tongue', '=','egc_languages.sno')
            ->leftJoin('egc_department','egc_staff.department_id','=','egc_department.sno')
            ->leftJoin('egc_division','egc_staff.division_id','=','egc_division.sno')
            ->leftJoin('egc_job_role','egc_staff.job_role_id','=','egc_job_role.sno')
            ->leftJoin('egc_blood_group','egc_staff.blood_group','=','egc_blood_group.sno')
            ->leftJoin('egc_nationality','egc_staff.nationality_id','=','egc_nationality.sno')
            ->leftJoin('egc_religion','egc_staff.religion_id','=','egc_religion.sno')
            ->leftJoin('egc_community','egc_staff.community_id','=','egc_community.sno')
            ->leftJoin('egc_caste','egc_staff.caste_id','=','egc_caste.sno')
            ->first();

        if (!$data) {
            return response()->json([
                'status'    => 404,
                'message'   => 'Staff not found',
                'error_msg' => 'No active staff record was found with the given ID.',
                'data'      => null,
            ], 404);
        }

        $helper = new \App\Helpers\Helpers();
        $general_setting = $helper->general_setting_data();

        if ($data->company_type == 1) {
          $data->company_name = $general_setting->title;
          $data->company_base_color = '#ab2b22';
          $data->company_logo = $general_setting->logo;
        }

        $defaultImage = asset(
            $data->gender == 2
                ? 'assets/egc_images/auth/user_7.png'
                : 'assets/egc_images/auth/user_2.png'
        );

        if (!empty($data->staff_image)) {
            if ($data->company_type == 1) {
                $profileImagePath ='staff_images/Management/' .$data->staff_image;
            } else {
                $profileImagePath = 'staff_images/Buisness/' . $data->company_id . '/' . $data->entity_id . '/' . $data->staff_image;
            }
            $data->profile_image_url = asset($profileImagePath);
        } else {
            $data->profile_image_url = $defaultImage;
        }

        $languageIds = $this->decodeJsonArray(
            $data->languages ?? null
        );

        $languageKnown = collect();

        if (!empty($languageIds)) {
            $languageKnown = DB::table('egc_languages')
                ->whereIn('sno', $languageIds)
                ->pluck('name');
        }
        $data->language_known = $languageKnown->values();

        $hobbyIds = $this->decodeJsonArray(
            $data->hobby ?? null
        );

        $hobbies = collect();
        if (!empty($hobbyIds)) {
            $hobbies = DB::table('egc_hobbies')
                ->whereIn('sno', $hobbyIds)
                ->pluck('hobby_name');
        }
        $data->hobbies = $hobbies->values();

        /*
        |--------------------------------------------------------------------------
        | 5. FAMILY DETAILS
        |--------------------------------------------------------------------------
        */
        $staffFamily = DB::table('egc_staff_family')
            ->where('staff_id', $id)
            ->where('status', '!=', 2)
            ->first();

        /*
         * Keep the same fields your old view already uses.
         */

        $data->anniversary_date = $staffFamily ? ($staffFamily->anniversary_date ? date('d-M-Y', strtotime($staffFamily->anniversary_date)) : NULL) : '';

        

        $data->father_name = $staffFamily->father_name ?? null;
        $data->father_occup = $staffFamily->father_occup ?? null;
        $data->mother_name =$staffFamily->mother_name ?? null;
        $data->mother_occup = $staffFamily->mother_occup ?? null;
        $data->has_children = $staffFamily->has_children ?? null;
        $data->children_count =$staffFamily->children_count ?? null;
        $data->has_siblings =$staffFamily->has_siblings ?? null;
     
        $data->spouse_name = $staffFamily->spouse_name ?? null;
        $data->spouse_mobile =$staffFamily->spouse_mobile ?? null;
        $data->spouse_dob =$staffFamily->spouse_dob ?? null;
        $data->spouse_is_working =$staffFamily->spouse_working ?? null;
        $data->spouse_designation =$staffFamily->spouse_designation ?? null;
        $data->spouse_company_name =$staffFamily->spouse_company_name ?? null;

        $children = [];


        // if ($staffFamily) {

        //     $childrenNames = $this->decodeJsonArray(
        //         $staffFamily->children_name ?? null
        //     );

        //     $childrenDob = $this->decodeJsonArray(
        //         $staffFamily->children_dob ?? null
        //     );

        //     $childrenGender = $this->decodeJsonArray(
        //         $staffFamily->children_gender ?? null
        //     );


        //     $childrenMax = max(
        //         count($childrenNames),
        //         count($childrenDob),
        //         count($childrenGender)
        //     );


        //     for ($i = 0; $i < $childrenMax; $i++) {

        //         $children[] = [

        //             'name' =>
        //                 $childrenNames[$i] ?? null,

        //             'dob' =>
        //                 $childrenDob[$i] ?? null,

        //             'gender' =>
        //                 $childrenGender[$i] ?? null,

        //         ];

        //     }

        // }

        $children = [];

        if ($staffFamily && !empty($staffFamily->children_details)) {

            $childrenDetails = json_decode($staffFamily->children_details, true);

            if (is_array($childrenDetails)) {

                foreach ($childrenDetails as $child) {

                    $children[] = [
                        'name' => $child['child_name'] ?? null,
                        'dob' => !empty($child['child_dob'])
                            ? date('d-M-Y', strtotime($child['child_dob']))
                            : null,
                        'std' => $child['child_std'] ?? null,
                        'year' => $child['child_year'] ?? null,
                    ];

                }

            }

        }

        $data->children = $children;



        /*
        |--------------------------------------------------------------------------
        | SIBLINGS
        |--------------------------------------------------------------------------
        */

        $siblings = [];


        // if ($staffFamily) {

        //     $siblingNames = $this->decodeJsonArray(
        //         $staffFamily->sibling_name ?? null
        //     );

        //     $siblingRelations = $this->decodeJsonArray(
        //         $staffFamily->sibling_relation ?? null
        //     );

        //     $siblingOccupations = $this->decodeJsonArray(
        //         $staffFamily->sibling_occupation ?? null
        //     );


        //     $siblingsMax = max(
        //         count($siblingNames),
        //         count($siblingRelations),
        //         count($siblingOccupations)
        //     );


        //     for ($i = 0; $i < $siblingsMax; $i++) {

        //         $siblings[] = [

        //             'name' =>
        //                 $siblingNames[$i] ?? null,

        //             'relation' =>
        //                 $siblingRelations[$i] ?? null,

        //             'occupation' =>
        //                 $siblingOccupations[$i] ?? null,

        //         ];

        //     }

        // }


        $siblings = [];

        if ($staffFamily && !empty($staffFamily->siblings_detail)) {

            $siblingDetails = json_decode($staffFamily->siblings_detail, true);

            if (is_array($siblingDetails)) {

                foreach ($siblingDetails as $sibling) {

                    $siblings[] = [
                        'name'       => $sibling['sibling_name'] ?? null,
                        'relation'       => $sibling['sibling_type'] ?? null,
                        'occupation'        => $sibling['sibling_std'] ?? null,
                        'income'     => $sibling['sibling_income'] ?? null,
                    ];

                }

            }

        }

        $data->siblings = $siblings;


        /*
        |--------------------------------------------------------------------------
        | 6. EMERGENCY / CONTACT PERSON DETAILS
        |--------------------------------------------------------------------------
        */
        $contactNames = $this->decodeJsonArray( $data->contact_person_name ?? null);

        $contactNumbers = $this->decodeJsonArray( $data->contact_person_no ?? null);

        $contactRelationIds = $this->decodeJsonArray( $data->contact_person_relation ?? null);


        /*
         * Get relationships indexed by ID.
         */

        $relationships = collect();


        if (!empty($contactRelationIds)) {
            $relationships = DB::table('egc_relationship_type')
                ->whereIn('sno', $contactRelationIds)
                ->pluck('relationship_name','sno');
        }

        $emergencyContacts = [];

        $contactMax = max(
            count($contactNames),
            count($contactNumbers),
            count($contactRelationIds)
        );

        for ($i = 0; $i < $contactMax; $i++) {
            $relationId = $contactRelationIds[$i] ?? null;

            $emergencyContacts[] = [
                'name' => $contactNames[$i] ?? null,
                'mobile' => $contactNumbers[$i] ?? null,
                'relation_id' =>$relationId,
                'relation' => $relationId ? ($relationships[$relationId] ?? null) : null,
            ];

        }

        $data->emergency_contacts = $emergencyContacts;
        $data->contact_person_name = $contactNames;
        $data->contact_person_no = $contactNumbers;
        $data->relationship = collect($contactRelationIds)
                ->map(function ($relationId) use ($relationships) {
                    return $relationships[$relationId] ?? null;
                })
                ->values();
        /*
        |--------------------------------------------------------------------------
        | 7. WORK EXPERIENCE
        |--------------------------------------------------------------------------
        */
        $workDetails = DB::table('egc_staff_work_info')
            ->where('staff_id',$id )
            ->where('status','!=', 2)
            ->orderBy('start_date','desc')
            ->orderBy('sno','desc')
            ->get();


        $data->work_details =$workDetails;
        /*
        |--------------------------------------------------------------------------
        | CURRENT WORKING EXPERIENCE
        |--------------------------------------------------------------------------
        | Calculate current organisation experience:
        |
        | date_of_joining -> Today
        |--------------------------------------------------------------------------
        */

        $data->current_work_experience = null;

        if (!empty($data->date_of_joining)) {
            try {
                $joinedDate = Carbon::parse($data->date_of_joining)->startOfDay();

                $today = Carbon::today();

                if ($joinedDate->lte($today)) {

                    $difference = $joinedDate->diff($today);
                    $years =(int) $difference->y;
                    $months = (int) $difference->m;
                    $days =(int) $difference->d;
                    $experienceParts = [];
                    if ($years > 0) {
                        $experienceParts[] = $years .' ' .($years === 1 ? 'Year' : 'Years' );
                    }
                    if ($months > 0) {
                        $experienceParts[] =$months .' ' .($months === 1 ? 'Month' : 'Months');
                    }
                    if ($days > 0 || empty($experienceParts)) {
                        $experienceParts[] =$days .' ' .($days === 1 ? 'Day' : 'Days');
                    }
                    $data->current_work_experience = [
                        'joined_date' => $joinedDate->format('Y-m-d'),
                        'as_of_date' =>$today->format('Y-m-d'),
                        'years' =>$years,
                        'months' =>$months,
                        'days' => $days,
                        'formatted' =>implode( ', ', $experienceParts),
                        'total_months' => ($years * 12) + $months,
                        'is_current' => true,
                    ];

                }
            } catch (\Throwable $e) {
                Log::warning(
                    'Unable to calculate staff current experience',
                    [
                        'staff_id' => $id,
                        'date_of_joining' => $data->date_of_joining,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
        /*
         * Calculate total experience if the main table
         * does not already contain it.
         */

        if (empty($data->total_experience)) {
            $totalExperience = $workDetails->sum(
                function ($work) {
                    return (float) ( $work->year_of_experience ?? 0);
                }
            );

            $data->total_experience = $totalExperience > 0 ? $totalExperience  : null;
        }

        /*
        |--------------------------------------------------------------------------
        | 8. EDUCATION
        |--------------------------------------------------------------------------
        */

        $qualification = DB::table('egc_staff_education_info' )
            ->select(
                'egc_staff_education_info.*',
                'egc_qualification_level.qualification_level_name as qualification_name',
                'egc_major.major_name'
            )
            ->leftJoin('egc_qualification_level', 'egc_staff_education_info.qualification_type', '=','egc_qualification_level.sno')
            ->leftJoin('egc_major','egc_staff_education_info.major','=','egc_major.sno')
            ->where('egc_staff_education_info.staff_id',$id)
            ->where('egc_staff_education_info.status','!=',2)
            ->orderBy('egc_staff_education_info.year', 'desc')
            ->get();


        $data->qualification = $qualification;


        /*
        |--------------------------------------------------------------------------
        | 9. OTHER CREDENTIALS
        |--------------------------------------------------------------------------
        */

        $otherCredentials = DB::table('egc_staff_credential')
            ->select(
                'egc_staff_credential.sno',
                'egc_staff_credential.staff_id',
                'egc_staff_credential.credential_id',
                'egc_staff_credential.user_name',
                'egc_staff_credential.url_link',
                'egc_staff_credential.description',
                'egc_credential.credential_name'
            )

            ->leftJoin('egc_credential','egc_staff_credential.credential_id', '=','egc_credential.sno')
            ->where('egc_staff_credential.staff_id',$id)
            ->where('egc_staff_credential.status','!=',2)
            ->orderBy('egc_staff_credential.sno','asc')
            ->get();

        $data->other_credential =$otherCredentials;
        /*
        |--------------------------------------------------------------------------
        | 10. STAFF DOCUMENTS
        |--------------------------------------------------------------------------
        */
        $documents = DB::table('egc_staff_attachment')
            ->select(
                'egc_staff_attachment.*',
                'egc_documents.document_name'
            )
            ->leftJoin('egc_documents','egc_staff_attachment.document_id', '=', 'egc_documents.sno')
            ->where('egc_staff_attachment.staff_id',$id )
            ->where('egc_staff_attachment.status', '!=',  2 )
            ->orderBy('egc_staff_attachment.sno','desc' )
            ->get();
        $documentData = [];
        foreach ($documents as $document) {
            $fileNames = $this->decodeJsonArray($document->attachment_name ?? null );
            $files = [];
            foreach ($fileNames as $fileName) {
                if (empty($fileName)) {
                    continue;
                }

                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION ));
                $files[] = [
                    'name' =>$fileName,
                    'extension' =>$extension,
                    'type' => $this->getStaffDocumentType( $extension),
                    'url' =>asset("staff_attachments/{$document->staff_id}/{$document->document_id}/{$fileName}"),
                ];
            }


            $documentData[] = [

                'id' =>
                    $document->sno,

                'document_id' =>
                    $document->document_id,

                'document_name' =>
                    $document->document_name
                    ?? 'Document',

                'files' =>
                    $files,

            ];

        }

        $data->documents = $documentData;

        /*
        |--------------------------------------------------------------------------
        | 11. DOCUMENT CHECKLIST
        |--------------------------------------------------------------------------
        */
        $documentChecklist = DB::table( 'egc_document_checklist' )
            ->where('status','!=',2 )
            ->orderBy('sno','asc')
            ->get();

        $checkedDocumentIds = $this->decodeJsonArray( $data->document_checklist ?? null );


        $checkedDocumentIds = array_map( 'strval', $checkedDocumentIds);
        $completedChecklistCount = 0;
        foreach ($documentChecklist as $document) {

            $document->is_checked = in_array(  (string) $document->sno, $checkedDocumentIds, true )  ? 1 : 0;

            if ($document->is_checked) {
                $completedChecklistCount++;
            }
        }

        $data->document_checkList =  $documentChecklist;

        $data->checklist_summary = [
            'total' => $documentChecklist->count(),
            'completed' => $completedChecklistCount,
            'pending' => max( 0, $documentChecklist->count() - $completedChecklistCount),
            'percentage' => $documentChecklist->count() > 0 ? round(( $completedChecklistCount / $documentChecklist->count() ) * 100 ) : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | 12. SKILLS / KNOWLEDGE TAGS
        |--------------------------------------------------------------------------
        */

        $data->skills =$this->normalizeTagValues( $data->knowledge_tag ?? null );

        /*
        |--------------------------------------------------------------------------
        | 13. COMPLETED COURSE TAGS
        |--------------------------------------------------------------------------
        */

        $data->course_tags =$this->normalizeTagValues($data->course_tag ?? null);

        /*
        |--------------------------------------------------------------------------
        | 14. SOCIAL MEDIA
        |--------------------------------------------------------------------------
        |
        | Existing Add Staff source should be matched exactly here.
        | This supports JSON object / array formats if social_media
        | is stored directly on egc_staff.
        */

        $data->social_media = $this->decodeJsonValue($data->social_media ?? null);

        /*
        |--------------------------------------------------------------------------
        | 15. LOCATION
        |--------------------------------------------------------------------------
        */

        $data->location_data = [
            'latitude' =>  $data->latitude ?? null,
            'longitude' =>  $data->longitude ?? null,
            'location_url' =>  $data->location_url ?? null,
        ];

        /*
        |--------------------------------------------------------------------------
        | 16. PROFILE COMPLETION
        |--------------------------------------------------------------------------
        */

        $profileFields = [
            $data->staff_name ?? null,
            $data->mobile_no ?? null,
            $data->email_id ?? null,
            $data->gender ?? null,
            $data->dob ?? null,
            $data->address ?? null,
            $data->department_id ?? null,
            $data->division_id ?? null,
            $data->job_role_id ?? null,
            $data->date_of_joining ?? null,
            $data->mother_tongue ?? null,
            $data->languages ?? null,
            $data->staff_image ?? null,
            $data->description ?? null,
        ];


        $completedFields = collect($profileFields)
            ->filter(function ($value) {
                return $value !== null && $value !== '' && $value !== [];
            })
            ->count();

        $data->profile_completion = round(($completedFields / count($profileFields)) * 100);

        /*
        |--------------------------------------------------------------------------
        | 17. SUMMARY COUNTS
        |--------------------------------------------------------------------------
        */
        $data->summary = [
            'education_count' =>$qualification->count(),
            'work_experience_count' => $workDetails->count(),
            'document_type_count' => count($documentData),
            'document_file_count' =>collect($documentData)
                    ->sum(function ($document) {
                        return count( $document['files']);
                    }),
            'credential_count' => $otherCredentials->count(),
            'emergency_contact_count' => count($emergencyContacts),
            'children_count' =>count($children),
            'siblings_count' =>count($siblings),
        ];

        /*
        |--------------------------------------------------------------------------
        | 18. REMOVE SENSITIVE DATA
        |--------------------------------------------------------------------------
        */

        /*
         * Never expose passwords through Staff View API.
         */
        unset($data->password);

        /*
        |--------------------------------------------------------------------------
        | FINAL RESPONSE
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'status' => 200,
            'message' => 'Staff fetched successfully',
            'error_msg' => null,
            'data' =>  $data,
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' =>500,
            'message' =>'Unable to fetch staff details',
            'error_msg' =>config('app.debug') ? $e->getMessage() : 'An unexpected error occurred while loading the employee profile.',
            'data' =>  null,
        ], 500);
    }
}

/*
|--------------------------------------------------------------------------
| HELPER: SAFE JSON ARRAY DECODER
|--------------------------------------------------------------------------
|
| Handles:
|
| null
| ""
| JSON array
| double encoded JSON
| existing PHP array
|
*/

private function decodeJsonArray($value): array
{

    if (empty($value)) {

        return [];

    }


    if (is_array($value)) {

        return $value;

    }


    if (is_object($value)) {

        return (array) $value;

    }


    $decoded = json_decode(
        $value,
        true
    );


    /*
     * Handle double encoded JSON.
     */

    if (is_string($decoded)) {

        $decodedAgain = json_decode(
            $decoded,
            true
        );


        if (
            json_last_error()
            ===
            JSON_ERROR_NONE
        ) {

            $decoded =
                $decodedAgain;

        }

    }


    return is_array($decoded)
        ? $decoded
        : [];

}

/*
|--------------------------------------------------------------------------
| HELPER: DECODE ANY JSON VALUE
|--------------------------------------------------------------------------
*/
private function decodeJsonValue($value)
{
    if (empty($value)) {
        return [];
    }


    if (is_array($value) || is_object($value)) {
        return $value;
    }

    $decoded = json_decode( $value, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    /*
     * Handle double encoded JSON.
     */
    if (is_string($decoded)) {
        $decodedAgain = json_decode( $decoded, true);

        if (json_last_error() === JSON_ERROR_NONE ) {
            return $decodedAgain;
        }
    }
    return $decoded;
}
/*
|--------------------------------------------------------------------------
| HELPER: NORMALIZE TAGIFY DATA
|--------------------------------------------------------------------------
| [{"value":"PHP"},{"value":"Laravel"}]
| Double encoded JSON
| ["PHP","Laravel"]
|
*/
private function normalizeTagValues($value): array
{
    $tags = $this->decodeJsonArray($value);
    $result = [];
    foreach ($tags as $tag) {
        if (is_array($tag)) {
            if (!empty($tag['value'])) {
                $result[] = $tag['value'];
            }
            continue;
        }
        if (is_object($tag)) {
            if (!empty($tag->value)) {
                $result[] = $tag->value;
            }
            continue;
        }
        if (is_string($tag) && trim($tag) !== '') {
            $result[] = trim($tag);
        }
    }

    return array_values(array_unique($result));
}

/* HELPER: DOCUMENT FILE TYPE */
private function getStaffDocumentType($extension): string {

    $extension =strtolower($extension);
    if (in_array($extension,['jpg','jpeg','png','gif','webp'])) {
        return 'image';
    }
    if ($extension === 'pdf') {
        return 'pdf';
    }
    if ( in_array($extension,['doc','docx'])) {
        return 'word';
    }

    if (in_array($extension,['xls','xlsx'])) {
        return 'excel';
    }
    return 'file';
}

public function getStaffSalaryPayrollView($employee)
{
    try {

        $staff = DB::table('egc_staff')
            ->where('sno', $employee)
            ->where('status', '!=', 2)
            ->select(
                'sno',
                'staff_id',
                'staff_name',
                'salary_type',
                'salary_date',
                'date_of_joining'
            )
            ->first();

        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Staff not found',
                'data' => null
            ], 404);
        }

        $salaryAccounts = DB::table('egc_staff_salary_accounts as acc')
            ->leftJoin('egc_company as company','company.sno','=','acc.salary_company_id')
            ->where('acc.staff_id',$employee)
            ->where('acc.status',0)
            ->select('acc.*','company.company_name as account_name','company.company_base_color as account_color')
            ->orderByDesc('acc.is_primary')
            ->orderBy('acc.sno')
            ->get();
        /*
        |--------------------------------------------------------------------------
        | CURRENT SALARY STRUCTURES
        |--------------------------------------------------------------------------
        */
        $today = Carbon::today();
        $currentStructures = [];
        foreach ($salaryAccounts as $account) {

            $structure = DB::table('egc_payroll_employee_structures as structure' )
                ->leftJoin('egc_payroll_salary_templates as template','template.sno','=','structure.payroll_template_sno' )
                ->where('structure.salary_account_id',$account->sno)
                ->where('structure.status',0)
                ->whereDate('structure.effective_from','<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('structure.effective_to')
                        ->orWhereDate('structure.effective_to','>=',$today);
                })
                ->select(
                    'structure.*',
                    'template.template_name'
                )
                ->orderByDesc('structure.effective_from')
                ->first();

            $components = collect();

            if ($structure) {
                $components = DB::table('egc_payroll_employee_structure_details as details')
                    ->join('egc_payroll_components as component','component.sno','=','details.payroll_component_sno')
                    ->where('details.payroll_employee_structure_sno',$structure->sno)
                    ->where('details.status',0)
                    ->select(
                        'component.sno',
                        'component.component_name',
                        'component.component_code',
                        'component.category as component_type',
                        'component.calculation_type as master_calculation_type',
                        'details.calculation_type',
                        'details.percentage_value',
                        'details.fixed_amount',
                        'details.calculated_amount',
                        'details.display_order'
                    )
                    ->orderBy('details.display_order')
                    ->get();
            }


            $earnings = $components
                        ->where('component_type','earning')
                        ->values();

            $deductions = $components
                ->where('component_type','deduction')
                ->values();

            $employerContributions = $components
                ->where('component_type','employer_contribution')
                ->values();

            $currentStructures[] = [
                'salary_account_id' => $account->sno,
                'account_name' =>$account->account_name,
                'account_color' =>$account->account_color,
                'is_primary' =>(bool) $account->is_primary,
                'account_gross_salary' =>(float) ($account->gross_salary ?? 0),
                'per_day_salary' =>(float) ($account->per_day_salary ?? 0),
                'structure' => $structure
                    ? [
                        'sno' =>$structure->sno,
                        'payroll_template_sno' => $structure->payroll_template_sno ?? null,
                        'template_name' => $structure->template_name ?? null,
                        'gross_salary' =>(float) ($structure->gross_salary ?? 0),
                        'per_day_salary' =>(float) ($structure->per_day_salary ?? 0),
                        'effective_from' =>$structure->effective_from ? Carbon::parse($structure->effective_from)->format('Y-m-d') : null,
                        'effective_to' =>$structure->effective_to ? Carbon::parse($structure->effective_to)->format('Y-m-d') : null,
                    ] : null,

                'earnings' =>$earnings,
                'deductions' =>$deductions,
                'employer_contributions' =>$employerContributions,
                'components' =>$components->values(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SALARY APPRAISAL / REVISION HISTORY
        |--------------------------------------------------------------------------
        */
        $salaryHistory = StaffSalaryAppraisalHistoryModel::query()
            ->leftJoin('egc_staff_salary_accounts as acc','acc.sno','=','egc_staff_salary_appraisal_history.salary_account_id' )
            ->leftJoin('egc_company','egc_company.sno','=','acc.salary_company_id')
            ->leftJoin('egc_payroll_salary_templates as temp','temp.sno','=','egc_staff_salary_appraisal_history.payroll_template_sno')
            ->where('egc_staff_salary_appraisal_history.employee_sno',$employee)
            ->where('egc_staff_salary_appraisal_history.status',0)
            ->orderByDesc('egc_staff_salary_appraisal_history.effective_from' )
            ->select([
                'egc_staff_salary_appraisal_history.*',
                'egc_company.company_name as account_name',
                'temp.template_name'
            ])
            ->get();
        $salaryHistoryData = $salaryHistory
            ->map(function ($row) {
                return [
                    'sno' =>$row->sno,
                    'salary_account_id' =>$row->salary_account_id,
                    'payroll_template_sno' =>$row->payroll_template_sno,
                    'account_name' => $row->account_name,
                    'template_name' => $row->template_name,
                    'effective_from' => $row->effective_from ? Carbon::parse( $row->effective_from)->format('Y-m') : null,
                    'gross_salary' =>(float) ($row->gross_salary ?? 0),
                    'appraisal_type' =>$row->appraisal_type,
                    'appraisal_type_name' =>$row->appraisal_type_name,
                    'appraisal_unit' =>$row->appraisal_unit,
                    'appraisal_value' =>(float) ($row->appraisal_value ?? 0),
                    'appraisal_reason' => $row->appraisal_reason,
                    'variable_amount' =>(float) ($row->variable_amount ?? 0),
                    'variable_months' =>$row->variable_months,
                    'variable_from' => $row->variable_from,
                    'variable_end_month' => $row->variable_end_month,
                    'processed' =>(bool) $row->processed,
                    'processed_at' => $row->processed_at ? Carbon::parse( $row->processed_at)->format('d M Y') : null,
                ];
            })
            ->values();
        /*
        |--------------------------------------------------------------------------
        | ACTUAL PROCESSED PAYROLL HISTORY
        |--------------------------------------------------------------------------
        | Salary History:
        |   Shows salary revisions/appraisals.
        | Payroll History:
        |   Shows actual processed monthly salary.
        |
        */
    $payrollHistory = DB::table('egc_payroll_employee_payrolls as payroll')
        ->where('payroll.staff_id',$staff->sno)
        ->where('payroll.status',0)
        ->orderByDesc('payroll.payroll_year')
        ->orderByDesc('payroll.payroll_month')
        ->limit(24)
        ->get();

    $payrollIds = $payrollHistory
        ->pluck('sno')
        ->filter()
        ->values();

    $allComponents = collect();

    if ($payrollIds->isNotEmpty()) {
        $allComponents = DB::table('egc_payroll_employee_payroll_details as details')
            ->leftJoin('egc_payroll_components as component','component.sno', '=','details.payroll_component_sno')
            ->whereIn('details.payroll_employee_sno', $payrollIds)
            ->where('details.status', 0 )
            ->select(
                'details.payroll_employee_sno',
                'details.payroll_component_sno as sno',
                'details.component_name as component',
                'component.component_code as code',
                'details.component_category as type',
                'details.calculation_type',
                'details.percentage_value as percentage',
                'details.actual_amount as amount'
            )
            ->get()
            ->groupBy('payroll_employee_sno');
    }

    $allPayslips = collect();
    if ($payrollIds->isNotEmpty()) {
        $allPayslips = DB::table('egc_payroll_payslips')
            ->whereIn('payroll_employee_sno', $payrollIds )
            ->where('status',0)
            ->orderByDesc('sno')
            ->get()
            ->unique('payroll_employee_sno')
            ->keyBy('payroll_employee_sno');
    }

    $payrollHistoryData = $payrollHistory
        ->map(function ($payroll) use ($allComponents,$allPayslips) {
            $components = collect($allComponents->get( $payroll->sno, collect()))
            ->map(function ($component) {
                return [
                    'sno' =>$component->sno,
                    'component' => $component->component,
                    'code' =>$component->code,
                    'type' =>$component->type,
                    'calculation_type' =>$component->calculation_type,
                    'percentage' =>(float) ( $component->percentage ?? 0),
                    'amount' =>round((float) ( $component->amount ?? 0),2),
                ];

            })
            ->values();

            $payslip = $allPayslips->get($payroll->sno);

            return [
                'sno' => $payroll->sno,
                'payroll_month' =>$payroll->payroll_month,
                'payroll_year' => $payroll->payroll_year,
                'basic_salary' =>round((float) ( $payroll->basic_salary ?? 0 ),2 ),
                'gross_earnings' =>round((float) ($payroll->gross_earnings ?? 0),2),
                'gross_deductions' => round((float) ($payroll->gross_deductions ?? 0 ),2),
                'net_payable' =>round((float) ($payroll->net_payable ?? 0),2),
                'employer_contribution' => round( (float) ( $payroll->employer_contribution ?? 0 ),2 ),
                'monthly_ctc' =>round((float) ( $payroll->gross_earnings ?? 0 ) + (float) ( $payroll->employer_contribution ?? 0),2),
                'lop_days' => round( (float) ($payroll->lop_days ?? 0), 2),
                'lop_amount' => round( (float) ( $payroll->lop_amount ?? 0), 2 ),
                'present_days' =>round((float) ( $payroll->present_days ?? 0),  2),
                'absent_days' =>round((float) ( $payroll->absent_days?? 0), 2),
                'components' =>$components,
                'earnings' =>$components->where('type','earning')->values(),
                'deductions' =>$components->where('type','deduction')->values(),
                'employer_contributions' => $components->where('type','employer_contribution')->values(),
                'has_payslip' =>(bool) $payslip,
                'payslip_id' => $payslip->sno ?? null,
                'payslip_encrypt' =>$payslip ? encrypt( $payslip->sno) : null,
            ];
        })
        ->values();

            $primaryAccount = $salaryAccounts
                ->firstWhere( 'is_primary', 1);
            if (!$primaryAccount) {
                $primaryAccount =$salaryAccounts->first();
            }
            $currentSalary =(float) ( $primaryAccount->gross_salary ?? 0);
            $pendingAppraisals = $salaryHistory->where('processed',0)->count();

            return response()->json([
                'status' => true,
                'message' =>'Salary and payroll details fetched successfully',
                'data' => [
                    'employee' => [
                        'sno' =>$staff->sno,
                        'staff_id' =>$staff->staff_id,
                        'staff_name' =>$staff->staff_name,
                        'salary_type' =>$staff->salary_type,
                        'salary_date' =>$staff->salary_date,
                    ],
                    'summary' => [
                        'current_salary' =>$currentSalary,
                        'salary_account_count' =>$salaryAccounts->count(),
                        'salary_revision_count' =>$salaryHistory->count(),
                        'pending_appraisal_count' =>$pendingAppraisals,
                        'processed_payroll_count' => $payrollHistory->count(),
                    ],
                    'salary_accounts' =>$salaryAccounts,
                    'current_structures' =>$currentStructures,
                    'salary_history' => $salaryHistoryData,
                    'payroll_history' =>$payrollHistoryData,
                ]
            ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' =>'Unable to load salary and payroll information',
            'error_msg' =>config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
            'data' => null
        ], 500);
    }
}

public function viewStaffPayslip($encryptedPayslip)
{
    try {

        try {
            $payslipSno = decrypt($encryptedPayslip);
        } catch (DecryptException $e) {
            abort(404,'Invalid payslip reference.');
        }

        $payslip = DB::table('egc_payroll_payslips as payslip')
            ->leftJoin('egc_payroll_processes as process','process.sno','=','payslip.payroll_process_sno')
            ->leftJoin('egc_staff as staff','staff.sno', '=','payslip.employee_sno')
            ->leftJoin('egc_company as company','company.sno','=','staff.company_id')
            ->leftJoin('egc_entity as entity','entity.sno','=','staff.entity_id')
            ->leftJoin('egc_department as department','department.sno', '=','staff.department_id')
            ->leftJoin('egc_division as division','division.sno','=','staff.division_id')
            ->leftJoin('egc_job_role as job_role','job_role.sno','=','staff.job_role_id')
            ->where('payslip.sno',$payslipSno)
            ->where('staff.status','!=',2 )
            ->select(
                'payslip.*',
                'process.payroll_month',
                'process.payroll_year',
                'staff.sno as employee_sno',
                'staff.staff_id',
                'staff.staff_name',
                'company.company_name',
                'company.company_base_color',
                'entity.entity_name',
                'entity.entity_short_name',
                'department.department_name',
                'division.division_name',
                'job_role.job_position_name as job_role_name'
            )
            ->first();

        if (!$payslip) {
            abort(404,'Payslip not found.');
        }

        $payroll = DB::table('egc_payroll_employee_payrolls as payroll')
            ->where('payroll.staff_id',$payslip->employee_sno )
            ->where('payroll.payroll_month',$payslip->payroll_month)
            ->where('payroll.payroll_year',$payslip->payroll_year)
            ->where('payroll.status', 0)
            ->orderByDesc('payroll.sno')
            ->first();

        if (!$payroll) {
            abort(404,'Processed payroll record not found for this payslip.');
        }

        $components = DB::table('egc_payroll_employee_payroll_details as details')
            ->leftJoin('egc_payroll_components as component','component.sno','=','details.payroll_component_sno')
            ->where('details.payroll_employee_sno',$payroll->sno)
            ->where('details.status', 0)
            ->select(
                'details.sno',
                'details.payroll_component_sno',
                'details.component_name',
                'details.component_category',
                'details.calculation_type',
                'details.percentage_value',
                'details.actual_amount',
                'component.component_code'
            )
            ->orderBy('details.sno','asc')
            ->get()
            ->map(function ($component) {
                return [
                    'sno' => $component->sno,
                    'payroll_component_sno' => $component->payroll_component_sno,
                    'component_name' => $component->component_name,
                    'component_code' => $component->component_code,
                    'component_category' => $component->component_category,
                    'calculation_type' =>$component->calculation_type,
                    'percentage_value' =>(float) ($component->percentage_value ?? 0),
                    'actual_amount' =>round((float) ( $component->actual_amount ?? 0), 2),
                ];
            });

        $earnings = $components
            ->where('component_category','earning')
            ->values();

        $deductions = $components
            ->where('component_category','deduction' )
            ->values();

        $employerContributions = $components
            ->where('component_category','employer_contribution')
            ->values();

        $grossEarnings = round((float) ($payroll->gross_earnings ?? 0),2 );
        $grossDeductions = round((float) ($payroll->gross_deductions ?? 0 ),2);
        $netPayable = round((float) ($payroll->net_payable ?? 0), 2 );
        $employerContribution = round((float) ( $payroll->employer_contribution ?? 0 ), 2);
        $monthlyCtc = round( $grossEarnings + $employerContribution, 2);
   
        $data = [
            'payslip' => $payslip,
            'employee' => [
                'sno' => $payslip->employee_sno,
                'staff_id' => $payslip->staff_id,
                'staff_name' =>$payslip->staff_name,
                'company_name' => $payslip->company_name,
                'company_base_color' => $payslip->company_base_color,
                'entity_name' =>  $payslip->entity_name,
                'entity_short_name' => $payslip->entity_short_name,
                'department_name' =>$payslip->department_name,
                'division_name' => $payslip->division_name,
                'job_role_name' => $payslip->job_role_name,
            ],
            'period' => [
                'month' => (int) $payslip->payroll_month,
                'year' => (int) $payslip->payroll_year,
                'month_name' => Carbon::create( $payslip->payroll_year,$payslip->payroll_month, 1 )->format('F Y'),
            ],
            'attendance' => [
                'present_days' =>(float) ( $payroll->present_days ?? 0),
                'absent_days' =>(float) ( $payroll->absent_days ?? 0),
                'lop_days' => (float) ( $payroll->lop_days ?? 0),
                'lop_amount' => round((float) ( $payroll->lop_amount ?? 0),2 ),
            ],
            'summary' => [
                'gross_earnings' =>$grossEarnings,
                'gross_deductions' => $grossDeductions,
                'net_payable' => $netPayable,
                'employer_contribution' =>$employerContribution,
                'monthly_ctc' => $monthlyCtc,
            ],
            'components' =>$components,
            'earnings' =>$earnings,
            'deductions' =>$deductions,
            'employer_contributions' => $employerContributions,
        ];

        return view( 'payroll.staff_payslip',compact('data'));

    } catch (DecryptException $e) {
        abort( 404, 'Invalid payslip reference.' );
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        throw $e;
    } catch (\Throwable $e) {
        Log::error( 'Staff Payslip View Error', 
            [
                'encrypted_payslip' =>$encryptedPayslip,
                'message' => $e->getMessage(),
                'file' =>$e->getFile(),
                'line' =>$e->getLine(),
            ]
        );
        abort( 500,'Unable to load the payslip.');
    }
}


public function getStaffAssets($staff)
{
    try {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE STAFF ID
        |--------------------------------------------------------------------------
        */
        if (!is_numeric($staff) || (int) $staff <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid staff ID.',
                'data' => null,
            ], 422);
        }
        $staffId = (int) $staff;

        $staffData = StaffModel::query()
            ->leftJoin('egc_company','egc_staff.company_id','=','egc_company.sno')
            ->leftJoin('egc_entity','egc_staff.entity_id','=','egc_entity.sno')
            ->leftJoin('egc_department','egc_staff.department_id','=','egc_department.sno')
            ->leftJoin('egc_division','egc_staff.division_id','=','egc_division.sno')
            ->leftJoin('egc_job_role','egc_staff.job_role_id','=','egc_job_role.sno')
            ->where('egc_staff.sno',$staffId)
            ->where('egc_staff.status','!=',2)
            ->select([
                'egc_staff.sno',
                'egc_staff.staff_id',
                'egc_staff.staff_name',
                'egc_staff.date_of_joining',
                'egc_staff.company_id',
                'egc_staff.entity_id',
                'egc_staff.department_id',
                'egc_staff.division_id',
                'egc_staff.job_role_id',
                'egc_company.company_name',
                'egc_entity.entity_name',
                'egc_department.department_name',
                'egc_division.division_name',
                'egc_job_role.job_position_name as job_role_name',
            ])
            ->first();

        if (!$staffData) {
            return response()->json([
                'status' => false,
                'message' => 'Staff not found.',
                'data' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | GET CURRENTLY ASSIGNED ASSET IDS
        |--------------------------------------------------------------------------
        | asset_status = 1 means currently assigned.
        | These assets must NOT appear in Available Assets.
        |--------------------------------------------------------------------------
        */
        $assignedAssetIds = DB::table('egc_staff_asset_assignments')
            ->where('asset_status',1)
            ->where('status',0)
            ->pluck('asset_id');
        /*
        |--------------------------------------------------------------------------
        | GET AVAILABLE ASSETS
        |--------------------------------------------------------------------------
        | Rules:
        | status = 0
        | availability_status = 1
        | no active assignment
        |--------------------------------------------------------------------------
        */

        $availableAssetsQuery = DB::table('egc_assets')
            ->where('status',0)
            ->where('availability_status', 1);
        if ($assignedAssetIds->isNotEmpty()) {
            $availableAssetsQuery->whereNotIn( 'sno',$assignedAssetIds);
        }

        $availableAssets =$availableAssetsQuery
                            ->select([
                                'sno',
                                'asset_name',
                                'asset_code',
                                'asset_type',
                                'serial_number',
                                'asset_value',
                                'purchase_date',
                                'warranty_expiry',
                            ])
                            ->orderBy('asset_name', 'asc')
                            ->get();

        $currentAssignedAssets = DB::table('egc_staff_asset_assignments as assignment' )
            ->join('egc_assets as asset','asset.sno', '=','assignment.asset_id' )
            ->where('assignment.staff_id',$staffId)
            ->where('assignment.asset_status',1 )
            ->where('assignment.status', 0)
            ->select([
                'assignment.sno as assignment_id',
                'assignment.asset_id',
                'assignment.assigned_date',
                'assignment.expected_return_date',
                'assignment.remarks',
                'asset.asset_name',
                'asset.asset_code',
                'asset.asset_type',
                'asset.serial_number',
            ])
            ->orderBy('assignment.assigned_date','desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' =>'Staff asset information fetched successfully.',
            'data' => [
                'staff' => $staffData,
                'assets' =>$availableAssets,
                'assigned_assets' =>$currentAssignedAssets,
                'summary' => [
                    'available_count' => $availableAssets->count(),
                    'assigned_count' => $currentAssignedAssets->count(),
                ],
            ],
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' =>'Unable to load staff asset information.',
            'error' => config('app.debug')  ? $e->getMessage()  : null,
            'data' => null,
        ], 500);
    }
}

public function assignStaffAssets(Request $request)
{

    $validator = Validator::make(
        $request->all(),
        [
            'staff_id' =>'required|integer',
            'assets' =>'required|array|min:1',
            'assets.*.asset_id' =>'required|integer|distinct',
            'assets.*.assigned_date' =>'required|date',
            'assets.*.expected_return_date' =>'nullable|date',
            'remarks' =>'nullable|string|max:1000',
        ],
        [
            'staff_id.required' =>'Staff ID is required.',
            'assets.required' =>'Please select at least one asset.',
            'assets.min' =>'Please select at least one asset.',
            'assets.*.asset_id.required' =>'Asset ID is required.',
            'assets.*.asset_id.distinct' => 'The same asset cannot be selected more than once.',
            'assets.*.assigned_date.required' =>'Assigned date is required for every selected asset.',
        ]
    );

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' =>$validator->errors()->first(),
            'errors' =>$validator->errors(),
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE DATE RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    foreach ($request->assets as $index => $asset ) {
        if ( !empty($asset['expected_return_date' ]) && $asset['expected_return_date'] < $asset['assigned_date' ]) {
            return response()->json([
                'status' => false,
                'message' =>
                    'Expected return date cannot be before the assigned date.',
                'errors' => [
                    "assets.$index.expected_return_date" => [
                        'Expected return date cannot be before the assigned date.'
                    ]
                ],
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK STAFF
    |--------------------------------------------------------------------------
    */
    $staff = StaffModel::query()
        ->where( 'sno',$request->staff_id )
        ->where('status','!=',2 )
        ->first();

    if (!$staff) {
        return response()->json([
            'status' => false,
            'message' =>
                'Staff record not found.',
        ], 404);
    }

    try {
        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */
        $result = DB::transaction( function () use ($request,$staff) {
            $user_id =$request->user()->user_id;
                /*
                |--------------------------------------------------------------------------
                | GET REQUESTED ASSET IDS
                |--------------------------------------------------------------------------
                */
                $assetIds = collect($request->assets)
                    ->pluck('asset_id' )
                    ->map( fn ($id) =>(int) $id)
                    ->unique()
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | LOCK REQUESTED ASSET MASTER RECORDS
                |--------------------------------------------------------------------------
                |
                | lockForUpdate prevents two HR/Admin users from assigning
                | the same available asset at exactly the same time.
                |--------------------------------------------------------------------------
                */
                $assets = DB::table('egc_assets')
                    ->whereIn('sno', $assetIds)
                    ->where('status', 0)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('sno');


                /*
                |--------------------------------------------------------------------------
                | VERIFY EVERY ASSET EXISTS
                |--------------------------------------------------------------------------
                */

                if ($assets->count() !== $assetIds->count()) {

                    throw new \RuntimeException(
                        'One or more selected assets could not be found.'
                    );
                }


                foreach ($assetIds as $assetId) {
                    $asset =$assets->get($assetId);

                    if ((int)$asset->availability_status !== 1) {

                        throw new \RuntimeException(
                            "Asset {$asset->asset_name} is not currently available."
                        );
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | LOCK ACTIVE ASSIGNMENT RECORDS
                |--------------------------------------------------------------------------
                |
                | An asset is unavailable when it has:
                |
                | asset_status = 1
                | status       = 0
                |--------------------------------------------------------------------------
                */

                $alreadyAssigned = DB::table('egc_staff_asset_assignments' )
                    ->whereIn('asset_id',$assetIds)
                    ->where('asset_status',1)
                    ->where('status', 0)
                    ->lockForUpdate()
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | DUPLICATE ACTIVE ASSIGNMENT
                |--------------------------------------------------------------------------
                */
                if ($alreadyAssigned->isNotEmpty()) {

                    $conflictingAssetIds = $alreadyAssigned->pluck( 'asset_id');
                    $conflictingNames = $assets->whereIn( 'sno', $conflictingAssetIds)
                            ->pluck( 'asset_name' )
                            ->implode( ', ' );

                    throw new \RuntimeException(
                        $conflictingNames
                            ?
                            "{$conflictingNames} is already assigned."
                            :
                            'One or more selected assets are already assigned.'
                    );
                }
                /*
                |--------------------------------------------------------------------------
                | PREPARE INSERT DATA
                |--------------------------------------------------------------------------
                */

                $insertRows = [];

                $now = now();

                $assignedBy =$user_id;

                foreach ($request->assets as $asset) {
                    $insertRows[] = [
                        'staff_id' => $staff->sno,
                        'asset_id' => (int) $asset['asset_id'],
                        'assigned_date' => $asset['assigned_date'],
                        'expected_return_date' =>!empty($asset['expected_return_date'] ) ? $asset['expected_return_date'] :  null,
                        'returned_date' => null,
                        'asset_status' => 1,
                        'remarks' => $request->remarks ?: null,
                        'return_remarks' =>null,
                        'assigned_by' => $assignedBy,
                        'returned_by' => null,
                        'status' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                /*
                |--------------------------------------------------------------------------
                | INSERT ASSIGNMENTS
                |--------------------------------------------------------------------------
                */
                DB::table('egc_staff_asset_assignments')
                    ->insert( $insertRows);
                /*
                |--------------------------------------------------------------------------
                | RETURN TRANSACTION RESULT
                |--------------------------------------------------------------------------
                */
                return [
                    'assigned_count' => count( $insertRows ),
                    'asset_ids' =>$assetIds->values()->all(),
                ];
            },
            3
        );

        return response()->json([
            'status' =>true,
            'message' => $result['assigned_count' ].($result['assigned_count' ] === 1 ? ' asset assigned successfully.' : ' assets assigned successfully.' ),
            'data' => [
                'staff_id' => $staff->sno,
                'assigned_count' =>$result['assigned_count' ],
                'asset_ids' =>$result['asset_ids'],
            ],
        ], 200);

    } catch (\RuntimeException $e ) {

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 409);

    } catch ( \Throwable $e) {
        // Log::error(
        //     'Assign Staff Assets Error',
        //     [
        //         'staff_id' =>$request->staff_id ?? null,
        //         'asset_ids' => collect($request->assets ?? [])->pluck('asset_id')->all(),
        //         'user_id' =>$user_id,
        //         'message' =>$e->getMessage(),
        //         'file' => $e->getFile(),
        //         'line' => $e->getLine(),
        //     ]
        // );

        return response()->json([
            'status' =>false,
            'message' =>'Unable to assign assets. Please try again.',
            'error' =>config('app.debug') ? $e->getMessage() :  null,
        ], 500);
    }
}

public function employeeProfile(string $token)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | 1. Decrypt employee token
        |--------------------------------------------------------------------------
        */

        $helper = new \App\Helpers\Helpers();

        $staffId = $helper->encrypt_decrypt(
            $token,
            'decrypt'
        );

        if (
            $staffId === false ||
            $staffId === null ||
            !is_numeric($staffId) ||
            (int) $staffId <= 0
        ) {
            abort(404);
        }

        $staffId = (int) $staffId;

    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Never expose decrypt errors
        |--------------------------------------------------------------------------
        */

        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | 2. Load active staff
    |--------------------------------------------------------------------------
    |
    | Do not allow inactive/deleted staff to access the shared link.
    |
    */

    $staff = StaffModel::query()
        ->where('egc_staff.status', '!=', 2)
        ->where('egc_staff.sno', $staffId)
        ->first();


    if (!$staff) {
        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | 3. Family
    |--------------------------------------------------------------------------
    */

    $family = StaffFamilyModel::query()
        ->where('staff_id', $staffId)
        ->where('status', '!=', 2)
        ->first();


    /*
    |--------------------------------------------------------------------------
    | 4. Education
    |--------------------------------------------------------------------------
    */

    $education = StaffEducationInfoModel::query()
        ->where('staff_id', $staffId)
        ->where('status', '!=', 2)
        ->orderBy('sno', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | 5. Existing attachments
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Keep deleted records excluded.
    |
    */

    $attachments = StaffAttachmentModel::query()
        ->where('staff_id', $staffId)
        ->where('status', '!=', 2)
        ->orderBy('sno', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | 6. Master data required by employee_profile.blade.php
    |--------------------------------------------------------------------------
    |
    | The Blade currently expects these exact variable names.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Languages
    |--------------------------------------------------------------------------
    */

    $languageList = LanguageModel::query()
        ->where('status', '!=', 2)
        ->orderBy('name', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Hobbies
    |--------------------------------------------------------------------------
    */

    $hobbyList = HobbyModel::query()
        ->where('status', '!=', 2)
        ->orderBy('hobby_name', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Blood groups
    |--------------------------------------------------------------------------
    */

     $bloodGroupList = DB::table('egc_blood_group')->where('egc_blood_group.status',0)->get();
      

          
          $communityList = DB::table('egc_community')->where('status',0)->orderBy('sno','asc')->get();



    /* Nationality */

      $nationalityList = DB::table('egc_nationality')
                ->where('status', 0)
                ->orderByRaw("
                    CASE
                        WHEN nationality_name = 'Indian' THEN 0
                        ELSE 1
                    END
                ")
                ->orderBy('nationality_name', 'ASC')
                ->get();


    /*
    |--------------------------------------------------------------------------
    | Religion
    |--------------------------------------------------------------------------
    */
      $religionList = DB::table('egc_religion')->where('status',0)->orderBy('sno','asc')->get();
 

    /*
    |--------------------------------------------------------------------------
    | Social media
    |--------------------------------------------------------------------------
    */

    $social_media_list = SocialMediaModel::query()
        ->where('status', '!=', 2)
        ->orderBy('sno', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Qualification
    |--------------------------------------------------------------------------
    */

    $qualificationList = QualificationModel::query()
        ->where('status', '!=', 2)
        ->orderBy('sno', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Course tags
    |--------------------------------------------------------------------------
    */

    $courseList = CourseTagModel::query()
        ->where('status', '!=', 2)
        ->pluck('course_tag_name');


    /*
    |--------------------------------------------------------------------------
    | Relationship
    |--------------------------------------------------------------------------
    */

    $relationshipList = RelationshipModel::query()
        ->where('status', 0)
        ->orderBy('sno', 'asc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Document types
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Replace DocumentTypeModel with the exact model used by your
    | existing HR edit() method if its class name differs.
    |
    */

    $documentTypeList = DocumentModel::where('status', 0)->orderBy('sno', 'ASC')->get();


    /*
    |--------------------------------------------------------------------------
    | 7. Server-side completion
    |--------------------------------------------------------------------------
    |
    | Never calculate this in Blade/JavaScript.
    |
    */

    $completionService =
        app(
            \App\Services\StaffService\StaffProfileCompletionService::class
        );

    $completion =
        $completionService->calculate(
            $staffId
        );


    /*
    |--------------------------------------------------------------------------
    | 8. Synchronize the stored completion value
    |--------------------------------------------------------------------------
    |
    | This makes sure the employee page fixes old/stale percentage values
    | when the profile is opened.
    |
    */

    $serverPercentage =
        (float) data_get(
            $completion,
            'overall.percentage',
            0
        );

    $storedStepProgress =
        json_encode(
            $completion,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


    /*
    |--------------------------------------------------------------------------
    | Only update if the stored value is different.
    |--------------------------------------------------------------------------
    */

    $existingPercentage =
        (float) ($staff->completion_percentage ?? -1);

    $existingStepProgress =
        $staff->step_progress ?? null;

    if (
        $existingPercentage !== $serverPercentage ||
        $existingStepProgress !== $storedStepProgress
    ) {

        StaffModel::where(
            'sno',
            $staffId
        )->update([
            'completion_percentage' =>
                $serverPercentage,

            'step_progress' =>
                $storedStepProgress,
        ]);

        /*
         * Keep local model synchronized too.
         */
        $staff->completion_percentage =
            $serverPercentage;

        $staff->step_progress =
            $storedStepProgress;
    }


    /*
    |--------------------------------------------------------------------------
    | 9. Return employee profile
    |--------------------------------------------------------------------------
    |
    | Variable names intentionally match employee_profile.blade.php.
    |
    */

    return view(
        'content.hr_management.hr_enroll.manage_staff.employee_profile',
        [
            'staff' =>
                $staff,

            'family' =>
                $family,

            'education' =>
                $education,

            'attachments' =>
                $attachments,

            'languageList' =>
                $languageList,

            'hobbyList' =>
                $hobbyList,

            'bloodGroupList' =>
                $bloodGroupList,

            'nationalityList' =>
                $nationalityList,

            'religionList' =>
                $religionList,

            'communityList' =>
                $communityList,

            'social_media_list' =>
                $social_media_list,

            'qualificationList' =>
                $qualificationList,

            'courseList' =>
                $courseList,

            'relationshipList' =>
                $relationshipList,

            'documentTypeList' =>
                $documentTypeList,

            'completion' =>
                $completion,

            /*
             * Preserve the original encrypted token.
             * Never pass the decrypted staff ID as the public token.
             */
            'token' =>
                $token,
        ]
    );
}


public function employeeProfileUpdate(
    Request $request,
    $token
) {
    /*
    |--------------------------------------------------------------------------
    | 1. Decrypt employee token
    |--------------------------------------------------------------------------
    */

    try {

        $helper = new \App\Helpers\Helpers();

        $staffId = $helper->encrypt_decrypt(
            $token,
            'decrypt'
        );

        if (
            $staffId === false ||
            !is_numeric($staffId)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid profile link.',
            ], 403);
        }

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => 'Invalid profile link.',
        ], 403);
    }

    $staffId = (int) $staffId;

    /*
    |--------------------------------------------------------------------------
    | 2. Find staff
    |--------------------------------------------------------------------------
    */

    $staff = StaffModel::where(
        'sno',
        $staffId
    )
    ->where(
        'status',
        '!=',
        2
    )
    ->first();

    if (!$staff) {

        return response()->json([
            'status' => false,
            'message' => 'Staff record not found.',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Employee editable STAFF fields
    |--------------------------------------------------------------------------
    */

    $allowedStaffFields = [
        'staff_name',
        'mobile_no',
        'alternative_no',
        'email_id',
        'gender',
        'dob',

        'martial_status',

        'address',
        'residential_address',

        'location_url',
        'latitude',
        'longitude',

        'mother_tongue',
        'languages',

        'hobby',
        'description',
        'birth_place',

        'blood_group',
        'height',
        'weight',

        'nationality_id',
        'religion_id',
        'community_id',
        'caste_id',

        'identification_mark',

        'vehicle_check',
        'driving_license_no',
        'vehicle_register_no',
        'license_expiry',

        'is_Course',
        'course_tag',

        'social_media_details',
    ];

    /*
    |--------------------------------------------------------------------------
    | 4. Explicitly blocked HR fields
    |--------------------------------------------------------------------------
    */

    $blockedFields = [

        // Organisation
        'entity_id',
        'company_id',
        'branch_id',
        'department_id',
        'division_id',
        'job_role_id',
        'role_id',

        // Employment
        'staff_id',
        'employee_id',
        'date_of_joining',
        'exp_type',

        // Work
        'work_type',
        'work_company_name',
        'work_position',
        'work_exp_yrs',
        'work_salary',

        // Salary
        'basic_salary',
        'per_hour_cost',
        'salary',
        'gross_salary',
        'ctc',

        // Login
        'user_name',
        'password',
        'login_access',

        // HR workflow
        'application_status',
        'application_stage',
        'checklist',

        // Completion
        'completion_percentage',
        'step_progress',

        // Internal
        'created_by',
        'updated_by',
        'status',
    ];

    foreach ($blockedFields as $blockedField) {

        if ($request->has($blockedField)) {

            return response()->json([
                'status' => false,
                'message' =>
                    'HR-managed information cannot be changed from this profile.',
                'field' => $blockedField,
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Never mass assign request
    |--------------------------------------------------------------------------
    */

    $staffData = $request->only(
        $allowedStaffFields
    );

    /*
    |--------------------------------------------------------------------------
    | 6. Remove protected fields
    |--------------------------------------------------------------------------
    */

    unset(
        $staffData['completion_percentage'],
        $staffData['step_progress'],
        $staffData['staff_id'],
        $staffData['employee_id'],
        $staffData['company_id'],
        $staffData['department_id'],
        $staffData['designation'],
        $staffData['salary'],
        $staffData['basic_salary']
    );

    /*
    |--------------------------------------------------------------------------
    | 7. Normalize vehicle fields
    |--------------------------------------------------------------------------
    */

    $vehicleCheck =
        (string) (
            $request->vehicle_check ??
            '0'
        );

    if ($vehicleCheck !== '1') {

        $staffData['vehicle_check'] = 0;

        $staffData['driving_license_no'] = null;
        $staffData['vehicle_register_no'] = null;
        $staffData['license_expiry'] = null;
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Normalize social media
    |--------------------------------------------------------------------------
    */

    if ($request->has('social_media')) {

        $socialMedia = $request->input(
            'social_media',
            []
        );

        if (!is_array($socialMedia)) {
            $socialMedia = [];
        }

        $socialMedia = array_filter(
            $socialMedia,
            function ($value) {
                return !is_null($value)
                    && trim((string) $value) !== '';
            }
        );

        $staffData['social_media_details'] =
            empty($socialMedia)
                ? null
                : json_encode(
                    $socialMedia,
                    JSON_UNESCAPED_UNICODE
                );
    }

    /*
    |--------------------------------------------------------------------------
    | 9. Course
    |--------------------------------------------------------------------------
    */

    $isCourse =
        $request->input(
            'is_Course'
        );

    if ($isCourse !== 'Yes') {

        $staffData['is_Course'] = 'No';
        $staffData['course_tag'] = null;

    } else {

        $courseTags =
            $request->input(
                'course_tag',
                []
            );

        if (!is_array($courseTags)) {
            $courseTags = [];
        }

        $courseTags = array_values(
            array_filter(
                $courseTags,
                fn ($value) =>
                    trim((string) $value) !== ''
            )
        );

        $staffData['is_Course'] = 'Yes';

        $staffData['course_tag'] =
            empty($courseTags)
                ? null
                : json_encode(
                    $courseTags,
                    JSON_UNESCAPED_UNICODE
                );
    }

    /*
    |--------------------------------------------------------------------------
    | 10. Validate
    |--------------------------------------------------------------------------
    */

    $validator = Validator::make(
        $request->all(),
        [
            'staff_name' => [
                'required',
                'string',
                'max:255',
            ],

            'mobile_no' => [
                'required',
                'digits:10',
            ],

            'email_id' => [
                'nullable',
                'email',
                'max:255',
            ],

            'gender' => [
                'required',
            ],

            'dob' => [
                'required',
                'date',
            ],

            'permanent_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'residential_address' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]
    );

    if ($validator->fails()) {

        return response()->json([
            'status' => false,
            'message' =>
                'Please correct the highlighted fields.',
            'errors' =>
                $validator->errors(),
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | 11. Transaction
    |--------------------------------------------------------------------------
    */

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        */

        foreach ($staffData as $column => $value) {

            /*
             * Extra protection.
             */
            if (
                in_array(
                    $column,
                    $blockedFields,
                    true
                )
            ) {
                continue;
            }

            $staff->{$column} = $value;
        }

        /*
         * Never allow employee endpoint to alter these.
         */

        $staff->updated_by =
            $staff->updated_by;

        $staff->save();


        /*
        |--------------------------------------------------------------------------
        | FAMILY
        |--------------------------------------------------------------------------
        */

        $this->saveEmployeeFamily(
            $request,
            $staffId
        );


        /*
        |--------------------------------------------------------------------------
        | CONTACT
        |--------------------------------------------------------------------------
        */

        $this->saveEmployeeContacts(
            $request,
            $staffId
        );


        /*
        |--------------------------------------------------------------------------
        | EDUCATION
        |--------------------------------------------------------------------------
        */

        $this->saveEmployeeEducation(
            $request,
            $staffId
        );


        /*
        |--------------------------------------------------------------------------
        | ATTACHMENTS
        |--------------------------------------------------------------------------
        */

        $this->saveEmployeeAttachments(
            $request,
            $staffId
        );


        /*
        |--------------------------------------------------------------------------
        | SERVER-SIDE COMPLETION
        |--------------------------------------------------------------------------
        */

        $completionService =
            app(
                \App\Services\StaffProfileCompletionService::class
            );

        $completion =
            $completionService->calculate(
                $staffId
            );

        $staff->completion_percentage =
            $completion['overall']['percentage'];

        $staff->step_progress =
            json_encode(
                $completion,
                JSON_UNESCAPED_UNICODE
            );

        $staff->save();

        DB::commit();

        return response()->json([
            'status' => true,

            'message' =>
                'Profile updated successfully.',

            'completion' =>
                $completion,

            'percentage' =>
                $completion['overall']['percentage'],
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error(
            'Employee profile update failed.',
            [
                'staff_id' => $staffId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );

        return response()->json([
            'status' => false,
            'message' =>
                'Unable to save your profile. Please try again.'.$e->getMessage(),
        ], 500);
    }
}


protected function saveEmployeeFamily(
    Request $request,
    int $staffId
) {
    $family = StaffFamilyModel::where(
        'staff_id',
        $staffId
    )
    ->where(
        'status',
        '!=',
        2
    )
    ->first();

    if (!$family) {

        $family = new StaffFamilyModel();

        $family->staff_id = $staffId;
        $family->created_by =
            auth()->user()->user_id ?? 1;
    }

    $family->father_name =
        $request->input('father_name');

    $family->father_occup =
        $request->input('father_occup');

    $family->mother_name =
        $request->input('mother_name');

    $family->mother_occup =
        $request->input('mother_occup');

    $family->marital_status =
        $request->input('marital_status');

    /*
    |--------------------------------------------------------------------------
    | Spouse
    |--------------------------------------------------------------------------
    */

    if (
        (string) $request->input(
            'marital_status'
        ) === '1'
    ) {

        $family->anniversary_date =
            $request->input(
                'anniversary_date'
            );

        $family->spouse_name =
            $request->input(
                'spouse_name'
            );

        $family->spouse_mobile =
            $request->input(
                'spouse_mobile'
            );

        $family->spouse_dob =
            $request->input(
                'spouse_dob'
            );

        $family->spouse_working =
            $request->input(
                'spouse_working'
            );

        if (
            strtolower(
                trim(
                    (string) $request->input(
                        'spouse_working'
                    )
                )
            ) === 'yes'
        ) {

            $family->spouse_designation =
                $request->input(
                    'spouse_designation'
                );

            $family->spouse_company_name =
                $request->input(
                    'spouse_company_name'
                );

            $family->spouse_salary =
                $request->input(
                    'spouse_salary'
                );
        } else {

            $family->spouse_designation = null;
            $family->spouse_company_name = null;
            $family->spouse_salary = 0;
        }

    } else {

        $family->anniversary_date = null;
        $family->spouse_name = null;
        $family->spouse_mobile = null;
        $family->spouse_dob = null;
        $family->spouse_working = null;
        $family->spouse_designation = null;
        $family->spouse_company_name = null;
        $family->spouse_salary = 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Children
    |--------------------------------------------------------------------------
    */

    $hasChildren =
        $request->input(
            'has_children'
        );

    $family->has_children =
        $hasChildren;

    if (
        strtolower(
            trim((string) $hasChildren)
        ) === 'yes'
    ) {

        $childrenCount =
            max(
                0,
                (int) $request->input(
                    'children_count',
                    0
                )
            );

        $childNames =
            $request->input(
                'child_name',
                []
            );

        $childDobs =
            $request->input(
                'child_dob',
                []
            );

        $childStd =
            $request->input(
                'child_std',
                []
            );

        $childYears =
            $request->input(
                'child_year',
                []
            );

        $children = [];

        for (
            $i = 0;
            $i < $childrenCount;
            $i++
        ) {

            $children[] = [
                'child_name' =>
                    $childNames[$i] ?? null,

                'child_dob' =>
                    !empty($childDobs[$i])
                        ? date(
                            'Y-m-d',
                            strtotime(
                                $childDobs[$i]
                            )
                        )
                        : null,

                'child_std' =>
                    $childStd[$i] ?? null,

                'child_year' =>
                    $childYears[$i] ?? null,
            ];
        }

        $family->children_count =
            $childrenCount;

        $family->children_details =
            json_encode(
                $children,
                JSON_UNESCAPED_UNICODE
            );

    } else {

        $family->children_count = 0;
        $family->children_details = null;
    }


    /*
    |--------------------------------------------------------------------------
    | Siblings
    |--------------------------------------------------------------------------
    */

    $hasSiblings =
        $request->input(
            'has_siblings'
        );

    $family->has_siblings =
        $hasSiblings;

    if (
        strtolower(
            trim((string) $hasSiblings)
        ) === 'yes'
    ) {

        $siblingsCount =
            max(
                0,
                (int) $request->input(
                    'siblings_count',
                    0
                )
            );

        $siblingNames =
            $request->input(
                'sibling_name',
                []
            );

        $siblingTypes =
            $request->input(
                'sibling_type',
                []
            );

        $siblingStd =
            $request->input(
                'sibling_std',
                []
            );

        $siblingIncome =
            $request->input(
                'sibling_income',
                []
            );

        $siblings = [];

        for (
            $i = 0;
            $i < $siblingsCount;
            $i++
        ) {

            $siblings[] = [
                'sibling_name' =>
                    $siblingNames[$i] ?? null,

                'sibling_type' =>
                    $siblingTypes[$i] ?? null,

                'sibling_std' =>
                    $siblingStd[$i] ?? null,

                'sibling_income' =>
                    $siblingIncome[$i] ?? null,
            ];
        }

        $family->sibling_count =
            $siblingsCount;

        $family->siblings_detail =
            json_encode(
                $siblings,
                JSON_UNESCAPED_UNICODE
            );

    } else {

        $family->sibling_count = 0;
        $family->siblings_detail = null;
    }


    $family->updated_by =
        auth()->user()->user_id ?? 1;

    $family->save();

    return $family;
}

protected function saveEmployeeContacts(
    Request $request,
    int $staffId
) {
    $staff = StaffModel::findOrFail(
        $staffId
    );

    $names =
        $request->input(
            'contact_person_name',
            []
        );

    $numbers =
        $request->input(
            'contact_person_no',
            []
        );

    $relations =
        $request->input(
            'contact_person_relation',
            []
        );

    if (!is_array($names)) {
        $names = [];
    }

    if (!is_array($numbers)) {
        $numbers = [];
    }

    if (!is_array($relations)) {
        $relations = [];
    }

    $cleanNames = [];
    $cleanNumbers = [];
    $cleanRelations = [];

    $count = max(
        count($names),
        count($numbers),
        count($relations)
    );

    for ($i = 0; $i < $count; $i++) {

        $name =
            trim(
                (string) (
                    $names[$i] ?? ''
                )
            );

        $number =
            preg_replace(
                '/\D+/',
                '',
                (string) (
                    $numbers[$i] ?? ''
                )
            );

        $relation =
            trim(
                (string) (
                    $relations[$i] ?? ''
                )
            );

        /*
         * Ignore completely empty rows.
         */
        if (
            $name === '' &&
            $number === '' &&
            $relation === ''
        ) {
            continue;
        }

        $cleanNames[] = $name;
        $cleanNumbers[] = $number;
        $cleanRelations[] = $relation;
    }

    $staff->contact_person_name =
        empty($cleanNames)
            ? null
            : json_encode(
                $cleanNames,
                JSON_UNESCAPED_UNICODE
            );

    $staff->contact_person_no =
        empty($cleanNumbers)
            ? null
            : json_encode(
                $cleanNumbers,
                JSON_UNESCAPED_UNICODE
            );

    $staff->contact_person_relation =
        empty($cleanRelations)
            ? null
            : json_encode(
                $cleanRelations,
                JSON_UNESCAPED_UNICODE
            );

    $staff->save();

    return $staff;
}

protected function saveEmployeeEducation(
    Request $request,
    int $staffId
) {
    $qualifications =
        $request->input(
            'qualification_type',
            []
        );

    $majors =
        $request->input(
            'major',
            []
        );

    $universities =
        $request->input(
            'univ_name',
            []
        );

    $years =
        $request->input(
            'pass_year',
            []
        );

    if (!is_array($qualifications)) {
        $qualifications = [];
    }

    if (!is_array($majors)) {
        $majors = [];
    }

    if (!is_array($universities)) {
        $universities = [];
    }

    if (!is_array($years)) {
        $years = [];
    }

    /*
    |--------------------------------------------------------------------------
    | Existing active records
    |--------------------------------------------------------------------------
    */

    $existing =
        StaffEducationInfoModel::where(
            'staff_id',
            $staffId
        )
        ->where(
            'status',
            '!=',
            2
        )
        ->get();

    $submittedIds = [];

    foreach (
        $qualifications as $key => $qualification
    ) {

        if (
            trim((string) $qualification) === ''
        ) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Qualification-specific rules
        |--------------------------------------------------------------------------
        */

        $skipMajor =
            in_array(
                (string) $qualification,
                [
                    '4',
                    '5',
                    '6',
                    'Others',
                ],
                true
            );

        $major =
            $majors[$key] ?? null;

        $university =
            $universities[$key] ?? null;

        $year =
            $years[$key] ?? null;

        if ($skipMajor) {
            $major = null;
            $university = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing record by qualification
        |--------------------------------------------------------------------------
        */

        $education =
            StaffEducationInfoModel::where(
                'staff_id',
                $staffId
            )
            ->where(
                'qualification_type',
                $qualification
            )
            ->where(
                'status',
                '!=',
                2
            )
            ->first();

        if (!$education) {

            $education =
                new StaffEducationInfoModel();

            $education->staff_id =
                $staffId;

            $education->qualification_type =
                $qualification;

            $education->created_by =
                auth()->user()->user_id ?? 1;
        }

        $education->major =
            $major;

        $education->university_name =
            $university;

        $education->year =
            $year;

        $education->updated_by =
            auth()->user()->user_id ?? 1;

        $education->status = 1;

        $education->save();

        $submittedIds[] =
            $education->sno;
    }

    /*
    |--------------------------------------------------------------------------
    | Soft delete removed education rows
    |--------------------------------------------------------------------------
    */

    foreach ($existing as $oldEducation) {

        if (
            !in_array(
                $oldEducation->sno,
                $submittedIds,
                true
            )
        ) {

            $oldEducation->status = 2;

            $oldEducation->updated_by =
                auth()->user()->user_id ?? 1;

            $oldEducation->save();
        }
    }
}


protected function saveEmployeeAttachments(
    Request $request,
    int $staffId
) {
    $docTypes =
        $request->input(
            'doc_type',
            []
        );

    $uploadedFiles =
        $request->input(
            'uploaded_files',
            []
        );

    $actions =
        $request->input(
            'existing_attachment_action',
            []
        );

    if (!is_array($docTypes)) {
        $docTypes = [];
    }

    if (!is_array($uploadedFiles)) {
        $uploadedFiles = [];
    }

    if (!is_array($actions)) {
        $actions = [];
    }


    /*
    |--------------------------------------------------------------------------
    | Existing attachments
    |--------------------------------------------------------------------------
    */

    foreach ($actions as $attachmentId => $action) {

        if (!in_array(
            $action,
            [
                'keep',
                'delete',
                'replace'
            ],
            true
        )) {
            continue;
        }

        $attachment =
            StaffAttachmentModel::where(
                'sno',
                $attachmentId
            )
            ->where(
                'staff_id',
                $staffId
            )
            ->where(
                'status',
                '!=',
                2
            )
            ->first();

        if (!$attachment) {
            continue;
        }

        /*
         * KEEP
         */
        if ($action === 'keep') {
            continue;
        }

        /*
         * DELETE
         */
        if ($action === 'delete') {

            $attachment->status = 2;

            $attachment->updated_by =
                auth()->user()->user_id ?? 1;

            $attachment->save();

            continue;
        }

        /*
         * REPLACE is handled below if a new file
         * was supplied for this document.
         */
    }


    /*
    |--------------------------------------------------------------------------
    | New uploaded files
    |--------------------------------------------------------------------------
    */

    foreach ($uploadedFiles as $index => $fileList) {

        $documentId =
            $docTypes[$index] ?? null;

        if (!$documentId) {
            continue;
        }

        if (is_string($fileList)) {

            $decoded =
                json_decode(
                    $fileList,
                    true
                );

            $fileList =
                is_array($decoded)
                    ? $decoded
                    : [];
        }

        if (!is_array($fileList)) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        $safeFiles = [];

        foreach ($fileList as $file) {

            if (!is_string($file)) {
                continue;
            }

            $file = basename(
                trim($file)
            );

            if ($file === '') {
                continue;
            }

            /*
             * Only accept files actually existing
             * inside the application's temp directory.
             */
            $tempPath =
                public_path(
                    'staff_attachments/temp/' .
                    $file
                );

            if (!is_file($tempPath)) {
                continue;
            }

            $safeFiles[] = $file;
        }

        if (empty($safeFiles)) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing document
        |--------------------------------------------------------------------------
        */

        $existing =
            StaffAttachmentModel::where(
                'staff_id',
                $staffId
            )
            ->where(
                'document_id',
                $documentId
            )
            ->where(
                'status',
                '!=',
                2
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Determine action
        |--------------------------------------------------------------------------
        */

        $action = 'new';

        if ($existing) {

            $action =
                $actions[
                    $existing->sno
                ] ?? 'replace';
        }


        /*
        |--------------------------------------------------------------------------
        | Destination
        |--------------------------------------------------------------------------
        */

        $destination =
            public_path(
                'staff_attachments/' .
                $staffId .
                '/' .
                $documentId .
                '/'
            );

        if (!is_dir($destination)) {

            mkdir(
                $destination,
                0755,
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Replace existing files
        |--------------------------------------------------------------------------
        */

        if (
            $existing &&
            $action === 'replace'
        ) {

            $oldFiles =
                json_decode(
                    $existing->attachment_name,
                    true
                );

            if (!is_array($oldFiles)) {
                $oldFiles = [];
            }

            foreach ($oldFiles as $oldFile) {

                $oldFile =
                    basename(
                        (string) $oldFile
                    );

                $oldPath =
                    $destination .
                    $oldFile;

                if (
                    $oldFile !== '' &&
                    is_file($oldPath)
                ) {
                    @unlink($oldPath);
                }
            }

            $existingFiles = [];

        } elseif ($existing) {

            $existingFiles =
                json_decode(
                    $existing->attachment_name,
                    true
                );

            if (!is_array($existingFiles)) {
                $existingFiles = [];
            }

        } else {

            $existingFiles = [];
        }


        /*
        |--------------------------------------------------------------------------
        | Move files
        |--------------------------------------------------------------------------
        */

        $newFiles = [];

        foreach ($safeFiles as $file) {

            $extension =
                strtolower(
                    pathinfo(
                        $file,
                        PATHINFO_EXTENSION
                    )
                );

            $name =
                pathinfo(
                    $file,
                    PATHINFO_FILENAME
                );

            $name =
                preg_replace(
                    '/[^A-Za-z0-9_-]/',
                    '_',
                    $name
                );

            $finalName =
                $name .
                '_' .
                bin2hex(
                    random_bytes(8)
                );

            if ($extension !== '') {
                $finalName .= '.' . $extension;
            }

            $target =
                $destination .
                $finalName;

            if (!@rename(
                public_path(
                    'staff_attachments/temp/' .
                    $file
                ),
                $target
            )) {

                Log::warning(
                    'Employee attachment move failed.',
                    [
                        'staff_id' =>
                            $staffId,

                        'document_id' =>
                            $documentId,

                        'file' =>
                            $file,
                    ]
                );

                continue;
            }

            $newFiles[] =
                $finalName;
        }

        if (empty($newFiles)) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Save database record
        |--------------------------------------------------------------------------
        */

        $finalFiles =
            array_values(
                array_unique(
                    array_merge(
                        $existingFiles,
                        $newFiles
                    )
                )
            );

        if ($existing) {

            $existing->attachment_name =
                json_encode(
                    $finalFiles,
                    JSON_UNESCAPED_UNICODE
                );

            $existing->status = 1;

            $existing->updated_by =
                auth()->user()->user_id ?? 1;

            $existing->save();

        } else {

            $attachment =
                new StaffAttachmentModel();

            $attachment->staff_id =
                $staffId;

            $attachment->document_id =
                $documentId;

            $attachment->attachment_name =
                json_encode(
                    $finalFiles,
                    JSON_UNESCAPED_UNICODE
                );

            $attachment->status = 1;

            $attachment->created_by =
                auth()->user()->user_id ?? 1;

            $attachment->updated_by =
                auth()->user()->user_id ?? 1;

            $attachment->save();
        }
    }
}
  
}
