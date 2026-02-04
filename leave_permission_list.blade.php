@extends('layouts/layoutMaster')

@section('title', 'Manage Staff')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
])
@endsection
@section('page-script')
    @vite(['resources/assets/js/form_wizard_icons.js'])
    @vite('resources/assets/js/forms-file-upload.js')
    @vite('resources/assets/js/forms-pickers.js')
     @vite(['resources/assets/js/forms_date_time_pickers.js'])

@endsection
@section('content')

@php
  
    $helper = new \App\Helpers\Helpers();
    $common_date_format = $helper->general_setting_data()->date_format ?? 'd-M-y';
    $user_id = auth()->user()->user_id ;
    $auth_id = auth()->user()->id ;
  @endphp
<style>
    .dataTables_scroll {
        max-height: 200px;
    }

    .floating-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        /* animation: floatBounce 2.5s ease-in-out infinite; */
    }

    .view-btn{
        width: 100%;
        height: 100%;
        display:none;
        color:black;
        background-color:rgba(0,0,0,0.2);
    }

    .document-thumbnail:hover .view-btn{
        display:flex;
        justify-content:center;
        align-items:center;
    }
/*
    @keyframes floatBounce {
        0%   { transform: translateY(0px); }
        50%  { transform: translateY(-8px); }
        100% { transform: translateY(0px); }
    } */
</style>

<style>
    .act_bx:hover {
        background-color: #eae1fc;

    }

    .act_bx_selected {
        background-color: #fbecdb !important;
        border: 1px solid #766e6e70 !important;
    }


    .approval-ui { border-radius:18px; }

.section-title{
    font-weight:600;
    color:#374151;
    margin-bottom:8px;
}

.employee-preview{
    max-height:300px;
    overflow:auto;
    background:#f8fafc;
    border-radius:12px;
    padding:10px;
}

