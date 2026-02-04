<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\laravel_example\UserManagement;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\Crm;

use App\Http\Controllers\authentications\Login;
use App\Http\Controllers\authentications\NetworkHandle;
use App\Models\ManageEntityModel;
use App\Models\AiModel;

use App\Http\Controllers\errors\ErrorController;
//Settings 
use App\Http\Controllers\settings\GeneralSettings;
use App\Http\Controllers\settings\common\SmsTemplate;
use App\Http\Controllers\settings\common\EmailTemplate;
use App\Http\Controllers\settings\common\WhatsappTemplate;
use App\Http\Controllers\settings\common\FinancialYear;
use App\Http\Controllers\settings\common\CurrencyFormat;
use App\Http\Controllers\settings\common\Country;
use App\Http\Controllers\settings\common\State;
use App\Http\Controllers\settings\common\City;
use App\Http\Controllers\settings\common\TimeZone;
use App\Http\Controllers\settings\common\Internal_cugs;
use App\Http\Controllers\settings\entity\CredentialBook;
use App\Http\Controllers\settings\entity\SocialMedia;
use App\Http\Controllers\settings\entity\WebHookUrl;
use App\Http\Controllers\settings\entity\Grade;
use App\Http\Controllers\settings\entity\Level;

//Exam Settings
use App\Http\Controllers\settings\assessment\ExamBadge;
use App\Http\Controllers\settings\assessment\ExamCategory;
use App\Http\Controllers\settings\assessment\ExamSection;
use App\Http\Controllers\settings\assessment\ExamGuideLines;
use App\Http\Controllers\settings\assessment\JobRoleSchedule;

use App\Http\Controllers\AiUsageController;
use App\Http\Controllers\VideoSubmissionController;
use App\Http\Controllers\Admin\WebhookAdminController;

// HRM SETTINGS
use App\Http\Controllers\settings\hrm\Documents;
use App\Http\Controllers\settings\hrm\DocumentChecklist;
use App\Http\Controllers\settings\hrm\Metrics;
use App\Http\Controllers\settings\hrm\OnBoardingStaging;
use App\Http\Controllers\settings\hrm\OnBoardingCheckList;
use App\Http\Controllers\settings\hrm\HRQuestionnaire;
use App\Http\Controllers\settings\hrm\OnboardingQuestion;
use App\Http\Controllers\Admin\ApiConfig;

// Interview Setting
use App\Http\Controllers\settings\interview\InterviewMode;
use App\Http\Controllers\settings\interview\InterviewCategory;
use App\Http\Controllers\settings\interview\InterviewQuestion;

use App\Http\Controllers\settings\interview\FeedbackSection;
use App\Http\Controllers\settings\interview\FeedbackQuestion;

// RECRUITMENT SETTINGS
use App\Http\Controllers\settings\recruitment\ApplicantStatus;
use App\Http\Controllers\settings\recruitment\Qualification;
use App\Http\Controllers\settings\recruitment\QualificationLevel;
use App\Http\Controllers\settings\recruitment\Major;
use App\Http\Controllers\settings\recruitment\Experience;
use App\Http\Controllers\settings\recruitment\Source;
use App\Http\Controllers\settings\recruitment\CallStatus;
use App\Http\Controllers\settings\recruitment\MaritalStatus;

// Main menu 
// Control Panel
use App\Http\Controllers\control_panel\entity_hub\ManageCompany;
use App\Http\Controllers\control_panel\entity_hub\ManageEntity;
use App\Http\Controllers\control_panel\entity_hub\ManageBranch;
use App\Http\Controllers\control_panel\entity_hub\ManageHoliday;

use App\Http\Controllers\control_panel\user_management\ManageUsers;
use App\Http\Controllers\control_panel\user_management\UserRolePermission;
use App\Http\Controllers\control_panel\user_management\UserRole;
use App\Http\Controllers\control_panel\user_management\UserChart;
use App\Http\Controllers\control_panel\user_management\UserTimestamp;
use App\Http\Controllers\control_panel\communication_tool\NewsBroadcast;

use App\Http\Controllers\settings\common\BroadcastTheme;

// HR Management
use App\Http\Controllers\hr_management\hr_enroll\ManageStaff;
use App\Http\Controllers\hr_management\hr_enroll\OnboardingStaff;
use App\Http\Controllers\hr_management\hr_enroll\ManageAttendance;
// HR Operation
use App\Http\Controllers\hr_management\hr_operation\HROperation;

use App\Http\Controllers\hr_management\hr_recruiter\JobController;
use App\Http\Controllers\hr_management\hr_recruiter\ZoomController;
use App\Http\Controllers\hr_management\hr_recruiter\JobRequest;
use App\Http\Controllers\hr_management\hr_recruiter\InterviewStaging;
use App\Http\Controllers\hr_management\hr_recruiter\ManageCandidate;


//Exam Management
use App\Http\Controllers\hr_management\exam_management\ManageExam;
use App\Http\Controllers\hr_management\exam_management\ManageQuestionBank;
use App\Http\Controllers\hr_management\exam_management\ManageAssessment;
use App\Http\Controllers\hr_management\exam_management\ManageResult;

use App\Http\Controllers\hr_management\hr_training\ManageTraining;
use App\Http\Controllers\hr_management\hr_training\TrainingPlanner;

//Management SETTINGS
use App\Http\Controllers\settings\management\Department;
use App\Http\Controllers\settings\management\Division;
use App\Http\Controllers\settings\management\JobRole;

//Business SETTINGS
use App\Http\Controllers\settings\business\BusinessDepartment;
use App\Http\Controllers\settings\business\BusinessDivision;
use App\Http\Controllers\settings\business\BusinessJobRole;




// Login 
// Route::get('/', [Login::class, 'index'])->name('login');


Route::post('/switch-erp', [Login::class, 'switchToErp'])->name('switch.erp.portal');

    $entities = ManageEntityModel::where('status',0)->get();

    foreach ($entities as $entity) {
        Route::get("/sso-login/erp{$entity->sno}", [Login::class, 'verify'])
            ->name("erp{$entity->sno}.sso.login");
    }


// logout
Route::post('/logout', [Login::class, 'destroy'])->name('logout');
// login form
Route::get('/', [Login::class, 'index'])->name('login');
Route::post('/login', [Login::class, 'Login'])->name('login_auth');
Route::get('/get_staff', [Login::class, 'get_staff'])->name('get-staff');
Route::get('/forgot_password', [Login::class, 'forgot_password'])->name('forgot-password');
Route::get('/change_password', [Login::class, 'new_password'])->name('new-password');



