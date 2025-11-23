@extends('layouts/layoutMaster')

@section('title', 'Manage Attendance')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
     'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
     'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    //  'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
     'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js',
    //  'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
     'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
     'resources/assets/vendor/libs/flatpickr/flatpickr.js'],)
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-pickers.js']),
     @vite(['resources/assets/js/forms_date_time_pickers.js'])
@endsection

@section('content')

    <style>
        .sticky_table thead th:not(:first-child):not(:last-child) {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            line-height: 1.2;
        }

        .sticky_table thead th:not(:first-child):not(:last-child) a {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .sticky_table thead th:not(:first-child):not(:last-child) a div:first-child {
            font-weight: 600;
            font-size: 14px;

        }

        .sticky_table thead th:not(:first-child):not(:last-child) a div:last-child {
            font-size: 12px;
            color: #ccc;

        }

        .sticky_table tbody td {
            vertical-align: middle;
        }

        .sticky_table tbody td:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #fff;
        }

        .sticky_table thead th:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #ab2b22;
        }

        .sticky_table tbody td:last-child {
            position: sticky;
            right: 0;
            z-index: 2;
            background: #fff;


        }

        .sticky_table thead th:last-child {
            position: sticky;
            right: 0;
            padding: 2px;
            z-index: 2;
            background: #ab2b22;
        }
    </style>

<style>
.attendance-cards .card-attendance {
  width: 90px; /* fixed width for all cards */
  min-width: 90px;
  max-width: 90px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 0.5rem; /* smaller padding */
  gap: 0.25rem;
}

.attendance-cards .percent-value {
  font-size: 1.2rem; /* reduced font */
  line-height: 1.2;
  word-wrap: break-word;
}
</style>

<style>
.team-card {
  width: 180px;
  background-color: #fff;
  border-radius: 0.4rem;
  border: 1px solid #e0e0e0;
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}


.role-indicator {
  width: 40px;
  height: 40px;
}

.role-abbr {
  font-size: 0.8rem;
}

.team-info .fs-6 {
  margin-bottom: 0.1rem;
  font-size: 0.95rem !important;
}