.emp-item{
    display:flex;
    align-items:center;
    gap:10px;
    padding:6px 8px;
    border-radius:8px;
    transition:.2s;
}
.emp-item:hover{ background:#eef2ff; }

.emp-avatar{
    width:32px;height:32px;
    border-radius:50%;
    background:#6366f1;
    color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-weight:600;
}

/* LEVEL CARDS */
.level-card{
    border-radius:14px;
    border:1px solid #e5e7eb;
    background:#fff;
    box-shadow:0 4px 14px rgba(0,0,0,.05);
    margin-bottom:14px;
}

.level-header{
    background:#f1f5f9;
    border-bottom:1px solid #e5e7eb;
    padding:10px 14px;
    border-top-left-radius:14px;
    border-top-right-radius:14px;
    font-weight:600;
}

.remove-level{ cursor:pointer;color:#ef4444 }
.emp-avatar-wrapper{
    width:38px;
    height:38px;
    border-radius:50%;
    overflow:hidden;
    flex-shrink:0;
}

.emp-avatar-img{
    width:100%;
    height:100%;
    border-radius:50%;
    object-fit:cover;
    color:#fff;
    font-weight:600;
    font-size:14px;
}
.level-card{
    border:1px solid #e5e7eb;
    border-left:4px solid #6366f1;
    transition:.25s;
    background:#fff;
}

.level-card:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.level-header{
    background:#f8fafc;
    font-weight:600;
    padding:12px 16px;
}

.level-title{
    display:flex;
    align-items:center;
    font-size:14px;
}

.level-number{
    font-size:12px;
    padding:5px 8px;
}

.remove-level{
    cursor:pointer;
    color:#ef4444;
    font-size:18px;
}

.level-card .form-select{
    border-radius:10px;
}

#approvalLevels{
    min-height:150px;
    border:2px dashed #e5e7eb;
    padding:10px;
    border-radius:12px;
}

.workflow-preview{
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:14px;
    background:#fbfdff;
}

.preview-title{
    font-weight:600;
    margin-bottom:10px;
    color:#374151;
}

.flow-preview-box{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    min-height:60px;
}

.flow-node{
    padding:8px 14px;
    border-radius:999px;
    background:#eef2ff;
    font-weight:600;
    position:relative;
}

.flow-node.start{ background:#dcfce7; }
.flow-node.hr{ background:#fee2e2; }
.flow-node.manager{ background:#fef3c7; }

.flow-arrow{
    font-size:18px;
    color:#9ca3af;
}


</style>
<style>
    /* ===== MODERN HR LEAVE UI ===== */

.leave-steps{
    display:flex;
    justify-content:space-between;
    margin-bottom:25px;
    position:relative;
}

.leave-steps:before{
    content:'';
    position:absolute;
    top:18px;
    left:0;
    right:0;
    height:3px;
    background:#e5e7eb;
}

.step{
    position:relative;
    background:#fff;
    padding:8px 16px;
    border-radius:30px;
    font-weight:600;
    color:#6b7280;
    border:2px solid #e5e7eb;
    z-index:2;
    transition:.25s;
}

.step.active{
    background:#6366f1;
    color:#fff;
    border-color:#6366f1;
    box-shadow:0 6px 18px rgba(99,102,241,.25);
}

/* Card sections */
.leave-card{
    background:#f9fafb;
    border-radius:16px;
    padding:18px;
    border:1px solid #e5e7eb;
    margin-bottom:18px;
}

/* request selector */
.request-type-box{
    display:flex;
    gap:20px;
}

.type-card{
    flex:1;
    border:2px solid #e5e7eb;
    border-radius:16px;
    padding:20px;
    text-align:center;
    cursor:pointer;
    transition:.25s;
}

.type-card:hover{
    transform:translateY(-3px);
    box-shadow:0 12px 30px rgba(0,0,0,.08);
}

.type-card.active{
    border-color:#6366f1;
    background:#eef2ff;
}

.type-icon{
    font-size:32px;
    margin-bottom:10px;
}

/* date planner */
.leave-date-input{
    background:#ffffff;
    border-radius:14px !important;
    border:1px solid #e5e7eb !important;
    transition:.2s;
}

.leave-date-input:hover{
    border-color:#6366f1 !important;
}

/* summary */
.leave-summary{
    background:#111827;
    color:#fff;
    border-radius:16px;
    padding:18px;
}

.flow-preview-box-req{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    min-height:60px;
}

.flow-node-req{
    padding:8px 14px;
    border-radius:999px;
    background:#eef2ff;
    font-weight:600;
}

.flow-node-req.employee{ background:#dcfce7; }
.flow-node-req.manager{ background:#fef3c7; }
.flow-node-req.hr{ background:#fee2e2; }
.flow-node-req.director{ background:#e0f2fe; }

.flow-arrow-req{
    font-size:18px;
    color:#9ca3af;
}


</style>
<!-- Lead List Table -->
<div class="card card-action">
    <div class="card-header d-flex justify-content-between border-bottom pb-0 mb-0">
        <div class="d-flex flex-column align-items-start">
                <h5 class="card-title mb-1 text-black">Leave & Permission</h5>
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
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="javascript:void(0);" class="active-link">
                                HR Operation
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>
        <div>
            <!-- <a href="javascript:;" class="btn btn-sm fw-bold btn-primary text-white" id="branch_filter" data-bs-toggle="modal" data-bs-target="#kt_modal_filter">
                <span><i class="mdi mdi-filter-outline" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filter"></i></span>
            </a> -->
            <a href="javascript:;" class="btn btn-sm fw-bold btn-primary text-white me-1" id="filter">
                 <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filter"><i class="mdi mdi-filter-outline text-center"></i></span>
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-end">
            <div class="d-flex justify-content-end align-items-center mb-2 gap-2">
                <a href="javascript:;" class="btn btn-sm fw-bold btn-primary text-white" id="branch_filter" data-bs-toggle="modal" data-bs-target="#kt_modal_add_leave_request">
                    <span class="me-2"><i class="mdi mdi-plus"></i></span>Leave Request
                </a>
                <a href="javascript:;" class="btn btn-sm fw-bold btn-primary text-white" id="" data-bs-toggle="modal" data-bs-target="#approvalMatrixModal">
                    Approval Form
                </a>
            </div>
        </div>
        <div class="filter_tbox" style="display: none;">
            <input type="hidden" name="page" value="{{ request('page', 1) }}">
            <input type="hidden" name="filter_on" value="1">
            <input type="hidden" class="sorting_filter_class" name="sorting_filter" id="sorting_filter" value="@php echo $perpage ? $perpage : 25; @endphp" />
            
            <div class="row mb-3 border rounded py-1">
                <div class="col-lg-4 mb-2">
                    <label class="text-black mb-1 fs-6 fw-semibold">Company Name<span class="text-danger">*</span></label>
                    <select id="company_fill" name="company_fill" class="select3 form-select">
                        <option value="">Select Company Name</option>
                        <option value="egc">Elysium Groups</option>
                        @if(isset($company_list))
                        @foreach($company_list as $clist)
                            <option value="{{$clist->sno}}" {{$company_fill== $clist->sno ? 'selected':''}}>{{$clist->company_name}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-4 mb-2">
                    <label class="text-black mb-1 fs-6 fw-semibold">Entity Name<span class="text-danger">*</span></label>
                    <select id="entity_fill" name="entity_fill" class="select3 form-select">
                        <option value="">Select Entity Name</option>
                    </select>
                </div>
                <div class="col-lg-4 mb-2">
                    <label class="text-black mb-1 fs-6 fw-semibold">Department Name<span class="text-danger">*</span></label>
                    <select id="department_fill" name="department_fill" class="select3 form-select">
                        <option value="">Select Department Name</option>
                    </select>
                </div>
                <div class="col-lg-4 mb-2">
                    <label class="text-black mb-1 fs-6 fw-semibold">Division Name<span class="text-danger">*</span></label>
                    <select id="division_fill" name="division_fill" class="select3 form-select">
                        <option value="">Select Division Name</option>
                    </select>
                </div>
                <div class="col-lg-4 mb-2">
                    <label class="text-black mb-1 fs-6 fw-semibold">Job Role<span class="text-danger">*</span></label>
                    <select id="job_role_fill" name="job_role_fill" class="select3 form-select">
                        <option value="">Select Job Role Name</option>
                    </select>
                </div>
                <div class="col-lg-4 mb-2">
                    <label class="text-dark mb-1 fs-6 fw-semibold">Date</label>
                    <select class="select3 form-select" name="dt_fill_issue_rpt" id="dt_fill_issue_rpt" onchange="date_fill_issue_rpt();">
                        <option value="all">All</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="custom_date">Custom Date</option>
                    </select>
                </div>
                <div class="col-lg-4 mb-2" id="today_dt_iss_rpt" style="display: none;">
                    <label class="text-dark mb-1 fs-6 fw-semibold">Today</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-gray-200"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                        <input type="text" id="cus_today_dt_fill" placeholder="Select Date" class="form-control" value="<?php echo date("d-M-Y"); ?>" disabled />
                    </div>
                </div>
                <div class="col-lg-4 mb-2" id="week_from_dt_iss_rpt" style="display: none;">
                    <label class="text-dark mb-1 fs-6 fw-semibold">Start Date</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-gray-200"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                        <input type="text" id="cus_week_st_dt_fill" placeholder="Select Date" class="form-control" value="<?php echo date("d-M-Y"); ?>" disabled />
                    </div>
                </div>
                <div class="col-lg-4 mb-2" id="week_to_dt_iss_rpt" style="display: none;">
                    <label class="text-dark mb-1 fs-6 fw-semibold">End Date</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-gray-200"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                        <input type="text" id="cus_week_ed_dt_fill" placeholder="Select Date" class="form-control" value="<?php echo date("d-M-Y"); ?>" disabled />
                    </div>
                </div>
                <div class="col-lg-4 mb-2" id="monthly_dt_iss_rpt" style="display: none;">
                    <label class="text-dark mb-1 fs-6 fw-semibold">This Month</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                        <input type="text" id="cus_this_month_dt_fill" placeholder="Select Date" class="form-control this_month_dt_fill" value="<?php echo date("M-Y"); ?>" />
                    </div>
                </div>
                <div class="col-lg-4 mb-2" id="from_dt_iss_rpt" style="display: none;">
                    <label class="text-dark mb-1 fs-6 fw-semibold">From Date</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                        <input type="text" id="cus_custom_from_dt_fill" placeholder="Select Date" class="form-control common_datepicker" value="<?php echo date("d-M-Y"); ?>" />
                    </div>
                </div>
                <div class="col-lg-4 mb-2" id="to_dt_iss_rpt" style="display: none;">
                    <label class="text-dark mb-1 fs-6 fw-semibold">To Date</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                        <input type="text" id="cus_custom_to_dt_fill" placeholder="Select Date" class="form-control common_datepicker" value="<?php echo date("d-M-Y"); ?>" />
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end mt-3 mb-3">
                <a href="{{ url('/hr_enroll/manage_staff') }}" class="btn btn-secondary btn-sm me-3">Reset</a>
                <a href="javascript:;"  class="filterSubmit" id="filterSubmit" >
                    <span class="btn btn-primary btn-sm" > Go</span>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-2 py-1 rounded bg-label-warning" style="border: 1px solid #fba919; display: none;" id="filter_div">
                <div class="row">
                    <div class="col-lg-4 border-end border-danger">
                        <div class="row">
                            <label class="col-5 fw-semibold fs-6 text-danger">Staff</label>
                            <label class="col-1 fw-semibold fs-6 text-danger">:</label>
                            <label class="col-6 fw-bold fs-6 text-danger">Kanimozhi</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end gap-3 py-2">
                    <a href="javascript:void(0)" onclick="clearFilter()"
                    class="btn btn-sm fw-bold text-white" style="background-color: #350501ff">
                        Clear Filter
                    </a>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="d-flex align-items-center justify-content-between mb-4 ">
                    <div>
                        <span>Show</span>
                        <select id="perpage" class="form-select form-select-sm w-75px"
                            onchange="loadThemes(1)">
                            @php $options = [5,10, 25, 100, 500]; @endphp
                            @foreach ($options as $option)
                                <option value="{{ $option }}" {{ $perpage == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center justify-content-end flex-wrap gap-2">
                        <div class="searchBar">
                            <input type="text" id="search_filter" class="searchQueryInput"
                                placeholder="Search Staff Name/mobile..."
                                value="{{ $search_filter }}"/>
                            
                            <div class="searchAction">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:;"  class="searchSubmit" id="searchSubmit" >
                                        <span class="mdi mdi-magnify fs-4 fw-bold"  style="color:#ab2b22;"></span>
                                    </a>
                                    <a href="javascript:;" class="refreshBar" id="refreshSearch" >
                                        <span class="mdi mdi-refresh fs-4 fw-bold" style="color:#ab2b22;"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                 <div class="table-responsive">
                    <table class="table align-middle table-row-dashed  table-striped table-hover gy-0 gs-1 ">
                        <thead>
                            <tr class="text-start align-top  fw-bold fs-6 gs-0 bg-primary" >
                                <th class="min-w-100px">Staff</th>
                                <th class="min-w-100px">Dept /Div /<br>Job Role</th>
                                <th class="min-w-100px">Request Type</th>
                                <th class="min-w-100px">Leave / Permission Details</th>
                                <th class="min-w-50px">Status</th>
                                <th class="min-w-80px text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fw-semibold fs-7" id="list-table-body" >
                            <tr class="skeleton-loader" id="skeleton-loader" style="border-left: 5px solid #e2e2e2;">
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                                <td class="skeleton-cell">
                                    <div class="skeleton"></div>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="text-center my-3" id="pagination-container">
                <!-- Pagination buttons will appear here -->
            </div>
        </div>
    </div>
</div>



<div class=" modal fade" id="approvalMatrixModal" tabindex="-1" aria-hidden="true" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content approval-ui">

            <div class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold text-primary">
                    <i class="mdi mdi-sitemap-outline me-2"></i>
                    Leave / Permission Approval Workflow
                </h4>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2">

                <div class="row g-0">

                    <!-- LEFT SIDE -->
                    <div class="col-lg-4 border-end pe-lg-4">

                        <h6 class="section-title">Requesting Employees</h6>

                        <label>Company</label>
                        <select id="company_id_approv" name="company_id_approv" class="form-select select3" required onchange="comapnyAproveChange()">
                            <option value="">Select Company Name</option>
                            <option value="egc">Elysium Groups</option>
                            @foreach($company_list as $clist)
                            <option value="{{$clist->sno}}">{{$clist->company_name}}</option>
                            @endforeach
                        </select>
                        <div class="business_div_approv">
                            <label>Entity</label>
                            <select id="entity_id_aprov" name="entity_id_aprov" class="form-select select3" required onchange="entityAproveChange()">
                                <option value="">Select Entity Name</option>
                            </select>
                        </div>
                        <label class="mt-3">Department</label>

                        <select id="department_id_aprov" class="form-select select3" onchange="departAproveChange()"></select>

                        <label class="mt-3">Role</label>
                        <select id="role_id_aprov" class="form-select select3" onchange="roleAproveChange()"></select>

                        <hr>

                        <h6 class="section-title">Employees under this Role</h6>
                        <div id="roleEmployees" class="employee-preview"></div>

                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="col-lg-8 ps-lg-4">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="section-title">Approval Hierarchy</h6>
                            <button class="btn btn-light-primary btn-sm" id="addLevelBtn">
                                <i class="mdi mdi-plus"></i> Add Level
                            </button>
                        </div>
                        <div class="workflow-preview mb-3">
                            <div class="preview-title">
                                <i class="mdi mdi-source-branch"></i> Workflow Preview
                            </div>

                            <div id="flowPreview" class="flow-preview-box">
                                <div class="flow-node start">Employee</div>
                            </div>
                        </div>
                        <div id="approvalLevels" class="levels-container"></div>

                    </div>

                </div>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary px-4" id="saveMatrixBtn">
                    <i class="mdi mdi-content-save-outline me-1"></i> Save Workflow
                </button>
            </div>

        </div>
    </div>
</div>



<!--begin::Modal - Delete Staff-->
<div class=" modal fade" id="kt_modal_delete_staff" tabindex="-1" aria-hidden="true" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-m">
        <!--begin::Modal content-->
        <div class="modal-content rounded">
            <div class="swal2-icon swal2-danger swal2-icon-show" style="display: flex;">
                <!-- <div class="swal2-icon-content"><i class="mdi mdi-trash fs-2 text-danger"></i></div> -->
                <div>
                    <i class="fa-solid fa-trash text-danger" style="font-size: 35px;"></i>
                </div>
            </div>
            <div class="swal2-html-container mb-4" id="swal2-html-container" style="display: block;">
                <span id="delete_message">Are you sure you want to delete <br><b class="text-danger">Mahesh </b> Staff ?</span>
            </div>
            <div class="d-flex justify-content-center align-items-center pt-8 mb-4">
                <button type="submit" class="btn btn-danger me-3"  onclick="deleteFunc()">Yes,delete!</button>
                <button type="reset" class="btn btn-secondary text-black" data-bs-dismiss="modal">No,cancel</button>
            </div>
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Delete Staff-->






<!--begin::Modal Filter--->
<div class="modal fade" id="kt_modal_add_leave_request" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static" data-bs-focus="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded">
            <div class="modal-header justify-content-end border-0 pb-0">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor" />
                            <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="currentColor" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                <div class="mb-8 text-center">
                    <h3 class="text-center mb-4 text-black">Leave Request</h3>
                </div>

                <div class="modal-body pt-4 pb-5 px-4 px-xl-6">
                    <form method="POST" action="{{ route('add_leave_perm_request') }}" enctype="multipart/form-data" autocomplete="off" id="addLeaveRequestForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Company Name<span class="text-danger">*</span></label>
                                <select id="staff_company_name" name="staff_company_name" class="form-select select3" required>
                                    <option value="">Select Company Name</option>
                                    <option value="egc">Elysium Groups</option>
                                    @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}">{{$clist->company_name}}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a valid company name.</div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Entity Name<span class="text-danger">*</span></label>
                                <select id="staff_entity_name" name="staff_entity_name" class="form-select select3" required>
                                    <option value="">Select Entity Name</option>
                                </select>
                                <div class="invalid-feedback">Please select a valid entity name.</div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Branch Name<span class="text-danger">*</span></label>
                                <select id="staff_branch_name" name="staff_branch_name" class="form-select select3" required>
                                    <option value="">Select Branch Name</option>
                                </select>
                                <div class="invalid-feedback">Please select a valid branch name.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Department<span class="text-danger">*</span></label>
                                <select id="department_id" name="department_id" class="form-select select3" required>
                                    <option value="">Select Department</option>
                                </select>
                                <div class="invalid-feedback">Please select a valid department.</div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="text-black mb-1 fs-6 fw-semibold">Staff<span class="text-danger">*</span></label>
                                <select id="staff_id" name="staff_id" class="form-select select3" required>
                                    <option value="">Select Staff</option>
                                </select>
                                <div class="invalid-feedback">Please select a staff member.</div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="fs-6 fw-semibold">Request Type<span class="text-danger">*</span></label>
                                <select id="request_type_id" name="request_type_id" class="form-select select3" onchange="toggleLeaveOrPermission()" required>
                                    <option value="">Select Request Type</option>
                                    <option value="Leave">Leave</option>
                                    <option value="Permission">Permission</option>
                                </select>
                                <div class="invalid-feedback">Please select a request type.</div>
                            </div>
                        </div>

                        <div id="leave_dates_section">
                            <div id="leave_date_list" class="px-5 pt-3 pb-1" style="max-height: 400px; overflow-y: auto; padding-right: 10px;"></div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-primary btn-sm" onclick="addLeaveOrPermissionDate()" disabled id="add_more_btn">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4" id="leave_days_div" style="display:none;">
                                    <label class="fs-6 fw-semibold" id="leave_days_label" style="display:none;">Total Leave Days</label>
                                    <div>
                                        <span id="total_leave_days" class="badge text-center bg-primary fs-8" style="display:none;">0</span>
                                    </div>
                                </div>
                                <div class="col-lg-4" id="permission_hrs_div" style="display:none;">
                                    <label class="fs-6 fw-semibold" id="permission_hours_label" style="display:none;">Total Permission Hours</label>
                                    <div>
                                        <span id="total_permission_hours" class="badge text-center bg-primary fs-8" style="display:none;">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="leave-card mb-3">
                                <label class="fw-semibold mb-2">Request Type</label>

                                <div class="request-type-box">
                                    <div class="type-card" data-type="Leave">
                                        <div class="type-icon"></div>
                                        <h5>Leave</h5>
                                        <small>Full / Half day leave</small>
                                    </div>

                                    <div class="type-card" data-type="Permission">
                                        <div class="type-icon"></div>
                                        <h5>Permission</h5>
                                        <small>Short time permission</small>
                                    </div>
                                </div>

                                <input type="hidden" name="request_type" id="request_type">
                            </div>


                            <!-- Planner -->
                            <div class="leave-card mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="fw-semibold">Schedule Planner</label>
                                    <button type="button" class="btn btn-sm btn-primary" id="addRow">
                                        <i class="mdi mdi-plus"></i> Add
                                    </button>
                                </div>

                                <div id="plannerRows"></div>
                            </div>


                            <!-- Live Summary -->
                            <div class="leave-summary">
                                <h5 class="mb-2">Live Summary</h5>
                                <div id="liveSummary">No entries added</div>
                            </div>

                            <div class="leave-card mt-3">
                                <h5 class="mb-3">
                                    <i class="mdi mdi-sitemap-outline me-1"></i>
                                    Approval Flow
                                </h5>

                                <div id="approvalPreview" class="flow-preview-box-req">
                                    <div class="text-muted">Select employee to load approval flow</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary" id="submitStaffBtn" onclick="submitLeaveRequest()">Submit Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!--end::Modal - Filter-->
  


   <script>
     function submit_form() {
        const form = document.getElementById("addOldStaffForm");
        const submitBtn = document.getElementById("submitStaffBtn");

        if (form) {
            // Disable the button to prevent duplicate submission
            submitBtn.disabled = true;

            // ✅ Create FormData manually
            const formData = new FormData(form);

            // ✅ Send via AJAX (so files are sent correctly)
            $.ajax({
                url: form.action,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log("Success:", response);
                    // window.location.href = '/hr_enroll/manage_staff';
                    submitBtn.disabled = false;
                },
                error: function(err) {
                    console.error("Error:", err);
                    // In case of an error, re-enable the button and reset the text
                    submitBtn.disabled = false;
                }
            });
        }
    }
   </script>

   <script>
     function submit_timestamp_form() {
        const form = document.getElementById("updateTimestampForm");
        const submitBtn = document.getElementById("submitTimestampBtn");

        if (form) {
            // Disable the button to prevent duplicate submission
            submitBtn.disabled = true;

            // ✅ Create FormData manually
            const formData = new FormData(form);

            // ✅ Send via AJAX (so files are sent correctly)
            $.ajax({
                url: form.action,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log("Success:", response);
                    window.location.href = '/hr_enroll/manage_staff';
                },
                error: function(err) {
                    console.error("Error:", err);
                    // In case of an error, re-enable the button and reset the text
                    submitBtn.disabled = false;
                }
            });
        }
    }
   </script>

<script>
    let currentPage = 1;
    let isLoading = false;
    let abortController = new AbortController();
    let auth_user_id =@json($user_id);

    function formatDate(date) {
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
        return new Date(date).toLocaleDateString('en-GB', options);
    }
   
    function buildRow(item,index) {    
        
        var data = typeof item.data === 'string' ? JSON.parse(item.data) : item.data;
  
        let staff_image = '';
        if (data.staff_image && data.staff_image.trim() !== '') {
            if (data.company_type == 1) {
                staff_image = `staff_images/Management/${data.staff_image}`;
            } else {
                staff_image = `staff_images/Buisness/${data.company_id}/${data.entity_id}/${data.staff_image}`;
            }
        } else {
            staff_image = data.gender == 1
                ? 'assets/egc_images/auth/user_2.png'
                : 'assets/egc_images/auth/user_2.png';
        }
        staff_image = `{{ asset('${staff_image}') }}`;
         
        return `
            <tr>
                <td style="position:relative;">
                    <div style="position:absolute; left:0; top:0; bottom:0; width:5px; background:${item.company_base_color || ''};"></div>
                    <div class="d-flex">
                        <div class="symbol symbol-35px me-2">
                            <div class="image-input image-input-circle">
                                <img src="${staff_image}" 
                                    alt="user-avatar"  class="w-px-40 h-px-40 rounded-circle"
                                    id="uploadedlogo" onerror="this.onerror=null; this.src='{{ asset('assets/egc_images/auth/') }}' ${(item.gender == 1 ? 'user_2.png' : 'user_2.png')}"/>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="d-flex gap-1 no-wrap">
                                <span class="fs-7 me-1 text-nowrap" data-bs-toggle="tooltip" data-bs-placement="bottom" title="${item.nick_name || '-'}">${item.staff_name}</span>
                                    ${item.gender == 1 ?
                                        `<span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Male"><i
                                            class="mdi mdi-face-man text-info"></i></span>`
                                    : 
                                        `<span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Female"><i
                                            class="mdi mdi-face-woman text-info"></i></span>`
                                    }
                                <div class="">
                                    <a href="javascript:;" class="dropdown-toggle hide-arrow "
                                        data-bs-toggle="dropdown" data-trigger="hover">
                                        <i class="ms-1 mdi mdi-information fs-9" style="color: ${item.company_base_color || ''} !important;"></i>
                                    </a>
                                    <div class="dropdown-menu py-2 px-4 text-black scroll-y w-400px max-h-250px">
                                        <div class="mb-2 d-flex">
                                            <div class="fw-semibold w-30">Mob No</div>
                                            <div class="mx-1">:</div>
                                            <div class="fw-bold">${data.mobile_no || '-'}</div>
                                        </div>
                                        <div class="mb-2 d-flex">
                                            <div class="fw-semibold w-30">Email ID</div>
                                            <div class="mx-1">:</div>
                                            <div class="fw-bold">${data.email_id || '-'}</div>
                                        </div>
                                        <div class="mb-2 d-flex">
                                            <div class="fw-semibold w-30">DOB</div>
                                            <div class="mx-1">:</div>
                                            <div class="fw-bold d-flex align-items-center gap-1">
                                                ${data.dob ? formatDateCommon(data.dob) : '-'}
                                                <span 
                                                    class="badge fs-8 bg-info" 
                                                    data-bs-toggle="tooltip" 
                                                    title="${data.dob ? getNextBDayDate(data.dob).full : '-'}">
                                                    ${data.dob ? getNextBDayDate(data.dob).short : '-'}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-2 d-flex">
                                            <div class="fw-semibold w-30">Educational</div>
                                            <div class="mx-1">:</div>
                                            <div class="fw-bold">
                                            ${data.education && Array.isArray(data.education) && data.education.length > 0 ? data.education : '-'}
                                            </div>
                                        </div>
                                    </div>
                                </div>        
                                
                            </label>
                            
                            <div class="d-flex  fs-8" >
                                ${data.company_type == 1 ?
                                    `<div class="d-flex align-items-start justify-content-center flex-column">
                                        <label class="fw-semibold fs-8 text-truncate badge bg-label-danger d-block"
                                            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px;"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="${data.company_name || '-'}">
                                            ${data.company_name || '-'}
                                        </label>
                                    </div>`
                                    :
                                    `<div class="d-flex align-items-start justify-content-center flex-column">
                                        <label class="fw-semibold text-black fs-8 text-truncate d-block"
                                            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px;"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="${data.company_name || '-'}">
                                            ${data.company_name || '-'}
                                        </label>

                                        <label class="fw-semibold fs-8 text-truncate badge  d-block"
                                            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px; background-color: ${item.company_base_color || ''};"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="${data.entity_name || '-'}" >
                                            ${data.entity_name || '-'}
                                        </label>
                                    </div>`
                                }
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                   <div class="d-flex align-items-start justify-content-center flex-column gap-1">
                        <label class="fw-semibold text-black fs-7 text-truncate d-block"
                            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100px;"
                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="${data.department_name || '-'}">
                            ${data.department_name || '-'}
                        </label>
                        <label class="fw-semibold fs-8 text-truncate text-dark d-block"
                            style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100px;"
                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="${data.division_name || '-'}">
                            ${data.division_name || '-'}
                        </label>
                        <label class="badge bg-label-primary fs-7 fw-bold mb-2" >
                            <span class="fs-7">${data.job_role_name || '-'}</span>
                        </label>
                    </div> 
                </td>
                <td>
                    <div class="d-flex align-items-start justify-content-center flex-column">
                        <label class="fw-semibold text-black fs-7 d-block text-nowrap">
                           ${data.date_of_joining ? formatDateCommon(data.date_of_joining) : '-'}
                        </label>
                        <label class="badge bg-warning text-black fw-semibold fs-8 text-dark d-block text-nowrap">
                           Leave
                        </label>
                    </div>
                </td>
                <td>
                    <label class="fw-semibold fs-8 text-truncate text-dark d-block"
                        style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100px;"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Sick Leave">
                        Sick Leave
                    </label>
                </td>
                <td>
                    <span class="badge border border-warning rounded bg-white text-black fw-semibold fs-7" data-bs-toggle="dropdown">
                        Pending
                    </span>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 200px;">
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#kt_modal_approve_job"onclick="updatelevelStatus('${item.staff_name}','${item.sno}',1)">Accept</a>
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#kt_modal_reject_job"onclick="updatelevelStatus('${item.staff_name}','${item.sno}',4)">Recjected</a>
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#kt_modal_reject_job"onclick="updatelevelStatus('${item.staff_name}','${item.sno}',3)">OnHold</a>
                    </div>
                </td>
                
                <td >
                    
      
                    <a href="javascript:;" class="fs-7" data-bs-toggle="modal" data-bs-target="#kt_modal_view_staff" onclick="viewStaff(${item.sno})">
                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="View"> <i class="mdi mdi-eye fs-3 text-dark "></i></span>
                    </a>
                           
                          
                </td>
            </tr>
        `;
    }
    function loadThemes(page = 1) {
        const perpage = document.getElementById('perpage').value;
        const search = "jjkj";
        const company_fill = document.getElementById('company_fill').value;
        const to_dt_iss_rpt = document.getElementById('to_dt_iss_rpt').value;
        const from_dt_iss_rpt = document.getElementById('from_dt_iss_rpt').value;
        const dt_fill_issue_rpt = document.getElementById('dt_fill_issue_rpt').value;
        const job_role_fill = document.getElementById('job_role_fill').value;
        const division_fill = document.getElementById('division_fill').value;
        const department_fill = document.getElementById('department_fill').value;
        const entity_fill = document.getElementById('entity_fill').value;

        const url = `/hr_operation/leave_permission?page=${page}&sorting_filter=${perpage}&search_filter=${search}&company_fill=${company_fill}&entity_fill=${entity_fill}&department_fill=${department_fill}&division_fill=${division_fill}&job_role_fill=${job_role_fill}&dt_fill_issue_rpt=${dt_fill_issue_rpt}&from_dt_iss_rpt=${from_dt_iss_rpt}&to_dt_iss_rpt=${to_dt_iss_rpt}`;

        // Show skeleton loader and clear old data before fetching new data
        isLoading = true;
        document.getElementById('list-table-body').innerHTML = ''; // Clear old data
        document.getElementById('list-table-body').insertAdjacentHTML('beforeend', skeletenRow()); // Clear old data
        $('#skeleton-loader').show(); // Show skeleton loader

        if (abortController.signal) {
            abortController.abort(); // Abort the previous request
        }
        abortController = new AbortController();

         fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: abortController.signal })
            .then(res => res.json())
            .then(res => {
                // Insert new data into the table
                if(res.data.length > 0){
                    res.data.forEach((item, index) => {
                        document.getElementById('list-table-body').insertAdjacentHTML('beforeend', buildRow(item, index + 1));
                    });

                }else{
                    document.getElementById('list-table-body').insertAdjacentHTML('beforeend', NoDataFound());
                }
                    

                // Update pagination and results info
                updatePagination(res.current_page, res.last_page, res.total, perpage);

                // Hide skeleton loader after data is fetched
                isLoading = false;
                $('#skeleton-loader').hide();
                 $('[data-bs-toggle="tooltip"]').tooltip();
            })
            .catch(error => {
                if (error.name !== 'AbortError') { // Only handle abort error
                    console.error('Error loading data:', error);
                }
                // Hide skeleton loader in case of error
                $('#skeleton-loader').hide();
                isLoading = false;
            });
    }

    function skeletenRow(){
        return `
            <tr class="skeleton-loader" id="skeleton-loader">
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
                <td class="skeleton-cell">
                    <div class="skeleton"></div>
                </td>
            </tr>
            `;
    }

    function NoDataFound(){
        return `
            <tr><td colspan="7" class="text-center">No Data Found</td></tr>
            `;
    }

    function updatePagination(currentPage, lastPage, total, perpage) {
        let paginationContainer = document.getElementById('pagination-container');
        paginationContainer.innerHTML = ''; // Clear old pagination

        // Set the pagination container style
        paginationContainer.style.display = "flex";
        paginationContainer.style.justifyContent = "space-between";
        paginationContainer.style.alignItems = "center";

        // Showing result count info (e.g., Showing 1 to 25 of 3556 results)
        let start = (currentPage - 1) * perpage + 1;
        let end = Math.min(currentPage * perpage, total);
        if(total == 0){
            start =0;
        }else{
            start=start;
        }
        let showingInfo = `Showing ${start} to ${end} of ${total} results`;
        paginationContainer.insertAdjacentHTML('beforeend', showingInfo);

        // Create Pagination Buttons

        // << First button
        let firstButton = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}" data-bs-toggle="tooltip" data-bs-placement="top" title="First Page"><button class=" page-link" onclick="loadThemes(1)" >«</button> </li>`;
        
        // < Previous button
        let prevButton = `<li class="page-item ${currentPage > 1 ? '' : 'disabled'}" data-bs-toggle="tooltip" data-bs-placement="top" title="Previous"><button class=" page-link" onclick="loadThemes(${currentPage - 1})" >‹</button> </li>`;
        
        // Next button
        let nextButton = `<li class="page-item ${currentPage < lastPage ? '' : 'disabled'}" data-bs-toggle="tooltip" data-bs-placement="top" title="Next"><button class="page-link" onclick="loadThemes(${currentPage + 1})" >›</button> </li>`;
        
        // >> Last button
        let lastButton = `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}" data-bs-toggle="tooltip" data-bs-placement="top" title="Last Page"><button class=" page-link" onclick="loadThemes(${lastPage})" >»</button> </li>`;

        // Page Number Buttons (Dynamically show a range of pages around the current page)
        let pageButtons = '';
        let range = 2; // Show 2 pages before and after the current page
        let startPage = Math.max(1, currentPage - range);
        let endPage = Math.min(lastPage, currentPage + range);

        // Generate page numbers
        for (let i = startPage; i <= endPage; i++) {
            pageButtons += `<li class="page-item ${i === currentPage ? 'active' : ''}"><button class="page-link " onclick="loadThemes(${i})">${i}</button> </li>`;
        }

        // Add the pagination buttons and page numbers
        paginationContainer.insertAdjacentHTML('beforeend', `
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    ${firstButton}
                    ${prevButton}
                    ${pageButtons}
                    ${nextButton}
                    ${lastButton}
                </ul>
            </nav>
        `);

        // Update perpage dropdown if changed
        document.getElementById('perpage').value = perpage;
    }

    function debounceSearch(e) {
        if (e.keyCode === 13) {
            loadThemes(1);  // Trigger the search when the user presses enter
        }
    }

    // Debounce function to handle input changes
    let debounceTimeout;
    function debounce(fn, delay) {
        return function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(fn, delay);
        };
    };

  

    // SearchBar
    document.getElementById('search_filter').addEventListener('input', function() {
        const searchValue = document.getElementById('search_filter').value;
        if (searchValue) {
            document.getElementById('refreshSearch').style.display = 'inline-block';  // Show the refresh button
        } else {
            document.getElementById('refreshSearch').style.display = 'none';  // Hide the refresh button
        }
    });

     // Listen for changes in the perpage dropdown and reload data
    document.getElementById('perpage').addEventListener('change', () => loadThemes(1));

    // Listen for Enter key in the search filter and reload data
    document.getElementById('search_filter').addEventListener('keyup', debounceSearch);

    document.getElementById('refreshSearch').addEventListener('click', function() {
        document.getElementById('search_filter').value = '';  // Clear the search input
        loadThemes(1);  // Reload the table data without the search filter
    });

    document.getElementById('searchSubmit').addEventListener('click', function() {
        loadThemes(1);  // Reload the table data without the search filter
    });

     document.getElementById('filterSubmit').addEventListener('click', function() {
        loadThemes(1);  // Reload the table data without the search filter
    });

    // Initial load
    loadThemes(1);

</script>

<script>
  function formatDate(dateString) {
    const date = new Date(dateString);

    // Check if the date is valid
    if (isNaN(date.getTime())) {
        return '-'; // Return '-' if the date is invalid
    }

    // Get the day, month (abbreviated), and year
    const day = String(date.getDate()).padStart(2, '0'); // Get day and pad with leading zero
    const month = date.toLocaleString('default', { month: 'short' }); // Get abbreviated month
    const year = date.getFullYear(); // Get full year

    // Construct and return the formatted date string
    return `${day}-${month}-${year}`; // Format as DD-MMM-YYYY
  }
 
 
 
</script>

<script>
   

    function clearFilter() {
        document.getElementById('filter_div').style.display = 'none';
        location.reload();
    }
</script>

<script>
    let levelCount = 0;
    let roleStaff = [];
    let editId = null;

    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        }
    });

         function comapnyAproveChange() {
              console.log("jfhj")
            var companyId = $('#company_id_approv').val();
            var entityDropdown = $('#entity_id_aprov');
          
            companyChangeAprove()
            // Reset dropdown
            entityDropdown.empty().append('<option value="">Select Entity</option>');

            if (companyId) {
                $.ajax({
                    url: "{{ route('entity_list') }}",
                    type: "GET",
                    data: { company_id: companyId },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(entity) {
                                entityDropdown.append(
                                    $('<option></option>')
                                        .attr('value', entity.sno)
                                        .text(entity.entity_name)
                                );
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching entities:', error);
                    }
                });
            }
        };
        // When entity changes staff_branch_name
        function entityAproveChange() {
            var entityId = $('#entity_id_aprov').val();
            var departmentDropdown = $('#department_id_aprov');
            departmentDropdown.empty().append('<option value="">Select Department</option>');

            if (entityId) {
                // Fetch and populate states based on selected country
                $.ajax({
                    url: "{{ route('department') }}",
                    type: "GET",
                    data: {
                        entity_id: entityId
                    },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(state) {
                                departmentDropdown.append($('<option></option>').attr(
                                    'value', state.sno)
                                    .text(state.department_name));
                            });
                            
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Department:', error);
                    }
                });
            }
            
        };

        function departAproveChange() {
            var department_id = $('#department_id_aprov').val();
            var staffDropdown = $('#role_id_aprov');
            staffDropdown.empty().append('<option value="">Select Job Role</option>');
            if (department_id) {
                // Fetch and populate states based on selected country
                $.ajax({
                    url: "{{ route('get_role_by_department') }}",
                    type: "GET",
                    data: {
                        department_id: department_id
                    },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(state) {
                                staffDropdown.append($('<option></option>').attr('value', state.sno).text(state.job_position_name));
                            });
                            
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Staff:', error);
                    }
                });
                    
            }
        };
       function roleAproveChange() {

            let id = $('#role_id_aprov').val();
            if (!id) return;

            $('#roleEmployees').html('<div class="p-3 text-muted">Loading employees...</div>');

            $.get("{{ route('get_staff_by_role') }}", { role_id: id }, function (response) {

                if (response.status === 200) {
                    roleStaff = response.data;   // ❌ you wrote res.data before
                    renderEmployeePreview();
                    refreshApproverDropdowns();
                }

            }).fail(() => {
                $('#roleEmployees').html('<div class="text-danger p-2">Failed to load staff</div>');
            });
        };

            function companyChangeAprove(){
                var company = $('#company_id_approv').val();
                if(company =='egc'){
                    $('.business_div_approv').hide();
                    var departmentDropdown = $('#department_id_aprov');
                    departmentDropdown.empty().append('<option value="">Select Department</option>');
                    var entityId = 0;
                        $.ajax({
                            url: "{{ route('department') }}",
                            type: "GET",
                            data: {
                                entity_id: entityId
                            },
                            success: function(response) {
                                if (response.status === 200 && response.data) {
                                    response.data.forEach(function(state) {
                                        departmentDropdown.append($('<option></option>').attr(
                                            'value', state.sno)
                                            .text(state.department_name));
                                    });
                                    
                                }
                            },
                            error: function(error) {
                                console.error('Error fetching Department:', error);
                            }
                        });
                }else{
                    $('.business_div_approv').show();
                }
            }

 
    function renderEmployeePreview(){

        let html='';

        roleStaff.forEach(emp=>{

        let staff_image = '';
        let img = 'no';
            if (emp.staff_image && emp.staff_image.trim() !== '') {
                img = 'yes';
                if (emp.company_type == 1) {
                    staff_image = `staff_images/Management/${emp.staff_image}`;
                } else {
                    staff_image = `staff_images/Buisness/${emp.company_id}/${emp.entity_id}/${emp.staff_image}`;
                }
            } else {
                img = 'no';
            }
            staff_image = `{{ asset('${staff_image}') }}`;
            html+=`
            <div class="emp-item">
                <div class="emp-avatar">
                     
                ${
                    img !== 'no'
                    ? `<img src="${staff_image}" class="emp-avatar-img" 
                            onerror="this.remove(); this.parentElement.innerHTML=getInitialAvatar('${escapeHtml(emp.staff_name)}')">`
                    : getInitialAvatar(emp.staff_name)
                }                       
                </div>
                <div>
                    <div class="fw-semibold">${emp.staff_name}</div>
                    <small class="text-muted">${emp.nick_name}</small>
                </div>
            </div>`;
        });

        $('#roleEmployees').html(html || '<div class="text-muted p-2">No employees</div>');
    }

    function refreshApproverDropdowns(){

        $('.approver-select').each(function(){

            let selected=$(this).val() || [];

            $(this).empty();

            roleStaff.forEach(emp=>{
                $(this).append(
                    new Option(emp.name,emp.id,false,selected.includes(String(emp.id)))
                );
            });

            $(this).trigger('change.select2');
        });
    }



    $('#addLevelBtn').click(()=>{
        if(roleStaff.length===0){
            alert('Select role first');
            return;
        }
        addLevel();
    })
   function addLevel(){

        levelCount++;

        $('#approvalLevels').append(`
        <div class="level-card" data-level="${levelCount}">

        <div class="level-header d-flex justify-content-between align-items-center">
            <div class="level-title">
                <i class="mdi mdi-drag-vertical me-2 handle text-muted"></i>
                <span class="badge bg-primary level-number">${levelCount}</span>
                <span class="ms-2">Approval Level</span>
            </div>
            <span class="remove-level"><i class="mdi mdi-close"></i></span>
        </div>

        <div class="p-3 row g-2">

            <div class="col-md-3">
                <label>Company</label>
                <select class="form-select level-company"></select>
            </div>

            <div class="col-md-3 level-entity-box">
                <label>Entity</label>
                <select class="form-select level-entity"></select>
            </div>

            <div class="col-md-3">
                <label>Department</label>
                <select class="form-select level-department"></select>
            </div>

            <div class="col-md-3">
                <label>Approver</label>
                <select class="form-select level-staff"></select>
            </div>

        </div>
        </div>`);

        loadLevelCompanies(levelCount);
        }

    let companyList = @json($company_list);
    companyList.unshift({sno:'egc',company_name:'Elysium Groups'})
   function loadLevelCompanies(level){

        let select=$(`[data-level="${level}"] .level-company`);

        fillSelect(select,companyList,'Select Company');
    }

    function updateFlowPreview(){

        let html='<div class="flow-node start">Employee</div>';

        let hasLevel=false;

        $('#approvalLevels .level-card').each(function(){

            let staffName=$(this).find('.level-staff option:selected').text();

            if(staffName && staffName!=='Select Staff'){
                hasLevel=true;

                html+=`<div class="flow-arrow"><i class="mdi mdi-arrow-right"></i></div>
                    <div class="flow-node manager">${staffName}</div>`;
            }
        });

        if(!hasLevel){
            html='<div class="flow-node start">Employee</div>';
        }

        $('#flowPreview').html(html);
    }