// Settings Menu

    Route::middleware(['auth:web', 'timezone'])->group(function () {
    
    Route::get('/download-login-report', [Login::class, 'downloadLoginReport']);
    //updateProfile 
    Route::post('/update-profile', [Login::class, 'updateProfile'])->name('update_profile');

    
    
    
    Route::post('/portal/switch', function () {
        $current = session('portalState', 'off');
        session(['portalState' => $current === 'on' ? 'off' : 'on']);
    
        // Always redirect to dashboard
        return redirect('/dashboard');
    })->name('portal.switch');
    
    
// General Setting Start
Route::get('/settings/general_settings', [GeneralSettings::class, 'index'])->name('settings-general-settings');
Route::post('/general_settings-update', [GeneralSettings::class, 'Update'])->name('general_settings_update');
// General Setting End


// CurrencyFormat Setting Start
Route::get('/settings/currency_format', [CurrencyFormat::class, 'index'])->name('settings-common');
Route::get('/currency_format', [CurrencyFormat::class, 'List'])->name('currency_format');
Route::post('/add_currency_format', [CurrencyFormat::class, 'Add'])->name('add_currency_format');
Route::get('/currency_format_edit/{id}', [CurrencyFormat::class, 'Edit'])->name('currency_format_edit');
Route::post('/currency_format_update', [CurrencyFormat::class, 'Update'])->name('currency_format_update');
Route::delete('/currency_format_delete/{id}', [CurrencyFormat::class, 'Delete']);
Route::post('/currency_format_status/{id}', [CurrencyFormat::class, 'Status']);
// CurrencyFormat Setting End

// Country Setting Start
Route::get('/settings/country', [Country::class, 'index'])->name('settings-common');
Route::post('/add_country', [Country::class, 'Add'])->name('add_country');
Route::get('/country_edit', [Country::class, 'Edit'])->name('country_edit');
Route::post('/country_update', [Country::class, 'Update'])->name('country_update');
Route::delete('/country_delete/{id}', [Country::class, 'Delete'])->name('country_delete');
Route::post('/country_status_change/{id}', [Country::class, 'Status'])->name('country_status_change');
Route::get('/country', [Country::class, 'List'])->name('country');
Route::get('/country_ed_list', [Country::class, 'List_for_edit'])->name('country_ed_list');
// Country Setting End

// State Setting Start
Route::get('/settings/state', [State::class, 'index'])->name('settings-common');
Route::post('/add_state', [State::class, 'Add'])->name('add_state');
Route::get('/state_edit', [State::class, 'Edit'])->name('state_edit');
Route::post('/state_update', [State::class, 'Update'])->name('state_update');
Route::delete('/state_delete/{id}', [State::class, 'Delete'])->name('state_delete');
Route::delete('/batch_delete_permanently/{id}', [State::class, 'DeleteBatchPermanently'])->name('batch_delete_permanently');
Route::post('/state_status_change/{id}', [State::class, 'Status'])->name('state_status_change');
Route::get('/state', [State::class, 'List'])->name('state');
Route::get('/state_ed_list', [State::class, 'List_for_edit'])->name('state_ed_list');
// State Setting End

// City Setting Start
Route::get('/settings/city', [City::class, 'index'])->name('settings-common');
Route::post('/add_city', [City::class, 'Add'])->name('add_city');
Route::get('/city_edit', [City::class, 'Edit'])->name('city_edit');
Route::post('/city_update', [City::class, 'Update'])->name('city_update');
Route::delete('/city_delete/{id}', [City::class, 'Delete'])->name('city_delete');
Route::post('/city_status_change/{id}', [City::class, 'Status'])->name('city_status_change');
Route::get('/city', [City::class, 'List'])->name('city');
Route::get('/city_ed_list', [City::class, 'List_for_edit'])->name('city_ed_list');
// City Setting End

// TimeZone Setting Start
  Route::get('/settings/timezone', [TimeZone::class, 'index'])->name('settings-common');
  Route::get('/time_zone', [TimeZone::class, 'List'])->name('time_zone');
  Route::post('/add_time_zone', [TimeZone::class, 'Add'])->name('add_time_zone');
  Route::get('/time_zone_edit', [TimeZone::class, 'Edit'])->name('time_zone_edit');
  Route::post('/time_zone_update', [TimeZone::class, 'Update'])->name('time_zone_update');
  Route::delete('/time_zone_delete/{id}', [TimeZone::class, 'Delete'])->name('time_zone_delete');
  Route::post('/time_zone_status_change/{id}', [TimeZone::class, 'Status'])->name('time_zone_status_change');
// TimeZone Setting End

// Internal_cugs Setting Start
Route::get('/settings/internal_cugs', [Internal_cugs::class, 'index'])->name('settings-common');
 Route::post('/Internal_cugs_add', [Internal_cugs::class, 'Add'])->name('Internal_cugs_add');
  Route::post('/Internal_cugs_update', [Internal_cugs::class, 'Update'])->name('Internal_cugs_update');
  Route::delete('/Internal_cugs_delete/{id}', [Internal_cugs::class, 'Delete']);
// Internal_cugs Setting End

// CredentialBook Setting Start
  Route::get('/settings/credential_book', [CredentialBook::class, 'index'])->name('settings-entity');
  Route::get('/credential', [CredentialBook::class, 'List'])->name('credential');
  Route::post('/add_credential', [CredentialBook::class, 'Add'])->name('add_credential');
  Route::get('/credential_edit/{id}', [CredentialBook::class, 'Edit']);
  Route::post('/credential_update/{id}', [CredentialBook::class, 'Update']);
  Route::delete('/credential_delete/{id}', [CredentialBook::class, 'Delete']);
  Route::post('/credential_status/{id}', [CredentialBook::class, 'Status']);
// CredentialBook Setting End


// SocialMedia Setting Start
  Route::get('/settings/social_media', [SocialMedia::class, 'index'])->name('settings-entity');
  Route::get('/social_media', [SocialMedia::class, 'List'])->name('social_media');
  Route::post('/add_social_media', [SocialMedia::class, 'Add'])->name('add_social_media');
  Route::get('/social_media_edit/{id}', [SocialMedia::class, 'Edit']);
  Route::post('/social_media_update/{id}', [SocialMedia::class, 'Update']);
  Route::delete('/social_media_delete/{id}', [SocialMedia::class, 'Delete']);
  Route::post('/social_media_status/{id}', [SocialMedia::class, 'Status']);
// SocialMedia Setting End

// WebHook Url Setting Start
  Route::get('/settings/webhook_url', [WebHookUrl::class, 'index'])->name('settings-entity');
  Route::get('/webhook_url_list', [WebHookUrl::class, 'List'])->name('webhook_url_list');
  Route::post('/update_all_hook_url', [WebHookUrl::class, 'UpdateAll'])->name('update_all_hook_url');
  Route::post('/add_webhook_url', [WebHookUrl::class, 'Add'])->name('add_webhook_url');
  Route::get('/webhook_url_edit/{id}', [WebHookUrl::class, 'Edit']);
  Route::post('/webhook_url_update/{id}', [WebHookUrl::class, 'Update']);
  Route::delete('/webhook_url_delete/{id}', [WebHookUrl::class, 'Delete']);
  Route::post('/webhook_url_status/{id}', [WebHookUrl::class, 'Status']);
// WebHook Url Setting End

Route::get('/settings/sms_template', [SmsTemplate::class, 'index'])->name('settings-common');
Route::post('/add_sms_template', [SmsTemplate::class, 'Add'])->name('add_sms_template');
Route::get('/sms_template_edit', [SmsTemplate::class, 'Edit'])->name('sms_template_edit');
Route::post('/sms_template_update', [SmsTemplate::class, 'Update'])->name('sms_template_update');
Route::delete('/sms_template_delete/{id}', [SmsTemplate::class, 'Delete'])->name('sms_template_delete');
Route::post('/sms_template_status_change/{id}', [SmsTemplate::class, 'Status'])->name('sms_template_status_change');
Route::get('/sms/balance', [SmsTemplate::class, 'getBalance']);

Route::get('/settings/email_template', [EmailTemplate::class, 'index'])->name('settings-common');
Route::get('/settings/email_template/email_template_add', [EmailTemplate::class, 'EmailAdd'])->name('settings-common');
Route::post('/email_template_add', [EmailTemplate::class, 'Add'])->name('email_template_add');
Route::get('/settings/common/email_template/edit/{id}', [EmailTemplate::class, 'Edit'])->name('email_template_edit');
Route::post('/email_template_update', [EmailTemplate::class, 'Update'])->name('email_template_update');
Route::delete('/email_template_delete/{id}', [EmailTemplate::class, 'Delete'])->name('email_template_delete');
Route::post('/email_template_status/{id}', [EmailTemplate::class, 'Status'])->name('email_template_status');


Route::get('/settings/finiancial_year', [FinancialYear::class, 'index'])->name('settings-common');
Route::get('/settings/grade', [Grade::class, 'index'])->name('settings-common');
Route::get('/settings/level', [Level::class, 'index'])->name('settings-common');
// Route::get('/settings/social_media_profile', [SocialMediaProfile::class, 'index'])->name('settings-common');

//whatsapp template
Route::get('/settings/whatsapp_template', [WhatsappTemplate::class, 'index'])->name('settings-common');
 Route::get('/whatsapp_template_list', [WhatsappTemplate::class, 'List'])->name('whatsapp_template_list');
  Route::post('/whatsapp_template_add', [WhatsappTemplate::class, 'Add'])->name('whatsapp_template_add');
  Route::post('/whatsapp_template_update', [WhatsappTemplate::class, 'Update'])->name('whatsapp_template_update');
  Route::delete('/whatsapp_template_delete/{id}', [WhatsappTemplate::class, 'Delete'])->name('whatsapp_template_delete');
  Route::post('/whatsapp_template_status_change/{id}', [WhatsappTemplate::class, 'Status'])->name('whatsapp_template_status_change');
  Route::get('/whatsapp_templates', [WhatsappTemplate::class, 'showTemplates'])->name('whatsapp_templates');

// HRM SETTINGS

// Documents Setting Start
Route::get('/settings/document', [Documents::class, 'index'])->name('settings-hrm');
Route::post('/add_document', [Documents::class, 'Add'])->name('add_document');
Route::get('/document_edit', [Documents::class, 'Edit'])->name('document_edit');
Route::post('/document_update', [Documents::class, 'Update'])->name('document_update');
Route::delete('/document_delete/{id}', [Documents::class, 'Delete'])->name('document_delete');
Route::post('/document_status_change/{id}', [Documents::class, 'Status'])->name('document_status_change');
Route::get('/document_list', [Documents::class, 'List'])->name('document_list');
// Documents Setting End

// DocumentChecklist Setting Start
Route::get('/settings/document_checklist', [DocumentChecklist::class, 'index'])->name('settings-hrm');
Route::post('/add_document_checklist', [DocumentChecklist::class, 'Add'])->name('add_document_checklist');
Route::get('/document_checklist_edit', [DocumentChecklist::class, 'Edit'])->name('document_checklist_edit');
Route::post('/document_checklist_update', [DocumentChecklist::class, 'Update'])->name('document_checklist_update');
Route::delete('/document_checklist_delete/{id}', [DocumentChecklist::class, 'Delete'])->name('document_checklist_delete');
Route::post('/document_checklist_status_change/{id}', [DocumentChecklist::class, 'Status'])->name('document_checklist_status_change');
Route::get('/document_checklist_list', [DocumentChecklist::class, 'List'])->name('document_checklist_list');
// DocumentChecklist Setting End

// Metrics Setting Start
Route::get('/settings/metrics', [Metrics::class, 'index'])->name('settings-hrm');
Route::post('/add_metrics', [Metrics::class, 'Add'])->name('add_metrics');
Route::get('/metrics_edit', [Metrics::class, 'Edit'])->name('metrics_edit');
Route::post('/metrics_update', [Metrics::class, 'Update'])->name('metrics_update');
Route::delete('/metrics_delete/{id}', [Metrics::class, 'Delete'])->name('metrics_delete');
Route::post('/metrics_status_change/{id}', [Metrics::class, 'Status'])->name('metrics_status_change');
Route::get('/metrics_list', [Metrics::class, 'List'])->name('metrics_list');
// DocumentChecklist Setting End

Route::get('/settings/questionnaire', [HRQuestionnaire::class, 'index'])->name('settings-hrm');
Route::get('/settings/questionnaire/questionnaire_add', [HRQuestionnaire::class, 'add'])->name('settings-hrm');
Route::get('/settings/questionnaire/questionnaire_edit/{id}', [HRQuestionnaire::class, 'edit'])->name('settings-hrm');
Route::get('/hr_question_list', [HRQuestionnaire::class, 'List'])->name('hr_question_list');
Route::get('/hr_question_by_id/{id}', [HRQuestionnaire::class, 'ListDisplay'])->name('hr_question_by_id');
Route::post('/add_hr_question', [HRQuestionnaire::class, 'QuestionSave'])->name('add_hr_question');
Route::get('/hr_question_view/{id}', [HRQuestionnaire::class, 'View']);
Route::post('/hr_question_update', [HRQuestionnaire::class, 'Update'])->name('hr_question_update');
Route::delete('/hr_question_delete/{id}', [HRQuestionnaire::class, 'Delete']);
Route::post('/hr_question_status/{id}', [HRQuestionnaire::class, 'Status']);


Route::get('/settings/onboarding_staging', [OnBoardingStaging::class, 'index'])->name('settings-hrm');
Route::post('/add_onboarding_staging', [OnBoardingStaging::class, 'Add'])->name('add_onboarding_staging');
Route::get('/onboarding_staging_edit', [OnBoardingStaging::class, 'Edit'])->name('onboarding_staging_edit');
Route::post('/onboarding_staging_update', [OnBoardingStaging::class, 'Update'])->name('onboarding_staging_update');
Route::delete('/onboarding_staging_delete/{id}', [OnBoardingStaging::class, 'Delete'])->name('onboarding_staging_delete');
Route::post('/onboarding_staging_status_change/{id}', [OnBoardingStaging::class, 'Status'])->name('onboarding_staging_status_change');
Route::get('/onboarding_staging_list', [OnBoardingStaging::class, 'List'])->name('onboarding_staging_list');


Route::get('/settings/onboarding', [OnBoardingCheckList::class, 'index'])->name('settings-hrm');
Route::post('/add_onboarding_checklist', [OnBoardingCheckList::class, 'Add'])->name('add_onboarding_checklist');
Route::get('/onboarding_checklist_edit', [OnBoardingCheckList::class, 'Edit'])->name('onboarding_checklist_edit');
Route::post('/onboarding_checklist_update', [OnBoardingCheckList::class, 'Update'])->name('onboarding_checklist_update');
Route::delete('/onboarding_checklist_delete/{id}', [OnBoardingCheckList::class, 'Delete'])->name('onboarding_checklist_delete');
Route::post('/onboarding_checklist_status_change/{id}', [OnBoardingCheckList::class, 'Status'])->name('onboarding_checklist_status_change');
Route::get('/onboarding_checklist_list', [OnBoardingCheckList::class, 'List'])->name('onboarding_checklist_list');


// RECRUITMENT SETTINGS
Route::get('/settings/major', [Major::class, 'index'])->name('settings-recruitment');
Route::get('/settings/experience', [Experience::class, 'index'])->name('settings-recruitment');

Route::get('/settings/call_status', [CallStatus::class, 'index'])->name('settings-recruitment');
Route::get('/settings/marital_status', [MaritalStatus::class, 'index'])->name('settings-recruitment');

  // Department
  Route::get('/settings/department', [Department::class, 'index'])->name('settings-base-settings');
  Route::get('/department', [Department::class, 'List'])->name('department');
  Route::post('/add_department', [Department::class, 'Add'])->name('add_department');
  Route::get('/department_edit/{id}', [Department::class, 'Edit'])->name('department_edit');
  Route::post('/department_update', [Department::class, 'Update'])->name('department_update');
  Route::delete('/department_delete/{id}', [Department::class, 'Delete']);
  Route::post('/department_status/{id}', [Department::class, 'Status']);
  Route::get('/branch_department_list', [Department::class, 'BranchDepartList'])->name('branch_department_list');
  
  //Division
  Route::get('/settings/division', [Division::class, 'index'])->name('settings-base-settings');
  Route::post('/add_division', [Division::class, 'Add'])->name('add_division');
  Route::get('/division_edit/{id}', [Division::class, 'Edit'])->name('division_edit');
  Route::post('/division_update', [Division::class, 'Update'])->name('division_update');
  Route::post('/division_status/{id}', [Division::class, 'Status']);
  Route::delete('/division_delete/{id}', [Division::class, 'Delete']);
  Route::get('/get_division', [Division::class, 'DepartDivisionList'])->name('get_division');
  Route::get('/get_job_role', [Division::class, 'JobPostionList'])->name('get_job_role');
  Route::get('/get_job_role_by_entity', [Division::class, 'JobPostionListEntity'])->name('get_job_role_by_entity');

  // Job_Role
  Route::get('/settings/job_role', [JobRole::class, 'index'])->name('settings-hrm-settings');
  Route::get('/job_position', [JobRole::class, 'JobPostionList'])->name('job_position');
  Route::post('/add_job_role', [JobRole::class, 'Add'])->name('add_job_role');
  Route::get('/job_role_edit/{id}', [JobRole::class, 'Edit']);
   Route::post('/job_role_update', [JobRole::class, 'Update'])->name('job_role_update');
  Route::delete('/job_role_delete/{id}', [JobRole::class, 'Delete']);
  Route::post('/job_role_status/{id}', [JobRole::class, 'Status']);

  // business setting

   // Department
  Route::get('/settings/business/department', [BusinessDepartment::class, 'index'])->name('settings-base-settings');
  Route::get('/business_department', [BusinessDepartment::class, 'List'])->name('business_department');
  Route::post('/add_business_department', [BusinessDepartment::class, 'Add'])->name('add_business_department');
  Route::get('/business_department_edit/{id}', [BusinessDepartment::class, 'Edit'])->name('business_department_edit');
  Route::post('/business_department_update', [BusinessDepartment::class, 'Update'])->name('business_department_update');
  Route::delete('/business_department_delete/{id}', [BusinessDepartment::class, 'Delete']);
  Route::post('/business_department_status/{id}', [BusinessDepartment::class, 'Status']);
  Route::post('/old-department-add', [BusinessDepartment::class, 'AddOldData'])->name('old-department-add');
  
  //Division
  Route::get('/settings/business/division', [BusinessDivision::class, 'index'])->name('settings-base-settings');
  Route::post('/add_business_division', [BusinessDivision::class, 'Add'])->name('add_business_division');
  Route::get('/business_division_edit/{id}', [BusinessDivision::class, 'Edit'])->name('business_division_edit');
  Route::post('/business_division_update', [BusinessDivision::class, 'Update'])->name('business_division_update');
  Route::post('/business_division_status/{id}', [BusinessDivision::class, 'Status']);
  Route::delete('/business_division_delete/{id}', [BusinessDivision::class, 'Delete']);
  Route::post('/old-division-add', [BusinessDivision::class, 'AddOldData'])->name('old-division-add');

  // Job_Role
  Route::get('/settings/business/job_role', [BusinessJobRole::class, 'index'])->name('settings-hrm-settings');
  Route::get('/business_job_position', [BusinessJobRole::class, 'JobPostionList'])->name('business_job_position');
  Route::post('/add_business_job_role', [BusinessJobRole::class, 'Add'])->name('add_business_job_role');
  Route::get('/business_job_role_edit/{id}', [BusinessJobRole::class, 'Edit']);
   Route::post('/business_job_role_update', [BusinessJobRole::class, 'Update'])->name('business_job_role_update');
  Route::delete('/business_job_role_delete/{id}', [BusinessJobRole::class, 'Delete']);
  Route::post('/business_job_role_status/{id}', [BusinessJobRole::class, 'Status']);
  Route::post('/old-jobRole-add', [BusinessJobRole::class, 'AddOldData'])->name('old-jobRole-add');

  // Api Config Setting Start
Route::get('/settings/api_config', [ApiConfig::class, 'index'])->name('settings-apiconfig-settings');
Route::post('/add_integration', [ApiConfig::class, 'Create'])->name('add_integration');
Route::post('/integration_status/{id}', [ApiConfig::class, 'Status']);
Route::delete('/delete_integration/{id}', [ApiConfig::class, 'Delete']);
Route::get('/integration_edit/{id}', [ApiConfig::class, 'Edit']);
Route::post('/update_integration', [ApiConfig::class, 'Update'])->name('update_integration');

// Main Page Route
Route::get('/dashboard', [Crm::class, 'index'])->name('dashboard');

// Control Panel
// Manage Entity
Route::get('/entity_hub/manage_entity', [ManageEntity::class, 'index'])->name('entity-hub-manage-entity');
Route::get('/entity_dropdown_list', [ManageEntity::class, 'DropdownList'])->name('entity_dropdown_list');
Route::post('/add_entity', [ManageEntity::class, 'Add'])->name('add_entity');
Route::get('/entity_list', [ManageEntity::class, 'List'])->name('entity_list');
Route::post('/update_entity', [ManageEntity::class, 'Update'])->name('update_entity');
Route::post('/entity_status/{id}', [ManageEntity::class, 'Status']);
Route::delete('/entity_delete/{id}', [ManageEntity::class, 'Delete']);
Route::get('/entity_view/{id}', [ManageEntity::class, 'View']);
Route::post('/check_duplicate_entities', [ManageEntity::class, 'checkDuplicates']);

// manage Holiday
Route::get('/entity_hub/manage_holiday', [ManageHoliday::class, 'index'])->name('entity-hub-manage-holiday');
Route::get('/entity_hub/holiday_calender', [ManageHoliday::class, 'HolidayCalender'])->name('entity-hub-manage-holiday');
 Route::get('/holiday/sample-excel', [ManageHoliday::class, 'sampleHolidayEntryExcel']);
Route::post('/upload_holiday_calender', [ManageHoliday::class, 'UploadHoliday'])->name('upload_holiday_calender');
Route::get('/holiday-calendar/events', [ManageHoliday::class, 'calendarEvents']);
Route::get('/holiday_dropdown_list', [ManageHoliday::class, 'DropdownList'])->name('holiday_dropdown_list');
Route::post('/holiday_add', [ManageHoliday::class, 'Add'])->name('holiday_add');
Route::get('/holiday_list', [ManageHoliday::class, 'List'])->name('holiday_list');
Route::post('/holiday_update', [ManageHoliday::class, 'Update'])->name('holiday_update');
Route::post('/holiday_status/{id}', [ManageHoliday::class, 'Status']);
Route::delete('/holiday_delete/{id}', [ManageHoliday::class, 'Delete']);
Route::get('/holiday_view/{id}', [ManageHoliday::class, 'View']);
Route::post('/check_duplicate_holiday', [ManageHoliday::class, 'checkDuplicates']);

// Manage Company
Route::get('/entity_hub/manage_company', [ManageCompany::class, 'index'])->name('entity-hub-manage-company');
  
  Route::post('/change-branch', [ManageCompany::class, 'changeBranch'])->name('change-branch');
  Route::post('/change-role', [ManageCompany::class, 'changeRole'])->name('change-role');
  Route::post('/get-users-by-role', [ManageCompany::class, 'getUsersByRole'])->name('get-users-by-role');
  Route::post('/change-user', [ManageCompany::class, 'changeUser'])->name('change-user');
    Route::post('/add_company', [ManageCompany::class, 'Add'])->name('add_company');
  Route::get('/company_list', [ManageCompany::class, 'List'])->name('company_list');
  Route::post('/update_company', [ManageCompany::class, 'Update'])->name('update_company');
  Route::post('/company_status/{id}', [ManageCompany::class, 'Status']);
  Route::delete('/company_delete/{id}', [ManageCompany::class, 'Delete']);
  Route::get('/company_view/{id}', [ManageCompany::class, 'View']);
  Route::post('/check_duplicates_company', [ManageCompany::class, 'checkDuplicates']);

  Route::get('/entity_list_by_company/{id}', [ManageCompany::class, 'EntityListByCompany'])->name('entity_list_by_company');

// Manage Branch
// Route::get('/entity_hub/manage_branch', [ManageBranch::class, 'index'])->name('entity-hub-manage-branch');

// Route::get('/branch/edit', [ManageBranch::class, 'edit'])->name('entity-hub-manage-branch');


// Manage Branch Start
 Route::get('/entity_hub/manage_branch', [ManageBranch::class, 'index'])->name('entity-hub-manage-branch');

 Route::get('/entity_branch_dropdown_list', [ManageBranch::class, 'Entity_branch_dropdown_list'])->name('entity_branch_dropdown_list');
  Route::get('/branch/create', [ManageBranch::class, 'create_branch_franchise'])->name('branch-management-manage-branch');
  Route::get('/edit_branch_franchise/{id}', [ManageBranch::class, 'Edit'])->name('branch-management-manage-branch');
  Route::post('/branch_status/{id}', [ManageBranch::class, 'Status']);
  Route::delete('/branch_delete/{id}', [ManageBranch::class, 'Delete']);
  Route::get('/branch_view/{id}', [ManageBranch::class, 'View']);
  Route::get('/create_branch_franchise', [ManageBranch::class, 'create_branch_franchise'])->name('branch');
  Route::post('/add_branch_franchise', [ManageBranch::class, 'Add'])->name('add_branch_franchise');
  Route::post('/update_branch_franchise', [ManageBranch::class, 'Update'])->name('update_branch_franchise');
  Route::post('/assign_center_head', [ManageBranch::class, 'AssignCenterHead'])->name('assign_center_head');
  Route::post('/branch/Add_staff_branch', [ManageBranch::class, 'Add_staff_branch'])->name('Add_staff_branch');
  Route::get('/bran_drop_down', [ManageBranch::class, 'List'])->name('bran_drop_down');
  Route::get('/get_course_branch', [ManageBranch::class, 'get_course_by_type'])->name('get_course_branch');
  Route::get('/job_position_branch', [ManageBranch::class, 'Job_postion_to_Cug'])->name('job_position_branch');
  Route::get('/get_staff_data_based_on_role/{id}', [ManageBranch::class, 'Staff_based_on_role'])->name('get_staff_data_based_on_role');
  Route::post('/assign_cug_Detail', [ManageBranch::class, 'AssignCugDetails'])->name('assign_cug_Detail');
  Route::post('/edit_cug_to_staff', [ManageBranch::class, 'Update_staff_Cug_modal'])->name('edit_cug_to_staff');
  Route::get('/cre_dropdown_for_branch', [ManageBranch::class, 'Cre_Dropdown'])->name('cre_dropdown_for_branch');
  Route::get('/cug_details', [ManageBranch::class, 'Cug_Edit_Fetch'])->name('cug_details');
  Route::delete('/cug_delete', [ManageBranch::class, 'Cug_delete'])->name('cug_delete');
  Route::get('/check_unique_mobile_number', [ManageBranch::class, 'checkUniqueMobileNumber'])->name('check_unique_mobile_number');
  Route::post('/check-branch-duplicates', [ManageBranch::class, 'checkDuplicates']);

  Route::get('/staff_access_app/{id}', [ManageBranch::class, 'StaffAccessApp']);
  Route::post('update_staff_access', [ManageBranch::class, 'updateStaffAccess'])->name('update_staff_access');

Route::get('/branch/add', [ManageBranch::class, 'addPage'])->name('entity-hub-manage-branch');
Route::get('/branch/edit/{id}', [ManageBranch::class, 'Edit'])->name('entity-hub-manage-branch');

// Manage Branch End

// User Management
// Manage Users
Route::get('/user_management/manage_users', [ManageUsers::class, 'index'])->name('user-management-manage-users'); 
Route::get('/user_role_manage', [ManageUsers::class, 'List'])->name('user_role_manage');
Route::get('/staff/view/{id}', [ManageUsers::class, 'View'])->name('staff.view');

// User Role

Route::get('/user_management/manage_permission', [UserRolePermission::class, 'index'])->name('user-management-user-role-permission'); 
Route::get('/user_management/manage_permission/create_manage_users', [UserRolePermission::class, 'users_add'])->name('user-management-user-role-permission');
Route::get('/user_management/manage_permission/update_role_permision/{id}', [UserRolePermission::class, 'users_edit'])->name('user-management-user-role-permission');
Route::get('/user_management/manage_permission/view_role', [UserRolePermission::class, 'view'])->name('user-management-user-role-permission');

Route::get('/user_management/manage_business_permission', [UserRolePermission::class, 'indexBusiness'])->name('user-management-user-role-permission'); 

Route::get('/users/view_manage_users/{id}', [UserRolePermission::class, 'users_view'])->name('users-manage-users');
Route::post('/add_user_role_permission', [UserRolePermission::class, 'Add'])->name('add_user_role_permission');
Route::post('/edit_user_role_permission/{id}', [UserRolePermission::class, 'Edit']);
Route::post('/update_user_role_permission/{id}', [UserRolePermission::class, 'Update'])->name('update_user_role_permission');
Route::delete('/user_role_permission_delete/{id}', [UserRolePermission::class, 'Delete']);
Route::post('/user_role_permission_status/{id}', [UserRolePermission::class, 'Status']);


Route::get('/hr_recruitment/job_request', [JobRequest::class, 'index'])->name('hrm-hr-management-recruitment-manage-job-request');
Route::get('/hr_recruitment/interview_schedule/{id}', [JobRequest::class, 'InterviewSchedule'])->name('hrm-hr-management-recruitment-manage-job-request');
Route::post('/add_job_request', [JobRequest::class, 'Add'])->name('add_job_request');
Route::post('/create-interview-schedule', [JobRequest::class, 'createInterviewSchedule'])->name('create-interview-schedule');
Route::get('/job_request_edit/{id}', [JobRequest::class, 'Edit'])->name('job_request_edit');
Route::post('/update_job_request', [JobRequest::class, 'Update'])->name('update_job_request');
Route::get('/job_request_list', [JobRequest::class, 'List'])->name('job_request_list');
Route::get('/interview_questions_by_role_categ', [JobRequest::class, 'interviewQuestionsByRole'])->name('interview_questions_by_role_categ');
Route::post('/job_request_status/{id}', [JobRequest::class, 'Status']);
Route::delete('/job_request_delete/{id}', [JobRequest::class, 'Delete']);

Route::post('/add_job_candidate', [JobRequest::class, 'AddJobCandidate'])->name('add_job_candidate');
Route::get('/applicant_list_by_job_request', [JobRequest::class, 'ApplyCandidateListByJob'])->name('applicant_list_by_job_request');
Route::post('/update_shortlist_candidate', [JobRequest::class, 'UpdateShortlistCandidate'])->name('update_shortlist_candidate');
Route::match(['get', 'post'], '/qr_job_request_scanners', [JobRequest::class, 'Scanner_view'])->name('qr.scanner.job_request');
Route::match(['get', 'post'], '/qr.scanner.job_request.map', [JobRequest::class, 'MultiMap'])->name('qr.scanner.job_request.map');
Route::get('/not_apply_list_by_job_request', [JobRequest::class, 'NotAppliedListByJob'])->name('not_apply_list_by_job_request');
Route::get('/major_list_by_qualification', [JobRequest::class, 'majorListByQualification'])->name('major_list_by_qualification');

Route::get('get_interview_schedule_by_job_id', [JobRequest::class, 'getInterviewScheduleByJobId'])->name('get_interview_schedule_by_job_id');


Route::get('/hr_recruitment/manage_candidate', [ManageCandidate::class, 'index'])->name('hrm-hr-management-recruitment-manage-candidate');

Route::get('/hr_recruitment/interview_staging', [InterviewStaging::class, 'index'])->name('hrm-hr-management-recruitment-interview-staging');
Route::get('/monitoring_candidate_list', [InterviewStaging::class, 'candidateList'])->name('monitoring_candidate_list');
Route::post('/hr/candidate/hire', [InterviewStaging::class, 'hireCandidate'])
    ->name('hr.hire.candidate');

Route::post('/hr/candidate/shortlist', [InterviewStaging::class, 'shortlistCandidate'])
    ->name('hr.shortlist.candidate');
Route::post('/hr/candidate/reject', [InterviewStaging::class, 'rejectCandidate'])
    ->name('hr.reject.candidate');
Route::get('/interview-media/{answer}', [InterviewStaging::class, 'stream'])
    ->name('interview.media');

Route::post('/send_confirmation_bulk', [InterviewStaging::class, 'sendConfirmationBulk'])->name('send_confirmation_bulk');
Route::get('/job-confirmation/{token}', [InterviewStaging::class, 'jobConfirmationPage'])
    ->name('job.confirmation.page');

Route::post('/job-confirmation/submit', [InterviewStaging::class, 'jobConfirmationSubmit'])
    ->name('job.confirmation.submit');
    
Route::get('/job-confirmation/thank-you-accept/{token}', [InterviewStaging::class, 'jobConfirmationAccept'])
    ->name('job.confirmation.thankyou.accept');
Route::get('/job-confirmation/thank-you-decline/{token}', [InterviewStaging::class, 'jobConfirmationDecline'])
    ->name('job.confirmation.thankyou.decline');
Route::get('/job-confirmation/thank-you-expired/{token}', [InterviewStaging::class, 'jobConfirmationExpired'])
    ->name('job.confirmation.thankyou.expired');



// user Role Multiple
  Route::get('/user_management/user_role', [UserRole::class, 'index'])->name('user-management-role');
  Route::get('/user_role', [UserRole::class, 'List'])->name('user_role');
  Route::get('/user_role_by_entity', [UserRole::class, 'ListByEntity'])->name('user_role_by_entity');
  Route::post('/add_user_role', [UserRole::class, 'Add'])->name('add_user_role');
  Route::get('/user_role_edit/{id}', [UserRole::class, 'Edit']);
  Route::post('/user_role_update/{id}', [UserRole::class, 'Update']);
  Route::delete('/user_role_delete/{id}', [UserRole::class, 'Delete']);
  Route::post('/user_role_status/{id}', [UserRole::class, 'Status']);
  Route::get('/role/export-excel', [UserRole::class, 'ExportExcel']);

  Route::get('/user_management/business_user_role', [UserRole::class, 'businessIndex'])->name('user-management-role');
  Route::get('/business_user_role', [UserRole::class, 'businessList'])->name('business_user_role');
  Route::post('/add_business_user_role', [UserRole::class, 'Add'])->name('add_business_user_role');
  Route::get('/business_user_role_edit/{id}', [UserRole::class, 'businessEdit']);
  Route::post('/business_user_role_update/{id}', [UserRole::class, 'businessUpdate']);
  Route::delete('/business_user_role_delete/{id}', [UserRole::class, 'businessDelete']);
  Route::post('/business_user_role_status/{id}', [UserRole::class, 'businessStatus']);
  Route::post('/old-userRole-add', [UserRole::class, 'AddOldData'])->name('old-userRole-add');
  
// User Chart
Route::get('/user_management/user_chart', [UserChart::class, 'index'])->name('user-management-user-chart'); 

// User Timestamp
Route::get('/hr_management/staff_timechamp', [UserTimestamp::class, 'index'])->name('hrm-hr-management-operation-timechamp'); 
Route::post('/update_staff_timestamp_report', [UserTimestamp::class, 'UpdateTimestampReport'])->name('update_staff_timestamp_report');
Route::get('/get_staff_daily_timestamp', [UserTimestamp::class, 'getDailySummaryByEmployeeId'])->name('get_staff_daily_timestamp');

// HR Enroll
// Manage Staff
Route::get('/hr_enroll/manage_staff', [ManageStaff::class, 'index'])->name('hrm-hr-management-enroll-manage-staff');
Route::post('/add_old_staff', [ManageStaff::class, 'AddOldStaff'])->name('add_old_staff');
Route::get('/hr_enroll/exit_staff', [ManageStaff::class, 'exit_staff'])->name('hrm-hr-management-enroll-manage-staff');
Route::get('/hr_enroll/manage_staff/add_staff', [ManageStaff::class, 'staff_add'])->name('hrm-hr-management-enroll-manage-staff');
Route::get('/hr_enroll/manage_staff/update_staff/{id}', [ManageStaff::class, 'edit'])->name('hrm-hr-management-enroll-manage-staff');
Route::get('hr_enroll/manage_staff/orientation_edit', [ManageStaff::class, 'Orientation'])->name('manage-staff-staff-list');
Route::post('/add_staff', [ManageStaff::class, 'Add'])->name('add_staff');
Route::post('/update_staff', [ManageStaff::class, 'Update'])->name('update_staff');
Route::post('/update_staff_by_stage', [ManageStaff::class, 'UpdateStaffStage'])->name('update_staff_by_stage');
Route::get('/staff_list_by_branch_id/{id}', [ManageStaff::class, 'StaffListByBranch'])->name('staff_list_by_branch_id');

Route::get('/staff_view/{id}', [ManageStaff::class, 'View']);

Route::get('/staff', [ManageStaff::class, 'List'])->name('staff');
Route::post('/staff_status/{id}', [ManageStaff::class, 'Status']);
Route::delete('/staff_delete/{id}', [ManageStaff::class, 'Delete']);
Route::get('/get_per_hour_cost_staff', [ManageStaff::class, 'get_per_hour_cost_staff'])->name('get_per_hour_cost_staff');
Route::post('/check-staff-mobile-exists', [ManageStaff::class, 'checkStaffMobileExists'])->name('checkStaffMobileExists');
Route::post('/checkunique_mobile_edit', [ManageStaff::class, 'checkStaffMobileExists_edit'])->name('checkunique_mobile_edit');
Route::post('/checkunique_user_name', [ManageStaff::class, 'checkunique_user_name'])->name('checkunique_user_name');
Route::post('/checkunique_user_name_edit', [ManageStaff::class, 'checkunique_user_name_edit'])->name('checkunique_user_name_edit');
// upload temp
Route::post('/upload-temp-documentstaff', [ManageStaff::class, 'uploadTempDocument'])->name('upload-temp-documentstaff');
Route::post('/delete-temp-documentstaff', [ManageStaff::class, 'deleteTempDocument'])->name('delete-temp-documentstaff');
Route::post('/update_welcome_status', [ManageStaff::class, 'UpdateWelcomeStatus'])->name('update_welcome_status');

Route::get('/get_staff_by_id', [ManageStaff::class, 'staffDataById'])->name('get_staff_by_id');
Route::post('/departure_staff', [ManageStaff::class, 'Departure_staff'])->name('departure_staff');

// Manage Attendance
Route::get('/hr_enroll/manage_attendance', [ManageAttendance::class, 'index'])->name('hrm-hr-management-general-manage-attendance');
Route::get('/staff_att', [ManageAttendance::class, 'getStaff'])->name('staff_att');
Route::get('/get_staff_attendance_by_date', [ManageAttendance::class, 'getStaffAttendanceByDate'])->name('get_staff_attendance_by_date');
Route::post('/staff_attendance', [ManageAttendance::class, 'Add'])->name('staff_attendance');
Route::post('/update_staff_attendance', [ManageAttendance::class, 'Update'])->name('update_staff_attendance');
Route::get('/get_month_staff_attendance_by_id', [ManageAttendance::class, 'getMonthStaffAttendanceById'])->name('get_month_staff_attendance_by_id');

Route::post('/upload_essl', [ManageAttendance::class, 'UploadEssl'])->name('upload_essl');
// 29-01-26
Route::post('/fetchStaffMonthlyAttendance', [ManageAttendance::class, 'fetchStaffMonthlyAttendance'])->name('fetchStaffMonthlyAttendance');
Route::post('/fetchIndividualStaffAttendance', [ManageAttendance::class, 'fetchIndividualStaffAttendance'])->name('fetchIndividualStaffAttendance');


Route::get('/staff_attendance_chart', [ManageAttendance::class, 'GetAttendanceChartData'])->name('staff_attendance_chart');
Route::get('/staff_attendance_chart_present', [ManageAttendance::class, 'GetAttendanceChartDataPresent'])->name('staff_attendance_chart_present');

Route::get('/get-company-staff', [ManageStaff::class, 'getCompanyStaff']);
Route::post('/bulk-update-staff-id', [ManageStaff::class, 'bulkUpdateStaffId']);


// Onboarding Staff
Route::get('/hr_enroll/onboarding_staff', [OnboardingStaff::class, 'index'])->name('hrm-hr-management-enroll-onboarding-staff');

// Recruitment Setting
// ApplicantStatus
  Route::get('/settings/applicant_status', [ApplicantStatus::class, 'index'])->name('settings-recruitment');
  Route::get('/applicant_status', [ApplicantStatus::class, 'List'])->name('applicant_status');
  Route::post('/add_applicant_status', [ApplicantStatus::class, 'Add'])->name('add_applicant_status');
  Route::get('/applicant_status_edit/{id}', [ApplicantStatus::class, 'Edit'])->name('applicant_status_edit');
  Route::post('/applicant_status_update', [ApplicantStatus::class, 'Update'])->name('applicant_status_update');
  Route::delete('/applicant_status_delete/{id}', [ApplicantStatus::class, 'Delete']);
  Route::post('/applicant_status_change/{id}', [ApplicantStatus::class, 'Status']);

  // Qualification
  Route::get('/settings/qualification', [Qualification::class, 'index'])->name('settings-recruitment');
  Route::get('/qualification', [Qualification::class, 'List'])->name('qualification');
  Route::post('/add_qualification', [Qualification::class, 'Add'])->name('add_qualification');
  Route::get('/qualification_edit/{id}', [Qualification::class, 'Edit'])->name('qualification_edit');
  Route::post('/qualification_update', [Qualification::class, 'Update'])->name('qualification_update');
  Route::delete('/qualification_delete/{id}', [Qualification::class, 'Delete']);
  Route::post('/qualification_status_change/{id}', [Qualification::class, 'Status']);

   // Qualification Level
  Route::get('/settings/qualification_level', [QualificationLevel::class, 'index'])->name('settings-recruitment');
  Route::get('/qualification_level', [QualificationLevel::class, 'List'])->name('qualification_level');
  Route::post('/add_qualification_level', [QualificationLevel::class, 'Add'])->name('add_qualification_level');
  Route::get('/qualification_level_edit/{id}', [QualificationLevel::class, 'Edit'])->name('qualification_level_edit');
  Route::post('/qualification_level_update', [QualificationLevel::class, 'Update'])->name('qualification_level_update');
  Route::delete('/qualification_level_delete/{id}', [QualificationLevel::class, 'Delete']);
  Route::post('/qualification_level_status_change/{id}', [QualificationLevel::class, 'Status']);
  
   // Qualification Level
  Route::get('/settings/major', [Major::class, 'index'])->name('settings-recruitment');
  Route::get('/major_list', [Major::class, 'List'])->name('major_list');
  Route::post('/add_major', [Major::class, 'Add'])->name('add_major');
  Route::get('/major_edit/{id}', [Major::class, 'Edit'])->name('major_edit');
  Route::post('/major_update', [Major::class, 'Update'])->name('major_update');
  Route::delete('/major_delete/{id}', [Major::class, 'Delete']);
  Route::post('/major_status_change/{id}', [Major::class, 'Status']);

   // Source
  Route::get('/settings/source', [Source::class, 'index'])->name('settings-recruitment');
  Route::get('/source_list', [Source::class, 'List'])->name('source_list');
  Route::post('/add_source', [Source::class, 'Add'])->name('add_source');
  Route::get('/source_edit/{id}', [Source::class, 'Edit'])->name('source_edit');
  Route::post('/source_update', [Source::class, 'Update'])->name('source_update');
  Route::delete('/source_delete/{id}', [Source::class, 'Delete']);
  Route::post('/source_status_change/{id}', [Source::class, 'Status']);

// Leave & Permsiison
Route::get('/hr_operation/leave_permission', [HROperation::class, 'index'])->name('hrm-hr-management-operation-leave-permission');
Route::post('/add_leave_perm_request', [HROperation::class, 'AddLeavePermission'])->name('add_leave_perm_request');
Route::get('/get_staff_by_branch', [HROperation::class, 'getStaffByBranch'])->name('get_staff_by_branch');
Route::get('/get_staff_by_role', [HROperation::class, 'getStaffByRole'])->name('get_staff_by_role');
Route::get('/get_staff_by_depart', [HROperation::class, 'getStaffByDepart'])->name('get_staff_by_depart');
Route::post('/approval_matrix_save', [HROperation::class, 'save'])->name('approval_matrix_save');
Route::get('/get_role_by_department', [HROperation::class, 'get_role_by_department'])->name('get_role_by_department');
Route::get('/get-leave-approval-chain',[HROperation::class,'getLeaveApprovalChain'])
    ->name('get_leave_approval_chain');


  
  // Manage Exam
        Route::match(['get', 'post'], '/manage_exam', [ManageExam::class, 'index'])->name('hrm-hr-management-assessment-manage-exam');
        Route::get('/manage_exam/add', [ManageExam::class, 'showCreateExam'])->name('hrm-hr-management-assessment-manage-exam');
        Route::get('/fetch_job_roles', [ManageExam::class, 'fetchJobRoles']);
        Route::post('/manage_exam/create', [ManageExam::class, 'Create'])->name('manage_exam_create');
        Route::get('/edit_manage_exam/{id}', [ManageExam::class, 'Edit']);
        Route::get('/view_manage_exam/{id}', [ManageExam::class, 'View'])->name('hrm-hr-management-assessment-manage-exam');
        Route::post('/manage_exam/update', [ManageExam::class, 'Update'])->name('manage_exam_update');
        Route::post('/status_manage_exam/{id}', [ManageExam::class, 'Status']);
        Route::get('/level_based_exam_questions', [ManageExam::class, 'levelBasedQuestion']);
        Route::get('/fetch_question_bank_name/{id}', [ManageExam::class, 'fetchQuestionBankName']);
        Route::get('/edit_level_based_exam_questions/{sno}', [ManageExam::class, 'editLevelQuestions']);
        Route::get('/check-exam-process/{id}', [ManageExam::class, 'checkExamProcess']);
        Route::delete('/delete-exam/{id}', [ManageExam::class, 'Delete']);

    // Exam Question Bank
        Route::match(['get', 'post'], '/question-bank', [ManageQuestionBank::class, 'index'])->name('hrm-hr-management-assessment-manage-question-bank');
        Route::post('/question_bank_status/{id}', [ManageQuestionBank::class, 'Status']);
        Route::get('/question-bank/view/{id}', [ManageQuestionBank::class, 'View'])->name('hrm-hr-management-assessment-manage-question-bank');
        Route::get('/question-bank/edit/{id}', [ManageQuestionBank::class, 'Edit']);
        Route::post('/add_quesion_bank', [ManageQuestionBank::class, 'Add'])->name('add_quesion_bank');
        Route::post('/edit_quesion_bank', [ManageQuestionBank::class, 'Update'])->name('edit_quesion_bank');
        Route::post('/add_quesions', [ManageQuestionBank::class, 'AddQuestion'])->name('add_quesions');
        Route::post('/update_quesions', [ManageQuestionBank::class, 'UpdateQuestion'])->name('update_quesions');
    
        Route::get('/question-bank/section_add/{id}', [ManageQuestionBank::class, 'question_add'])->name('hrm-hr-management-assessment-manage-question-bank');
        Route::get('/question-bank/section_edit/{id}', [ManageQuestionBank::class, 'question_edit'])->name('hrm-hr-management-assessment-manage-question-bank');
        Route::post('/questions_import', [ManageQuestionBank::class, 'ImportQuestion'])->name('questions_import');
    // Manage assesment  
        Route::match(['get', 'post'], '/manage-assessments', [ManageAssessment::class, 'index'])->name('hrm-hr-management-assessment-manage-assessments');
        Route::get('/manage-assessments/write_exam/{id}', [ManageAssessment::class, 'write_exam'])->name('hrm-hr-management-assessment-manage-assessments');
        Route::post('/save-exam-answer', [ManageAssessment::class, 'saveAnswer'])->name('saveExamAnswer');
        Route::post('/save-exam-answer-complete', [ManageAssessment::class, 'saveAnswerComplete'])->name('saveAnswerComplete');
        Route::post('/time-out', [ManageAssessment::class, 'TimeOut'])->name('timeout');
        
        Route::post('/exam-time-save', [ManageAssessment::class, 'ExamTimeSave'])->name('exam-time-save');
        Route::post('/exam/get-question-statuses', [ManageAssessment::class, 'getQuestionStatuses'])->name('examgetQuestionStatuses');
        Route::get('/exam-result/{id}', [ManageAssessment::class, 'view_result'])->name('hrm-hr-management-assessment-manage-assessments');
        
        Route::get('/staff-assessment-certificate/{id}', [ManageAssessment::class, 'assesment_certificate'])->name('staff-assessment-certificate');
        Route::get('/assesment_certificate_preview/{id}', [ManageAssessment::class, 'assesment_certificate_preview'])->name('assesment_certificate_preview');
        Route::get('/staff_certificate_send/{id}', [ManageAssessment::class, 'staff_certificate_send'])->name('staff_certificate_send');
        
    // Manage Result
        Route::match(['get', 'post'], '/manage-result', [ManageResult::class, 'index'])->name('hrm-hr-management-assessment-manage-result');
        Route::match(['get', 'post'], '/staff-exam-report/{id}', [ManageResult::class, 'view_report'])->name('hrm-hr-management-assessment-manage-result');
        Route::post('/exam-limit-increase', [ManageResult::class, 'store_limit'])->name('exam.limit.increase');
        Route::post('/exam-process-delete', [ManageResult::class, 'exam_process_delete'])->name('exam-process-delete');
        
        Route::post('/next-attempt-schedule', [ManageAssessment::class, 'NextSchudule'])->name('next-attempt-schedule');
        Route::post('/exam-badge-claim', [ManageAssessment::class, 'BadgeClaim'])->name('exam-badge-claim');
        
        Route::get('/exam_log/{id}', [ManageAssessment::class, 'exam_log']);
        Route::post('ids/encrypt', [ManageAssessment::class, 'encrypt_ids'])->name('ids/encrypt');


        // Trainning Planner
    Route::match(['get', 'post'],'/hr_training/training_planner', [TrainingPlanner::class, 'index'])->name('hrm-hr-management-training-manage-training-planner');
    Route::post('/training_filter_manage', [TrainingPlanner::class, 'list_filter'])->name('training_filter_manage');
      Route::get('/training_view_manage/{id}', [TrainingPlanner::class, 'View']);

        // Main Training Planner
    Route::match(['get', 'post'],'/hr_training/manage_training', [ManageTraining::class, 'index'])->name('hrm-hr-management-training-manage-training');
    Route::post('/training_filter', [ManageTraining::class, 'list_filter'])->name('training_filter');
    Route::post('/training_status/{id}', [ManageTraining::class, 'Status']);
    Route::delete('/training_delete/{id}', [ManageTraining::class, 'Delete']);
    Route::post('/add_training', [ManageTraining::class, 'Add'])->name('add_training');
    Route::get('/training_edit/{id}', [ManageTraining::class, 'Edit']);
    Route::post('/update_training', [ManageTraining::class, 'Update'])->name('update_training');
    Route::get('/get-all-staff', [ManageTraining::class, 'getAllStaff']);
    Route::get('/get_branch_staff', [ManageTraining::class, 'get_branch_staff'])->name('get_branch_staff');
    Route::get('/jobPosition', [ManageTraining::class, 'jobPosition'])->name('jobPosition');
    Route::get('/branchList', [ManageTraining::class, 'branchList'])->name('branchList');
    Route::get('/get-staff-excluding/{trainerId}', [ManageTraining::class, 'getStaffExcluding']);
    Route::get('/training_view/{id}', [ManageTraining::class, 'View']);
    Route::get('/training_attendance/{id}', [ManageTraining::class, 'Attendance']);
    Route::get('/tableGet', [ManageTraining::class, 'tableGet'])->name('tableGet');
    Route::post('/complete_training', [ManageTraining::class, 'completeTraining'])->name('complete_training');
      Route::get('/get_training_department', [ManageTraining::class, 'getDepartmentList'])->name('get_training_department');
      Route::post('/save-image', [ManageTraining::class, 'store'])->name('image.save');

     

  // exam Setting
  // Exam Guidelines
    Route::get('/settings/assessment/guidelines', [ExamGuideLines::class, 'index'])->name('settings-exam-settings');
    Route::post('/create_guidelines', [ExamGuideLines::class, 'store'])->name('create_guidelines');

    // Exam Category
    Route::get('/settings/assessment/exam_category', [ExamCategory::class, 'index'])->name('settings-exam-settings');
    Route::post('/create_exam_category', [ExamCategory::class, 'Create'])->name('create_exam_category');
    Route::post('/status_exam_category/{id}', [ExamCategory::class, 'Status']);
    Route::delete('/delete_exam_category/{id}', [ExamCategory::class, 'Delete']);
    Route::post('/update_exam_category', [ExamCategory::class, 'Update'])->name('update_exam_category');
    Route::get('/list_exam_category', [ExamCategory::class, 'List']);

    // Exam Section
    Route::get('/settings/assessment/exam_section', [ExamSection::class, 'index'])->name('settings-exam-settings');
    Route::post('/create_exam_section', [ExamSection::class, 'Create'])->name('create_exam_section');
    Route::post('/status_exam_section/{id}', [ExamSection::class, 'Status']);
    Route::delete('/delete_exam_section/{id}', [ExamSection::class, 'Delete']);
    Route::post('/update_exam_section', [ExamSection::class, 'Update'])->name('update_exam_section');

        // Question Bank Type
    Route::get('/settings/assessment/question_bank_type', [QuestionBankType::class, 'index'])->name('settings-exam-settings');
    Route::post('/create_question_bank_type', [QuestionBankType::class, 'Create'])->name('create_question_bank_type');
    Route::post('/status_question_bank_type/{id}', [QuestionBankType::class, 'Status']);
    Route::delete('/delete_question_bank_type/{id}', [QuestionBankType::class, 'Delete']);
    Route::post('/update_question_bank_type', [QuestionBankType::class, 'Update'])->name('update_question_bank_type');

    // Exam Badge 
    Route::get('/settings/assessment/exam_badge', [ExamBadge::class, 'index'])->name('settings-exam-settings');
    Route::post('/create_exam_badge', [ExamBadge::class, 'Create'])->name('create_exam_badge');
    Route::post('/status_exam_badge/{id}', [ExamBadge::class, 'Status']);
    Route::delete('/delete_exam_badge/{id}', [ExamBadge::class, 'Delete']);
    Route::post('/update_exam_badge', [ExamBadge::class, 'Update'])->name('update_exam_badge');
    Route::get('/edit_exam_badge/{id}', [ExamBadge::class, 'Edit']);
    Route::get('/list_exam_badge', [ExamBadge::class, 'list']);


       // Job Role Schedule
    Route::get('/settings/assessment/job_role_schedule', [JobRoleSchedule::class, 'index'])->name('settings-exam-settings');
    Route::post('/create_exam_schedule', [JobRoleSchedule::class, 'Create'])->name('create_exam_schedule');
    Route::post('/status_exam_schedule/{id}', [JobRoleSchedule::class, 'Status']);
    Route::delete('/delete_job_role_schedule/{id}', [JobRoleSchedule::class, 'Delete']);
    Route::get('/edit_job_role_schedule/{id}', [JobRoleSchedule::class, 'Edit']);
    Route::get('/list_job_role_schedule', [JobRoleSchedule::class, 'list']);
    Route::post('/update_job_role_schedule', [JobRoleSchedule::class, 'Update'])->name('update_job_role_schedule');


    
    // OnboardingQuestion Setting Start
  Route::get('/settings/onboarding_question', [OnboardingQuestion::class, 'index'])->name('settings-hrm');
  Route::get('/settings/hrm/onboarding_question_add', [OnboardingQuestion::class, 'AddForm'])->name('settings-hrm');
  Route::get('/settings/hrm/onboarding_question_edit/{id}', [OnboardingQuestion::class, 'edit'])->name('settings-hrm');
  Route::post('/add_onboarding_question', [OnboardingQuestion::class, 'Add'])->name('add_onboarding_question');
  Route::post('/add_new_onboarding_question', [OnboardingQuestion::class, 'AddNew'])->name('add_new_onboarding_question');
  Route::post('/onboarding_question_update', [OnboardingQuestion::class, 'Update'])->name('onboarding_question_update');
  Route::delete('/onboarding_question_delete/{id}', [OnboardingQuestion::class, 'Delete'])->name('onboarding_question_delete');
  Route::post('/onboarding_question_status_change/{id}', [OnboardingQuestion::class, 'Status'])->name('onboarding_question_status_change');
  Route::get('/onboarding_question_list', [OnboardingQuestion::class, 'List'])->name('onboarding_question_list');
  Route::get('/job_role_list_by_onboarding_category', [OnboardingQuestion::class, 'JobRoleListByCategory'])->name('job_role_list_by_onboarding_category');
  Route::get('/job_role_list_by_onboarding_category_edit', [OnboardingQuestion::class, 'JobRoleListByCategoryEdit'])->name('job_role_list_by_onboarding_category_edit');
  Route::get('/onboarding_question_list_by_role', [OnboardingQuestion::class, 'onboardingListByRole'])->name('onboarding_question_list_by_role');
  // OnboardingQuestion Setting End

  // Interview Setting
  // InterviewMode Setting Start
  Route::get('/settings/interview_mode', [InterviewMode::class, 'index'])->name('settings-interview');
  Route::post('/add_interview_mode', [InterviewMode::class, 'Add'])->name('add_interview_mode');
  Route::get('/interview_mode_edit', [InterviewMode::class, 'Edit'])->name('interview_mode_edit');
  Route::post('/interview_mode_update', [InterviewMode::class, 'Update'])->name('interview_mode_update');
  Route::delete('/interview_mode_delete/{id}', [InterviewMode::class, 'Delete'])->name('interview_mode_delete');
  Route::post('/interview_mode_status_change/{id}', [InterviewMode::class, 'Status'])->name('interview_mode_status_change');
  Route::get('/interview_mode_list', [InterviewMode::class, 'List'])->name('interview_mode_list');
  // InterviewMode Setting End

  // InterviewCategory Setting Start
  Route::get('/settings/interview_category', [InterviewCategory::class, 'index'])->name('settings-hrm');
  Route::post('/add_interview_category', [InterviewCategory::class, 'Add'])->name('add_interview_category');
  Route::get('/interview_category_edit', [InterviewCategory::class, 'Edit'])->name('interview_category_edit');
  Route::post('/interview_category_update', [InterviewCategory::class, 'Update'])->name('interview_category_update');
  Route::delete('/interview_category_delete/{id}', [InterviewCategory::class, 'Delete'])->name('interview_category_delete');
  Route::post('/interview_category_status_change/{id}', [InterviewCategory::class, 'Status'])->name('interview_category_status_change');
  Route::get('/interview_category_list', [InterviewCategory::class, 'List'])->name('interview_category_list');
  // InterviewCategory Setting End

    // InterviewQuestion Setting Start
  Route::get('/settings/interview_question', [InterviewQuestion::class, 'index'])->name('settings-interview');
  Route::get('/settings/intreview/intreview_question_add', [InterviewQuestion::class, 'AddForm'])->name('settings-interview');
Route::get('/settings/intreview/intreview_question_edit/{id}', [InterviewQuestion::class, 'edit'])->name('settings-interview');
  Route::post('/add_interview_question', [InterviewQuestion::class, 'Add'])->name('add_interview_question');
  Route::post('/add_new_interview_question', [InterviewQuestion::class, 'AddNew'])->name('add_new_interview_question');
  Route::post('/interview_question_update', [InterviewQuestion::class, 'Update'])->name('interview_question_update');
  Route::delete('/interview_question_delete/{id}', [InterviewQuestion::class, 'Delete'])->name('interview_question_delete');
  Route::post('/interview_question_status_change/{id}', [InterviewQuestion::class, 'Status'])->name('interview_question_status_change');
  Route::get('/interview_question_list', [InterviewQuestion::class, 'List'])->name('interview_question_list');
  Route::get('/job_role_list_by_interview_category', [InterviewQuestion::class, 'JobRoleListByCategory'])->name('job_role_list_by_interview_category');
  Route::get('/job_role_list_by_interview_category_edit', [InterviewQuestion::class, 'JobRoleListByCategoryEdit'])->name('job_role_list_by_interview_category_edit');
  Route::get('/interview_question_list_by_role', [InterviewQuestion::class, 'InterviewListByRole'])->name('interview_question_list_by_role');
  // InterviewQuestion Setting End

  // FeedbackSection Setting Start
  Route::get('/settings/feedback_section', [FeedbackSection::class, 'index'])->name('settings-hrm');
  Route::post('/add_feedback_section', [FeedbackSection::class, 'Add'])->name('add_feedback_section');
  Route::get('/feedback_section_edit', [FeedbackSection::class, 'Edit'])->name('feedback_section_edit');
  Route::post('/feedback_section_update', [FeedbackSection::class, 'Update'])->name('feedback_section_update');
  Route::delete('/feedback_section_delete/{id}', [FeedbackSection::class, 'Delete'])->name('feedback_section_delete');
  Route::post('/feedback_section_status_change/{id}', [FeedbackSection::class, 'Status'])->name('feedback_section_status_change');
  Route::get('/feedback_section_list', [FeedbackSection::class, 'List'])->name('feedback_section_list');
  // FeedbackSection Setting End

    // FeedbackQuestion Setting Start
  Route::get('/settings/feedback_question', [FeedbackQuestion::class, 'index'])->name('settings-interview');
  Route::get('/settings/intreview/feedback_question_add', [FeedbackQuestion::class, 'AddForm'])->name('settings-interview');
  Route::get('/settings/intreview/feedback_question_edit/{id}', [FeedbackQuestion::class, 'edit'])->name('settings-interview');
  Route::post('/add_feedback_question', [FeedbackQuestion::class, 'Add'])->name('add_feedback_question');
  Route::post('/add_new_feedback_question', [FeedbackQuestion::class, 'AddNew'])->name('add_new_feedback_question');
  Route::post('/feedback_question_update', [FeedbackQuestion::class, 'Update'])->name('feedback_question_update');
  Route::delete('/feedback_question_delete/{id}', [FeedbackQuestion::class, 'Delete'])->name('feedback_question_delete');
  Route::post('/feedback_question_status_change/{id}', [FeedbackQuestion::class, 'Status'])->name('feedback_question_status_change');
  Route::get('/feedback_question_list', [FeedbackQuestion::class, 'List'])->name('feedback_question_list');
  Route::get('/feedback_question_list_by_role', [FeedbackQuestion::class, 'InterviewListByRole'])->name('feedback_question_list_by_role');
  // InterviewQuestion Setting End


// NewsBroadcast
  Route::get('/communication_tool/news_broadcast', [NewsBroadcast::class, 'index'])->name('communication-tool-news-broadcast');
  Route::get('/settings/add_news_broadcast', [NewsBroadcast::class, 'Add'])->name('communication-tool-news-broadcast');
  Route::post('/broadcast_template_create', [NewsBroadcast::class, 'saveTemplate'])->name('broadcast_template_create');
  Route::get('/edit_news_broadcast/{id}', [NewsBroadcast::class, 'Edit'])->name('communication-tool-news-broadcast');
  Route::post('/news_broadcast_update', [NewsBroadcast::class, 'Update'])->name('news_broadcast_update');
  Route::delete('/news_broadcast_update_delete/{id}', [NewsBroadcast::class, 'Delete'])->name('news_broadcast_update_delete');
  Route::post('/check_news_broadcast_dates', [NewsBroadcast::class, 'checkDates']);
  Route::post('/check_news_broadcast_datesEdit', [NewsBroadcast::class, 'checkDatesEdit']);
   Route::post('/news_broadcast_status_change/{id}', [NewsBroadcast::class, 'Status'])->name('news_broadcast_status_change');
   Route::get('/broadcast_theme_by_id', [NewsBroadcast::class, 'broadcastThemeById'])->name('broadcast_theme_by_id');
   Route::get('/news_broadcast_by_id', [NewsBroadcast::class, 'newsBroadcastById'])->name('news_broadcast_by_id');
   
    Route::get('/role_list_broadcast/{id}', [NewsBroadcast::class, 'role_list_broadcast']);
    Route::post('/update_broadcast_roles', [NewsBroadcast::class, 'Update_role'])->name('update_broadcast_roles');
    Route::post('/broadcast_roles_remove', [NewsBroadcast::class, 'removeRole'])->name('broadcast_roles_remove');
    
    Route::get('/branch_list_broadcast/{id}', [NewsBroadcast::class, 'branch_list_broadcast']);
    Route::post('/update_broadcast_branch', [NewsBroadcast::class, 'Update_branch'])->name('update_broadcast_branch');
    Route::post('/broadcast_branch_remove', [NewsBroadcast::class, 'removeBranch'])->name('broadcast_branch_remove');
    Route::get('/branch_list', [NewsBroadcast::class, 'branch_list'])->name('branch_list');
   
  //Broadcast Theme Setting
    Route::get('/settings/broadcast_theme', [BroadcastTheme::class, 'index'])->name('settings-broadcast-theme');
  Route::get('/settings/add_broadcast_theme', [BroadcastTheme::class, 'Add'])->name('settings-broadcast-theme');
  Route::post('/broadcast_theme_create', [BroadcastTheme::class, 'saveTemplate'])->name('broadcast_theme_create');
  Route::get('/edit_broadcast_theme/{id}', [BroadcastTheme::class, 'Edit'])->name('settings-broadcast-theme');
  Route::post('/broadcast_theme_update', [BroadcastTheme::class, 'Update'])->name('broadcast_theme_update');
  Route::delete('/broadcast_theme_update_delete/{id}', [BroadcastTheme::class, 'Delete'])->name('broadcast_theme_update_delete');
  Route::post('/broadcast_theme_status_change/{id}', [BroadcastTheme::class, 'Status'])->name('broadcast_theme_status_change');
   
    
});