.team-info .fs-8 {
  font-size: 0.75rem;
  color: #6c757d;
}
</style>



    <!-- Lead List Table -->
    <div class="card card-action">
        <div class="card-header border-bottom pb-0 flex-wrap">
            <div class="card-action-title text-black fs-5 fw-bold mb-1">
                <h5 class="card-title mb-1">Manage Attendance</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboards') }}" class="d-flex align-items-center"><i
                                    class="mdi mdi-home text-body fs-4"></i></a>
                        </li>
                        <span class="text-dark opacity-75 me-1 ms-1">
                            <i class="mdi mdi-chevron-double-right fs-4"></i>
                        </span>
                        <li class="breadcrumb-item">
                            <a href="javascript:;" class="d-flex align-items-center fw-semibold">HR Management</a>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="card-action-element mb-1 gap-2 text-center">
                <div class="d-flex justify-content-end align-items-center flex-wrap gap-2 mb-1">
                    <div class="d-flex flex-row align-items-center justify-content-between gap-2">
                        <ul class="nav nav-pills justify-content-end" role="tablist">
                            <li class="nav-item me-1">
                                <a class="nav-link px-3" href="#" id="prevMonth" >
                                <div class="text-center">
                                    <span class="mdi mdi-arrow-left-circle-outline fs-2"></span>
                                </div>
                                </a>
                            </li>
                            <li class="nav-item me-1 mt-1">
                                <a class="nav-link active px-3" href="#">
                                <div class="text-center">
                                    <span class="fs-6 fw-semibold text-white text-capitalize" id="currentMonth"></span>
                                </div>
                                </a>
                            </li>
                            <li class="nav-item me-1">
                                <a class="nav-link px-3" href="#" id="nextMonth" >
                                <div class="text-center">
                                    <span class="mdi mdi-arrow-right-circle-outline fs-2"></span>
                                </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="javascript:;" class="btn btn-sm fw-bold btn-primary text-white" data-bs-toggle="modal" data-bs-target="#kt_modal_upload_essl"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Upload">
                        <span class="me-2"><i class="mdi mdi-plus"></i></span>Upload ESSL
                    </a>
                    <a href="javascript:;" class="btn btn-sm fw-bold btn-primary text-white" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="me-2"><i class="mdi mdi-plus"></i></span>Add Attendance
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 200px;">
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_attendance_present">
                            <span class="d-flex gap-2">
                            <label class="badge bg-label-success text-black border border-success fw-bold px-3  py-1"><span>P</span></label>
                            <span>Present</span>
                            </span>
                        </a>
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_attendance_absent">
                            <span class="d-flex gap-2">
                                <label class="badge bg-label-danger text-black border border-danger fw-bold px-3  py-1"><span>A</span></label>
                                <span>Absent</span>
                            </span>
                        </a>
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_attendance_leave">
                            <span class="d-flex gap-2">
                                <label class="badge bg-label-warning text-black border border-warning fw-bold px-3  py-1"><span>L</span></label>
                                <span>Leave</span>
                            </span>
                        </a>
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_attendance_permission">
                            <span class="d-flex gap-2">
                                <label><span class="badge text-black fw-bold px-3  py-1"
                                style="background-color: #DCE2FC; border:1px solid #2856FA;">Pr</span></label>
                                <span>Permission</span>
                            </span>
                        </a>
                        <a href="javascript:;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_attendance_onduty">
                            <span class="d-flex gap-2">
                                <label>
                                    <span class="badge text-black fw-bold px-3  py-1"
                                        style="background-color: #EDD4FF; border:1px solid #9C2DEB">OD</span>
                                </label>
                                <span>On Duty</span>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center text-black gap-2">
                    <div class="d-flex align-items-center justify-content-center me-3">
                        <label
                            class="badge bg-label-success text-black border border-success fw-bold px-3  py-1"><span>P</span></label>
                        <span class="fs-6 fw-semibold ms-1">Present</span>
                    </div>
                    <div class=" d-flex align-items-center justify-content-center me-3">
                        <label class="badge bg-label-danger text-black border border-danger fw-bold px-3  py-1"><span>A</span></label>
                        <span class="fs-6 fw-semibold ms-1">Absent</span>
                        <a href="javascipt:;" data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="Leave without intimation"><i class="mdi mdi mdi-information text-dark"></i></a>
                    </div>
                    <div class="d-flex align-items-center justify-content-center me-3">
                        <label class="badge bg-label-warning text-black border border-warning fw-bold px-3  py-1"><span>L</span></label>
                        <span class="fs-6 fw-semibold ms-1">Leave</span>
                        <a href="javascipt:;" data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="Leave with intimation"><i class="mdi mdi mdi-information text-dark"></i></a>
                    </div>
                    <div class="d-flex align-items-center justify-content-center me-3">
                        <label>
                            <span class="badge text-black fw-bold px-3  py-1"
                                style="background-color: #EDD4FF; border:1px solid #9C2DEB">OD</span>
                        </label>
                        <span class="fs-6 fw-semibold ms-1">On Duty</span>
                        <div class="">
                            <a href="#" class="dropdown-toggle hide-arrow " data-bs-toggle="dropdown"
                                data-trigger="hover">
                                <i class="ms-1 mdi mdi-information fs-9"></i>
                            </a>
                            <div class="dropdown-menu py-2 px-4 text-black scroll-y w-250px max-h-250px">
                                <div class="d-flex align-items-center mb-2 ">
                                    <label><span class="badge text-black fw-bold px-3  py-1"
                                            style="background-color: #EDD4FF; border:1px solid #9C2DEB">30 M</span></label>
                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                    <label class="fs-6 fw-semibold">30 Minutes</label>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <label><span class="badge text-black fw-bold px-3  py-1"
                                            style="background-color: #EDD4FF; border:1px solid #9C2DEB">1 H</span></label>
                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                    <label class="fs-6 fw-semibold">1 Hour</label>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <label><span class="badge text-black fw-bold px-3  py-1"
                                            style="background-color: #EDD4FF; border:1px solid #9C2DEB">1.5 H</span></label>
                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                    <label class="fs-6 fw-semibold">1.5 Hours</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center me-3">
                        <label><span class="badge text-black fw-bold px-3  py-1"
                                style="background-color: #DCE2FC; border:1px solid #2856FA;">Pr</span></label>
                        <span class="fs-6 fw-semibold ms-1">Permission</span>
                        <div class="">
                            <a href="#" class="dropdown-toggle hide-arrow " data-bs-toggle="dropdown"
                                data-trigger="hover">
                                <i class="ms-1 mdi mdi-information fs-9"></i>
                            </a>
                            <div class="dropdown-menu py-2 px-4 text-black scroll-y w-250px max-h-250px">
                                <div class="d-flex align-items-center mb-2 ">
                                    <label><span class="badge text-black fw-bold px-3  py-1"
                                            style="background-color: #DCE2FC; border:1px solid #2856FA;">30
                                            M</span></label>
                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                    <label class="fs-6 fw-semibold">30 Minutes</label>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <label><span class="badge text-black fw-bold px-3  py-1"
                                            style="background-color: #DCE2FC; border:1px solid #2856FA;">1 H</span></label>
                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                    <label class="fs-6 fw-semibold">1 Hour</label>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <label><span class="badge text-black fw-bold px-3  py-1"
                                            style="background-color: #DCE2FC; border:1px solid #2856FA;">0.5
                                            D</span></label>
                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                    <label class="fs-6 fw-semibold">Half Day</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center me-3">
                        <label
                            class="badge bg-primary border border-primary text-white fw-bold px-3  py-1"><span>H</span></label>
                        <span class="fs-6 fw-semibold ms-1">Holiday</span>
                        <a href="javascipt:;" data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="Company Leave"><i class="mdi mdi mdi-information text-dark"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-2">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active attendance-container" id="tab_jan" role="tabpanel">
                    <div class="row pt-4">
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="text-dark mb-1 fs-6 fw-semibold">Company<span
                                            class="text-danger">*</span></label>
                                    <select id="company_fill" name="company_fill" class="select3 form-select" onchange="loadAttendance(1)">
                                        <option value="egc">Elysium Groups of Companies</option>
                                        @if(isset($company_list))
                                        @foreach($company_list as $clist)
                                            <option value="{{$clist->sno}}" {{$company_fill== $clist->sno ? 'selected':''}}>{{$clist->company_name}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="text-dark mb-1 fs-6 fw-semibold">Entity<span
                                            class="text-danger">*</span></label>
                                    <select class="select3 form-select" id="entity_fill" name="entity_fill">/
                                        <option value="">Select Entity Name</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 attendance-cards d-flex align-items-center justify-content-between gap-2">

                          <div class="d-flex flex-column gap-1 mb-0 border border-success p-2 rounded bg-label-success text-center card-attendance">
                            <label class="fw-bold text-black percent-value">90%</label>
                            <label class="text-black fw-semibold fs-8">Present</label>
                          </div>

                          <div class="d-flex flex-column gap-1 mb-0 border border-danger p-2 rounded bg-label-danger text-center card-attendance">
                            <label class="fw-bold text-black percent-value">03%</label>
                            <label class="text-black fw-semibold fs-8">Absent</label>
                          </div>

                          <div class="d-flex flex-column gap-1 mb-0 p-2 rounded card-attendance" style="background: #DCE2FC; border:1px solid #2856FA; text-align:center;">
                            <label class="fw-bold text-black percent-value" style="color:#2856FA;">02%</label>
                            <label class="text-black fw-semibold fs-8">Permission</label>
                          </div>

                          <div class="d-flex flex-column gap-1 mb-0 border border-warning p-2 rounded bg-label-warning text-center card-attendance">
                            <label class="fw-bold text-black percent-value">04%</label>
                            <label class="text-black fw-semibold fs-8">Leave</label>
                          </div>

                          <div class="d-flex flex-column gap-1 mb-0 p-2 rounded card-attendance" style="background: #EDD4FF; border:1px solid #9C2DEB; text-align:center;">
                            <label class="fw-bold text-black percent-value" style="color:#9C2DEB;">01%</label>
                            <label class="text-black fw-semibold fs-8">On Duty</label>
                          </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                         <div class="col-lg-12">
                            <div class="d-flex align-items-center justify-content-between mb-4 ">
                                <div>
                                    <span>Show</span>
                                    <select id="perpage" class="form-select form-select-sm w-75px"
                                        onchange="loadAttendance(1)">
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
                                            value=""/>
                                        
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
                        <div class="col-lg-12 mt-2">
                            <div class="table-responsive">
                                <table class="table sticky_table align-middle table-row-dashed table-striped table-hover gy-0 gs-1">
                                    <thead>
                                        <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary attendance-header">
                                            <th class="min-w-200px text-center">Staffs</th>

                                            <th class="min-w-100px">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-black fw-semibold fs-7 attendance-body">
                                        <tr class="skeleton-loader" id="skeleton-loader" style="border-left: 5px solid #e2e2e2;">
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
        </div>
    </div>

        <div class="modal fade" id="kt_modal_upload_essl" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
          <!--begin::Modal dialog-->
          <div class="modal-dialog modal-md">
              <!--begin::Modal content-->
              <div class="modal-content rounded">
                  <!--begin::Modal header-->
                  <div class="modal-header justify-content-end border-0 pb-0">
                      <!--begin::Close-->
                      <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                          <span class="svg-icon svg-icon-1">
                              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor" />
                                  <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="currentColor" />
                              </svg>
                          </span>
                          <!--end::Svg Icon-->
                      </div>
                      <!--end::Close-->
                  </div>
                  <!--end::Modal header-->
                  <!--begin::Modal body-->
                  <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                      <!--begin::Heading-->
                      <div class="mb-4 text-center">
                          <h3 class="text-center mb-4 text-black">Upload Documents</h3>
                      </div>
                      <div class="text-center">
                          <span>
                              <label class="badge bg-warning text-black rounded fw-bold" id="upload_chap_course_name"></label>
                              <input type="hidden" name="upload_chap_course_id" id="upload_chap_course_id">
                          </span>
                      </div>
                      <form id="uploadMaterialForm" method="POST" enctype="multipart/form-data" autocomplete="off">
                          @csrf 
                          <div class="row mt-2">
                          
                        
                            <!-- File Upload -->
                            <div class="col-lg-12 mb-3">
                              <label class="text-dark mb-1 fs-6 fw-semibold">Documents<span class="text-danger">*</span></label>
                              <div id="dropArea" class="border border-4 border-dashed p-5 text-center rounded">
                                <p class="mb-2">Drag & Drop your files here or click to select</p>
                                <input type="file" id="fileInput" name="file_upload" class="d-none"  />
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                                  Browse Files
                                </button>
                              </div>
                              <div id="fileList" class="text-muted"></div>
                              <div class="text-danger" id="err_mssg"></div>
                            </div>
                          </div>
                        
                          <!-- Footer -->
                          <div class="row">
                            <div class="d-flex justify-content-center align-items-center mt-4">
                              <button type="reset" class="btn btn-secondary me-3" data-bs-dismiss="modal">Cancel</button>
                              <div class="position-relative">
                                <button type="submit" class="btn btn-primary" id="uploadButton">Upload</button>
                                <div id="uploadLoader" class="import-loader d-none">
                                  <div class="spinner-border text-light" role="status"></div>
                                  <span class="ms-2"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </form>
                  </div>
                  <!--end::Modal body-->
              </div>
              <!--end::Modal content-->
          </div>
          <!--end::Modal dialog-->
        </div>

    <!--begin::Modal -  Mark Present Attendance  -->
    <div class="modal fade" id="kt_modal_attendance_present" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                    <div class="text-center mt-4 d-flex gap-3 align-items-center">
                        <h3 class="text-center text-black"> Mark Attendance</h3>
                        <div class="pb-3"><span class="badge bg-label-success fs-6 text-black border border-success fw-bold px-3  py-1">Present</span></div>
                    </div>
                    <!--begin::Close-->
                   <div class="d-flex justify-content-end px-2 py-2">
                        <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                            data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="#000" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="#000" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <form id="attendanceForm">
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <!--begin::Heading-->
                     
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i">
                                  <i class="mdi mdi-calendar-month-outline fs-4"></i>
                                </span>
                                <input type="text" class="form-control" value="<?php echo date("d-M-Y"); ?>" id="attend_date" name="attend_date" readonly />
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Company<span class="text-danger">*</span></label>
                            <select id="company_id_present" name="company_id" class="select3 form-select" >
                                <option value="">Select Company</option>
                                <option value="egc">Elysium Groups of Companies</option>
                                @if(isset($company_list))
                                @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}" >{{$clist->company_name}}</option>
                                @endforeach
                                @endif
                            </select>
                             <div id="company_id_present_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-6 mb-3 business_div" id="business_present">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Entity<span class="text-danger">*</span></label>
                            <select class="select3 form-select" id="entity_id_present" name="entity_id" >
                                <option value="">Select Entity</option>

                            </select>
                            <div id="entity_id_present_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Department<span class="text-danger">*</span></label>
                            <select class="select3 form-select" placeholder="Select Department" id="department_add" name="department_id">
                                <option value="" >Select Department</option>
                            </select>
                            <div id="department_add_err" class="text-danger"></div>
                        </div>
                        <input type="hidden" class="form-control" name="entry_select" value="1"  />
                       <div class="col-lg-12 mb-3">
                          <div class="d-flex justify-content-between">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Staff<span class="text-danger">*</span></label>
                            <label id="staff-count" class="bg-primary text-white mb-1 fs-6 fw-semibold rounded px-3">0</label>
                          </div>
                          <select class="select3 form-select" id="staff_add" name="staff_id[]" multiple> 
                          </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="reset" class="btn btn-outline-danger text-primary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="markPresentBtn" class="btn btn-primary" onclick="mark_present_att_func()">Mark Present Attendance</button>
                    </div>
                </div>
                </form>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal -  Mark Present Attendance -->


    <!--begin::Modal -  Mark Absent Attendance  -->
    <div class="modal fade" id="kt_modal_attendance_absent" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                   <div class="text-center mt-4 d-flex gap-3 align-items-center">
                        <h3 class="text-center text-black"> Mark Attendance</h3>
                        <div class="pb-3"><span class="badge bg-label-danger fs-6 text-black border border-danger fw-bold px-3  py-1">Absent</span></div>
                    </div>
                    <!--begin::Close-->
                    <div class="d-flex justify-content-end px-2 py-2">
                        <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                            data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="#000" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="#000" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <form id="attendanceFormabsent">
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <!--begin::Heading-->
                    
                    <div class="row">
                          <div class="col-lg-6 mb-3">
                              <label class="text-dark mb-1 fs-6 fw-semibold">Date</label>
                              <div class="input-group input-group-merge">
                                  <span class="input-group-text bg-gray-200i">
                                    <i class="mdi mdi-calendar-month-outline fs-4"></i>
                                  </span>
                                  <input type="text" class="form-control" value="<?php echo date('d-M-Y'); ?>" id="attend_date_absent" name="attend_date" readonly />
                              </div>
                          </div>
                          <input type="hidden" class="form-control" name="entry_select" value="2"  />
                          <div class="col-lg-6 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Company<span class="text-danger">*</span></label>
                            <select id="company_id_absent" name="company_id" class="select3 form-select" >
                                <option value="">Select Company</option>
                                <option value="egc">Elysium Groups of Companies</option>
                                @if(isset($company_list))
                                @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}" >{{$clist->company_name}}</option>
                                @endforeach
                                @endif
                            </select>
                             <div id="company_id_absent_err" class="text-danger"></div>
                            </div>
                            <div class="col-lg-6 mb-3 " id="business_absent">
                                <label class="text-dark mb-1 fs-6 fw-semibold">Entity<span class="text-danger">*</span></label>
                                <select class="select3 form-select" id="entity_id_absent" name="entity_id" >
                                    <option value="">Select Entity</option>

                                </select>
                                <div id="entity_id_absent_err" class="text-danger"></div>
                            </div>
                          <div class="col-lg-6 mb-3">
                              <label class="text-dark mb-1 fs-6 fw-semibold">Department<span class="text-danger">*</span></label>
                              <select class="select3 form-select" id="department_add_absent" name="department_id" >
                              </select>
                              <div id="department_add_absent_err" class="text-danger"></div>
                          </div>
                          <div class="col-lg-12 mb-3">
                            <div class="d-flex justify-content-between">
                              <label class="text-dark mb-1 fs-6 fw-semibold">Staff<span class="text-danger">*</span></label>
                              <label id="absent-count" class="bg-primary text-white mb-1 fs-6 fw-semibold rounded px-3">0</label>
                            </div>
                            <select class="select3 form-select" id="staff_add_absent" name="staff_id[]" multiple>
                            </select>
                             <div id="staff_add_absent_err" class="text-danger"></div>
                          </div>
                          <!-- <div class="col-lg-12 scroll-y d-flex flex-wrap gap-4 my-4" style="max-height: 150px">
                             
                              <div class="team-card d-flex align-items-center p-2 rounded shadow-sm">
                              
                                <div class="role-indicator bg-primary rounded-start d-flex align-items-center justify-content-center me-2">
                                  <span class="role-abbr text-white fw-bold">Y</span>
                                </div>
                                <div class="team-info">
                                  <div class="fw-semibold fs-6 text-dark">Yasmin</div>
                                  <div class="text-secondary fs-8">HR Manager</div>
                                </div>
                              </div>

                              <div class="team-card d-flex align-items-center p-2 rounded shadow-sm">
                                <div class="role-indicator bg-primary rounded-start d-flex align-items-center justify-content-center me-2">
                                  <span class="role-abbr text-white fw-bold">V</span>
                                </div>
                                <div class="team-info">
                                  <div class="fw-semibold fs-6 text-dark">Vasan</div>
                                  <div class="text-secondary fs-8">Sales Lead</div>
                                </div>
                              </div>
                          </div> -->

                          <div class="col-lg-12 mb-3">
                              <label class="text-dark mb-1 fs-6 fw-semibold">Reason</label>
                              <textarea class="form-control" rows="3"  name="reason" placeholder="Enter Reason"></textarea>
                          </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="reset" class="btn btn-outline-danger text-primary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="absentBtn" class="btn btn-primary" onclick="mark_absent_att_func()">Mark Absent Attendance</button>
                    </div>
                </div>
                </form>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal -  Mark Absent Attendance -->

     <!--begin::Modal -  Mark Leave Attendance  -->
    <div class="modal fade" id="kt_modal_attendance_leave" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                   <div class="text-center mt-4 d-flex gap-3 align-items-center">
                        <h3 class="text-center text-black"> Mark Attendance</h3>
                        <div class="pb-3"><span class="badge bg-label-warning fs-6 text-black border border-warning fw-bold px-3  py-1">Leave</span></div>
                    </div>
                    <!--begin::Close-->
                    <div class="d-flex justify-content-end px-2 py-2">
                        <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                            data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="#000" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="#000" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <!--begin::Heading-->
                    <form id="attendanceFormleave">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Attendance Type<span class="text-danger">*</span></label>
                            <select id="attendance_type_add" name="attendance_type_add" class="select3 form-select"  onchange="attendance_type_func_add()">
                                <option value="today">Today</option>
                                <option value="tomorrow">Tomorrow</option>
                                <option value="custom">Custom</option>
                                
                            </select>
                            <div id="attendance_type_add_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3" id="attd_type_today_add" style="display: block;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Today Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i">
                                  <i class="mdi mdi-calendar-month-outline fs-4"></i>
                                </span>
                                <input type="text" class="form-control" value="<?php echo date('d-M-Y'); ?>" disabled />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="attd_type_tomor_add" style="display: none;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Tomorrow Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i">
                                  <i class="mdi mdi-calendar-month-outline fs-4"></i>
                                </span>
                                <?php
                                $currentDate = new DateTime();
                                $currentDate->modify('+1 days');
                                $futureDate = $currentDate->format('d-M-Y');
                                ?>
                                <input type="text" class="form-control" value="<?php echo $futureDate; ?>" disabled />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="attd_type_custom_add" style="display: none;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Custom Date<span  class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                <input type="text" id="custom_date_add" name="attendance_date_range_add" placeholder="Select Date" class="form-control common_datepicker" readonly value="<?php echo date("d-M-Y"); ?>" />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Company<span  class="text-danger">*</span></label>
                            <select id="company_id_leave" name="company_id" class="select3 form-select" >
                                <option value="">Select Company</option>
                                <option value="egc">Elysium Groups of Companies</option>
                                @if(isset($company_list))
                                @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}" >{{$clist->company_name}}</option>
                                @endforeach
                                @endif
                            </select>
                             <div id="company_id_leave_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3" id="business_leave">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Entity<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="entity_id_leave" name="entity_id" >
                                <option value="">Select Entity</option>

                            </select>
                            <div id="entity_id_leave_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Department<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="department_add_leave" name="department_id" placeholder="Select Department Name">
                            </select>
                            <div id="department_add_leave_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Staff<span class="text-danger">*</span></label>
                            <select class="select3 form-select" id="staff_add_leave" name="staff_id[]" multiple placeholder="Select Staff">
                            </select>
                        </div>
                        
                        <input type="hidden" class="form-control" name="entry_select" value="5"  />
                            <!-- <div class="col-lg-12 scroll-y d-flex flex-wrap gap-4 my-4" style="max-height: 150px">
                            
                              <div class="team-card d-flex align-items-center p-2 rounded shadow-sm">
                         
                                <div class="role-indicator bg-primary rounded-start d-flex align-items-center justify-content-center me-2">
                                  <span class="role-abbr text-white fw-bold">Y</span>
                                </div>
                                <div class="team-info">
                                  <div class="fw-semibold fs-6 text-dark">Yasmin</div>
                                  <div class="text-secondary fs-8">HR Manager</div>
                                </div>
                              </div>

                              <div class="team-card d-flex align-items-center p-2 rounded shadow-sm">
                                <div class="role-indicator bg-primary rounded-start d-flex align-items-center justify-content-center me-2">
                                  <span class="role-abbr text-white fw-bold">V</span>
                                </div>
                                <div class="team-info">
                                  <div class="fw-semibold fs-6 text-dark">Vasan</div>
                                  <div class="text-secondary fs-8">Sales Lead</div>
                                </div>
                              </div>
                            </div> -->

                        <div class="col-lg-12 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Reason<span   class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" placeholder="Enter Reason" name="reason" id="reason_leave"></textarea>
                             <div id="reason_leave_err" class="text-danger"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="reset" class="btn btn-outline-danger text-primary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="leaveBtn" class="btn btn-primary" onclick="mark_leave_att_func()">Mark Leave Attendance</button>
                    </div>
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal -  Mark Leave Attendance -->


    <!--begin::Modal -  Mark Permission Attendance  -->
    <div class="modal fade" id="kt_modal_attendance_permission" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                   <div class="text-center mt-4 d-flex gap-3 align-items-center">
                        <h3 class="text-center text-black"> Mark Attendance</h3>
                        <div class="pb-3"><span class="badge text-black fw-bold px-3 fs-6 py-1" style="background-color: #DCE2FC; border:1px solid #2856FA;">Permission</span></div>
                    </div>

                    <!--begin::Close-->
                    <div class="d-flex justify-content-end px-2 py-2">
                        <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                            data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="#000" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="#000" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <!--begin::Heading-->
                    <form id="attendanceFormpermission">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Attendance Type<span  class="text-danger">*</span></label>
                            <select id="perm_attendance_type_add" name="perm_attendance_type_add"
                                class="select3 form-select" onchange="perm_attendance_type_func_add()">
                                <option value="today">Today</option>
                                <option value="tomorrow">Tomorrow</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-lg-4 mb-3" id="perm_attd_type_today_add" style="display: block;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Today Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                <input type="text" class="form-control" value="<?php echo date('d-M-Y'); ?>" disabled />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="perm_attd_type_tomor_add" style="display: none;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Tomorrow Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i">
                                  <i   class="mdi mdi-calendar-month-outline fs-4"></i>
                                </span>
                                <?php
                                $perm_currentDate = new DateTime();
                                $perm_currentDate->modify('+1 days');
                                $perm_futureDate = $perm_currentDate->format('d-M-Y');
                                ?>
                                <input type="text" class="form-control" value="<?php echo $perm_futureDate; ?>" disabled />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="perm_attd_type_custom_add" style="display: none;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Custom Date<span  class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                  <i class="mdi mdi-calendar-month-outline fs-4"></i>
                                </span>
                                <input type="text" class="form-control common_datepicker" id="custom_date_add_pr" name="attendance_date_range_add" value="<?php echo date('d-M-Y'); ?>" />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="perm_attd_type_custom_st_time_add" >
                            <label class="text-dark mb-1 fs-6 fw-semibold">Start Time<span class="text-danger">*</span></label>
                            <input class="form-control form-control-solid perm_attd_type_custom_st_time_add common_timepicker" id="perm_st_time_add" name="st_time"  placeholder="Pick time" />
                            <div id="perm_st_time_add_err" class="text-danger "></div>
                        </div>
                        <div class="col-lg-4 mb-3" id="perm_attd_type_custom_ed_time_add" >
                            <label class="text-dark fs-6 mb-1 fw-semibold">End Time<span  class="text-danger">*</span></label>
                            <input class="form-control form-control-solid perm_attd_type_custom_ed_time_add common_time_class" id="perm_ed_time_add" name="end_time" placeholder="Pick time" />
                             <div id="perm_ed_time_add_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Company<span class="text-danger">*</span></label>
                            <select id="company_id_pr" name="company_id" class="select3 form-select" >
                                <option value="">Select Company</option>
                                <option value="egc">Elysium Groups of Companies</option>
                                @if(isset($company_list))
                                @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}" >{{$clist->company_name}}</option>
                                @endforeach
                                @endif
                            </select>
                             <div id="company_id_pr_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3" id="business_pr">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Entity<span class="text-danger">*</span></label>
                            <select class="select3 form-select" id="entity_id_pr" name="entity_id" >
                                <option value="">Select Entity</option>

                            </select>
                            <div id="entity_id_pr_err" class="text-danger"></div>
                        </div>
                        <input type="hidden" class="form-control" name="entry_select" value="4"  />
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Department<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="department_add_pr" name="department_id" placeholder="Select Department Name">
                            </select>
                             <div id="department_add_pr_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Staff<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="staff_add_pr" name="staff_id[]" multiple placeholder="Select Staff">
                            </select>
                            <div id="staff_add_pr_err" class="text-danger"></div>
                        </div>

                        
                    </div>
                    <div class="row">
                        
                        <div class="col-lg-12 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Reason<span  class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" id="pr_reason" name="reason" placeholder="Enter Reason"></textarea>
                            <div id="pr_reason_err" class="text-danger"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="reset" class="btn btn-outline-danger text-primary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="prBtn" class="btn btn-primary" onclick="mark_pr_att_func()">Mark Permission Attendance</button>
                    </div>
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal -  Mark Permission Attendance -->

    <!--begin::Modal -  Mark  On Duty Attendance  -->
    <div class="modal fade" id="kt_modal_attendance_onduty" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                    <div class="text-center mt-4 d-flex gap-3 align-items-center">
                        <h3 class="text-center text-black"> Mark Attendance</h3>
                        <div class="pb-3"><span class="badge text-black fw-bold px-3 fs-6 py-1" style="background-color: #EDD4FF; border:1px solid #9C2DEB">On Duty</span></div>
                    </div>
                    <!--begin::Close-->
                    <div class="d-flex justify-content-end px-2 py-2">
                        <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                            data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="#000" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="#000" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <!--begin::Heading-->
                    <form id="attendanceFormonduty">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Attendance Type<span   class="text-danger">*</span></label>
                            <select id="onduty_attendance_type_add" name="onduty_attendance_type_add" class="select3 form-select" onchange="onduty_attendance_type_func_add()">
                                <option value="today">Today</option>
                                <option value="tomorrow">Tomorrow</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-lg-4 mb-3" id="onduty_attd_type_today_add" style="display: block;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Today Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i">
                                  <i  class="mdi mdi-calendar-month-outline fs-4"></i>
                                </span>
                                <input type="text" class="form-control" value="<?php echo date('d-M-Y'); ?>" disabled />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="onduty_attd_type_tomor_add" style="display: none;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Tomorrow Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-gray-200i"><i  class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                <?php
                                $onduty_currentDate = new DateTime();
                                $onduty_currentDate->modify('+1 days');
                                $onduty_futureDate = $onduty_currentDate->format('d-M-Y');
                                ?>
                                <input type="text" class="form-control" value="<?php echo $onduty_futureDate; ?>" disabled />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="onduty_attd_type_custom_add" style="display: none;">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Custom Date<span   class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i  class="mdi mdi-calendar-month-outline fs-4"></i></span>
                                <input type="text" class="form-control common_datepicker" id="custom_date_add_on" name="attendance_date_range_add" readonly value="<?php echo date('d-M-Y'); ?>" />
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3" id="onduty_attd_type_custom_st_time_add" >
                            <label class="text-dark mb-1 fs-6 fw-semibold">Start Time<span   class="text-danger">*</span></label>
                            <input class="form-control form-control-solid onduty_attd_type_custom_st_time_add common_time_class" id="onduty_st_time_add" name="st_time"  placeholder="Pick time" />
                            <div id="onduty_st_time_add_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3" id="onduty_attd_type_custom_ed_time_add" >
                            <label class="text-dark fs-6 mb-1 fw-semibold">End Time<span    class="text-danger">*</span></label>
                            <input class="form-control form-control-solid onduty_attd_type_custom_ed_time_add common_timepicker" id="onduty_ed_time_add" name="end_time"  placeholder="Pick time" />
                            <div id="onduty_ed_time_add_err" class="text-danger"></div>
                            
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Company<span class="text-danger">*</span></label>
                            <select id="company_id_od" name="company_id" class="select3 form-select" >
                                <option value="">Select Company</option>
                                <option value="egc">Elysium Groups of Companies</option>
                                @if(isset($company_list))
                                @foreach($company_list as $clist)
                                    <option value="{{$clist->sno}}" >{{$clist->company_name}}</option>
                                @endforeach
                                @endif
                            </select>
                            <div id="company_id_od_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3" id="business_od">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Entity<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="entity_id_od" name="entity_id" >
                                <option value="">Select Entity</option>

                            </select>
                            <div id="entity_id_od_err" class="text-danger"></div>
                        </div>
                        <input type="hidden" class="form-control" name="entry_select" value="3"  />
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Department<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="department_add_od" name="department_id">
                                <option value="">Select Department</option>
                            </select>
                            <div id="department_add_od_err" class="text-danger"></div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Staff<span  class="text-danger">*</span></label>
                            <select class="select3 form-select" id="staff_add_od" name="staff_id[]" placeholder="Select Staff">
                            </select>
                             <div id="staff_add_od_err" class="text-danger"></div>
                        </div>

                        
                    </div>
                    <div class="row">
                        
                        <div class="col-lg-12 mb-3">
                            <label class="text-dark mb-1 fs-6 fw-semibold">Reason<span   class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" id="od_reason" name="reason" placeholder="Enter Reason"></textarea>
                             <div id="od_reason_err" class="text-danger"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="reset" class="btn btn-outline-danger text-primary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="odDutyBtn" class="btn btn-primary" onclick="mark_onDuty_att_func()">Mark OnDuty Attendance</button>
                    </div>
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal -  Mark  On Duty Attendance -->


  <!--begin::Modal -  Individual Staff Monthly Attendance -->
    <div class="modal fade" id="kt_modal_view_individual_day_attendance" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                    <div class="text-center mt-4 d-flex gap-3 align-items-center justify-content-start">
                        <h3 class="text-center text-black"> View Attendance</h3>
                        <div class="pb-2">
                          <label class="text-primary fw-bold fs-5">26-09-2025</label>
                        </div>
                    </div>
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary border rounded border-gray-200"
                        data-bs-dismiss="modal">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                        <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opa Credential Book="0.5" x="6" y="17.3137" width="16" height="2"
                                    rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                    transform="rotate(45 7.41422 6)" fill="currentColor" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <div class="row">
                        <div class=" col-lg-12 d-flex justify-content-between gap-2 flex-wrap flex-shrink-0 pb-4" >
                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                <small class="text-black fw-semibold">Present</small>
                            </div>
                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                <small class="text-black fw-semibold">Absent</small>
                            </div>
                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                <small class="text-black fw-semibold">Permission</small>
                            </div>
                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                <small class="text-black fw-semibold">Leave</small>
                            </div>
                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                <small class="text-black fw-semibold">On Duty</small>
                            </div>
                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                <label class="fw-bold text-center" style="font-size: 1.3rem;  min-width:80px;">90%</label>
                                <small class="fw-semibold">Overall</small>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <table
                                class="table align-middle table-row-dashed table-striped table-hover gy-0 gs-1 indiv_date_scroll_table">
                                <thead>
                                    <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary">
                                        <th class="min-w-100px">Staff's</th>
                                        <th class="min-w-80px">Mobile</th>
                                        <th class="min-w-100px">Dept / Sub Dept</th>
                                        <th class="min-w-100px">Job Position</th>
                                        <th class="min-w-50px">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold fs-7">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-start">
                                                <div class="avatar">
                                                    <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                        alt="user image" class="w-px-40 h-auto rounded-circle">
                                                </div>
                                                <div class="ms-2">
                                                    <div data-bs-toggle="modal"
                                                        data-bs-target="#kt_modal_view_individual_staff_attendance">
                                                        <span class="fs-7 me-1 fw-semibold">Sabana</span>
                                                    </div>
                                                    <div class="d-block text-secondary fs-8" data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom" title="Nick Name">Yasmin</div>
                                                </div>
                                            </div>

                                        </td>
                                        <td>
                                            <label class="text-black fw-semibold fs-7">9852124578</label>
                                        </td>
                                        <td>
                                            <label class="text-black fs-7">Production</label>
                                            <div class="d-block">
                                                <label class="badge bg-secondary text-white fs-8">Faculty</label>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="text-black fs-7">QA Tester</label>
                                        </td>
                                        <td>
                                           <span class="badge bg-label-success fs-6 text-black border border-success fw-bold px-3  py-1">Present</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-start">
                                                <div class="avatar">
                                                    <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                        alt="user image" class="w-px-40 h-auto rounded-circle">
                                                </div>
                                                <div class="ms-2">
                                                    <div data-bs-toggle="modal"
                                                        data-bs-target="#kt_modal_view_individual_staff_attendance">
                                                        <span class="fs-7 me-1">Nirmal</span>

                                                    </div>
                                                    <div class="d-block text-secondary fs-8" data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom" title="Nick Name">Vasav</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="text-black fw-semibold fs-7">9096202021</label>
                                        </td>
                                        <td>
                                            <label class="text-black fs-7">Production</label>
                                            <div class="d-block">
                                                <label class="badge bg-secondary text-white fs-8">Faculty</label>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="text-black fs-7">QA Tester</label>
                                        </td>
                                        <td>
                                          <span class="badge bg-label-success fs-6 text-black border border-success fw-bold px-3  py-1">Present</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-start">
                                                <div class="avatar">
                                                    <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                        alt="user image" class="w-px-40 h-auto rounded-circle">
                                                </div>
                                                <div class="ms-2">
                                                    <div data-bs-toggle="modal"
                                                        data-bs-target="#kt_modal_view_individual_staff_attendance">
                                                        <span class="fs-7 me-1">Venkatesh S</span>

                                                    </div>
                                                    <div class="d-block text-secondary fs-8" data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom" title="Nick Name">Venkat S</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="text-black fw-semibold fs-7">9645124512</label>
                                        </td>
                                        <td>
                                            <label class="text-black fs-7">Production</label>
                                            <div class="d-block">
                                                <label class="badge bg-secondary text-white fs-8">Faculty</label>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="text-black fs-7">QA Tester</label>
                                        </td>
                                        <td>
                                           <span class="badge bg-label-danger fs-6 text-black border border-danger fw-bold px-3  py-1">Absent</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
  <!--end::Modal -  Individual Staff Monthly Attendance-->


    <!--begin::Modal View Attendance--->
    <div class="modal fade" id="kt_modal_view_attendance" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded" style="background: linear-gradient(225deg, white 20%, #fba919 100%);">
                <!--begin::Close-->
                <div class="d-flex justify-content-end px-2 py-2">
                    <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                        data-bs-dismiss="modal">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                        <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                    transform="rotate(-45 6 17.3137)" fill="#000" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                    transform="rotate(45 7.41422 6)" fill="#000" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </div>
                </div>
                <!--end::Close-->
                <!--begin::Modal header-->
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom-1">
                    <div class="d-flex flex-column">
                        <div class="avatar-stack">
                            <img src="{{ asset('assets/egc_images/auth/user_3.png') }}" alt="user-avatar"
                                class="avatar-img" />
                            <img src="#" alt="user-avatar"
                                class="avatar-img" />
                            <img src="#" alt="user-avatar"
                                class="avatar-img" />
                        </div>
                        <div class="row mb-2">
                            <h3 class="text-black">View Staff Attendance</h3>
                        </div>
                    </div>
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                 <div class="modal-body pt-0 pb-10 px-10 px-xl-20 bg-white">
                    <input type="hidden" id="sts_change_id" name="sts_change_id" />

                    <div class="row mb-3">

                        <!--begin::Tabs-->
                        <div class="nav-align-top nav-tabs-shadow mb-3">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <button type="button" class="nav-link active" role="tab"
                                        data-bs-toggle="tab" data-bs-target="#basic_info" aria-controls="basic_info"
                                        aria-selected="true">
                                        Staff Info
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#Attendence_info" aria-controls="Attendence_info"
                                        aria-selected="false">
                                        Attendence Info
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <!--end::Tabs-->

                        <!--begin::Tab content-->
                        <div class="tab-content">

                            <!--begin::Basic Info-->
                            <div class="tab-pane fade show active" id="basic_info" role="tabpanel">
                                <div class="row mb-2">
                                    <!-- Left side -->
                                    <div class="col-lg-8">
                                        <div class="row mb-2">
                                            <label class="col-5 fw-semibold fs-7 text-dark">Department</label>
                                            <label class="col-1 fw-semibold fs-7">:</label>
                                            <label class="col-6 fw-semibold fs-6 text-black">Production</label>
                                        </div>
                                        <div class="row mb-2">
                                            <label class="col-5 fw-semibold fs-7 text-dark">Overall Percentage</label>
                                            <label class="col-1 fw-semibold fs-7">:</label>
                                            <label class="col-6 fw-semibold fs-6 text-primary">96%</label>
                                        </div>
                                        <div class="row mb-2">
                                            <label class="col-5 fw-semibold fs-7 text-dark">Weekoff</label>
                                            <label class="col-1 fw-semibold fs-7">:</label>
                                            <label class="col-6 fw-semibold fs-6 text-black">Tuesday</label>
                                        </div>
                                        <div class="row mb-2">
                                            <label class="col-5 fw-semibold fs-7 text-dark">Branch</label>
                                            <label class="col-1 fw-semibold fs-7">:</label>
                                            <label class="col-6 fw-semibold fs-6 text-black">Madurai, Anna Nagar</label>
                                        </div>
                                        <div class="row mb-2">
                                            <label class="col-5 fw-semibold fs-7 text-dark">Mobile No</label>
                                            <label class="col-1 fw-semibold fs-7">:</label>
                                            <label class="col-6 fw-semibold fs-6 text-black">9898745120</label>
                                        </div>
                                        <div class="row mb-2">
                                            <label class="col-5 fw-semibold fs-7 text-dark">Shift Time</label>
                                            <label class="col-1 fw-semibold fs-7">:</label>
                                            <label class="col-6 fw-semibold fs-6 text-black">Morning Shift I</label>
                                        </div>
                                    </div>

                                    <!-- Right side -->
                                    <div class="col-lg-4">
                                        <div class="d-flex flex-column justify-content-center align-items-center gap-2">
                                            <div class="symbol symbol-35px me-2">
                                                <div class="image-input image-input-circle" data-kt-image-input="true">
                                                    <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                        alt="user-avatar" class="w-px-150 h-auto rounded-circle"
                                                        id="uploadedlogo" style="border: 2px solid #ab2b22;" />
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <label class="fs-7 text-black fw-semibold text-primary">Arun MK</label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!--end::Basic Info-->

                            <div class="tab-pane fade" id="Attendence_info" role="tabpanel">
                                <div class="row mb-2">
                                  <div class="col-lg-12 d-flex justify-content-end align-items-start gap-2 pb-4">
                                    <div class="d-flex align-items-center justify-content-center me-3">
                                        <label class="badge bg-label-success text-black border border-success fw-bold px-3  py-1"><span>P</span></label>
                                        <span class="fs-6 fw-semibold ms-1">Present</span>
                                    </div>
                                    <div class=" d-flex align-items-center justify-content-center me-3">
                                        <label class="badge bg-label-danger text-black border border-danger fw-bold px-3  py-1">
                                          <span>A</span>
                                        </label>
                                        <span class="fs-6 fw-semibold ms-1">Absent</span>
                                        <a href="javascipt:;" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Leave without intimation">
                                          <i class="mdi mdi mdi-information text-dark"></i>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center me-3">
                                            <label class="badge bg-label-warning text-black border border-warning fw-bold px-3  py-1">
                                              <span>L</span>
                                            </label>
                                        <span class="fs-6 fw-semibold ms-1">Leave</span>
                                        <a href="javascipt:;" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Leave with intimation">
                                          <i class="mdi mdi mdi-information text-dark"></i>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center me-3">
                                        <label>
                                            <span class="badge text-black fw-bold px-3  py-1"
                                                style="background-color: #EDD4FF; border:1px solid #9C2DEB">OD</span>
                                        </label>
                                        <span class="fs-6 fw-semibold ms-1">On Duty</span>
                                        <div class="">
                                            <a href="#" class="dropdown-toggle hide-arrow " data-bs-toggle="dropdown"
                                                data-trigger="hover">
                                                <i class="ms-1 mdi mdi-information fs-9"></i>
                                            </a>
                                            <div class="dropdown-menu py-2 px-4 text-black scroll-y w-250px max-h-250px">
                                                <div class="d-flex align-items-center mb-2 ">
                                                    <label>
                                                      <span class="badge text-black fw-bold px-3  py-1"style="background-color: #EDD4FF; border:1px solid #9C2DEB">30 M</span>
                                                    </label>
                                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                                    <label class="fs-6 fw-semibold">30 Minutes</label>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label>
                                                      <span class="badge text-black fw-bold px-3  py-1" style="background-color: #EDD4FF; border:1px solid #9C2DEB">1  H</span>
                                                    </label>
                                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                                    <label class="fs-6 fw-semibold">1 Hour</label>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label>
                                                      <span class="badge text-black fw-bold px-3  py-1"style="background-color: #EDD4FF; border:1px solid #9C2DEB">1.5 H</span>
                                                    </label>
                                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                                    <label class="fs-6 fw-semibold">1.5 Hours</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center me-3">
                                        <label>
                                          <span class="badge text-black fw-bold px-3  py-1" style="background-color: #DCE2FC; border:1px solid #2856FA;">Pr</span>
                                        </label>
                                        <span class="fs-6 fw-semibold ms-1">Permission</span>
                                        <div class="">
                                            <a href="#" class="dropdown-toggle hide-arrow " data-bs-toggle="dropdown"
                                                data-trigger="hover">
                                                <i class="ms-1 mdi mdi-information fs-9"></i>
                                            </a>
                                            <div class="dropdown-menu py-2 px-4 text-black scroll-y w-250px max-h-250px">
                                                <div class="d-flex align-items-center mb-2 ">
                                                    <label>
                                                      <span class="badge text-black fw-bold px-3  py-1" style="background-color: #DCE2FC; border:1px solid #2856FA;">30  M</span>
                                                    </label>
                                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                                    <label class="fs-6 fw-semibold">30 Minutes</label>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label>
                                                      <span class="badge text-black fw-bold px-3  py-1" style="background-color: #DCE2FC; border:1px solid #2856FA;">1 H</span>
                                                    </label>
                                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                                    <label class="fs-6 fw-semibold">1 Hour</label>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label>
                                                      <span class="badge text-black fw-bold px-3  py-1"style="background-color: #DCE2FC; border:1px solid #2856FA;">0.5 D</span>
                                                    </label>
                                                    <label class="fs-6 fw-semibold me-2 ms-2">-</label>
                                                    <label class="fs-6 fw-semibold">Half Day</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="col-lg-12">
                                    <div class="table-responsive" style="max-height:400px; overflow-y:auto; overflow-x:hidden;">
                                      <table
                                          class="table align-middle table-row-dashed table-striped table-hover gy-1 gs-2">
                                          <thead style="position: sticky; top: 0; z-index: 5;">
                                              <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary">
                                                  <th class="min-w-150px">Date</th>
                                                  <th class="min-w-100px">Attendance</th>
                                                  <th class="min-w-100px">Timing</th>
                                                  <th class="min-w-100px">Reason</th>
                                              </tr>
                                          </thead>
                                          <tbody class="text-gray-600 fw-semibold fs-7">
                                              <tr>
                                                  <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 4</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Thu</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                     <div class="d-flex flex-column gap-1">
                                                          <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#DCE2FC; border:1px solid #2856FA;">30 M</span></label>
                                                          <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#EDD4FF; border:1px solid #9C2DEB;">1 H</span></label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label>09:30 AM to 10:00 AM</label>
                                                      <label>06:00 PM to 07:00 PM</label>
                                                  </td>
                                                  <td>
                                                      <label class="text-danger">Banking, College Lecture</label>
                                                  </td>
                                              </tr>
                                              <tr>
                                                   <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 6</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Sat</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label
                                                          class="badge bg-label-warning text-black border border-warning fw-bold px-3  py-1"><span>L</span></label>
                                                  </td>
                                                  <td>-</td>
                                                  <td>
                                                      <label class="text-danger">Health issues</label>
                                                  </td>
                                              </tr>
                                              <tr>
                                                  <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 18</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Mon</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label
                                                          class="badge bg-label-danger text-black border border-danger fw-bold px-3  py-1"><span>A</span></label>
                                                  </td>
                                                  <td>-</td>
                                                  <td><label class="text-danger"></label></td>
                                              </tr>
                                              <tr>
                                                   <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 10</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Wed</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <div class="d-flex flex-column gap-1">
                                                          <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#DCE2FC; border:1px solid #2856FA;">30 M</span></label>
                                                          <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#EDD4FF; border:1px solid #9C2DEB;">1.5 H</span></label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label>06:00 PM to 08:30 PM</label>
                                                      <label>05:00 PM to 06:00 PM</label>
                                                  </td>
                                                  <td>
                                                      <label class="text-danger">College Lecture, emergency Purpose</label>
                                                  </td>
                                              </tr>
                                              <tr>
                                                  <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 13</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Sat</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label
                                                          class="badge bg-label-warning text-black border border-warning fw-bold px-3  py-1"><span>L</span></label>
                                                  </td>
                                                  <td>-</td>
                                                  <td>
                                                      <label class="text-danger">Temple Visit</label>
                                                  </td>
                                              </tr>
                                              <tr>
                                                   <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 14</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">WKD</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label><span class="badge text-black fw-bold px-3  py-1"
                                                              style="background-color: #EDD4FF; border:1px solid #9C2DEB">1.5
                                                              H</span></label>
                                                  </td>
                                                  <td>09:00 AM to 07:30 PM</td>
                                                  <td>
                                                      <label class="text-danger">College Lecture</label>
                                                  </td>
                                              </tr>
                                              <tr>
                                                   <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 17</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Wed</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label
                                                          class="badge bg-label-danger text-black border border-danger fw-bold px-3  py-1"><span>A</span></label>
                                                  </td>
                                                  <td>-</td>
                                                  <td><label class="text-danger"></label></td>
                                              </tr>
                                              <tr>
                                                   <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 24</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Wed</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                      <label><span class="badge text-black fw-bold px-3  py-1"
                                                              style="background-color: #EDD4FF; border:1px solid #9C2DEB">1.5
                                                              H</span></label>
                                                  </td>
                                                  <td>07:00 AM to 08:30 AM</td>
                                                  <td>
                                                      <label class="text-danger">College Lecture</label>
                                                  </td>
                                              </tr>
                                              <tr>
                                                   <td>
                                                    <label class="fw-semibold fs-7 text-nowrap">Jan 25</label>
                                                      <div class="d-block">
                                                        <label class="text-warning fs-8">Mon</label>
                                                      </div>
                                                  </td>
                                                  <td>
                                                     <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#DCE2FC; border:1px solid #2856FA;">30 M</span></label>
                                                  </td>
                                                  <td>01:00 PM to 02:00 PM</td>
                                                  <td>
                                                      <label class="text-danger">Emergency Purpose</label>
                                                  </td>
                                              </tr>
                                          </tbody>
                                      </table>
                                    </div>
                                  </div>
                                </div>
                            </div>

                        </div>
                        <!--end::Tab content-->

                    </div>
                </div>
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal View Attendance-->


     <!--begin::Modal Monthly View--->
    <div class="modal fade" id="kt_modal_view_individual_staff_attendance" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-xl">
            <!--begin::Modal content-->
            <div class="modal-content rounded" style="background: linear-gradient(225deg, white 20%, #fba919 100%);">
                <!--begin::Close-->
                <div class="d-flex justify-content-end px-2 py-2">
                    <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                        data-bs-dismiss="modal">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                        <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                    transform="rotate(-45 6 17.3137)" fill="#000" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                    transform="rotate(45 7.41422 6)" fill="#000" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </div>
                </div>
                <!--end::Close-->
                <!--begin::Modal header-->
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom-1">
                    <div class="d-flex flex-column">
                        <div class="avatar-stack">
                            <img src="{{ asset('assets/egc_images/auth/user_3.png') }}" alt="user-avatar"
                                class="avatar-img" />
                            <img src="{{ asset('assets/newImgs/user_8.jfif') }}" alt="user-avatar"
                                class="avatar-img" />
                            <img src="{{ asset('assets/newImgs/user_4.png') }}" alt="user-avatar"
                                class="avatar-img" />
                        </div>
                        <div class="row mb-2">
                            <h3 class="text-black">View Monthly Attendanced</h3>
                        </div>
                    </div>
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20 bg-white ">
                    <div class="row mb-3">
                        <!--begin::Tabs-->
                      <div class="nav-align-top nav-tabs-shadow mb-3 ">
                          <ul class="nav nav-tabs  singlestaff-tabs" role="tablist">
                              <li class="nav-item">
                                  <button type="button" class="nav-link active" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_jan" aria-controls="tab_single_jan"
                                      aria-selected="true">
                                      Jan
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_feb" aria-controls="tab_single_feb"
                                      aria-selected="false">
                                      Feb
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_mar" aria-controls="tab_single_mar"
                                      aria-selected="false">
                                      Mar
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_apr" aria-controls="tab_single_apr"
                                      aria-selected="false">
                                      Apr
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_may" aria-controls="tab_single_may"
                                      aria-selected="false">
                                      May
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_jun" aria-controls="tab_single_jun"
                                      aria-selected="false">
                                      Jun
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_jul" aria-controls="tab_single_jul"
                                      aria-selected="false">
                                      Jul
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_aug" aria-controls="tab_single_aug"
                                      aria-selected="false">
                                      Aug
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_sep" aria-controls="tab_single_sep"
                                      aria-selected="false">
                                      Sep
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_oct" aria-controls="tab_single_oct"
                                      aria-selected="false">
                                      Oct
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_nov" aria-controls="tab_single_nov"
                                      aria-selected="false">
                                      Nov
                                  </button>
                              </li>
                              <li class="nav-item">
                                  <button type="button" class="nav-link" role="tab"
                                      data-bs-toggle="tab" data-bs-target="#tab_single_dec" aria-controls="tab_single_dec"
                                      aria-selected="false">
                                      Dec
                                  </button>
                              </li>
                          </ul>
                      </div>
                        <!--end::Tabs-->

                        <!--begin::Tab content-->
                        <div class="tab-content">
                            <!--begin::Basic Info-->
                            <div class="tab-pane fade show active singlestaff-container" id="tab_single_jan" role="tabpanel">
                                <div class="row">
                                  <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>

                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_feb" role="tabpanel">
                                <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_mar" role="tabpanel">
                              <div class="row ">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem;  min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_apr" role="tabpanel">
                              <div class="row ">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_may" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_jun" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_jul" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_aug" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_sep" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_oct" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_nov" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="tab_single_dec" role="tabpanel">
                              <div class="row">
                                   <div class="col-lg-12 d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-shrink-1 pb-3">
                                            <div class="avatar avatar-xl flex-shrink-0">
                                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="d-flex flex-column overflow-hidden ms-2">
                                                <label class="text-primary fs-5 fw-semibold">
                                                    Balaji Muruga Prasath Prasath
                                                </label>
                                                <div>
                                                  <span class="badge bg-warning text-white fs-8" data-bs-toggle="tooltip"  data-bs-placement="bottom" title="Department Name">
                                                    Production
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap flex-shrink-0" >
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-success rounded bg-label-success">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">22</label>
                                                <small class="text-black fw-semibold">Present</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-danger rounded bg-label-danger">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">01</label>
                                                <small class="text-black fw-semibold">Absent</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#DCE2FC; border:1px solid #2856FA;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Permission</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-warning rounded bg-label-warning">
                                                <label class="fw-bold text-black text-center" style="font-size: 1.3rem; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">Leave</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 rounded"
                                                style="background:#EDD4FF; border:1px solid #9C2DEB;">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; color:black; min-width:80px;">02</label>
                                                <small class="text-black fw-semibold">On Duty</small>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center p-2 border border-primary rounded bg-primary text-white">
                                                <label class="fw-bold text-center" style="font-size: 1.3rem; min-width:80px;">90%</label>
                                                <small class="fw-semibold">Overall</small>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-lg-12">
                                        <table class="table align-top gy-1 gs-2 list_page_empty">
                                            <thead>
                                                <tr class="text-start align-top fw-bold fs-6 gs-0 bg-primary singlestaff-header">
                                                    <th class="min-w-80px text-center">Monday</th>
                                                    <th class="min-w-80px text-center">Tuesday</th>
                                                    <th class="min-w-80px text-center">Wednesday</th>
                                                    <th class="min-w-80px text-center">Thursday</th>
                                                    <th class="min-w-80px text-center">Friday</th>
                                                    <th class="min-w-80px text-center">Saturday</th>
                                                    <th class="min-w-80px text-center">WKDday</th>
                                                </tr>
                                            </thead>
                                            <tbody class="singlestaff-body">

                                            </tbody>
                                        </table>
                                  </div>
                              </div>
                            </div>
                            <!--end::Basic Info-->
                        </div>
                        <!--end::Tab content-->
                    </div>
                </div>
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal Monthly View-->

        <!--begin::Modal -  Mark Present Attendance  -->
    <div class="modal fade" id="kt_modal_edit" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
        data-bs-backdrop="static" data-bs-focus="false">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div
                    class="modal-header d-flex align-items-center justify-content-between border border-bottom-1 pb-0 mb-4">
                    <div class="text-center mt-4 d-flex gap-3 align-items-center">
                        <h3 class="text-center text-black">Update Attendance</h3>
                    </div>
                    <!--begin::Close-->
                   <div class="d-flex justify-content-end px-2 py-2">
                        <div class="btn btn-sm btn-icon btn-active-color-primary rounded" style="border: 2px solid #000;"
                            data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#000"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="#000" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="#000" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body pt-0 pb-10 px-10 px-xl-20">
                    <div class="row">

                      <!-- Staff Info -->
                      <div class="col-lg-6 mb-3">
                        <div class="d-flex align-items-center justify-content-start">
                            <div class="avatar-xl mt-3">
                                <img src="{{ asset('assets/egc_images/auth/user_3.png') }}" alt="user image"
                                    class="w-px-50 h-auto rounded-circle ">
                            </div>
                            <div
                                class=" d-flex flex-column justify-content-between align-items-start gap-1">
                                <a href="javascript:;" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_view_individual_staff_attendance">
                                    <span class="fs-5 me-1 text-black">Arun MK</span>
                                </a>
                                {{-- <span class="badge bg-warning text-white fs-8 me-1"
                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                    title="Department Name">Production</span> --}}

                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 mb-3">
                        <div class="row mb-2">
                          <label class="col-4 fs-7 fw-semibold">Branch</label>
                          <label class="col-1 fs-7 fw-bold text-center">:</label>
                          <label class="col-7 fs-6 fw-bold" id="staff_branch">Elysium Academy Madurai</label>
                        </div>
                        <div class="row mb-2">
                          <label class="col-4 fs-7 fw-semibold">Department</label>
                          <label class="col-1 fs-7 fw-bold text-center">:</label>
                          <label class="col-7 fs-6 fw-bold" id="staff_department">Production</label>
                        </div>
                      </div>

                      <!-- Date -->
                      <div class="col-lg-6 mb-3">
                        <label class="text-dark mb-1 fs-6 fw-semibold">Date<span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                          <span class="input-group-text"><i class="mdi mdi-calendar-month-outline fs-4"></i></span>
                          <input type="text" id="attendance_date_edit" name="attendance_date_edit" placeholder="Select Date" class="form-control date_att_up">
                        </div>
                      </div>

                      <!-- Entry Dropdown -->
                      <div class="col-lg-6 mb-3">
                        <label class="text-dark mb-1 fs-6 fw-semibold">Entry<span class="text-danger">*</span></label>
                        <select id="entry_select_edit" name="entry_select_edit" class="form-select" onchange="updateAttendanceUI(this.value)">
                          <option value="present" selected>Present</option>
                          <option value="absent">Absent (Leave Without Intimation)</option>
                          <option value="on_duty">On Duty</option>
                          <option value="permission">Permission</option>
                          <option value="leave">Leave (Leave With Intimation)</option>
                          <option value="workoff">Work Off</option>
                        </select>
                      </div>

                      <!-- Start Time -->
                      <div class="col-lg-3 mb-3 type_st_dt_edit" style="display: none;">
                        <label class="text-dark mb-1 fs-6 fw-semibold">Start Time<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="HH:MM" id="Startflatpickr-time" />
                      </div>



                      <!-- End Time -->
                      <div class="col-lg-3 mb-3 type_end_dt_edit" style="display: none;">
                        <label class="text-dark fs-6 mb-1 fw-semibold">End Time<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="HH:MM" id="Endflatpickr-time" />
                      </div>

                      <!-- Reason -->
                      <div class="col-lg-6 mb-3 reason_edit" style="display: none;">
                        <label class="text-dark mb-1 fs-6 fw-semibold">Reason<span class="text-danger">*</span></label>
                        <textarea class="form-control" rows="1" id="reason_edit" name="reason_edit" placeholder="Enter Reason"></textarea>
                      </div>

                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="reset" class="btn btn-outline-danger text-primary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="create_sms_btn" class="btn btn-primary" data-bs-dismiss="modal">Update Attendance</button>
                    </div>
                </div>

                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal -  Mark Present Attendance -->
<script>
  const dropArea = document.getElementById("dropArea");
  const fileInput = document.getElementById("fileInput");
  const fileList = document.getElementById("fileList");

  // Highlight drop area when dragging files
  dropArea.addEventListener("dragover", (e) => {
      e.preventDefault();
      dropArea.classList.add("bg-light");
  });

  dropArea.addEventListener("dragleave", () => {
      dropArea.classList.remove("bg-light");
  });

  // Handle file drop
  dropArea.addEventListener("drop", (e) => {
      e.preventDefault();
      dropArea.classList.remove("bg-light");

      if (e.dataTransfer.files.length > 0) {
          fileInput.files = e.dataTransfer.files;
          displayFileNames(Array.from(e.dataTransfer.files));
      }
  });

  // Display selected file names
  fileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
          displayFileNames(Array.from(e.target.files));
      }
  });

  function displayFileNames(files) {
      fileList.innerHTML = files
          .map((file, index) => `<span>${index + 1}. ${file.name} </span>`)
          .join("");
  }
   document.getElementById('uploadMaterialForm').addEventListener('submit', function (event) {
            event.preventDefault();
            
            const errMssg = document.getElementById('err_mssg');
            errMssg.innerHTML = "";
            
            const fileInput = document.getElementById('fileInput');
            const uploadCourseId = document.getElementById('upload_chap_course_id').value;
                // ✅ For non-lab docs → file required
                if (!fileInput.value || fileInput.files.length === 0) {
                errMssg.textContent = "Please select at least one file to upload.";
                return;
                }
            
            
            const formData = new FormData();
                for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('file_upload', fileInput.files[i]);
                }
            
            
            const uploadButton = document.getElementById('uploadButton');
            const uploadLoader = document.getElementById('uploadLoader');
            
            uploadButton.disabled = true;
            uploadLoader.classList.remove('d-none');
            
            fetch('{{ route("upload_essl") }}', {
                method: 'POST',
                headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
                .then(async response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    const text = await response.text();
                    throw new Error("Server returned non-JSON response:\n" + text);
                }
                })
                .then(data => {
                uploadLoader.classList.add('d-none');
                uploadButton.disabled = false;
                if (data.success) {
                    toastr.success('Materials uploaded successfully!');
                
                    // Reset full form
                    document.getElementById('uploadMaterialForm').reset();
                
                    // Reset select2/select3 dropdowns
                    $('#document_type_id').val('').trigger('change');
                    $('#course_chapter_id').val('').trigger('change');
                    $('#languageSelect').val('javascript').trigger('change'); // default back
                
                    // Reset code editor
                    $('#codeEditor').text('');
                    $('#codeTitle').val('');
                    $('#labCodeSection').addClass('d-none'); // hide Lab Code section
                
                    // Reset file input + file list
                    $('#fileInput').val('');
                    $('#fileList').empty();
                    $('#err_mssg').text('');
                
                    // Hide modal
                    $('#kt_modal_upload_chapter').modal('hide');
                }
                else {
                    errMssg.textContent = data.message || "An error occurred while uploading files.";
                }
                })
                .catch(error => {
                uploadLoader.classList.add('d-none');
                uploadButton.disabled = false;
                console.error('Error:', error);
                errMssg.textContent = "An error occurred while uploading files.";
                });
        });