$(document).on('change','.level-type,.level-staff,.level-company,.level-entity',updateFlowPreview);
$(document).on('click','.remove-level',updateFlowPreview);
$(document).on('change','.level-staff',updateFlowPreview);



$(document).on('change','.level-type',function(){

    let card=$(this).closest('.level-card');

    if($(this).val()==='custom'){
        card.find('.level-staff-box').show();

        let dept=$('#department_id_aprov').val();

        $.get("{{ route('get_staff_by_depart') }}",{department_id:dept},res=>{
            fillSelect(card.find('.level-staff'),res.data,'Select Employee');
        });

    }else{
        card.find('.level-staff-box').hide();
    }

    updateFlowPreview();
});
$(document).on('change','.level-company',function(){

    let card=$(this).closest('.level-card');
    let company=$(this).val();

    if(company==='egc'){
        card.find('.level-entity-box').hide();

        $.get("{{ route('department') }}",{entity_id:0},res=>{
            fillSelect(card.find('.level-department'),res.data,'Select Department');
        });

    }else{

        card.find('.level-entity-box').show();

        $.get("{{ route('entity_list') }}",{company_id:company},res=>{
            fillSelect(card.find('.level-entity'),res.data,'Select Entity');
        });
    }

    updateFlowPreview();
});


$(document).on('change','.level-entity',function(){

    let card=$(this).closest('.level-card');
    let entity=$(this).val();

    $.get("{{ route('department') }}",{entity_id:entity},res=>{
        fillSelect(card.find('.level-department'),res.data,'Select Department');
    });
});