Route::get('/record/{token}', [VideoSubmissionController::class, 'show'])->name('record.show');
Route::post('/record/upload', [VideoSubmissionController::class, 'upload'])->name('record.upload');

Route::get('/admin/webhooks', [WebhookAdminController::class, 'index'])->name('admin.webhooks');
Route::post('/admin/webhooks/retry/{id}', [WebhookAdminController::class, 'retry'])->name('admin.webhooks.retry');
Route::get('/webhooks_log', [WebhookAdminController::class, 'webhooksLog'])->name('webhooks_log');

Route::get('/test-broadcast', function () {
    $dispatch = \App\Models\WebhookDispatchModel::latest()->first();
    event(new \App\Events\WebhookDispatchedEvent($dispatch));
    return 'Event fired';
});

Route::get('/test-broadcast-job', function () {
    $dispatch = \App\Models\JobRequestModel::latest()->first();
    event(new \App\Events\JobRequestEvent($dispatch));
    return 'Event fired';
});

Route::get('/quote', function() {
    $response = Http::get('https://zenquotes.io/api/random');
    return $response->json();
});

Route::get('/role_permission_error', [ErrorController::class, 'index'])->name('role_permission_error');
Route::post('/send_test_template', [ManageStaff::class, 'TestMessageSend'])->name('send_test_template');

