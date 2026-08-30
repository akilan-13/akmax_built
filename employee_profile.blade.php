@extends('layouts/layoutMaster')

@section('title', 'Complete Your Profile')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
        'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/flatpickr/flatpickr.js',
        'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js'
    ])
@endsection

@section('content')
@php
    $languageIds = !empty($staff->languages) ? (json_decode($staff->languages, true) ?: []) : [];
    $hobbyIds = !empty($staff->hobby) ? (json_decode($staff->hobby, true) ?: []) : [];
    $socialDetails = !empty($staff->social_media_details) ? (json_decode($staff->social_media_details, true) ?: []) : [];
    $children = $family && !empty($family->children_details) ? (json_decode($family->children_details, true) ?: []) : [];
    $siblings = $family && !empty($family->siblings_detail) ? (json_decode($family->siblings_detail, true) ?: []) : [];
    $contactNames = !empty($staff->contact_person_name) ? (json_decode($staff->contact_person_name, true) ?: []) : [];
    $contactRelations = !empty($staff->contact_person_relation) ? (json_decode($staff->contact_person_relation, true) ?: []) : [];
    $contactNumbers = !empty($staff->contact_person_no) ? (json_decode($staff->contact_person_no, true) ?: []) : [];
    $completionPercent = data_get($completion, 'overall.percentage', $staff->completion_percentage ?? 0);
    $completionRemaining = data_get($completion, 'overall.remaining', 0);
    $isComplete = data_get($completion, 'overall.is_complete', false);
@endphp