$(document).on('change','.level-department',function(){

    let card=$(this).closest('.level-card');
    let dept=$(this).val();

    $.get("{{ route('get_staff_by_depart') }}",{department_id:dept},res=>{
        fillSelect(card.find('.level-staff'),res.data,'Select Approver');
    });
});


    function loadApprovers(level,selected){
        let select=$(`[data-level="${level}"] .approver-select`);

        select.empty();
        roleStaff.forEach(emp=>{
            select.append(new Option(emp.name,emp.id,false,selected.includes(String(emp.id))));
        });

        select.select2({
            placeholder:'Select Approvers',
            width:'100%'
        });
    }

    $(document).on('click','.remove-level',function(){
        $(this).closest('.level-card').remove();
        reorderLevels();
        updateFlowPreview();
    });


    new Sortable(document.getElementById('approvalLevels'),{
        animation:150,
        onEnd: reorderLevels
    });

    function reorderLevels(){

        $('#approvalLevels .level-card').each(function(index){

            let level=index+1;

            $(this).attr('data-level',level);

            $(this).find('.level-number').text(level);
        });

    }


    $('#saveMatrixBtn').click(function(){

            let levels=[];

            $('.level-card').each(function(index){

                let staff=$(this).find('.level-staff').val();

                if(!staff){
                    alert(`Select approver in Level ${index+1}`);
                    return false;
                }

                levels.push({
                    level:index+1,
                    company:$(this).find('.level-company').val(),
                    entity:$(this).find('.level-entity').val(),
                    department:$(this).find('.level-department').val(),
                    staff_id:staff
                });
            });

            $.post("{{ route('approval_matrix_save') }}",{
                company_id:$('#company_id_approv').val(),
                entity_id:$('#entity_id_aprov').val(),
                department_id:$('#department_id_aprov').val(),
                role_id:$('#role_id_aprov').val(),
                levels:levels
            },res=>{
                toastr.success('Workflow Saved');
                $('#approvalMatrixModal').modal('hide');
            });
    });



    function editMatrix(id){
        editId=id;

        $.get('/api/approval-matrix/get/'+id,res=>{
            $('#company_id').val(res.company_id).trigger('change');

            setTimeout(()=>{
                $('#department_id').val(res.department_id).trigger('change');
            },400);

            setTimeout(()=>{
                $('#role_id').val(res.role_id).trigger('change');
            },800);

            setTimeout(()=>{
                $('#approvalLevels').html('');
                levelCount=0;
                res.workflow.levels.forEach(l=>addLevel(l.approvers));
            },1200);

            $('#approvalMatrixModal').modal('show');
        });
    }

    $('#approvalMatrixModal').on('hidden.bs.modal',function(){

        levelCount=0;
        editId=null;
        roleStaff=[];
        $('#approvalLevels').empty();
        $('#roleEmployees').empty();

        $(this).find('select').val('').trigger('change');
    });
  
    let sortableInstance=null;