// JOb Application
Route::get('/interview_login/{id}', [JobController::class, 'startInterview'])->name('interview-login');
Route::get('/apply_job/{id}', [JobController::class, 'jobWelcomeScreen'])->name('apply_job');
Route::get('/job_application/{id}', [JobController::class, 'jobApplication'])->name('job_application');
Route::get('/interview_old/{id}', [JobController::class, 'Interview'])->name('interview');
Route::get('/gen_job_qr/{id}/generate-qr', [JobController::class, 'generateQrJob']);
Route::post('/ai/generate-interview-questions', [JobController::class, 'generateAiQuestion']);


Route::get('/network-status', [NetworkHandle::class, 'networkCheck'])->name('network-status');

// edit page
Route::get('/hr_recruitment/interview_schedule_edit/{encoded}', [JobRequest::class, 'editInterviewSchedule']);
Route::get('/interview/{encoded}', [JobController::class, 'runInterview'])->name('interview.run');

Route::get('/applicant_feedback_list/{id}', [JobController::class, 'FeedbackListByCategory'])->name('applicant_feedback_list');
Route::post('/submit_applicant_feedback/{id}', [JobController::class, 'SubmitApplicantFeedback']);

// update action
Route::post('/submit_job_application', [JobController::class, 'submitJobApplication'])->name('submit_job_application');
Route::get('/application_submitted/{id}', [JobController::class, 'applicationSubmitted'])->name('application_submitted');
Route::get('/interview_not_eligible/{id}', [JobController::class, 'showNotEligible'])->name('interview_not_eligible');
Route::get('/interview_feedback/{id}', [JobController::class, 'interviewFeedback']);

