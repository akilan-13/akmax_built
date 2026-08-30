@extends('layouts/layoutMaster')

@section('title', 'Edit Staff')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js', 'resources/assets/vendor/libs/sortablejs/sortable.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/form_wizard_icons.js'])
    @vite('resources/assets/js/forms-file-upload.js')
    @vite('resources/assets/js/forms-pickers.js')

@endsection

@section('content')



    <style>
        .dataTables_scroll {
            max-height: 200px;
        }

        /* Completed step */
        .bs-stepper-header .step.crossed .step-trigger .avatar .avatar-initial {
            background-color: #28a745 !important;
            color: #fff !important;
        }

        /* Active/current step */
        .bs-stepper-header .step.active .step-trigger .avatar .avatar-initial {
            background-color: #0d6efd !important;
        }

        /* Pending step */
        .bs-stepper-header .step.disabled .step-trigger .avatar .avatar-initial {
            background-color: #e9ecef;
            color: #6c757d;
            pointer-events: none;
        }
        /*  .step-trigger.no-click {
             pointer-events: none; 
           }

           .err_border{
            border:1px solid red;
           }*/
           
    /* Horizontal scroll container */
    .file-previews {
        display: flex;
        flex-wrap: nowrap;
        gap: 10px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 10px 0;
        scroll-behavior: smooth;
        cursor: grab; /* 👈 show grab hand */
        user-select: none;
    }

    /* While dragging */
    .file-previews:active {
        cursor: grabbing; /* 👈 active grab effect */
    }

    /* Optional: scrollbar style */
    .file-previews::-webkit-scrollbar {
        height: 8px;
    }
    .file-previews::-webkit-scrollbar-thumb {
        background: #c94545;
        border-radius: 4px;
    }
    .file-previews::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

        /* Dropzone preview styles */
        .dz-preview {
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #fff;
            text-align: center;
            padding: 5px;
            width: 120px !important;
            height: 150px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            flex: 0 0 auto;
            position: relative;
        }

        /* File image */
        .dz-image img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* Progress bar */
        .dz-progress {
            position: absolute;
            bottom: 6px;
            left: 5px;
            right: 5px;
            height: 5px;
            background: #eee;
            border-radius: 3px;
            overflow: hidden;
        }
        .dz-filename {
            position: absolute;
            width: 120px;
            overflow: hidden;
            padding: 0.625rem 0.625rem 0 0.625rem;
            background: #fff;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .dz-upload {
            display: block;
            height: 100%;
            background: #c94545;
            width: 0;
            transition: width 0.3s ease;
        }

        /* Success and error icons */
            .dz-success-mark,
            .dz-error-mark {
                position: absolute;
                top: 40%;
                left: 40%;
                /* transform: translate(-50%, -50%); */
                font-size: 32px;
                display: none;
                z-index: 10; /* ensures it's above the image */
            }

            /* Success checkmark */
            .dz-success-mark {
                color: #28a745; /* green */
            }

            /* Error cross */
            .dz-error-mark {
                color: #dc3545; /* red */
            }

            /* Show only when upload succeeds or fails */
            .dz-success .dz-success-mark {
                display: block;
            }
            .dz-error .dz-error-mark {
                display: block;
            }

            /* Make sure the image wrapper is positioned for absolute centering */
            .dz-image {
                position: relative;
                overflow: hidden;
                border-radius: 6px;
            }



      /* Example: Customizing optgroup label color */
      .select2-results__group {
          color: #beb63c; /* Primary blue color */
          font-weight: bold;
      }

    </style>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Lead List Table -->
    <div class="card card-action mb-2">
        <div class="card-header border-bottom pb-1 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-start justify-content-start flex-column">
                <h5 class="card-title mb-1 text-black">Update Staff</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb custom-breadcrumb">
                        <!-- Home -->
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">
                                <i class="mdi mdi-home"></i> Home
                            </a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <a href="javascript:void(0);">
                                <i class="mdi mdi-account-group"></i> HR Management
                            </a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <a href="javascript:void(0);">
                                HR Enroll
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="javascript:void(0);" class="active-link">
                                Manage Staff
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('add_staff') }}" enctype="multipart/form-data" autocomplete="off" data-track-percentage
        id="staff_form">
        @csrf
        <input type="hidden" name="completion_percentage" id="completion_percentage" class="form-percentage" value="0">
        <input type="hidden" name="edit_id" id="edit_id"  value="{{$staffData->sno}}">
        <div class="bs-stepper wizard-vertical vertical wizard-vertical-icons-example wizard-vertical-icons mt-2 gap-3 ">
        <div class="bs-stepper-header gap-lg-2">
            <div class="step active" data-target="#staff_add">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="fa-solid fa-handshake"></i>
                        </span>
                    </span>
                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Base Details</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="staff_add" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>  
            <div class="step" data-target="#family_add">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-human-male-male-child fs-3"></i>
                        </span>
                    </span>
                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Family Details</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="family_add" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step" data-target="#contact_add">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-card-account-phone fs-3"></i>
                        </span>
                    </span>
                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Contact Details</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="contact_add" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step" data-target="#socialmedia">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-web fs-3"></i>
                        </span>
                    </span>

                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Social Media</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="socialmedia" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step" data-target="#Education">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-book-education fs-3"></i>
                        </span>
                    </span>
                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Education</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="Education" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step" data-target="#work_add">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-monitor fs-3"></i>
                        </span>
                    </span>
                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Work Type</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="work_add" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step" data-target="#companydetails">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-office-building fs-3"></i>
                        </span>
                    </span>

                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Company Details</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="companydetails" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step " data-target="#salarydetails">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-calendar-account fs-3"></i>
                        </span>
                    </span>

                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Salary Details</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="salarydetails" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            <div class="step" data-target="#application_add">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-file-outline fs-3"></i>
                        </span>
                    </span>

                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">Application Details</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="application_add" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                    </div>
                </button>
            </div>
            <div class="step" data-target="#checklist_add">
                <button type="button" class="step-trigger no-click">
                    <span class="avatar">
                        <span class="avatar-initial rounded-2">
                            <i class="mdi mdi-format-list-checks fs-3"></i>
                        </span>
                    </span>

                    <div class="d-flex flex-column gap-1">
                        <span class="bs-stepper-label flex-column align-items-start gap-1 ms-2">
                            <span class="bs-stepper-title">CheckList</span>
                        </span>
                        <div class="progress step-progress ms-2 rounded" data-step="checklist_add" style="height: 10px; width:100px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </button>
            </div>
            
        </div>
        <div class="bs-stepper-content">
            <div id="staff_add" class="content active">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Personal Details</label>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <label class="text-black fs-6 fw-semibold mb-2">Staff Image</label>
                        <div class="d-flex align-items-sm-center justify-content-start">
                            <div class="d-flex align-items-start justify-content-center flex-column gap-2">
                               @if($staffData->company_type == 1)
                                    @php
                                        $imagePath = public_path('staff_images/Management/' . $staffData->staff_image);
                                    @endphp
                                @else
                                    @php
                                        $imagePath = public_path('staff_images/Buisness/'.$staffData->company_id.'/'.$staffData->entity_id.'/'.$staffData->staff_image);
                                    @endphp
                                @endif

                                <img src="{{ file_exists($imagePath) && !empty($staffData->staff_image) ? asset('staff_images/' . ($staffData->company_type == 1 ? 'Management/' : 'Buisness/' . $staffData->company_id . '/' . $staffData->entity_id . '/') . $staffData->staff_image) : asset('assets/egc_images/auth/user_3.png') }}" 
                                    alt="Attachment" 
                                    class="d-block w-px-120 h-px-120 rounded" 
                                    id="logo_create"
                                    style="border: 2px solid #ab2b22;" />
                                
                                <div class="small">Allowed JPG, PNG. Max size of 800K</div>
                            </div>
                            <div class="button-wrapper">
                                <div class="d-flex align-items-start justify-content-start mt-2 mb-2 flex-wrap gap-2">
                                    <label class="btn btn-sm btn-primary me-2" tabindex="0">
                                        <span class="fw-semibold text-white fs-6">Upload</span>
                                        <input type="file" name="staff_add_icon" id="fav_upload" class="fav_file-in" hidden accept="image/png, image/jpeg" data-ignore-total/>
                                        <input type="hidden" name="old_staff_image" id="old_staff_image"
                                            value="{{ $staffData->staff_image ?? '' }}">
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-danger fav_file-reset">
                                        <span class="fw-semibold text-primary fs-6">Reset</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Staff Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="staff_name" name="staff_name"
                        value="{{$staffData->staff_name}}"
                            placeholder="Enter Staff Name"
                            oninput="formatName(this)" />
                            <div class="text-danger" id="staff_name_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Staff Mobile No<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mobile_no" name="mobile_no" maxlength="10"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Enter Mobile No" onkeyup="mobile_chk(this.value)" value="{{$staffData->mobile_no}}"/>
                            <div class="text-danger" id="mobile_no_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Gender<span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_male"
                                    value="1" {{$staffData->gender == '1' ? 'checked' : ''}} />
                                <label class="form-check-label" for="gender_male">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_female"
                                    value="2" {{$staffData->gender == '2' ? 'checked' : ''}} />
                                <label class="form-check-label" for="gender_female">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_others"
                                    value="3" {{ ($staffData->gender != 1 && $staffData->gender != 2 ) ? 'checked' : ''}}/>
                                <label class="form-check-label" for="gender_others">Others</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Date of Birth<span
                                class="text-danger">*</span><span id="age_display" class="badge bg-label-info rounded"></span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                            <input type="text" id="staff_dob" name="dob" placeholder="Select Date"
                                class="form-control common_datepicker" readonly  value="{{$staffData->dob}}"/>
                                
                        </div>
                        <div class="text-danger" id="staff_dob_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Birth Place</label>
                        <input type="text" class="form-control" id="birth_place" name="birth_place"  placeholder="Enter Birth Place" oninput="this.value=this.value.replace(/^\w/, txt=>txt.toUpperCase());" value="{{$staffData->birth_place ?? ''}}" />
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Email ID<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="email_id" name="email_id"
                            placeholder="Enter E-Mail ID" oninput="this.value=this.value.toLowerCase();" value="{{$staffData->email_id}}"/>
                            <div class="text-danger" id="email_id_err"></div>
                    </div>
                    @php
                      $language_ids = !empty($staffData->languages) ? json_decode($staffData->languages, true) : [];
                      $hobby_ids = !empty($staffData->hobby) ? json_decode($staffData->hobby, true) : [];
                    @endphp
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Mother Tongue<span class="text-danger">*</span></label>
                        <select id="mother_tongue" name="mother_tongue" class="select3 form-select">
                            <option value="">Select Mother Tongue</option>
                            @if(isset($languageList))
                            @foreach($languageList as $language)
                                <option value="{{$language->sno}}" {{ $language->sno == $staffData->mother_tongue ? 'selected' : '' }}>{{$language->name}}</option>
                            @endforeach
                            @endif
                        </select>
                            <div class="text-danger" id="mother_tongue_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Languages Known<span
                                class="text-danger">*</span></label>
                        <select id="Languages" name="Languages[]" class="select3 form-select" multiple data-placeholder="Select Languages">
                            <option value="">Select Languages</option>
                            @if(isset($languageList))
                            @foreach($languageList as $language)
                                <option value="{{$language->sno}}" {{ in_array($language->sno, $language_ids) ? 'selected' : '' }}>{{$language->name}}</option>
                            @endforeach
                            @endif
                            
                        </select>
                        <div class="text-danger" id="Languages_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Hobby</label>
                        <select id="hobby" name="hobby[]" class="select3 form-select" multiple data-placeholder="Select Hobby">
                            <option value="">Select Hobby</option>
                            @if(isset($hobbyList))
                            @foreach($hobbyList as $hobby)
                                <option value="{{$hobby->sno}}" {{ in_array($hobby->sno, $hobby_ids) ? 'selected' : '' }}>{{$hobby->hobby_name}}</option>
                            @endforeach
                            @endif
                            
                        </select>
                        <div class="text-danger" id="hobby_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Blood Group<span class="text-danger">*</span></label>
                        <select id="blood_group" name="blood_group" class="select3 form-select">
                            <option value="">Select Blood Group</option>
                            @if(isset($bloodGroupList))
                            @foreach($bloodGroupList as $blood)
                                <option value="{{$blood->sno}}" {{ $blood->sno == $staffData->blood_group ? 'selected' : '' }}>{{$blood->blood_group}}</option>
                            @endforeach
                            @endif
                        </select>
                            <div class="text-danger" id="blood_group_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Height in Cm</label>
                        <input type="text" class="form-control" id="height" name="height" maxlength="4"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'');" placeholder="Enter Height" value="{{$staffData->height ?? ''}}" />
                            <div class="text-danger" id="height_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Weight in Kg</label>
                        <input type="text" class="form-control" id="weight" name="weight" maxlength="4"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'');" placeholder="Enter Weight" value="{{$staffData->weight ?? ''}}"/>
                            <div class="text-danger" id="weight_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Nationality<span class="text-danger">*</span></label>
                        <select id="nationality" name="nationality" class="select3 form-select">
                            <option value="">Select Nationality</option>
                            @if(isset($nationalityList))
                            @foreach($nationalityList as $blood)
                                <option value="{{$blood->sno}}" {{ $blood->sno == $staffData->nationality_id ? 'selected' : '' }}>{{$blood->nationality_name}}</option>
                            @endforeach
                            @endif
                        </select>
                            <div class="text-danger" id="nationality_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Religion<span class="text-danger">*</span></label>
                        <select id="religion" name="religion" class="select3 form-select">
                            <option value="">Select Religion</option>
                            @if(isset($religionList))
                            @foreach($religionList as $blood)
                                <option value="{{$blood->sno}}" {{ $blood->sno == $staffData->religion_id ? 'selected' : '' }}>{{$blood->religion_name}}</option>
                            @endforeach
                            @endif
                        </select>
                            <div class="text-danger" id="religion_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Community<span class="text-danger">*</span></label>
                        <select id="community" name="community" class="select3 form-select" onchange="community_change_func()">
                            <option value="">Select Community</option>
                            @if(isset($communityList))
                            @foreach($communityList as $blood)
                                <option value="{{$blood->sno}}" {{ $blood->sno == $staffData->community_id ? 'selected' : '' }}>{{$blood->community_code}} - {{$blood->community_name}}</option>
                            @endforeach
                            @endif
                        </select>
                            <div class="text-danger" id="community_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between gap-5">
                            <label class="text-black fs-6 fw-semibold">Caste</label>
                            <div class="dropdown">
                                <i class="mdi mdi-cog-outline cursor-pointer more-options-dropdown" role="button" id="dropdownMenuButton" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" onclick="return casteSettingAdd(event)">
                                </i>
                                <div class="dropdown-menu dropdown-menu-end w-px-300 p-3" aria-labelledby="dropdownMenuButton">
                                        
                                    <div class="row g-3" id="add_sett_module_div">
                                        <input type="hidden" id="sett_religion_id_add">
                                        <input type="hidden" id="sett_community_id_add">

                                        <div class="col-12">
                                            <label class="form-label">Religion <span class="text-danger">*</span></label>
                                            <div class="form-control bg-light">
                                                <span id="sett_religion_name_add">--</span>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Community <span class="text-danger">*</span></label>
                                            <div class="form-control bg-light">
                                                <span id="sett_community_name_add">--</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="sett_community_name_add" class="form-label">Caste Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sett_caste_name_add"/>
                                            <div id="sett_caste_name_add_err"></div>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" id="btn_add_caste_setting" class="btn btn-outline-primary btn-apply-changes" onclick="submitSettCasteList()" disabled>Add Caste</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <select id="caste" name="caste" class="select3 form-select" >
                            <option value="">Select Caste</option>
                        </select>
                            <div class="text-danger" id="caste_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Aadhar Number<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="aadhar_no" name="aadhar_no"  oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Enter Aadhar no."  maxlength="12" value="{{$staffStatutory ? ($staffStatutory->aadhar_no ?? '') : ''}}" />
                            <div class="text-danger" id="aadhar_no_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">PAN Number<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pan_no" name="pan_no" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');" placeholder="Enter PAN no."  maxlength="10" value="{{$staffStatutory ? ($staffStatutory->pan_no ?? '') : ''}}"/>
                        <div class="text-danger" id="pan_no_err"></div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="text-black fs-6 fw-semibold">Identification Mark</label>
                        <textarea class="form-control" rows="1" id="identification_mark" name="identification_mark"
                            placeholder="Enter Identification Mark">{{$staffData->identification_mark ?? ''}}</textarea>
                    </div>

                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Vehicle <input class="form-check-input me-2" type="checkbox" id="vehicle_check" value="1" {{$staffData->vehicle_check == 1 ? 'checked' : ''}} name="vehicle_check" onchange="vehicle_check_change()"/></label>
                    </div>

                    
                    <div class="col-lg-12 mb-3 vehicle_check">
                        <div class="row">
                            <div class="col-lg-4 err-chk">
                                <label class="fw-semibold mb-1 text-black">
                                    <span id="user_name_label">Driving License No</span><span
                                        class="text-danger ">*</span>
                                </label>
                                <input type="text" class="form-control  required-field"
                                    id="driving_license_no" name="driving_license_no" placeholder="Driving License No" value="{{$staffData->driving_license_no ?? ''}}" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,20);" maxlength="20"/>
                                <div class="text-danger error_msg" id="driving_license_no_err"></div>
                            </div>
                            <div class="col-lg-4 err-chk">
                                <label class="fw-semibold mb-1 text-black">
                                    <span id="user_name_label">Vehicle Registration No</span><span
                                        class="text-danger ">*</span>
                                </label>
                                <input type="text" class="form-control  required-field"
                                    id="vehicle_register_no" name="vehicle_register_no" placeholder="Vehicle Registration No" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,15);" maxlength="15" value="{{$staffData->vehicle_register_no ?? ''}}" />
                                <div class="text-danger error_msg" id="vehicle_register_no_err"></div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">License Expiry Date</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                    <input type="text" id="license_expiry" name="license_expiry" placeholder="Select Date" class="form-control common_datepicker" value="{{$staffData->license_expiry ?? ''}}" readonly/>
                                        
                                </div>
                                <div class="text-danger" id="license_expiry_err"></div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary " id="prev1" disabled>
                            <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <div class="d-flex gap-2">
                            <button  type="button" class="btn btn-primary" id="updateClose1" onclick="close_validation_func(1)" disabled>
                                 <span id="updateBtnText1">Update & Close</span>
                                <span id="updateBtnLoader1" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button  type="button" class="btn btn-primary" id="updateNxt1" onclick="next_validation_func(1)" disabled>
                                <span id="updateNxtText1" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                                <span id="updateNxtLoader1" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button  type="button" class="btn btn-primary" id="stage1" onclick="validation_func(1)">
                                <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="family_add" class="content">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Father Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="father_name" id="father_name" placeholder="Enter Father Name" value="{{$staffFamily ? $staffFamily->father_name : ''}}" oninput="formatName(this)"/>
                        <div class="text-danger" id="father_name_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Father Occupation<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Father Occupation" name="father_occup" id="father_occup" value="{{$staffFamily ? $staffFamily->father_occup : ''}}" oninput="formatName(this)"/>
                        <div class="text-danger" id="father_occup_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Mother Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Mother Name" id="mother_name" name="mother_name" value="{{$staffFamily ? $staffFamily->mother_name : ''}}" oninput="formatName(this)"/>
                        <div class="text-danger" id="mother_name_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Mother Occupation<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Mother Occupation" name="mother_occup" id="mother_occup" value="{{$staffFamily ? $staffFamily->mother_occup : ''}}" oninput="formatName(this)"/>
                        <div class="text-danger" id="mother_occup_err"></div>
                    </div>
                    @php
                        $children = !empty($staffFamily->children_details) ? json_decode($staffFamily->children_details, true) : [];
                        $sibling = !empty($staffFamily->siblings_detail) ? json_decode($staffFamily->siblings_detail, true) : [];
                        $spouseWorkingCheck = $staffFamily->spouse_working ?? 'No';
                        $has_childrenCheck = $staffFamily->has_children ?? 'No';
                        $has_siblingsCheck = $staffFamily->has_siblings ?? 'No';
                        $is_CourseCheck = $staffData->is_Course ?? 'No';
                        
                    @endphp
                    <div class="col-lg-4 mb-3">
                        <label class="text-black fs-6 fw-semibold">Marital Status<span
                                class="text-danger">*</span></label>
                        <select id="marital_status" name="marital_status" class="select3 form-select">
                            <option value="">Select Marital Status</option>
                            <option value="1" {{$staffData->martial_status ==1 ? 'selected' : ''}}>Married</option>
                            <option value="2" {{$staffData->martial_status ==2 ? 'selected' : ''}}>Unmarried</option>
                            
                        </select>
                        <div class="text-danger" id="marital_status_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 spouse-field d-none">
                        <label class="text-black fs-6 fw-semibold">Anniversary Date</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                            <input type="text" name="anniversary_date" placeholder="Select Date" id="anniversary_date"
                                class="form-control common_datepicker" value="{{$staffFamily->anniversary_date ??''}}" readonly/>
                        </div>
                        <div class="text-danger" id="anniversary_date_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 spouse-field d-none">
                        <label class="text-black fs-6 fw-semibold">Spouse Name</label>
                        <input type="text" class="form-control" placeholder="Enter Spouse Name" name="spouse_name" id="spouse_name" value="{{$staffFamily->spouse_name ??''}}" oninput="formatName(this)"/>
                        <div class="text-danger" id="spouse_name_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 spouse-field d-none">
                        <label class="text-black fs-6 fw-semibold">Spouse Mobile No</label>
                        <input type="text" class="form-control" placeholder="Enter Spouse Mobile No" name="spouse_mobile" id="spouse_mobile" value="{{$staffFamily->spouse_mobile ??''}}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"/>
                        <div class="text-danger" id="spouse_mobile_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 spouse-field d-none">
                        <label class="text-black fs-6 fw-semibold">Date Of Birth</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                            <input type="text" id="spouse_dob" name="spouse_dob" placeholder="Select Date"
                                class="form-control common_datepicker" value="{{$staffFamily->spouse_dob ??''}}" readonly/>
                        </div>
                        <div class="text-danger" id="spouse_dob_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 spouse-field d-none">
                        <label class="text-black mb-1 fs-6 fw-semibold">Spouse Working ?</label>
                        <div class="d-block">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_working" id="workingYes"
                                    value="Yes" {{$spouseWorkingCheck == 'Yes' ? 'checked' : ''}}/>
                                <label class="form-check-label" for="workingYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_working" id="workingNo" {{$spouseWorkingCheck == 'Yes' ? '' : 'checked'}}
                                    value="No" />
                                <label class="form-check-label" for="workingNo">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 working-fields d-none">
                        <label class="text-black fs-6 fw-semibold">Spouse Designation</label>
                        <input type="text" class="form-control" placeholder="Enter Spouse Designation" id="spouse_designation" name="spouse_designation" value="{{$staffFamily->spouse_designation ??''}}" oninput="formatName(this)"/>
                        <div class="text-danger" id="spouse_designation_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 working-fields d-none">
                        <label class="text-black fs-6 fw-semibold">Spouse Company Name</label>
                        <input type="text" class="form-control" placeholder="Enter Spouse Company Name" id="spouse_company_name" name="spouse_company_name" value="{{$staffFamily->spouse_company_name ??''}}" oninput="formatCompanyName(this)"/>
                        <div class="text-danger" id="spouse_company_name_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3 working-fields d-none">
                        <label class="text-black fs-6 fw-semibold">Spouse Salary in LPA</label>
                        <input type="text" class="form-control" placeholder="Enter Spouse Salary" id="spouse_salary" name="spouse_salary" value="{{$staffFamily->spouse_salary ??''}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                        <div class="text-danger" id="spouse_salary_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Children ?</label>
                        <div class="d-block">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_children" id="childrenYes"
                                    value="Yes" {{$has_childrenCheck == 'Yes' ? 'checked' : ''}}/>
                                <label class="form-check-label" for="childrenYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_children" id="childrenNo" {{$has_childrenCheck == 'Yes' ? '' : 'checked'}}
                                    value="No" />
                                <label class="form-check-label" for="childrenNo">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 children-section d-none">
                        <label class="text-black mb-1 fs-6 fw-semibold">Children Count <span
                                class="text-danger">*</span></label>
                        <input type="text" id="childrenCount" name="childrenCount" class="form-control" placeholder="Enter Children Count"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,1)" value="{{$staffFamily->children_count ?? '0'}}">
                            <div class="text-danger" id="childrenCount_err"></div>
                    </div>
                    <div id="childrenDetails" class="col-lg-12 mt-3"></div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Siblings ?</label>
                        <div class="d-block">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_Siblings" id="SiblingsYes"
                                    value="Yes" {{$has_siblingsCheck == 'Yes' ? 'checked' : ''}}/>
                                <label class="form-check-label" for="SiblingsYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_Siblings" id="SiblingsNo" 
                                    value="No" {{$has_siblingsCheck == 'Yes' ? '' : 'checked'}}/>
                                <label class="form-check-label" for="SiblingsNo">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3 sibling-section d-none">
                        <label class="text-black mb-1 fs-6 fw-semibold">Siblings Count <span class="text-danger">*</span></label>
                        <input type="text" id="siblingsCount" name="siblingsCount" value="{{$staffFamily->sibling_count ?? '0'}}" class="form-control" placeholder="Enter Siblings Count" maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,1)">
                        <div class="text-danger" id="siblingsCount_err"></div>
                    </div>
                    <div id="siblingDetails" class="col-lg-12 mt-3"></div>
                    <!-- <div class="col-lg-4 mb-3 d-none" id="siblingsDescription">
                        <label class="text-black mb-1 fs-6 fw-semibold">Siblings Details</label>
                        <textarea class="form-control" rows="1" placeholder="Enter Siblings Details" name="siblings_detail" id="siblings_detail">{{$staffFamily->siblings_detail ?? ''}}</textarea>
                        <div class="text-danger" id="siblings_detail_err"></div>
                    </div> -->
                </div>
                <div class="col-12 d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="prev2" onclick="safePrev(2)">
                        <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    <div class="d-flex gap-2">
                        <button  type="button" class="btn btn-primary" id="updateClose2" onclick="close_validation_func(2)" disabled>
                                <span id="updateBtnText2">Update & Close</span>
                            <span id="updateBtnLoader2" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-primary " id="updateNxt2" onclick="next_validation_func(2)" disabled>
                            <span id="updateNxtText2" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                            <span id="updateNxtLoader2" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button  type="button" class="btn btn-primary" id="stage2" onclick="validation_func(2)">
                            <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div id="contact_add" class="content">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Residential Details</label>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Permanent Address <span class="text-danger">*</span></label>
                        <textarea class="form-control required-field" rows="1" name="permanent_address" id="permanent_address" placeholder="Enter Permanent Address" >{{$staffData->address ?? ''}}</textarea>
                        <div class="text-danger" id="permanent_address_err"></div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Residential Address</label>
                        <textarea class="form-control required-field" rows="1" name="residential_address" id="residential_address"
                            placeholder="Enter Residential Address">{{$staffData->residential_address ?? ''}}</textarea>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Location URL </label>
                        <input type="text" class="form-control required-field" placeholder="Enter Location URL" name="staff_location_url" id="staff_location_url" value="{{$staffData->location_url ?? ''}}">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Latitude</label>
                        <input type="text" class="form-control required-field" placeholder="Enter latitude" name="staff_latitude" id="staff_latitude" value="{{$staffData->latitude ?? ''}}">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Longitude</label>
                        <input type="text" class="form-control required-field" placeholder="Enter longitude" name="staff_longitude" id="staff_longitude" value="{{$staffData->longitude ?? ''}}">
                    </div>
                    @php
                        $contact_person_name = json_decode($staffData->contact_person_name ?? '[]', true);
                        $contact_person_relation = json_decode($staffData->contact_person_relation ?? '[]', true);
                        $contact_person_no = json_decode($staffData->contact_person_no ?? '[]', true);
                    @endphp
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Contact Details</label>
                    </div>
                    <div class="col-lg-12">
                        <div id="altmobile-wrapper">
                            <div class="altmobile-row bg-gray-200 rounded px-2 py-2 mb-2">
                                <div class="row">
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold" for="contact_person_name">Contact
                                            Person<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control required-field"
                                            id="contact_person_name" name="contact_person_name[]"
                                            placeholder="Enter Contact Person Name"
                                            oninput="formatName(this)" />
                                            <div class="text-danger" id="contact_person_name_err"></div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold" for="contact_person_relation">Contact
                                            Person Relation<span class="text-danger">*</span></label>
                                        <select id="contact_person_relation" name="contact_person_relation[]" class="select3 form-select" >
                                                <option value="">Select Relation</option>
                                                @if(isset($relationshipList))
                                                @foreach($relationshipList as $relation)
                                                    <option value="{{$relation->sno}}">{{$relation->relationship_name}}</option>
                                                @endforeach
                                                @endif
                                                
                                            </select>
                                            <div class="text-danger" id="contact_person_relation_err"></div>
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold text-nowrap" for="contact_person_no">Contact
                                            Person Mobile No.
                                            <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control required-field me-2" maxlength="10"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                                            id="contact_person_no" name="contact_person_no[]"
                                            placeholder="Enter Contact Person Mobile Number" />
                                            <div class="text-danger" id="contact_person_no_err"></div>
                                    </div>
                                    <div class="col-lg-1 d-flex align-items-center mt-2 justify-content-center">
                                        <div class="d-flex align-items-end mb-2 justify-content-end">
                                            <a href="javascript:;" class="btn text-danger px-2 py-1 altmobile_del"
                                                style="display: none !important;">
                                                <i class="fa-solid fa-trash-can fs-4"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="mb-1 d-flex align-items-center justify-content-end">
                            <button type="button" class="btn btn-primary" id="add-altmobile-btn">
                                <i class="mdi mdi-plus me-1"></i>Add More
                            </button>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary " id="prev3" onclick="safePrev(3)">
                            <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        
                        <div class="d-flex gap-2">
                            <button  type="button" class="btn btn-primary" id="updateClose3" onclick="close_validation_func(3)" disabled>
                                 <span id="updateBtnText3">Update & Close</span>
                                <span id="updateBtnLoader3" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-primary" id="updateNxt3" onclick="next_validation_func(3)" disabled>
                                <span id="updateNxtText3" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                                <span id="updateNxtLoader3" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button  type="button" class="btn btn-primary" id="stage3" onclick="validation_func(3)">
                                <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="socialmedia" class="content">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Social Media Details</label>
                    </div>
                    @php
                        $social_media_details = json_decode($staffData->social_media_details ?? '[]', true);
                    @endphp
                    <div class="col-lg-12 mb-3">
                        <div class="row">
                            @if(isset($social_media_list))
                                @foreach($social_media_list as $slist)
                                    <div class="col-lg-4 mb-3">
                                        <div class="form-check mb-2">
                                            <input 
                                                class="form-check-input toggle-field" 
                                                type="checkbox" 
                                                id="checkSocialMedia_{{$slist->sno}}" 
                                                data-target="#socialMediaField_{{$slist->sno}}"
                                            >
                                            <label 
                                                class="form-check-label text-black mb-1 fs-6 fw-semibold" 
                                                for="checkSocialMedia_{{$slist->sno}}"
                                            >
                                                {{$slist->social_media_name}}
                                            </label>
                                        </div>

                                        <div id="socialMediaField_{{$slist->sno}}" class="d-none">
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                name="social_media[{{$slist->sno}}]" 
                                                placeholder="Enter {{$slist->social_media_name}} URL" 
                                            />
                                        </div>
                                        <div class="text-danger" id="checkSocialMedia_{{$slist->sno}}_err"></div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary " id="prev4" onclick="safePrev(4)">
                        <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    
                    <div class="d-flex gap-2">
                        <button  type="button" class="btn btn-primary" id="updateClose4" onclick="close_validation_func(4)" disabled>
                                <span id="updateBtnText4">Update & Close</span>
                            <span id="updateBtnLoader4" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-primary" id="updateNxt4" onclick="next_validation_func(4)" disabled>
                            <span id="updateNxtText4" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                            <span id="updateNxtLoader4" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button  type="button" class="btn btn-primary" id="stage4" onclick="validation_func(4)">
                            <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div id="Education" class="content">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Educational Details</label>
                    </div>
                    @php
                        $oldEdu = $staffEducation->map(function($edu) {
                            return [
                                'qualification_type' => $edu->qualification_type,
                                'degree' => $edu->degree_name,
                                'major' => $edu->major,
                                'university_name' => $edu->university_name,
                                'year' => $edu->year,
                            ];
                        });
                    @endphp
                    <div class="col-lg-12 mb-3">
                           <div id="education-wrapper">
                            <div class="education-row bg-gray-200 rounded px-2 py-2 mb-2">
                                <div class="row">
                                    <div class="col-lg-11">
                                        <div class="row">
                                            <div class="col-lg-4 mb-3">
                                                <label class="text-black mb-1 fs-6 fw-semibold">Qualification<span
                                                        class="text-danger">*</span></label>
                                                <select id="qualification_type_0" name="qualification_type[]"
                                                    class="select3 form-select required-field" onchange="qualification_change(this)">
                                                    <option value="">Select Qualification</option>
                                                    <option value="1">UG</option>
                                                    <option value="2">PG</option>
                                                    <option value="3">Doctorate</option>
                                                    <option value="4">HSC</option>
                                                    <option value="5">SSLC</option>
                                                    <option value="6">Below SSLC</option>
                                                    <option value="Others">Others</option>
                                                </select>
                                                <div class="text-danger error_msg"></div>
                                            </div>
                                            <div class="col-lg-4 mb-3" >
                                                <label class="text-black mb-1 fs-6 fw-semibold">Major<span class="text-danger">*</span></label>
                                                <select  name="major[]" class="select3 form-select required-field" >
                                                    <option value="">Select Major</option>
                                                    
                                                </select>
                                                <div class="text-danger error_msg"></div>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <label class="text-black mb-1 fs-6 fw-semibold">Institute / University
                                                    Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required-field"
                                                    name="univ_name[]" placeholder="Enter Institute / University Name" oninput="formatCompanyName(this)"/>
                                                     <div class="text-danger error_msg"></div>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <label class="text-black mb-1 fs-6 fw-semibold">Year
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required-field"
                                                    name="pass_year[]" placeholder="Enter Year" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"/>
                                                     <div class="text-danger error_msg"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 d-flex align-items-center mt-2 justify-content-center">
                                        <div class="d-flex align-items-end mb-2 justify-content-end">
                                            <a href="javascript:;" class="btn text-danger px-2 py-1 staff_edu_del"
                                                style="display: none !important;">
                                                <i class="fa-solid fa-trash-can fs-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-1 mt-2 d-flex align-items-center justify-content-end">
                            <button type="button" class="btn btn-primary" id="add-edu-btn">
                                <i class="mdi mdi-plus me-1"></i>Add More
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <label class="text-black mb-1 fs-6 fw-semibold">Any Course Completed ?<span
                                class="text-danger">*</span></label>
                        <div class="d-block">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input required-field" type="radio" name="is_Course"
                                    id="CourseYes" value="Yes" {{$is_CourseCheck == 'Yes' ? 'checked' : ''}}/>
                                <label class="form-check-label" for="CourseYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input required-field" type="radio" name="is_Course"
                                    id="CourseNo" value="No" {{$is_CourseCheck == 'Yes' ? '' : 'checked'}} />
                                <label class="form-check-label" for="CourseNo">No</label>
                            </div>
                        </div>
                    </div>
                    @php
                    $courseTag = !empty($staffData->course_tag) 
                        ? json_decode($staffData->course_tag, true) 
                        : [];
                      
                    @endphp
                    <div class="col-lg-8 mb-3 d-none" id="courseAttachmentHeader">
                        <label class="text-black mb-1 fs-6 fw-semibold">Enter Course <span
                                class="text-danger">*</span></label>
                        <div class="form-floating form-floating-outline">
                            <input id="course_tag" name="course_tag" class="form-control h-auto course_tag required-field"
                                row="1" placeholder="Select Course Tags"  value="{{ !empty($courseTag) ? $courseTag :'' }}">
                                <div class="text-danger error_msg"></div>
                        </div>
                    </div>

                     

                    <div class="col-12 d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary " id="prev5" onclick="safePrev(5)">
                            <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        
                        <div class="d-flex gap-2">
                            <button  type="button" class="btn btn-primary" id="updateClose5" onclick="close_validation_func(5)" disabled>
                                <span id="updateBtnText5">Update & Close</span>
                                <span id="updateBtnLoader5" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-primary " id="updateNxt5" onclick="next_validation_func(5)" disabled>
                                <span id="updateNxtText5" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                                <span id="updateNxtLoader5" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button  type="button" class="btn btn-primary" id="stage5" onclick="validation_func(5)">
                                <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="work_add" class="content " >
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Work Type</label>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Type<span
                                        class="text-danger">*</span></label>
                                <select id="work_type" name="work_type" class="select3 form-select required-field">
                                    <option value="">Select Type</option>
                                    <option value="1" {{$staffData->exp_type == '1' ? 'selected' :''}}>Fresher</option>
                                    <option value="2" {{$staffData->exp_type == '2' ? 'selected' :''}}>Experience</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-4 mb-3 d-none shiftedCompanyField">
                                <label class="text-black fs-6 mb-1 fw-semibold">Shifted Company Count<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control required-field"
                                    placeholder="Enter Shifted Company Count" id="total_company_shift" name="total_company_shift" value="{{$staffData->total_company_shift ?? 0}}" maxlength ="2" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"/>
                                    <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-4 mb-3 d-none shiftedCompanyField">
                                <label class="text-black fs-6 mb-1 fw-semibold">Total Years Of Experience<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control required-field"
                                    placeholder="Enter Total Years Of Experience" id="total_experience" name="total_experience" value="{{$staffData->total_experience ?? 0}}" maxlength ="2" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                                    <div class="text-danger error_msg"></div>
                            </div>
                        </div>

                        <div id="work-exp-wrapper">
                            <div class="col-lg-12 mb-3">
                                <label class="fs-5 text-primary fw-bold">Previous Company Details</label>
                            </div>
                            <div class="work-exp-row bg-gray-200 rounded px-2 py-2 mb-2">
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-lg-11">
                                                <div class="row">
                                                    <input type="hidden" name="edit_exp_id[]">
                                                    <div class="col-lg-4 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">Company Name<span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control required-field"
                                                            name="company_name[]" placeholder="Enter Company Name" />
                                                            <div class="text-danger error_msg"></div>
                                                    </div>
                                                    <div class="col-lg-4 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">Position<span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control required-field"
                                                            name="position[]" placeholder="Enter Position" />
                                                            <div class="text-danger error_msg"></div>
                                                    </div>
                                                    <div class="col-lg-4 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">Experience
                                                            Years<span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control required-field"
                                                            name="exp_yrs[]" placeholder="Enter Experience Years" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                                                            <div class="text-danger error_msg"></div>
                                                    </div>
                                                    <div class="col-lg-4 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">Salary<span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control required-field"
                                                            name="salary[]" placeholder="Enter Salary" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                                                            <div class="text-danger error_msg"></div>
                                                    </div>
                                                    <div class="col-lg-4 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">Start Date<span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                                            <input type="text" id="work_st_date" name="work_st_date[]"
                                                                class="form-control common_datepicker required-field"
                                                                placeholder="Select Date" value="" readonly/>
                                                        </div>
                                                        <div class="text-danger error_msg"></div>
                                                    </div>
                                                    <div class="col-lg-4 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">End Date<span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                                            <input type="text" id="work_end_date"
                                                                name="work_end_date[]"
                                                                class="form-control common_datepicker required-field"
                                                                placeholder="Select Date" value="" readonly/>
                                                        
                                                        </div>
                                                         <div class="text-danger error_msg"></div>
                                                    </div>
                                                    <div class="col-lg-12 mb-3">
                                                        <label class="text-black mb-1 fs-6 fw-semibold">Exit Reason<span class="text-danger">*</span></label>
                                                        <textarea class="form-control required-field" rows="1" name="ExitReason[]" placeholder="Enter Exit Reason"></textarea>
                                                         <div class="text-danger error_msg"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-1 d-flex align-items-center mt-2 justify-content-center">
                                                <div class="d-flex align-items-end mb-2 justify-content-end">
                                                    <a href="javascript:;"
                                                        class="btn text-danger px-2 py-1 staff_work_del"
                                                        style="display: none !important;">
                                                        <i class="fa-solid fa-trash-can fs-4"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="work-exp-div mb-4">
                            <div class="d-flex align-items-center justify-content-end">
                                <button type="button" class="btn btn-primary" id="add-work-btn">
                                    <i class="mdi mdi-plus me-1"></i>Add More
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Attachment Details</label>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <div id="document-wrapper">
                            <div class="document-row bg-gray-200 rounded px-2 py-2 mb-2">
                                <div class="row">
                                    <div class="col-lg-11">
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-black mb-1 fs-6 fw-semibold">Document Type</label>
                                                <select id="doc_type_0" name="doc_type[]"
                                                    class="select3 form-select required-field">
                                                    <option value="">Select Document Type</option>
                                                    @if(isset($documentTypeList))
                                                    @foreach($documentTypeList as $doc)
                                                    <option value="{{$doc->sno}}">{{$doc->document_name}}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-black fs-6 fw-semibold mt-2">Attachment</label>
                                                <div class="dropzone needsclick dz-clickable" id="dropzone-multi_staff_0"
                                                    style="background-color: #fff; border: 1px dotted #c94545;">
                                                    <div class="dz-message needsclick fs-6 text-center text-black me-3">
                                                        Drop files here or click to upload
                                                    </div>

                                                    <!-- Horizontal scroll container for previews -->
                                                    <div class="file-previews"></div>

                                                    <div class="fallback">
                                                        <input type="file" name="attachment[]" multiple class="required-field" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 d-flex align-items-center mt-2 justify-content-center">
                                        <div class="d-flex align-items-end mb-2 justify-content-end">
                                            <a href="javascript:;" class="btn text-danger px-2 py-1 staff_doc_del"
                                                style="display: none !important;">
                                                <i class="fa-solid fa-trash-can fs-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-1 d-flex align-items-center justify-content-end">
                                <button type="button" class="btn btn-primary" id="add-doc-btn">
                                    <i class="mdi mdi-plus me-1"></i>Add More
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary " id="prev6" onclick="safePrev(6)">
                            <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <div class="d-flex gap-2">
                            <button  type="button" class="btn btn-primary" id="updateClose6" onclick="close_validation_func(6)" disabled>
                                <span id="updateBtnText6">Update & Close</span>
                                <span id="updateBtnLoader6" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-primary " id="updateNxt6" onclick="next_validation_func(6)" disabled>
                                <span id="updateNxtText6" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                                <span id="updateNxtLoader6" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                            <button  type="button" class="btn btn-primary" id="stage6" onclick="validation_func(6)">
                                <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="companydetails" class="content ">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Company Details</label>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <div class="form-check form-check-inline mb-3">
                            <label class="form-check-label" for="management">
                                <input class="form-check-input required-field" type="radio" name="company"
                                    id="management" value="1"  {{$staffData->company_type== '2' ? '':'checked'}} />
                                Management
                            </label>
                        </div>
                        <div class="form-check form-check-inline mb-3">
                            <label class="form-check-label" for="business">
                                <input class="form-check-input required-field" type="radio" name="company"
                                    id="business" value="2" {{$staffData->company_type== '2' ? 'checked':''}}/>
                                Business
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 mb-3 business_div ">
                            <label class="text-black mb-1 fs-6 fw-semibold">Company Name<span
                                    class="text-danger">*</span></label>
                            <select id="staff_company_name" name="staff_company_name" class="select3 form-select required-field">
                                <option value="">Select Company Name</option>
                                    @if(isset($company_list))
                                    @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}" {{$staffData->company_id== $clist->sno ? 'selected':''}}>{{$clist->company_name}}</option>
                                    @endforeach
                                    @endif
                            </select>
                            <div class="text-danger error_msg"></div>
                        </div>
                        <div class="col-lg-4 mb-3 business_div">
                            <label class="text-black mb-1 fs-6 fw-semibold">Entity Name<span
                                    class="text-danger">*</span></label>
                            <select id="entity_name" name="entity_name" class="select3 form-select required-field">
                                <option value="">Select Entity Name</option>
                            </select>
                            <div class="text-danger error_msg"></div>
                        </div>
                         <input type="hidden" name="erp_branch_id" id="erp_branch_id">
                         <input type="hidden" name="erp_department_id" id="erp_department_id">
                         <input type="hidden" name="erp_division_id" id="erp_division_id">
                         <input type="hidden" name="erp_job_role_id" id="erp_job_role_id">
                         <input type="hidden" name="erp_role_id" id="erp_role_id">
                         <input type="hidden" name="erp_under_role_id" id="erp_under_role_id">
                        <div class="col-lg-4 mb-3 business_div">
                            <label class="text-black mb-1 fs-6 fw-semibold">Branch Name<span
                                    class="text-danger">*</span></label>
                            <select id="branch_id" name="branch_id" class="select3 form-select required-field">
                                <option value="">Select Branch</option>
                            </select>
                            <div class="text-danger error_msg"></div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-black mb-1 fs-6 fw-semibold">Department<span
                                    class="text-danger">*</span></label>
                            <div class="management_div err-chk">
                                <select name="management_depart" id="management_depart" class="select3 form-select required-field">
                                    <option value="">Select Department</option>
                                    @if(isset($management_department))
                                    @foreach($management_department as $depart)
                                    <option value="{{$depart->sno}}" {{$staffData->department_id== $depart->sno ? 'selected':''}}>{{$depart->department_name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="business_div err-chk">
                                <select name="business_depart" id="business_depart" class="select3 form-select required-field">
                                    <option value="">Select Department</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label class="text-black mb-1 fs-6 fw-semibold">Division<span
                                    class="text-danger">*</span></label>
                            <div class="management_div err-chk">
                                <select name="management_division" id="management_division" class="select3 form-select required-field">
                                    <option value="">Select Division</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="business_div err-chk">
                                <select name="business_division" id="business_division" class="select3 form-select required-field">
                                    <option value="">Select Division</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-black mb-1 fs-6 fw-semibold">Job Role<span
                                    class="text-danger">*</span></label>
                            <div class="management_div err-chk">
                                <select name="management_job_role" id="management_job_role" class="select3 form-select required-field">
                                    <option value="">Select Job Role</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="business_div err-chk">
                                <select name="business_job_role" id="business_job_role" class="select3 form-select required-field">
                                    <option value="">Select Job Role</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Designation Details</label>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Employee ID<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control required-field" id="employee_id" value="{{$staffData->staff_id ?? ''}}" name="employee_id" placeholder="Enter Employee Id" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');" onkeyup="employee_id_chk(this.value)"/>
                                    <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Pseudo Name<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control required-field" id="pseudo_name"
                                    name="pseudo_name" placeholder="Enter Pseudo Name" value="{{$staffData->nick_name ?? ''}}"  oninput="formatName(this)"/>
                                    <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Date of Joining<span
                                        class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i
                                            class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                    <input type="text" id="staff_doj" name="doj" autocomplete="off"
                                        placeholder="Select Date" class="form-control common_datepicker required-field" value="{{$staffData->date_of_joining ?? ''}}" readonly />
                                </div>
                                <div class="text-danger error_msg"></div>
                            </div>
                            
                            
                            @php
                            $skillTag = !empty($staffData->knowledge_tag) 
                                ? json_decode($staffData->knowledge_tag, true) 
                                : [];
                            @endphp
                            <div class="col-lg-8 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Skill Tag<span
                                        class="text-danger">*</span></label>
                                <div class="form-floating form-floating-outline">
                                    <input id="skill_tag" name="skill_tag"
                                        class="form-control h-auto skill_tag required-field"
                                        placeholder="Select Skill Tags" value="{{ !empty($skillTag) ? $skillTag :'' }}" >
                                        <div class="text-danger error_msg"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="fw-semibold mb-1 text-black">
                                    <input class="form-check-input me-2" type="checkbox" id="login_access" value="1" {{$staffData->login_access == 1 ? 'checked' :''}}
                                        name="login_access" />Login Access
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mb-3 login_access">
                        <label class="fs-5 text-primary fw-bold">Credentials</label>
                    </div>
                    <div class="col-lg-12 mb-3 login_access">
                        <div class="row">
                            <div class="col-lg-4 err-chk">
                                <label class="fw-semibold mb-1 text-black">
                                    <span id="user_name_label">Login Credentials</span><span
                                        class="text-danger login_fields">*</span>
                                </label>
                                <input type="text" class="form-control login_fields required-field"
                                    id="loginuser_name" name="loginuser_name" placeholder="Enter User Name"
                                    value="{{$staffData->user_name ??''}}"  onkeyup="user_name_chk(this.value)"/>
                                     <div class="text-danger error_msg" id="loginuser_name_err"></div>
                            </div>
                            <div class="col-lg-4 mb-3 login_fields err-chk">
                                <label class="text-black mb-1 fs-6 fw-semibold">Password<span
                                        class="text-danger">*</span></label>
                                <div class="form-password-toggle">
                                    <div class="input-group input-group-merge">
                                        <input type="password" class="form-control required-field" id="loginpassword"
                                            name="loginpassword" placeholder="Enter Password" value="{{$staffData->password ??''}}"
                                             />
                                        <span class="input-group-text cursor-pointer"><i
                                                class="mdi mdi-eye-off-outline fs-4"></i></span>
                                    </div>
                                     <div class="text-danger error_msg"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">User Role<span
                                        class="text-danger">*</span></label>
                                <div class="management_div err-chk">
                                    <select name="management_user_role" id="management_user_role" class="select3 form-select required-field">
                                        <option value="">Select User Role</option>
                                        @if(isset($management_user_role))
                                        @foreach($management_user_role as $role)
                                        <option value="{{$role->sno}}" {{$staffData->role_id== $role->sno ? 'selected':''}}>{{$role->role_name}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <div class="text-danger error_msg"></div>
                                </div>
                                <div class="business_div err-chk">
                                    <select name="business_user_role" id="business_user_role" class="select3 form-select required-field">
                                        <option value="">Select User Role</option>
                                    </select>
                                    <div class="text-danger error_msg"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="fw-semibold mb-1 text-black">
                                    <input class="form-check-input me-2" type="checkbox" id="other_access" value="1" {{$staffData->credential == 1 ? 'checked' :''}}
                                        name="other_access" />Other Credentials
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary " id="prev7" onclick="safePrev(7)"> <i
                            class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    <div class="d-flex gap-2">
                        <button  type="button" class="btn btn-primary" id="updateClose7" onclick="close_validation_func(7)" disabled>
                            <span id="updateBtnText7">Update & Close</span>
                            <span id="updateBtnLoader7" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-primary " id="updateNxt7" onclick="next_validation_func(7)" disabled>
                            <span id="updateNxtText7" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                            <span id="updateNxtLoader7" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button  type="button" class="btn btn-primary" id="stage7" onclick="validation_func(7)">
                            <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                        </button>
                    </div>
                    
                </div>
            </div>
             <div id="salarydetails" class="content ">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Salary Details</label>
                    </div>

                        <!-- Payroll Configuration -->
                    <div class="col-lg-12 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-label-primary">
                                <h5 class="mb-0 text-primary">Payroll Configuration</h5>
                            </div>
                            <input type="hidden" id="single_salary_account_sno" name="single_salary_account_sno">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Account Type -->
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold"> Account Type</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="is_multiple_account" value="0" checked onclick="accountTypeChange()">
                                                <label class="form-check-label">
                                                    Single
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="is_multiple_account" value="1" onclick="accountTypeChange()" disabled>
                                                <label class="form-check-label">
                                                    Multiple
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Salary Type -->
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Salary Type</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="salary_type" id="salary_cash" value="1"  {{$staffData->salary_type != 2 ? 'checked' : ''}}>
                                                <label class="form-check-label">
                                                    Cash
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="salary_type" id="salary_bank"  value="2" {{$staffData->salary_type == 2 ? 'checked' : ''}}>
                                                <label class="form-check-label">
                                                    Bank
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Salary Date -->
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Salary Date<span class="text-danger">*</span></label>
                                        <select name="salary_date" id="salary_date" class="form-select select3">
                                            <option value="" >Select</option>
                                            <option value="1" {{$staffData->salary_date == 1 ? 'selected' : ''}}>5th</option>
                                            <option value="2" {{$staffData->salary_date == 2 ? 'selected' : ''}}>15th</option>
                                        </select>
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <!-- Casual Leave -->
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Casual Leave</label>
                                        <input type="text" class="form-control" id="casual_leave_count" name="casual_leave_count" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" value="{{$staffData->casual_leave_count_per_month ?? 0}}" placeholder="Enter Casual Leave Count">
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <!-- Payslip -->
                                    <div class="col-lg-4 mt-3">
                                        <div class="mt-4">
                                            <label class="text-black mb-1 fs-6 fw-semibold">Payslip Available</label> <input class="form-check-input" type="checkbox" id="is_payslip" name="is_payslip" value="1" {{$staffData->is_payslip == 1 ? 'checked' : ''}}> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- SINGLE ACCOUNT PAYROLL -->
                    
                    <div id="singleAccountDiv" class="col-lg-12 mb-3">
                        <div class="card shadow-sm border-0 ">
                            <div class="card-header bg-label-primary">
                                <h5 class="mb-0 text-primary">
                                    Single Account Payroll
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Monthly Basic Salary (₹)<span class="text-danger">*</span></label></label>
                                        <input type="text" id="basic_salary" class="form-control mb-2" name="basic_salary" placeholder="Enter Basic Salary" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'),per_day_salary_value();" value="{{$staffData->basic_salary ?? 0}}">
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                            <label class="text-black mb-1 fs-6 fw-semibold">Per Day Salary<span class="text-danger">*</span></label></label>
                                        <input type="text" id="per_day_salary" class="form-control mb-2" name="per_day_salary" placeholder="Enter Per Day Cost" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" readonly>
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Per Hour Cost<span class="text-danger">*</span></label>
                                        <input type="text" id="per_hr_cost" class="form-control mb-2" name="per_hr_cost" placeholder="Enter Per Hour Cost" value="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" readonly>
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Salary Company<span class="text-danger">*</span></label>
                                        <select id="salary_company_id" name="salary_company_id"  class="form-select select3" onchange="salary_company_change()">
                                            <option value="">Select Company</option>
                                            @foreach($company_list as $company)
                                            <option value="{{ $company->sno }}" {{$staffData->salary_company_id ==  $company->sno  ? 'selected' : ''}}>
                                                {{ $company->company_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Salary Bank</label>
                                        <select id="salary_bank_id" name="salary_bank_id" class="form-select select3">
                                            <option value=""> Select Bank</option>
                                        </select>
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label text-black mb-1 fs-6 fw-semibold"> Salary Template</label>
                                        <select class="form-select select3" id="payroll_template_sno" name="payroll_template_sno" onchange="changePayrollTemplate()">
                                            <option value="">Select Template</option>
                                            @if(isset($salaryTemplates))
                                                @foreach($salaryTemplates as $template)
                                                    <option value="{{ $template->sno }}"  {{ $existingStructure ? ($existingStructure->payroll_template_sno ==  $template->sno  ? 'selected' :($template->sno == '2' ?'selected' : '')):($template->sno == '2' ?'selected' : '')}}>{{ $template->template_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="text-danger error_msg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MULTIPLE ACCOUNT PAYROLL -->
                    
                    <div id="multipleAccountDiv" class="d-none col-lg-12 mb-3">
                        <div class="card shadow-sm border-0 ">
                            <div class="card-header d-flex justify-content-between align-items-center bg-label-primary">
                                <h5 class="mb-0 text-primary">
                                    Multiple Salary Accounts
                                </h5>
                                <button type="button"  class="btn btn-primary btn-sm" onclick="addSalaryAccountRow()">Add Account</button>
                            </div>
                            <div class="card-body p-0 mt-2">
                                <div id="salaryAccountBody">
                                    
                                </div>
                                <div class="">
                                    <div class="card-header m-0 pt-3 px-5 pb-0">
                                        <h5 class="mb-0 text-primary">
                                            Salary Summary
                                        </h5>
                                    </div>
                                    <div class="card-body py-4">
                                        <div class="row text-center">
                                            <div class="col">
                                                <div class="fs-6  text-black">Total Salary</div>
                                                <div class="fs-5 fw-bold text-black"  id="total_gross_salary">₹0</div>
                                            </div>
                                            <div class="col">
                                                <div class="fs-6  text-black">Per Day</div>
                                                <div class="fs-5 fw-bold text-black"  id="total_per_day">₹0</div>
                                            </div>
                                            <div class="col">
                                                <div class="fs-6  text-black">Per Hour</div>
                                                <div class="fs-5 fw-bold text-black" id="total_per_hour">₹0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    

                    <!-- STATUTORY DETAILS -->
                    <div class="col-lg-12 mb-3">
                        <div class="card shadow-sm border-0  statutorySection">
                            <div class="card-header bg-label-primary">
                                <h5 class="mb-0 text-primary">
                                    Statutory & Staff Bank Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">UAN Number (PF)</label>
                                        <input type="text" name="uan_no" class="form-control" maxlength="12" oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Enter UAN Number" value="{{$staffStatutory ? ($staffStatutory->uan_no ?? '') : ''}}">
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">ESI Number </label>
                                        <input type="text" name="esi_no" class="form-control" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Enter ESI Number" value="{{$staffStatutory ? ($staffStatutory->esi_no ?? '') : ''}}">
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Staff Bank Name</label>
                                        <input  type="text" name="staff_bank_name" id="staff_bank_name" class="form-control" placeholder="Enter Staff Bank Name" value="{{$staffBank ? ($staffBank->bank_name ?? '') : ''}}" >
                                        <small class="text-danger error" data-error="staff_bank_name"></small>
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Staff Bank Branch</label>
                                        <input type="text" class="form-control required-field" id="staff_bank_branch" name="staff_bank_branch" placeholder="Enter Staff Bank Branch" value="{{$staffBank ? ($staffBank->bank_branch ?? '') : ''}}" />
                                        <small class="text-danger error" data-error="staff_bank_branch"></small>
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Account Holder</label>
                                        <input type="text" class="form-control" placeholder="Enter Account Holder" id="staff_acc_holder" name="staff_acc_holder" maxlength="50" value="{{$staffBank ? ($staffBank->account_holder ?? '') : ''}}" />
                                        <small class="text-danger error" data-error="staff_acc_holder"></small>
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">Account No</label>
                                        <input type="text" class="form-control" placeholder="Enter Account No" id="staff_acc_no" name="staff_acc_no" maxlength="20" value="{{$staffBank ? ($staffBank->bank_account_no ?? '') : ''}}" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"/>
                                        <small class="text-danger error" data-error="staff_acc_no"></small>
                                    </div>
                                    <div class="col-lg-3 mb-3">
                                        <label class="text-black mb-1 fs-6 fw-semibold">IFSC Code</label>
                                        <input type="text" class="form-control" placeholder="Enter IFSC Code" id="ifsc_code" name="ifsc_code" maxlength="20" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');" value="{{$staffBank ? ($staffBank->ifsc_code ?? '') : ''}}" />
                                        <small class="text-danger error" data-error="ifsc_code"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mb-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- SUMMARY -->
                                <div class="card mt-3 shadow-sm border-0">
                                    <div class="card-body">

                                        <h6 class="fw-bold text-primary">Summary</h6>

                                        <div class="d-flex justify-content-between">
                                            <span>Gross</span>
                                            <span id="emp_total_earnings">₹ 0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Deductions</span>
                                            <span id="emp_total_deductions">₹ 0.00</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Net Salary</span>
                                            <span id="emp_net_salary">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>CTC</span>
                                            <span id="emp_ctc">0</span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div id="employee_salary_components"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary " id="prev8" onclick="safePrev(8)">
                        <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    
                    <div class="d-flex gap-2">
                        <button  type="button" class="btn btn-primary" id="updateClose8" onclick="close_validation_func(8)" >
                            <span id="updateBtnText8">Update & Close</span>
                            <span id="updateBtnLoader8" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-primary " id="updateNxt8" onclick="next_validation_func(8)" >
                            <span id="updateNxtText8" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                            <span id="updateNxtLoader8" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button  type="button" class="btn btn-primary" id="stage8" onclick="validation_func(8)">
                            <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                        </button>
                    </div>
                </div>
                
            </div>
            <div id="application_add" class="content ">
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 text-primary fw-bold">Applied For </label>
                    </div>
                    @php
                      $appliedPositionIds = !empty($staffData->applied_position) ? json_decode($staffData->applied_position, true) : [];
                      $appliedCompanyIds = !empty($staffData->applied_company_ids) ? json_decode($staffData->applied_company_ids, true) : [];
                      $applicationDetails = json_decode($staffData->application_details, true) ?? [];
                    @endphp
                    <div class="col-lg-6 mb-3 err-chk">
                        <label class="text-black mb-1 fs-6 fw-semibold">Select Position<span
                                class="text-danger">*</span></label>
                       <select id="applied_position" name="applied_position[]" class="select3 form-select required-field" multiple  data-placeholder="Select Position">
                            @if(isset($jobPositionlist))
                                <optgroup label="Technical Position" class="text-primary fw-bold">
                                    @foreach($jobPositionlist as $position)
                                        @if($position->job_type == 1)
                                            <option value="{{ $position->sno }}" {{ in_array($position->sno, $appliedPositionIds) ? 'selected' : '' }}>{{ $position->job_position_name }}</option>
                                        @endif
                                    @endforeach
                                </optgroup>

                                <optgroup label="Non Technical Position" class="text-primary fw-bold">
                                    @foreach($jobPositionlist as $position)
                                        @if($position->job_type == 2)
                                            <option value="{{ $position->sno }}" {{ in_array($position->sno, $appliedPositionIds) ? 'selected' : '' }}>{{ $position->job_position_name }}</option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <div class="text-danger error_msg"></div>
                    </div>

                    <div class="col-lg-6 mb-3 err-chk">
                        <label class="text-black mb-1 fs-6 fw-semibold">Staff Source<span
                                class="text-danger">*</span></label>
                        <select id="source_id" name="source_id" class="select3 form-select required-field">
                            <option value="">Select Source</option>
                                @foreach($source_list as $source)
                                        <option value="{{ $source->sno }}" data-detail="{{$source->detail_check ?? 0}}" {{ $source->sno == $staffData->source_id ? 'selected' : '' }}>{{ $source->source_name }}</option>
                             
                                @endforeach
                        </select>
                         <div class="text-danger error_msg"></div>
                    </div>

                    <div class="col-lg-6 mb-3 d-none err-chk" id="knowDetails">
                        <label class="text-black mb-1 fs-6 fw-semibold">Details</label>
                        <textarea class="form-control required-field" name="source_details" id="source_details" rows="1" placeholder="Enter Details">{{$staffData->source_details ?? ''}}</textarea>
                        <div class="text-danger error_msg"></div>
                    </div>

                    <div class="col-lg-6 mb-3 err-chk">
                        <label class="text-black mb-1 fs-6 fw-semibold">Which Elysium Group Of Company Are You Attending
                            ?<span class="text-danger">*</span></label>
                        <select id="interview_company" name="interview_company[]" class="select3 form-select required-field"
                            multiple data-placeholder="Select Company">
                            <option value="">Select Attending Company</option>
                             @if(isset($company_list))
                            @foreach($company_list as $clist)
                            <option value="{{$clist->sno}}" {{ in_array($clist->sno, $appliedCompanyIds) ? 'selected' : '' }}>{{$clist->company_name}}</option>
                            @endforeach
                            @endif
                        </select>
                        <div class="text-danger error_msg"></div>
                    </div>
                    <!-- dynamic  Questions -->
            
                 {{-- Dynamic Questions --}}
                    @foreach($questions as $question)
                        {{-- Parent Question --}}
                        <div class="col-lg-6 mb-3 dynamic-question" id="question_{{ $question->sno }}">
                            <label class="text-black mb-1 fs-6 fw-semibold">
                                {{ $question->field_name }}
                                @if($question->is_mandatory)<span class="text-danger">*</span>@endif
                            </label>
                            @php
                                $savedAnswer = $applicationDetails['questions'][$question->sno] ?? null;
                            @endphp

                            @php $options = json_decode($question->field_option, true) ?? []; @endphp

                            {{-- ✅ Handle all field types --}}
                            @switch($question->field_value)

                                @case('text_field')
                                    <input type="text" 
                                        class="form-control hrq-field"
                                        name="q_{{ $question->sno }}"
                                        data-question="{{ $question->sno }}"
                                        placeholder="Enter {{ $question->field_name }}" value="{{$savedAnswer ?? ''}}">
                                    @break

                                @case('text_area')
                                    <textarea class="form-control hrq-field"
                                            name="q_{{ $question->sno }}"
                                            data-question="{{ $question->sno }}"
                                            rows="3"
                                            placeholder="Enter {{ $question->field_name }}">{{$savedAnswer ?? ''}}</textarea>
                                    @break

                                @case('date_field')
                                    <input type="date" 
                                        class="form-control hrq-field common_date_class"
                                        name="q_{{ $question->sno }}"
                                        data-question="{{ $question->sno }}" value="{{$savedAnswer ?? ''}}">
                                    @break

                                @case('multiple_images')
                                    <input type="file"
                                        class="form-control hrq-field"
                                        name="q_{{ $question->sno }}[]"
                                        data-question="{{ $question->sno }}"
                                        accept="image/*"
                                        multiple>
                                    @break

                                @case('check_box')
                                    <div class="d-block">
                                        @foreach($options as $opt)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input hrq-field"
                                                    type="checkbox"
                                                    name="q_{{ $question->sno }}[]"
                                                    value="{{ $opt['label'] }}"
                                                    data-question="{{ $question->sno }}" {{ $savedAnswer === $opt['label'] ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $opt['label'] }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @break

                                @case('radio_button')
                                    <div class="d-block">
                                        @foreach($options as $index => $opt)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input hrq-field"
                                                    type="radio"
                                                    name="q_{{ $question->sno }}"
                                                    value="{{ $opt['label'] }}"
                                                    data-question="{{ $question->sno }}"
                                                    {{ $savedAnswer === $opt['label'] ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $opt['label'] }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @break

                                @case('list_box')
                                    <select class="form-select select3 hrq-field" 
                                            name="q_{{ $question->sno }}"
                                            data-question="{{ $question->sno }}">
                                        <option value="">Select {{$question->field_name}}</option>
                                        @foreach($options as $opt)
                                            <option value="{{ $opt['label'] }}" {{ $savedAnswer === $opt['label'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @break

                            @endswitch
                        </div>

                        {{-- Dependent Questions --}}
                        @foreach($question->depends as $depend)
                            @php $dep_options = json_decode($depend->field_option, true) ?? []; @endphp
                            @php
                                // Get the saved answer for the dependent question
                                $savedDependentAnswer = $applicationDetails['dependents'][$depend->sno] ?? null;
                            @endphp
                            <div class="col-lg-6 mb-3 dependent-question d-none"
                                id="depend_{{ $depend->sno }}"
                                data-parent="{{ $question->sno }}"
                                data-trigger='{{ $depend->depends_on }}'>

                                <label class="text-black mb-1 fs-6 fw-semibold">
                                    {{ $depend->field_name }}
                                    @if($depend->is_mandatory)<span class="text-danger">*</span>@endif
                                </label>

                                {{-- ✅ Same logic for dependents --}}
                                @switch($depend->field_value)

                                    @case('text_field')
                                        <input type="text" name="depend_{{ $depend->sno }}" class="form-control" placeholder="Enter {{ $depend->field_name }}" value="{{$savedDependentAnswer ?? ''}}">
                                        @break

                                    @case('text_area')
                                        <textarea class="form-control" name="depend_{{ $depend->sno }}" rows="3" placeholder="Enter {{ $depend->field_name }}">{{$savedDependentAnswer ?? ''}}</textarea>
                                        @break

                                    @case('date_field')
                                        <input type="text" name="depend_{{ $depend->sno }}" class="form-control common_date_class" value="{{$savedDependentAnswer ?? ''}}">
                                        @break

                                    @case('multiple_images')
                                        <input type="file" class="form-control" name="depend_{{ $depend->sno }}[]" multiple accept="image/*">
                                        @break

                                    @case('check_box')
                                        <div class="d-block">
                                            @foreach($dep_options as $opt)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" value="{{ $opt['label'] }}" {{ $savedDependentAnswer === $opt['label'] ? 'checked' : '' }} name="depend_{{ $depend->sno }}[]">
                                                    <label class="form-check-label">{{ $opt['label'] }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('radio_button')
                                        <div class="d-block">
                                            @foreach($dep_options as $index => $opt)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" 
                                                        name="depend_{{ $depend->sno }}" 
                                                        value="{{ $opt['label'] }}" 
                                                        {{ $savedDependentAnswer === $opt['label'] ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ $opt['label'] }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('list_box')
                                        <select class="form-select select3" name="depend_{{ $depend->sno }}">
                                            <option value="">Select {{ $depend->field_name }}</option>
                                            @foreach($dep_options as $opt)
                                                <option value="{{ $opt['label'] }}" {{ $savedDependentAnswer === $opt['label'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                @endswitch
                            </div>
                        @endforeach
                    @endforeach

                </div>

                <div class="col-12 d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary " id="prev9" onclick="safePrev(9)">
                        <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    
                    <div class="d-flex gap-2">
                        <button  type="button" class="btn btn-primary" id="updateClose9" onclick="close_validation_func(9)" >
                            <span id="updateBtnText9">Update & Close</span>
                            <span id="updateBtnLoader9" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-primary " id="updateNxt9" onclick="next_validation_func(9)" >
                            <span id="updateNxtText9" class="align-middle d-sm-inline-block d-none me-sm-1" >Update & Next</span>
                            <span id="updateNxtLoader9" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                        <button  type="button" class="btn btn-primary" id="stage9" onclick="validation_func(9)">
                            <i class="mdi mdi-chevron-double-right" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Next"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div id="checklist_add" class="content ">
                <div class="row">
                    <div class="col-lg-12 d-flex align-items-center justify-content-end mb-5">
                        <div class="form-check mb-2">
                            <input class="form-check-input ignore-progress" type="checkbox" id="selectAll">
                            <label class="form-check-label fw-semibold" for="selectAll">Select All</label>
                        </div>
                    </div>
                    @php
                      $checklistIds = !empty($staffData->document_checklist) ? json_decode($staffData->document_checklist, true) : [];
                    @endphp
                    <div class="col-lg-12">
                        <div style="max-height: 300px; overflow-y: auto;">
                            @if(isset($documentCheckList))
                                @foreach($documentCheckList as $checklist)
                                    <div class="form-check mb-3">
                                        <input class="form-check-input checklist-item start-field" type="checkbox" {{ in_array($checklist->sno, $checklistIds) ? 'checked' : '' }} value="{{$checklist->sno}}" name="document_checked[]"
                                            id="check_{{$checklist->sno}}">
                                        <label class="form-check-label" for="check_{{$checklist->sno}}">{{$checklist->document_checklist}}</label>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary " id="prev10" onclick="safePrev(10)">
                            <i class="icon-base ri ri-arrow-left-line icon-sm scaleX-n1-rtl me-sm-1 me-0"></i>
                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <button type="button" class="btn btn-primary " id="stage10" onclick="validation_func(10)">
                            <span class="align-middle d-sm-inline-block d-none me-sm-1">Update Staff</span>
                            <i class="icon-base ri ri-arrow-right-line icon-sm"></i>
                        </button>
                        <input type="hidden" name="submit_popup" id="submit_popup" data-bs-toggle="modal"
                                data-bs-target="#kt_modal_confirmation_staff">
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>

    <div class="modal fade" id="kt_modal_confirmation_staff" tabindex="-1" aria-hidden="true" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-m">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <div class="swal2-icon swal2-danger swal2-icon-show" style="display: flex;">
                    <div class="swal2-icon-content">?</div>
                </div>
                <div class="swal2-html-container" id="swal2-html-container" style="display: block;">Are you sure you want
                    to
                    Update Staff ?
                    <div class="d-block fw-bold fs-5 py-2">
                        <label id="create_staff_label"></label>
                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center pt-8 pb-8">
                    <a href="javascript:;" id="submitStaffBtn" class="btn btn-primary me-3" onclick="submit_form()">
                        <span id="yesBtnText">Yes</span>
                        <span id="yesBtnLoader" style="display: none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </a>
                    <a href="#" class="btn btn-secondary" data-bs-dismiss="modal">No</a>
                </div>
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>

    <!--begin::Modal - Other Credentials-->
    <div class="modal fade" id="kt_modal_other_credentials" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded">
                <div class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                    <div class="text-center mt-4">
                        <h3 class="text-center text-black">Other Credentials</h3>
                    </div>
                    <div class="btn btn-sm btn-icon btn-active-color-primary rounded border border-black" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#000" xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="#000" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="#000" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <div class="row mt-2">
                        <div class="col-lg-12 mb-3">
                            @if(isset($credential_list) && count($credential_list) > 0)
                                @foreach($credential_list as $cred)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input cred_check" type="checkbox" id="cred_{{$cred->sno}}" data-cred="{{$cred->sno}}"
                                            @if($staffCredntial->contains('credential_id', $cred->sno)) checked @endif />
                                        <label class="text-black mb-1 fs-6 fw-semibold">
                                            {{$cred->credential_name}}
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        @if(isset($credential_list))
                            @foreach($credential_list as $cred)
                                <div class="mb-2" id="cred_field_{{$cred->sno}}" style="display:none;">
                                    <h5 class="title fw-bold mt-2">{{$cred->credential_name}} Credentials</h5>
                                    <div class="row mt-2">
                                        <div class="col-lg-3 mb-3">
                                            <label class="text-black mb-1 fs-6 fw-semibold">User Name<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control"
                                                name="credential[{{$cred->sno}}][username]"
                                                placeholder="Enter User Name"
                                                value="{{ optional($staffCredntial->where('credential_id', $cred->sno)->first())->user_name }}" />
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="text-black mb-1 fs-6 fw-semibold">Password<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control"
                                                name="credential[{{$cred->sno}}][password]"
                                                placeholder="Enter Password"
                                                value="{{ optional($staffCredntial->where('credential_id', $cred->sno)->first())->password }}" />
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="text-black mb-1 fs-6 fw-semibold">URL</label>
                                            <input type="text" class="form-control"
                                                name="credential[{{$cred->sno}}][url]"
                                                placeholder="Enter URL"
                                                value="{{ optional($staffCredntial->where('credential_id', $cred->sno)->first())->url_link }}" />
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="text-black mb-1 fs-6 fw-semibold">Description</label>
                                            <textarea class="form-control" rows="1"
                                                    name="credential[{{$cred->sno}}][description]"
                                                    placeholder="Enter Description">{{ optional($staffCredntial->where('credential_id', $cred->sno)->first())->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center pb-4">
                        <button type="button" class="btn btn-outline-danger me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="btn_give_access">Give Access</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--end::Modal - Other Credentials-->


    <!--begin::Modal - Confirmation Staff-->
    <div class="modal fade" id="kt_modal_confirmation_staff" tabindex="-1" aria-hidden="true" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-m">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <div class="swal2-icon swal2-success swal2-icon-show" style="display: flex;">
                    <div class="swal2-icon-content"><i class="mdi mdi-check fs-1"></i></div>
                </div>
                <div class="swal2-html-container mb-2" id="swal2-html-container" style="display: block;">Are you sure
                    you
                    want to
                    Create Staff ?
                    <div class="d-block fw-bold fs-5 py-2">
                        <label class="text-black">Mahesh</label>
                        <span class="ms-2 me-2">-</span>
                        <label class="text-black">EGCS-0001/24</label>
                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center pt-8 pb-8 mb-4">
                    <a href="{{ url('/hr_enroll/manage_staff') }}" class="btn btn-success me-3">Yes</a>
                    <a href="#" class="btn btn-outline-danger" data-bs-dismiss="modal">No</a>
                </div>
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal - Confirmation Staff-->



    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

<script> 
    $(document).ready(function() {
        // Toggle credential fields in modal
        $('.cred_check').on('change', function() {
            const credId = $(this).data('cred');
            if ($(this).is(':checked')) {
                $('#cred_field_' + credId).slideDown();
            } else {
                $('#cred_field_' + credId).slideUp();
            }
        });

        // Auto-open modal when checkbox is checked
        $('#other_access').on('change', function() {
            if ($(this).is(':checked')) {
                // Open modal when checkbox is checked
                $('#kt_modal_other_credentials').modal('show');
            } else {
                // Do nothing or optionally close modal if unchecked
                $('#kt_modal_other_credentials').modal('hide');
            }
        });

        // When modal is reopened, restore visibility for already checked creds
        $('#kt_modal_other_credentials').on('show.bs.modal', function() {
            $('.cred_check').each(function() {
                const credId = $(this).data('cred');
                if ($(this).is(':checked')) {
                    $('#cred_field_' + credId).show();
                }
            });
        });

        // Validate and save credential data on "Give Access" button click
        $('#btn_give_access').on('click', function() {
            let valid = true;
            // Clear previous error messages
            $('.text-danger').remove();

            // Validate visible credential fields
            $('.cred_check:checked').each(function() {
                const credId = $(this).data('cred');
                const username = $(`input[name="credential[${credId}][username]"]`).val().trim();
                const password = $(`input[name="credential[${credId}][password]"]`).val().trim();

                // Remove previous input errors
                $(`#cred_field_${credId}`).find('.is-invalid').removeClass('is-invalid');

                // Check if username or password is empty and show error below the input
                if (username === '') {
                    valid = false;
                    $(`#cred_field_${credId} input[name="credential[${credId}][username]"]`)
                        .addClass('is-invalid')
                        .after('<div class="text-danger">Username is required.</div>');
                }
                if (password === '') {
                    valid = false;
                    $(`#cred_field_${credId} input[name="credential[${credId}][password]"]`)
                        .addClass('is-invalid')
                        .after('<div class="text-danger">Password is required.</div>');
                }
            });

            // If validation failed, prevent form submission and return
            if (!valid) {
                return;
            }

            // Remove old hidden credentials before adding new ones
            $('#staff_form').find('input[name^="credential["], textarea[name^="credential["]').remove();

            // Append selected credential data as hidden inputs
            $('.cred_check:checked').each(function() {
                const credId = $(this).data('cred');
                const fields = ['username', 'password', 'url', 'description'];

                fields.forEach(field => {
                    const value = $(`input[name="credential[${credId}][${field}]"], textarea[name="credential[${credId}][${field}]"]`).val();
                    $('<input>').attr({
                            type: 'hidden',
                            name: `credential[${credId}][${field}]`,
                            value: value
                        }).appendTo('#staff_form');
                    });
            });

                // Close modal after save
                $('#kt_modal_other_credentials').modal('hide');
        });
    });
</script>


<script>
    let existingChildren = @json($children);
    let existingSibling = @json($sibling);
    
   
    let existingContacts = {
        names: @json($contact_person_name),
        relations: @json($contact_person_relation),
        numbers: @json($contact_person_no)
    };
    let social_media_details = @json($staffData->social_media_details);
    let oldEducation = @json($oldEdu);
    let oldWorkInfos = @json($staffWork ?? []);
</script>

<script>
    $(document).ready(function() {
        if (social_media_details) {
            const socialMediaDetails = JSON.parse(social_media_details);
            Object.keys(socialMediaDetails).forEach(key => {
                const checkbox = document.getElementById(`checkSocialMedia_${key}`);
                if (checkbox) {
                    checkbox.checked = true;  // Check the checkbox if URL exists
                    const inputField = document.querySelector(`#socialMediaField_${key} input`);
                    if (inputField) {
                        inputField.value = socialMediaDetails[key]; // Set the URL in the input field
                    }
                    const target = document.querySelector(`#socialMediaField_${key}`);
                    if (checkbox.checked) {
                        target.classList.remove("d-none"); // Show the target if checked
                    } else {
                        target.classList.add("d-none"); // Hide the target if unchecked
                    }
                }
            });
        } 
    });
</script>

    <script>
        var pendingtasks = document.querySelector('#pending-tasks');
        // var completedtasks = document.querySelector('#completed-tasks');
        new Sortable(pendingtasks, {
            animation: 150,
            group: 'tasklist'
        });

        // new Sortable(completedtasks, {
        //     animation: 150,
        //     group: 'tasklist'
        // });
    </script>

    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.checklist-item').forEach(item => {
                item.checked = checked;
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Run once on load
            toggleDivs();

            // Change event on radio buttons
            $("input[name='company']").on("change", function() {
                toggleDivs();
            });

            function toggleDivs() {
                if ($("#management").is(":checked")) {
                    $(".management_div").show();
                    $(".business_div").hide();
                } else if ($("#business").is(":checked")) {
                    $(".business_div").show();
                    $(".management_div").hide();
                }
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            let eduIndex = 1; // for unique IDs

            // Initialize first select2
            $(".select3").select2();

            $('input[name="pass_year[]"]').datepicker({
                format: "yyyy",
                viewMode: "years",
                minViewMode: "years",
                autoclose: true
            });

            // old data fetch
            // if (oldEducation.length > 0) {

            //     // Fill first row
            //     let first = oldEducation[0];
            //     $(".education-row:first").find('select[name="qualification_type[]"]').val(first.qualification_type).trigger("change");
            //     $(".education-row:first").find('input[name="degree[]"]').val(first.degree);
            //     $(".education-row:first").find('input[name="major[]"]').val(first.major);
            //     $(".education-row:first").find('input[name="univ_name[]"]').val(first.university_name);
            //     $(".education-row:first").find('input[name="pass_year[]"]').val(first.year);

            //     // Create remaining rows
            //     for (let i = 1; i < oldEducation.length; i++) {

            //         let clone = $(".education-row:first").clone(false, false);

            //         let row = oldEducation[i];

            //         clone.find(".select2").remove();

            //         clone.find('select[name="qualification_type[]"]').val(row.qualification_type);
            //         clone.find('input[name="degree[]"]').val(row.degree);
            //         clone.find('input[name="major[]"]').val(row.major);
            //         clone.find('input[name="univ_name[]"]').val(row.university_name);
            //         clone.find('input[name="pass_year[]"]').val(row.year);

            //         clone.find("select.select3").select2();
            //         clone.find(".staff_edu_del").show();

            //         $("#education-wrapper").append(clone);
            //     }
            // }
            console.log("education : ",oldEducation)
            if (oldEducation.length > 0) {
                oldEducation.forEach(function(row, i) {

                    if (i > 0) {
                        addEducationRow();
                    }

                    let currentRow = $(".education-row").eq(i);

                    currentRow.find('select[name="qualification_type[]"]')
                        .val(row.qualification_type)
                        .trigger("change");

                    // Wait for AJAX major load
                    setTimeout(function () {
                        console.log(row.major)
                        currentRow.find('select[name="major[]"]')
                            .val(row.major)
                            .trigger("change");
                           
                    }, 100);

                    currentRow.find('input[name="univ_name[]"]').val(row.university_name);
                    currentRow.find('input[name="pass_year[]"]').val(row.year);
                });
            }

            // Add new education row
            // $("#add-edu-btn").click(function () {
            //     let clone = $(".education-row:first").clone(false, false);

            //     // Clear input values
            //     clone.find("input").val("");
            //     clone.find("select").val("").trigger("change");

            //     // Remove select2 container
            //     clone.find(".select2").remove();
            //     let newSelect = clone.find("select.select3");

            //     // Assign unique ID
            //     newSelect.attr("id", "qualification_type_" + eduIndex);

            //     // Update input IDs to be unique
            //     clone.find('input[name="degree[]"]').attr("id", "degree_" + eduIndex);
            //     clone.find('input[name="major[]"]').attr("id", "major_" + eduIndex);
            //     clone.find('input[name="univ_name[]"]').attr("id", "univ_name_" + eduIndex);
            //     clone.find('input[name="pass_year[]"]').attr("id", "pass_year_" + eduIndex);

            //     // Add error message placeholders (if not present)
            //     clone.find(".error_msg").remove();
            //     clone.find(".form-control, .form-select").each(function () {
            //         $(this).after('<div class="text-danger error_msg"></div>');
            //     });

            //     // Re-init select2
            //     newSelect.select2();

            //     // Show delete button
            //     clone.find(".staff_edu_del").show();

            //     // Append to wrapper
            //     $("#education-wrapper").append(clone);

            //     eduIndex++;
            // });

            // Delete Education Row
            // $(document).on("click", ".staff_edu_del", function () {
            //     $(this).closest(".education-row").remove();
            // });

             $("#add-edu-btn").click(function () {
                addEducationRow();
            });

            function addEducationRow() {

                let clone = $(".education-row:first").clone(false, false);

                clone.find("input").val("");
                clone.find("select").val("");

                clone.find(".select2-container").remove();

                clone.find("select.select3").select2({
                    width: '100%'
                });

                clone.find('input[name="pass_year[]"]').datepicker({
                    format: "yyyy",
                    viewMode: "years",
                    minViewMode: "years",
                    autoclose: true
                });

                clone.find(".staff_edu_del").show();

                $("#education-wrapper").append(clone);

                eduIndex++;
            }

            // ================= DELETE =================
            $(document).on("click", ".staff_edu_del", function () {
                $(this).closest(".education-row").remove();
            });

            
        });
    </script>



    <script>
        function loadOldWorkInfo() {

            if (!oldWorkInfos || oldWorkInfos.length === 0) {
                // No previous experience, keep form default
                return;
            }

            // First remove all rows except first template
            $("#work-exp-wrapper .work-exp-row:not(:first)").remove();

            // First row
            const first = oldWorkInfos[0];

            let firstRow = $("#work-exp-wrapper .work-exp-row:first");

            // Fill first row
            firstRow.find("input[name='edit_exp_id[]']").val(first.sno);
            firstRow.find("input[name='company_name[]']").val(first.company_name);
            firstRow.find("input[name='position[]']").val(first.position);
            firstRow.find("input[name='exp_yrs[]']").val(first.year_of_experience);
            firstRow.find("input[name='salary[]']").val(first.salary);
            firstRow.find("input[name='work_st_date[]']").val(first.start_date);
            firstRow.find("input[name='work_end_date[]']").val(first.end_date);
            firstRow.find("textarea[name='ExitReason[]']").val(first.exit_reason);

            // Make delete hidden for first row
            firstRow.find(".staff_work_del").hide();

            // Additional rows
            for (let i = 1; i < oldWorkInfos.length; i++) {

                let data = oldWorkInfos[i];
                let clone = $(".work-exp-row:first").clone(false, false);

                clone.find("input[name='edit_exp_id[]']").val(data.sno);
                clone.find("input[name='company_name[]']").val(data.company_name);
                clone.find("input[name='position[]']").val(data.position);
                clone.find("input[name='exp_yrs[]']").val(data.year_of_experience);
                clone.find("input[name='salary[]']").val(data.salary);
                clone.find("input[name='work_st_date[]']").val(data.start_date);
                clone.find("input[name='work_end_date[]']").val(data.end_date);
                clone.find("textarea[name='ExitReason[]']").val(data.exit_reason);

                clone.find(".staff_work_del").show();

                $("#work-exp-wrapper").append(clone);

                clone.find(".common_datepicker").datepicker({
                    format: "yyyy-mm-dd",
                    autoclose: true,
                    todayHighlight: true
                });
            }
        }
        $(document).ready(function() {
            // Hide wrapper by default
            $("#work-exp-wrapper").hide();
            $("#add-work-btn").hide();
            $(".work-exp-div").hide();
            
            work_type_change()
            function work_type_change(){
                 if ($('#work_type').val() === "2") {
                    // Experience selected
                    $("#work-exp-wrapper").show();
                    $("#add-work-btn").show();
                    $(".work-exp-div").show();
                    loadOldWorkInfo();
                } else {
                    // Fresher selected
                    $("#work-exp-wrapper").hide();
                    $("#add-work-btn").hide();
                    $(".work-exp-div").hide();

                    // Reset rows to only the first one (clean form)
                    $("#work-exp-wrapper .work-exp-row:not(:first)").remove();
                    $("#work-exp-wrapper")
                        .find("input")
                        .val(""); // clear inputs
                    $("#work-exp-wrapper .staff_work_del").hide(); // hide delete button in first row
                }

                if ($('#work_type').val() === '2') {
                    $('.shiftedCompanyField').removeClass('d-none');
                } else {
                    $('.shiftedCompanyField').addClass('d-none');
                }
            }
            // Handle type change
            $("#work_type").change(function() {
                if ($(this).val() === "2") {
                    // Experience selected
                    $("#work-exp-wrapper").show();
                    $("#add-work-btn").show();
                    $(".work-exp-div").show();
                    loadOldWorkInfo();
                } else {
                    // Fresher selected
                    $("#work-exp-wrapper").hide();
                    $("#add-work-btn").hide();
                    $(".work-exp-div").hide();

                    // Reset rows to only the first one (clean form)
                    $("#work-exp-wrapper .work-exp-row:not(:first)").remove();
                    $("#work-exp-wrapper")
                        .find("input")
                        .val(""); // clear inputs
                    $("#work-exp-wrapper .staff_work_del").hide(); // hide delete button in first row
                }
            });
            

            // Add More Work Experience
            $("#add-work-btn").click(function() {
                let clone = $(".work-exp-row:first").clone(false, false);

                // clear values
                clone.find("input").val("");
                clone.find("textarea").val("");

                // show delete button
                clone.find(".staff_work_del").show();

                // append
                $("#work-exp-wrapper").append(clone);

                // re-init datepicker if needed
                clone.find(".common_datepicker").datepicker({
                    format: "yyyy-mm-dd",
                    autoclose: true,
                    todayHighlight: true
                });
            });

            // Delete Work Row
            $(document).on("click", ".staff_work_del", function() {
                $(this).closest(".work-exp-row").remove();
            });

            // Initialize first datepickers
            $(".common_datepicker").datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>

  

    <script>
        $(document).ready(function() {
            // Initialize Dropzone + Swiper combo
            function initDropzone(selector, dropzoneElement, index) {
                const previewContainer = dropzoneElement.find(".file-previews");

                // Create a hidden input to store uploaded filenames
                const hiddenInput = $('<input>', {
                    type: 'hidden',
                    name: `uploaded_files[${index}]` // Ensure unique name for each row
                });
                dropzoneElement.append(hiddenInput);

                new Dropzone(selector, {
                    url: "/upload-temp-documentstaff",
                    method: "POST",
                    paramName: "file",
                    autoProcessQueue: true,
                    previewsContainer: previewContainer[0],
                    addRemoveLinks: true,
                    acceptedFiles: null, // accept all file types
                    maxFilesize: 50, // 20 MB
                    previewTemplate: `
                        <div class="dz-preview dz-file-preview">
                            <div class="dz-image">
                                <img data-dz-thumbnail />
                                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                                <div class="dz-success-mark text-success"></div>
                                <div class="dz-error-mark text-danger"></div>
                            </div>
                            <div class="dz-details">
                                <div class="dz-filename"><span data-dz-name></span></div>
                                <div class="dz-size" data-dz-size></div>
                            </div>
                            <a class="dz-remove" href="javascript:void(0);" data-dz-remove>×</a>
                        </div>
                    `,
                    init: function () {
                        this.on("sending", function (file, xhr, formData) {
                            // CSRF token for Laravel
                            formData.append("_token", $('meta[name="csrf-token"]').attr("content"));
                        });

                        this.on("success", function (file, response) {
                            let res = response;
                            if (typeof response === 'string') {
                                try {
                                    res = JSON.parse(response);
                                } catch (err) {
                                    res = { status: false, message: response };
                                }
                            }

                            if (res && res.status) {
                                file.serverFileName = res.filename;
                                if (file.previewElement) {
                                    file.previewElement.classList.remove('dz-error');
                                    file.previewElement.classList.add('dz-success');
                                }

                                // Store filename in hidden input
                                const current = hiddenInput.val() ? JSON.parse(hiddenInput.val()) : [];
                                current.push(res.filename);
                                hiddenInput.val(JSON.stringify(current));

                                // console.log("✅ Uploaded successfully:", res.filename);
                            } else {
                                const msg = (res && res.message) ? res.message : 'Upload failed';
                                if (file.previewElement) {
                                    file.previewElement.classList.remove('dz-success');
                                    file.previewElement.classList.add('dz-error');
                                }
                                this.emit("error", file, msg);
                                console.error("Upload error:", msg);
                            }
                        });

                        this.on("error", function (file, errorMessage) {
                            if (file.previewElement) {
                                file.previewElement.classList.add("dz-error");
                            }
                            console.error("❌ Upload failed:", errorMessage);
                        });

                        this.on("removedfile", function (file) {
                            if (file.serverFileName) {
                                const current = hiddenInput.val() ? JSON.parse(hiddenInput.val()) : [];
                                const updated = current.filter(f => f !== file.serverFileName);
                                hiddenInput.val(JSON.stringify(updated));

                                // console.log("🗑️ Removing file:", file.serverFileName);

                                // AJAX call to delete from temp folder
                                $.ajax({
                                    url: "/delete-temp-documentstaff",
                                    type: "POST",
                                    data: {
                                        filename: file.serverFileName,
                                        _token: $('meta[name="csrf-token"]').attr("content")
                                    },
                                    success: function (res) {
                                        if (res.status) {
                                            // console.log("✅ Deleted from temp:", res.message);
                                        } else {
                                            console.warn("⚠️ Delete failed:", res.message);
                                        }
                                    },
                                    error: function (xhr) {
                                        console.error("❌ Server error deleting file:", xhr.responseText);
                                    }
                                });
                            }
                        });
                    },
                });
            }

            // Initialize first Dropzone
            initDropzone("#dropzone-multi_staff_0", $(".document-row:first .dropzone"), 0);

            // Add document row
            $("#add-doc-btn").click(function() {
                let cloneIndex = $("#document-wrapper .document-row").length;
                let firstRowSelect = $(".document-row:first select");
                firstRowSelect.select2('destroy');

                let clone = $(".document-row:first").clone(false, false);
                firstRowSelect.select2();

                clone.find("select").val("");
                clone.find(".select2-container").remove();
                clone.find(".dropzone .file-previews").empty();
                clone.find("input[type=file]").val("");
                clone.find(".staff_doc_del").show();

                // Update the ID and name attributes with the dynamic index
                clone.find("select").attr("id", `doc_type_${cloneIndex}`).attr("name", `doc_type[${cloneIndex}]`);
                clone.find(".dropzone").attr("id", `dropzone-multi_staff_${cloneIndex}`);

                $("#document-wrapper").append(clone);

                // Reinitialize select2 and Dropzone for the new row
                clone.find("select").select2();
                initDropzone(`#dropzone-multi_staff_${cloneIndex}`, clone.find(".dropzone"), cloneIndex);
            });

            // Delete row
            $(document).on("click", ".staff_doc_del", function() {
                $(this).closest(".document-row").remove();
            });

            // Initialize select2 for the first row
            $("#doc_type_0").select2();
        });

    </script>


    <script>
        $(document).ready(function() {
            let courseIndex = 1;

            // Initialize Select2 for first dropdown
            $(".course-select").select2();

            changeCourseTag()
            function changeCourseTag(){
                    let is_Course = @json($is_CourseCheck);

                if (is_Course == 'Yes') {
                    $('#courseAttachmentHeader, #courseDocumentWrapper, #addCourseBtnWrapper').removeClass(
                        'd-none');
                } else {
                    $('#courseAttachmentHeader, #courseDocumentWrapper, #addCourseBtnWrapper').addClass(
                        'd-none');
                }
            }
            // Toggle visibility based on Yes/No selection
            $('input[name="is_Course"]').on('change', function() {
                if ($(this).val() === 'Yes') {
                    $('#courseAttachmentHeader, #courseDocumentWrapper, #addCourseBtnWrapper').removeClass(
                        'd-none');
                } else {
                    $('#courseAttachmentHeader, #courseDocumentWrapper, #addCourseBtnWrapper').addClass(
                        'd-none');
                }
            });

            // Add new course row
            $("#add-course-btn").click(function() {
                let clone = $(".course-document-row:first").clone(false, false);
                clone.find("input, select").val("");

                // remove any existing Select2 DOM
                clone.find(".select2").remove();
                let newSelect = clone.find("select.course-select");
                newSelect.attr("id", "course_doc_type_" + courseIndex);
                newSelect.show();
                newSelect.select2();
                courseIndex++;

                // show delete button
                clone.find(".course_doc_del").show();

                // append to wrapper
                $("#courseDocumentWrapper").append(clone);
            });

            // Delete course row
            $(document).on("click", ".course_doc_del", function() {
                $(this).closest(".course-document-row").remove();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            maritalStausChange()
            function maritalStausChange(){
                if ($('#marital_status').val() === '1') {
                    $('.spouse-field').removeClass('d-none');
                } else {
                    $('.spouse-field').addClass('d-none');
                }
            }
            $('#marital_status').on('change', function() {
                if ($(this).val() === '1') {
                    $('.spouse-field').removeClass('d-none');
                } else {
                    $('.spouse-field').addClass('d-none');
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const yesRadio = document.getElementById("childrenYes");
            const noRadio = document.getElementById("childrenNo");
            const childrenSections = document.querySelectorAll(".children-section");
            toggleChildrenFields()
            function toggleChildrenFields() {
                childrenSections.forEach(section => {
                    if (yesRadio.checked) {
                        section.classList.remove("d-none");
                    } else {
                        section.classList.add("d-none");
                    }
                });
            }

            yesRadio.addEventListener("change", toggleChildrenFields);
            noRadio.addEventListener("change", toggleChildrenFields);
        });
    </script>

    <script>
        $(document).ready(function() {
            spouseWorkingChange()
            function spouseWorkingChange(){
                const yesRadio = document.getElementById("workingYes");
                 const noRadio = document.getElementById("workingNo");

                 if (yesRadio.checked) {
                   $('.working-fields').removeClass('d-none');
                } else {
                      $('.working-fields').addClass('d-none');
                }
            }

            ChangeChildrenCount()

            function ChangeChildrenCount(){
                const yesRadio = document.getElementById("childrenYes");
                 const noRadio = document.getElementById("childrenNo");
                if (yesRadio.checked) {
                    $('.children-section').removeClass('d-none');
                } else {
                     $('.children-section').addClass('d-none');
                }
            }


            $('input[name="is_working"]').on('change', function() {
                if ($(this).val() === 'Yes') {
                    $('.working-fields').removeClass('d-none');
                } else {
                    $('.working-fields').addClass('d-none');
                }
            });
        });
    </script>


    <script>
     
           
            changeSiblingDetails()

            function changeSiblingDetails(){
                const yesRadio = document.getElementById("SiblingsYes");
                 const noRadio = document.getElementById("SiblingsNo");
                if (yesRadio.checked) {
                    $('.sibling-section').removeClass('d-none');
                } else {
                     $('.sibling-section').addClass('d-none');
                }
            }
            // $('input[name="has_Siblings"]').on('change', function() {
            //     if ($(this).val() === 'Yes') {
            //         $('#siblingsDescription').removeClass('d-none');
            //     } else {
            //         $('#siblingsDescription').addClass('d-none');
            //     }
            // });
              document.addEventListener("DOMContentLoaded", function() {
                const siblingYesRadio = document.getElementById("SiblingsYes");
                const siblingNoRadio = document.getElementById("SiblingsNo");
                const siblingSections = document.querySelectorAll(".sibling-section");

                function toggleSiblingFields() {
                    siblingSections.forEach(section => {
                        if (siblingYesRadio.checked) {
                            section.classList.remove("d-none");
                        } else {
                            section.classList.add("d-none");
                        }
                    });
                }

                siblingYesRadio.addEventListener("change", toggleSiblingFields);
                siblingNoRadio.addEventListener("change", toggleSiblingFields);
            });
        
    </script>

    <script>
        $(document).ready(function() {
            $('#work_type').on('change', function() {
                if ($(this).val() === '2') {
                    $('.shiftedCompanyField').removeClass('d-none');
                } else {
                    $('.shiftedCompanyField').addClass('d-none');
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#source_id').on('change', function () {
                // Get the selected option
                const selectedOption = $(this).find(':selected');

                // Get the data-detail attribute value
                const detailCheck = selectedOption.data('detail');

                // Show or hide based on data-detail == 1
                if (detailCheck == 1) {
                    $('#knowDetails').removeClass('d-none');
                } else {
                    $('#knowDetails').addClass('d-none');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('input[name="is_MinimumWork"]').on('change', function() {
                if ($('#MinimumWorkNo').is(':checked')) {
                    $('#minimumWorkReason').removeClass('d-none');
                } else {
                    $('#minimumWorkReason').addClass('d-none');
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            $('input[name="is_OriginalCertificate"]').on('change', function() {
                if ($('#OriginalCertificateNo').is(':checked')) {
                    $('#originalCertificateReason').removeClass('d-none');
                } else {
                    $('#originalCertificateReason').addClass('d-none');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('input[name="is_Travel"]').on('change', function() {
                if ($('#TravelNo').is(':checked')) {
                    $('#travelReason').removeClass('d-none');
                } else {
                    $('#travelReason').addClass('d-none');
                }
            });
        });
    </script>


    <script>
        document.getElementById('childrenCount').addEventListener('input', loadChildrenFields);

        function loadChildrenFields() {
            const count = parseInt(document.getElementById('childrenCount').value);
            const container = document.getElementById('childrenDetails');
            container.innerHTML = '';

            if (!isNaN(count) && count > 0) {
                for (let i = 1; i <= count; i++) {

                    // fetch old data if exists
                    const childData = existingChildren[i - 1] || {};
                    // console.log('existingChildren:', existingChildren);
                    // console.log('childData:', childData);

                    const childDiv = document.createElement('div');
                    childDiv.classList.add('row', 'mb-3', 'border', 'rounded', 'p-3', 'bg-gray-200');

                    childDiv.innerHTML = `
                        <div class="col-lg-3 err-chk">
                            <label class="text-black fs-6 fw-semibold">Child ${i} Name<span class="text-danger">*</span></label>
                            <input type="text" name="child_name[]" class="form-control" value="${childData.child_name ?? ''}" placeholder="Enter Name">
                            <div class="text-danger error_msg"></div>
                        </div>

                        <div class="col-lg-3 err-chk">
                            <label class="text-black fs-6 fw-semibold">Date of Birth</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                <input type="text" name="child_dob[]" class="form-control common_datepicker"
                                    value="${childData.child_dob ?? ''}" readonly placeholder="Select Date" />
                            </div>
                            <div class="text-danger error_msg"></div>
                        </div>

                        <div class="col-lg-3 err-chk">
                            <label class="text-black fs-6 fw-semibold">Standard / Degree</label>
                            <input type="text" name="child_std[]" class="form-control"
                                value="${childData.child_std ?? ''}" placeholder="e.g. 12th std / BSc">
                            <div class="text-danger error_msg"></div>
                        </div>

                        <div class="col-lg-3 err-chk">
                            <label class="text-black fs-6 fw-semibold">Completion Year</label>
                            <input type="text" name="child_year[]" class="form-control"
                                value="${childData.child_year ?? ''}" placeholder="e.g. 2025" min="2000" max="2099">
                            <div class="text-danger error_msg"></div>
                        </div>
                    `;

                    container.appendChild(childDiv);

                    // Initialize Datepicker on pre-filled input
                    $(childDiv).find('.common_datepicker').datepicker({
                        todayHighlight: true,
                        autoclose: true,
                        format: 'dd-M-yyyy',
                        endDate: new Date()
                    });
                }
            }
        }
    </script>

    <script>
        document.getElementById('siblingsCount').addEventListener('input', loadSiblingFields);

        function loadSiblingFields() {
            const count = parseInt(document.getElementById('siblingsCount').value);
            const container = document.getElementById('siblingDetails');
            container.innerHTML = '';
            if (!isNaN(count) && count > 0) {
                for (let i = 1; i <= count; i++) {
                    const siblingData = existingSibling[i - 1] || {};
                    const siblingDiv = document.createElement('div');
                    siblingDiv.classList.add('row', 'mb-3', 'border', 'rounded', 'p-3', 'bg-gray-200');

                    siblingDiv.innerHTML = `
                    <div class="col-lg-3 err-chk">
                        <label class="text-black fs-6 fw-semibold">Sibling ${i} Name<span class="text-danger">*</span></label>
                        <input type="text" name="sibling_name[]" value="${siblingData.sibling_name ?? ''}" class="form-control" placeholder="Enter Name" oninput="formatName(this)">
                        <div class="text-danger error_msg"></div>
                    </div>

                    <div class="col-lg-3 err-chk sibling-type">
                        <label class="text-black fs-6 fw-semibold">Elder/Younger<span class="text-danger">*</span></label>
                        <select class="form-select select3"  name="sibling_type[]">
                            <option value="">Select Sibling</option>
                            <option value="Elder" ${siblingData.sibling_type == 'Elder' ? 'selected' : ''}>Elder</option>
                            <option value="Younger" ${siblingData.sibling_type == 'Younger' ? 'selected' : ''}>Younger</option>
                        </select>
                        <div class="text-danger error_msg"></div>
                    </div>
                    
                    <div class="col-lg-3 err-chk">
                        <label class="text-black fs-6 fw-semibold">Occupation / Education</label>
                        <input type="text" name="sibling_std[]" class="form-control" placeholder="e.g. 12th std / BSc / Teacher" value="${siblingData.sibling_std ?? ''}" oninput="formatCompanyName(this)">
                        <div class="text-danger error_msg"></div>
                    </div>
                    <div class="col-lg-3 err-chk">
                        <label class="text-black fs-6 fw-semibold">Annual Income</label>
                        <input type="text" name="sibling_income[]" class="form-control" placeholder="Enter Income" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" value="${siblingData.sibling_income ?? ''}">
                        <div class="text-danger error_msg"></div>
                    </div>
                `;
                    container.appendChild(siblingDiv);

                    $(siblingDiv).find('.select3').select2({
                        width: '100%',
                        dropdownParent: $(siblingDiv)
                    });

                    // Initialize Bootstrap Datepicker for the new input
                    $(siblingDiv).find('.common_datepicker').datepicker({
                        todayHighlight: true,
                        autoclose: true,
                        format: 'dd-M-yyyy',
                        endDate: new Date() // Prevent future dates
                    });
                }
            }
        }
        
    </script>

    <script>
        $(document).ready(function() {
            // When any checkbox with class .toggle-field is changed
            $('.toggle-field').on('change', function() {
                var target = $(this).data('target'); // get the target div ID

                if ($(this).is(':checked')) {
                    $(target).removeClass('d-none').hide().slideDown(200); // show field smoothly
                } else {
                    $(target).slideUp(200, function() {
                        $(this).addClass('d-none'); // hide and reapply d-none
                        $(this).find('input').val(''); // clear input value when unchecked
                    });
                }
            });
        });
    </script>


 {{-- progress --}}
<script>

  const stepIds = [
    "staff_add",
    "family_add",
    "contact_add",
    "socialmedia",
    "Education",
    "work_add",
    "companydetails",
    "salarydetails",
    "application_add",
    "checklist_add",
  ];

  /** 🔍 Check if element is visible */
    //   function isVisible(el) {
    //     if (el.classList.contains("select2-search__field")) return false;
    //     return !!(el.offsetParent !== null && !el.classList.contains("d-none"));
    //   }

    // function isVisible(el) {

    //     if (el.classList.contains("select2-search__field"))
    //         return false;

    //     // Ignore hidden Bootstrap sections
    //     if (el.closest(".d-none"))
    //         return false;

    //     // Ignore hidden containers
    //     if ($(el).closest(':hidden').length && !$(el).closest('.bs-stepper-content').length)
    //         return false;

    //     return true;
    // }
    function isVisible(el, ignoreStepperHidden = false) {

        if (el.classList.contains("select2-search__field"))
            return false;

        if (el.closest(".d-none"))
            return false;

        let parent = el;

        while (parent && parent !== document.body) {

            const style = window.getComputedStyle(parent);

            // Ignore bs-stepper hidden pages ONLY during initial load
            if (
                !ignoreStepperHidden &&
                style.display === "none"
            ) {
                return false;
            }

            parent = parent.parentElement;
        }

        return true;
    }
  /** 🔍 Exclude irrelevant fields */
  function shouldExclude(el) {
    if (el.classList.contains("select2-search__field")) return true;
    if (el.closest(".select2-container")) return true;
    return false;
  }

  /** ✅ Calculate step progress (with console logging) */
  function calculateStepPercentage(stepId, ignoreStepperHidden = false) {
    const section = document.getElementById(stepId);
      if (!section)
        return 0;

    // During editing only
    if (
        !ignoreStepperHidden &&
        (section.classList.contains("d-none") ||
         section.style.display === "none")
    ) {
        return 0;
    }

    const fields = section.querySelectorAll(
      'input:not([type=hidden]):not([type=button]):not([type=submit]):not([type=reset]), select, textarea, .common_datepicker'
    );

    if (!fields.length) return 0;

    let total = 0;
    let filled = 0;

    console.group(`🧩 Step: ${stepId}`);

   fields.forEach(f => {
    const name = f.name || f.id || f.className;
    if (shouldExclude(f)) return;
    if (f.hasAttribute('data-ignore-total')) return;
    if (f.type === "radio") return;
    if (f.classList.contains("ignore-progress")) return;

    // 🚀 Skip hidden elements
    if (!isVisible(f, ignoreStepperHidden)) return;

    total++;
    let isFilled = false;
    
    if (f.type === "file" && f.files.length > 0) isFilled = true;
    else if (f.type === "checkbox" && f.checked) isFilled = true;
    else if (f.tagName === "SELECT" && f.value !== "") isFilled = true;
    else if (f.classList.contains("common_datepicker") && f.value.trim() !== "") isFilled = true;
    else if ((f.tagName === "INPUT" || f.tagName === "TEXTAREA") && f.value.trim() !== "") isFilled = true;

    if (isFilled) filled++;
    });

    const percent = total > 0 ? Math.round((filled / total) * 100) : 0;
    console.log(`📊 Step: ${stepId} | Total: ${total} | Filled: ${filled} | % = ${percent}`);
    console.groupEnd();

    // Update only this step’s progress bar
    const progressBar = document.querySelector(`.step-progress[data-step="${stepId}"] .progress-bar`);
    if (progressBar) {
      progressBar.style.width = percent + "%";
      progressBar.setAttribute("aria-valuenow", percent);
      progressBar.textContent = percent + "%";

      progressBar.classList.remove("bg-success", "bg-warning", "bg-secondary");
      if (percent >= 90) progressBar.classList.add("bg-success");
      else if (percent > 0) progressBar.classList.add("bg-warning");
      else progressBar.classList.add("bg-secondary");
    }

    return percent;
  }

  /** ✅ Calculate total % (lightweight, no console spam) */
  function calculateTotalPercentage(form) {
    if (!form) return 0;
    const fields = form.querySelectorAll(
      'input:not([type=hidden]):not([type=button]):not([type=submit]):not([type=reset]):not([type=radio]), select, textarea, .common_datepicker'
    );
    let total = 0, filled = 0;
    fields.forEach(f => {
      if (shouldExclude(f)) return;
      if (f.hasAttribute('data-ignore-total')) return;
      total++;
      if (f.type === "file" && f.files.length > 0) filled++;
      else if (f.type === "checkbox" && f.checked) filled++;
      else if (f.tagName === "SELECT" && f.value !== "") filled++;
      else if (f.classList.contains("common_datepicker") && f.value.trim() !== "") filled++;
      else if ((f.tagName === "INPUT" || f.tagName === "TEXTAREA") && f.value.trim() !== "") filled++;
    });
    return total > 0 ? Math.round((filled / total) * 100) : 0;
  }

  /** ✅ Update only the active step */
  function updateStepPercentage(stepId , ignoreStepperHidden = false) {
    const form = document.getElementById("staff_form");
    if (!form || !stepId) return;
    calculateStepPercentage(stepId,ignoreStepperHidden);

    // Update hidden total field
    const total = calculateTotalPercentage(form);
    const hiddenInput = form.querySelector(".form-percentage");
    if (hiddenInput) hiddenInput.value = total;
  }

  /** ✅ Attach event listeners for real-time update of current step */
  function attachStepListeners(stepId) {
    const form = document.getElementById("staff_form");
    if (!form) return;

    form.addEventListener("input", e => {
      if (e.target.closest(`#${stepId}`)) {
        if (e.target.matches("input, textarea, .common_datepicker")) updateStepPercentage(stepId);
      }
    });

    form.addEventListener("change", e => {
      if (e.target.closest(`#${stepId}`)) {
        if (e.target.matches("select, .change-data, input[type=checkbox], .common_datepicker, input[type=file]"))
          updateStepPercentage(stepId);
      }
    });

    if (window.jQuery) {
      $(`#${stepId} .common_datepicker`).on("changeDate change", () => updateStepPercentage(stepId));
      $(`#${stepId} .select3`).on("change.select2 select2:select select2:unselect", () => updateStepPercentage(stepId));
    }

    // Run once initially
    updateStepPercentage(stepId);
  }

  function getStepId(element) {
    const step = element.closest(".content");
    return step ? step.id : null;
}

    function getCurrentStepPercentages() {
        let progress = {};
        document.querySelectorAll(".step-progress").forEach(item => {
            const step = item.dataset.step;
            const value = parseFloat(
                item.querySelector(".progress-bar")
                    .getAttribute("aria-valuenow")
            );
            progress[step] = value;
        });
        return progress;
    }
</script>



<script>
    let payrolDetails = [];
    function submit_form(stage) {
        const form = document.getElementById("staff_form");
        const submitBtn = document.getElementById("submitStaffBtn");
        const submitBtnText = document.getElementById("yesBtnText");
        const submitBtnLoader = document.getElementById("yesBtnLoader");
         const edit_id =$('#edit_id').val();
        if (form) {
            // Disable the button to prevent duplicate submission
            submitBtn.disabled = true;
            submitBtnText.style.display = "none"; // Hide "Yes"
            submitBtnLoader.style.display = "inline-block"; // Show loader

            const total = calculateTotalPercentage(form);
            // $('#completion_percentage').val(total);

            // ✅ Create FormData manually
            const formData = new FormData(form);
            formData.append('stage', stage);
            formData.append('edit_id', edit_id);

            const progress = getCurrentStepPercentages();

            const average =
                Object.values(progress).reduce((a,b)=>a+b,0) /
                Object.keys(progress).length;

            formData.append("step_progress", JSON.stringify(progress));
            formData.append("completion_percentage", average.toFixed(1));


            // ✅ Send via AJAX (so files are sent correctly)
            $.ajax({
                url: '/update_staff_by_stage',
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // console.log("Success:", response);
                    window.location.href = '/hr_enroll/manage_staff';
                },
                error: function(err) {
                    console.error("Error:", err);
                    // In case of an error, re-enable the button and reset the text
                    submitBtn.disabled = false;
                    submitBtnText.style.display = "inline-block"; // Show "Yes" again
                    submitBtnLoader.style.display = "none"; // Hide loader
                }
            });
        }
    }

    function updateAndNext(stage) {
        const form = document.getElementById("staff_form");
        const submitBtn = document.getElementById("updateNxt"+stage);
        const submitBtnText = document.getElementById("updateNxtText"+stage);
        const submitBtnLoader = document.getElementById("updateNxtLoader"+stage);

        const edit_id =$('#edit_id').val();

        if (form) {
            // Disable the button to prevent duplicate submission
            submitBtn.disabled = true;
            submitBtnText.style.display = "none"; // Hide "Yes"
            submitBtnLoader.style.display = "inline-block"; // Show loader

            const total = calculateTotalPercentage(form);
            // $('#completion_percentage').val(total);

            // ✅ Create FormData manually
            const formData = new FormData(form);

            formData.append('stage', stage);
            formData.append('edit_id', edit_id);

            const progress = getCurrentStepPercentages();

            const average =
                Object.values(progress).reduce((a,b)=>a+b,0) /
                Object.keys(progress).length;

            formData.append("step_progress", JSON.stringify(progress));
            formData.append("completion_percentage", average.toFixed(1));

            

            // ✅ Send via AJAX (so files are sent correctly)
            $.ajax({
                url: '/update_staff_by_stage',
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // console.log("Success:", response);
                    safeNext(stage);
                    // safeNext(stage);
                    submitBtn.disabled = false;
                    submitBtnText.style.display = "inline-block"; // Show "Yes" again
                    submitBtnLoader.style.display = "none"; 
                },
                error: function(err) {
                    console.error("Error:", err);
                    // In case of an error, re-enable the button and reset the text
                    submitBtn.disabled = false;
                    submitBtnText.style.display = "inline-block"; // Show "Yes" again
                    submitBtnLoader.style.display = "none"; // Hide loader
                }
            });
        }
    }

    function updateStaff(stage) {
        const form = document.getElementById("staff_form");
        const submitBtn = document.getElementById("updateClose"+stage);
        const submitBtnText = document.getElementById("updateBtnText"+stage);
        const submitBtnLoader = document.getElementById("updateBtnLoader"+stage);

        const edit_id =$('#edit_id').val();

        if (form) {
            // Disable the button to prevent duplicate submission
            submitBtn.disabled = true;
            submitBtnText.style.display = "none"; // Hide "Yes"
            submitBtnLoader.style.display = "inline-block"; // Show loader

            const total = calculateTotalPercentage(form);
            // $('#completion_percentage').val(total);

            // ✅ Create FormData manually
            const formData = new FormData(form);
            // let salary_structure = collectSalaryComponents();
            // formData.append('salary_structure', JSON.stringify(salary_structure));
            formData.append('stage', stage);
            formData.append('edit_id', edit_id);

            const progress = getCurrentStepPercentages();

            const average =
                Object.values(progress).reduce((a,b)=>a+b,0) /
                Object.keys(progress).length;

            formData.append("step_progress", JSON.stringify(progress));
            formData.append("completion_percentage", average.toFixed(1));

            // ✅ Send via AJAX (so files are sent correctly)
            $.ajax({
                url: '/update_staff_by_stage',
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // console.log("Success:", response);
                    window.location.href = '/hr_enroll/manage_staff';
                    // safeNext(stage);
                },
                error: function(err) {
                    console.error("Error:", err);
                    // In case of an error, re-enable the button and reset the text
                    submitBtn.disabled = false;
                    submitBtnText.style.display = "inline-block"; // Show "Yes" again
                    submitBtnLoader.style.display = "none"; // Hide loader
                }
            });
        }
    }

</script>


<!-- validation  -->

<script>
  // ✅ Initialize Stepper once globally
  let stepper;

  $(document).ready(function () {
      const stepperEl = document.querySelector('.bs-stepper');
      if (stepperEl) {
          stepper = new Stepper(stepperEl);
      }
  });


  function validation_func(stage) {
    let err = false; 

    // ===== STAGE 1 =====
    if (stage === 1) {
            if (!$('#staff_name').val().trim()) { $('#staff_name_err').text('Staff Name is required.'); err = true; }else{$('#staff_name_err').text('');}
            if (!$('#mobile_no').val().trim()) { $('#mobile_no_err').text('Mobile Number is required.'); err = true; }else{$('#mobile_no_err').text('');}
            if (!$('#staff_dob').val().trim()) { $('#staff_dob_err').text('Date Of Birth is required.'); err = true; }else{$('#staff_dob_err').text('');}
            
          const email = $('#email_id').val().trim();
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if(email == ''){
                $('#email_id_err').text('Email Id is required.'); err = true;
            }else if (email !== '' && !emailPattern.test(email)) {
                $('#email_id_err').text('Enter a valid Email ID.');
                err = true;
            }else{
                $('#email_id_err').text('');
            }
            if (!$('#mother_tongue').val().trim()) { $('#mother_tongue_err').text('Mother Tongue is required.'); err = true; }else{$('#mother_tongue_err').text('');}
            if ($('#Languages').val()=="") { $('#Languages_err').text('Language is required.'); err = true; }else{$('#Languages_err').text('');}

        // if (!err)
             safeNext(stage); // ✅ only go next if no validation errors
    }
    // ===== STAGE 2 =====
    else if(stage === 2) {
        if (!$('#father_name').val().trim()) { $('#father_name_err').text('Father Name is required.'); err = true; }else{$('#father_name_err').text('')}
        if (!$('#father_occup').val().trim()) { $('#father_occup_err').text('Father Occupation is required.'); err = true; }else{$('#father_occup_err').text('')}
        if (!$('#mother_name').val().trim()) { $('#mother_name_err').text('Mother Name is required.'); err = true; }else{$('#mother_name_err').text('')}
        if (!$('#mother_occup').val().trim()) { $('#mother_occup_err').text('Mother Occupation is required.'); err = true; }else{$('#mother_occup_err').text('')}
        if (!$('#marital_status').val().trim()) { $('#marital_status_err').text('Marital Status is required.'); err = true; }else{$('#marital_status_err').text('')}
        var marital_status=$('#marital_status').val().trim();
        
        if(marital_status==1){
            // if (!$('#anniversary_date').val().trim()) { $('#anniversary_date_err').text('Anniversary Date is required.'); err = true; }else{$('#anniversary_date_err').text('')}
            // if (!$('#spouse_name').val().trim()) { $('#spouse_name_err').text('Spouse Name is required.'); err = true; }else{$('#spouse_name_err').text('')}
            // if (!$('#spouse_mobile').val().trim()) { $('#spouse_mobile_err').text('Spouse Mobile No is required.'); err = true; }else{$('#spouse_mobile_err').text('')}
            // if (!$('#spouse_dob').val().trim()) { $('#spouse_dob_err').text('Spouse Date Of Birth is required.'); err = true; }else{$('#spouse_dob_err').text('')}
            // if($('#workingYes').prop('checked')){
            //     if (!$('#spouse_designation').val().trim()) { $('#spouse_designation_err').text('Spouse Designation is required.'); err = true; }else{$('#spouse_designation_err').text('')}
            //     if (!$('#spouse_company_name').val().trim()) { $('#spouse_company_name_err').text('Spouse Company Name is required.'); err = true; }else{$('#spouse_company_name_err').text('')}
            //     if (!$('#spouse_salary').val().trim()) { $('#spouse_salary_err').text('Spouse Salary is required.'); err = true; }else{$('#spouse_salary_err').text('')}
            // }
        }
        
        if ($('#childrenYes').prop('checked')) {
            if (!$('#childrenCount').val().trim()) {
                $('#childrenCount_err').text('Children Count is required.');
                err = true;
            } else {
                $('#childrenCount_err').text('');
            }

            // ✅ Validate each dynamically added child field
            $('#childrenDetails .row').each(function (index) {
                const childIndex = index + 1;

                const childNameField = $(this).find('input[name="child_name[]"]');
                const childDobField = $(this).find('input[name="child_dob[]"]');
                const childStdField = $(this).find('input[name="child_std[]"]');
                const childYearField = $(this).find('input[name="child_year[]"]');

                const childName = childNameField.val().trim();
                const childDob = childDobField.val().trim();
                const childStd = childStdField.val().trim();
                const childYear = childYearField.val().trim();

                // Clear previous error messages
                $(this).find('.error_msg').text('');

                if (!childName) {
                    childNameField.closest('.err-chk').find('.error_msg').text('Please enter the child name.');
                    err = true;
                }

                // if (!childDob) {
                //     childDobField.closest('.err-chk').find('.error_msg').text('Please select the date of birth.');
                //     err = true;
                // }

                // if (!childStd) {
                //     childStdField.closest('.err-chk').find('.error_msg').text('Please enter the standard or degree.');
                //     err = true;
                // }

                // if (!childYear) {
                //     childYearField.closest('.err-chk').find('.error_msg').text('Please enter the completion year.');
                //     err = true;
                // }
            });
        }

        if ($('#SiblingsYes').prop('checked')) {
            if (!$('#siblingsCount').val().trim()) {
                $('#siblingsCount_err').text('Sibling Count is required.');
                err = true;
            } else {
                $('#siblingsCount_err').text('');
            }

            // ✅ Validate each dynamically added child field
            $('#siblingDetails .row').each(function (index) {
                const siblingIndex = index + 1;

                const siblingNameField = $(this).find('input[name="sibling_name[]"]');
                const siblingTypeField = $(this).find('select[name="sibling_type[]"]');
                const siblingStdField = $(this).find('input[name="sibling_std[]"]');
                const siblingYearField = $(this).find('input[name="sibling_income[]"]');

                const siblingName = siblingNameField.val().trim();
                const siblingType = siblingTypeField.val();
                const siblingStd = siblingStdField.val().trim();
                const siblingYear = siblingYearField.val().trim();

                // Clear previous error messages
                $(this).find('.error_msg').text('');

                if (!siblingName) {
                    siblingNameField.closest('.err-chk').find('.error_msg').text('Please enter the Sibling name.');
                    err = true;
                }

                if (!siblingType) {
                    siblingTypeField.closest('.err-chk').find('.error_msg').text('Please select the Sibling Type.');
                    err = true;
                }

                // if (!childStd) {
                //     childStdField.closest('.err-chk').find('.error_msg').text('Please enter the standard or degree.');
                //     err = true;
                // }

                // if (!childYear) {
                //     childYearField.closest('.err-chk').find('.error_msg').text('Please enter the completion year.');
                //     err = true;
                // }
            });
        }
        
        // if($('#SiblingsYes').prop('checked')){
        //      if (!$('#siblings_detail').val().trim()) { $('#siblings_detail_err').text('Siblings Detail is required.'); err = true; }else{$('#siblings_detail_err').text('')}
        // }
        

        // if (!err)
             safeNext(stage); // ✅ only go next if no validation errors
    }
    // ===== STAGE 3 =====
    else if (stage === 3) {
             if (!$('#permanent_address').val().trim()) { $('#permanent_address_err').text('Permanent Address is required.'); err = true; }else{$('#permanent_address_err').text('')}
               // Validate Contact Person(s)
            $('#altmobile-wrapper .altmobile-row').each(function (index) {
                const nameInput = $(this).find('input[name="contact_person_name[]"]');
                const relationInput = $(this).find('select[name="contact_person_relation[]"]');
                const mobileInput = $(this).find('input[name="contact_person_no[]"]');
                const nameErr = $(this).find('#contact_person_name_err');
                const relationErr = $(this).find('#contact_person_relation_err');
                const mobileErr = $(this).find('#contact_person_no_err');

                let rowErr = false;

                // Name validation
                if (!nameInput.val().trim()) {
                    nameErr.text('Contact person name is required.');
                    rowErr = true;
                } else {
                    nameErr.text('');
                }
                // console.log('cname -',nameInput.val())
                // console.log('crelation -',relationInput.val())
                if (!relationInput.val()) {

                    relationErr.text('Contact person Relation is required.');
                    rowErr = true;
                } else {
                    relationErr.text('');
                }
                // Mobile validation (required + 10 digits)
                const mobileVal = mobileInput.val().trim();
                if (!mobileVal) {
                    mobileErr.text('Contact person mobile No is required.');
                    rowErr = true;
                } else if (!/^\d{10}$/.test(mobileVal)) {
                    mobileErr.text('Enter a valid 10-digit mobile number.');
                    rowErr = true;
                } else {
                    mobileErr.text('');
                }

                if (rowErr) err = true;
            });
        
        // if (!err)
             safeNext(stage);
    }
    // ===== STAGE 4 =====
    else if (stage === 4) {
        const socialMediaFields = document.querySelectorAll('.toggle-field');
        let err = false; // ensure err variable starts false here if this block runs independently

        socialMediaFields.forEach(checkbox => {
            const socialMediaId = checkbox.getAttribute('id').replace('checkSocialMedia_', '');
            const urlInput = document.querySelector(`#socialMediaField_${socialMediaId} input`);
            const errorElement = document.querySelector(`#checkSocialMedia_${socialMediaId}_err`);

            // Only validate if checkbox is checked
            if (checkbox.checked) {
                const url = urlInput.value.trim();

                if (url === '') {
                    errorElement.textContent = `${checkbox.nextElementSibling.innerText} URL is required.`;
                    err = true;
                } 
                else if (!isValidURL(url)) {
                    errorElement.textContent = `Please enter a valid ${checkbox.nextElementSibling.innerText} URL.`;
                    err = true;
                } 
                else {
                    errorElement.textContent = '';
                }
            } else {
                // Clear error and input if unchecked
                errorElement.textContent = '';
                urlInput.value = '';
            }
        });

        // if (!err) 
        safeNext(stage);
    }
    // ===== STAGE 5 =====
    else if (stage === 5) {

         $("#education-wrapper .education-row").each(function (index) {

            const $row = $(this);

            const qualification = $row.find('select[name="qualification_type[]"]');
            const major         = $row.find('select[name="major[]"]');
            const univ          = $row.find('input[name="univ_name[]"]');
            const passYear      = $row.find('input[name="pass_year[]"]');

            const qualificationErr = qualification.closest(".mb-3").find(".error_msg");
            const majorErr         = major.closest(".mb-3").find(".error_msg");
            const univErr          = univ.closest(".mb-3").find(".error_msg");
            const passYearErr      = passYear.closest(".mb-3").find(".error_msg");

            // Clear previous errors
            $row.find(".error_msg").text("");
            qualification.next('.select2').find('.select2-selection')
                .removeClass('is-invalid');

            // Qualifications where major should NOT be required
            const skipMajorFor = ['4', '5', '6', 'Others'];

            // ------------------ Qualification ------------------
            if (!qualification.val()) {
                qualificationErr.text("Qualification Type is required.");
                qualification.next('.select2').find('.select2-selection')
                    .addClass('is-invalid');
                err = true;
            }

            // ------------------ Major ------------------
            if (!skipMajorFor.includes(qualification.val())) {

                // Only validate if visible
                if (!major.val()) {
                    majorErr.text("Major is required.");
                    err = true;
                }

            } else {
                // Clear value if skipped
                major.val("").trigger("change");
            }

            // ------------------ University ------------------
            if (!skipMajorFor.includes(qualification.val())) {
                if (!univ.val() || !univ.val().trim()) {
                    univErr.text("Institute / University Name is required.");
                    err = true;
                }
            }

            // ------------------ Year ------------------
            if (!passYear.val() || !passYear.val().trim()) {
                passYearErr.text("Qualification Year is required.");
                err = true;
            }

        });

        // === Validate "Any Course Completed?" ===
        const courseTag = $("#course_tag");
        const courseTagErr = courseTag.siblings(".error_msg");
        courseTagErr.text("");

        const selectedCourseOption = $('input[name="is_Course"]:checked').val();

        if (!selectedCourseOption) {
            courseTagErr.text("Please select whether any course is completed.");
            err = true;
        } else if (selectedCourseOption === "Yes") {
            if (!courseTag.val() || !courseTag.val().trim()) {
                courseTagErr.text("Please enter the course name.");
                err = true;
            }
        }
    //    if (!err)
         safeNext(stage);
    }
    // ===== STAGE 6 =====
    else if (stage === 6) {
       
        const workType = $("#work_type");
        const workTypeErr = workType.closest(".col-lg-4").find(".error_msg");

        // ✅ Validate Work Type
        if (!workType.val()) {
            workTypeErr.text("Please select Work Type.");
            err = true;
        }

        // ✅ If Experience selected (value = 2)
        if (workType.val() === "2") {
                const companyshift = $("#total_company_shift");
                const companyshiftErr = companyshift.closest(".col-lg-4").find(".error_msg");
                const totalExpr = $("#total_experience");
                const totalExprErr = totalExpr.closest(".col-lg-4").find(".error_msg");

                // ✅ Validate Work Type
                if (!companyshift.val()) {
                    companyshiftErr.text("Company Shift Count is Required");
                    err = true;
                }
                // ✅ Validate Work Type
                if (!totalExpr.val()) {
                    totalExprErr.text("Total Year of Experience Count is Required");
                    err = true;
                }

            // Validate shifted company fields
            $(".shiftedCompanyField").each(function () {
                const input = $(this).find("input");
                const inputErr = $(this).find(".error_msg");

                if (!input.val().trim()) {
                    inputErr.text("This field is required.");
                    err = true;
                } else {
                    inputErr.text("");
                }
            });

            // Validate each Previous Company Detail row
            $("#work-exp-wrapper .work-exp-row").each(function (index) {
                const row = $(this);
                let rowErr = false;

                // Loop through required inputs
                row.find(".required-field").each(function () {
                    const input = $(this);
                    const inputErr = input.closest(".mb-3").find(".error_msg");

                    if (!input.val().trim()) {
                        inputErr.text("This field is required.");
                        rowErr = true;
                        err = true;
                    } else {
                        inputErr.text("");
                    }
                });

                // ✅ Check start < end date
                const stDate = row.find('input[name="work_st_date[]"]').val().trim();
                const endDate = row.find('input[name="work_end_date[]"]').val().trim();
                const endDateErr = row.find('input[name="work_end_date[]"]').closest(".mb-3").find(".error_msg");

                if (stDate && endDate && new Date(stDate) > new Date(endDate)) {
                    endDateErr.text("End Date must be after Start Date.");
                    rowErr = true;
                    err = true;
                }
            });
        }

        // ✅ Validate Document Attachments
        // $("#document-wrapper .document-row").each(function (index) {
        //     const docType = $(this).find('select[name="doc_type[]"]');
        //     const fileInput = $(this).find('input[type="file"]')[0];
        //     const docTypeErr = docType.closest(".mb-3").find(".error_msg");

        //     if (!docType.val()) {
        //         docTypeErr.text("Please select a Document Type.");
        //         err = true;
        //     } else {
        //         docTypeErr.text("");
        //     }

        //     if (fileInput && fileInput.files.length === 0) {
        //         // Append error message below the dropzone if missing
        //         const fileErrDiv = $(this).find(".dropzone").next(".error_msg");
        //         if (fileErrDiv.length) {
        //             fileErrDiv.text("Please upload a file.");
        //         } else {
        //             $(this).find(".dropzone").after('<div class="text-danger error_msg">Please upload a file.</div>');
        //         }
        //         err = true;
        //     }
        // });

        // if (!err) 
            safeNext(stage);

        // if (!err){
        //     var staff_name = $('#staff_name').val().trim();
        //     var staff_mobile = $('#mobile_no').val().trim();
        //     var lastFourDigitMobileNo = staff_mobile.slice(-4);
        //     var name_parts = staff_name.split(' '); // Split the name by spaces
        //     var randomNumber = Math.floor(1000 + Math.random() * 9000);
           
        //     // Join all parts except the last part (first name + middle name if present)
        //     var first_name = name_parts.slice(0, -1).join(''); 
            
        //     var last_name = name_parts[name_parts.length - 1]; // Last part is the last name
            
        //     // Construct the username: if the last name is more than one character, just combine the first and last name
        //     var username = first_name.toLowerCase() + (last_name.length === 1 ? '.' + last_name.toLowerCase() : last_name.toLowerCase());
        //     // random passs
        //     // var password = username + '@' + randomNumber;
        //     // mobile no pass
        //     var password = username + '@' + lastFourDigitMobileNo;
        //     // Set the username in the login input field
        //     $('#loginuser_name').val(username);
        //     $('#loginpassword').val(password);
        //     user_name_chk(username)
        //     safeNext(stage);
        // } 

    }
    // ===== STAGE 7 =====
    else if (stage === 7) {
        
        // Get the selected company type (Management or Business)
            const selectedCompanyType = $('input[name="company"]:checked').val();

            
            const management_depart = $("#management_depart");
            const management_user_role = $("#management_user_role");
            const management_division = $("#management_division");
            const management_job_role = $("#management_job_role");
            const staffCompany = $("#staff_company_name");
            const entity_name = $("#entity_name");
            const branch_id = $("#branch_id");
            const business_depart = $("#business_depart");
            const business_user_role = $("#business_user_role");
            const business_division = $("#business_division");
            const business_job_role = $("#business_job_role");
            const loginuser_name = $("#loginuser_name");
            const loginpassword = $("#loginpassword");

            const manageDepartErr = management_depart.closest(".err-chk").find(".error_msg");
            const manageRoleErr = management_user_role.closest(".err-chk").find(".error_msg");
            const managementDivisionErr = management_division.closest(".err-chk").find(".error_msg");
            const managementJobRoleErr = management_job_role.closest(".err-chk").find(".error_msg");
            const staffCompanyErr = staffCompany.closest(".mb-3").find(".error_msg");
            const entityNameErr = entity_name.closest(".mb-3").find(".error_msg");
            const branchIdErr = branch_id.closest(".mb-3").find(".error_msg");
            const businessDepartErr = business_depart.closest(".err-chk").find(".error_msg");
            const businessRoleErr = business_user_role.closest(".err-chk").find(".error_msg");
            const businessDivisionErr = business_division.closest(".err-chk").find(".error_msg");
            const businessJobRoleErr = business_job_role.closest(".err-chk").find(".error_msg");
            const loginUserNameErr = loginuser_name.closest(".err-chk").find(".error_msg");
            const loginPasswordErr = loginpassword.closest(".err-chk").find(".error_msg");

            
            // Clear any previous error messages
            manageDepartErr.text(""); 
            manageRoleErr.text(""); 
            managementDivisionErr.text(""); 
            managementJobRoleErr.text(""); 
            staffCompanyErr.text("");
            entityNameErr.text("");
            branchIdErr.text("");
            businessDepartErr.text(""); 
            businessRoleErr.text(""); 
            businessDivisionErr.text(""); 
            businessJobRoleErr.text(""); 
            loginUserNameErr.text(""); 
            loginPasswordErr.text(""); 


            if (selectedCompanyType == 1) {
                // For Management: Validate the Department field
                if (!management_depart.val() || !management_depart.val().trim()) {
                    manageDepartErr.text("Department is Required.");
                    err = true;  
                } 
                if (!management_division.val() || !management_division.val().trim()) {
                    managementDivisionErr.text("Division is Required.");
                    err = true;  
                } 
                if (!management_job_role.val() || !management_job_role.val().trim()) {
                    managementJobRoleErr.text("Job Role is Required.");
                    err = true; 
                } 

                if ($('#login_access').is(':checked')) {
                    if (!management_user_role.val() || !management_user_role.val().trim()) {
                        manageRoleErr.text("User Role is Required.");
                        err = true;  
                    }
                }

            } else {
                // For Business: Validate the Company Name field
                if (!staffCompany.val() || staffCompany.val().trim() === '') {
                    staffCompanyErr.text("Company is Required.");
                    err = true;  
                } 
                if (!entity_name.val() || entity_name.val().trim() === '') {
                    entityNameErr.text("Entity is Required.");
                    err = true;  
                } 
                if (!branch_id.val() || branch_id.val().trim() === '') {
                    branchIdErr.text("Branch is Required.");
                    err = true;  
                } 
                if (!business_depart.val() || business_depart.val().trim() === '') {
                    businessDepartErr.text("Department is Required.");
                    err = true;  
                } 
                if (!business_division.val() || business_division.val().trim() === '') {
                    businessDivisionErr.text("Division is Required.");
                    err = true;  
                } 
                if (!business_job_role.val() || business_job_role.val().trim() === '') {
                    businessJobRoleErr.text("Job Role is Required.");
                    err = true;  
                } 

                if ($('#login_access').is(':checked')) {
                    if (!business_user_role.val() || business_user_role.val().trim() === '') {
                        businessRoleErr.text("User Role is Required.");
                        err = true;  
                    }
                }
            }

            // Check other fields like Pseudo Name, Date of Joining, Basic Salary, Per Hour Cost, etc.
            const pseudo_name = $("#pseudo_name");
            const pseudoNameErr = pseudo_name.closest(".mb-3").find(".error_msg");
            pseudoNameErr.text("");

            if (!pseudo_name.val().trim()) {
                 pseudoNameErr.text('Pseudo Name is required');
                err = true;
            }

            const staff_doj = $("#staff_doj");
            const dojErr = staff_doj.closest(".mb-3").find(".error_msg");
            dojErr.text("");

            if (!staff_doj.val()) {
                dojErr.text("Date of Joining is Required.");
                err = true;
            }

            

            const skill_tag = $("#skill_tag");
            const skillTagErr = skill_tag.siblings(".error_msg");
            skillTagErr.text("");

            if (!skill_tag.val().trim()) {
                skillTagErr.text("Skill Tag is Required.");
                err = true;
            }

               if ($('#login_access').is(':checked')) {
                if (!loginuser_name.val() || loginuser_name.val().trim() === '') {
                    loginUserNameErr.text("Username is Required.");
                    err = true;  
                } 
                if (!loginpassword.val() || loginpassword.val().trim() === '') {
                    loginPasswordErr.text("Password is Required.");
                    err = true;  
                } 
            }
        // if (!err) 
            
          
            safeNext(stage);
    }else if (stage === 8) {

                const salary_date = $("#salary_date");
                const salary_dateErr = salary_date.closest('.col-lg-4').find('.error_msg');
                salary_dateErr.text("");

                if (!salary_date.val()) {
                    salary_dateErr.text("Salary Date is Required.");
                    err = true;
                }

            let accountType =$('[name="is_multiple_account"]:checked').val() || 0;
            let salaryType =$('[name="salary_type"]:checked').val() || 1;
            let accounts = [];

            if(accountType == 1){
                accounts = collectSalaryAccounts();
                if(accounts.length === 0)
                {
                    toastr.error( 'Please add at least one salary account');
                     err = true;
                }
                let primaryAccounts = accounts.filter( x => parseInt(x.is_primary) === 1);
                if(primaryAccounts.length !== 1){
                    toastr.error('Select exactly one primary account');
                     err = true;
                }

                for(let i = 0; i < accounts.length; i++)
                {
                    if(!accounts[i].salary_company_id){
                        toastr.error('Company is required in row ' + (i + 1));
                        err = true;
                    }
                    if(parseFloat(accounts[i].gross_salary || 0) <= 0){
                        toastr.error('Gross salary must be greater than 0 in row ' + (i + 1));
                         err = true;
                    }
                }
            }else{
                const basic_salary = $("#basic_salary");
                const basicSalaryErr = basic_salary.siblings(".error_msg");
                basicSalaryErr.text("");

                if(basic_salary.val() == 0){
                    basicSalaryErr.text("Salary Must Be Greater Than Zero.");
                    err = true;
                }

                if (!basic_salary.val().trim()) {
                    basicSalaryErr.text("Basic Salary is Required.");
                    err = true;
                }

                const salary_company_id = $("#salary_company_id");
                const salaryCompanyErr = salary_company_id.closest('.col-lg-4').find('.error_msg');
                salaryCompanyErr.text("");

                if (!salary_company_id.val()) {
                    salaryCompanyErr.text("Salary Company is Required.");
                    err = true;
                }
            }


            const payroll_template_sno = $("#payroll_template_sno");
            const payroll_template_snoErr = payroll_template_sno.closest('.col-lg-4').find('.error_msg');
            payroll_template_snoErr.text("");

            if (!payroll_template_sno.val()) {
                payroll_template_snoErr.text("Payroll Template is Required.");
                err = true;
            }

       
            $('.salary-component-row').each(function(index) {
                let row = $(this);
                let componentSno = row.find('.payroll_component_sno').val();
                let componentType = row.find('.component_type').val();
                let calculationType = row.data('calculation-type');
                let percentageValue = parseFloat(row.find('.percentage_value').val()) || 0;
                let calculatedAmount = parseFloat(row.find('.calculated_amount').val()) || 0;
                payrolDetails.push({
                    payroll_component_sno: componentSno,
                    payroll_rule_sno: row.find('.payroll_rule_sno').val(),
                    component_type: componentType,
                    calculation_type: calculationType,
                    percentage_value: percentageValue,
                    calculated_amount: calculatedAmount,
                    fixed_amount: calculatedAmount,
                    include_in_ctc: 1,
                    include_in_gross: componentType === 'earning' ? 1 : 0,
                    include_in_payslip: 1,
                    display_order: index + 1
                });
            });

            if (payrolDetails.length == 0) {
                toastr.error('Please Once again Select Payroll Template');
                 err = true;
            }

        // if (!err)
             safeNext(stage);
    }
    // ===== STAGE 9 =====
    else if (stage === 9) {
      
            const applied_position = $("#applied_position");
            const source_id = $("#source_id");
            const interview_company = $("#interview_company");

            const appliedPostionErr = applied_position.closest(".err-chk").find(".error_msg");
            const sourceIdErr = source_id.closest(".err-chk").find(".error_msg");
            const intervCompErr = interview_company.closest(".err-chk").find(".error_msg");

            // 🔹 Clear previous error messages
            $(".error_msg").text("");

            // 🔹 Validate main fields
            if (!applied_position.val() || applied_position.val().length === 0) {
                appliedPostionErr.text("Position is required.");
                err = true;
            }
            if (!source_id.val() || source_id.val().trim() === "") {
                sourceIdErr.text("Source is required.");
                err = true;
            }
            if (!interview_company.val() || interview_company.val().length === 0) {
                intervCompErr.text("Company is required.");
                err = true;
            }

            // 🔹 Validate both dynamic and dependent questions
            $(".dynamic-question, .dependent-question:not(.d-none)").each(function () {
                const label = $(this).find("label").first().text().trim();
                const hasMandatory = $(this).find("span.text-danger").length > 0;
                if (!hasMandatory) return; // skip non-mandatory

                const field = $(this).find(".hrq-field, input, textarea, select").first();
                const fieldType = field.attr("type") || field.prop("tagName").toLowerCase();

                let value = null;
                let isValid = true;

                // --- Text field ---
                if (fieldType === "text") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Text area ---
                else if (fieldType === "textarea") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Date field ---
                else if (fieldType === "date") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Radio buttons ---
                else if (fieldType === "radio") {
                    const name = field.attr("name");
                    if (!$(`input[name='${name}']:checked`).length) isValid = false;
                }

                // --- Checkboxes ---
                else if (fieldType === "checkbox") {
                    const name = field.attr("name");
                    if (!$(`input[name='${name}[]']:checked`).length) isValid = false;
                }

                // --- Select (list_box) ---
                else if (field.prop("tagName").toLowerCase() === "select") {
                    value = field.val();
                    if (!value || value === "") isValid = false;
                }

                // --- File (multiple_images) ---
                else if (fieldType === "file") {
                    const files = field[0].files;
                    if (!files || files.length === 0) isValid = false;
                }

                // --- Show error if invalid ---
                if (!isValid) {
                    err = true;
                    let errorBox = $(this).find(".error_msg");
                    if (errorBox.length === 0) {
                        $(this).append('<div class="text-danger error_msg"></div>');
                        errorBox = $(this).find(".error_msg");
                    }
                    errorBox.text(`${label.replace("*", "").trim()} is required.`);
                } else {
                    $(this).find(".error_msg").text(""); // clear old error
                }
            });



    //    if (!err) 
        safeNext(stage);
    }
    // ===== STAGE 10 =====
    else if (stage === 10) {
        if (!err) {
            var staff_name = $("#staff_name").val().trim();
            $('#create_staff_label').html(staff_name);
            $('#submit_popup').trigger('click');
        } else {
            
        }
    }
  }

  function next_validation_func(stage) {
    let err = false; 

    // ===== STAGE 1 =====
    if (stage === 1) {
            if (!$('#staff_name').val().trim()) { $('#staff_name_err').text('Staff Name is required.'); err = true; }else{$('#staff_name_err').text('');}
            if (!$('#mobile_no').val().trim()) { $('#mobile_no_err').text('Mobile Number is required.'); err = true; }else{$('#mobile_no_err').text('');}
            if (!$('#staff_dob').val().trim()) { $('#staff_dob_err').text('Date Of Birth is required.'); err = true; }else{$('#staff_dob_err').text('');}
            
          const email = $('#email_id').val().trim();
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if(email == ''){
                $('#email_id_err').text('Email Id is required.'); err = true;
            }else if (email !== '' && !emailPattern.test(email)) {
                $('#email_id_err').text('Enter a valid Email ID.');
                err = true;
            }else{
                $('#email_id_err').text('');
            }
            if (!$('#mother_tongue').val().trim()) { $('#mother_tongue_err').text('Mother Tongue is required.'); err = true; }else{$('#mother_tongue_err').text('');}
            if ($('#Languages').val()=="") { $('#Languages_err').text('Language is required.'); err = true; }else{$('#Languages_err').text('');}
            if (!$('#blood_group').val().trim()) { $('#blood_group_err').text('Blood Group is required.'); err = true; }else{$('#blood_group_err').text('');}
            if (!$('#nationality').val().trim()) { $('#nationality_err').text('Nationality is required.'); err = true; }else{$('#nationality_err').text('');}
            if (!$('#religion').val().trim()) { $('#religion_err').text('Religion is required.'); err = true; }else{$('#religion_err').text('');}
            if (!$('#community').val().trim()) { $('#community_err').text('Community is required.'); err = true; }else{$('#community_err').text('');}
            
            
            
            let pan_no = $('#pan_no').val().trim();
            let aadhar_no = $('#aadhar_no').val().trim();
            if (!$('#aadhar_no').val().trim()) { $('#aadhar_no_err').text('Aadhar number is required.'); err = true; }else if (aadhar_no && !/^[0-9]{12}$/.test(aadhar_no)) { $('#aadhar_no_err').text('Aadhar must be 12 digits..'); err = true; }else{$('#aadhar_no_err').text('');}
            if (!$('#pan_no').val().trim()) { $('#pan_no_err').text('PAN number is required.'); err = true; }else if (!/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(pan_no.toUpperCase())) { $('#pan_no_err').text('Enter valid PAN number.'); err = true; }else{$('#pan_no_err').text('');}
            if ($('#vehicle_check').is(':checked')) {
                if (!$('#driving_license_no').val().trim()) { $('#driving_license_no_err').text('Vehicle Registration No is required.'); err = true; }else{$('#driving_license_no_err').text('');}
                if (!$('#vehicle_register_no').val().trim()) { $('#vehicle_register_no_err').text('Vehicle Registration No is required.'); err = true; }else{$('#vehicle_register_no_err').text('');}
            }

        if (!err) updateAndNext(stage); // ✅ only go next if no validation errors
    }
    // ===== STAGE 2 =====
    else if(stage === 2) {
        if (!$('#father_name').val().trim()) { $('#father_name_err').text('Father Name is required.'); err = true; }else{$('#father_name_err').text('')}
        if (!$('#father_occup').val().trim()) { $('#father_occup_err').text('Father Occupation is required.'); err = true; }else{$('#father_occup_err').text('')}
        if (!$('#mother_name').val().trim()) { $('#mother_name_err').text('Mother Name is required.'); err = true; }else{$('#mother_name_err').text('')}
        if (!$('#mother_occup').val().trim()) { $('#mother_occup_err').text('Mother Occupation is required.'); err = true; }else{$('#mother_occup_err').text('')}
        if (!$('#marital_status').val().trim()) { $('#marital_status_err').text('Marital Status is required.'); err = true; }else{$('#marital_status_err').text('')}
        var marital_status=$('#marital_status').val().trim();
        
        if(marital_status==1){
            // if (!$('#anniversary_date').val().trim()) { $('#anniversary_date_err').text('Anniversary Date is required.'); err = true; }else{$('#anniversary_date_err').text('')}
            // if (!$('#spouse_name').val().trim()) { $('#spouse_name_err').text('Spouse Name is required.'); err = true; }else{$('#spouse_name_err').text('')}
            // if (!$('#spouse_mobile').val().trim()) { $('#spouse_mobile_err').text('Spouse Mobile No is required.'); err = true; }else{$('#spouse_mobile_err').text('')}
            // if (!$('#spouse_dob').val().trim()) { $('#spouse_dob_err').text('Spouse Date Of Birth is required.'); err = true; }else{$('#spouse_dob_err').text('')}
            // if($('#workingYes').prop('checked')){
            //     if (!$('#spouse_designation').val().trim()) { $('#spouse_designation_err').text('Spouse Designation is required.'); err = true; }else{$('#spouse_designation_err').text('')}
            //     if (!$('#spouse_company_name').val().trim()) { $('#spouse_company_name_err').text('Spouse Company Name is required.'); err = true; }else{$('#spouse_company_name_err').text('')}
            //     if (!$('#spouse_salary').val().trim()) { $('#spouse_salary_err').text('Spouse Salary is required.'); err = true; }else{$('#spouse_salary_err').text('')}
            // }
        }
        
        if ($('#childrenYes').prop('checked')) {
            if (!$('#childrenCount').val().trim()) {
                $('#childrenCount_err').text('Children Count is required.');
                err = true;
            } else {
                $('#childrenCount_err').text('');
            }

            // ✅ Validate each dynamically added child field
            $('#childrenDetails .row').each(function (index) {
                const childIndex = index + 1;

                const childNameField = $(this).find('input[name="child_name[]"]');
                const childDobField = $(this).find('input[name="child_dob[]"]');
                const childStdField = $(this).find('input[name="child_std[]"]');
                const childYearField = $(this).find('input[name="child_year[]"]');

                const childName = childNameField.val().trim();
                const childDob = childDobField.val().trim();
                const childStd = childStdField.val().trim();
                const childYear = childYearField.val().trim();

                // Clear previous error messages
                $(this).find('.error_msg').text('');

                if (!childName) {
                    childNameField.closest('.err-chk').find('.error_msg').text('Please enter the child name.');
                    err = true;
                }

                // if (!childDob) {
                //     childDobField.closest('.err-chk').find('.error_msg').text('Please select the date of birth.');
                //     err = true;
                // }

                // if (!childStd) {
                //     childStdField.closest('.err-chk').find('.error_msg').text('Please enter the standard or degree.');
                //     err = true;
                // }

                // if (!childYear) {
                //     childYearField.closest('.err-chk').find('.error_msg').text('Please enter the completion year.');
                //     err = true;
                // }
            });
        }

        if ($('#SiblingsYes').prop('checked')) {
            if (!$('#siblingsCount').val().trim()) {
                $('#siblingsCount_err').text('Sibling Count is required.');
                err = true;
            } else {
                $('#siblingsCount_err').text('');
            }

            // ✅ Validate each dynamically added child field
            $('#siblingDetails .row').each(function (index) {
                const siblingIndex = index + 1;

                const siblingNameField = $(this).find('input[name="sibling_name[]"]');
                const siblingTypeField = $(this).find('select[name="sibling_type[]"]');
                const siblingStdField = $(this).find('input[name="sibling_std[]"]');
                const siblingYearField = $(this).find('input[name="sibling_income[]"]');

                const siblingName = siblingNameField.val().trim();
                const siblingType = siblingTypeField.val();
                const siblingStd = siblingStdField.val().trim();
                const siblingYear = siblingYearField.val().trim();

                // Clear previous error messages
                $(this).find('.error_msg').text('');

                if (!siblingName) {
                    siblingNameField.closest('.err-chk').find('.error_msg').text('Please enter the Sibling name.');
                    err = true;
                }

                if (!siblingType) {
                    siblingTypeField.closest('.err-chk').find('.error_msg').text('Please select the Sibling Type.');
                    err = true;
                }

                // if (!childStd) {
                //     childStdField.closest('.err-chk').find('.error_msg').text('Please enter the standard or degree.');
                //     err = true;
                // }

                // if (!childYear) {
                //     childYearField.closest('.err-chk').find('.error_msg').text('Please enter the completion year.');
                //     err = true;
                // }
            });
        }
        
        // if($('#SiblingsYes').prop('checked')){
        //      if (!$('#siblings_detail').val().trim()) { $('#siblings_detail_err').text('Siblings Detail is required.'); err = true; }else{$('#siblings_detail_err').text('')}
        // }
        

        if (!err) updateAndNext(stage); // ✅ only go next if no validation errors
    }
    // ===== STAGE 3 =====
    else if (stage === 3) {
             if (!$('#permanent_address').val().trim()) { $('#permanent_address_err').text('Permanent Address is required.'); err = true; }else{$('#permanent_address_err').text('')}
               // Validate Contact Person(s)
            $('#altmobile-wrapper .altmobile-row').each(function (index) {
                const nameInput = $(this).find('input[name="contact_person_name[]"]');
                const relationInput = $(this).find('select[name="contact_person_relation[]"]');
                const mobileInput = $(this).find('input[name="contact_person_no[]"]');
                const nameErr = $(this).find('#contact_person_name_err');
                const relationErr = $(this).find('#contact_person_relation_err');
                const mobileErr = $(this).find('#contact_person_no_err');

                let rowErr = false;

                // Name validation
                if (!nameInput.val().trim()) {
                    nameErr.text('Contact person name is required.');
                    rowErr = true;
                } else {
                    nameErr.text('');
                }
                // console.log('cname -',nameInput.val())
                // console.log('crelation -',relationInput.val())
                if (!relationInput.val()) {

                    relationErr.text('Contact person Relation is required.');
                    rowErr = true;
                } else {
                    relationErr.text('');
                }
                // Mobile validation (required + 10 digits)
                const mobileVal = mobileInput.val().trim();
                if (!mobileVal) {
                    mobileErr.text('Contact person mobile No is required.');
                    rowErr = true;
                } else if (!/^\d{10}$/.test(mobileVal)) {
                    mobileErr.text('Enter a valid 10-digit mobile number.');
                    rowErr = true;
                } else {
                    mobileErr.text('');
                }

                if (rowErr) err = true;
            });
        
        if (!err) updateAndNext(stage);
    }
    // ===== STAGE 4 =====
    else if (stage === 4) {
        const socialMediaFields = document.querySelectorAll('.toggle-field');
        let err = false; // ensure err variable starts false here if this block runs independently

        socialMediaFields.forEach(checkbox => {
            const socialMediaId = checkbox.getAttribute('id').replace('checkSocialMedia_', '');
            const urlInput = document.querySelector(`#socialMediaField_${socialMediaId} input`);
            const errorElement = document.querySelector(`#checkSocialMedia_${socialMediaId}_err`);

            // Only validate if checkbox is checked
            if (checkbox.checked) {
                const url = urlInput.value.trim();

                if (url === '') {
                    errorElement.textContent = `${checkbox.nextElementSibling.innerText} URL is required.`;
                    err = true;
                } 
                else if (!isValidURL(url)) {
                    errorElement.textContent = `Please enter a valid ${checkbox.nextElementSibling.innerText} URL.`;
                    err = true;
                } 
                else {
                    errorElement.textContent = '';
                }
            } else {
                // Clear error and input if unchecked
                errorElement.textContent = '';
                urlInput.value = '';
            }
        });

        if (!err) updateAndNext(stage);
    }
    // ===== STAGE 5 =====
    else if (stage === 5) {

         $("#education-wrapper .education-row").each(function (index) {

            const $row = $(this);

            const qualification = $row.find('select[name="qualification_type[]"]');
            const major         = $row.find('select[name="major[]"]');
            const univ          = $row.find('input[name="univ_name[]"]');
            const passYear      = $row.find('input[name="pass_year[]"]');

            const qualificationErr = qualification.closest(".mb-3").find(".error_msg");
            const majorErr         = major.closest(".mb-3").find(".error_msg");
            const univErr          = univ.closest(".mb-3").find(".error_msg");
            const passYearErr      = passYear.closest(".mb-3").find(".error_msg");

            // Clear previous errors
            $row.find(".error_msg").text("");
            qualification.next('.select2').find('.select2-selection')
                .removeClass('is-invalid');

            // Qualifications where major should NOT be required
            const skipMajorFor = ['4', '5', '6', 'Others'];

            // ------------------ Qualification ------------------
            if (!qualification.val()) {
                qualificationErr.text("Qualification Type is required.");
                qualification.next('.select2').find('.select2-selection')
                    .addClass('is-invalid');
                err = true;
            }

            // ------------------ Major ------------------
            if (!skipMajorFor.includes(qualification.val())) {

                // Only validate if visible
                if (!major.val()) {
                    majorErr.text("Major is required.");
                    err = true;
                }

            } else {
                // Clear value if skipped
                major.val("").trigger("change");
            }

            // ------------------ University ------------------
            if (!skipMajorFor.includes(qualification.val())) {
                if (!univ.val() || !univ.val().trim()) {
                    univErr.text("Institute / University Name is required.");
                    err = true;
                }
            }

            // ------------------ Year ------------------
            if (!passYear.val() || !passYear.val().trim()) {
                passYearErr.text("Qualification Year is required.");
                err = true;
            }

        });

        // === Validate "Any Course Completed?" ===
        const courseTag = $("#course_tag");
        const courseTagErr = courseTag.siblings(".error_msg");
        courseTagErr.text("");

        const selectedCourseOption = $('input[name="is_Course"]:checked').val();

        if (!selectedCourseOption) {
            courseTagErr.text("Please select whether any course is completed.");
            err = true;
        } else if (selectedCourseOption === "Yes") {
            if (!courseTag.val() || !courseTag.val().trim()) {
                courseTagErr.text("Please enter the course name.");
                err = true;
            }
        }
       if (!err) updateAndNext(stage);
    }
    // ===== STAGE 6 =====
    else if (stage === 6) {
       
        const workType = $("#work_type");
        const workTypeErr = workType.closest(".col-lg-4").find(".error_msg");

        // ✅ Validate Work Type
        if (!workType.val()) {
            workTypeErr.text("Please select Work Type.");
            err = true;
        }

        // ✅ If Experience selected (value = 2)
        if (workType.val() === "2") {
                const companyshift = $("#total_company_shift");
                const companyshiftErr = companyshift.closest(".col-lg-4").find(".error_msg");
                const totalExpr = $("#total_experience");
                const totalExprErr = totalExpr.closest(".col-lg-4").find(".error_msg");

                // ✅ Validate Work Type
                if (!companyshift.val()) {
                    companyshiftErr.text("Company Shift Count is Required");
                    err = true;
                }
                // ✅ Validate Work Type
                if (!totalExpr.val()) {
                    totalExprErr.text("Total Year of Experience Count is Required");
                    err = true;
                }

            // Validate shifted company fields
            $(".shiftedCompanyField").each(function () {
                const input = $(this).find("input");
                const inputErr = $(this).find(".error_msg");

                if (!input.val().trim()) {
                    inputErr.text("This field is required.");
                    err = true;
                } else {
                    inputErr.text("");
                }
            });

            // Validate each Previous Company Detail row
            $("#work-exp-wrapper .work-exp-row").each(function (index) {
                const row = $(this);
                let rowErr = false;

                // Loop through required inputs
                row.find(".required-field").each(function () {
                    const input = $(this);
                    const inputErr = input.closest(".mb-3").find(".error_msg");

                    if (!input.val().trim()) {
                        inputErr.text("This field is required.");
                        rowErr = true;
                        err = true;
                    } else {
                        inputErr.text("");
                    }
                });

                // ✅ Check start < end date
                const stDate = row.find('input[name="work_st_date[]"]').val().trim();
                const endDate = row.find('input[name="work_end_date[]"]').val().trim();
                const endDateErr = row.find('input[name="work_end_date[]"]').closest(".mb-3").find(".error_msg");

                if (stDate && endDate && new Date(stDate) > new Date(endDate)) {
                    endDateErr.text("End Date must be after Start Date.");
                    rowErr = true;
                    err = true;
                }
            });
        }

        // ✅ Validate Document Attachments
        // $("#document-wrapper .document-row").each(function (index) {
        //     const docType = $(this).find('select[name="doc_type[]"]');
        //     const fileInput = $(this).find('input[type="file"]')[0];
        //     const docTypeErr = docType.closest(".mb-3").find(".error_msg");

        //     if (!docType.val()) {
        //         docTypeErr.text("Please select a Document Type.");
        //         err = true;
        //     } else {
        //         docTypeErr.text("");
        //     }

        //     if (fileInput && fileInput.files.length === 0) {
        //         // Append error message below the dropzone if missing
        //         const fileErrDiv = $(this).find(".dropzone").next(".error_msg");
        //         if (fileErrDiv.length) {
        //             fileErrDiv.text("Please upload a file.");
        //         } else {
        //             $(this).find(".dropzone").after('<div class="text-danger error_msg">Please upload a file.</div>');
        //         }
        //         err = true;
        //     }
        // });

        if (!err) updateAndNext(stage);

        // if (!err){
        //     var staff_name = $('#staff_name').val().trim();
        //     var staff_mobile = $('#mobile_no').val().trim();
        //     var lastFourDigitMobileNo = staff_mobile.slice(-4);
        //     var name_parts = staff_name.split(' '); // Split the name by spaces
        //     var randomNumber = Math.floor(1000 + Math.random() * 9000);
           
        //     // Join all parts except the last part (first name + middle name if present)
        //     var first_name = name_parts.slice(0, -1).join(''); 
            
        //     var last_name = name_parts[name_parts.length - 1]; // Last part is the last name
            
        //     // Construct the username: if the last name is more than one character, just combine the first and last name
        //     var username = first_name.toLowerCase() + (last_name.length === 1 ? '.' + last_name.toLowerCase() : last_name.toLowerCase());
        //     // random passs
        //     // var password = username + '@' + randomNumber;
        //     // mobile no pass
        //     var password = username + '@' + lastFourDigitMobileNo;
        //     // Set the username in the login input field
        //     $('#loginuser_name').val(username);
        //     $('#loginpassword').val(password);
        //     user_name_chk(username)
        //     safeNext(stage);
        // } 

    }
    // ===== STAGE 7 =====
    else if (stage === 7) {
        
        // Get the selected company type (Management or Business)
            const selectedCompanyType = $('input[name="company"]:checked').val();

            
            const management_depart = $("#management_depart");
            const management_user_role = $("#management_user_role");
            const management_division = $("#management_division");
            const management_job_role = $("#management_job_role");
            const staffCompany = $("#staff_company_name");
            const entity_name = $("#entity_name");
            const branch_id = $("#branch_id");
            const business_depart = $("#business_depart");
            const business_user_role = $("#business_user_role");
            const business_division = $("#business_division");
            const business_job_role = $("#business_job_role");
            const loginuser_name = $("#loginuser_name");
            const loginpassword = $("#loginpassword");

            const manageDepartErr = management_depart.closest(".err-chk").find(".error_msg");
            const manageRoleErr = management_user_role.closest(".err-chk").find(".error_msg");
            const managementDivisionErr = management_division.closest(".err-chk").find(".error_msg");
            const managementJobRoleErr = management_job_role.closest(".err-chk").find(".error_msg");
            const staffCompanyErr = staffCompany.closest(".mb-3").find(".error_msg");
            const entityNameErr = entity_name.closest(".mb-3").find(".error_msg");
            const branchIdErr = branch_id.closest(".mb-3").find(".error_msg");
            const businessDepartErr = business_depart.closest(".err-chk").find(".error_msg");
            const businessRoleErr = business_user_role.closest(".err-chk").find(".error_msg");
            const businessDivisionErr = business_division.closest(".err-chk").find(".error_msg");
            const businessJobRoleErr = business_job_role.closest(".err-chk").find(".error_msg");
            const loginUserNameErr = loginuser_name.closest(".err-chk").find(".error_msg");
            const loginPasswordErr = loginpassword.closest(".err-chk").find(".error_msg");

            
            // Clear any previous error messages
            manageDepartErr.text(""); 
            manageRoleErr.text(""); 
            managementDivisionErr.text(""); 
            managementJobRoleErr.text(""); 
            staffCompanyErr.text("");
            entityNameErr.text("");
            branchIdErr.text("");
            businessDepartErr.text(""); 
            businessRoleErr.text(""); 
            businessDivisionErr.text(""); 
            businessJobRoleErr.text(""); 
            loginUserNameErr.text(""); 
            loginPasswordErr.text(""); 


            if (selectedCompanyType == 1) {
                // For Management: Validate the Department field
                if (!management_depart.val() || !management_depart.val().trim()) {
                    manageDepartErr.text("Department is Required.");
                    err = true;  
                } 
                if (!management_division.val() || !management_division.val().trim()) {
                    managementDivisionErr.text("Division is Required.");
                    err = true;  
                } 
                if (!management_job_role.val() || !management_job_role.val().trim()) {
                    managementJobRoleErr.text("Job Role is Required.");
                    err = true; 
                } 

                if ($('#login_access').is(':checked')) {
                    if (!management_user_role.val() || !management_user_role.val().trim()) {
                        manageRoleErr.text("User Role is Required.");
                        err = true;  
                    }
                } 

            } else {
                // For Business: Validate the Company Name field
                if (!staffCompany.val() || staffCompany.val().trim() === '') {
                    staffCompanyErr.text("Company is Required.");
                    err = true;  
                } 
                if (!entity_name.val() || entity_name.val().trim() === '') {
                    entityNameErr.text("Entity is Required.");
                    err = true;  
                } 
                if (!branch_id.val() || branch_id.val().trim() === '') {
                    branchIdErr.text("Branch is Required.");
                    err = true;  
                } 
                if (!business_depart.val() || business_depart.val().trim() === '') {
                    businessDepartErr.text("Department is Required.");
                    err = true;  
                } 
                if (!business_division.val() || business_division.val().trim() === '') {
                    businessDivisionErr.text("Division is Required.");
                    err = true;  
                } 
                if (!business_job_role.val() || business_job_role.val().trim() === '') {
                    businessJobRoleErr.text("Job Role is Required.");
                    err = true;  
                } 
                if ($('#login_access').is(':checked')) {
                    if (!business_user_role.val() || business_user_role.val().trim() === '') {
                        businessRoleErr.text("User Role is Required.");
                        err = true;  
                    }
                }
            }

            // Check other fields like Pseudo Name, Date of Joining, Basic Salary, Per Hour Cost, etc.
            const pseudo_name = $("#pseudo_name");
            const pseudoNameErr = pseudo_name.closest(".mb-3").find(".error_msg");
            pseudoNameErr.text("");

            if (!pseudo_name.val().trim()) {
                 pseudoNameErr.text('Pseudo Name is required');
                err = true;
            }

            const staff_doj = $("#staff_doj");
            const dojErr = staff_doj.closest(".mb-3").find(".error_msg");
            dojErr.text("");

            if (!staff_doj.val()) {
                dojErr.text("Date of Joining is Required.");
                err = true;
            }

            const basic_salary = $("#basic_salary");
            const basicSalaryErr = basic_salary.siblings(".error_msg");
            basicSalaryErr.text("");

            if (!basic_salary.val().trim()) {
                basicSalaryErr.text("Basic Salary is Required.");
                err = true;
            }

            const per_hr_cost = $("#per_hr_cost");
            const perHrCostErr = per_hr_cost.siblings(".error_msg");
            perHrCostErr.text("");

            if (!per_hr_cost.val().trim()) {
                perHrCostErr.text("Per Hour Cost is Required.");
                err = true;
            }

            const skill_tag = $("#skill_tag");
            const skillTagErr = skill_tag.siblings(".error_msg");
            skillTagErr.text("");

            if (!skill_tag.val().trim()) {
                skillTagErr.text("Skill Tag is Required.");
                err = true;
            }

               if ($('#login_access').is(':checked')) {
                if (!loginuser_name.val() || loginuser_name.val().trim() === '') {
                    loginUserNameErr.text("Username is Required.");
                    err = true;  
                } 
                if (!loginpassword.val() || loginpassword.val().trim() === '') {
                    loginPasswordErr.text("Password is Required.");
                    err = true;  
                } 
            }
        if (!err){
           
          
            updateAndNext(stage);
        } 
    }

    else if (stage === 8) {

                const salary_date = $("#salary_date");
                const salary_dateErr = salary_date.closest('.col-lg-4').find('.error_msg');
                salary_dateErr.text("");

                if (!salary_date.val()) {
                    salary_dateErr.text("Salary Date is Required.");
                    err = true;
                }

            let accountType =$('[name="is_multiple_account"]:checked').val() || 0;
            let salaryType =$('[name="salary_type"]:checked').val() || 1;
            let accounts = [];

            if(accountType == 1){
                accounts = collectSalaryAccounts();
                if(accounts.length === 0)
                {
                    toastr.error( 'Please add at least one salary account');
                     err = true;
                }
                let primaryAccounts = accounts.filter( x => parseInt(x.is_primary) === 1);
                if(primaryAccounts.length !== 1){
                    toastr.error('Select exactly one primary account');
                     err = true;
                }

                for(let i = 0; i < accounts.length; i++)
                {
                    if(!accounts[i].salary_company_id){
                        toastr.error('Company is required in row ' + (i + 1));
                        err = true;
                    }
                    if(parseFloat(accounts[i].gross_salary || 0) <= 0){
                        toastr.error('Gross salary must be greater than 0 in row ' + (i + 1));
                         err = true;
                    }
                }
            }else{
                const basic_salary = $("#basic_salary");
                const basicSalaryErr = basic_salary.siblings(".error_msg");
                basicSalaryErr.text("");

                if(basic_salary.val() == 0){
                    basicSalaryErr.text("Salary Must Be Greater Than Zero.");
                    err = true;
                }

                if (!basic_salary.val().trim()) {
                    basicSalaryErr.text("Basic Salary is Required.");
                    err = true;
                }

                const salary_company_id = $("#salary_company_id");
                const salaryCompanyErr = salary_company_id.closest('.col-lg-4').find('.error_msg');
                salaryCompanyErr.text("");

                if (!salary_company_id.val()) {
                    salaryCompanyErr.text("Salary Company is Required.");
                    err = true;
                }
            }


            const payroll_template_sno = $("#payroll_template_sno");
            const payroll_template_snoErr = payroll_template_sno.closest('.col-lg-4').find('.error_msg');
            payroll_template_snoErr.text("");

            if (!payroll_template_sno.val()) {
                payroll_template_snoErr.text("Payroll Template is Required.");
                err = true;
            }

       
            $('.salary-component-row').each(function(index) {
                let row = $(this);
                let componentSno = row.find('.payroll_component_sno').val();
                let componentType = row.find('.component_type').val();
                let calculationType = row.data('calculation-type');
                let percentageValue = parseFloat(row.find('.percentage_value').val()) || 0;
                let calculatedAmount = parseFloat(row.find('.calculated_amount').val()) || 0;
                payrolDetails.push({
                    payroll_component_sno: componentSno,
                    payroll_rule_sno: row.find('.payroll_rule_sno').val(),
                    component_type: componentType,
                    calculation_type: calculationType,
                    percentage_value: percentageValue,
                    calculated_amount: calculatedAmount,
                    fixed_amount: calculatedAmount,
                    include_in_ctc: 1,
                    include_in_gross: componentType === 'earning' ? 1 : 0,
                    include_in_payslip: 1,
                    display_order: index + 1
                });
            });

            if (payrolDetails.length == 0) {
                toastr.error('Please Once again Select Payroll Template');
                 err = true;
            }

        if (!err) updateAndNext(stage);
    }
    // ===== STAGE 8 =====
    else if (stage === 9) {
      
            const applied_position = $("#applied_position");
            const source_id = $("#source_id");
            const interview_company = $("#interview_company");

            const appliedPostionErr = applied_position.closest(".err-chk").find(".error_msg");
            const sourceIdErr = source_id.closest(".err-chk").find(".error_msg");
            const intervCompErr = interview_company.closest(".err-chk").find(".error_msg");

            // 🔹 Clear previous error messages
            $(".error_msg").text("");

            // 🔹 Validate main fields
            if (!applied_position.val() || applied_position.val().length === 0) {
                appliedPostionErr.text("Position is required.");
                err = true;
            }
            if (!source_id.val() || source_id.val().trim() === "") {
                sourceIdErr.text("Source is required.");
                err = true;
            }
            if (!interview_company.val() || interview_company.val().length === 0) {
                intervCompErr.text("Company is required.");
                err = true;
            }

            // 🔹 Validate both dynamic and dependent questions
            $(".dynamic-question, .dependent-question:not(.d-none)").each(function () {
                const label = $(this).find("label").first().text().trim();
                const hasMandatory = $(this).find("span.text-danger").length > 0;
                if (!hasMandatory) return; // skip non-mandatory

                const field = $(this).find(".hrq-field, input, textarea, select").first();
                const fieldType = field.attr("type") || field.prop("tagName").toLowerCase();

                let value = null;
                let isValid = true;

                // --- Text field ---
                if (fieldType === "text") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Text area ---
                else if (fieldType === "textarea") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Date field ---
                else if (fieldType === "date") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Radio buttons ---
                else if (fieldType === "radio") {
                    const name = field.attr("name");
                    if (!$(`input[name='${name}']:checked`).length) isValid = false;
                }

                // --- Checkboxes ---
                else if (fieldType === "checkbox") {
                    const name = field.attr("name");
                    if (!$(`input[name='${name}[]']:checked`).length) isValid = false;
                }

                // --- Select (list_box) ---
                else if (field.prop("tagName").toLowerCase() === "select") {
                    value = field.val();
                    if (!value || value === "") isValid = false;
                }

                // --- File (multiple_images) ---
                else if (fieldType === "file") {
                    const files = field[0].files;
                    if (!files || files.length === 0) isValid = false;
                }

                // --- Show error if invalid ---
                if (!isValid) {
                    err = true;
                    let errorBox = $(this).find(".error_msg");
                    if (errorBox.length === 0) {
                        $(this).append('<div class="text-danger error_msg"></div>');
                        errorBox = $(this).find(".error_msg");
                    }
                    errorBox.text(`${label.replace("*", "").trim()} is required.`);
                } else {
                    $(this).find(".error_msg").text(""); // clear old error
                }
            });



       if (!err) updateAndNext(stage);
    }
    // ===== STAGE 9 =====
    // ===== STAGE 10 =====
    else if (stage === 10) {
        if (!err) {
            var staff_name = $("#staff_name").val().trim();
            $('#create_staff_label').html(staff_name);
            $('#submit_popup').trigger('click');
        } else {
            
        }
    }
  }

  function close_validation_func(stage) {
    let err = false; 

    // ===== STAGE 1 =====
    if (stage === 1) {
            if (!$('#staff_name').val().trim()) { $('#staff_name_err').text('Staff Name is required.'); err = true; }else{$('#staff_name_err').text('');}
            if (!$('#mobile_no').val().trim()) { $('#mobile_no_err').text('Mobile Number is required.'); err = true; }else{$('#mobile_no_err').text('');}
            if (!$('#staff_dob').val().trim()) { $('#staff_dob_err').text('Date Of Birth is required.'); err = true; }else{$('#staff_dob_err').text('');}
            
          const email = $('#email_id').val().trim();
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if(email == ''){
                $('#email_id_err').text('Email Id is required.'); err = true;
            }else if (email !== '' && !emailPattern.test(email)) {
                $('#email_id_err').text('Enter a valid Email ID.');
                err = true;
            }else{
                $('#email_id_err').text('');
            }
            if (!$('#mother_tongue').val().trim()) { $('#mother_tongue_err').text('Mother Tongue is required.'); err = true; }else{$('#mother_tongue_err').text('');}
            if ($('#Languages').val()=="") { $('#Languages_err').text('Language is required.'); err = true; }else{$('#Languages_err').text('');}
            if (!$('#blood_group').val().trim()) { $('#blood_group_err').text('Blood Group is required.'); err = true; }else{$('#blood_group_err').text('');}
            if (!$('#nationality').val().trim()) { $('#nationality_err').text('Nationality is required.'); err = true; }else{$('#nationality_err').text('');}
            if (!$('#religion').val().trim()) { $('#religion_err').text('Religion is required.'); err = true; }else{$('#religion_err').text('');}
            if (!$('#community').val().trim()) { $('#community_err').text('Community is required.'); err = true; }else{$('#community_err').text('');}
            
            
            
            let pan_no = $('#pan_no').val().trim();
            let aadhar_no = $('#aadhar_no').val().trim();
            if (!$('#aadhar_no').val().trim()) { $('#aadhar_no_err').text('Aadhar number is required.'); err = true; }else if (aadhar_no && !/^[0-9]{12}$/.test(aadhar_no)) { $('#aadhar_no_err').text('Aadhar must be 12 digits..'); err = true; }else{$('#aadhar_no_err').text('');}
            if (!$('#pan_no').val().trim()) { $('#pan_no_err').text('PAN number is required.'); err = true; }else if (!/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(pan_no.toUpperCase())) { $('#pan_no_err').text('Enter valid PAN number.'); err = true; }else{$('#pan_no_err').text('');}
            if ($('#vehicle_check').is(':checked')) {
                if (!$('#driving_license_no').val().trim()) { $('#driving_license_no_err').text('Vehicle Registration No is required.'); err = true; }else{$('#driving_license_no_err').text('');}
                if (!$('#vehicle_register_no').val().trim()) { $('#vehicle_register_no_err').text('Vehicle Registration No is required.'); err = true; }else{$('#vehicle_register_no_err').text('');}
            }
        if (!err) updateStaff(stage); // ✅ only go next if no validation errors
    }
    // ===== STAGE 2 =====
    else if(stage === 2) {
        if (!$('#father_name').val().trim()) { $('#father_name_err').text('Father Name is required.'); err = true; }else{$('#father_name_err').text('')}
        if (!$('#father_occup').val().trim()) { $('#father_occup_err').text('Father Occupation is required.'); err = true; }else{$('#father_occup_err').text('')}
        if (!$('#mother_name').val().trim()) { $('#mother_name_err').text('Mother Name is required.'); err = true; }else{$('#mother_name_err').text('')}
        if (!$('#mother_occup').val().trim()) { $('#mother_occup_err').text('Mother Occupation is required.'); err = true; }else{$('#mother_occup_err').text('')}
        if (!$('#marital_status').val().trim()) { $('#marital_status_err').text('Marital Status is required.'); err = true; }else{$('#marital_status_err').text('')}
        var marital_status=$('#marital_status').val().trim();
        
        if(marital_status==1){
            // if (!$('#anniversary_date').val().trim()) { $('#anniversary_date_err').text('Anniversary Date is required.'); err = true; }else{$('#anniversary_date_err').text('')}
            // if (!$('#spouse_name').val().trim()) { $('#spouse_name_err').text('Spouse Name is required.'); err = true; }else{$('#spouse_name_err').text('')}
            // if (!$('#spouse_mobile').val().trim()) { $('#spouse_mobile_err').text('Spouse Mobile No is required.'); err = true; }else{$('#spouse_mobile_err').text('')}
            // if (!$('#spouse_dob').val().trim()) { $('#spouse_dob_err').text('Spouse Date Of Birth is required.'); err = true; }else{$('#spouse_dob_err').text('')}
            // if($('#workingYes').prop('checked')){
            //     if (!$('#spouse_designation').val().trim()) { $('#spouse_designation_err').text('Spouse Designation is required.'); err = true; }else{$('#spouse_designation_err').text('')}
            //     if (!$('#spouse_company_name').val().trim()) { $('#spouse_company_name_err').text('Spouse Company Name is required.'); err = true; }else{$('#spouse_company_name_err').text('')}
            //     if (!$('#spouse_salary').val().trim()) { $('#spouse_salary_err').text('Spouse Salary is required.'); err = true; }else{$('#spouse_salary_err').text('')}
            // }
        }
        
        if ($('#childrenYes').prop('checked')) {
            if (!$('#childrenCount').val().trim()) {
                $('#childrenCount_err').text('Children Count is required.');
                err = true;
            } else {
                $('#childrenCount_err').text('');
            }

            // ✅ Validate each dynamically added child field
            $('#childrenDetails .row').each(function (index) {
                const childIndex = index + 1;

                const childNameField = $(this).find('input[name="child_name[]"]');
                const childDobField = $(this).find('input[name="child_dob[]"]');
                const childStdField = $(this).find('input[name="child_std[]"]');
                const childYearField = $(this).find('input[name="child_year[]"]');

                const childName = childNameField.val().trim();
                const childDob = childDobField.val().trim();
                const childStd = childStdField.val().trim();
                const childYear = childYearField.val().trim();

                // Clear previous error messages
                $(this).find('.error_msg').text('');

                if (!childName) {
                    childNameField.closest('.err-chk').find('.error_msg').text('Please enter the child name.');
                    err = true;
                }

                // if (!childDob) {
                //     childDobField.closest('.err-chk').find('.error_msg').text('Please select the date of birth.');
                //     err = true;
                // }

                // if (!childStd) {
                //     childStdField.closest('.err-chk').find('.error_msg').text('Please enter the standard or degree.');
                //     err = true;
                // }

                // if (!childYear) {
                //     childYearField.closest('.err-chk').find('.error_msg').text('Please enter the completion year.');
                //     err = true;
                // }
            });
        }

        if ($('#SiblingsYes').prop('checked')) {
            if (!$('#siblingsCount').val().trim()) {
                $('#siblingsCount_err').text('Sibling Count is required.');
                err = true;
            } else {
                $('#siblingsCount_err').text('');
            }

            // ✅ Validate each dynamically added child field
            $('#siblingDetails .row').each(function (index) {
                const siblingIndex = index + 1;

                const siblingNameField = $(this).find('input[name="sibling_name[]"]');
                const siblingTypeField = $(this).find('select[name="sibling_type[]"]');
                const siblingStdField = $(this).find('input[name="sibling_std[]"]');
                const siblingYearField = $(this).find('input[name="sibling_income[]"]');

                const siblingName = siblingNameField.val().trim();
                const siblingType = siblingTypeField.val();
                const siblingStd = siblingStdField.val().trim();
                const siblingYear = siblingYearField.val().trim();

                // Clear previous error messages
                $(this).find('.error_msg').text('');

                if (!siblingName) {
                    siblingNameField.closest('.err-chk').find('.error_msg').text('Please enter the Sibling name.');
                    err = true;
                }

                if (!siblingType) {
                    siblingTypeField.closest('.err-chk').find('.error_msg').text('Please select the Sibling Type.');
                    err = true;
                }

                // if (!childStd) {
                //     childStdField.closest('.err-chk').find('.error_msg').text('Please enter the standard or degree.');
                //     err = true;
                // }

                // if (!childYear) {
                //     childYearField.closest('.err-chk').find('.error_msg').text('Please enter the completion year.');
                //     err = true;
                // }
            });
        }
        
        // if($('#SiblingsYes').prop('checked')){
        //      if (!$('#siblings_detail').val().trim()) { $('#siblings_detail_err').text('Siblings Detail is required.'); err = true; }else{$('#siblings_detail_err').text('')}
        // }
        

        if (!err) updateStaff(stage); // ✅ only go next if no validation errors
    }
    // ===== STAGE 3 =====
    else if (stage === 3) {
             if (!$('#permanent_address').val().trim()) { $('#permanent_address_err').text('Permanent Address is required.'); err = true; }else{$('#permanent_address_err').text('')}
               // Validate Contact Person(s)
            $('#altmobile-wrapper .altmobile-row').each(function (index) {
                const nameInput = $(this).find('input[name="contact_person_name[]"]');
                const relationInput = $(this).find('select[name="contact_person_relation[]"]');
                const mobileInput = $(this).find('input[name="contact_person_no[]"]');
                const nameErr = $(this).find('#contact_person_name_err');
                const relationErr = $(this).find('#contact_person_relation_err');
                const mobileErr = $(this).find('#contact_person_no_err');

                let rowErr = false;

                // Name validation
                if (!nameInput.val().trim()) {
                    nameErr.text('Contact person name is required.');
                    rowErr = true;
                } else {
                    nameErr.text('');
                }
                // console.log('cname -',nameInput.val())
                // console.log('crelation -',relationInput.val())
                if (!relationInput.val()) {

                    relationErr.text('Contact person Relation is required.');
                    rowErr = true;
                } else {
                    relationErr.text('');
                }
                // Mobile validation (required + 10 digits)
                const mobileVal = mobileInput.val().trim();
                if (!mobileVal) {
                    mobileErr.text('Contact person mobile No is required.');
                    rowErr = true;
                } else if (!/^\d{10}$/.test(mobileVal)) {
                    mobileErr.text('Enter a valid 10-digit mobile number.');
                    rowErr = true;
                } else {
                    mobileErr.text('');
                }

                if (rowErr) err = true;
            });
        
        if (!err) updateStaff(stage);
    }
    // ===== STAGE 4 =====
    else if (stage === 4) {
        const socialMediaFields = document.querySelectorAll('.toggle-field');
        let err = false; // ensure err variable starts false here if this block runs independently

        socialMediaFields.forEach(checkbox => {
            const socialMediaId = checkbox.getAttribute('id').replace('checkSocialMedia_', '');
            const urlInput = document.querySelector(`#socialMediaField_${socialMediaId} input`);
            const errorElement = document.querySelector(`#checkSocialMedia_${socialMediaId}_err`);

            // Only validate if checkbox is checked
            if (checkbox.checked) {
                const url = urlInput.value.trim();

                if (url === '') {
                    errorElement.textContent = `${checkbox.nextElementSibling.innerText} URL is required.`;
                    err = true;
                } 
                else if (!isValidURL(url)) {
                    errorElement.textContent = `Please enter a valid ${checkbox.nextElementSibling.innerText} URL.`;
                    err = true;
                } 
                else {
                    errorElement.textContent = '';
                }
            } else {
                // Clear error and input if unchecked
                errorElement.textContent = '';
                urlInput.value = '';
            }
        });

        if (!err) updateStaff(stage);
    }
    // ===== STAGE 5 =====
    else if (stage === 5) {

         $("#education-wrapper .education-row").each(function (index) {

            const $row = $(this);

            const qualification = $row.find('select[name="qualification_type[]"]');
            const major         = $row.find('select[name="major[]"]');
            const univ          = $row.find('input[name="univ_name[]"]');
            const passYear      = $row.find('input[name="pass_year[]"]');

            const qualificationErr = qualification.closest(".mb-3").find(".error_msg");
            const majorErr         = major.closest(".mb-3").find(".error_msg");
            const univErr          = univ.closest(".mb-3").find(".error_msg");
            const passYearErr      = passYear.closest(".mb-3").find(".error_msg");

            // Clear previous errors
            $row.find(".error_msg").text("");
            qualification.next('.select2').find('.select2-selection')
                .removeClass('is-invalid');

            // Qualifications where major should NOT be required
            const skipMajorFor = ['4', '5', '6', 'Others'];

            // ------------------ Qualification ------------------
            if (!qualification.val()) {
                qualificationErr.text("Qualification Type is required.");
                qualification.next('.select2').find('.select2-selection')
                    .addClass('is-invalid');
                err = true;
            }

            // ------------------ Major ------------------
            if (!skipMajorFor.includes(qualification.val())) {

                // Only validate if visible
                if (!major.val()) {
                    majorErr.text("Major is required.");
                    err = true;
                }

            } else {
                // Clear value if skipped
                major.val("").trigger("change");
            }

            // ------------------ University ------------------
            if (!skipMajorFor.includes(qualification.val())) {
                if (!univ.val() || !univ.val().trim()) {
                    univErr.text("Institute / University Name is required.");
                    err = true;
                }
            }

            // ------------------ Year ------------------
            if (!passYear.val() || !passYear.val().trim()) {
                passYearErr.text("Qualification Year is required.");
                err = true;
            }

        });

        // === Validate "Any Course Completed?" ===
        const courseTag = $("#course_tag");
        const courseTagErr = courseTag.siblings(".error_msg");
        courseTagErr.text("");

        const selectedCourseOption = $('input[name="is_Course"]:checked').val();

        if (!selectedCourseOption) {
            courseTagErr.text("Please select whether any course is completed.");
            err = true;
        } else if (selectedCourseOption === "Yes") {
            if (!courseTag.val() || !courseTag.val().trim()) {
                courseTagErr.text("Please enter the course name.");
                err = true;
            }
        }
       if (!err) updateStaff(stage);
    }
    // ===== STAGE 6 =====
    else if (stage === 6) {
       
        const workType = $("#work_type");
        const workTypeErr = workType.closest(".col-lg-4").find(".error_msg");

        // ✅ Validate Work Type
        if (!workType.val()) {
            workTypeErr.text("Please select Work Type.");
            err = true;
        }

        // ✅ If Experience selected (value = 2)
        if (workType.val() === "2") {
                const companyshift = $("#total_company_shift");
                const companyshiftErr = companyshift.closest(".col-lg-4").find(".error_msg");
                const totalExpr = $("#total_experience");
                const totalExprErr = totalExpr.closest(".col-lg-4").find(".error_msg");

                // ✅ Validate Work Type
                if (!companyshift.val()) {
                    companyshiftErr.text("Company Shift Count is Required");
                    err = true;
                }
                // ✅ Validate Work Type
                if (!totalExpr.val()) {
                    totalExprErr.text("Total Year of Experience Count is Required");
                    err = true;
                }

            // Validate shifted company fields
            $(".shiftedCompanyField").each(function () {
                const input = $(this).find("input");
                const inputErr = $(this).find(".error_msg");

                if (!input.val().trim()) {
                    inputErr.text("This field is required.");
                    err = true;
                } else {
                    inputErr.text("");
                }
            });

            // Validate each Previous Company Detail row
            $("#work-exp-wrapper .work-exp-row").each(function (index) {
                const row = $(this);
                let rowErr = false;

                // Loop through required inputs
                row.find(".required-field").each(function () {
                    const input = $(this);
                    const inputErr = input.closest(".mb-3").find(".error_msg");

                    if (!input.val().trim()) {
                        inputErr.text("This field is required.");
                        rowErr = true;
                        err = true;
                    } else {
                        inputErr.text("");
                    }
                });

                // ✅ Check start < end date
                const stDate = row.find('input[name="work_st_date[]"]').val().trim();
                const endDate = row.find('input[name="work_end_date[]"]').val().trim();
                const endDateErr = row.find('input[name="work_end_date[]"]').closest(".mb-3").find(".error_msg");

                if (stDate && endDate && new Date(stDate) > new Date(endDate)) {
                    endDateErr.text("End Date must be after Start Date.");
                    rowErr = true;
                    err = true;
                }
            });
        }

        // ✅ Validate Document Attachments
        // $("#document-wrapper .document-row").each(function (index) {
        //     const docType = $(this).find('select[name="doc_type[]"]');
        //     const fileInput = $(this).find('input[type="file"]')[0];
        //     const docTypeErr = docType.closest(".mb-3").find(".error_msg");

        //     if (!docType.val()) {
        //         docTypeErr.text("Please select a Document Type.");
        //         err = true;
        //     } else {
        //         docTypeErr.text("");
        //     }

        //     if (fileInput && fileInput.files.length === 0) {
        //         // Append error message below the dropzone if missing
        //         const fileErrDiv = $(this).find(".dropzone").next(".error_msg");
        //         if (fileErrDiv.length) {
        //             fileErrDiv.text("Please upload a file.");
        //         } else {
        //             $(this).find(".dropzone").after('<div class="text-danger error_msg">Please upload a file.</div>');
        //         }
        //         err = true;
        //     }
        // });

        if (!err) updateStaff(stage);

        // if (!err){
        //     var staff_name = $('#staff_name').val().trim();
        //     var staff_mobile = $('#mobile_no').val().trim();
        //     var lastFourDigitMobileNo = staff_mobile.slice(-4);
        //     var name_parts = staff_name.split(' '); // Split the name by spaces
        //     var randomNumber = Math.floor(1000 + Math.random() * 9000);
           
        //     // Join all parts except the last part (first name + middle name if present)
        //     var first_name = name_parts.slice(0, -1).join(''); 
            
        //     var last_name = name_parts[name_parts.length - 1]; // Last part is the last name
            
        //     // Construct the username: if the last name is more than one character, just combine the first and last name
        //     var username = first_name.toLowerCase() + (last_name.length === 1 ? '.' + last_name.toLowerCase() : last_name.toLowerCase());
        //     // random passs
        //     // var password = username + '@' + randomNumber;
        //     // mobile no pass
        //     var password = username + '@' + lastFourDigitMobileNo;
        //     // Set the username in the login input field
        //     $('#loginuser_name').val(username);
        //     $('#loginpassword').val(password);
        //     user_name_chk(username)
        //     safeNext(stage);
        // } 

    }
    // ===== STAGE 7 =====
    else if (stage === 7) {
        
        // Get the selected company type (Management or Business)
            const selectedCompanyType = $('input[name="company"]:checked').val();

            
            const management_depart = $("#management_depart");
            const management_user_role = $("#management_user_role");
            const management_division = $("#management_division");
            const management_job_role = $("#management_job_role");
            const staffCompany = $("#staff_company_name");
            const entity_name = $("#entity_name");
            const branch_id = $("#branch_id");
            const business_depart = $("#business_depart");
            const business_user_role = $("#business_user_role");
            const business_division = $("#business_division");
            const business_job_role = $("#business_job_role");
            const loginuser_name = $("#loginuser_name");
            const loginpassword = $("#loginpassword");

            const manageDepartErr = management_depart.closest(".err-chk").find(".error_msg");
            const manageRoleErr = management_user_role.closest(".err-chk").find(".error_msg");
            const managementDivisionErr = management_division.closest(".err-chk").find(".error_msg");
            const managementJobRoleErr = management_job_role.closest(".err-chk").find(".error_msg");
            const staffCompanyErr = staffCompany.closest(".mb-3").find(".error_msg");
            const entityNameErr = entity_name.closest(".mb-3").find(".error_msg");
            const branchIdErr = branch_id.closest(".mb-3").find(".error_msg");
            const businessDepartErr = business_depart.closest(".err-chk").find(".error_msg");
            const businessRoleErr = business_user_role.closest(".err-chk").find(".error_msg");
            const businessDivisionErr = business_division.closest(".err-chk").find(".error_msg");
            const businessJobRoleErr = business_job_role.closest(".err-chk").find(".error_msg");
            const loginUserNameErr = loginuser_name.closest(".err-chk").find(".error_msg");
            const loginPasswordErr = loginpassword.closest(".err-chk").find(".error_msg");

            
            // Clear any previous error messages
            manageDepartErr.text(""); 
            manageRoleErr.text(""); 
            managementDivisionErr.text(""); 
            managementJobRoleErr.text(""); 
            staffCompanyErr.text("");
            entityNameErr.text("");
            branchIdErr.text("");
            businessDepartErr.text(""); 
            businessRoleErr.text(""); 
            businessDivisionErr.text(""); 
            businessJobRoleErr.text(""); 
            loginUserNameErr.text(""); 
            loginPasswordErr.text(""); 


            if (selectedCompanyType == 1) {
                // For Management: Validate the Department field
                if (!management_depart.val() || !management_depart.val().trim()) {
                    manageDepartErr.text("Department is Required.");
                    err = true;  
                } 
                if (!management_division.val() || !management_division.val().trim()) {
                    managementDivisionErr.text("Division is Required.");
                    err = true;  
                } 
                if (!management_job_role.val() || !management_job_role.val().trim()) {
                    managementJobRoleErr.text("Job Role is Required.");
                    err = true; 
                } 

                if ($('#login_access').is(':checked')) {
                    if (!management_user_role.val() || !management_user_role.val().trim()) {
                        manageRoleErr.text("User Role is Required.");
                        err = true;  
                    }
                } 

            } else {
                // For Business: Validate the Company Name field
                if (!staffCompany.val() || staffCompany.val().trim() === '') {
                    staffCompanyErr.text("Company is Required.");
                    err = true;  
                } 
                if (!entity_name.val() || entity_name.val().trim() === '') {
                    entityNameErr.text("Entity is Required.");
                    err = true;  
                } 
                if (!branch_id.val() || branch_id.val().trim() === '') {
                    branchIdErr.text("Branch is Required.");
                    err = true;  
                } 
                if (!business_depart.val() || business_depart.val().trim() === '') {
                    businessDepartErr.text("Department is Required.");
                    err = true;  
                } 
                if (!business_division.val() || business_division.val().trim() === '') {
                    businessDivisionErr.text("Division is Required.");
                    err = true;  
                } 
                if (!business_job_role.val() || business_job_role.val().trim() === '') {
                    businessJobRoleErr.text("Job Role is Required.");
                    err = true;  
                } 
                if ($('#login_access').is(':checked')) {
                    if (!business_user_role.val() || business_user_role.val().trim() === '') {
                        businessRoleErr.text("User Role is Required.");
                        err = true;  
                    }
                } 
            }

            // Check other fields like Pseudo Name, Date of Joining, Basic Salary, Per Hour Cost, etc.
            const pseudo_name = $("#pseudo_name");
            const pseudoNameErr = pseudo_name.closest(".mb-3").find(".error_msg");
            pseudoNameErr.text("");

            if (!pseudo_name.val().trim()) {
                 pseudoNameErr.text('Pseudo Name is required');
                err = true;
            }

            const staff_doj = $("#staff_doj");
            const dojErr = staff_doj.closest(".mb-3").find(".error_msg");
            dojErr.text("");

            if (!staff_doj.val()) {
                dojErr.text("Date of Joining is Required.");
                err = true;
            }

            const employee_id = $("#employee_id");
            const employee_idErr = employee_id.siblings(".error_msg");
            employee_idErr.text("");

            if (!employee_id.val().trim()) {
                employee_idErr.text("Employee Id is Required.");
                err = true;
            }

            

            const skill_tag = $("#skill_tag");
            const skillTagErr = skill_tag.siblings(".error_msg");
            skillTagErr.text("");

            if (!skill_tag.val().trim()) {
                skillTagErr.text("Skill Tag is Required.");
                err = true;
            }

            if ($('#login_access').is(':checked')) {
                if (!loginuser_name.val() || loginuser_name.val().trim() === '') {
                    loginUserNameErr.text("Username is Required.");
                    err = true;  
                } 
                if (!loginpassword.val() || loginpassword.val().trim() === '') {
                    loginPasswordErr.text("Password is Required.");
                    err = true;  
                } 
            } 
        if (!err){
          
            updateStaff(stage);
        } 
    }

     else if (stage === 8) {

                const salary_date = $("#salary_date");
                const salary_dateErr = salary_date.closest('.col-lg-4').find('.error_msg');
                salary_dateErr.text("");

                if (!salary_date.val()) {
                    salary_dateErr.text("Salary Date is Required.");
                    err = true;
                }

            let accountType =$('[name="is_multiple_account"]:checked').val() || 0;
            let salaryType =$('[name="salary_type"]:checked').val() || 1;
            let accounts = [];

            if(accountType == 1){
                accounts = collectSalaryAccounts();
                if(accounts.length === 0)
                {
                    toastr.error( 'Please add at least one salary account');
                     err = true;
                }
                let primaryAccounts = accounts.filter( x => parseInt(x.is_primary) === 1);
                if(primaryAccounts.length !== 1){
                    toastr.error('Select exactly one primary account');
                     err = true;
                }

                for(let i = 0; i < accounts.length; i++)
                {
                    if(!accounts[i].salary_company_id){
                        toastr.error('Company is required in row ' + (i + 1));
                        err = true;
                    }
                    if(parseFloat(accounts[i].gross_salary || 0) <= 0){
                        toastr.error('Gross salary must be greater than 0 in row ' + (i + 1));
                         err = true;
                    }
                }
            }else{
                const basic_salary = $("#basic_salary");
                const basicSalaryErr = basic_salary.siblings(".error_msg");
                basicSalaryErr.text("");

                if(basic_salary.val() == 0){
                    basicSalaryErr.text("Salary Must Be Greater Than Zero.");
                    err = true;
                }

                if (!basic_salary.val().trim()) {
                    basicSalaryErr.text("Basic Salary is Required.");
                    err = true;
                }

                const salary_company_id = $("#salary_company_id");
                const salaryCompanyErr = salary_company_id.closest('.col-lg-4').find('.error_msg');
                salaryCompanyErr.text("");

                if (!salary_company_id.val()) {
                    salaryCompanyErr.text("Salary Company is Required.");
                    err = true;
                }
            }


            const payroll_template_sno = $("#payroll_template_sno");
            const payroll_template_snoErr = payroll_template_sno.closest('.col-lg-4').find('.error_msg');
            payroll_template_snoErr.text("");

            if (!payroll_template_sno.val()) {
                payroll_template_snoErr.text("Payroll Template is Required.");
                err = true;
            }

       
            $('.salary-component-row').each(function(index) {
                let row = $(this);
                let componentSno = row.find('.payroll_component_sno').val();
                let componentType = row.find('.component_type').val();
                let calculationType = row.data('calculation-type');
                let percentageValue = parseFloat(row.find('.percentage_value').val()) || 0;
                let calculatedAmount = parseFloat(row.find('.calculated_amount').val()) || 0;
                payrolDetails.push({
                    payroll_component_sno: componentSno,
                    payroll_rule_sno: row.find('.payroll_rule_sno').val(),
                    component_type: componentType,
                    calculation_type: calculationType,
                    percentage_value: percentageValue,
                    calculated_amount: calculatedAmount,
                    fixed_amount: calculatedAmount,
                    include_in_ctc: 1,
                    include_in_gross: componentType === 'earning' ? 1 : 0,
                    include_in_payslip: 1,
                    display_order: index + 1
                });
            });

            if (payrolDetails.length == 0) {
                toastr.error('Please Once again Select Payroll Template');
                 err = true;
            }

       if (!err) updateStaff(stage);
    }
    // ===== STAGE 8 =====
   
    // ===== STAGE 9 =====
    else if (stage === 9) {

            const applied_position = $("#applied_position");
            const source_id = $("#source_id");
            const interview_company = $("#interview_company");

            const appliedPostionErr = applied_position.closest(".err-chk").find(".error_msg");
            const sourceIdErr = source_id.closest(".err-chk").find(".error_msg");
            const intervCompErr = interview_company.closest(".err-chk").find(".error_msg");

            // 🔹 Clear previous error messages
            $(".error_msg").text("");

            // 🔹 Validate main fields
            if (!applied_position.val() || applied_position.val().length === 0) {
                appliedPostionErr.text("Position is required.");
                err = true;
            }
            if (!source_id.val() || source_id.val().trim() === "") {
                sourceIdErr.text("Source is required.");
                err = true;
            }
            if (!interview_company.val() || interview_company.val().length === 0) {
                intervCompErr.text("Company is required.");
                err = true;
            }

            // 🔹 Validate both dynamic and dependent questions
            $(".dynamic-question, .dependent-question:not(.d-none)").each(function () {
                const label = $(this).find("label").first().text().trim();
                const hasMandatory = $(this).find("span.text-danger").length > 0;
                if (!hasMandatory) return; // skip non-mandatory

                const field = $(this).find(".hrq-field, input, textarea, select").first();
                const fieldType = field.attr("type") || field.prop("tagName").toLowerCase();

                let value = null;
                let isValid = true;

                // --- Text field ---
                if (fieldType === "text") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Text area ---
                else if (fieldType === "textarea") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Date field ---
                else if (fieldType === "date") {
                    value = field.val().trim();
                    if (!value) isValid = false;
                }

                // --- Radio buttons ---
                else if (fieldType === "radio") {
                    const name = field.attr("name");
                    if (!$(`input[name='${name}']:checked`).length) isValid = false;
                }

                // --- Checkboxes ---
                else if (fieldType === "checkbox") {
                    const name = field.attr("name");
                    if (!$(`input[name='${name}[]']:checked`).length) isValid = false;
                }

                // --- Select (list_box) ---
                else if (field.prop("tagName").toLowerCase() === "select") {
                    value = field.val();
                    if (!value || value === "") isValid = false;
                }

                // --- File (multiple_images) ---
                else if (fieldType === "file") {
                    const files = field[0].files;
                    if (!files || files.length === 0) isValid = false;
                }

                // --- Show error if invalid ---
                if (!isValid) {
                    err = true;
                    let errorBox = $(this).find(".error_msg");
                    if (errorBox.length === 0) {
                        $(this).append('<div class="text-danger error_msg"></div>');
                        errorBox = $(this).find(".error_msg");
                    }
                    errorBox.text(`${label.replace("*", "").trim()} is required.`);
                } else {
                    $(this).find(".error_msg").text(""); // clear old error
                }
            });
      

        if (!err) updateStaff(stage);
    }

    // ===== STAGE 10 =====
    else if (stage === 10) {
        if (!err) {
            var staff_name = $("#staff_name").val().trim();
            $('#create_staff_label').html(staff_name);
            $('#submit_popup').trigger('click');
        } else {
            
        }
    }
  }

function safeNext(stage) {
    // console.log(`== SafeNext(${stage}) ==`);
    // console.log("Before .to():", stepper._currentIndex);

    // 🩹 Force Stepper to truly reset to this stage
    stepper.to(stage);     // ensure we're at the right step
    stepper.to(stage);     // call twice to force internal sync

    // console.log("After double .to():", stepper._currentIndex);

    // Now advance one step
    stepper.next();

    const nextStage = stage + 1;
    const nextStepId = stepIds[nextStage - 1];
    if (nextStepId) {
        // console.log(`🎯 Activating next step: ${nextStepId}`);
        updateStepPercentage(nextStepId);
    }

    // console.log("After .next():", stepper._currentIndex);
}
function safePrev(stage) {
    // console.log(`== SafePrev(${stage}) ==`);
    // console.log("Before .to():", stepper._currentIndex);

    // Go back one stage safely
    stepper.to(stage - 1);

    // console.log("After .to():", stepper._currentIndex);
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
  
        const faviconFileInput = document.querySelector('.fav_file-in');
        const faviconResetButton = document.querySelector('.fav_file-reset');
        const faviconImage = document.getElementById('logo_create');  // The image element

        // Get the original image URL
        const originalImageSrc = faviconImage.src;

        // Function to reset favicon image and input
        faviconResetButton.addEventListener('click', function() {
            // Reset image source to the original or default image
            faviconImage.src = originalImageSrc;

            // Reset file input value to null
            faviconFileInput.value = null;
        });

        // Function to preview the image on upload
        faviconFileInput.addEventListener('change', function() {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    faviconImage.src = e.target.result;  // Set previewed image
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>

    <script>
        

        $(document).ready(function() {

            function updateContactLabels() {
                updateStepPercentage(3);
                $("#altmobile-wrapper .altmobile-row").each(function(index) {
                    const personLabel = $(this).find('label[for="contact_person_name"]');
                    const relationLabel = $(this).find('label[for="contact_person_relation"]');
                    const mobileLabel = $(this).find('label[for="contact_person_no"]');
                    if (index === 0) {
                        personLabel.html('Contact Person <span class="text-danger">*</span>');
                        relationLabel.html('Contact Person Relation<span class="text-danger">*</span>');
                        mobileLabel.html('Contact Person Mobile No. <span class="text-danger">*</span>');
                        $(this).find('.altmobile_del').hide(); // hide delete for first row
                    } else {
                        personLabel.html('Contact Person ' + (index + 1) + ' <span class="text-danger">*</span>');
                         relationLabel.html('Contact Person ' + (index + 1) + ' Relation<span class="text-danger">*</span>');
                        mobileLabel.html('Contact Person ' + (index + 1) + ' Mobile No. <span class="text-danger">*</span>');
                        $(this).find('.altmobile_del').show(); // show delete for added rows
                    }
                });
            }

            // Hide all delete buttons first
            $("#altmobile-wrapper .altmobile_del").hide();

            // Initial label setup
            updateContactLabels();

            // Add new contact row
            $("#add-altmobile-btn").click(function() {
                const clone = $(".altmobile-row:first").clone();
                clone.find("input").val(""); // clear input values
                clone.find('.select2-container').remove();
                $("#altmobile-wrapper").append(clone);
                const newSelect = $("#altmobile-wrapper .altmobile-row:last").find('.select3');
                newSelect.select2({
                    dropdownParent: newSelect.closest('.col-lg-4'), // parent column
                    width: '100%'
                });
                
                updateContactLabels();
               
            });

            // Delete a contact row
            $(document).on("click", ".altmobile_del", function() {
                $(this).closest(".altmobile-row").remove();
                updateContactLabels();
               
            });

            // Initialize Select2 for the first dropdown
            $('.select3').select2({
                dropdownParent: $('.select3').closest('.col-lg-4'),
                width: '100%'
            });
            

        });

        $(document).ready(function () {
            // If old data exists, load rows
            if (existingContacts.names.length > 0) {

                // First row → fill old data
                $(".altmobile-row:first").find('#contact_person_name').val(existingContacts.names[0]);
                $(".altmobile-row:first").find('#contact_person_relation').val(existingContacts.relations[0]).trigger('change');
                $(".altmobile-row:first").find('#contact_person_no').val(existingContacts.numbers[0]);

                // Remaining old rows → clone new rows
                for (let i = 1; i < existingContacts.names.length; i++) {
                    const clone = $(".altmobile-row:first").clone();

                    clone.find("input").val("");
                    clone.find(".select2-container").remove();

                    // Fill cloned row values
                    clone.find('#contact_person_name').val(existingContacts.names[i]);
                    clone.find('#contact_person_relation').val(existingContacts.relations[i]);
                    clone.find('#contact_person_no').val(existingContacts.numbers[i]);

                    // Append the row
                    $("#altmobile-wrapper").append(clone);

                    // Reinitialize Select2
                    const newSelect = $("#altmobile-wrapper .altmobile-row:last").find('.select3');
                    newSelect.select2({
                        dropdownParent: newSelect.closest('.col-lg-4'),
                        width: '100%'
                    });
                }
            }

            function updateContactLabels() {
                updateStepPercentage(3);
                $("#altmobile-wrapper .altmobile-row").each(function(index) {
                    const personLabel = $(this).find('label[for="contact_person_name"]');
                    const relationLabel = $(this).find('label[for="contact_person_relation"]');
                    const mobileLabel = $(this).find('label[for="contact_person_no"]');
                    if (index === 0) {
                        personLabel.html('Contact Person <span class="text-danger">*</span>');
                        relationLabel.html('Contact Person Relation<span class="text-danger">*</span>');
                        mobileLabel.html('Contact Person Mobile No. <span class="text-danger">*</span>');
                        $(this).find('.altmobile_del').hide(); // hide delete for first row
                    } else {
                        personLabel.html('Contact Person ' + (index + 1) + ' <span class="text-danger">*</span>');
                         relationLabel.html('Contact Person ' + (index + 1) + ' Relation<span class="text-danger">*</span>');
                        mobileLabel.html('Contact Person ' + (index + 1) + ' Mobile No. <span class="text-danger">*</span>');
                        $(this).find('.altmobile_del').show(); // show delete for added rows
                    }
                });
            }

            updateContactLabels();
        });
        
      
        document.addEventListener("DOMContentLoaded", function() {
            const progressBar = document.getElementById('companyProgressBar');
            const container = document.getElementById('companydetails');

            // Track user-modified fields
            const userFilled = new Set();

            // Show Management fields by default
            const managementRadio = document.getElementById('management');
            if (managementRadio.checked) {
                container.querySelectorAll('.management_div').forEach(d => d.classList.remove('d-none'));
                container.querySelectorAll('.business_div').forEach(d => d.classList.add('d-none'));
            }

            // Mark a field as user-filled on interaction
            function markUserFilled(e) {
                userFilled.add(e.target);
            }

            // Attach listeners
            container.querySelectorAll('.required-field').forEach(field => {
                field.addEventListener('input', markUserFilled);
                field.addEventListener('change', markUserFilled);

                // For Select2 or datepickers
                if ($(field).hasClass('select3') || $(field).hasClass('common_datepicker') || $(field)
                    .hasClass('datepicker')) {
                    $(field).on('change', e => markUserFilled({
                        target: field
                    }));
                }
            });

            // Radio show/hide logic
            container.querySelectorAll('input[name="company"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.id === 'management') {
                        container.querySelectorAll('.management_div').forEach(d => d.classList
                            .remove('d-none'));
                        container.querySelectorAll('.business_div').forEach(d => d.classList.add(
                            'd-none'));
                    } else {
                        container.querySelectorAll('.business_div').forEach(d => d.classList.remove(
                            'd-none'));
                        container.querySelectorAll('.management_div').forEach(d => d.classList.add(
                            'd-none'));
                    }
                    userFilled.add(this);
                });
            });
           
        });
    </script>
    <script>
        let company_type = {{$staffData->company_type ?? 1}};
        if(company_type == 1){
            staffDepartChange();
        }else{
            staffCompanyChange();
        }
        
        
        function staffCompanyChange(){
                var countryId = $('#staff_company_name').val();
                var stateDropdown = $('#entity_name');
                var entity_id ={{$staffData->entity_id ?? 0}};

                stateDropdown.empty().append('<option value="">Select Entity</option>');

                if (countryId) {
                    // Fetch and populate states based on selected country
                    $.ajax({
                        url: "{{ route('entity_list') }}",
                        type: "GET",
                        data: {
                            company_id: countryId
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    stateDropdown.append($('<option></option>').attr(
                                        'value', state.sno).text(state
                                        .entity_name));
                                });
                                $('#entity_name').val(entity_id).change();
                                // staffEntityChange();
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching states:', error);
                        }
                    });
                }
            
        }

        // function staffEntityChange(){
        //     var entity_id = $('#entity_name').val();
        //     var stateDropdown = $('#business_depart');
        //     var branchDropdown = $('#branch_id');
        //     var roleDropdown = $('#business_user_role');
        //     var branch_id ={{$staffData->branch_id ?? 0}};
        //     var depart_id ={{$staffData->department_id ?? 0}};
        //     var role_id ={{$staffData->role_id ?? 0}};

        //     stateDropdown.empty().append('<option value="">Select Department</option>');
        //     branchDropdown.empty().append('<option value="">Select Branch</option>');
        //     roleDropdown.empty().append('<option value="">Select User Role</option>');

        //     if (entity_id) {
        //         // Fetch and populate states based on selected country
        //         $.ajax({
        //             url: "{{ route('department') }}",
        //             type: "GET",
        //             data: {
        //                 entity_id: entity_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(state) {
        //                         stateDropdown.append($('<option></option>').attr(
        //                             'value', state.sno)
        //                             .attr('data-erpdepartmentid', state.erp_department_id)
        //                             .text(state.department_name));
        //                     });
        //                   $('#business_depart').val(depart_id).change();  
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching Department:', error);
        //             }
        //         });

        //         $.ajax({
        //             url: "{{ route('entity_branch_dropdown_list') }}",
        //             type: "GET",
        //             data: {
        //                 entity_id: entity_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(state) {
        //                         branchDropdown.append($('<option></option>').attr(
        //                             'value', state.sno)
        //                             .attr('data-erpbranchid', state.erp_branch_id)
        //                             .text(state.branch_name));
        //                     });
        //                     $('#branch_id').val(branch_id).change();
        //                     staffBusinessDepartChange();
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching Department:', error);
        //             }
        //         });

        //         $.ajax({
        //             url: "{{ route('user_role_by_entity') }}",
        //             type: "GET",
        //             data: {
        //                 entity_id: entity_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(state) {
        //                         roleDropdown.append($('<option></option>').attr(
        //                             'value', state.sno)
        //                             .attr('data-erproleid', state.erp_role_id)
        //                             .attr('data-erpunderroleid', state.erp_under_role_id)
        //                             .text(state.role_name));
        //                     });
        //                     $('#business_user_role').val(role_id).change();
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching Role:', error);
        //             }
        //         });

        //     }

            
        // }

        // function staffBusinessDepartChange() {
        //     var department_id = $('#business_depart').val();
        //     var stateDropdown = $('#business_division');
        //     stateDropdown.empty().append('<option value="">Select Division</option>');

        //     let erp_depert = $('#business_depart').find(':selected').data('erpdepartmentid');
        //     $('#erp_department_id').val(erp_depert);
        //     var division_id ={{$staffData->division_id ?? 0}};
        //     if (department_id) {
        //         // Fetch and populate states based on selected country
        //         $.ajax({
        //             url: "{{ route('get_division') }}",
        //             type: "GET",
        //             data: {
        //                 department_id: department_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(state) {
        //                         stateDropdown.append($('<option></option>').attr(
        //                             'value', state.sno)
        //                                 .attr('data-erpdivisionid', state.erp_division_id)
        //                             .text(state.division_name));
        //                     });
        //                      $('#business_division').val(division_id).change();
        //                      staffBusinessDivisionChange();
                            
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching Division:', error);
        //             }
        //         });
                    
        //     }
        // }

        // function staffBusinessDivisionChange() {
        //     var department_id = $('#business_division').val();
        
        //     var jobRoleDropdown = $('#business_job_role');
        //     var job_role_id ={{$staffData->job_role_id ?? 0}};

        //     jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');

        //     let erp_depert = $('#business_division').find(':selected').data('erpdivisionid');
        //     $('#erp_division_id').val(erp_depert);

        //     if (department_id) {
        //         // Fetch and populate states based on selected country
        //             // Job role dropdown
        //             $.ajax({
        //             url: "{{ route('get_job_role') }}",
        //             type: "GET",
        //             data: {
        //                 division_id: department_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(state) {
        //                         jobRoleDropdown.append($('<option></option>').attr(
        //                             'value', state.sno)
        //                                 .attr('data-erpjobroleid', state.erp_job_role_id)
        //                             .text(state.job_position_name));
        //                     });
        //                     $('#business_job_role').val(job_role_id).change();
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching Job Role:', error);
        //             }
        //         });
        //     }
        // }

        function staffDepartChange() {
            console.log('chcghg')
            var department_id = $('#management_depart').val();
            var stateDropdown = $('#management_division');
            var jobRoleDropdown = $('#management_job_role');
            var division_id ={{$staffData->division_id ?? 0}};
           
            
            

            stateDropdown.empty().append('<option value="">Select Division</option>');
            jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');

            if (department_id) {
                // Fetch and populate states based on selected country
                $.ajax({
                    url: "{{ route('get_division') }}",
                    type: "GET",
                    data: {
                        department_id: department_id
                    },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(state) {
                                stateDropdown.append($('<option></option>').attr(
                                    'value', state.sno).text(state
                                    .division_name));
                            });
                            $('#management_division').val(division_id).change();
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Division:', error);
                    }
                });

                // Fetch and populate Job Role
                // $.ajax({
                //     url: "{{ route('get_job_role') }}",
                //     type: "GET",
                //     data: {
                //         department_id: department_id
                //     },
                //     success: function(response) {
                //         if (response.status === 200 && response.data) {
                //             response.data.forEach(function(state) {
                //                 jobRoleDropdown.append($('<option></option>').attr(
                //                     'value', state.sno).text(state
                //                     .job_position_name));
                //             });
                //             $('#management_job_role').val(job_role_id).change();
                //         }
                //     },
                //     error: function(error) {
                //         console.error('Error fetching Job Role:', error);
                //     }
                // });

            }
        }

        // function staffDivisionChange() {
        //     var department_id = $('#management_division').val();
        //     var jobRoleDropdown = $('#management_job_role');
        //     jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');
        //         var job_role_id ={{$staffData->job_role_id ?? 0}};
        //     if (department_id) {

        //         // Fetch and populate Job Role
        //         $.ajax({
        //             url: "{{ route('get_job_role') }}",
        //             type: "GET",
        //             data: {
        //                 division_id: department_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(state) {
        //                         jobRoleDropdown.append($('<option></option>').attr(
        //                             'value', state.sno).text(state
        //                             .job_position_name));
        //                     });
        //                     $('#management_job_role').val(job_role_id).change();
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching Job Role:', error);
        //             }
        //         });

        //     }
        // }
        
    $(document).ready(function() {
            // Business dropdown
            $('#staff_company_name').on('change', function() {
                var countryId = $(this).val();
                var stateDropdown = $('#entity_name');

                stateDropdown.empty().append('<option value="">Select Entity</option>');

                if (countryId) {
                    // Fetch and populate states based on selected country
                    $.ajax({
                        url: "{{ route('entity_list') }}",
                        type: "GET",
                        data: {
                            company_id: countryId
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    stateDropdown.append($('<option></option>').attr(
                                        'value', state.sno).text(state
                                        .entity_name));
                                });
                                
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching states:', error);
                        }
                    });
                }
            });
            // depart list
            $('#entity_name').on('change', function() {
                var entity_id = $(this).val();
                var stateDropdown = $('#business_depart');
                var branchDropdown = $('#branch_id');
                var roleDropdown = $('#business_user_role');
                var branch_id ={{$staffData->branch_id ?? 0}};
                var depart_id ={{$staffData->department_id ?? 0}};
                var role_id ={{$staffData->role_id ?? 0}};
                appliedPostionDropdown();

                stateDropdown.empty().append('<option value="">Select Department</option>');
                branchDropdown.empty().append('<option value="">Select Branch</option>');
                roleDropdown.empty().append('<option value="">Select User Role</option>');

                if (entity_id) {
                    // Fetch and populate states based on selected country
                    $.ajax({
                        url: "{{ route('entity_branch_dropdown_list') }}",
                        type: "GET",
                        data: {
                            entity_id: entity_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    branchDropdown.append($('<option></option>').attr(
                                        'value', state.sno)
                                        .attr('data-erpbranchid', state.erp_branch_id)
                                        .text(state.branch_name));
                                });
                                if(branch_id && branch_id >0 ){
                                    $('#branch_id').val(branch_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Department:', error);
                        }
                    });

                    $.ajax({
                        url: "{{ route('department') }}",
                        type: "GET",
                        data: {
                            entity_id: entity_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    stateDropdown.append($('<option></option>').attr(
                                        'value', state.sno)
                                        .attr('data-erpdepartmentid', state.erp_department_id)
                                        .text(state.department_name));
                                });
                                if(depart_id && depart_id >0 ){
                                    $('#business_depart').val(depart_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Department:', error);
                        }
                    });

                    $.ajax({
                        url: "{{ route('user_role_by_entity') }}",
                        type: "GET",
                        data: {
                            entity_id: entity_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    roleDropdown.append($('<option></option>').attr(
                                        'value', state.sno)
                                        .attr('data-erproleid', state.erp_role_id)
                                        .attr('data-erpunderroleid', state.erp_under_role_id)
                                        .text(state.role_name));
                                });
                                if(role_id && role_id >0 ){
                                    $('#business_user_role').val(role_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Role:', error);
                        }
                    });

                }

                
            });
            
            // user Role change
            $('#business_user_role').on('change', function() {
            let erp_role = $(this).find(':selected').data('erproleid');
            let erp_under_role = $(this).find(':selected').data('erpunderroleid');
                $('#erp_role_id').val(erp_role);
                $('#erp_under_role_id').val(erp_role);
            });

            //  branch Chnage
            $('#branch_id').on('change', function() {
            let erp_branch = $(this).find(':selected').data('erpbranchid');
                $('#erp_branch_id').val(erp_branch);
            });

            //  branch Chnage
            $('#business_job_role').on('change', function() {
            let erp_branch = $(this).find(':selected').data('erpjobroleid');
                $('#erp_job_role_id').val(erp_branch);
            });

            // division dropdown
            $('#business_depart').on('change', function() {
                var department_id = $(this).val();
                var stateDropdown = $('#business_division');
                stateDropdown.empty().append('<option value="">Select Division</option>');
                var division_id ={{$staffData->division_id ?? 0}};
                let erp_depert = $(this).find(':selected').data('erpdepartmentid');
                $('#erp_department_id').val(erp_depert);

                if (department_id) {
                    // Fetch and populate states based on selected country
                    $.ajax({
                        url: "{{ route('get_division') }}",
                        type: "GET",
                        data: {
                            department_id: department_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    stateDropdown.append($('<option></option>').attr(
                                        'value', state.sno)
                                         .attr('data-erpdivisionid', state.erp_division_id)
                                        .text(state.division_name));
                                });
                                if(division_id && division_id >0 ){
                                    $('#business_division').val(division_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Division:', error);
                        }
                    });
                       
                }
            });

            $('#business_division').on('change', function() {
                var department_id = $(this).val();
           
                var jobRoleDropdown = $('#business_job_role');
                var job_role_id ={{$staffData->job_role_id ?? 0}};
                jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');

                let erp_depert = $(this).find(':selected').data('erpdivisionid');
                $('#erp_division_id').val(erp_depert);

                if (department_id) {
                    // Fetch and populate states based on selected country
                        // Job role dropdown
                      $.ajax({
                        url: "{{ route('get_job_role') }}",
                        type: "GET",
                        data: {
                            division_id: department_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    jobRoleDropdown.append($('<option></option>').attr(
                                        'value', state.sno)
                                         .attr('data-erpjobroleid', state.erp_job_role_id)
                                        .text(state.job_position_name));
                                });
                                if(job_role_id && job_role_id >0 ){
                                    $('#business_job_role').val(job_role_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Job Role:', error);
                        }
                    });
                }
            });

           
           

        // management dropdown
            // division dropdown
             $('#management_depart').on('change', function() {
                var department_id = $(this).val();
                var stateDropdown = $('#management_division');
                var jobRoleDropdown = $('#management_job_role');
                var division_id ={{$staffData->division_id ?? 0}};
                

                stateDropdown.empty().append('<option value="">Select Division</option>');
                jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');

                if (department_id) {
                    // Fetch and populate states based on selected country
                    $.ajax({
                        url: "{{ route('get_division') }}",
                        type: "GET",
                        data: {
                            department_id: department_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    stateDropdown.append($('<option></option>').attr(
                                        'value', state.sno).text(state
                                        .division_name));
                                });
                                if(division_id && division_id >0 ){
                                    $('#management_division').val(division_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Division:', error);
                        }
                    });

                    // Fetch and populate Job Role
                    // $.ajax({
                    //     url: "{{ route('get_job_role') }}",
                    //     type: "GET",
                    //     data: {
                    //         department_id: department_id
                    //     },
                    //     success: function(response) {
                    //         if (response.status === 200 && response.data) {
                    //             response.data.forEach(function(state) {
                    //                 jobRoleDropdown.append($('<option></option>').attr(
                    //                     'value', state.sno).text(state
                    //                     .job_position_name));
                    //             });
                                
                    //         }
                    //     },
                    //     error: function(error) {
                    //         console.error('Error fetching Job Role:', error);
                    //     }
                    // });

                }
             });

             $('#management_division').on('change', function() {
                var department_id = $(this).val();
                var jobRoleDropdown = $('#management_job_role');
                jobRoleDropdown.empty().append('<option value="">Select Job Role</option>');
                var job_role_id ={{$staffData->job_role_id ?? 0}};
                if (department_id) {

                    // Fetch and populate Job Role
                    $.ajax({
                        url: "{{ route('get_job_role') }}",
                        type: "GET",
                        data: {
                            division_id: department_id
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(state) {
                                    jobRoleDropdown.append($('<option></option>').attr(
                                        'value', state.sno).text(state
                                        .job_position_name));
                                });
                                if(job_role_id && job_role_id >0 ){
                                    $('#management_job_role').val(job_role_id).change();  
                                }
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Job Role:', error);
                        }
                    });

                }
             });

            
    });
    </script>

    <script>
        function isValidURL(str) {
            const pattern = /^(https?:\/\/)?([\w-]+\.)+[\w-]{2,}(\/[\w- ./?%&=]*)?$/i;
            return pattern.test(str);
        }
    </script>
     <script>
      document.addEventListener("DOMContentLoaded", function () {
         let SkillTagList = @json($skillTagList);
         let CourseTagList = @json($CourseTag);
        const tagInputs = document.querySelectorAll(".course_tag");

        const whitelist = CourseTagList;

        tagInputs.forEach((input) => {
            new Tagify(input, {
                whitelist: whitelist,
                maxTags: 10,
                dropdown: {
                    maxItems: 20,
                    classname: "tags-inline",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
        });

        const tagInputsSkill = document.querySelectorAll(".skill_tag");
        // console.log('CourseTagList' ,CourseTagList)
        // console.log('skillTag' ,SkillTagList)
        const whitelistSkill = SkillTagList;

        tagInputsSkill.forEach((input) => {
            new Tagify(input, {
                whitelist: whitelistSkill,
                maxTags: 10,
                dropdown: {
                    maxItems: 20,
                    classname: "tags-inline",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
        });
      });
    </script>

  

    <script>
        function mobile_chk(val) {
             var id = '{{ $staffData->sno ?? '' }}';
            if (val != "") {
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                $.ajax({
                    url: "{{ route('checkunique_mobile_edit') }}",
                    type: 'POST',
                    data: { mobile: val ,id: id},
                    dataType: 'json',
                    success: function(response) {
                        // console.log(response);
                        if (response.data !== 0) {
                            $('#mobile_no_err').text('Mobile Number is already assigned!');
                            $('#mobile_no').addClass('err_border');
                            $('#stage1').prop('disabled', true);
                            $('#updateClose1').prop('disabled', true);
                        } else {
                            $('#mobile_no_err').text('');
                            $('#mobile_no').removeClass('err_border');
                            $('#stage1').prop('disabled', false);
                            $('#updateClose1').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#stage1').prop('disabled', true);
                        $('#updateClose1').prop('disabled', true);
                        
                    }
                });
            }
        }
    </script>


    <script>
        // Enable drag-to-scroll for all .file-previews containers
        $(document).on('mousedown', '.file-previews', function(e) {
            const el = this;
            let startX = e.pageX - el.offsetLeft;
            let scrollLeft = el.scrollLeft;

            let isDown = true;
            $(el).addClass('active');
            $(document).on('mouseup.dragscroll', function() {
                isDown = false;
                $(document).off('.dragscroll');
                $(el).removeClass('active');
            });
        });
    </script>

  <script>
$(document).ready(function () {

    // ✅ Initialize Select2 / Select3 first
    // ✅ Handle radio or select change dynamically
    $(document).on('change', '.hrq-field', function () {
        const qid = $(this).data('question');
        const val = $(this).val();

        // Loop through all dependents linked to this parent question
        $(`.dependent-question[data-parent='${qid}']`).each(function () {
            const triggers = JSON.parse($(this).attr('data-trigger'));
            const match = triggers.some(t => 
                t.label.trim().toLowerCase() === val.trim().toLowerCase()
            );

            // Toggle visibility
            $(this).toggleClass('d-none', !match);
        });
    });

    // ✅ Wait for all dynamic DOM elements to render
    setTimeout(() => {
        // Trigger change manually for all default checked radios
        $('.hrq-field[type="radio"]:checked').each(function () {
            $(this).trigger('change');
             
        });
    }, 100); // small delay ensures dependents exist

});

document.addEventListener("DOMContentLoaded", () => {
    attachStepListeners("staff_add");
    attachStepListeners("family_add");
    attachStepListeners("contact_add");
    attachStepListeners("socialmedia");
    attachStepListeners("Education");
    attachStepListeners("companydetails");
    attachStepListeners("salarydetails");
    attachStepListeners("application_add");
    attachStepListeners("checklist_add");
  });
</script>
 {{-- username unique check --}}
    <script>
        function user_name_chk(val) {
             var id = '{{ $staffData->sno ?? '' }}';
            if (val != "") {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    url: "{{ route('checkunique_user_name_edit') }}",
                    type: 'POST',
                    data: {
                        value: val,
                        id: id
                    },
                    dataType: 'json', // Expect JSON response from server
                    success: function(response) {
                        if (response.data !== 0) {
                            $('#loginuser_name_err').text('Staff username already assigned!.');
                             $('#loginuser_name').addClass('err_border');
                            $('#stage7').prop('disabled', true); // Disable submit button
                        } else {
                            $('#loginuser_name_err').text('');
                             $('#loginuser_name').removeClass('err_border');
                            $('#stage7').prop('disabled', false); // Enable submit button
                        }
                    },
                    error: function(xhr, status, error) {
                        // alert("Error: " + error); // Display error message
                    }
                });
            }

        }

        loginAccesCheck()
        function loginAccesCheck(){
            if ($('#login_access').is(':checked')) {
                $('.login_access').show();
            } else {
                // Do nothing or optionally close modal if unchecked
                $('.login_access').hide();
            }
        }

        $('#login_access').on('change', function() {
            if ($(this).is(':checked')) {
                $('.login_access').show();
            } else {
                // Do nothing or optionally close modal if unchecked
                $('.login_access').hide();
            }
        });

        window.onload = function() {
            // First, load children fields (if you need to populate dynamic fields)
            loadChildrenFields();
            loadSiblingFields();

            // Wait for the DOM to be fully loaded, then calculate the step percentages
            updateAllStepPercentages();
        };

        // Function to update all step percentages
        function updateAllStepPercentages() {

            vehicle_check_change();
            dob_change_age();
            community_change_func();
            
           changePayrollTemplate()
            appliedPostionDropdown()
           per_day_salary_value()
           salary_company_change()
            stepIds.forEach(stepId => {
                updateStepPercentage(stepId, true);  // Calculate and update percentage for each step
            });
            console.log("gsdfhgksjdgfjhsdgjhs")
           buttonEnalbleAll();
        }
    </script>
    
     <script>
       
        function appliedPostionDropdown() {
            let entity_id = 0;

            if ($("#management").is(":checked")) {
                entity_id = 0;
            } else {
                entity_id = $('#entity_name').val();
            }
            let appliedPositionIds = @json($appliedPositionIds ?? []);
            let stateDropdown = $('#applied_position');
            stateDropdown.empty(); // clear dropdown
            console.log('entity_id', entity_id);
            
                 console.log('axjax entity_id', entity_id);
                $.ajax({
                    url: "{{ route('get_job_role_by_entity') }}",
                    type: "GET",
                    data: { entity_id: entity_id },
                    success: function (response) {
                        if (response.status === 200 && response.data) {

                            // Create optgroups
                            let technicalGroup = $('<optgroup label="Technical Position"></optgroup>');
                            let nonTechnicalGroup = $('<optgroup label="Non Technical Position"></optgroup>');

                            response.data.forEach(function (item) {
                                let option = $('<option></option>')
                                    .val(item.sno)
                                    .text(item.job_position_name);

                                if (item.technical_type == 1) {
                                    technicalGroup.append(option);
                                } else if (item.technical_type == 2) {
                                    nonTechnicalGroup.append(option);
                                }
                            });

                            // Append optgroups only if they have options
                            if (technicalGroup.children().length > 0) {
                                stateDropdown.append(technicalGroup);
                            }
                            if (nonTechnicalGroup.children().length > 0) {
                                stateDropdown.append(nonTechnicalGroup);
                            }
                          
                            // Refresh Select2 / Select3 if used
                            // stateDropdown.trigger('change');
                              // Set selected values
                            stateDropdown.val(appliedPositionIds).change();
                        }
                    },
                    error: function (error) {
                        console.error('Error fetching Job Role:', error);
                    }
                });
            
        }
    </script>
        <script>
        // function qualification_change(element) {
        //     let qualification_id = $(element).val();
        //     let row = $(element).closest('.education-row');
        //     let majorDropdown = row.find('select[name="major[]"]');

        //     majorDropdown.empty().append('<option value="">Select Major</option>');

        //     if (qualification_id) {
        //         $.ajax({
        //             url: "{{ route('major_list_by_qualification') }}",
        //             type: "GET",
        //             data: { qualification_id: qualification_id },
        //             success: function(response) {
        //                 if (response.status === 200 && response.data) {
        //                     response.data.forEach(function(item) {
        //                         majorDropdown.append(
        //                             $('<option></option>')
        //                                 .attr('value', item.sno)
        //                                 .text(item.major_name)
        //                         );
        //                     });

        //                     majorDropdown.append('<option value="Others">Others</option>');
        //                 }
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching major:', error);
        //             }
        //         });
        //     }
        // }
        function qualification_change(element) {
            let qualification_id = $(element).val();
            let row = $(element).closest('.education-row');
            let majorWrapper = row.find('select[name="major[]"]').closest('.col-lg-4');
            let majorDropdown = row.find('select[name="major[]"]');

            // Qualifications where major should be hidden
            let hideMajorFor = ['4', '5', '6', 'Others'];

            if (hideMajorFor.includes(qualification_id)) {
                majorWrapper.hide();
                majorDropdown.val("").trigger("change");
                majorDropdown.removeClass("required-field");
                return; // stop ajax call
            } else {
                majorWrapper.show();
                majorDropdown.addClass("required-field");
            }

            // Reset dropdown
            majorDropdown.empty().append('<option value="">Select Major</option>');

            if (qualification_id) {
                $.ajax({
                    url: "{{ route('major_list_by_qualification') }}",
                    type: "GET",
                    data: { qualification_id: qualification_id },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(item) {
                                majorDropdown.append(
                                    $('<option></option>')
                                        .attr('value', item.sno)
                                        .text(item.major_name)
                                );
                            });

                            majorDropdown.append('<option value="Others">Others</option>');
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching major:', error);
                    }
                });
            }
        }

    </script>

  
    <script>
       function buttonEnalbleAll(){
        const updateClose1 = document.getElementById("updateClose1");
         updateClose1.disabled = false;
        const updateNxt1 = document.getElementById("updateNxt1");
            updateNxt1.disabled = false;

        const updateClose2 = document.getElementById("updateClose2");
         updateClose2.disabled = false;
        const updateNxt2 = document.getElementById("updateNxt2");
            updateNxt2.disabled = false;

        const updateClose3 = document.getElementById("updateClose3");
         updateClose3.disabled = false;
        const updateNxt3 = document.getElementById("updateNxt3");
            updateNxt3.disabled = false;

        const updateClose4 = document.getElementById("updateClose4");
         updateClose4.disabled = false;
        const updateNxt4 = document.getElementById("updateNxt4");
            updateNxt4.disabled = false;

        const updateClose5 = document.getElementById("updateClose5");
         updateClose5.disabled = false;
        const updateNxt5 = document.getElementById("updateNxt5");
            updateNxt5.disabled = false;

        const updateClose6 = document.getElementById("updateClose6");
         updateClose6.disabled = false;
        const updateNxt6 = document.getElementById("updateNxt6");
            updateNxt6.disabled = false;

        const updateClose7 = document.getElementById("updateClose7");
         updateClose7.disabled = false;
        const updateNxt7 = document.getElementById("updateNxt7");
            updateNxt7.disabled = false;
       }
    </script>
    <script>
        /**
         * Parse DOB from:
         * - d-M-Y (25-Jul-1998)
         * - Y-m-d (1998-07-25)
         */
        function parseDOB(dateStr) {
            if (!dateStr) return null;

            dateStr = dateStr.trim();

            // Format: YYYY-MM-DD
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                const [y, m, d] = dateStr.split('-').map(Number);
                return new Date(y, m - 1, d);
            }

            // Format: DD-MMM-YYYY
            if (/^\d{1,2}-[A-Za-z]{3}-\d{4}$/.test(dateStr)) {

                const months = {
                    Jan:0, Feb:1, Mar:2, Apr:3,
                    May:4, Jun:5, Jul:6, Aug:7,
                    Sep:8, Oct:9, Nov:10, Dec:11
                };

                const parts = dateStr.split('-');

                const day = parseInt(parts[0], 10);
                const month = months[parts[1]];
                const year = parseInt(parts[2], 10);

                if (month === undefined) return null;

                return new Date(year, month, day);
            }

            return null;
        }

        /**
         * Calculate Age
         */
        function calculateAge() {

            const dobString = $('#staff_dob').val();

            const dob = parseDOB(dobString);

            if (!dob || isNaN(dob.getTime())) {
                return {
                    status: false,
                    message: 'Invalid Date of Birth'
                };
            }

            const today = new Date();

            if (dob > today) {
                return {
                    status: false,
                    message: 'DOB cannot be in the future'
                };
            }

            let years = today.getFullYear() - dob.getFullYear();
            let months = today.getMonth() - dob.getMonth();
            let days = today.getDate() - dob.getDate();

            if (days < 0) {
                months--;

                const previousMonth = new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    0
                ).getDate();

                days += previousMonth;
            }

            if (months < 0) {
                years--;
                months += 12;
            }

            return {
                status: true,
                years,
                months,
                days,
                text: `${years} Year${years !== 1 ? 's' : ''} ${months} Month${months !== 1 ? 's' : ''} ${days} Day${days !== 1 ? 's' : ''}`
            };
        }

        function dob_change_age(){
            const age = calculateAge();
            if (age.status) {
                $('#age_display').text(age.text);
            } else {
               $('#age_display').text('-');
            }
        }

        function vehicle_check_change(){
            if ($('#vehicle_check').is(':checked')) {
                $('.vehicle_check').removeClass('d-none');
            } else {
                $('.vehicle_check').addClass('d-none');
            }
            updateStepPercentage("staff_add");
        }

       
    </script>
    
    <script>
         
            function community_change_func() {
                var religion_id = $('#religion').val();
                var community_id = $('#community').val();
                var caste_id ={{$staffData->caste_id ?? 0}};

                var casteDropdown = $('#caste');

                casteDropdown.empty().append('<option value="">Select Caste</option>');
               

                if (religion_id && community_id) {
                    $.ajax({
                        url: "{{ route('caste_list_by_religion_community') }}",
                        type: "GET",
                        data: {
                            religion_id: religion_id,
                            community_id: community_id,
                        },
                        success: function(response) {
                            if (response.status === 200 && response.data) {
                                response.data.forEach(function(caste) {
                                    casteDropdown.append($('<option></option>').attr(
                                        'value', caste.sno)
                                        .text(caste.caste_name));
                                });
                                if(caste_id){
                                    $('#caste').val(caste_id).change();  
                                }
                                
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Caste:', error);
                        }
                    });
                }else{
                    toastr.warning('Please Select Religion & Community');
                }
                
            }
               
    </script>
    <script>
        function casteSettingAdd(e) {

            const religionId = $('#religion').val();
            const communityId = $('#community').val();

            if (!religionId) {
                toastr.warning('Please select Religion first.');
                $('#religion').focus();

                // Prevent dropdown opening
                if (e) {
                    e.stopPropagation();
                    e.preventDefault();
                }

                return false;
            }

            if (!communityId) {
                toastr.warning('Please select Community first.');
                $('#community').focus();

                if (e) {
                    e.stopPropagation();
                    e.preventDefault();
                }

                return false;
            }

            // Store IDs
            $('#sett_religion_id_add').val(religionId);
            $('#sett_community_id_add').val(communityId);

            toggleAddCasteButton();

            // Store display names
            $('#sett_religion_name_add').text(
                $('#religion option:selected').text()
            );

            $('#sett_community_name_add').text(
                $('#community option:selected').text()
            );

            return true;
        }
    </script>
    <script>
        function submitSettCasteList() {

            var caste_name = $('#sett_caste_name_add').val().trim();
            var religion_id = $('#sett_religion_id_add').val();
            var community_id = $('#sett_community_id_add').val();
            var errorMessage = 0;

            const btn = $('#btn_add_caste_setting');
            if (btn.prop('disabled')) {
                return false;
            }


            $('#add_sett_module_div .errorMessage').remove();

            if (!caste_name || caste_name === '' || caste_name === undefined || caste_name === null) {
                $('#sett_caste_name_add_err').after('<div class="text-danger errorMessage">Module Name is Required..!</div>');
                errorMessage++;
            }

            if(errorMessage == 0){

             btn.prop('disabled', true)
                .addClass('btn-loading')
                .html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
                /* ------------ DATA ------------ */
                let data = {
                    caste_name: caste_name,
                    religion_id: religion_id,
                    community_id: community_id,
                };
            
                fetch(`{{ url('/add_caste_list') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 200) {
                        toastr.success('Module Added Successfully.');
                        $('#dropdownMenuButton').click();
                        $('#sett_caste_name_add, #sett_caste_desc_add').val('');
                        community_change_func();
                    } else {
                        toastr.error('Something went wrong');
                    }
                })
                .catch(err => {
                    console.error(err);
                    toastr.error('Server error');
                    btn.prop('disabled', false)
                        .removeClass('btn-loading')
                        .html('Add Caste');
                }).finally(function () {

                    btn.prop('disabled', false)
                        .removeClass('btn-loading')
                        .html('Add Caste');

                });
            }else{
                return false;
            }
        }

        function toggleAddCasteButton() {

            const religionId = $('#religion').val();
            const communityId = $('#community').val();

            if (religionId && communityId) {
                $('#btn_add_caste_setting').prop('disabled', false);
            } else {
                $('#btn_add_caste_setting').prop('disabled', true);
            }
        }

        function formatName(input) {

            let value = input.value;
            // Allow only letters, space, dot, apostrophe and hyphen
            value = value.replace(/[^A-Za-z\s.'-]/g, '');
            // Remove leading spaces
            value = value.replace(/^\s+/, '');
            // Replace multiple spaces with single space
            value = value.replace(/\s{2,}/g, ' ');
            // Capitalize every word
            value = value.toLowerCase().replace(/\b[a-z]/g, function(char) {
                return char.toUpperCase();
            });
            input.value = value;
        }

        function formatWord(input) {

            let value = input.value;
            // Allow only letters, space, dot, apostrophe and hyphen
            value = value.replace(/[^A-Za-z\s.'-]/g, '');
            // Remove leading spaces
            value = value.replace(/^\s+/, '');
            // Replace multiple spaces with single space
            value = value.replace(/\s{2,}/g, ' ');
            // Capitalize every word
            value = value.toLowerCase().replace(/\b[a-z]/g, function(char) {
                return char.toUpperCase();
            });
            input.value = value;
        }

        function formatCompanyName(input) {

            let value = input.value;

            // Allow letters, numbers, spaces and common company symbols
            value = value.replace(/[^A-Za-z0-9\s.,&()/'-]/g, '');

            // Remove leading spaces
            value = value.replace(/^\s+/, '');

            // Replace multiple spaces with a single space
            value = value.replace(/\s{2,}/g, ' ');

            // Capitalize each word
            value = value.toLowerCase().replace(/\b[a-z]/g, function(char) {
                return char.toUpperCase();
            });

            input.value = value;
        }
    </script>
    <script>
        function salary_company_change() {
            let id = $('#salary_company_id').val();
            var salary_company_id ={{$staffData->salary_company_id ?? 0}};

            if (!id) return;

            $.ajax({
                url: '/company-banks/' + id,
                type: 'GET',
                success: function(res) {

                    let options = '<option value="">Select Bank</option>';

                    res.data.forEach(bank => {
                        options += `<option value="${bank.sno}" >
                                ${bank.bank_name} - ${bank.account_number}
                            </option>`;
                    });

                    $('#salary_bank_id').html(options);

                    if(salary_company_id){
                        $('#salary_bank_id').val(salary_company_id).change();
                    }

                }
            });
        }

        function per_day_salary_value() {

            let salary = parseFloat($('#basic_salary').val());

            if (isNaN(salary) || salary <= 0) {
                $('#per_day_salary').val('0.00');
                $('#per_hr_cost').val('0.00');
                return;
            }

            // ✅ Per Day
            let perDaySalary = salary / 30;
            perDaySalary = Math.round(perDaySalary * 100) / 100;
            $('#per_day_salary').val(perDaySalary.toFixed(1));

            // ✅ Per Hour (8 hrs default)
            let workingHours = 8;
            let perHour = salary / (30 * workingHours);
            perHour = Math.round(perHour * 100) / 100;
            $('#per_hr_cost').val(perHour.toFixed(1));
            calculateEmployeeSalarySummary()
        }
    </script>
    <script>
        let companyOptions = $('#salary_company_id').html();
        function accountTypeChange() {
            let accountType = $('[name="is_multiple_account"]:checked').val();
            if (accountType == 1) {
                // Multiple Account
                $('#multipleAccountDiv').removeClass('d-none');
                $('#singleAccountDiv').addClass('d-none');
                $('.companyBankSection').addClass('d-none');
                calculateMultipleAccountTotals();
            } else {
                // Single Account
                $('#multipleAccountDiv').addClass('d-none');
                $('#singleAccountDiv').removeClass('d-none');
                if ($('#salary_bank').is(':checked')) {
                    $('.companyBankSection').removeClass('d-none');
                }
                per_day_salary_value();
            }
        }

        function addSalaryAccountRow() {

            let index = $('.salary-card').length + 1;
            let isFirst = $('.salary-card').length === 0;
            let card = $(`
                <div class="card salary-card shadow-sm border mb-3">
                  
                    <div class="card-header d-flex justify-content-between align-items-center pb-0">
                        <h6 class="mb-0">
                            <i class="mdi mdi-bank-outline me-2"></i>
                            Salary Account #${index}
                        </h6>
                        <div>
                            <div class="form-check form-switch d-inline-block me-3">
                                <input class="form-check-input primary-account" type="radio" name="primary_account"  ${isFirst ? 'checked' : ''}>
                                <label class="form-check-label">Primary</label>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-card">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label>Company</label>
                                <select class="form-select company-id select3" onchange="companyBankChange(this)">
                                    ${companyOptions}
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label>Bank</label>
                                <select class="form-select bank-id select3">
                                    <option>Select Bank</option>
                                </select>
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-4">
                                <label>Basic Salary</label>
                                <input class="form-control gross-salary" value="" placeholder="Enter Basic Salary">
                                <div class="text-danger error_msg"></div>
                            </div>
                            <div class="col-lg-4">
                                <label>Per Day</label>
                                <input class="form-control per-day" readonly value="0">
                            </div>
                            <div class="col-lg-4">
                                <label>Per Hour</label>
                                <input class="form-control per-hour" readonly value="0">
                            </div>
                        </div>
                    </div>
                </div>
            `);

            $('#salaryAccountBody').append(card);

            card.find('.select3').select2({
                dropdownParent: $('#multipleAccountDiv'),
                width: '100%'
            });      

        }

        $(document).on('input', '.gross-salary', function () {
            // Allow only numbers and one decimal point
            this.value = this.value
                .replace(/[^0-9.]/g, '')
                .replace(/(\..*)\./g, '$1');

            let salary = parseFloat(this.value) || 0;

            let row = $(this).closest('.salary-card');

            let perDay = salary / 30;
            let perHour = salary / 240; // 30 days × 8 hours

            row.find('.per-day').val(perDay.toFixed(2));
            row.find('.per-hour').val(perHour.toFixed(2));

            calculateMultipleAccountTotals();
        });

        function collectSalaryAccounts(){
            let accounts = [];
            $('#salaryAccountBody .salary-card').each(function(){
                accounts.push({
                    sno: $(this).find('.salary-account-sno').val(),
                    salary_company_id:$(this).find('.company-id').val(),
                    salary_bank_id:$(this).find('.bank-id').val(),
                    gross_salary:$(this).find('.gross-salary').val(),
                    per_day_salary:$(this).find('.per-day').val(),
                    per_hour_cost:$(this).find('.per-hour').val(),
                    is_primary:$(this).find('.primary-account').is(':checked') ? 1 : 0
                });
            });
            return accounts;
        }

        function companyBankChange(element) {
            let companyId = $(element).val();
            let bankDropdown =$(element).closest('.salary-card').find('.bank-id');
            bankDropdown.html('<option value="">Loading...</option>');
            $.ajax({
                url: '/company-banks/' + companyId,
                type: 'GET',
                success: function (res) {
                    let options ='<option value="">Select Bank</option>';
                    res.data.forEach(bank => {
                        options += `
                        <option value="${bank.sno}">
                            ${bank.bank_name}-${bank.account_number}
                        </option>
                        `;
                    });
                    bankDropdown.html(options);
                }
            });
        }

        function calculateMultipleAccountTotals(){

            let totalSalary = 0;
            let totalPerDay = 0;
            let totalPerHour = 0;

            $('#salaryAccountBody .salary-card').each(function(){
                totalSalary += parseFloat($(this).find('.gross-salary').val()) || 0;
                totalPerDay += parseFloat($(this).find('.per-day').val()) || 0;
                totalPerHour += parseFloat($(this).find('.per-hour').val()) || 0;
            });

            $('#total_gross_salary').text(totalSalary.toFixed(1));
            $('#total_per_day').text(totalPerDay.toFixed(1));
            $('#total_per_hour').text(totalPerHour.toFixed(1));
        }

        $(document).on('click', '.remove-card', function () {
            let card = $(this).closest('.salary-card');
            let wasPrimary = card.find('.primary-account').is(':checked');
            card.remove();
            renumberCards();
            if (wasPrimary) {
                $('.salary-card:first .primary-account').prop('checked', true);
            }
            calculateMultipleAccountTotals();

        });

        function renumberCards(){
            $('.salary-card').each(function(i){
                $(this).find('.card-header h6') .html(`<i class="mdi mdi-bank-outline me-2"></i> Salary Account #${i+1}`);
            });
        }
    </script>

    <script>
        function calculateEmployeeSalarySummary() {

            let grossSalary = parseFloat($('#basic_salary').val()) || 0;

            let earnings = 0;
            let deductions = 0;
            let employerContribution = 0;

            let basic = 0;
            let da = 0;

            $('.salary-component-row').each(function () {
                let row = $(this);
                let type = row.find('.component_type').val();

                if (type !== 'earning') {
                    return;
                }
                let calculationType = row.data('calculation-type');
                let percentage =parseFloat(row.find('.percentage_value').val()) || 0;
                let amount = parseFloat(row.find('.calculated_amount').val()) || 0;
               
                if (calculationType === 'percentage') {
                    amount =(grossSalary * percentage) / 100;
                }else if (calculationType === 'fixed' ||calculationType === 'manual_input') {
                    amount = parseFloat( row.find('.calculated_amount').val()) || 0;
                }
                row.find('.calculated_amount').val(parseFloat(amount).toFixed(2));
                let componentCode =row.find('.component_code').val();
                if (componentCode === 'BASIC') {
                    basic = amount;
                }
                if (componentCode === 'DA') {
                    da = amount;
                }
                earnings += amount;
            });

            $('.salary-component-row').each(function () {

                let row = $(this);
                let type =row.find('.component_type').val();
                if ( type !== 'deduction' && type !== 'employer_contribution') {
                    return;
                }
                let componentCode =row.find('.component_code').val();
                let calculationType = row.data('calculation-type');
                let percentage = parseFloat( row.find('.percentage_value').val()) || 0;
                let amount =parseFloat( row.find('.calculated_amount').val()) || 0;
                
                if (componentCode === 'PF' && calculationType !== 'fixed') {
                    let pfBase = basic + da;
                    amount = pfBase > 15000 ? 1800 : (pfBase * 0.12);
                }else if (componentCode === 'EMPLOYER_PF' && calculationType !== 'fixed') {
                    let pfBase = basic + da;
                    amount = pfBase > 15000 ? 1800 : (pfBase * 0.12);
                }else if (componentCode === 'ESI') {
                    let esiBase = basic + da;
                    if (esiBase > 21000) {
                        amount = 0;
                    }else if (calculationType === 'fixed') {
                        amount = parseFloat( row.find('.calculated_amount').val()) || 0;
                    }else {
                        amount = esiBase * 0.0075;
                    }
                }else if (componentCode === 'EMPLOYER_ESI') {
                    let esiBase = basic + da;
                    if (esiBase > 21000) {
                        amount = 0;
                    }else if (calculationType === 'fixed') {
                        amount = parseFloat( row.find('.calculated_amount').val()) || 0;
                    }else {
                        amount = esiBase * 0.0325;
                    }
                }else if (calculationType === 'percentage') {
                    amount = (grossSalary * percentage) / 100;
                }else if (calculationType === 'fixed' || calculationType === 'manual_input') {
                    if (componentCode === 'PT') {
                        amount = grossSalary <= 10000 ? 140 : 208;
                    }else{
                        amount = parseFloat( row.find('.calculated_amount').val() ) || 0;
                    }
                }

                row.find('.calculated_amount').val(parseFloat(amount).toFixed(2));
                if (type === 'deduction') {
                    deductions += amount;
                }
                if (type === 'employer_contribution') {
                    employerContribution += amount;
                }
            });

            let netSalary = earnings - deductions;
            let ctc = earnings + employerContribution;
            $('#emp_total_earnings').html('₹ ' +earnings.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#emp_total_deductions').html( '₹ ' + deductions.toLocaleString('en-IN', { minimumFractionDigits: 2}));
            $('#emp_net_salary').html('₹ ' + netSalary.toLocaleString('en-IN', { minimumFractionDigits: 2}));
            $('#emp_ctc').html('₹ ' +ctc.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        }

        function changePayrollTemplate() {
            let templateId = $('#payroll_template_sno').val();
            $('#employee_salary_components').html('');
            if (templateId == '') {
                return;
            }
            $.ajax({
                url: '/payroll/template/components/' +
                    templateId,
                type: 'GET',
                success: function(response) {
                    let details = response.data.details;
                    if (details.length > 0) {
                        details.forEach(detail => {
                            appendEmployeeComponent(detail);
                        });
                        calculateEmployeeSalarySummary();
                    } else {
                        $('#employee_salary_components')
                            .html(`
                                <div class="alert alert-warning rounded-4">
                                    No components found in template
                                </div>

                            `);
                    }
                },
                error: function(xhr) {
                    toastr.error(
                        xhr.responseJSON?.message ||
                        'Unable to load template components'
                    );
                }
            });
        }
    </script>
    <script>
        function appendEmployeeComponent(detail) {
            let grossSalary = parseFloat( $('#basic_salary').val()) || 0;
            let calculationType =detail.calculation_type || '';
            let percentage = parseFloat( detail.percentage_value ||detail.percentage || 0);
            let fixedAmount = parseFloat(detail.amount ||detail.fixed_amount || 0);
            let amount = parseFloat( detail.calculated_amount || 0);

            if (calculationType === 'percentage') {
                amount = (grossSalary * percentage) / 100;
            } else if (calculationType === 'fixed') {
                amount = fixedAmount;
            }
           
            
            let html = `
                <div class="salary-component-row" data-calculation-type="${calculationType}">
                    <div class="">
                        <div class="">
                            <div class="">
                                <input type="hidden" class="payroll_component_sno" value="${detail.payroll_component_sno || detail.component_sno}">
                                <input type="hidden" class="payroll_rule_sno" value="${detail.payroll_rule_sno || ''}">
                                <input type="hidden" class="component_type" value="${detail.component_type}">
                                <input type="hidden" class="component_code" value="${detail.component_code || ''}">
                                <input type="hidden" class="rule_expression" value="${detail.rule_expression || ''}">
                                <input type="hidden" class="percentage_value" value="${percentage}" readonly>
                                <input type="hidden"  class="calculated_amount" value="${parseFloat(amount).toFixed(2)}"  >
                            </div>
                        </div>
                    </div>
                </div>
                `;
            $('#employee_salary_components').append(html);
            calculateEmployeeSalarySummary();
        }
    </script>

    <script>
        function employee_id_chk(val) {
              var id = '{{ $staffData->sno ?? '' }}';
            if (val != "") {
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                $.ajax({
                    url: "{{ route('checkStaffIdExistsEdit') }}",
                    type: 'POST',
                    data: { employee_id: val ,id:id },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        if (response.data !== 0) {
                            $('#employee_id_err').text('Employee Id is already assigned!');
                            $('#employee_id').addClass('err_border');
                            $('#updateNxt7').prop('disabled', true);
                            $('#updateClose7').prop('disabled', true);
                            $('#stage7').prop('disabled', true);
                        } else {
                            $('#employee_id_err').text('');
                            $('#employee_id').removeClass('err_border');
                            $('#updateNxt7').prop('disabled', false);
                            $('#updateClose7').prop('disabled', false);
                            $('#stage7').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#updateClose7').prop('disabled', true);
                        $('#updateNxt7').prop('disabled', true);
                        $('#stage7').prop('disabled', true);
                        
                    }
                });
            }
        }
    </script>


{{-- ================================================================
     PRODUCTION EDIT-PAGE OVERRIDES
     - Profile completion is field-weighted and condition-aware.
     - Optional visible fields count toward completion.
     - Hidden / not-applicable fields do not count.
     - HR-controlled stages remain visible but do not affect employee
       profile completion.
     - Existing attachment rows are restored for edit safety.
     ================================================================ --}}
<style>
    .profile-completion-card {
        border: 1px solid rgba(105,108,255,.18);
        background: linear-gradient(135deg, rgba(105,108,255,.08), rgba(255,255,255,.96));
        border-radius: 14px;
    }
    .profile-completion-ring {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: conic-gradient(#696cff 0deg, #696cff 0deg, #e9ecef 0deg);
        position: relative;
        flex: 0 0 auto;
    }
    .profile-completion-ring::after {
        content: '';
        position: absolute;
        inset: 7px;
        background: #fff;
        border-radius: 50%;
    }
    .profile-completion-ring span {
        position: relative;
        z-index: 2;
        font-weight: 700;
        font-size: 15px;
    }
    .completion-step-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        background: #fff;
        padding: 10px 12px;
    }
    .completion-step-card .progress {
        height: 7px;
    }
    .completion-step-meta {
        font-size: 11px;
        color: #697a8d;
    }
    .completion-warning-list {
        max-height: 190px;
        overflow-y: auto;
    }
    .completion-warning-list .list-group-item {
        border: 0;
        border-bottom: 1px solid #f0f2f4;
        padding: 8px 0;
        font-size: 12px;
    }
    .existing-attachment-panel {
        border: 1px dashed #cfd5dc;
        border-radius: 10px;
        background: #fff;
        padding: 10px;
        margin-top: 8px;
    }
    .existing-attachment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 7px 0;
        border-bottom: 1px solid #f1f1f1;
    }
    .existing-attachment-item:last-child { border-bottom: 0; }
    .existing-attachment-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .hr-managed-badge {
        font-size: 10px;
        letter-spacing: .2px;
    }
    @media (max-width: 991px) {
        .profile-completion-ring { width: 64px; height: 64px; }
    }
</style>

<script>
(function () {
    'use strict';

    const PROFILE_SCOPE = [
        'staff_add',
        'family_add',
        'contact_add',
        'socialmedia',
        'Education'
    ];

    const HR_MANAGED_SCOPE = [
        'work_add',
        'companydetails',
        'salarydetails',
        'application_add',
        'checklist_add'
    ];

    const STEP_LABELS = {
        staff_add: 'Base Details',
        family_add: 'Family Details',
        contact_add: 'Contact Details',
        socialmedia: 'Social Media',
        Education: 'Education',
        work_add: 'Work Type',
        companydetails: 'Company Details',
        salarydetails: 'Salary Details',
        application_add: 'Application Details',
        checklist_add: 'Checklist'
    };

    const QUALIFICATION_NO_MAJOR = ['4', '5', '6', 'Others'];

    function safeJson(value, fallback) {
        if (value === null || value === undefined || value === '') return fallback;
        if (typeof value === 'object') return value;
        try { return JSON.parse(value); } catch (e) { return fallback; }
    }

    function normalized(value) {
        if (value === null || value === undefined) return '';
        return String(value).trim();
    }

    function isExcludedElement(el) {
        if (!el || el.nodeType !== 1) return true;
        if (el.matches('.select2-search__field')) return true;
        if (el.closest('.select2-container')) return true;
        if (el.matches('[type="hidden"], [type="button"], [type="submit"], [type="reset"]')) return true;
        if (el.matches('.ignore-progress, [data-ignore-total]')) return true;
        if (el.closest('[data-progress-exclude="true"]')) return true;
        return false;
    }

    function visibleForProgress(el) {
        if (isExcludedElement(el)) return false;
        if (el.closest('.d-none')) return false;
        if ($(el).is(':hidden')) return false;
        return true;
    }

    function fieldFilled(el) {
        if (!el) return false;
        const type = (el.type || '').toLowerCase();
        if (type === 'radio') return false;
        if (type === 'checkbox') return el.checked;
        if (type === 'file') return !!(el.files && el.files.length);
        if (el.tagName === 'SELECT') {
            if ($(el).prop('multiple')) return ($(el).val() || []).length > 0;
            return normalized($(el).val()) !== '';
        }
        return normalized(el.value) !== '';
    }

    function labelText(el) {
        if (!el) return '';
        const id = el.id;
        if (id) {
            const label = document.querySelector('label[for="' + CSS.escape(id) + '"]');
            if (label) return label.textContent.replace(/\*/g, '').trim();
        }
        const parent = el.closest('.mb-3, .err-chk, .form-check, .col-lg-4, .col-lg-6, .col-lg-12');
        const label = parent ? parent.querySelector('label') : null;
        return label ? label.textContent.replace(/\*/g, '').trim() : (el.name || el.id || 'Field');
    }

    function addField(stats, key, label, included, filled, mode, meta) {
        stats.fields.push({
            key: key || null,
            label: label || key || 'Field',
            mode: mode || 'optional',
            included: !!included,
            filled: !!filled,
            status: !included ? 'not_applicable' : (filled ? 'completed' : 'incomplete'),
            ...(meta || {})
        });
        if (included) {
            stats.total++;
            if (filled) stats.completed++;
        }
    }

    function collectStandardFields(section, stats) {
        const elements = section.querySelectorAll(
            'input, select, textarea'
        );
        const radioGroups = new Set();

        elements.forEach(el => {
            if (isExcludedElement(el)) return;
            if (!visibleForProgress(el)) return;

            const type = (el.type || '').toLowerCase();
            if (type === 'radio') {
                const group = el.name || el.id;
                if (radioGroups.has(group)) return;
                radioGroups.add(group);
                const groupEls = section.querySelectorAll('input[type="radio"][name="' + CSS.escape(group) + '"]');
                const anyChecked = Array.from(groupEls).some(r => r.checked);
                addField(stats, group, labelText(el), true, anyChecked, 'optional');
                return;
            }

            // Conditional checkboxes are handled explicitly below.
            if (type === 'checkbox') return;

            addField(stats, el.name || el.id, labelText(el), true, fieldFilled(el), 'optional');
        });
    }

    function collectBase(section) {
        const stats = { completed: 0, total: 0, fields: [] };
        const handled = new Set();

        const requiredSelectors = [
            '#staff_name', '#mobile_no', 'input[name="gender"]', '#staff_dob',
            '#email_id', '#mother_tongue', '#Languages', '#blood_group',
            '#nationality', '#religion', '#community', '#aadhar_no', '#pan_no'
        ];

        // Start with all visible normal fields. Optional fields count too.
        collectStandardFields(section, stats);

        // Vehicle is a choice, not an incomplete field when unchecked.
        const vehicle = section.querySelector('#vehicle_check');
        if (vehicle) {
            ['driving_license_no', 'vehicle_register_no', 'license_expiry'].forEach(name => {
                const el = section.querySelector('[name="' + name + '"]');
                if (!el) return;
                const key = name;
                const existing = stats.fields.find(f => f.key === key);
                if (existing) {
                    const idx = stats.fields.indexOf(existing);
                    if (!vehicle.checked) {
                        if (existing.included) {
                            stats.total--;
                            if (existing.filled) stats.completed--;
                        }
                        stats.fields[idx] = {
                            ...existing,
                            included: false,
                            filled: false,
                            status: 'not_applicable',
                            reason: 'vehicle_not_selected'
                        };
                    } else {
                        existing.mode = 'required';
                    }
                }
            });
        }

        // A file input is only complete when a new file is selected; existing profile image is handled by server data.
        const image = section.querySelector('#fav_upload');
        if (image) {
            const existing = stats.fields.find(f => f.key === image.name);
            if (existing) {
                const idx = stats.fields.indexOf(existing);
                if (existing.included) stats.total--;
                if (existing.filled) stats.completed--;
                stats.fields[idx] = {
                    ...existing,
                    included: true,
                    filled: !!(image.files && image.files.length) || {{ !empty($staffData->staff_image) ? 'true' : 'false' }},
                    status: ((image.files && image.files.length) || {{ !empty($staffData->staff_image) ? 'true' : 'false' }}) ? 'completed' : 'incomplete',
                    mode: 'optional',
                    source: 'existing_or_new_file'
                };
                stats.total++;
                if (stats.fields[idx].filled) stats.completed++;
            }
        }

        return finalizeStats(stats);
    }

    function collectFamily(section) {
        const stats = { completed: 0, total: 0, fields: [] };
        collectStandardFields(section, stats);

        const marital = section.querySelector('#marital_status');
        const married = marital && normalized(marital.value) === '1';
        const spouseFields = section.querySelectorAll('.spouse-field');
        const workingFields = section.querySelectorAll('.working-fields');
        const spouseWorkingYes = section.querySelector('#workingYes')?.checked === true;

        // Remove fields that are structurally hidden/not applicable.
        [...spouseFields, ...workingFields].forEach(wrapper => {
            wrapper.querySelectorAll('input,select,textarea').forEach(el => {
                const item = stats.fields.find(f => f.key === (el.name || el.id));
                if (!item) return;
                if (!married || wrapper.classList.contains('working-fields') && !spouseWorkingYes) {
                    if (item.included) {
                        stats.total--;
                        if (item.filled) stats.completed--;
                    }
                    item.included = false;
                    item.filled = false;
                    item.status = 'not_applicable';
                    item.reason = !married ? 'marital_status_unmarried' : 'spouse_not_working';
                }
            });
        });

        // Children / siblings radio selection is an answer; No is valid and complete.
        ['has_children', 'has_Siblings'].forEach(name => {
            const radios = section.querySelectorAll('input[type="radio"][name="' + name + '"]');
            if (!radios.length) return;
            const existing = stats.fields.find(f => f.key === name);
            if (existing) {
                if (!existing.filled) {
                    existing.filled = Array.from(radios).some(r => r.checked);
                    if (existing.filled) {
                        stats.completed++;
                    }
                }
            }
        });

        return finalizeStats(stats);
    }

    function collectContact(section) {
        const stats = { completed: 0, total: 0, fields: [] };
        collectStandardFields(section, stats);
        return finalizeStats(stats);
    }

    function collectSocial(section) {
        const stats = { completed: 0, total: 0, fields: [] };
        const checkboxes = section.querySelectorAll('.toggle-field');
        let selected = 0;
        checkboxes.forEach(cb => {
            const id = cb.id.replace('checkSocialMedia_', '');
            const input = section.querySelector('#socialMediaField_' + CSS.escape(id) + ' input');
            if (cb.checked) {
                selected++;
                addField(
                    stats,
                    'social_' + id,
                    cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : 'Social Media',
                    true,
                    !!input && normalized(input.value) !== '',
                    'optional'
                );
            } else {
                stats.fields.push({
                    key: 'social_' + id,
                    label: cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : 'Social Media',
                    mode: 'excluded',
                    included: false,
                    filled: false,
                    status: 'not_applicable',
                    reason: 'social_platform_not_selected'
                });
            }
        });

        // No social account selected is a valid completed state.
        if (selected === 0) {
            stats.total = 1;
            stats.completed = 1;
            stats.fields.push({
                key: 'social_media_choice',
                label: 'Social Media Selection',
                mode: 'optional',
                included: true,
                filled: true,
                status: 'completed',
                reason: 'no_social_account_selected'
            });
        }
        return finalizeStats(stats);
    }

    function collectEducation(section) {
        const stats = { completed: 0, total: 0, fields: [] };
        const rows = section.querySelectorAll('.education-row');

        if (!rows.length) {
            addField(stats, 'education_record', 'Education Record', true, false, 'required');
            return finalizeStats(stats);
        }

        rows.forEach((row, index) => {
            const qualification = row.querySelector('[name="qualification_type[]"]');
            const major = row.querySelector('[name="major[]"]');
            const university = row.querySelector('[name="univ_name[]"]');
            const year = row.querySelector('[name="pass_year[]"]');
            const qualificationValue = normalized($(qualification).val());
            const skipMajor = QUALIFICATION_NO_MAJOR.includes(qualificationValue);

            addField(stats, 'education_' + index + '_qualification', 'Education ' + (index + 1) + ' Qualification', true, !!qualificationValue, 'required');

            if (skipMajor) {
                stats.fields.push({
                    key: 'education_' + index + '_major',
                    label: 'Education ' + (index + 1) + ' Major',
                    mode: 'excluded', included: false, filled: false,
                    status: 'not_applicable', reason: 'qualification_does_not_require_major'
                });
            } else {
                addField(stats, 'education_' + index + '_major', 'Education ' + (index + 1) + ' Major', true, !!major && normalized($(major).val()) !== '', 'required');
            }

            if (skipMajor) {
                stats.fields.push({
                    key: 'education_' + index + '_university',
                    label: 'Education ' + (index + 1) + ' Institute / University',
                    mode: 'excluded', included: false, filled: false,
                    status: 'not_applicable', reason: 'qualification_does_not_require_university'
                });
            } else {
                addField(stats, 'education_' + index + '_university', 'Education ' + (index + 1) + ' Institute / University', true, !!university && normalized(university.value) !== '', 'required');
            }

            addField(stats, 'education_' + index + '_year', 'Education ' + (index + 1) + ' Year', true, !!year && normalized(year.value) !== '', 'required');
        });

        const courseRadio = section.querySelectorAll('input[name="is_Course"]');
        if (courseRadio.length) {
            const selected = Array.from(courseRadio).find(r => r.checked);
            addField(stats, 'is_Course', 'Any Course Completed?', true, !!selected, 'required');
            const courseYes = selected && selected.value === 'Yes';
            const courseInputs = section.querySelectorAll('[name="course_tag[]"], [name="course_tag"]');
            if (courseYes && courseInputs.length) {
                const filled = Array.from(courseInputs).some(el => normalized($(el).val()).length > 0);
                addField(stats, 'course_tag', 'Course', true, filled, 'required');
            } else if (courseInputs.length) {
                stats.fields.push({
                    key: 'course_tag', label: 'Course', mode: 'excluded', included: false,
                    filled: false, status: 'not_applicable', reason: 'no_course_selected'
                });
            }
        }

        return finalizeStats(stats);
    }

    function finalizeStats(stats) {
        stats.total = Math.max(0, Number(stats.total || 0));
        stats.completed = Math.max(0, Math.min(stats.completed, stats.total));
        stats.remaining = Math.max(0, stats.total - stats.completed);
        stats.percentage = stats.total > 0 ? Math.round((stats.completed / stats.total) * 1000) / 10 : 100;
        return stats;
    }

    function collectStep(stepId) {
        const section = document.getElementById(stepId);
        if (!section) return finalizeStats({ completed: 0, total: 0, fields: [] });
        switch (stepId) {
            case 'staff_add': return collectBase(section);
            case 'family_add': return collectFamily(section);
            case 'contact_add': return collectContact(section);
            case 'socialmedia': return collectSocial(section);
            case 'Education': return collectEducation(section);
            default:
                // HR-controlled stages are intentionally excluded from employee completion.
                return {
                    completed: 0, total: 0, remaining: 0, percentage: 100,
                    fields: [], included: false, reason: 'hr_managed'
                };
        }
    }

    function updateStepBar(stepId, stats) {
        const bar = document.querySelector('.step-progress[data-step="' + CSS.escape(stepId) + '"] .progress-bar');
        if (!bar) return;
        const percent = Number(stats.percentage || 0);
        bar.style.width = percent + '%';
        bar.setAttribute('aria-valuenow', percent);
        bar.textContent = percent + '%';
        bar.classList.remove('bg-success', 'bg-warning', 'bg-secondary');
        bar.classList.add(percent >= 90 ? 'bg-success' : (percent > 0 ? 'bg-warning' : 'bg-secondary'));
    }

    function updateHeaderBadges() {
        HR_MANAGED_SCOPE.forEach(stepId => {
            const bar = document.querySelector('.step-progress[data-step="' + CSS.escape(stepId) + '"]');
            if (!bar) return;
            const progressBar = bar.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.textContent = '';
                progressBar.setAttribute('aria-valuenow', '100');
                progressBar.classList.remove('bg-success', 'bg-warning', 'bg-secondary');
                progressBar.classList.add('bg-secondary');
            }
            if (!bar.querySelector('.hr-managed-badge')) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-label-secondary hr-managed-badge ms-2';
                badge.textContent = 'HR';
                bar.parentElement.appendChild(badge);
            }
        });
    }

    function buildCompletionPayload() {
        const steps = {};
        let completed = 0;
        let total = 0;

        PROFILE_SCOPE.forEach(stepId => {
            const stats = collectStep(stepId);
            steps[stepId] = {
                label: STEP_LABELS[stepId],
                included: true,
                percentage: stats.percentage,
                completed: stats.completed,
                total: stats.total,
                remaining: stats.remaining,
                fields: stats.fields
            };
            completed += stats.completed;
            total += stats.total;
            updateStepBar(stepId, stats);
        });

        HR_MANAGED_SCOPE.forEach(stepId => {
            steps[stepId] = {
                label: STEP_LABELS[stepId],
                included: false,
                percentage: null,
                completed: 0,
                total: 0,
                remaining: 0,
                fields: [],
                reason: 'hr_managed'
            };
        });

        const percentage = total > 0 ? Math.round((completed / total) * 1000) / 10 : 100;
        return {
            version: 2,
            scope: 'employee_profile',
            overall: { percentage, completed, total, remaining: Math.max(0, total - completed) },
            steps
        };
    }

    function renderCompletionCard(payload) {
        let card = document.getElementById('production-profile-completion');
        if (!card) {
            card = document.createElement('div');
            card.id = 'production-profile-completion';
            card.className = 'card profile-completion-card mb-2';
            const form = document.getElementById('staff_form');
            if (form) form.parentNode.insertBefore(card, form);
        }

        const overall = payload.overall;
        const degrees = Math.round(overall.percentage * 3.6);
        const remainingText = overall.remaining === 0
            ? 'Profile is complete.'
            : overall.remaining + ' applicable field' + (overall.remaining === 1 ? '' : 's') + ' remaining.';

        card.innerHTML = `
            <div class="card-body py-3">
                <div class="d-flex flex-column flex-lg-row align-items-center gap-3">
                    <div class="profile-completion-ring" style="background:conic-gradient(#696cff ${degrees}deg,#e9ecef ${degrees}deg)">
                        <span>${overall.percentage}%</span>
                    </div>
                    <div class="flex-grow-1 w-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="fw-bold text-dark">Profile Completion</div>
                                <div class="text-muted small">${remainingText} Optional visible fields are included.</div>
                            </div>
                            <span class="badge ${overall.remaining === 0 ? 'bg-label-success' : 'bg-label-warning'}">
                                ${overall.completed}/${overall.total} complete
                            </span>
                        </div>
                        <div class="row g-2 mt-1">
                            ${PROFILE_SCOPE.map(id => {
                                const s = payload.steps[id];
                                return `<div class="col-12 col-md-6 col-xl-4">
                                    <div class="completion-step-card">
                                        <div class="d-flex justify-content-between gap-2 mb-1">
                                            <span class="fw-semibold small text-truncate">${STEP_LABELS[id]}</span>
                                            <span class="small fw-bold">${s.percentage}%</span>
                                        </div>
                                        <div class="progress rounded">
                                            <div class="progress-bar ${s.percentage >= 90 ? 'bg-success' : (s.percentage > 0 ? 'bg-warning' : 'bg-secondary')}" style="width:${s.percentage}%"></div>
                                        </div>
                                        <div class="completion-step-meta mt-1">${s.completed}/${s.total} complete${s.remaining ? ' · ' + s.remaining + ' remaining' : ''}</div>
                                    </div>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function updateProductionCompletion() {
        const payload = buildCompletionPayload();
        window.__staffCompletionPayload = payload;
        const hidden = document.getElementById('completion_percentage');
        if (hidden) hidden.value = payload.overall.percentage.toFixed(1);
        renderCompletionCard(payload);
        updateHeaderBadges();
        return payload;
    }

    // Public compatibility functions used by the existing page.
    window.getProductionCompletionPayload = updateProductionCompletion;
    window.calculateTotalPercentage = function () {
        return updateProductionCompletion().overall.percentage;
    };
    window.getCurrentStepPercentages = function () {
        return updateProductionCompletion();
    };
    window.updateStepPercentage = function (stepId) {
        const payload = updateProductionCompletion();
        return payload.steps[stepId] ? payload.steps[stepId].percentage : 100;
    };
    window.updateAllStepPercentages = function () {
        return updateProductionCompletion();
    };

    function appendCompletionToFormData(formData) {
        const payload = updateProductionCompletion();
        formData.set('completion_percentage', payload.overall.percentage.toFixed(1));
        formData.set('step_progress', JSON.stringify(payload));
        return payload;
    }

    function setButtonLoading(stage, loading, closeOnly) {
        const suffix = closeOnly ? 'Close' : 'Nxt';
        const button = document.getElementById('update' + suffix + stage);
        const text = document.getElementById('update' + suffix + 'Text' + stage);
        const loader = document.getElementById('update' + suffix + 'Loader' + stage);
        if (button) button.disabled = loading;
        if (text) text.style.display = loading ? 'none' : 'inline-block';
        if (loader) loader.style.display = loading ? 'inline-block' : 'none';
    }

    function ajaxSave(stage, closeAfter) {
        const form = document.getElementById('staff_form');
        if (!form) return;

        setButtonLoading(stage, true, closeAfter);
        const formData = new FormData(form);
        formData.set('stage', stage);
        formData.set('edit_id', $('#edit_id').val());
        appendCompletionToFormData(formData);

        $.ajax({
            url: "{{ url('/update_staff_by_stage') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response && (response.status === 200 || response.status === true)) {
                    updateProductionCompletion();
                    if (closeAfter) {
                        window.location.href = "{{ url('/hr_enroll/manage_staff') }}";
                    } else if (typeof safeNext === 'function') {
                        safeNext(stage);
                    }
                } else {
                    const msg = response?.message || 'Unable to update staff details.';
                    if (window.toastr) toastr.error(msg);
                }
            },
            error: function (xhr) {
                console.error(xhr);
                const msg = xhr.responseJSON?.message || 'Something went wrong while updating staff details.';
                if (window.toastr) toastr.error(msg);
            },
            complete: function () {
                setButtonLoading(stage, false, closeAfter);
            }
        });
    }

    window.updateAndNext = function (stage) {
        ajaxSave(stage, false);
    };

    window.updateStaff = function (stage) {
        ajaxSave(stage, true);
    };

    window.submit_form = function (stage) {
        const form = document.getElementById('staff_form');
        if (!form) return;
        const btn = document.getElementById('submitStaffBtn');
        const text = document.getElementById('yesBtnText');
        const loader = document.getElementById('yesBtnLoader');
        if (btn) btn.disabled = true;
        if (text) text.style.display = 'none';
        if (loader) loader.style.display = 'inline-block';

        const formData = new FormData(form);
        formData.set('stage', stage || 10);
        formData.set('edit_id', $('#edit_id').val());
        appendCompletionToFormData(formData);

        $.ajax({
            url: "{{ url('/update_staff_by_stage') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response && (response.status === 200 || response.status === true)) {
                    window.location.href = "{{ url('/hr_enroll/manage_staff') }}";
                } else if (window.toastr) {
                    toastr.error(response?.message || 'Unable to update staff.');
                }
            },
            error: function (xhr) {
                if (window.toastr) toastr.error(xhr.responseJSON?.message || 'Unable to update staff.');
            },
            complete: function () {
                if (btn) btn.disabled = false;
                if (text) text.style.display = 'inline-block';
                if (loader) loader.style.display = 'none';
            }
        });
    };

    // Production-safe qualification loader. Existing saved Major is applied only after AJAX options exist.
    window.qualification_change = function (element, selectedMajor) {
        const qualificationId = $(element).val();
        const row = $(element).closest('.education-row');
        const majorWrapper = row.find('select[name="major[]"]').closest('.col-lg-4');
        const majorDropdown = row.find('select[name="major[]"]');
        const hideMajor = QUALIFICATION_NO_MAJOR.includes(String(qualificationId));

        if (hideMajor) {
            majorWrapper.hide();
            majorDropdown.val('').trigger('change');
            majorDropdown.removeClass('required-field');
            updateProductionCompletion();
            return $.Deferred().resolve().promise();
        }

        majorWrapper.show();
        majorDropdown.addClass('required-field');
        majorDropdown.empty().append('<option value="">Select Major</option>');

        if (!qualificationId) {
            updateProductionCompletion();
            return $.Deferred().resolve().promise();
        }

        return $.ajax({
            url: "{{ route('major_list_by_qualification') }}",
            type: 'GET',
            data: { qualification_id: qualificationId }
        }).done(function (response) {
            if (response.status === 200 && response.data) {
                response.data.forEach(function (item) {
                    majorDropdown.append($('<option></option>').val(item.sno).text(item.major_name));
                });
                majorDropdown.append('<option value="Others">Others</option>');
                if (selectedMajor !== undefined && selectedMajor !== null && selectedMajor !== '') {
                    majorDropdown.val(String(selectedMajor)).trigger('change');
                }
            }
            updateProductionCompletion();
        }).fail(function () {
            updateProductionCompletion();
        });
    };

    function renderExistingAttachments() {
        const records = @json($attachments ?? []);
        if (!Array.isArray(records) || !records.length) return;

        const docTypes = @json(($documentTypeList ?? collect())->map(function($d) { return ['id' => $d->sno, 'name' => $d->document_name]; })->values());
        const docName = {};
        docTypes.forEach(d => { docName[String(d.id)] = d.name; });

        const normalizedRecords = records.map(r => {
            let files = safeJson(r.attachment_name, []);
            if (!Array.isArray(files)) files = files ? [files] : [];
            return {
                document_id: r.document_id,
                files: files
            };
        }).filter(r => r.document_id);

        const wrapper = $('#document-wrapper');
        if (!wrapper.length) return;

        // The backend uses doc_type[] to determine which existing document types remain active.
        // Restore those document rows before any new rows are added.
        const firstRow = wrapper.find('.document-row:first');
        firstRow.find('.select2-container').remove();
        firstRow.find('select[name="doc_type[]"]').select2?.('destroy');

        normalizedRecords.forEach((record, index) => {
            let row;
            if (index === 0) {
                row = firstRow;
            } else {
                row = firstRow.clone(false, false);
                row.find('.select2-container').remove();
                row.find('.file-previews').empty();
                row.find('input[type="file"]').val('');
                wrapper.append(row);
            }

            const select = row.find('select[name="doc_type[]"]');
            select.attr('name', 'doc_type[' + index + ']');
            select.val(String(record.document_id));
            select.select2({ width: '100%' });

            row.find('.staff_doc_del').toggle(index > 0);

            // Each restored row must have a unique uploaded_files index.
            let uploadedInput = row.find('input[name^="uploaded_files"]');
            if (!uploadedInput.length) {
                uploadedInput = $('<input type="hidden">').appendTo(row.find('.dropzone'));
            }
            uploadedInput.attr('name', 'uploaded_files[' + index + ']');
            uploadedInput.val('');

            // Keep the Dropzone id unique. The existing first row already owns the live instance.
            if (index > 0) {
                row.find('.dropzone').attr('id', 'dropzone-multi_staff_restored_' + index);
            }

            let panel = row.find('.existing-attachment-panel');
            if (!panel.length) {
                panel = $('<div class="existing-attachment-panel"></div>');
                row.find('.dropzone').after(panel);
            }

            panel.html(
                '<div class="small fw-bold text-dark mb-1"><i class="mdi mdi-paperclip me-1"></i>Existing files</div>' +
                (record.files.length ? record.files.map(function (file) {
                    const safeFile = encodeURIComponent(file);
                    const url = "{{ url('/staff_attachments') }}/" + encodeURIComponent($('#edit_id').val()) + "/" + encodeURIComponent(record.document_id) + "/" + safeFile;
                    return '<div class="existing-attachment-item">' +
                        '<span class="existing-attachment-name"><i class="mdi mdi-file-document-outline me-1"></i>' + $('<div>').text(file).html() + '</span>' +
                        '<a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="' + url + '"><i class="mdi mdi-eye-outline me-1"></i>View</a>' +
                    '</div>';
                }).join('') : '<div class="text-muted small">No existing file recorded.</div>')
            );
        });

        // Re-number any existing/new rows and ensure each row has a unique Dropzone index.
        wrapper.find('.document-row').each(function (index) {
            const row = $(this);
            row.find('select[name^="doc_type"]').attr('name', 'doc_type[' + index + ']');
            const dz = row.find('.dropzone');
            if (dz.length && !dz.attr('id')) dz.attr('id', 'dropzone-multi_staff_existing_' + index);
        });
    }

    function bindProductionListeners() {
        const form = document.getElementById('staff_form');
        if (!form || form.dataset.productionCompletionBound === '1') return;
        form.dataset.productionCompletionBound = '1';

        $(form).on('input.productionCompletion change.productionCompletion changeDate.productionCompletion select2:select.productionCompletion select2:unselect.productionCompletion', 'input,select,textarea', function () {
            window.requestAnimationFrame(updateProductionCompletion);
        });

        $(document).on('change.productionCompletion', '.toggle-field, #vehicle_check, input[name="has_children"], input[name="has_Siblings"], input[name="is_working"], input[name="is_Course"]', function () {
            window.requestAnimationFrame(updateProductionCompletion);
        });

        $(document).on('click.productionCompletion', '.staff_edu_del, .staff_work_del, .altmobile_del, .staff_doc_del, #add-edu-btn, #add-work-btn, #add-altmobile-btn, #add-doc-btn', function () {
            setTimeout(updateProductionCompletion, 50);
        });
    }

    $(function () {
        // Existing page already loads dynamic children/siblings/contacts. Run after those handlers have initialized.
        setTimeout(function () {
            renderExistingAttachments();
            bindProductionListeners();
            updateProductionCompletion();
        }, 250);
    });

    window.addEventListener('load', function () {
        setTimeout(function () {
            // Dynamic rows are now present; recompute from actual visible controls.
            updateProductionCompletion();
        }, 100);
    });
})();
</script>
<!-- https://chatgpt.com/share/6a94983e-0c68-83e8-b3d5-57c71a652685 -->
@endsection