$('#approvalMatrixModal').on('shown.bs.modal',function(){

    if(sortableInstance) sortableInstance.destroy();

    sortableInstance=new Sortable(document.getElementById('approvalLevels'),{
        animation:200,
        handle:'.handle',
        ghostClass:'bg-light',
        onEnd:function(){
            reorderLevels();
            updateFlowPreview();
        }
    });

});
function fillSelect($select, list, placeholder = 'Select') {

    // 1️⃣ Destroy select2 safely (if already initialized)
    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2('destroy');
    }

    // 2️⃣ Clear existing options
    $select.html('');
    $select.append(`<option value="">${placeholder}</option>`);

    // 3️⃣ Append new options safely
    list.forEach(item => {

        let text =
            item.department_name ??
            item.job_position_name ??
            item.role_name ??
            item.staff_name ??
            item.name ??
            'Unknown';

        let value =
            item.sno ??
            item.staff_id ??
            item.id ??
            '';

        $select.append(new Option(text, value));
    });

    // 4️⃣ Reinitialize select2 (IMPORTANT for modal)
    $select.select2({
        placeholder: placeholder,
        width: '100%',
        dropdownParent: $('#approvalMatrixModal'),
        allowClear: true
    });

    // 5️⃣ Reset selection
    $select.val('').trigger('change');
}