Route::post('/interview-schedule/update', [InterviewController::class, 'updateInterviewSchedule']);
Route::post('/interview/send-otp', [JobController::class, 'sendOtp'])
    ->name('interview.sendOtp');
Route::post('/interview/verify-otp', [JobController::class, 'verifyOtp'])
    ->name('interview.verifyOtp');

Route::post('/interview/tab-switch', [JobController::class, 'logTabSwitch']);
Route::post('/interview/answer/save', [JobController::class, 'saveAnswer']);
Route::get('/interview/question/next', [JobController::class, 'fetchNextQuestion']);
Route::get('/interview/check-resume', [InterviewController::class, 'checkResume']);
Route::get('/interview/thank-you/{id}', [JobController::class, 'ThankYouInterview'])->name('interview.thankyou'); 

Route::get('/upload_gdrive', [JobController::class, 'showUploadForm']);
Route::post('/upload_gdrive', [JobController::class, 'upload']);
Route::get('/gdrive_files', [JobController::class, 'listFiles']);
Route::get('/google/callback', [JobController::class, 'handleCallback']);
Route::get('/resumes/preview/{fileId}', [JobRequest::class, 'preview']);


Route::get('/zoom_ui', function () {
    return view('welcome')->with('respond', 'MEETING API RESPOND WILL COME IN THIS SECTION');
});
Route::get('start', [ZoomController::class, 'index']);
Route::any('interview-zoom-meeting-create', [ZoomController::class, 'index']);