</script>
<style>
  #dropArea {
      cursor: pointer;
  }
  #dropArea.bg-light {
      background-color: #f8f9fa;
  }
  #fileList p {
      margin: 0;
      font-size: 0.8rem;
  }
</style>

    <script>
        let currentPage = 1;
        let isLoading = false;
        let abortController = new AbortController();
    </script>

    <!--Add - Attendance Type - Leave Start -->
    <script>

       

        function attendance_type_func_add() {
            var attendance_type_add = document.getElementById("attendance_type_add").value;

            if (attendance_type_add == "today") {
                document.getElementById("attd_type_today_add").style.display = "block";
                document.getElementById("attd_type_tomor_add").style.display = "none";
                document.getElementById("attd_type_custom_add").style.display = "none";
            } else if (attendance_type_add == "tomorrow") {
                document.getElementById("attd_type_today_add").style.display = "none";
                document.getElementById("attd_type_tomor_add").style.display = "block";
                document.getElementById("attd_type_custom_add").style.display = "none";
            } else {
                document.getElementById("attd_type_today_add").style.display = "none";
                document.getElementById("attd_type_tomor_add").style.display = "none";
                document.getElementById("attd_type_custom_add").style.display = "block";
            }
        }
    </script>
    <!-- Add - Attendance Type - Leave End -->

    <!-- Add - Attendance Type - Permission Start -->
    <script>
        function perm_attendance_type_func_add() {
            var perm_attendance_type_add = document.getElementById("perm_attendance_type_add").value;

            if (perm_attendance_type_add == "today") {
                document.getElementById("perm_attd_type_today_add").style.display = "block";
                document.getElementById("perm_attd_type_tomor_add").style.display = "none";
                document.getElementById("perm_attd_type_custom_add").style.display = "none";

            } else if (perm_attendance_type_add == "tomorrow") {
                document.getElementById("perm_attd_type_today_add").style.display = "none";
                document.getElementById("perm_attd_type_tomor_add").style.display = "block";
                document.getElementById("perm_attd_type_custom_add").style.display = "none";
            } else {
                document.getElementById("perm_attd_type_today_add").style.display = "none";
                document.getElementById("perm_attd_type_tomor_add").style.display = "none";
                document.getElementById("perm_attd_type_custom_add").style.display = "block";
            }
        }
    </script>
    <!-- Add - Attendance Type - Permission End -->

    <!-- Add - Attendance Type - On Duty Start -->
    <script>
        function onduty_attendance_type_func_add() {
            var onduty_attendance_type_add = document.getElementById("onduty_attendance_type_add").value;

            if (onduty_attendance_type_add == "today") {
                document.getElementById("onduty_attd_type_today_add").style.display = "block";
                document.getElementById("onduty_attd_type_tomor_add").style.display = "none";
                document.getElementById("onduty_attd_type_custom_add").style.display = "none";

            } else if (onduty_attendance_type_add == "tomorrow") {
                document.getElementById("onduty_attd_type_today_add").style.display = "none";
                document.getElementById("onduty_attd_type_tomor_add").style.display = "block";
                document.getElementById("onduty_attd_type_custom_add").style.display = "none";
            } else {
                document.getElementById("onduty_attd_type_today_add").style.display = "none";
                document.getElementById("onduty_attd_type_tomor_add").style.display = "none";
                document.getElementById("onduty_attd_type_custom_add").style.display = "block";
            }
        }
    </script>
    <!-- Add - Attendance Type - On Duty End -->

    <script>
        $(".list_page").DataTable({
            "ordering": false,
            // "aaSorting":[],
            "language": {
                "lengthMenu": "Show _MENU_",
            },
            "dom": "<'row mb-3'" +
                "<'col-sm-6 d-flex align-items-center justify-conten-start'l>" +
                "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">"
        });
        $(".list_page_empty").DataTable({
            "ordering": false,
            "paging": false,
            // "aaSorting":[],
            "language": {
                "lengthMenu": "Show _MENU_",
            },
            "dom": "<'row mb-3'" +
                // "<'col-sm-6 d-flex align-items-center justify-conten-start'l>" +
                // "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                // "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                // "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">"
        });
    </script>

    <script>
        $(".scroll_table").DataTable({
            // "ordering": false,
            "aaSorting": [],
            // "pagingType": 'simple_numbers',
            "pagingType": "full_numbers",
            // "sorting":false,
            "paging": false,
            // "buttons": [
            //             'copy', 'csv', 'excel', 'pdf', 'print'
            //         ],
            "language": {
                "lengthMenu": "Show _MENU_",
            },
            // "pageLength": 5,
            "dom": "<'row'" +
                // "<'col-sm-6 d-flex align-items-center justify-conten-start my-3'l>" +
                "<'col-sm-12 d-flex align-items-center justify-content-end my-3'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                // "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">"
        });
        $('.scroll_table').wrap('<div class="dataTables_scroll" />');

        $(".indiv_date_scroll_table").DataTable({
            // "ordering": false,
            "aaSorting": [],
            // "pagingType": 'simple_numbers',
            // "pagingType": "full_numbers",
            // "sorting":false,
            "paging": false,
            // "buttons": [
            //             'copy', 'csv', 'excel', 'pdf', 'print'
            //         ],
            "language": {
                "lengthMenu": "Show _MENU_",
            },
            // "pageLength": 5,
            "dom": "<'row'" +
                // "<'col-sm-6 d-flex align-items-center justify-conten-start my-3'l>" +
                // "<'col-sm-12 d-flex align-items-center justify-content-end my-3'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                // "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">"
        });
        $('.indiv_date_scroll_table').wrap('<div class="indiv_date_scroll_table" />');
    </script>
 <!-- Toastr CSS from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Toastr JavaScript from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        /* Customize Toastr notification */
        .toast-success {
            background-color: green;
        }

        /* Customize Toastr notification */
        .toast-error {
            background-color: red;
        }
    </style>