</script>
<script>
    function date_fill_issue_rpt() {
        var dt_fill_issue_rpt = document.getElementById('dt_fill_issue_rpt').value;
        var today_dt_iss_rpt = document.getElementById('today_dt_iss_rpt');
        var week_from_dt_iss_rpt = document.getElementById('week_from_dt_iss_rpt');
        var week_to_dt_iss_rpt = document.getElementById('week_to_dt_iss_rpt');
        var monthly_dt_iss_rpt = document.getElementById('monthly_dt_iss_rpt');
        var from_dt_iss_rpt = document.getElementById('from_dt_iss_rpt');
        var to_dt_iss_rpt = document.getElementById('to_dt_iss_rpt');
        var from_date_fillter_iss_rpt = document.getElementById('from_date_fillter_iss_rpt');
        var to_date_fillter_iss_rpt = document.getElementById('to_date_fillter_iss_rpt');

        if (dt_fill_issue_rpt == "today") {
            today_dt_iss_rpt.style.display = "block";
            monthly_dt_iss_rpt.style.display = "none";
            from_dt_iss_rpt.style.display = "none";
            to_dt_iss_rpt.style.display = "none";
            week_from_dt_iss_rpt.style.display = "none";
            week_to_dt_iss_rpt.style.display = "none";
        } else if (dt_fill_issue_rpt == "week") {
            today_dt_iss_rpt.style.display = "none";
            week_from_dt_iss_rpt.style.display = "block";
            week_to_dt_iss_rpt.style.display = "block";
            monthly_dt_iss_rpt.style.display = "none";
            from_dt_iss_rpt.style.display = "none";
            to_dt_iss_rpt.style.display = "none";

            var curr = new Date; // get current date
            var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
            var last = first + 6; // last day is the first day + 6

            var firstday = new Date(curr.setDate(first)).toISOString().slice(0, 10);
            firstday = firstday.split("-").reverse().join("-");
            var lastday = new Date(curr.setDate(last)).toISOString().slice(0, 10);
            lastday = lastday.split("-").reverse().join("-");
            $('#week_from_date_fil').val(firstday);
            $('#week_to_date_fil').val(lastday);

        } else if (dt_fill_issue_rpt == "monthly") {
            today_dt_iss_rpt.style.display = "none";
            monthly_dt_iss_rpt.style.display = "block";
            from_dt_iss_rpt.style.display = "none";
            to_dt_iss_rpt.style.display = "none";
            week_from_dt_iss_rpt.style.display = "none";
            week_to_dt_iss_rpt.style.display = "none";
        } else if (dt_fill_issue_rpt == "custom_date") {
            today_dt_iss_rpt.style.display = "none";
            monthly_dt_iss_rpt.style.display = "none";
            from_dt_iss_rpt.style.display = "block";
            to_dt_iss_rpt.style.display = "block";
            week_from_dt_iss_rpt.style.display = "none";
            week_to_dt_iss_rpt.style.display = "none";
        } else {
            today_dt_iss_rpt.style.display = "none";
            monthly_dt_iss_rpt.style.display = "none";
            from_dt_iss_rpt.style.display = "none";
            to_dt_iss_rpt.style.display = "none";
            week_from_dt_iss_rpt.style.display = "none";
            week_to_dt_iss_rpt.style.display = "none";
        }
    }
</script>
 <script>
   
    function companyChange(){
        var company = $('#staff_company_name').val();
        if(company =='egc'){
            $('.business_div').hide();
            var departmentDropdown = $('#department_id');
            departmentDropdown.empty().append('<option value="">Select Department</option>');
            var entityId = 0;
                $.ajax({
                    url: "{{ route('department') }}",
                    type: "GET",
                    data: {
                        entity_id: entityId
                    },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(state) {
                                departmentDropdown.append($('<option></option>').attr(
                                    'value', state.sno)
                                    .text(state.department_name));
                            });
                            
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Department:', error);
                    }
                });
        }else{
            $('.business_div').show();
        }
    }
    $(document).ready(function() {

        // When company changes
        $('#staff_company_name').on('change', function() {
            var companyId = $(this).val();
            var entityDropdown = $('#staff_entity_name');
            companyChange()
            // Reset dropdown
            entityDropdown.empty().append('<option value="">Select Entity</option>');

            if (companyId) {
                $.ajax({
                    url: "{{ route('entity_list') }}",
                    type: "GET",
                    data: { company_id: companyId },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(entity) {
                                entityDropdown.append(
                                    $('<option></option>')
                                        .attr('value', entity.sno)
                                        .attr('data-baseurl', entity.entity_base_url)
                                        .text(entity.entity_name)
                                );
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching entities:', error);
                    }
                });
            }
        });

        // When entity changes staff_branch_name
        $('#staff_entity_name').on('change', function() {
            var entityId = $(this).val();
            var branchDropdown = $('#staff_branch_name');
            branchDropdown.empty().append('<option value="">Select Branch</option>');
            if (entityId) {
                $.ajax({
                    url: "{{ route('entity_branch_dropdown_list') }}",
                    type: "GET",
                    data: { entity_id: entityId},
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(entity) {
                                branchDropdown.append(
                                    $('<option></option>')
                                        .attr('value', entity.sno)
                                        .text(entity.branch_name)
                                );
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching entities:', error);
                    }
                });
            } 
            var departmentDropdown = $('#department_id');
            departmentDropdown.empty().append('<option value="">Select Department</option>');

            if (entityId) {
                // Fetch and populate states based on selected country
                $.ajax({
                    url: "{{ route('department') }}",
                    type: "GET",
                    data: {
                        entity_id: entityId
                    },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(state) {
                                departmentDropdown.append($('<option></option>').attr(
                                    'value', state.sno)
                                    .text(state.department_name));
                            });
                            
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Department:', error);
                    }
                });
            }
            
        });

        $('#department_id').on('change', function() {
            var department_id = $(this).val();
            var staffDropdown = $('#staff_id');
            staffDropdown.empty().append('<option value="">Select Staff</option>');
            if (department_id) {
                // Fetch and populate states based on selected country
                $.ajax({
                    url: "{{ route('get_staff_by_branch') }}",
                    type: "GET",
                    data: {
                        department_id: department_id
                    },
                    success: function(response) {
                        if (response.status === 200 && response.data) {
                            response.data.forEach(function(state) {
                                staffDropdown.append($('<option></option>').attr('value', state.sno).text(state.staff_name + '- ( '+state.job_role_name +' )' ));
                            });
                            
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Staff:', error);
                    }
                });
                    
            }
        });


    });
</script>

<script>
   let selectedDates = [];
let totalLeaveDays = 0;
let totalPermissionHours = 0;