use App\Http\Controllers\RazorpayPolicy;
// razorpay policies
Route::get('/contact-us',[RazorpayPolicy::class,'ContactUs'])->name('contact.us');
Route::get('/shipping-policy',[RazorpayPolicy::class,'ShippingPolicy'])->name('shipping.policy');
Route::get('/shipping-policy',[RazorpayPolicy::class,'ShippingPolicy'])->name('shipping.policy');
Route::get('/terms-and-conditions',[RazorpayPolicy::class,'TermsAndConditionPolicy'])->name('terms.conditions');
Route::get('/cancellations-and-refunds',[RazorpayPolicy::class,'CancelAndRefundPolicy'])->name('refunds.policy');
Route::get('/privacy-policy',[RazorpayPolicy::class,'PrivacyPolicy'])->name('privacy.policy');

// Route::get('/sentry-test', function () {
//     throw new Exception('Sentry local test error');
// });
use App\Http\Controllers\whatsapp_management\WhatsappConfig;
Route::match(['get', 'post'], '/webhook_whatsapp', [WhatsappConfig::class, 'WhatsappWebhook']);

Route::get('/admin/ai-usage', [AiUsageController::class, 'index']);
Route::get('/admin/ai-chat', [AiUsageController::class, 'aichat']);

Route::post('/admin/ai-models/{model}', function (Request $request, AiModel $model) {
    $model->update($request->only([
        'input_price_per_million',
        'output_price_per_million'
    ]));
});
Route::post('/ai/chat', [AiUsageController::class, 'chat'])
    ->middleware('ai.limit');
Route::get('/aichat/history', [AiUsageController::class, 'ChatHistory']);
Route::post('/aichat-clear-all/{id}', [AiUsageController::class, 'clearChatHistory'])->name('aichat-clear-all');