<script>


        // Display Toastr messages
            @if (Session::has('toastr'))
                var type = "{{ Session::get('toastr')['type'] }}";
                var message = "{{ Session::get('toastr')['message'] }}";
                toastr[type](message);
            @endif
    </script>
    {{-- Calender View Script --}}
    <script>
      document.addEventListener("DOMContentLoaded", function () {
          const ssMonthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

          function ssGenerateDays(container, year, monthIndex) {
              const ssHeaderRow = container.querySelector(".singlestaff-header");
              const ssBody = container.querySelector(".singlestaff-body");

              if (!ssHeaderRow || !ssBody) return;

              // Clear header and body
              ssHeaderRow.innerHTML = "";
              ssBody.innerHTML = "";

              // Weekdays header
              const ssWeekdays = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
              ssWeekdays.forEach(day => {
                  const th = document.createElement("th");
                  th.className = "min-w-80px text-center";
                  th.textContent = day;
                  ssHeaderRow.appendChild(th);
              });

              const ssDaysInMonth = new Date(year, monthIndex + 1, 0).getDate();
              let ssRow = document.createElement("tr");
              ssBody.appendChild(ssRow);

              // Determine first day weekday (0=Sunday, 1=Monday, ...)
              const firstDayWeekday = new Date(year, monthIndex, 1).getDay();

              // Convert JS Sunday=0 to our Monday=0 index (Mon=0, Sun=6)
              const startOffset = firstDayWeekday === 0 ? 6 : firstDayWeekday - 1;

              // Fill empty <td> for days before the first of the month
              for (let i = 0; i < startOffset; i++) {
                  ssRow.appendChild(document.createElement("td"));
              }

              for (let d = 1; d <= ssDaysInMonth; d++) {
                  const ssDate = new Date(year, monthIndex, d);
                  const ssWeekday = ssDate.getDay(); // 0=Sun, 1=Mon...

                  // Start new row on Monday if current row is full
                  if (ssRow.children.length === 7) {
                      ssRow = document.createElement("tr");
                      ssBody.appendChild(ssRow);
                  }

                  const td = document.createElement("td");

                  // Date label
                  const dateDiv = document.createElement("div");
                  dateDiv.className = "d-flex justify-content-end align-items-start";
                  dateDiv.innerHTML = `<label class="text-black fw-bold fs-8">${String(d).padStart(2,"0")}</label>`;
                  td.appendChild(dateDiv);

                  // Attendance assignment
                  let status;
                  const rand = Math.random();

                  if (ssWeekday === 0) {
                      // Sunday = Weekend
                      status = {
                          type: "weekend",
                          html: `<div class="d-flex justify-content-center mt-4 mb-8 align-items-center">
                                  <div class="badge bg-label-secondary border border-secondary text-black fw-bold px-3 py-1">Weekend</div>
                              </div>`
                      };
                  } else if (rand < 0.75) {
                      // 75% Present
                      status = {
                          type: "present",
                          html: `<div class="d-flex justify-content-center mt-4 mb-8 align-items-center">
                                  <div class="badge bg-label-success text-black border border-success fw-bold px-3 py-1">Present</div>
                              </div>`
                      };
                  } else if (rand < 0.82) {
                      status = {
                          type: "mix",
                          html: `<div class="d-flex flex-column align-items-center mt-4 mb-2">
                                  <div class="badge text-black fw-bold px-3 py-1 mb-1" style="background-color:#DCE2FC; border:1px solid #2856FA;">30 M - Permission</div>
                                  <div class="badge text-black fw-bold px-3 py-1" style="background-color:#EDD4FF; border:1px solid #9C2DEB;">01 H - On Duty</div>
                              </div>`
                      };
                  } else if (rand < 0.89) {
                      status = {
                          type: "leave",
                          html: `<div class="d-flex justify-content-center mt-4 mb-8 align-items-center">
                                  <div class="text-black fs-7 fw-bold mx-3 px-2">Leave</div>
                                  <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="bottom" title="-">
                                      <i class="mdi mdi-information text-black"></i>
                                  </a>
                              </div>`
                      };
                  } else {
                      status = {
                          type: "absent",
                          html: `<div class="d-flex justify-content-center mt-4 mb-8 align-items-center">
                                  <div class="text-black fs-7 fw-bold mx-3 px-2">Absent</div>
                              </div>`
                      };
                  }

                  const wrapper = document.createElement("div");
                  wrapper.innerHTML = status.html;
                  td.appendChild(wrapper.firstChild);

                  // Apply background for Leave/Absent
                  if (status.type === "leave") td.style.backgroundColor = "#F9FFC9";
                  if (status.type === "absent") td.style.backgroundColor = "#FFCCC9";

                  ssRow.appendChild(td);
              }

              // Fill trailing empty cells at the end of last row
              while (ssRow.children.length < 7) {
                  ssRow.appendChild(document.createElement("td"));
              }
          }

          // Tab click handler
          document.querySelectorAll('.nav-tabs [data-bs-toggle="tab"]').forEach(ssTab => {
              ssTab.addEventListener("shown.bs.tab", function (e) {
                  const ssTargetId = e.target.getAttribute("data-bs-target");
                  const ssContainer = document.querySelector(ssTargetId);
                  if (!ssContainer) return;

                  const ssParts = ssTargetId.split("_");
                  const ssShortMonth = ssParts[ssParts.length - 1].slice(0, 3).toLowerCase();
                  const ssMonthMap = { jan:0, feb:1, mar:2, apr:3, may:4, jun:5, jul:6, aug:7, sep:8, oct:9, nov:10, dec:11 };
                  const ssMonthIndex = ssMonthMap[ssShortMonth] ?? 0;

                  ssGenerateDays(ssContainer, 2025, ssMonthIndex);
              });
          });

          // Initial load (January)
          document.querySelectorAll(".singlestaff-container").forEach(ssContainer => {
              ssGenerateDays(ssContainer, 2025, 0);
          });
      });
    </script>

    <script>
  $(document).ready(function() {
    const $select = $('#staffpresent');
    const $count = $('#staff-count');

    // Initialize Select3 (if not already initialized)
    $select.select2({
      placeholder: 'Select staff',
      width: '100%'
    });

    // Function to update count
    function updateStaffCount() {
      $count.text($select.val() ? $select.val().length : 0);
    }

    // Initialize count on page load
    updateStaffCount();

    // Update count when selection changes
    $select.on('change', updateStaffCount);
  });