// When staff changes
$('#staff_id').on('change', function(){

    let staffId = $(this).val();
    if(!staffId){
        $('#approvalPreview').html('<div class="text-muted">No employee selected</div>');
        return;
    }

    $('#approvalPreview').html('Loading approval chain...');

    $.get("{{ route('get_leave_approval_chain') }}",{
        staff_id: staffId
    },function(res){

        if(!res.status || !res.data.length){
            $('#approvalPreview').html('<div class="text-danger">No approval workflow configured</div>');
            return;
        }

        renderApprovalPreview(res.data);

    }).fail(()=>{
        $('#approvalPreview').html('<div class="text-danger">Failed to load approval</div>');
    });

});


function renderApprovalPreview(levels){

    let html='';

    html+=`<div class="flow-node employee">Employee</div>`;

    levels.forEach(level=>{

        html+=`<div class="flow-arrow"><i class="mdi mdi-arrow-right"></i></div>`;

        let roleClass='manager';

        if(level.role_type==='HR') roleClass='hr';
        if(level.role_type==='Director') roleClass='director';

        html+=`
        <div class="flow-node ${roleClass}">
            ${level.staff_name}
            <div class="small text-muted">${level.role_name}</div>
        </div>`;
    });

    $('#approvalPreview').html(html);
}


function addLeaveOrPermissionDate() {
    const requestType = $('#request_type_id').val();
    const leaveDateList = $('#leave_date_list');
    let newDateInputId = 'leave_date_' + new Date().getTime();

    let dateInputHtml = `
        <div class="row leave-date-input mb-2" id="${newDateInputId}" style="border: 1px solid #ccc; border-radius: 8px; padding: 15px;  background-color: #f9f9f9; ">
            <div class="col-lg-3 mb-3">
                <label class="fs-6 fw-semibold">Date</label>
                <input type="text" class="form-control common_datepicker_leave datepicker" id="leave_date_input_${newDateInputId}" readonly placeholder="Select Date" required onchange="handleDateChange('${newDateInputId}')" />
                <div class="invalid-feedback">Please select a valid date.</div>
            </div>
    `;

    if (requestType === "Leave") {
        dateInputHtml += `
            <div class="col-lg-3 mb-3">
                <label class="fs-6 fw-semibold">Leave Type</label>
                <select name="leave_type[${newDateInputId}]" id="leave_type_${newDateInputId}" class="form-select select3 leave_type" required>
                    <option value="">Select Leave Type</option>
                    <option value="Full">Full Day</option>
                    <option value="Morning">Morning Half</option>
                    <option value="Afternoon">Afternoon Half</option>
                </select>
                <div class="invalid-feedback">Please select a leave type.</div>
            </div>
            <div class="col-lg-5 mb-3">
                <label class="fs-6 fw-semibold">Leave Reason</label>
                <textarea name="leave_reason[${newDateInputId}]" class="form-control" placeholder="Reason for leave" required></textarea>
                <div class="invalid-feedback">Please provide a reason for leave.</div>
            </div>
            <div class="leave-summary mt-3" id="leaveSummary">
                <h5 class="mb-2">Request Summary</h5>
                <div id="summaryContent">No dates selected</div>
            </div>

        `;
    } else if (requestType === "Permission") {
        dateInputHtml += `
            <div class="col-lg-2 mb-3">
                <label class="fs-6 fw-semibold">Permission From</label>
                <input type="text" name="permission_from_time[${newDateInputId}]" class="form-control timepicker12" placeholder="From Time" required />
                <div class="invalid-feedback">Please select a valid from time.</div>
            </div>
            <div class="col-lg-2 mb-3">
                <label class="fs-6 fw-semibold">Permission To</label>
                <input type="text" name="permission_to_time[${newDateInputId}]" id="permission_to_time_${newDateInputId}" class="form-control timepicker12" placeholder="To Time" required />
                <div class="invalid-feedback">Please select a valid to time.</div>
            </div>
            <div class="col-lg-4 mb-3">
                <label class="fs-6 fw-semibold">Permission Reason</label>
                <textarea name="permission_reason[${newDateInputId}]" class="form-control" placeholder="Reason for permission" required></textarea>
                <div class="invalid-feedback">Please provide a reason for permission.</div>
            </div>
        `;
    }

    dateInputHtml += `
        <div class="col-lg-1 mb-3 d-flex align-items-center">
            <button type="button" class="btn btn-danger remove-row-btn" onclick="removeRow('${newDateInputId}')"><i class="mdi mdi-delete"></i></button>
        </div>
    </div>
    `;

    leaveDateList.append(dateInputHtml);

    $(".common_datepicker_leave").datepicker({
        format: "dd-M-yyyy",
        autoclose: true,
        beforeShowDay: function(date) {
            let options = { day: '2-digit', month: 'short', year: 'numeric' };
            let formattedDate = date.toLocaleDateString('en-GB', options).replace(/ /g, '-');
            return selectedDates.indexOf(formattedDate) === -1;
        },
        todayHighlight: true
    });

    $(".common_datepicker_leave").on('change', function() {
        var dateText = $(this).val();
        handleDateChange(dateText);
    });
    $(`#leave_type_${newDateInputId}`).on('change', function() {
        updateLeaveDaysOrPermissionHours();
    });
    $(`#permission_to_time_${newDateInputId}`).on('change', function() {
        updateLeaveDaysOrPermissionHours();
    });

    $(`#leave_type_${newDateInputId}`).select2({
        width: '100%',
        dropdownParent: $(`#leave_type_${newDateInputId}`).closest('.col-lg-3'),
    });

    $(".timepicker12").flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K", 
        time_24hr: false, 
        minuteIncrement: 30, 
        defaultHour: 8, 
        defaultMinute: 0, 
    });

    // Show Remove Button Only After First Row
    if ($('.leave-date-input').length > 1) {
        $('.remove-row-btn').show();
    } else {
        $('.remove-row-btn').hide();
    }

    // Apply scroll to leave_date_list
    $('#leave_date_list').css({
        "max-height": "400px",
        "overflow-y": "auto",
        "padding-right": "10px",
        "border": "1px solid #ddd",
        "border-radius": "8px",
        "background-color": "#f9f9f9"
    });
}

function handleDateChange(dateText) {
    if (selectedDates.indexOf(dateText) !== -1) {
        let index = selectedDates.indexOf(dateText);
        selectedDates.splice(index, 1);
    }
    selectedDates.push(dateText);
    
}

function removeRow(rowId) {
    // Removes the entire row, not just the date field
    $(`#${rowId}`).remove();
    updateLeaveDaysOrPermissionHours();
    // Show Remove Button Only After First Row
    if ($('.leave-date-input').length <= 1) {
        $('.remove-row-btn').hide();
    }
}

// Function to update leave days or permission hours dynamically
function updateLeaveDaysOrPermissionHours() {
    const requestType = $('#request_type_id').val();
    totalLeaveDays = 0;
    totalPermissionHours = 0;
    console.log(requestType)
    $('.leave-date-input').each(function() {
        let dateInputId = $(this).attr('id'); // Get unique ID for each input
        if (requestType === "Leave") {
            let leaveType = $(`#leave_type_${dateInputId}`).val();
            console.log(leaveType)
            if (leaveType === "Full") {
                totalLeaveDays += 1; 
            } else if (leaveType === "Morning" || leaveType === "Afternoon") {
                totalLeaveDays += 0.5; 
            }
        } else if (requestType === "Permission") {
            let fromTime = $(`input[name="permission_from_time[${dateInputId}]"]`).val();
            let toTime = $(`input[name="permission_to_time[${dateInputId}]"]`).val();
            
            if (fromTime && toTime) {
                let fromTimeObj = new Date("1970-01-01 " + fromTime);
                let toTimeObj = new Date("1970-01-01 " + toTime);
                let diffMinutes = (toTimeObj - fromTimeObj) / 60000; 
                totalPermissionHours += diffMinutes / 60;
            }
        }
    });
    
    // Show Leave Days or Permission Hours
    if (requestType === 'Leave') {
        $('#leave_days_div').show();
        $('#leave_days_label').show();
        $('#permission_hours_label').hide();
        $('#total_permission_hours').hide();
        $('#permission_hrs_div').hide();
        $('#total_leave_days').text(totalLeaveDays.toFixed(1)).show();
    } else if (requestType === 'Permission') {
        $('#leave_days_div').hide();
        $('#leave_days_label').hide();
        $('#permission_hrs_div').show();
        $('#permission_hours_label').show();
        $('#total_leave_days').hide();
        $('#total_permission_hours').text(totalPermissionHours.toFixed(2)).show();
    }

    // Enable/Disable Add More Button
    if ($('.leave-date-input').length > 0) {
        $('#add_more_btn').prop('disabled', false);
    } else {
        $('#add_more_btn').prop('disabled', true);
    }
}

function toggleLeaveOrPermission() {
    const requestType = $('#request_type_id').val();
    // Disable Add More Button if no request type selected
    $('#add_more_btn').prop('disabled', requestType === "");

    // Reset the form section and selected dates
    selectedDates = [];
    $('#leave_date_list').empty();
    totalLeaveDays = 0;
    totalPermissionHours = 0;
    $('#total_leave_days').hide();
    $('#total_permission_hours').hide();

    if (requestType === 'Leave') {
        $('#leave_dates_section').show();
        $('#total_leave_days').show();
        $('#leave_days_label').show();
        $('#total_permission_hours').hide();
        $('#permission_hours_label').hide();
        addLeaveOrPermissionDate()
    } else if (requestType === 'Permission') {
        $('#leave_dates_section').show();
        $('#total_permission_hours').show();
        $('#permission_hours_label').show();
        $('#total_leave_days').hide();
        $('#leave_days_label').hide();
        addLeaveOrPermissionDate()
    } else {
        $('#leave_dates_section').hide();
        $('#total_leave_days').hide();
        $('#total_permission_hours').hide();
        $('#leave_days_div').hide();
    }
}