<style>
    .employee-shell{max-width:1180px;margin:0 auto;padding-bottom:40px}
    .profile-hero{border:1px solid rgba(67,89,113,.12);border-radius:20px;background:linear-gradient(135deg,#fff 0%,#f7f9fc 100%);box-shadow:0 8px 30px rgba(67,89,113,.08)}
    .completion-ring{width:96px;height:96px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--bs-primary) calc(var(--completion)*1%),#e9edf2 0);position:relative;flex:0 0 auto}
    .completion-ring:after{content:'';position:absolute;width:76px;height:76px;border-radius:50%;background:#fff}
    .completion-ring strong{position:relative;z-index:2;font-size:20px}
    .profile-step{border:1px solid rgba(67,89,113,.12);border-radius:16px;background:#fff;transition:.2s ease;cursor:pointer}
    .profile-step:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(67,89,113,.08)}
    .profile-step.active{border-color:rgba(105,108,255,.45);box-shadow:0 8px 24px rgba(105,108,255,.10)}
    .profile-step .step-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#f1f2ff;color:var(--bs-primary);font-size:21px}
    .profile-step.done .step-icon{background:#e9f8ef;color:#198754}
    .profile-step.locked{background:#f8f9fa;cursor:default}
    .field-card{border:1px solid #e8ebef;border-radius:14px;padding:16px;background:#fff;height:100%}
    .field-card.required{border-left:3px solid var(--bs-primary)}
    .section-card{border:0;border-radius:18px;box-shadow:0 5px 24px rgba(67,89,113,.07);overflow:hidden}
    .section-head{background:#fff;border-bottom:1px solid #edf0f3;padding:20px 22px}
    .section-body{padding:22px}
    .sticky-save{position:sticky;bottom:14px;z-index:20;background:rgba(255,255,255,.94);backdrop-filter:blur(12px);border:1px solid #e6e9ed;border-radius:16px;padding:12px;box-shadow:0 8px 28px rgba(0,0,0,.08)}
    .locked-panel{border:1px dashed #cbd2da;background:#f8f9fa;border-radius:16px;padding:18px}
    .existing-doc{border:1px solid #e7eaee;border-radius:14px;padding:14px;background:#fff}
    .existing-doc.deleted{opacity:.55;border-color:#dc3545;background:#fff5f5}
    .missing-item{cursor:pointer}
    .progress{background:#e9edf2}
    .mobile-stepbar{overflow-x:auto;white-space:nowrap;padding-bottom:4px}
    @media(max-width:767.98px){.employee-shell{padding:0 4px 30px}.profile-hero{border-radius:16px}.completion-ring{width:78px;height:78px}.completion-ring:after{width:62px;height:62px}.completion-ring strong{font-size:16px}.section-body{padding:16px}.section-head{padding:16px}.sticky-save{bottom:8px}.desktop-only{display:none!important}}
</style>

<div class="employee-shell">
    <div class="profile-hero p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3">
            <div class="completion-ring" style="--completion: {{ max(0,min(100,(float)$completionPercent)) }}">
                <strong>{{ number_format((float)$completionPercent,0) }}%</strong>
            </div>
            <div class="flex-grow-1 text-center text-md-start">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Employee Profile</div>
                <h3 class="mb-1">Complete your profile, {{ $staff->staff_name ?? 'there' }}</h3>
                <p class="text-muted mb-3">Please review your information and complete the highlighted items. Company, work type and salary information are managed by HR.</p>
                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                    @if($isComplete)
                        <span class="badge bg-success-subtle text-success px-3 py-2"><i class="mdi mdi-check-circle me-1"></i>Profile complete</span>
                    @else
                        <span class="badge bg-label-primary px-3 py-2"><i class="mdi mdi-alert-circle-outline me-1"></i>{{ $completionRemaining }} item(s) remaining</span>
                    @endif
                    <span class="badge bg-light text-muted px-3 py-2"><i class="mdi mdi-shield-lock-outline me-1"></i>HR-controlled fields are protected</span>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="d-flex justify-content-between small mb-1"><span class="fw-semibold">Profile completion</span><span id="completionLabel">{{ number_format((float)$completionPercent,0) }}%</span></div>
            <div class="progress" style="height:9px"><div id="completionBar" class="progress-bar" style="width:{{ $completionPercent }}%"></div></div>
        </div>
    </div>

    <div id="saveAlert" class="alert d-none" role="alert"></div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="d-flex flex-lg-column gap-2 mobile-stepbar">
                @foreach([
                    1 => ['Personal Details','mdi-account-outline','staff_add'],
                    2 => ['Family Details','mdi-account-group-outline','family_add'],
                    3 => ['Contact Details','mdi-phone-outline','contact_add'],
                    4 => ['Social Media','mdi-share-variant-outline','socialmedia'],
                    5 => ['Education & Documents','mdi-school-outline','education']
                ] as $number => $step)
                    @php $stepPct = data_get($completion, 'steps.'.(['personal','family','contact','social_media','education'][$number-1]).'.percentage', 0); @endphp
                    <div class="profile-step p-3 {{ $number===1 ? 'active' : '' }} flex-shrink-0" data-go-step="{{ $number }}" id="stepCard{{ $number }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="step-icon"><i class="mdi {{ $step[1] }}"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $step[0] }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1"><div class="progress flex-grow-1" style="height:5px"><div class="progress-bar step-progress-bar" data-step-progress="{{ $number }}" style="width:{{ $stepPct }}%"></div></div><small class="text-muted" data-step-label="{{ $number }}">{{ number_format($stepPct,0) }}%</small></div>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                @endforeach
                <div class="locked-panel mt-lg-2 flex-shrink-0">
                    <div class="d-flex align-items-start gap-3"><div class="step-icon bg-light text-secondary"><i class="mdi mdi-lock-outline"></i></div><div><div class="fw-semibold">HR-managed information</div><small class="text-muted">Work Type, Company Details, Salary Details and HR records cannot be edited from this link.</small></div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <form id="employeeProfileForm" method="POST" action="{{ route('employee.profile.update', ['token'=>$token]) }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="stage" id="currentStage" value="1">
                <input type="hidden" name="edit_id" value="{{ $staff->sno }}">

                {{-- STEP 1 --}}
                <section class="profile-section" data-section="1">
                    <div class="card section-card mb-3">
                        <div class="section-head"><div class="d-flex align-items-center gap-3"><div class="step-icon"><i class="mdi mdi-account-outline"></i></div><div><h4 class="mb-1">Personal Details</h4><p class="text-muted mb-0">Keep your basic information accurate and up to date.</p></div></div></div>
                        <div class="section-body">
                            <div class="row g-3">
                                <div class="col-12"><div class="field-card"><div class="d-flex align-items-center gap-3 flex-wrap">
                                    @php
                                        if(($staff->company_type ?? 1) == 1){ $imgPath=public_path('staff_images/Management/'.($staff->staff_image ?? '')); $imgUrl=asset('staff_images/Management/'.($staff->staff_image ?? '')); }
                                        else { $imgPath=public_path('staff_images/Buisness/'.$staff->company_id.'/'.$staff->entity_id.'/'.($staff->staff_image ?? '')); $imgUrl=asset('staff_images/Buisness/'.$staff->company_id.'/'.$staff->entity_id.'/'.($staff->staff_image ?? '')); }
                                    @endphp
                                    <img id="profilePreview" src="{{ (!empty($staff->staff_image) && file_exists($imgPath)) ? $imgUrl : asset('assets/egc_images/auth/user_3.png') }}" class="rounded-circle" style="width:76px;height:76px;object-fit:cover" alt="Profile photo">
                                    <div class="flex-grow-1"><div class="fw-semibold">Profile Photo <span class="text-muted">(Optional)</span></div><small class="text-muted d-block">JPG or PNG, maximum 800 KB.</small><label class="btn btn-sm btn-primary mt-2"><i class="mdi mdi-camera-plus-outline me-1"></i>Choose photo<input type="file" name="staff_add_icon" id="profilePhoto" hidden accept="image/png,image/jpeg"></label><input type="hidden" name="old_staff_image" value="{{ $staff->staff_image ?? '' }}"></div>
                                </div></div></div>
                                @php $personalFields=[
                                    ['staff_name','Staff Name',$staff->staff_name ?? '',true,'text'],['mobile_no','Mobile Number',$staff->mobile_no ?? '',true,'tel'],['email_id','Email ID',$staff->email_id ?? '',true,'email'],['birth_place','Birth Place',$staff->birth_place ?? '',false,'text'],['height','Height (cm)',$staff->height ?? '',false,'text'],['weight','Weight (kg)',$staff->weight ?? '',false,'text']
                                ]; @endphp
                                @foreach($personalFields as $f)<div class="col-md-6"><div class="field-card {{ $f[3]?'required':'' }}"><label class="form-label fw-semibold">{{ $f[1] }} @if($f[3])<span class="text-danger">*</span>@else <span class="text-muted fw-normal">Optional</span>@endif</label><input class="form-control" type="{{ $f[4] }}" name="{{ $f[0] }}" value="{{ $f[2] }}" {{ $f[0]=='mobile_no'?'maxlength=10':'' }}></div></div>@endforeach
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label><div class="d-flex gap-3 flex-wrap pt-2">@foreach([1=>'Male',2=>'Female',3=>'Others'] as $v=>$label)<label class="form-check"><input class="form-check-input" type="radio" name="gender" value="{{ $v }}" {{ (string)$staff->gender===(string)$v?'checked':'' }}> <span class="form-check-label">{{ $label }}</span></label>@endforeach</div></div></div>
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label><input class="form-control common_datepicker" type="text" name="dob" value="{{ $staff->dob ?? '' }}" autocomplete="off"></div></div>
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Mother Tongue <span class="text-danger">*</span></label><select class="form-select select3" name="mother_tongue"><option value="">Select</option>@foreach(($languageList ?? []) as $language)<option value="{{ $language->sno }}" {{ (string)$language->sno===(string)$staff->mother_tongue?'selected':'' }}>{{ $language->name }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Languages Known <span class="text-danger">*</span></label><select class="form-select select3" name="Languages[]" multiple>@foreach(($languageList ?? []) as $language)<option value="{{ $language->sno }}" {{ in_array($language->sno,$languageIds) ? 'selected':'' }}>{{ $language->name }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Hobbies <span class="text-muted fw-normal">Optional</span></label><select class="form-select select3" name="hobby[]" multiple>@foreach(($hobbyList ?? []) as $hobby)<option value="{{ $hobby->sno }}" {{ in_array($hobby->sno,$hobbyIds) ? 'selected':'' }}>{{ $hobby->hobby_name }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Blood Group <span class="text-danger">*</span></label><select class="form-select select3" name="blood_group"><option value="">Select</option>@foreach(($bloodGroupList ?? []) as $blood)<option value="{{ $blood->sno }}" {{ (string)$blood->sno===(string)$staff->blood_group?'selected':'' }}>{{ $blood->blood_group }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Nationality <span class="text-danger">*</span></label><select class="form-select select3" name="nationality"><option value="">Select</option>@foreach(($nationalityList ?? []) as $item)<option value="{{ $item->sno }}" {{ (string)$item->sno===(string)$staff->nationality_id?'selected':'' }}>{{ $item->nationality_name }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Religion <span class="text-danger">*</span></label><select class="form-select select3" name="religion"><option value="">Select</option>@foreach(($religionList ?? []) as $item)<option value="{{ $item->sno }}" {{ (string)$item->sno===(string)$staff->religion_id?'selected':'' }}>{{ $item->religion_name }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Community <span class="text-muted fw-normal">Optional</span></label><input class="form-control" name="community_id" value="{{ $staff->community_id ?? '' }}"></div></div>
                                <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Caste <span class="text-muted fw-normal">Optional</span></label><input class="form-control" name="caste_id" value="{{ $staff->caste_id ?? '' }}"></div></div>
                                <div class="col-12"><div class="field-card"><label class="form-label fw-semibold">Identification Mark <span class="text-muted fw-normal">Optional</span></label><textarea class="form-control" name="identification_mark" rows="2">{{ $staff->identification_mark ?? '' }}</textarea></div></div>
                                <div class="col-12"><div class="field-card"><div class="d-flex justify-content-between align-items-center"><div><div class="fw-semibold">Vehicle Information</div><small class="text-muted">Only complete these fields if you have selected Yes.</small></div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="vehicle_check" value="1" id="vehicleCheck" {{ (int)($staff->vehicle_check ?? 0)===1?'checked':'' }}></div></div><div id="vehicleFields" class="row g-3 mt-1 {{ (int)($staff->vehicle_check ?? 0)===1?'':'d-none' }}"><div class="col-md-4"><input class="form-control" name="driving_license_no" placeholder="Driving License No" value="{{ $staff->driving_license_no ?? '' }}"></div><div class="col-md-4"><input class="form-control" name="vehicle_register_no" placeholder="Vehicle Registration No" value="{{ $staff->vehicle_register_no ?? '' }}"></div><div class="col-md-4"><input class="form-control common_datepicker" name="license_expiry" placeholder="License Expiry" value="{{ $staff->license_expiry ?? '' }}"></div></div></div></div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- STEP 2 --}}
                <section class="profile-section d-none" data-section="2">
                    <div class="card section-card mb-3"><div class="section-head"><h4 class="mb-1">Family Details</h4><p class="text-muted mb-0">Add family information that is relevant to your current situation.</p></div><div class="section-body"><div class="row g-3">
                        @foreach([['father_name','Father Name',$family->father_name ?? '',true],['father_occup','Father Occupation',$family->father_occup ?? '',true],['mother_name','Mother Name',$family->mother_name ?? '',true],['mother_occup','Mother Occupation',$family->mother_occup ?? '',true]] as $f)<div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">{{ $f[1] }} <span class="text-danger">*</span></label><input class="form-control" name="{{ $f[0] }}" value="{{ $f[2] }}"></div></div>@endforeach
                        <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Marital Status <span class="text-danger">*</span></label><select class="form-select" name="marital_status" id="maritalStatus"><option value="">Select</option><option value="1" {{ (string)($staff->martial_status ?? '')==='1'?'selected':'' }}>Married</option><option value="2" {{ (string)($staff->martial_status ?? '')==='2'?'selected':'' }}>Unmarried</option></select></div></div>
                        <div id="spouseFields" class="row g-3 {{ (string)($staff->martial_status ?? '')==='1'?'':'d-none' }}"><div class="col-md-4"><div class="field-card"><label class="form-label fw-semibold">Anniversary Date</label><input class="form-control common_datepicker" name="anniversary_date" value="{{ $family->anniversary_date ?? '' }}"></div></div><div class="col-md-4"><div class="field-card"><label class="form-label fw-semibold">Spouse Name</label><input class="form-control" name="spouse_name" value="{{ $family->spouse_name ?? '' }}"></div></div><div class="col-md-4"><div class="field-card"><label class="form-label fw-semibold">Spouse Mobile</label><input class="form-control" name="spouse_mobile" maxlength="10" value="{{ $family->spouse_mobile ?? '' }}"></div></div><div class="col-md-4"><div class="field-card"><label class="form-label fw-semibold">Spouse DOB</label><input class="form-control common_datepicker" name="spouse_dob" value="{{ $family->spouse_dob ?? '' }}"></div></div><div class="col-md-4"><div class="field-card"><label class="form-label fw-semibold">Spouse Working?</label><div class="pt-2">@foreach(['Yes','No'] as $v)<label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_working" value="{{ $v }}" {{ (($family->spouse_working ?? 'No')===$v)?'checked':'' }}><span class="form-check-label">{{ $v }}</span></label>@endforeach</div></div></div><div class="col-md-4 spouse-working-fields {{ ($family->spouse_working ?? '')==='Yes'?'':'d-none' }}"><div class="field-card"><label class="form-label fw-semibold">Spouse Designation</label><input class="form-control" name="spouse_designation" value="{{ $family->spouse_designation ?? '' }}"></div></div><div class="col-md-4 spouse-working-fields {{ ($family->spouse_working ?? '')==='Yes'?'':'d-none' }}"><div class="field-card"><label class="form-label fw-semibold">Spouse Company</label><input class="form-control" name="spouse_company_name" value="{{ $family->spouse_company_name ?? '' }}"></div></div><div class="col-md-4 spouse-working-fields {{ ($family->spouse_working ?? '')==='Yes'?'':'d-none' }}"><div class="field-card"><label class="form-label fw-semibold">Spouse Salary (LPA)</label><input class="form-control" name="spouse_salary" value="{{ $family->spouse_salary ?? '' }}"></div></div></div>
                        <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Children?</label><div class="pt-2">@foreach(['Yes','No'] as $v)<label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="has_children" value="{{ $v }}" {{ (($family->has_children ?? 'No')===$v)?'checked':'' }}><span class="form-check-label">{{ $v }}</span></label>@endforeach</div></div></div>
                        <div class="col-md-6 children-toggle {{ ($family->has_children ?? 'No')==='Yes'?'':'d-none' }}"><div class="field-card"><label class="form-label fw-semibold">Children Count <span class="text-danger">*</span></label><input class="form-control" id="childrenCount" name="childrenCount" maxlength="1" value="{{ $family->children_count ?? count($children) }}"></div></div>
                        <div class="col-12 children-toggle {{ ($family->has_children ?? 'No')==='Yes'?'':'d-none' }}"><div id="childrenDetails" class="row g-3"></div></div>
                        <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Siblings?</label><div class="pt-2">@foreach(['Yes','No'] as $v)<label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="has_Siblings" value="{{ $v }}" {{ (($family->has_siblings ?? 'No')===$v)?'checked':'' }}><span class="form-check-label">{{ $v }}</span></label>@endforeach</div></div></div>
                        <div class="col-md-6 sibling-toggle {{ ($family->has_siblings ?? 'No')==='Yes'?'':'d-none' }}"><div class="field-card"><label class="form-label fw-semibold">Siblings Count <span class="text-danger">*</span></label><input class="form-control" id="siblingsCount" name="siblingsCount" maxlength="1" value="{{ $family->sibling_count ?? count($siblings) }}"></div></div>
                        <div class="col-12 sibling-toggle {{ ($family->has_siblings ?? 'No')==='Yes'?'':'d-none' }}"><div id="siblingDetails" class="row g-3"></div></div>
                    </div></div></div>
                </section>

                {{-- STEP 3 --}}
                <section class="profile-section d-none" data-section="3">
                    <div class="card section-card mb-3"><div class="section-head"><h4 class="mb-1">Contact Details</h4><p class="text-muted mb-0">Keep your address and emergency contacts current.</p></div><div class="section-body"><div class="row g-3">
                        <div class="col-md-6"><div class="field-card required"><label class="form-label fw-semibold">Permanent Address <span class="text-danger">*</span></label><textarea class="form-control" rows="3" name="permanent_address">{{ $staff->address ?? '' }}</textarea></div></div>
                        <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Residential Address <span class="text-muted fw-normal">Optional</span></label><textarea class="form-control" rows="3" name="residential_address">{{ $staff->residential_address ?? '' }}</textarea></div></div>
                        <div class="col-md-6"><div class="field-card"><label class="form-label fw-semibold">Location URL <span class="text-muted fw-normal">Optional</span></label><input class="form-control" name="staff_location_url" value="{{ $staff->location_url ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="field-card"><label class="form-label fw-semibold">Latitude</label><input class="form-control" name="staff_latitude" value="{{ $staff->latitude ?? '' }}"></div></div><div class="col-md-3"><div class="field-card"><label class="form-label fw-semibold">Longitude</label><input class="form-control" name="staff_longitude" value="{{ $staff->longitude ?? '' }}"></div></div>
                        <div class="col-12"><div class="d-flex justify-content-between align-items-center mb-2"><div><h5 class="mb-1">Emergency Contacts</h5><small class="text-muted">At least one complete contact is required.</small></div><button type="button" class="btn btn-sm btn-primary" id="addContact"><i class="mdi mdi-plus me-1"></i>Add contact</button></div><div id="contactsWrapper"></div></div>
                    </div></div></div>
                </section>

                {{-- STEP 4 --}}
                <section class="profile-section d-none" data-section="4">
                    <div class="card section-card mb-3"><div class="section-head"><h4 class="mb-1">Social Media</h4><p class="text-muted mb-0">Social profiles are optional. If you do not use a platform, simply leave it unselected.</p></div><div class="section-body"><div class="row g-3">
                        @foreach(($social_media_list ?? []) as $social)
                            @php $sid=(string)$social->sno; $saved=$socialDetails[$sid] ?? ($socialDetails[$social->sno] ?? ''); @endphp
                            <div class="col-md-6"><div class="field-card"><div class="form-check mb-2"><input class="form-check-input social-toggle" type="checkbox" id="social_{{ $sid }}" data-target="social_field_{{ $sid }}" name="social_media_selected[]" value="{{ $sid }}" {{ !empty($saved)?'checked':'' }}><label class="form-check-label fw-semibold" for="social_{{ $sid }}">{{ $social->social_media_name }}</label></div><div id="social_field_{{ $sid }}" class="{{ !empty($saved)?'':'d-none' }}"><input class="form-control social-url" name="social_media[{{ $social->sno }}]" value="{{ $saved }}" placeholder="https://..."></div></div></div>
                        @endforeach
                        @if(empty($social_media_list))<div class="col-12"><div class="alert alert-light mb-0"><i class="mdi mdi-information-outline me-1"></i>No social media platforms are configured. This section is complete.</div></div>@endif
                    </div></div></div>
                </section>

                {{-- STEP 5 --}}
                <section class="profile-section d-none" data-section="5">
                    <div class="card section-card mb-3"><div class="section-head"><h4 class="mb-1">Education & Documents</h4><p class="text-muted mb-0">Add your education records and required documents. HR-managed information is not shown here.</p></div><div class="section-body"><div class="row g-3">
                        <div class="col-12"><div class="d-flex justify-content-between align-items-center mb-2"><div><h5 class="mb-1">Education</h5><small class="text-muted">Add all relevant qualifications.</small></div><button type="button" class="btn btn-sm btn-primary" id="addEducation"><i class="mdi mdi-plus me-1"></i>Add education</button></div><div id="educationWrapper"></div></div>
                        <div class="col-12"><hr><div class="field-card"><label class="form-label fw-semibold">Any additional course completed? <span class="text-danger">*</span></label><div class="mb-2">@foreach(['Yes','No'] as $v)<label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="is_Course" value="{{ $v }}" {{ (($staff->is_Course ?? 'No')===$v)?'checked':'' }}><span class="form-check-label">{{ $v }}</span></label>@endforeach</div><div id="courseField" class="{{ ($staff->is_Course ?? 'No')==='Yes'?'':'d-none' }}"><input class="form-control" name="course_tag" value="{{ is_array($staff->course_tag ?? null) ? implode(', ', $staff->course_tag) : ($staff->course_tag ?? '') }}" placeholder="Enter course name"></div></div></div>
                        <div class="col-12"><hr><div class="d-flex justify-content-between align-items-center mb-2"><div><h5 class="mb-1">Existing Documents</h5><small class="text-muted">Existing files are kept unless you explicitly choose Delete.</small></div></div><div class="row g-3">@forelse(($attachments ?? []) as $attachment)<div class="col-md-6"><div class="existing-doc" data-attachment-id="{{ $attachment->sno }}"><input type="hidden" name="existing_attachment_action[{{ $attachment->sno }}]" value="keep"><div class="d-flex align-items-start justify-content-between gap-2"><div><div class="fw-semibold"><i class="mdi mdi-file-document-outline me-1"></i>{{ $attachment->document_name ?? $attachment->document_id }}</div>@php $files=json_decode($attachment->attachment_name ?? '[]',true) ?: []; @endphp@foreach($files as $file)<div class="small text-muted mt-1"><i class="mdi mdi-paperclip me-1"></i>{{ basename($file) }}</div>@endforeach</div><button type="button" class="btn btn-sm btn-outline-danger attachment-delete"><i class="mdi mdi-delete-outline"></i></button></div></div></div>@empty<div class="col-12"><div class="alert alert-warning mb-0"><i class="mdi mdi-alert-outline me-1"></i>No existing documents were found. If a required document is missing, upload it below.</div></div>@endforelse</div></div></div>
                        <div class="col-12"><hr><h5 class="mb-1">Upload / Replace Documents</h5><small class="text-muted d-block mb-3">Choose a document type and upload one or more files. Existing files are not deleted unless you explicitly select Delete.</small><div id="documentWrapper"></div><button type="button" class="btn btn-outline-primary btn-sm" id="addDocument"><i class="mdi mdi-plus me-1"></i>Add document</button></div>
                    </div></div></div>
                </section>

                <div class="sticky-save mt-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="previousBtn"><i class="mdi mdi-arrow-left me-1"></i>Previous</button>
                        <div class="d-flex gap-2 justify-content-end"><button type="button" class="btn btn-outline-primary" id="saveBtn"><i class="mdi mdi-content-save-outline me-1"></i><span id="saveText">Save</span><span id="saveLoader" class="spinner-border spinner-border-sm d-none ms-1"></span></button><button type="button" class="btn btn-primary" id="nextBtn">Save & Continue <i class="mdi mdi-arrow-right ms-1"></i></button></div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="locked-panel mt-2"><div class="d-flex align-items-start gap-3"><i class="mdi mdi-lock-outline fs-3 text-secondary"></i><div><div class="fw-semibold">Need to change company, work type or salary information?</div><div class="text-muted small">These fields are intentionally unavailable on the employee profile page. Please contact your HR executive for corrections.</div></div></div></div>
</div>

<script>
(() => {
    const form = document.getElementById('employeeProfileForm');
    const sections = [...document.querySelectorAll('.profile-section')];
    const stepCards = [...document.querySelectorAll('.profile-step')];
    let current = 1;

    const oldChildren = @json($children);
    const oldSiblings = @json($siblings);
    const oldContacts = {names:@json($contactNames), relations:@json($contactRelations), numbers:@json($contactNumbers)};
    const oldEducation = @json(($education ?? collect())->map(fn($e)=>['qualification_type'=>$e->qualification_type,'major'=>$e->major,'university_name'=>$e->university_name,'year'=>$e->year])->values());

    function esc(v){return String(v ?? '').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));}
    function initPlugins(scope=document){
        if(window.jQuery && $.fn.select2){$(scope).find('.select3').each(function(){if($(this).hasClass('select2-hidden-accessible')) return; $(this).select2({width:'100%',dropdownParent:$(this).closest('.section-body')});});}
        if(window.jQuery && $.fn.datepicker){$(scope).find('.common_datepicker').each(function(){if($(this).data('datepicker')) return; $(this).datepicker({format:'yyyy-mm-dd',autoclose:true,todayHighlight:true});});}
    }
    function renderChildren(){
        const yes=document.querySelector('input[name="has_children"]:checked')?.value==='Yes';
        document.querySelectorAll('.children-toggle').forEach(x=>x.classList.toggle('d-none',!yes));
        const box=document.getElementById('childrenDetails'); if(!box) return; if(!yes){box.innerHTML='';return;}
        const count=Math.min(9,Math.max(0,parseInt(document.getElementById('childrenCount')?.value||0,10)));
        let html=''; for(let i=0;i<count;i++){const d=oldChildren[i]||{}; html+=`<div class="col-md-6"><div class="field-card"><div class="fw-semibold mb-3">Child ${i+1}</div><div class="row g-2"><div class="col-12"><input class="form-control" name="child_name[]" placeholder="Name" value="${esc(d.child_name)}"></div><div class="col-4"><input class="form-control common_datepicker" name="child_dob[]" placeholder="DOB" value="${esc(d.child_dob)}"></div><div class="col-4"><input class="form-control" name="child_std[]" placeholder="Standard / Degree" value="${esc(d.child_std)}"></div><div class="col-4"><input class="form-control" name="child_year[]" placeholder="Year" value="${esc(d.child_year)}"></div></div></div></div>`;} box.innerHTML=html; initPlugins(box); }
    function renderSiblings(){
        const yes=document.querySelector('input[name="has_Siblings"]:checked')?.value==='Yes'; document.querySelectorAll('.sibling-toggle').forEach(x=>x.classList.toggle('d-none',!yes)); const box=document.getElementById('siblingDetails'); if(!box)return; if(!yes){box.innerHTML='';return;} const count=Math.min(9,Math.max(0,parseInt(document.getElementById('siblingsCount')?.value||0,10))); let html=''; for(let i=0;i<count;i++){const d=oldSiblings[i]||{}; html+=`<div class="col-md-6"><div class="field-card"><div class="fw-semibold mb-3">Sibling ${i+1}</div><div class="row g-2"><div class="col-12"><input class="form-control" name="sibling_name[]" placeholder="Name" value="${esc(d.sibling_name)}"></div><div class="col-6"><select class="form-select select3" name="sibling_type[]"><option value="">Elder / Younger</option><option value="Elder" ${d.sibling_type==='Elder'?'selected':''}>Elder</option><option value="Younger" ${d.sibling_type==='Younger'?'selected':''}>Younger</option></select></div><div class="col-6"><input class="form-control" name="sibling_std[]" placeholder="Education / Occupation" value="${esc(d.sibling_std)}"></div><div class="col-12"><input class="form-control" name="sibling_income[]" placeholder="Annual Income" value="${esc(d.sibling_income)}"></div></div></div></div>`;} box.innerHTML=html; initPlugins(box); }
    function renderContacts(){
        const box=document.getElementById('contactsWrapper'); let n=Math.max(1,oldContacts.names.length,oldContacts.relations.length,oldContacts.numbers.length); let html=''; for(let i=0;i<n;i++){html+=`<div class="field-card mb-3 contact-row"><div class="d-flex justify-content-between mb-3"><span class="fw-semibold">Emergency Contact ${i+1}</span>${i>0?'<button type="button" class="btn btn-sm btn-outline-danger remove-contact"><i class="mdi mdi-delete-outline"></i></button>':''}</div><div class="row g-2"><div class="col-md-4"><input class="form-control" name="contact_person_name[]" placeholder="Contact Name" value="${esc(oldContacts.names[i])}"></div><div class="col-md-4"><select class="form-select select3" name="contact_person_relation[]"><option value="">Select Relation</option>@foreach(($relationshipList ?? []) as $r)<option value="{{ $r->sno }}" ${String(oldContacts.relations[i]??'')===String({{ $r->sno }})?'selected':''}>{{ $r->relationship_name }}</option>@endforeach</select></div><div class="col-md-4"><input class="form-control" name="contact_person_no[]" maxlength="10" inputmode="numeric" placeholder="Mobile Number" value="${esc(oldContacts.numbers[i])}"></div></div></div>`;} box.innerHTML=html; initPlugins(box); }
    function renderEducation(){
        const box=document.getElementById('educationWrapper'); let rows=oldEducation.length?oldEducation:[{}]; let html=''; rows.forEach((d,i)=>{html+=`<div class="field-card mb-3 education-row"><div class="d-flex justify-content-between align-items-center mb-3"><span class="fw-semibold">Qualification ${i+1}</span>${i>0?'<button type="button" class="btn btn-sm btn-outline-danger remove-education"><i class="mdi mdi-delete-outline"></i></button>':''}</div><div class="row g-2"><div class="col-md-3"><select class="form-select select3 qualification" name="qualification_type[]"><option value="">Select Qualification</option><option value="1" ${String(d.qualification_type)==='1'?'selected':''}>UG</option><option value="2" ${String(d.qualification_type)==='2'?'selected':''}>PG</option><option value="3" ${String(d.qualification_type)==='3'?'selected':''}>Doctorate</option><option value="4" ${String(d.qualification_type)==='4'?'selected':''}>HSC</option><option value="5" ${String(d.qualification_type)==='5'?'selected':''}>SSLC</option><option value="6" ${String(d.qualification_type)==='6'?'selected':''}>Below SSLC</option><option value="Others" ${d.qualification_type==='Others'?'selected':''}>Others</option></select></div><div class="col-md-3 major-wrap"><input class="form-control" name="major[]" placeholder="Major / Specialization" value="${esc(d.major)}"></div><div class="col-md-3 univ-wrap"><input class="form-control" name="univ_name[]" placeholder="Institute / University" value="${esc(d.university_name)}"></div><div class="col-md-3"><input class="form-control" name="pass_year[]" maxlength="4" placeholder="Year" value="${esc(d.year)}"></div></div></div>`}); box.innerHTML=html; initPlugins(box); applyQualificationRules(); }
    function applyQualificationRules(){document.querySelectorAll('.education-row').forEach(row=>{const q=row.querySelector('.qualification')?.value; const skip=['4','5','6','Others'].includes(String(q)); row.querySelector('.major-wrap')?.classList.toggle('d-none',skip); row.querySelector('.univ-wrap')?.classList.toggle('d-none',skip); if(skip){row.querySelector('[name="major[]"]').value='';row.querySelector('[name="univ_name[]"]').value='';}})}
    function showStep(n){current=n; sections.forEach(s=>s.classList.toggle('d-none',Number(s.dataset.section)!==n)); stepCards.forEach(s=>s.classList.toggle('active',Number(s.dataset.goStep)===n)); document.getElementById('currentStage').value=n; document.getElementById('previousBtn').disabled=n===1; document.getElementById('nextBtn').innerHTML=n===5?'Save Profile <i class="mdi mdi-check ms-1"></i>':'Save & Continue <i class="mdi mdi-arrow-right ms-1"></i>'; window.scrollTo({top:0,behavior:'smooth'}); initPlugins(document.querySelector(`[data-section="${n}"]`)); }
    function setSaving(v){document.getElementById('saveBtn').disabled=v;document.getElementById('nextBtn').disabled=v;document.getElementById('previousBtn').disabled=v||current===1;document.getElementById('saveLoader').classList.toggle('d-none',!v);}
    function alertBox(type,msg){const a=document.getElementById('saveAlert');a.className='alert alert-'+type; a.innerHTML=msg; a.classList.remove('d-none'); if(type==='success')setTimeout(()=>a.classList.add('d-none'),3500);}
    function applyServerCompletion(data){const overall=data?.overall||{}; const p=Number(overall.percentage||0); document.getElementById('completionBar').style.width=p+'%';document.getElementById('completionLabel').textContent=Math.round(p)+'%';document.querySelector('.completion-ring').style.setProperty('--completion',p);document.querySelector('.completion-ring strong').textContent=Math.round(p)+'%';const keys=['personal','family','contact','social_media','education'];keys.forEach((k,i)=>{const sp=data?.steps?.[k]?.percentage??0;const bar=document.querySelector(`[data-step-progress="${i+1}"]`);const label=document.querySelector(`[data-step-label="${i+1}"]`);if(bar)bar.style.width=sp+'%';if(label)label.textContent=Math.round(sp)+'%';});}
    async function save(goNext){
        if(!form.reportValidity()) return;
        setSaving(true); const fd=new FormData(form); fd.set('stage',String(current));
        try{const res=await fetch(form.action,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});const data=await res.json();if(!res.ok||data.status===false)throw new Error(data.message||'Unable to save your profile.');applyServerCompletion(data.completion||data);alertBox('success',data.message||'Profile saved successfully.');if(goNext&&current<5)showStep(current+1);else if(goNext&&current===5){document.getElementById('nextBtn').innerHTML='<i class="mdi mdi-check-circle-outline me-1"></i>Profile Saved';} }catch(e){alertBox('danger','<i class="mdi mdi-alert-circle-outline me-1"></i>'+esc(e.message));}finally{setSaving(false);}}

    document.querySelectorAll('[data-go-step]').forEach(x=>x.addEventListener('click',()=>showStep(Number(x.dataset.goStep))));
    document.getElementById('previousBtn').addEventListener('click',()=>showStep(Math.max(1,current-1)));
    document.getElementById('saveBtn').addEventListener('click',()=>save(false));
    document.getElementById('nextBtn').addEventListener('click',()=>save(true));
    document.getElementById('profilePhoto').addEventListener('change',e=>{const f=e.target.files[0];if(!f)return;if(f.size>819200){e.target.value='';alertBox('danger','Profile photo must be 800 KB or smaller.');return;}document.getElementById('profilePreview').src=URL.createObjectURL(f);});
    document.getElementById('vehicleCheck').addEventListener('change',e=>document.getElementById('vehicleFields').classList.toggle('d-none',!e.target.checked));
    document.getElementById('maritalStatus').addEventListener('change',e=>document.getElementById('spouseFields').classList.toggle('d-none',e.target.value!=='1'));
    document.addEventListener('change',e=>{if(e.target.name==='is_working')document.querySelectorAll('.spouse-working-fields').forEach(x=>x.classList.toggle('d-none',e.target.value!=='Yes'));if(e.target.name==='has_children')renderChildren();if(e.target.name==='has_Siblings')renderSiblings();if(e.target.classList.contains('qualification'))applyQualificationRules();if(e.target.classList.contains('social-toggle'))document.getElementById(e.target.dataset.target)?.classList.toggle('d-none',!e.target.checked);if(e.target.name==='is_Course')document.getElementById('courseField').classList.toggle('d-none',e.target.value!=='Yes');});
    document.getElementById('childrenCount').addEventListener('input',renderChildren);document.getElementById('siblingsCount').addEventListener('input',renderSiblings);
    document.getElementById('addContact').addEventListener('click',()=>{const box=document.getElementById('contactsWrapper');const first=box.querySelector('.contact-row');if(!first)return;const clone=first.cloneNode(true);clone.querySelectorAll('input').forEach(i=>i.value='');clone.querySelectorAll('select').forEach(s=>s.value='');clone.querySelector('.d-flex.justify-content-between').insertAdjacentHTML('beforeend','<button type="button" class="btn btn-sm btn-outline-danger remove-contact"><i class="mdi mdi-delete-outline"></i></button>');box.appendChild(clone);initPlugins(clone);});
    document.getElementById('addEducation').addEventListener('click',()=>{const box=document.getElementById('educationWrapper');const clone=box.querySelector('.education-row').cloneNode(true);clone.querySelectorAll('input').forEach(i=>i.value='');clone.querySelectorAll('select').forEach(s=>s.value='');clone.querySelector('.d-flex.justify-content-between').insertAdjacentHTML('beforeend','<button type="button" class="btn btn-sm btn-outline-danger remove-education"><i class="mdi mdi-delete-outline"></i></button>');box.appendChild(clone);initPlugins(clone);});
    document.getElementById('addDocument').addEventListener('click',()=>{const wrap=document.getElementById('documentWrapper');const row=document.createElement('div');row.className='field-card mb-3 document-row';row.innerHTML=`<div class="d-flex justify-content-between mb-3"><span class="fw-semibold">New document</span><button type="button" class="btn btn-sm btn-outline-danger remove-document"><i class="mdi mdi-delete-outline"></i></button></div><div class="row g-2"><div class="col-md-6"><select class="form-select" name="doc_type[]"><option value="">Select Document Type</option>@foreach(($documentTypeList ?? []) as $doc)<option value="{{ $doc->sno }}">{{ $doc->document_name }}</option>@endforeach</select></div><div class="col-md-6"><input type="file" class="form-control" name="attachment[]" multiple></div></div>`;wrap.appendChild(row);});
    document.addEventListener('click',e=>{const del=e.target.closest('.attachment-delete');if(del){const card=del.closest('.existing-doc');const input=card.querySelector('input[name^="existing_attachment_action"]');if(input.value==='delete'){input.value='keep';card.classList.remove('deleted');del.innerHTML='<i class="mdi mdi-delete-outline"></i>';}else{input.value='delete';card.classList.add('deleted');del.innerHTML='<i class="mdi mdi-undo-variant"></i>';}}const rc=e.target.closest('.remove-contact');if(rc)rc.closest('.contact-row').remove();const re=e.target.closest('.remove-education');if(re)re.closest('.education-row').remove();const rd=e.target.closest('.remove-document');if(rd)rd.closest('.document-row').remove();});

    renderChildren();renderSiblings();renderContacts();renderEducation();initPlugins();showStep(1);
})();
</script>
@endsection