</script>

<script>
  $(document).ready(function() {
    const $absentSelect = $('#staffabsent');
    const $absentCount = $('#absent-count');

    // Initialize Select3 (or Select2) if not already initialized
    $absentSelect.select2({
      placeholder: 'Select absent staff',
      width: '100%'
    });

    // Function to update absent count
    function updateAbsentCount() {
      $absentCount.text($absentSelect.val() ? $absentSelect.val().length : 0);
    }

    // Initialize count on page load
    updateAbsentCount();

    // Update count when selection changes
    $absentSelect.on('change', updateAbsentCount);
  });
</script>


<script>
    function updateAttendanceUI(entryValue) {
    const reason = document.querySelector('.reason_edit');
    const startTime = document.querySelector('.type_st_dt_edit');
    const endTime = document.querySelector('.type_end_dt_edit');

    // Hide all by default
    reason.style.display = 'none';
    startTime.style.display = 'none';
    endTime.style.display = 'none';

    if(entryValue === 'present') {
        // Nothing visible
    }
    else if(['absent','leave','workoff'].includes(entryValue)) {
        reason.style.display = 'block';
    }
    else if(['on_duty','permission'].includes(entryValue)) {
        reason.style.display = 'block';
        startTime.style.display = 'block';
        endTime.style.display = 'block';
    }
    }

    // Initialize UI on page load
    document.addEventListener('DOMContentLoaded', () => {
    const entrySelect = document.getElementById('entry_select_edit');
    updateAttendanceUI(entrySelect.value);
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

                // stateDropdown.empty().append('<option value="">Select Department</option>');

                // if (entity_id) {
                //     // Fetch and populate states based on selected country
                //     $.ajax({
                //         url: "{{ route('department') }}",
                //         type: "GET",
                //         data: {
                //             entity_id: entity_id
                //         },
                //         success: function(response) {
                //             if (response.status === 200 && response.data) {
                //                 response.data.forEach(function(state) {
                //                     stateDropdown.append($('<option></option>').attr(
                //                         'value', state.sno)
                //                         .attr('data-erpdepartmentid', state.erp_department_id)
                //                         .text(state.department_name));
                //                 });
                                
                //             }
                //         },
                //         error: function(error) {
                //             console.error('Error fetching Department:', error);
                //         }
                //     });
                // }

                
            });
     });