</script>




 <!-- status Change -->
 <script>
    function updatelevelStatus(Id, isChecked) {
        const status = isChecked ? 0 : 1;
        fetch(`/staff_status/${Id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 200) {
                    toastr.success('Status Updated successfully!');
                      loadThemes(currentPage);
                }
            })
            .catch(error => {});
    }

    
</script>

<!-- Delete Function -->
<script>
      function confirmDelete(id,staff) {
  
          document.querySelector('#kt_modal_delete_staff .btn-danger').setAttribute('data-id', id);
          $('#delete_message').html(
              'Are you sure you want to delete Staff ?<br><br> <b class="text-black fw-bold fs-4">' +
              staff +
              '</b>');
      }

      function deleteFunc() {
          var categoryId = document.querySelector('#kt_modal_delete_staff .btn-danger').getAttribute('data-id');

          fetch('/staff_delete/' + categoryId, {
                  method: 'DELETE',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  }
              })
              .then(response => response.json())
              .then(data => {
                  if (data.status === 200) {
                      toastr.success(data.message);
                      location.reload();
                  } else {
                      toastr.error(data.error_msg);
                  }
              })
              .catch(error => {
                toastr.error(error);
              });
      }
</script>
<script>
    function formatDateCommon(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');  // Ensures day is 2 digits (e.g., '02')
        const month = date.toLocaleString('default', { month: 'short' });  // Abbreviated month (e.g., 'Nov')
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }

    function getExperienceDuration(joinDate) {
        if (!joinDate) return '-';

        const start = new Date(joinDate);
        const now = new Date();

        if (isNaN(start)) return '-'; // invalid date safeguard

        // Determine direction
        const isFuture = start > now;
        const earlier = isFuture ? now : start;
        const later = isFuture ? start : now;

        // Calculate raw differences
        let years = later.getFullYear() - earlier.getFullYear();
        let months = later.getMonth() - earlier.getMonth();
        let days = later.getDate() - earlier.getDate();

        // Adjust days
        if (days < 0) {
            const prevMonth = new Date(later.getFullYear(), later.getMonth(), 0);
            days += prevMonth.getDate();
            months--;
        }

        // Adjust months
        if (months < 0) {
            months += 12;
            years--;
        }

        // Build result text
        let result = '';
        if (years > 0) result += `${years} yr${years > 1 ? 's' : ''} `;
        if (months > 0) result += `${months} month${months > 1 ? 's' : ''} `;
        if (days > 0) result += `${days} day${days > 1 ? 's' : ''}`;

        if (!result.trim()) result = '0 days';

            result = result.trim();

            // Add "In" or "Ago"
            if (isFuture) {
                result = `In ${result}`;
            } else if (result !== '0 days') {
                // result += ' ago';
                result += '';
            }

            return result;
    }

    function getNextBDayDate(dob) {
        if (!dob) return { short: '-', full: '-' };

        const birthDate = new Date(dob);
        if (isNaN(birthDate)) return { short: '-', full: '-' };

        const today = new Date();

        let nextBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
        if (nextBirthday < today) nextBirthday.setFullYear(today.getFullYear() + 1);

        const diffMs = nextBirthday - today;
        const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        if (diffDays === 0) {
            return { short: '🎉 Today', full: '🎉 Happy Birthday!' };
        }

        let years = nextBirthday.getFullYear() - today.getFullYear();
        let months = nextBirthday.getMonth() - today.getMonth();
        let days = nextBirthday.getDate() - today.getDate();

        if (days < 0) {
            const prevMonth = new Date(nextBirthday.getFullYear(), nextBirthday.getMonth(), 0);
            days += prevMonth.getDate();
            months--;
        }

        if (months < 0) {
            months += 12;
            years--;
        }

        // Build readable text
        let full = '';
        if (years > 0) full += `${years} year${years > 1 ? 's' : ''} `;
        if (months > 0) full += `${months} month${months > 1 ? 's' : ''} `;
        if (days > 0) full += `${days} day${days > 1 ? 's' : ''}`;
        if (!full.trim()) full = `${diffDays} days`;
        full = `${full.trim()} to go 🎂`;

        // Short text (for compact badge)
        let short = '';
        if (months > 0) short += `${months}M `;
        if (days > 0) short += `${days}D`;
        if (!short.trim()) short = `${diffDays}D`;

        return { short: short.trim(), full };
    }

</script>
<script>
    $('#filter').click(function() {
        $('.filter_tbox').slideToggle('slow');
    });
</script>
<script>
        $(document).ready(function() {
            // Business dropdown
            $('#company_fill').on('change', function() {
                var countryId = $(this).val();
                var stateDropdown = $('#entity_fill');

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
            $('#entity_fill').on('change', function() {
                var entity_id = $(this).val();
                var stateDropdown = $('#department_fill');

                stateDropdown.empty().append('<option value="">Select Department</option>');

                if (entity_id) {
                    // Fetch and populate states based on selected country
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
                                
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Department:', error);
                        }
                    });
                }

                
            });

            // division dropdown
             $('#department_fill').on('change', function() {
                var department_id = $(this).val();
                var stateDropdown = $('#division_fill');
                stateDropdown.empty().append('<option value="">Select Division</option>');

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
                                
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Division:', error);
                        }
                    });
                       
                }
             });

              $('#division_fill').on('change', function() {
                var department_id = $(this).val();
           
                var jobRoleDropdown = $('#job_role_fill');

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
                                
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Job Role:', error);
                        }
                    });
                }
             });

        })
</script>



<script>
    function getOrdinalSuffix(number) {
        const suffixes = ["th", "st", "nd", "rd"];
        const remainder = number % 100;
        return suffixes[(remainder - 20) % 10] || suffixes[remainder] || suffixes[0];
    }

    function getInitials(name) {
        if (!name) return '';
        const words = name.trim().split(' ');
        
        return (words[0][0]).toUpperCase();
    }

    function getInitialAvatar(name){
        if(!name) name='?';

        let initials = name.trim().charAt(0).toUpperCase();
        let colors = ['#6366f1','#22c55e','#06b6d4','#f59e0b','#ef4444','#8b5cf6'];
        let color = colors[name.length % colors.length];

        return `
        <div class="emp-avatar-img d-flex align-items-center justify-content-center"
            style="background:${color}">
            ${initials}
        </div>`;
    }

    function getAvatarColor(name) {
        const colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return colors[Math.abs(hash) % colors.length];
    }
    function escapeHtml(text){
        if(!text) return '';
        return text.replace(/'/g,"&#39;").replace(/"/g,"&quot;");
    }
    function replaceWithInitials(img, name) {
        console.log(img,name)
        const initials = getInitials(name);
        const color = getAvatarColor(name);
        console.log(name ,initials)
        const div = document.createElement('div');
        div.className = `rounded-circle ${color} text-white d-flex align-items-center justify-content-center me-3 fw-bold`;
        div.style.width = '50px';
        div.style.height = '50px';
        div.innerText = initials;

        img.replaceWith(div);
    }
</script>

<script>
    const planner = {
    type: null,
    rows: [],
    totalDays: 0,
    totalHours: 0
};

$(document).on('click','.type-card',function(){
    $('.type-card').removeClass('active');
    $(this).addClass('active');

    planner.type = $(this).data('type');
    $('#request_type').val(planner.type);

    $('#plannerRows').empty();
    planner.rows = [];
    refreshSummary();
});


$('#addRow').on('click',()=>{

    if(!planner.type){
        toastr.warning('Select request type first');
        return;
    }

    const id = Date.now();

    let html = `
    <div class="planner-row border rounded p-3 mb-2" data-id="${id}">
        <div class="row g-2 align-items-end">

            <div class="col-md-3">
                <label>Date</label>
                <input type="text" class="form-control row-date" required>
            </div>
    `;

    if(planner.type === 'Leave'){
        html += `
            <div class="col-md-3">
                <label>Leave Type</label>
                <select class="form-select row-leave">
                    <option value="Full">Full Day</option>
                    <option value="Half">Half Day</option>
                </select>
            </div>

            <div class="col-md-5">
                <label>Reason</label>
                <input type="text" class="form-control row-reason">
            </div>
        `;
    }else{
        html += `
            <div class="col-md-2">
                <label>From</label>
                <input type="text" class="form-control time-from">
            </div>

            <div class="col-md-2">
                <label>To</label>
                <input type="text" class="form-control time-to">
            </div>

            <div class="col-md-4">
                <label>Reason</label>
                <input type="text" class="form-control row-reason">
            </div>
        `;
    }

    html += `
        <div class="col-md-1 text-end">
            <button class="btn btn-danger removeRow">✕</button>
        </div>
    </div></div>`;

    $('#plannerRows').append(html);

    $('.row-date').flatpickr({dateFormat:'d-M-Y'});
    $('.time-from,.time-to').flatpickr({enableTime:true,noCalendar:true,dateFormat:"h:i K"});

});


$(document).on('change','.row-leave,.time-from,.time-to',refreshSummary);
$(document).on('click','.removeRow',function(){
    $(this).closest('.planner-row').remove();
    refreshSummary();
});


function refreshSummary(){

    planner.totalDays = 0;
    planner.totalHours = 0;

    $('.planner-row').each(function(){

        if(planner.type === 'Leave'){
            let type=$(this).find('.row-leave').val();
            planner.totalDays += (type==='Full'?1:0.5);
        }
        else{
            let from=$(this).find('.time-from').val();
            let to=$(this).find('.time-to').val();
            if(from && to){
                let diff=(new Date('1970-01-01 '+to)-new Date('1970-01-01 '+from))/3600000;
                planner.totalHours+=diff;
            }
        }
    });

    let html = `<b>Type:</b> ${planner.type || '-'}<br>`;

    if(planner.type==='Leave')
        html+=`<b>Total Days:</b> ${planner.totalDays}`;
    else if(planner.type==='Permission')
        html+=`<b>Total Hours:</b> ${planner.totalHours.toFixed(2)}`;

    $('#liveSummary').html(html);
}

</script>
@endsection