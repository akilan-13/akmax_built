<?php

namespace App\Http\Controllers\hr_management\hr_operation;

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
use App\Models\DocumentModel;
use App\Models\DocumentCheckListModel;
use App\Models\StaffAttachmentModel;
use App\Models\StaffModel;
use App\Models\UserRoleModel;
use App\Models\ManageEntityModel;
use App\Models\User;
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

class HROperation extends Controller
{
  public function index(Request $request)
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
    $general_setting=$helper->general_setting_data();

    $staffData = StaffModel::where('egc_staff.status', '!=', 2)
      ->select('egc_staff.*',
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
      ->whereIn('egc_staff.status', [0,1]);
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

        if($company_fill != ''){
          if($company_fill == 'egc'){
             $staffData->where('egc_staff.company_type',1);
          }else{
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

        $staffData=$staffData->orderBy('egc_staff.sno', 'desc')->paginate($perpage);
        
        foreach($staffData as $staff){
            if($staff->company_type == 1){
                  $staff->company_name=$general_setting->title;
                  $staff->company_base_color ='#ab2b22';
            }
            
           $educations = DB::table('egc_staff_education_info')
            ->select('egc_education.education')
            ->join('egc_education', 'egc_education.sno', '=', 'egc_staff_education_info.qualification_type')
            ->where('egc_staff_education_info.staff_id', $staff->sno) 
            ->where('egc_staff_education_info.status', 0)
            ->pluck('egc_education.education');
            $staff->education=$educations;
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
     
    return view('content.hr_management.hr_operation.leave_permission.leave_permission_list',[
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

  // exist Staff
  public function Departure_staff(Request $request)
  {
    // return $request;
    $id = $request->exist_staff_id;
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
        $staff->dep_reason      = $request->dep_reason;
        break;

      case 5:
        $staff->dep_reason = $request->dep_reason;
        break;

      case 6:
        $staff->staff_last_date = date('Y-m-d', strtotime($request->staff_last_date));
        $staff->dep_reason = $request->dep_reason;
        break;

      case 7:
        $staff->dep_reason = $request->dep_reason;
        break;

      default:
        session()->flash('toastr', [
          'type' => 'error',
          'message' => 'Could not add the Exit for the staff!',
        ]);
    }

    // Save the updated staff data
    if ($staff->save()) {
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
    $general_setting=$helper->general_setting_data();

    $staffData = StaffModel::where('egc_staff.status', '!=', 2)
      ->select('egc_staff.*',
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

        if($company_fill != ''){
          if($company_fill == 'egc'){
             $staffData->where('egc_staff.company_type',1);
          }else{
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

        $staffData=$staffData->orderBy('egc_staff.sno', 'desc')->paginate($perpage);
        
        foreach($staffData as $staff){
            if($staff->company_type == 1){
                  $staff->company_name=$general_setting->title;
                  $staff->company_base_color ='#ab2b22';

                  $educations = DB::table('egc_staff_education_info')
                  ->select('egc_education.education')
                  ->join('egc_education', 'egc_education.sno', '=', 'egc_staff_education_info.qualification_type')
                  ->where('egc_staff_education_info.staff_id', $staff->sno) 
                  ->where('egc_staff_education_info.status', 0)
                  ->pluck('egc_education.education');
                  $staff->education=$educations;
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
      return view('content.hr_management.hr_enroll.manage_staff.exit_staff_list',[
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
  

  public function View($id)
  {
    $data =  StaffModel::where('egc_staff.status', '!=', 2)
      ->select('egc_staff.*',
      'egc_entity.entity_name',
      'egc_entity.entity_short_name',
      'egc_company.company_name',
      'egc_company.company_base_color',
      'egc_department.department_name',
      'egc_division.division_name',
      'egc_languages.name as mother_tongue',
      'egc_job_role.job_position_name as job_role_name',
      )
      ->leftJoin('egc_company', 'egc_staff.company_id', 'egc_company.sno')
      ->leftJoin('egc_entity', 'egc_staff.entity_id', 'egc_entity.sno')
      ->leftJoin('egc_languages', 'egc_staff.mother_tongue', 'egc_languages.sno')
      ->join('egc_department', 'egc_staff.department_id', 'egc_department.sno')
      ->join('egc_division', 'egc_staff.division_id', 'egc_division.sno')
      ->join('egc_job_role', 'egc_staff.job_role_id', 'egc_job_role.sno')
      ->where('egc_staff.sno', $id)
      ->first();
      $languages_ids=$data->languages ? json_decode($data->languages):[];
      $hobby_ids=$data->hobby ? json_decode($data->hobby):[];
      $language_known=DB::table('egc_languages')->whereIn('sno',$languages_ids)->pluck('name');
      $hobbies=DB::table('egc_hobbies')->whereIn('sno',$hobby_ids)->pluck('hobby_name');
      $staff_family=DB::table('egc_staff_family')->where('staff_id',$id)->where('status','!=',2)->first();
      $data->language_known=$language_known;
      $data->hobbies=$hobbies;
      // family details
      $data->father_name=$staff_family? $staff_family->father_name :'';
      $data->father_occup=$staff_family? $staff_family->father_occup :'';
      $data->mother_name=$staff_family? $staff_family->mother_name :'';
      $data->mother_occup=$staff_family? $staff_family->mother_occup :'';
      $data->has_children=$staff_family? $staff_family->has_children :'';
      $data->children_count=$staff_family? $staff_family->children_count :'';
      $data->has_siblings=$staff_family? $staff_family->has_siblings :'';

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
    $relation=DB::table('egc_relationship_type')->whereIn('sno',$contact_person_relation_ids)->pluck('relationship_name');
    $data->relationship=$relation;

    $work_details = DB::table('egc_staff_work_info')
        ->select('egc_staff_work_info.*')
        ->where('egc_staff_work_info.staff_id', $id)
        ->where('egc_staff_work_info.status', '!=', 2)
        ->orderBy('egc_staff_work_info.sno','asc')
        ->get();
    
    $data->work_details =$work_details;

    $documents = DB::table('egc_staff_attachment')
    ->where('egc_staff_attachment.staff_id', $id)
    ->select('egc_staff_attachment.*','egc_documents.document_name')
    ->where('egc_staff_attachment.status', '!=', 2)
    ->join('egc_documents', 'egc_staff_attachment.document_id', '=', 'egc_documents.sno')
    ->get();
    $documentData = [];
    foreach ($documents as $doc) {
        $fileNames = json_decode($doc->attachment_name); // Decode the JSON file names
        $documentData[] = [
            'document_name' => $doc->document_name,
            'files' => array_map(function($fileName) use ($doc) {
                return asset("staff_attachments/{$doc->staff_id}/{$doc->document_id}/{$fileName}");
            }, $fileNames)
        ];
    }

    $qualification = DB::table('egc_staff_education_info')
    ->select('egc_staff_education_info.*','egc_education.education as qualification_name')
    ->where('egc_staff_education_info.status', '!=', 2)
    ->where('egc_staff_education_info.staff_id', $id)
    ->join('egc_education', 'egc_staff_education_info.qualification_type', '=', 'egc_education.sno')
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
    $data->documents =$documentData;
    $data->qualification =$qualification;
    $data->document_checkList =$document_checkList;
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
    return view('content.hr_management.hr_enroll.manage_staff.add_staff',[
        'social_media_list' => $social_media_list,
        'company_list' => $company_list,
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
    $mobile_no = $request->mobile_no;
    $gender = $request->gender ?? 1;
    $dob = $request->dob ? date('Y-m-d', strtotime($request->dob)) : NULL;
    $email_id = $request->email_id ?? NULL;
    $mother_tongue = $request->mother_tongue ?? NULL;
    $languages = $request->Languages ? json_encode($request->Languages) : NULL;
    $hobby = $request->hobby ? json_encode($request->hobby) : NULL;
    $description = $request->description ?? NULL;

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
      $siblings_detail = $request->siblings_detail ?? Null;

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
          
          $nick_name = $request->pseudo_name ?? NULL;
          $doj = $request->doj ? date('Y-m-d', strtotime($request->doj)) : NULL;
          $basic_salary = $request->basic_salary ?? 0;
          $per_hr_cost = $request->per_hr_cost ?? 0;
          $skill_tag = $request->skill_tag ? json_encode($request->skill_tag) : NULL;

          $login_access = $request->login_access ?? 0;
          $loginuser_name =$login_access == 1 ? $request->loginuser_name : Null;
          $loginpassword = $login_access == 1 ? $request->loginpassword : Null;
          $credential_check = $request->other_access ?? 0;

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
   
        // Create new staff record
        $add_staff = new StaffModel();
        $add_staff->staff_id = $staff_id;
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
        // $add_staff->alternative_no = $request->alternative_no;
        $add_staff->email_id = $request->email_id;
        $add_staff->exp_type = $work_exp_type;
        $add_staff->total_company_shift = $total_company_shift;
        $add_staff->total_experience = $total_experience;
        $add_staff->gender = $request->gender;
        $add_staff->hobby = $hobby ;
        $add_staff->mother_tongue = $mother_tongue;
        $add_staff->languages = $languages;
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
        $add_staff->description = $description ?? null;
        $add_staff->basic_salary = $basic_salary;
        $add_staff->per_hour_cost = $per_hr_cost;
        $add_staff->knowledge_tag = $skill_tag;
        $add_staff->credential = $credential_check;
        $add_staff->user_name = $loginuser_name;
        $add_staff->password  = $loginpassword;
        $add_staff->completion_percentage  = $completion_percentage;
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
      $add_family->children_details = $children_details;
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
            'degree_name' => $degree[$key],
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

    
    return view('content.hr_management.hr_enroll.manage_staff.edit_staff',[
      'staffData' => $staffData,
      'staffFamily' => $staffFamily,
      'staffWork' => $staffWork,
      'attachments' => $attachments,
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




  public function getStaffByBranch(Request $request)
  {
      $department_id = $request->department_id;

      $staff = StaffModel::where('egc_staff.department_id', $department_id)
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


      return response()->json([
          'status' => 200,
          'success' => true,
          'data' => $staff,
          'message' => 'Staff data fetched Successfully',
      ]);
  }
  public function getStaffByRole(Request $request)
  {
      $role_id = $request->role_id;

      $staff = StaffModel::where('egc_staff.job_role_id', $role_id)
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


      return response()->json([
          'status' => 200,
          'success' => true,
          'data' => $staff,
          'message' => 'Staff data fetched Successfully',
      ]);
  }
  public function getStaffByDepart(Request $request)
  {
      $department_id = $request->department_id;

      $staff = StaffModel::where('egc_staff.department_id', $department_id)
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


      return response()->json([
          'status' => 200,
          'success' => true,
          'data' => $staff,
          'message' => 'Staff data fetched Successfully',
      ]);
  }
  public function get_role_by_department(Request $request)
  {
      $department_id = $request->department_id;

      $staff = DB::table('egc_job_role')->where('egc_job_role.department_id', $department_id)
          ->where('egc_job_role.status',0)
          ->orderBy('egc_job_role.job_position_name', 'ASC')
          ->get();


      return response()->json([
          'status' => 200,
          'success' => true,
          'data' => $staff,
          'message' => 'role data fetched Successfully',
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

  public function save(Request $request)
{

return $request;
    DB::transaction(function() use ($request){

        $workflow = ApprovalWorkflow::updateOrCreate(
            [
                'company_id'=>$request->company_id,
                'entity_id'=>$request->entity_id,
                'department_id'=>$request->department_id,
                'role_id'=>$request->role_id
            ],
            ['updated_by'=>auth()->id()]
        );

        ApprovalWorkflowLevel::where('workflow_id',$workflow->id)->delete();

        foreach($request->levels as $level){
            ApprovalWorkflowLevel::create([
                'workflow_id'=>$workflow->id,
                'level_no'=>$level['level'],
                'company_id'=>$level['company'],
                'entity_id'=>$level['entity'],
                'department_id'=>$level['department'],
                'approver_staff_id'=>$level['staff_id']
            ]);
        }
    });

    return response()->json(['status'=>true]);
}


  function staffDataById(Request $request){
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
      $staff_id=$request->staff_id;
        
       $newStaff =  StaffModel::where('sno', $staff_id)->first();
      
      if($newStaff){
        $newStaff->welcome_status =1;
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
        $lead_mobile ='9677482114';

          $whatsappApi = DB::table('egc_api_integration')->where('api_key','egc_whatsapp_meta_api_1')->where('status',0)->orderBy('sno', 'desc')->first();
          if($whatsappApi){
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
             
          }else{
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
                $emailTemplate_id =1; 
                $emailTemplate = EmailTemplateModel::where('status', 0)->where('sno', $emailTemplate_id)->first();
    
            // Gender condition
            $genderPrefix = 'Mr/Ms'; // Default value
            // if ($lead->lead_gender == 1) {
            //   $genderPrefix = 'Mr';
            // } elseif ($lead->lead_gender == 2) {
            //   $genderPrefix = 'Ms';
            // }
            
            
            
           
            $baseURL= url('/');
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
    
            $content = str_replace('#employee_name', $staff_name?? 'Ak Max', $content);
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
            $url= 'www.elysiumgroup.com';
    
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
            
              
                  $templateName  = "hello_world";
                  // $templateName  = "welcome_all_test";
  
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
                      $headerImage = 'https://erp.elysiumtechnologies.com/public/assets/egc_images/OnBoard.jpg';
                      // $headerImage =  url('public/assets/egc_images/OnBoard.jpg');
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
                  }else{
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
                  // $languageCode = 'en';
                  $languageCode = 'en_US';
      
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
                if ($response['status'] == 200) {}
                else{
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


  public function myApprovals()
{
    return ApprovalTask::with('leave','employee')
        ->where('approver_staff_id',auth()->user()->staff_id)
        ->where('status',0)
        ->get();
}

public function reject(Request $request)
{
    $task = ApprovalTask::findOrFail($request->task_id);

    DB::transaction(function() use ($task){

        $task->update([
            'status'=>2,
            'action_date'=>now(),
            'remarks'=>request('remarks')
        ]);

        LeaveRequest::where('sno',$task->request_id)
            ->update(['status'=>2]);
    });

    return response()->json(['message'=>'Rejected']);
}


public function approve(Request $request)
{
    $task = ApprovalTask::where('sno',$request->task_id)
            ->where('approver_staff_id',auth()->user()->staff_id)
            ->firstOrFail();

    DB::transaction(function() use ($task){

        // mark approved
        $task->update([
            'status'=>1,
            'action_date'=>now(),
            'remarks'=>request('remarks')
        ]);

        $leave = LeaveRequest::find($task->request_id);

        // next level?
        $nextLevel = ApprovalTask::where('request_id',$leave->sno)
                        ->where('level_no','>',$task->level_no)
                        ->where('status',0)
                        ->orderBy('level_no')
                        ->first();

        if($nextLevel){
            // move to next approver
            $leave->update([
                'current_level'=>$nextLevel->level_no
            ]);
        }else{
            // FINAL APPROVED
            $leave->update([
                'status'=>1
            ]);
        }
    });

    return response()->json(['message'=>'Approved']);
}


public function createApprovalFlow($leaveRequestId)
{
    $leave = LeaveRequest::find($leaveRequestId);

    $workflow = ApprovalWorkflow::where([
        'company_id'=>$leave->company_id,
        'entity_id'=>$leave->entity_id,
        'department_id'=>$leave->department_id,
        'role_id'=>$leave->role_id
    ])->first();

    if(!$workflow){
        throw new \Exception('Approval workflow not configured');
    }

    $levels = ApprovalWorkflowLevel::where('workflow_id',$workflow->id)
                ->orderBy('level_no')
                ->get();

    foreach($levels as $level){

        ApprovalTask::create([
            'request_id'=>$leave->sno,
            'level_no'=>$level->level_no,
            'approver_staff_id'=>$level->approver_staff_id
        ]);
    }
}

public function getLeaveApprovalChain(Request $request)
{
    $staffId = $request->staff_id;

    // find staff department & role
    $staff = Staff::find($staffId);

    $workflow = ApprovalMatrix::where([
        'company_id' => $staff->company_id,
        'entity_id' => $staff->entity_id,
        'department_id' => $staff->department_id,
        'role_id' => $staff->role_id
    ])->first();

    if(!$workflow){
        return response()->json(['status'=>false]);
    }

    $levels = json_decode($workflow->levels,true);

    $result=[];

    foreach($levels as $lvl){

        $emp = Staff::find($lvl['staff_id']);

        if($emp){
            $result[]=[
                'staff_name'=>$emp->staff_name,
                'role_name'=>$emp->job_role_name,
                'role_type'=>$emp->role_type ?? 'Manager'
            ];
        }
    }

    return response()->json([
        'status'=>true,
        'data'=>$result
    ]);
}



// CREATE TABLE egc_approval_tasks (
//     sno BIGINT AUTO_INCREMENT PRIMARY KEY,

//     request_id BIGINT NOT NULL,
//     level_no INT NOT NULL,
//     approver_staff_id BIGINT NOT NULL,

//     status INT DEFAULT 0 COMMENT '0 Pending 1 Approved 2 Rejected',
//     action_date DATETIME NULL,
//     remarks TEXT NULL,

//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     INDEX idx_request (request_id),
//     INDEX idx_approver (approver_staff_id)
// );

  
}