</script>

    {{-- List Script --}}
<!-- <script>
    // document.addEventListener("DOMContentLoaded", function () {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];

        const container = document.querySelector(".attendance-container"); // assuming single container
        let currentDate = new Date();

        const currentMonthLabel = document.getElementById("currentMonth");

        function renderMonth(date) {
            const monthIndex = date.getMonth();
            const year = date.getFullYear();

            // update label
            currentMonthLabel.textContent = `${monthNames[monthIndex]} ${year}`;

            // generate attendance table
            generateDays(container, year, monthIndex);
        }

        document.getElementById("prevMonth").addEventListener("click", function (e) {
            e.preventDefault();
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderMonth(currentDate);
            loadAttendance(currentPage);
        });

        document.getElementById("nextMonth").addEventListener("click", function (e) {
            e.preventDefault();
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderMonth(currentDate);
            loadAttendance(currentPage);
        });

        // Initial render
        renderMonth(currentDate);

        function generateDays(container, year, monthIndex) {
            const headerRow = container.querySelector(".attendance-header");
            const bodyRows = container.querySelectorAll(".attendance-body tr");

            if (!headerRow) return;

            // clear previous columns except first (Staffs) and last (Actions)
            while (headerRow.children.length > 2) {
                headerRow.removeChild(headerRow.children[1]);
            }

            const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();

            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, monthIndex, d);
                const dayName = date.toLocaleString("en-US", { weekday: "short" });

                const th = document.createElement("th");
                th.className = "min-w-100px text-center align-middle";

                th.innerHTML = `
                <a href="javascript:;"
                    class="text-white d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="modal"
                    data-bs-target="#kt_modal_view_individual_day_attendance">
                    <div style="font-weight:600; font-size:14px;">${monthNames[monthIndex]} ${d}</div>
                    <div style="font-size:12px; color:#ccc;">${dayName}</div>
                </a>
                `;
                headerRow.insertBefore(th, headerRow.lastElementChild);
            }

            // populate body rows
            bodyRows.forEach(row => {
                while (row.children.length > 2) {
                    row.removeChild(row.children[1]);
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(year, monthIndex, d);
                    const dayName = date.toLocaleString("en-US", { weekday: "short" });
                    const td = document.createElement("td");

                    let content;
                    // content ='<div class="skeleton"></div>'
                    if (dayName === "Sun") {
                        content = `<span class="badge bg-label-secondary border border-secondary text-black fw-bold px-3 py-1">WKD</span>`;
                    } else {
                        const badges = [
                            `<label class="badge bg-label-success text-black border border-success fw-bold px-3 py-1"><span>P</span></label>`,
                            `<label class="badge bg-label-warning text-black border border-warning fw-bold px-3 py-1"><span>L</span></label>`,
                            `<label class="badge bg-label-danger text-black border border-danger fw-bold px-3 py-1"><span>A</span></label>`,
                            `<label><span class="badge bg-primary border border-primary text-white fw-bold px-3 py-1">H</span></label>`,
                            `<div class="d-flex flex-column gap-1">
                                <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#DCE2FC; border:1px solid #2856FA;">30 M</span></label>
                                <label><span class="badge text-black fw-bold px-3 py-1" style="background-color:#EDD4FF; border:1px solid #9C2DEB;">1.5 H</span></label>
                            </div>`
                        ];
                        content = badges[Math.floor(Math.random() * badges.length)];
                    }

                    td.innerHTML = content;
                    row.insertBefore(td, row.lastElementChild);
                }
            });
        }
    // });
</script> -->

    {{-- List Script --}}
<script>
     let currentDate = new Date();
    document.addEventListener("DOMContentLoaded", function () {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];

        const container = document.querySelector(".attendance-container"); // assuming single container
       

        const currentMonthLabel = document.getElementById("currentMonth");

        function renderMonth(date) {
            const monthIndex = date.getMonth();
            const year = date.getFullYear();

            // update label
            currentMonthLabel.textContent = `${monthNames[monthIndex]} ${year}`;

            // generate attendance table
            generateDays(container, year, monthIndex);
        }

        document.getElementById("prevMonth").addEventListener("click", function (e) {
            e.preventDefault();
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderMonth(currentDate);
            loadAttendance(currentPage);
        });

        document.getElementById("nextMonth").addEventListener("click", function (e) {
            e.preventDefault();
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderMonth(currentDate);
            loadAttendance(currentPage);
        });

        // Initial render
        renderMonth(currentDate);

        function generateDays(container, year, monthIndex) {
            const headerRow = container.querySelector(".attendance-header");
            const bodyRows = container.querySelectorAll(".attendance-body tr");

            if (!headerRow) return;

            // clear previous columns except first (Staffs) and last (Actions)
            while (headerRow.children.length > 2) {
                headerRow.removeChild(headerRow.children[1]);
            }

            const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();

            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, monthIndex, d);
                const dayName = date.toLocaleString("en-US", { weekday: "short" });

                const th = document.createElement("th");
                th.className = "min-w-100px text-center align-middle";

                th.innerHTML = `
                <a href="javascript:;"
                    class="text-white d-flex flex-column align-items-center justify-content-center"
                    data-bs-toggle="modal"
                    data-bs-target="#kt_modal_view_individual_day_attendance">
                    <div style="font-weight:600; font-size:14px;">${monthNames[monthIndex]} ${d}</div>
                    <div style="font-size:12px; color:#ccc;">${dayName}</div>
                </a>
                `;
                headerRow.insertBefore(th, headerRow.lastElementChild);
            }

            // populate body rows
            bodyRows.forEach(row => {
                while (row.children.length > 2) {
                    row.removeChild(row.children[1]);
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(year, monthIndex, d);
                    const dayName = date.toLocaleString("en-US", { weekday: "short" });
                    const td = document.createElement("td");

                    td.classList.add('skeleton-cell');
                    td.innerHTML = `<div class="skeleton"></div>`;
                    row.insertBefore(td, row.lastElementChild);
                }
            });
        }
    });
</script>

<script>
    // LOAD LIST
    function loadAttendance(page = 1) {
        const perpage = document.getElementById('perpage').value;
        const search = document.getElementById('search_filter').value;
        const company_fill = document.getElementById('company_fill').value;
        const entity_fill = document.getElementById('entity_fill').value;
        const month = currentDate.getMonth() + 1;
        const year = currentDate.getFullYear();
        const formattedMonth = new Date(year, month - 1).toLocaleString('en-us', { month: 'short' }).toUpperCase();
        const monthFilter = `${formattedMonth}-${year}`;
        const url = `/hr_enroll/manage_attendance?page=${page}&sorting_filter=${perpage}&search_filter=${search}&company_fill=${company_fill}&entity_fill=${entity_fill}&month_filter=${monthFilter}`;

        isLoading = true;

        document.querySelector(".attendance-body").innerHTML = skeletenAttendanceRow();

        if (abortController.signal) abortController.abort();
        abortController = new AbortController();

        fetch(url, { 
            headers: { "X-Requested-With": "XMLHttpRequest" }, 
            signal: abortController.signal 
        })
        .then(res => res.json())
        .then(res => {

            renderAttendanceRows(res.data);        // only rows (center dates are created separately)

            updatePagination(res.current_page, res.last_page, res.total, perpage);

            // re-generate date columns after table loads
            renderMonth(currentDate);

            isLoading = false;
        })
        .catch(err => {
            if (err.name !== "AbortError") console.error(err);
            isLoading = false;
        });
    }

    // SKELETON
    function skeletenAttendanceRow() {
        return `
            <tr class="skeleton-loader">
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
            </tr>
        `;
    }

    // RENDER BODY ROWS
    function renderAttendanceRows(data) {

        let tbody = document.querySelector(".attendance-body");
        tbody.innerHTML = "";

        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="50" class="text-center">No Records Found</td></tr>`;
            return;
        }

        data.forEach(row => {
            let staff_image = '';
            if (row.staff_image && row.staff_image.trim() !== '') {
                if (row.company_type == 1) {
                    staff_image = `staff_images/Management/${row.staff_image}`;
                } else {
                    staff_image = `staff_images/Buisness/${row.company_id}/${row.entity_id}/${row.staff_image}`;
                }
            } else {
                staff_image = row.gender == 1
                    ? 'assets/egc_images/auth/user_2.png'
                    : 'assets/egc_images/auth/user_7.png';
            }
            staff_image = `{{ asset('${staff_image}') }}`;
            tbody.insertAdjacentHTML("beforeend", `
                <tr data-staff-id="${row.staff_id}">
                    
                    <!-- STAFF COLUMN -->
                    <td>
                        <div class="d-flex align-items-center justify-content-start">
                            <div class="avatar-xl mt-3">
                                <img src="${staff_image}" 
                                    class="w-px-50 h-px-50 rounded-circle" />
                            </div>

                            <div class="d-flex flex-column justify-content-between align-items-start gap-1 ms-2">
                                <a href="javascript:;" data-bs-toggle="modal"
                                data-bs-target="#kt_modal_view_individual_staff_attendance">
                                    <span class="fs-7 text-black">${row.staff_name}</span>
                                </a>

                                <span class="badge bg-warning text-white fs-8" 
                                    data-bs-toggle="tooltip" title="Department Name">
                                        ${row.department_name}
                                </span>

                                <div>
                                    <span class="badge bg-dark text-white fs-8"
                                        data-bs-toggle="tooltip" title="Overall Percentage">
                                        ${row.overall_percentage ?? '0'}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- AUTO DATE TDs WILL BE INSERTED HERE -->
                    
                    <!-- ACTIONS -->
                    <td class="text-center">
                        <span class="d-flex gap-1 justify-content-center">
                            <a href="javascript:;" data-bs-toggle="modal" 
                            data-bs-target="#kt_modal_view_attendance">
                                <i class="mdi mdi-eye fs-3 text-black"></i>
                            </a>

                            <a href="javascript:;" data-bs-toggle="modal" 
                            data-bs-target="#kt_modal_edit">
                                <i class="mdi mdi-square-edit-outline fs-3 text-black"></i>
                            </a>
                        </span>
                    </td>
                </tr>
            `);
        });

        // After rows created → fill attendance
        fillAttendanceCells(data);
    }

    // INSERT ATTENDANCE INTO CENTER DATE COLUMNS
    function fillAttendanceCells(data) {

        const bodyRows = document.querySelectorAll(".attendance-body tr");

        bodyRows.forEach(row => {

            const staffId = row.getAttribute("data-staff-id");

            let staff = data.find(s => s.staff_id == staffId);
            if (!staff) return;

            // existing TDs (Staff + Actions)
            const firstTD = row.children[0];
            const lastTD = row.children[row.children.length - 1];

            // remove old date cells
            while (row.children.length > 2) {
                row.removeChild(row.children[1]);
            }

            // use attendance object to fill days
            Object.keys(staff.attendance).forEach(date => {
                const status = staff.attendance[date];
                const td = document.createElement("td");
                td.innerHTML = getBadge(status);
                row.insertBefore(td, lastTD);
            });
        });
    }

    // BADGE STYLE
    function getBadge(a) {
        switch (a) {
            case 'P': return `<span class="badge bg-label-success border border-success text-black fw-bold px-3 py-1">P</span>`;
            case 'A': return `<span class="badge bg-label-danger border border-danger text-black fw-bold px-3 py-1">A</span>`;
            case 'L': return `<span class="badge bg-label-warning border border-warning text-black fw-bold px-3 py-1">L</span>`;
            case 'OD': return `<span class="badge bg-primary border border-primary text-white fw-bold px-3 py-1">OD</span>`;
            case 'PR': return `<span class="badge bg-info border border-info text-black fw-bold px-3 py-1">PR</span>`;
            case 'WK': return `<span class="badge bg-label-secondary border border-secondary text-black fw-bold px-3 py-1">WKD</span>`;
            case 'HD': return `<span class="badge bg-primary border border-primary text-white fw-bold px-3 py-1">H</span>`;
            default: return `<span class="badge bg-secondary">-</span>`;
        }
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
        let showingInfo = `Showing ${start} to ${end} of ${total} results`;
        paginationContainer.insertAdjacentHTML('beforeend', showingInfo);

        // Create Pagination Buttons

        // << First button
        let firstButton = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}" data-bs-toggle="tooltip" data-bs-placement="top" title="First Page"><button class=" page-link" onclick="loadAttendance(1)" >«</button> </li>`;
        
        // < Previous button
        let prevButton = `<li class="page-item ${currentPage > 1 ? '' : 'disabled'}" data-bs-toggle="tooltip" data-bs-placement="top" title="Previous"><button class=" page-link" onclick="loadAttendance(${currentPage - 1})" >‹</button> </li>`;
        
        // Next button
        let nextButton = `<li class="page-item ${currentPage < lastPage ? '' : 'disabled'}" data-bs-toggle="tooltip" data-bs-placement="top" title="Next"><button class="page-link" onclick="loadAttendance(${currentPage + 1})" >›</button> </li>`;
        
        // >> Last button
        let lastButton = `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}" data-bs-toggle="tooltip" data-bs-placement="top" title="Last Page"><button class=" page-link" onclick="loadAttendance(${lastPage})" >»</button> </li>`;

        // Page Number Buttons (Dynamically show a range of pages around the current page)
        let pageButtons = '';
        let range = 2; // Show 2 pages before and after the current page
        let startPage = Math.max(1, currentPage - range);
        let endPage = Math.min(lastPage, currentPage + range);

        // Generate page numbers
        for (let i = startPage; i <= endPage; i++) {
            pageButtons += `<li class="page-item ${i === currentPage ? 'active' : ''}"><button class="page-link " onclick="loadAttendance(${i})">${i}</button> </li>`;
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


    // INIT
    loadAttendance(1);
</script>


<script>
     $(document).ready(function() {

        
           
           // Business dropdown
            $("#company_id_present").on('change', function() {
               
                 var countryId = $(this).val();
                var entityDropdown = $("#entity_id_present");

                entityDropdown.empty().append('<option value="">Select Entity</option>');

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
                                    entityDropdown.append($('<option></option>').attr(
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

             
                var stateDropdown = $("#department_add");
                if (countryId == 'egc') {
                    $('#business_present').hide();
                    stateDropdown.empty().append('<option value="">Select Department</option>');
                    var entity_id = 0 ;
                   
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
                    
                }else{
                    $('#business_present').show();
                }
            });

            $("#entity_id_present").on('change', function() {
                 var entity_id = $(this).val();
                 var stateDropdown = $("#department_add");

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

            $("#department_add").on('change', function() {
                 var departmentId = $(this).val();
                    var date = $('#attend_date').val();

                    if(departmentId){
                         $.ajax({
                        url: "{{ route('staff_att') }}",
                        type: "GET",
                        data: {
                            department_id: departmentId,
                            date: date,
                        },
                        success: function (response) {
                            
                            if (response.status === 200 && response.data) {
                                var selectDropdown = $("#staff_add");
                                selectDropdown.empty().append('<option value="all">All</option>');
                                response.data.forEach(function (k) {
                                    selectDropdown.append($('<option></option>').attr('value', k.sno).text(k.staff_name + ' - ' + k.nick_name));
                                });

                            } else {
                            }
                        },
                        error: function (error) {
                            console.error('Error fetching staff:', error);
                           
                        }
                    });
                    }
                   
            });
            
     });
</script>

<script>
     $(document).ready(function() {
        // Business dropdown
            $("#company_id_absent").on('change', function() {
               
                 var countryId = $(this).val();
                var entityDropdown = $("#entity_id_absent");

                entityDropdown.empty().append('<option value="">Select Entity</option>');

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
                                    entityDropdown.append($('<option></option>').attr(
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

             
                var stateDropdown = $("#department_add_absent");
                if (countryId == 'egc') {
                    $('#business_absent').hide();
                    stateDropdown.empty().append('<option value="">Select Department</option>');
                    var entity_id = 0 ;
                   
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
                    
                }else{
                    $('#business_absent').show();
                }
            });

            $("#entity_id_absent").on('change', function() {
                 var entity_id = $(this).val();
                 var stateDropdown = $("#department_add_absent");

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

            $("#department_add_absent").on('change', function() {
                 var departmentId = $(this).val();
                    var date = $('#attend_date_absent').val();

                    if(departmentId){
                         $.ajax({
                        url: "{{ route('staff_att') }}",
                        type: "GET",
                        data: {
                            department_id: departmentId,
                            date: date,
                        },
                        success: function (response) {
                           
                            if (response.status === 200 && response.data) {
                                var selectDropdown = $("#staff_add_absent");
                                selectDropdown.empty().append('<option value="all">All</option>');
                                response.data.forEach(function (k) {
                                    selectDropdown.append($('<option></option>').attr('value', k.sno).text(k.staff_name + ' - ' + k.nick_name));
                                });

                            } else {
                            }
                        },
                        error: function (error) {
                            console.error('Error fetching staff:', error);
                           
                        }
                    });
                    }
                   
            });
     });
</script>

<script>
     $(document).ready(function() {
        // Business dropdown
            $("#company_id_leave").on('change', function() {
               
                 var countryId = $(this).val();
                var entityDropdown = $("#entity_id_leave");

                entityDropdown.empty().append('<option value="">Select Entity</option>');

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
                                    entityDropdown.append($('<option></option>').attr(
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

             
                var stateDropdown = $("#department_add_leave");
                if (countryId == 'egc') {
                    $('#business_leave').hide();
                    stateDropdown.empty().append('<option value="">Select Department</option>');
                    var entity_id = 0 ;
                   
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
                    
                }else{
                    $('#business_leave').show();
                }
            });

            $("#entity_id_leave").on('change', function() {
                 var entity_id = $(this).val();
                 var stateDropdown = $("#department_add_leave");

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

            $("#department_add_leave").on('change', function() {
                 var departmentId = $(this).val();
                    var type = $('#attendance_type_add').val();
                    var custom_date = $('#custom_date_add').val();

                    if(departmentId){
                         $.ajax({
                        url: "{{ route('staff_att') }}",
                        type: "GET",
                        data: {
                            department_id: departmentId,
                            type: type,
                            date: custom_date,
                        },
                        success: function (response) {
                            
                            if (response.status === 200 && response.data) {
                                var selectDropdown = $("#staff_add_leave");
                                selectDropdown.empty().append('<option value="all">All</option>');
                                response.data.forEach(function (k) {
                                    selectDropdown.append($('<option></option>').attr('value', k.sno).text(k.staff_name + ' - ' + k.nick_name));
                                });

                            } else {
                            }
                        },
                        error: function (error) {
                            console.error('Error fetching staff:', error);
                           
                        }
                    });
                    }
                   
            });
     });
</script>

<script>
     $(document).ready(function() {
        // Business dropdown
            $("#company_id_pr").on('change', function() {
               
                 var countryId = $(this).val();
                var entityDropdown = $("#entity_id_pr");

                entityDropdown.empty().append('<option value="">Select Entity</option>');

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
                                    entityDropdown.append($('<option></option>').attr(
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

             
                var stateDropdown = $("#department_add_pr");
                if (countryId == 'egc') {
                    $('#business_pr').hide();
                    stateDropdown.empty().append('<option value="">Select Department</option>');
                    var entity_id = 0 ;
                   
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
                    
                }else{
                    $('#business_pr').show();
                }
            });

            $("#entity_id_pr").on('change', function() {
                 var entity_id = $(this).val();
                 var stateDropdown = $("#department_add_pr");

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

            $("#department_add_pr").on('change', function() {
                 var departmentId = $(this).val();

                    var type = $('#perm_attendance_type_add').val();
                    var custom_date = $('#custom_date_add_pr').val();

                    if(departmentId){
                         $.ajax({
                        url: "{{ route('staff_att') }}",
                        type: "GET",
                        data: {
                            department_id: departmentId,
                            type: type,
                            date: custom_date,
                        },
                        success: function (response) {
                            
                            if (response.status === 200 && response.data) {
                                var selectDropdown = $("#staff_add_pr");
                                selectDropdown.empty().append('<option value="all">All</option>');
                                response.data.forEach(function (k) {
                                    selectDropdown.append($('<option></option>').attr('value', k.sno).text(k.staff_name + ' - ' + k.nick_name));
                                });

                            } else {
                            }
                        },
                        error: function (error) {
                            console.error('Error fetching staff:', error);
                           
                        }
                    });
                    }
                   
            });
     });
</script>



<script>
     $(document).ready(function() {
        // Business dropdown
            $("#company_id_od").on('change', function() {
               
                 var countryId = $(this).val();
                var entityDropdown = $("#entity_id_od");

                entityDropdown.empty().append('<option value="">Select Entity</option>');

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
                                    entityDropdown.append($('<option></option>').attr(
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

             
                var stateDropdown = $("#department_add_od");
                if (countryId == 'egc') {
                    $('#business_od').hide();
                    stateDropdown.empty().append('<option value="">Select Department</option>');
                    var entity_id = 0 ;
                   
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
                    
                }else{
                    $('#business_od').show();
                }
            });

            $("#entity_id_od").on('change', function() {
                 var entity_id = $(this).val();
                 var stateDropdown = $("#department_add_od");

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

            $("#department_add_od").on('change', function() {
                 var departmentId = $(this).val();
                    var type = $('#onduty_attendance_type_add').val();
                    var custom_date = $('#custom_date_add_on').val();

                    if(departmentId){
                         $.ajax({
                        url: "{{ route('staff_att') }}",
                        type: "GET",
                        data: {
                            department_id: departmentId,
                            type: type,
                            date: custom_date,
                        },
                        success: function (response) {
                           
                            if (response.status === 200 && response.data) {
                                var selectDropdown = $("#staff_add_od");
                                selectDropdown.empty().append('<option value="all">All</option>');
                                response.data.forEach(function (k) {
                                    selectDropdown.append($('<option></option>').attr('value', k.sno).text(k.staff_name + ' - ' + k.nick_name));
                                });

                            } else {
                                
                            }
                        },
                        error: function (error) {
                            console.error('Error fetching staff:', error);
                            
                           
                        }
                    });
                    }
                   
            });
     });
</script>

<script>
    function mark_present_att_func() { 
        $("#markPresentBtn").prop('disabled', true); // Disable the button
    
        let err = false; // Initialize error flag

        var department_add = $('#department_add').val();
        if(department_add == ''){
            $('#department_add_err').text('Department is Required..!');
            err=true;
        }else{
              $('#department_add_err').text('');
        }

         var staff_add = $('#staff_add').val();
            if(staff_add == ''){
                $('#staff_add_err').text('Staff is Required..!');
                err=true;
            }else{
                $('#staff_add_err').text('');
            }
    
        // If there's an error, enable the button again and exit the function
        if (err) {
            $("#markPresentBtn").prop('disabled', false);
            return false;
        }
    
        // Get the form data
        var formData = $('#attendanceForm').serialize(); // Serialize form data
    
        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        // Perform AJAX request
        $.ajax({
            url: "{{ route('staff_attendance') }}", // Replace with your route
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 200) {
                    toastr.success(response.message); // Show success message
                    location.reload(); // Reload page after success
                } else {
                    toastr.error('An error occurred while submitting the form.'); // Error message
                    $("#markPresentBtn").prop('disabled', false); // Enable button if there's an error
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while submitting the form.'); // Handle AJAX error
                console.error('Error:', xhr); // Log error for debugging
                $("#markPresentBtn").prop('disabled', false); // Enable button in case of an error
            }
        });
    }

    function mark_absent_att_func() { 
        $("#absentBtn").prop('disabled', true); // Disable the button
    
        let err = false; // Initialize error flag

        var department_add_absent = $('#department_add_absent').val();
        if(department_add_absent == ''){
            $('#department_add_absent_err').text('Department is Required..!');
            err=true;
        }else{
            $('#department_add_absent_err').text('');
        }

        var staff_add_absent = $('#staff_add_absent').val();
            if(staff_add_absent == ''){
                $('#staff_add_absent_err').text('Staff is Required..!');
                err=true;
            }else{
                $('#staff_add_absent_err').text('');
            }
    
        // If there's an error, enable the button again and exit the function
        if (err) {
            $("#absentBtn").prop('disabled', false);
            return false;
        }
    
        // Get the form data
        var formData = $('#attendanceFormabsent').serialize(); // Serialize form data
    
        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        // Perform AJAX request
        $.ajax({
            url: "{{ route('staff_attendance') }}", // Replace with your route
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 200) {
                    toastr.success(response.message); // Show success message
                    location.reload(); // Reload page after success
                } else {
                    toastr.error('An error occurred while submitting the form.'); // Error message
                    $("#absentBtn").prop('disabled', false); // Enable button if there's an error
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while submitting the form.'); // Handle AJAX error
                console.error('Error:', xhr); // Log error for debugging
                $("#absentBtn").prop('disabled', false); // Enable button in case of an error
            }
        });
    }

    function mark_leave_att_func() { 
        $("#leaveBtn").prop('disabled', true); // Disable the button
    
        let err = false; // Initialize error flag

        var department_add_leave = $('#department_add_leave').val();
        if(department_add_leave == ''){
            $('#department_add_leave_err').text('Department is Required..!');
            err=true;
        }else{
            $('#department_add_leave_err').text('');
        }

        var staff_add_leave = $('#staff_add_leave').val();
            if(staff_add_leave == ''){
                $('#staff_add_leave_err').text('Staff is Required..!');
                err=true;
            }else{
                $('#staff_add_leave_err').text('');
            }

            var reason_leave = $('#reason_leave').val();
            if(reason_leave == ''){
                $('#reason_leave_err').text('Reason is Required..!');
                err=true;
            }else{
                $('#reason_leave_err').text('');
            }
    
        // If there's an error, enable the button again and exit the function
        if (err) {
            $("#leaveBtn").prop('disabled', false);
            return false;
        }
    
        // Get the form data
        var formData = $('#attendanceFormleave').serialize(); // Serialize form data

        // Define variables for staff ID and leave date based on the attendance type
        var staffId = $('#staff_add_leave').val(); // Get selected staff_id
        var attendanceType = $('#attendance_type_add').val();
        var leaveDate;

        // Determine leave date based on attendance type
        if (attendanceType === 'today') {
            leaveDate = "{{ date('Y-m-d') }}"; // Today's date
        } else if (attendanceType === 'tomorrow') {
            leaveDate = "{{ now()->addDay()->format('Y-m-d') }}"; // Tomorrow's date
        } else if (attendanceType === 'custom') {
            leaveDate = $('#custom_date_add').val(); // Custom date from input
        }

    
        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        // Perform AJAX request
        $.ajax({
            url: "{{ route('staff_attendance') }}", // Replace with your route
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 200) {
                    toastr.success(response.message); // Show success message
                    location.reload(); // Reload page after success
                } else {
                    toastr.error('An error occurred while submitting the form.'); // Error message
                    $("#leaveBtn").prop('disabled', false); // Enable button if there's an error
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while submitting the form.'); // Handle AJAX error
                console.error('Error:', xhr); // Log error for debugging
                $("#leaveBtn").prop('disabled', false); // Enable button in case of an error
            }
        });
    }

     function mark_pr_att_func() { 
        $("#prBtn").prop('disabled', true); // Disable the button
    
        let err = false; // Initialize error flag

        var department_add_pr = $('#department_add_pr').val();
        if(department_add_pr == ''){
            $('#department_add_pr_err').text('Department is Required..!');
            err=true;
        }else{
            $('#department_add_pr_err').text('');
        }

        var staff_add_pr = $('#staff_add_pr').val();
            if(staff_add_pr == ''){
                $('#staff_add_pr_err').text('Staff is Required..!');
                err=true;
            }else{
                $('#staff_add_pr_err').text('');
            }

             var perm_st_time_add = $('#perm_st_time_add').val();
            if(perm_st_time_add == ''){
                $('#perm_st_time_add_err').text('Start Time is Required..!');
                err=true;
            }else{
                $('#perm_st_time_add_err').text('');
            }

             var perm_ed_time_add = $('#perm_ed_time_add').val();
            if(perm_ed_time_add == ''){
                $('#perm_ed_time_add_err').text('End Time is Required..!');
                err=true;
            }else{
                $('#perm_ed_time_add_err').text('');
            }

            var pr_reason = $('#pr_reason').val();
            if(pr_reason == ''){
                $('#pr_reason_err').text('Reason is Required..!');
                err=true;
            }else{
                $('#pr_reason_err').text('');
            }
    
        // If there's an error, enable the button again and exit the function
        if (err) {
            $("#prBtn").prop('disabled', false);
            return false;
        }
    
        // Get the form data
        var formData = $('#attendanceFormpermission').serialize(); // Serialize form data

        // Define variables for staff ID and leave date based on the attendance type
          var staffId = $('#staff_add_pr').val(); // Get selected staff_id
        var attendanceType = $('#perm_attendance_type_add').val();
        var startTime = $('#perm_st_time_add').val(); // Get selected staff_id
        var endtime = $('#perm_ed_time_add').val(); // Get selected staff_id
        var prDate;

        // Determine leave date based on attendance type
        if (attendanceType === 'today') {
            prDate = "{{ date('Y-m-d') }}"; // Today's date
        } else if (attendanceType === 'tomorrow') {
            prDate = "{{ now()->addDay()->format('Y-m-d') }}"; // Tomorrow's date
        } else if (attendanceType === 'custom') {
            prDate = $('#custom_date_add_pr').val(); // Custom date from input
        }

    
        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        // Perform AJAX request
        $.ajax({
            url: "{{ route('staff_attendance') }}", // Replace with your route
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 200) {
                    toastr.success(response.message); // Show success message
                    location.reload(); // Reload page after success
                } else {
                    toastr.error('An error occurred while submitting the form.'); // Error message
                    $("#prBtn").prop('disabled', false); // Enable button if there's an error
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while submitting the form.'); // Handle AJAX error
                console.error('Error:', xhr); // Log error for debugging
                $("#prBtn").prop('disabled', false); // Enable button in case of an error
            }
        });
    }

     function mark_onDuty_att_func() { 
        $("#odDutyBtn").prop('disabled', true); // Disable the button
    
        let err = false; // Initialize error flag

        var department_add_od = $('#department_add_od').val();
        if(department_add_od == ''){
            $('#department_add_od_err').text('Department is Required..!');
            err=true;
        }else{
            $('#department_add_od_err').text('');
        }

        var staff_add_od = $('#staff_add_od').val();
            if(staff_add_od == ''){
                $('#staff_add_od_err').text('Staff is Required..!');
                err=true;
            }else{
                $('#staff_add_od_err').text('');
            }

             var onduty_st_time_add = $('#onduty_st_time_add').val();
            if(onduty_st_time_add == ''){
                $('#onduty_st_time_add_err').text('Start Time is Required..!');
                err=true;
            }else{
                $('#onduty_st_time_add_err').text('');
            }

             var onduty_ed_time_add = $('#onduty_ed_time_add').val();
            if(onduty_ed_time_add == ''){
                $('#onduty_ed_time_add_err').text('End Time is Required..!');
                err=true;
            }else{
                $('#onduty_ed_time_add_err').text('');
            }

            var od_reason = $('#od_reason').val();
            if(od_reason == ''){
                $('#od_reason_err').text('Reason is Required..!');
                err=true;
            }else{
                $('#od_reason_err').text('');
            }
    
        // If there's an error, enable the button again and exit the function
        if (err) {
            $("#odDutyBtn").prop('disabled', false);
            return false;
        }
    
        // Get the form data
        var formData = $('#attendanceFormonduty').serialize(); // Serialize form data

         var staffId = $('#staff_add_od').val(); // Get selected staff_id
        var attendanceType = $('#onduty_attendance_type_add').val();
        var startTime = $('#onduty_st_time_add').val(); // Get selected staff_id
        var endtime = $('#onduty_ed_time_add').val(); // Get selected staff_id
        var odDate;

        // Determine leave date based on attendance type
        if (attendanceType === 'today') {
            odDate = "{{ date('Y-m-d') }}"; // Today's date
        } else if (attendanceType === 'tomorrow') {
            odDate = "{{ now()->addDay()->format('Y-m-d') }}"; // Tomorrow's date
        } else if (attendanceType === 'custom') {
            odDate = $('#custom_date_add_on').val(); // Custom date from input
        }

    
        // AJAX setup with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        // Perform AJAX request
        $.ajax({
            url: "{{ route('staff_attendance') }}", // Replace with your route
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 200) {
                    toastr.success(response.message); // Show success message
                    location.reload(); // Reload page after success
                } else {
                    toastr.error('An error occurred while submitting the form.'); // Error message
                    $("#odDutyBtn").prop('disabled', false); // Enable button if there's an error
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while submitting the form.'); // Handle AJAX error
                console.error('Error:', xhr); // Log error for debugging
                $("#odDutyBtn").prop('disabled', false); // Enable button in case of an error
            }
        });
    }

</script>
@endsection
