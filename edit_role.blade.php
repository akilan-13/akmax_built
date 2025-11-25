<!-- resources/views/content/user_management/manage_users/add.blade.php -->
@extends('layouts/layoutMaster')

@section('title', 'Update Manage Users')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')

  <div class="card">
    @php
        $helper = new \App\Helpers\Helpers();
    @endphp
    <div class="card-header border-bottom pb-1">
      <h5 class="card-title mb-1">Update Manage Users</h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{url('/dashboards')}}" class="d-flex align-items-center"><i class="mdi mdi-home text-body fs-4"></i></a>
          </li>
          <span class="text-dark opacity-75 me-1 ms-1">
            <i class="mdi mdi-chevron-double-right fs-4"></i>
          </span>
          <li class="breadcrumb-item">
            <a href="javascript:;" class="d-flex align-items-center">Users Management</a>
          </li>
        </ol>
      </nav>
    </div>
    <form id="userRolePermissionForm" action="{{ route('update_user_role_permission', ['id' => $role->sno]) }}" method="POST"  novalidate>
      @csrf
      <div class="card-body">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="form-check form-check-inline mt-8">
                    <label class="form-check-label" for="management">
                        <input class="form-check-input required-field" type="radio" name="company"
                            id="management" value="1"  {{$role->company_type == 2 ? '' :'checked' }}/>
                        Management
                    </label>
                </div>
                <div class="form-check form-check-inline mt-8">
                    <label class="form-check-label" for="business">
                        <input class="form-check-input required-field" type="radio" name="company"
                            id="business" value="2" {{$role->company_type == 2 ? 'checked' :'' }}/>
                        Business
                    </label>
                </div>
            </div>
            <div class="col-lg-4 mb-3 business_div ">
                <label class="text-black mb-1 fs-6 fw-semibold">Company Name</label>
                <select id="staff_company_name" name="staff_company_name" class="select3 form-select ">
                    <option value="">Select Company Name</option>
                        @if(isset($company_list))
                        @foreach($company_list as $clist)
                        <option value="{{$clist->sno}}" @if($role->comapny_id == $clist->sno) selected @endif>{{$clist->company_name}}</option>
                        @endforeach
                        @endif
                </select>
            </div>
            <div class="col-lg-4 mb-3 business_div">
                <label class="text-black mb-1 fs-6 fw-semibold">Entity Name</label>
                <select id="entity_name" name="entity_name" class="select3 form-select ">
                    <option value="">Select Entity Name</option>
                </select>
            </div>
        </div>
        <div class="row">
          <div class="col-lg-3 mb-3">
            <label class="text-dark mb-1 fs-6 fw-semibold">Role<span class="text-danger">*</span></label>
            <div class="management_div">
                <select id="role" name="role" class="select3 form-select">
                    <option value="">Select Role</option>
                    @php

                        $selectedRoleId = session('role_id'); // Get the role_id from session
                    @endphp
                    @foreach ($management_userRole as $rlist)
                        <option value="{{ $rlist->sno }}" @if($role->role_id == $rlist->sno) selected @endif>
                            {{ $rlist->role_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="business_div">
                <select name="business_role" id="business_user_role" class="select3 form-select required-field">
                    <option value="">Select Role</option>
                </select>
            </div>
          </div>
          <div class="col-lg-4 mb-2" id="accessHeadDiv" style="display: none;">
              <div class="card" style="background-color: #bebebe !important;">
                  <div class="card-body">
                      <div class="row px-1">
                          <div class="col-lg-4 ">
                              <input class="form-check-input " type="radio" name="access_head" id="view_rbh" value="1" @if($role->access_head == 1) checked @endif/>
                              <label class="text-black fs-6 fw-bold" for="view_rbh" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Regional Branch Head">RBH</label>
                          </div>
                          <div class="col-lg-4 ">
                              <input class="form-check-input " type="radio" name="access_head" id="view_rfh" value="2"  @if($role->access_head == 2) checked @endif/>
                              <label class="text-black fs-6 fw-bold" for="view_rfh" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Regional Franchises Head">RFH</label>
                          </div>
                          <div class="col-lg-4 ">
                              <input class="form-check-input " type="radio" name="access_head" id="view_gm" value="3" @if($role->access_head == 3) checked @endif/>
                              <label class="text-black fs-6 fw-bold" for="view_gm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Global Head">GH</label>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="col-lg-4 mb-2 ">
            <div class="card" style="background-color: #bebebe !important;">
                <div class="card-body">
                  <div class="row px-1">
                      <div class="col-lg-6 ">
                        <input class="form-check-input " type="radio" name="manage_branch" id="view_mm" value="2" @if($role->manage_branch == 2) checked @endif/>
                        <label class="text-black fs-6 fw-bold" for="view_mm" title="Multi Manage">Multi Manage</label>

                      </div>
                      <div class="col-lg-6 ">
                        <input class="form-check-input " type="radio" name="manage_branch" id="view_sm" value="1" @if($role->manage_branch == 1) checked @endif />
                        <label class="text-black fs-6 fw-bold" for="view_sm" title="Sigle Manage">Single Manage</label>

                      </div>
                  </div>
                </div>
            </div>
          </div>

        </div>
        <div class="d-flex justify-content-end align-items-center py-4">
          <div class="form-check form-check-inline">
            <input class="form-check-input select-all" type="checkbox" id="selectAllMain" />
            <label class="text-dark fs-6 fw-semibold" for="selectAllMain">Select All</label>
          </div>
        </div>
        <div id="accordion">
          @if(isset($menu['menu']))
              @foreach ($menu['menu'] as $menuItem)
                @if (isset($menuItem['name'], $menuItem['slug'])) 
                    <div class="col-lg-12 mb-2">
                        <div class="card-header" style="background-color: #fab845 !important;">
                            <div class="card-action-title d-flex justify-content-between align-items-center">
                                <div class="form-check form-check-inline">
                                    <?php $menuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['name']); ?>
                                    <input class="form-check-input menu-checkbox" type="checkbox" id="{{ $menuItem['slug'] }}" 
                                            @if(isset($flattenedPermissions[$menuKey]['menuChecked']) && $flattenedPermissions[$menuKey]['menuChecked'])
                                                checked
                                            @endif
                                    />
                                    <label class="text-black fs-6 fw-bold" for="{{ $menuItem['slug'] }}">{{ $menuItem['name'] }}</label>
                                </div>
                                <a class="dashboards_add text-black" type="button" data-toggle="collapse" data-target="#collapseMenu{{ $menuItem['slug'] }}" aria-expanded="true" aria-controls="collapseMenu{{ $menuItem['slug'] }}">
                                <i class="mdi mdi-chevron-down fs-3"></i>
                                </a>

                            </div>
                        </div>
                        <div id="collapseMenu{{ $menuItem['slug'] }}" class="collapse border" aria-labelledby="headingMenu{{ $menuItem['slug'] }}" data-parent="#accordion" >
                            <div class="card-body">
                            @if(isset($menuItem) && $menuItem['name'] == 'Dashboard')
                                <div class="row">
                                    @foreach(['Dashboard'] as $action)
                                        <div class="col-lg-2 mb-2 d-flex align-items-center">
                                            <div class="form-check form-check-inline">
                                                <?php
                                                    $menuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['name']);
                                                    $actionKey = preg_replace('/[^a-zA-Z0-9_]/', '', $action);
                                                ?>
                                                <input class="form-check-input submenu-action-checkbox"
                                                    type="checkbox" name="actions[]"
                                                    id="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $menuItem['slug'] }}"
                                                    value="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $menuItem['slug'] }}"
                                                    @if(isset($flattenedPermissions[$menuKey]['actions'])
                                                    && in_array($actionKey,
                                                    array_column($flattenedPermissions[$menuKey]['actions'], 'actionName'
                                                    )) &&
                                                    $flattenedPermissions[$menuKey]['actions'][array_search($actionKey,
                                                    array_column($flattenedPermissions[$menuKey]['actions'], 'actionName'
                                                    ))]['actionChecked']==1) checked @endif />
                                                <label class="text-black fs-6 fw-semibold text-truncate w-125px" data-bs-toggle="tooltip"
                                                data-bs-placement="bottom" title="{{ $action }}"
                                                    for="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $menuItem['slug'] }}">{{
                                                    $action }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="col-lg-4 mb-2">
                                        <div class="card" style="background-color: #dba4a0 !important;">
                                            <div class="card-body">
                                                <div class="row px-1">
                                                    <div class="col-lg-6">
                                                        <input class="form-check-input submenu-radio" type="radio"
                                                        name="radio_dashboard_{{ $menuItem['slug'] }}"
                                                        id="global_dashboard{{ $menuItem['slug'] }}"
                                                        value="global_dashboard"
                                                        checked
                                                        @if(isset($flattenedPermissions[$menuKey]['radios'][0])
                                                        &&
                                                        $flattenedPermissions[$menuKey]['radios'][0]['radioValue']=='global_lead'
                                                        &&
                                                        $flattenedPermissions[$menuKey]['radios'][0]['checked']==1)
                                                        checked @endif 
                                                            
                                                        />
                                                        <label class="text-black fs-6 fw-bold" for="global_dashboard{{ $menuItem['slug'] }}">Global</label>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <input class="form-check-input submenu-radio" type="radio"
                                                        name="radio_dashboard_{{ $menuItem['slug'] }}"
                                                        id="view_self_dashboard{{ $menuItem['slug'] }}"
                                                        value="view_self_dashboard" 
                                                        @if(isset($flattenedPermissions[$menuKey]['radios'][0])
                                                        &&
                                                        $flattenedPermissions[$menuKey]['radios'][0]['radioValue']=='view_self_lead'
                                                        &&
                                                        $flattenedPermissions[$menuKey]['radios'][0]['checked']==1)
                                                        checked @endif
                                                        />
                                                        <label class="text-black fs-6 fw-bold" for="view_self_dashboard{{ $menuItem['slug'] }}">View Self</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                                @if (isset($menuItem['submenu']))
                                    @foreach ($menuItem['submenu'] as $submenuItem)
                                        @if (isset($submenuItem['name'], $submenuItem['slug']))
                                        <?php $submenuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $submenuItem['name']); ?>
                                        <?php $mainmenuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['name']); ?>
                                        <div class="col-lg-12 mb-2">
                                            <div class="card-header" style="background-color: #dba4a0 !important;">
                                                <div class="card-action-title d-flex justify-content-between align-items-center">

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input submenu-checkbox" type="checkbox" id="{{ $submenuItem['slug'] }}"
                                                                @if (isset($flattenedPermissions[$mainmenuKey]['submenu'][$submenuKey]['submenuChecked']) &&
                                                                      $flattenedPermissions[$mainmenuKey]['submenu'][$submenuKey]['submenuChecked'] == 1
                                                                  )
                                                                  checked
                                                                  @endif
                                                        />
                                                        <label class="text-black fs-6 fw-bold" for="{{ $submenuItem['slug'] }}">{{ $submenuItem['name'] }}</label>
                                                    </div>
                                                    <a class="dashboards_add" type="button" data-toggle="collapse" data-target="#collapseSubmenu{{ $submenuItem['slug'] }}" aria-expanded="true" aria-controls="collapseSubmenu{{ $submenuItem['slug'] }}">
                                                        <i class="mdi mdi-chevron-down fs-3"></i>
                                                    </a>
                                                </div>
                                            </div>

                                        <div id="collapseSubmenu{{ $submenuItem['slug'] }}" class="collapse border" aria-labelledby="headingSubmenu{{ $submenuItem['slug'] }}">
                                            <div class="card-body">
                                            @if (isset($submenuItem['lastmenu']))
                                            @foreach ($submenuItem['lastmenu'] as $lastMenuItem)
                                                @if (isset($lastMenuItem['name'], $lastMenuItem['slug']))
                                                <?php $lastmenuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $lastMenuItem['name']); ?>
                                                <div class="col-lg-12 mb-2">
                                                    <div class="card-header" style="background-color: #ab2b22 !important;">
                                                        <div class="card-action-title d-flex justify-content-between align-items-center">
                                                            <div class="form-check form-check-inline">
                                                            <input class="form-check-input lastmenu-checkbox border-white"
                                                                type="checkbox"
                                                                id="{{ $lastMenuItem['slug'] }}"
                                                                @if (
                                                                        isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['lastmenuChecked'])
                                                                        && $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['lastmenuChecked'] == 1
                                                                    )
                                                                        checked
                                                                    @endif
                                                            >
                                                            <label class="text-white fs-6 fw-bold" for="{{ $lastMenuItem['slug'] }}">{{ $lastMenuItem['name'] }}</label>
                                                            </div>
                                                            <a class="dashboards_add_last text-white" type="button" data-toggle="collapse" data-target="#collapseLastmenu{{ $lastMenuItem['slug'] }}" aria-expanded="true" aria-controls="collapseLastmenu{{ $lastMenuItem['slug'] }}">
                                                            <i class="mdi mdi-chevron-down fs-3 " ></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div id="collapseLastmenu{{ $lastMenuItem['slug'] }}" class="collapse border" aria-labelledby="headingLastmenu{{ $lastMenuItem['slug'] }}">
                                                        <div class="card-body">

                                                            {{-- HR Management Start --}}
                                                            @if ($lastMenuItem['name'] == 'Manage Staff')
                                                            <div class="row">
                                                                @foreach(['List', 'View'] as $action)
                                                                <div class="col-lg-2 mb-2 d-flex align-items-center">
                                                                    <div class="form-check form-check-inline">
                                                                        <?php
                                                                            $menuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['name']);
                                                                            $actionKey = preg_replace('/[^a-zA-Z0-9_]/', '', $action);
                                                                        ?>
                                                                        <input class="form-check-input submenu-action-checkbox"
                                                                            type="checkbox" name="actions[]"
                                                                            id="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $lastMenuItem['slug'] }}"
                                                                            value="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $lastMenuItem['slug'] }}"
                                                                            @if(isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['actions'])
                                                                            && in_array($actionKey,
                                                                            array_column($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['actions'], 'actionName'
                                                                            )) &&
                                                                            $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['actions'][array_search($actionKey,
                                                                            array_column($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['actions'], 'actionName'
                                                                            ))]['actionChecked']==1) checked @endif />
                                                                        <label class="text-black fs-6 fw-semibold text-truncate w-125px" data-bs-toggle="tooltip"
                                                                        data-bs-placement="bottom" title="{{ $action }}"
                                                                            for="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $lastMenuItem['slug'] }}">{{
                                                                            $action }}</label>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                                <div class="col-lg-4 mb-2">
                                                                    <div class="card" style="background-color: #fab845 !important;">
                                                                        <div class="card-body">
                                                                            <div class="row px-1">
                                                                                <div class="col-lg-6">
                                                                                    <input class="form-check-input" type="radio"
                                                                                        name="view_lead_{{ $lastMenuItem['slug'] }}"
                                                                                        id="global_lead{{ $lastMenuItem['slug'] }}"
                                                                                        value="global_lead" checked
                                                                                        @if(isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['radios'][0])
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['radios'][0]['radioValue']=='global_lead'
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['radios'][0]['checked']==1)
                                                                                        checked @endif />
                                                                                    <label class="text-black fs-6 fw-semibold"
                                                                                        for="global_lead{{ $lastMenuItem['slug'] }}">Global</label>
                                                                                </div>
                                                                                <div class="col-lg-6">
                                                                                    <input class="form-check-input" type="radio"
                                                                                        name="view_lead_{{ $lastMenuItem['slug'] }}"
                                                                                        id="view_self_lead{{ $lastMenuItem['slug'] }}"
                                                                                        value="view_self_lead"
                                                                                        @if(isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0])
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0]['radioValue']=='view_self_lead'
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0]['checked']==1)
                                                                                        checked @endif />
                                                                                    <label class="text-black fs-6 fw-semibold"
                                                                                        for="view_self_lead{{ $lastMenuItem['slug'] }}">View
                                                                                        Self</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                            @if ($lastMenuItem['name'] == 'Onboarding Staff')
                                                            <div class="row">
                                                                @foreach(['List','View'] as $action)
                                                                <div class="col-lg-2 mb-2 d-flex align-items-center">
                                                                    <div class="form-check form-check-inline">
                                                                        <?php
                                                                            $menuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['name']);
                                                                            $submenuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $lastMenuItem['name']);
                                                                            $actionKey = preg_replace('/[^a-zA-Z0-9_]/', '', $action);
                                                                        ?>
                                                                        <input class="form-check-input submenu-action-checkbox"
                                                                            type="checkbox" name="actions[]"
                                                                            id="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $lastMenuItem['slug'] }}"
                                                                            value="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $lastMenuItem['slug'] }}"
                                                                            @if(isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['actions'])
                                                                            && in_array($actionKey,
                                                                            array_column($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['actions'], 'actionName'
                                                                            )) &&
                                                                            $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['actions'][array_search($actionKey,
                                                                            array_column($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['actions'], 'actionName'
                                                                            ))]['actionChecked']==1) checked @endif />
                                                                        <label class="text-black fs-6 fw-semibold text-truncate w-125px" data-bs-toggle="tooltip"
                                                                        data-bs-placement="bottom" title="{{ $action }}"
                                                                            for="{{ strtolower(str_replace(' ', '_', $action)) }}_{{ $lastMenuItem['slug'] }}">{{
                                                                            $action }}</label>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                                <div class="col-lg-4 mb-2">
                                                                    <div class="card" style="background-color: #e7a368 !important;">
                                                                        <div class="card-body">
                                                                            <div class="row px-1">
                                                                                <div class="col-lg-6">
                                                                                    <input class="form-check-input" type="radio"
                                                                                        name="view_lead_{{ $lastMenuItem['slug'] }}"
                                                                                        id="global_lead{{ $lastMenuItem['slug'] }}"
                                                                                        value="global_lead" checked
                                                                                        @if(isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0])
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0]['radioValue']=='global_lead'
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0]['checked']==1)
                                                                                        checked @endif />
                                                                                    <label class="text-black fs-6 fw-semibold"
                                                                                        for="global_lead{{ $lastMenuItem['slug'] }}">Global</label>
                                                                                </div>
                                                                                <div class="col-lg-6">
                                                                                    <input class="form-check-input" type="radio"
                                                                                        name="view_lead_{{ $lastMenuItem['slug'] }}"
                                                                                        id="view_self_lead{{ $lastMenuItem['slug'] }}"
                                                                                        value="view_self_lead"
                                                                                        @if(isset($flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0])
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0]['radioValue']=='view_self_lead'
                                                                                        &&
                                                                                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][0]['checked']==1)
                                                                                        checked @endif />
                                                                                    <label class="text-black fs-6 fw-semibold"
                                                                                        for="view_self_lead{{ $lastMenuItem['slug'] }}">View
                                                                                        Self</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                            {{-- HR Management end --}}


                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                            @endif
                                            </div>
                                        </div>
                                        </div>
                                         @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
              @endforeach
          @else
              <p>No menu items found.</p>
          @endif
        </div>
          <div class="d-flex justify-content-end align-items-center mt-4 mb-4 px-3">
            <a href="/user_management/manage_permission" class="btn btn-secondary me-3">Cancel</a>
            <a href="javascript:;" class="btn btn-primary" id="submitForm" >Update Manage Users</a>
          </div>
      </div>
    </form>
  </div>
  <div class="modal fade" id="kt_modal_confirm_create_manage_users" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-m">
      <div class="modal-content rounded">
        <div class="swal2-icon swal2-danger swal2-icon-show" style="display: flex;">
          <div class="swal2-icon-content">?</div>
        </div>
        <div class="swal2-html-container" id="swal2-html-container" style="display: block;">Are you sure you want to Update Manage Users ?
          <div class="d-block fw-bold fs-5 py-2 text-danger">
            <label id="role_confirm"></label>
            <!--<div id="permissions_confirm">Permissions: </div>-->
          </div>
        </div>
        <div class="d-flex justify-content-center align-items-center pt-8">
          <a href="javascript:;" type="button" class="btn btn-primary" id="confirmSubmit">Yes</a>&nbsp;
          <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">No</a>
        </div><br><br>
      </div>
    </div>
  </div>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<style>

 /* Customize Toastr notification */
 .toast-success {
            background-color: green;
        }
  .toast-error {
      background-color: red;
  }
  .error_msg {
      border: solid 2px red !important;
      border-color: red !important;
  }
</style>
<script>
  // Display Toastr messages
  @if(Session::has('toastr'))
  var type = "{{ Session::get('toastr')['type'] }}";
  var message = "{{ Session::get('toastr')['message'] }}";
  toastr[type](message);
  @endif
</script>
<script>
  function branch_type_func() {
    var branch_type = document.getElementById("branch_type").value;
    var branch_tbox = document.getElementById("branch_tbox");
    var franc_tbox = document.getElementById("franc_tbox");
    if (branch_type == "branch") {
      branch_tbox.style.display = "block";
      franc_tbox.style.display = "none";
    } else if (branch_type == "franc") {
      branch_tbox.style.display = "none";
      franc_tbox.style.display = "block";
    } else {
      branch_tbox.style.display = "none";
      franc_tbox.style.display = "none";
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
      $('#submitForm').on('click', function() {
          // Gather form data
          const branchType = $('#branch_type').val();
          const branch = $('#branch').val();
          const franchise = $('#franchise').val();
          const role = $('#role').val();
          const manage_branch = $('#manage_branch').val();
          const roleName = $('#role option:selected').text();
          let branchName = '';
          let franchiseName = '';

          if (branchType === 'branch') {
              branchName = $('#branch option:selected').text();
          } else if (branchType === 'franchise') {
              franchiseName = $('#franchise option:selected').text();
          }

         let permissions = [];

        $('.menu-checkbox').each(function () {

            const menuName = $(this).next('label').text();
            const menuChecked = $(this).is(':checked') ? 1 : 0;

            let submenu = [];

            const menuContainer = $(this).closest('.card-header').siblings('.collapse');

            // ---------------------------
            // LEVEL 2 → SUBMENU
            // ---------------------------
            menuContainer.find('.submenu-checkbox').each(function () {

                const submenuName = $(this).next('label').text();
                const submenuChecked = $(this).is(':checked') ? 1 : 0;

                let lastmenu = [];

                const submenuContainer = $(this).closest('.submenu-block');

                // ---------------------------
                // LEVEL 3 → LAST MENU
                const lastmenuCheckboxes = $(this)
                    .closest('.card-header')
                    .next('.collapse')
                    .find('.lastmenu-checkbox');

                lastmenuCheckboxes.each(function () {

                    const lastmenuName = $(this).next('label').text();
                    const lastmenuChecked = $(this).is(':checked') ? 1 : 0;

                    let actions = [];

                    const actionCheckboxes = $(this)
                        .closest('.card-header')
                        .next('.collapse')
                        .find('.submenu-action-checkbox');

                    actionCheckboxes.each(function () {
                        const actionName = $(this).next('label').text();
                        const actionChecked = $(this).is(':checked') ? 1 : 0;

                        actions.push({
                            actionName,
                            actionChecked
                        });
                    });

                    lastmenu.push({
                        lastmenuName,
                        lastmenuChecked,
                        actions
                    });
                });


                // PUSH SUBMENU
                submenu.push({
                    submenuName,
                    submenuChecked,
                    lastmenu
                });

            });

            // PUSH MENU
            permissions.push({
                menuName,
                menuChecked,
                submenu
            });

        });



          console.log(JSON.stringify(permissions, null, 2));



          console.log('Permissions:', permissions);
          console.log('Permissions:', JSON.stringify(permissions));

          $('#role_confirm').text('Role: ' + roleName);
          $('#branch_confirm').text(branchType === 'branch' ? 'Branch: ' + branchName : '');
          $('#franchise_confirm').text(branchType === 'franchise' ? 'Franchise: ' + franchiseName : '');
          // $('#permissions_confirm').text('Permissions: ' + JSON.stringify(permissions));

          $('#kt_modal_confirm_create_manage_users').modal('show');

          $('#confirmSubmit').off('click').on('click', function() {
              // Remove any old permissions[]
               $('#userRolePermissionForm input[name="permissions"]').remove();

                  $('#userRolePermissionForm').append(
                      $('<input>').attr({
                          type: 'hidden',
                          name: 'permissions',
                          value: JSON.stringify(permissions)
                      })
                  );


              $('#userRolePermissionForm').submit();
          });
      });

      // Handle select all
      document.getElementById('selectAllMain').addEventListener('change', (event) => {
          const isChecked = event.target.checked;
          document.querySelectorAll('.menu-checkbox').forEach(checkbox => checkbox.checked = isChecked);
          document.querySelectorAll('.submenu-checkbox').forEach(checkbox => checkbox.checked = isChecked);
          document.querySelectorAll('.lastmenu-checkbox').forEach(checkbox => checkbox.checked = isChecked);
          document.querySelectorAll('.submenu-action-checkbox').forEach(checkbox => checkbox.checked = isChecked);
          checkSelectAllStatus();  // Update the main select all checkbox status
      });

      // Handle checkbox changes
      document.querySelectorAll('.menu-checkbox, .submenu-checkbox,.lastmenu-checkbox, .submenu-action-checkbox').forEach(checkbox => {
          checkbox.addEventListener('change', checkSelectAllStatus);
      });

      function checkSelectAllStatus() {
          const allChecked = Array.from(document.querySelectorAll('.menu-checkbox')).every(checkbox => checkbox.checked) &&
                            Array.from(document.querySelectorAll('.submenu-checkbox')).every(checkbox => checkbox.checked) &&
                            Array.from(document.querySelectorAll('.lastmenu-checkbox')).every(checkbox => checkbox.checked) &&
                            Array.from(document.querySelectorAll('.submenu-action-checkbox')).every(checkbox => checkbox.checked);
          document.getElementById('selectAllMain').checked = allChecked;
      }
  });




  $(document).ready(function() {
    // Handle the "Select All" checkbox
    $('#selectAllMain').on('change', function() {
        $('.menu-checkbox').prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Handle the submenu checkboxes based on the parent menu checkbox
    $('.menu-checkbox').on('change', function() {
        let menuSlug = $(this).attr('id');
        $(`#collapseMenu${menuSlug} .submenu-checkbox`).prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Handle the submenu checkboxes
    $('.submenu-checkbox').on('change', function() {
        let submenuSlug = $(this).attr('id');
        $(`#collapseSubmenu${submenuSlug} .lastmenu-checkbox`).prop('checked', $(this).prop('checked'));
    });

    // Handle the lastmenu checkboxes
    $('.lastmenu-checkbox').on('change', function() {
        let lastmenuSlug = $(this).attr('id');
        $(`#collapseLastmenu${lastmenuSlug} .submenu-action-checkbox`).prop('checked', $(this).prop('checked'));
    });

    // Handle the submenu action checkboxes
    $('.submenu-action-checkbox').on('change', function() {
        let actionSlug = $(this).attr('id').split('_')[0];
        if ($(this).prop('checked')) {
            $(this).val(`${actionSlug}_checked`);
        } else {
            $(this).val(`${actionSlug}_unchecked`);
        }
    });
  });

    document.addEventListener('DOMContentLoaded', (event) => {
    const selectAllCheckbox = document.getElementById('selectAllMain');
    const menuCheckboxes = document.querySelectorAll('.menu-checkbox');
    const submenuCheckboxes = document.querySelectorAll('.submenu-checkbox');
    const lastmenuCheckboxes = document.querySelectorAll('.lastmenu-checkbox');
    const submenuActionCheckboxes = document.querySelectorAll('.submenu-action-checkbox');

    // Handle select all
    selectAllCheckbox.addEventListener('change', (event) => {
        const isChecked = event.target.checked;
        menuCheckboxes.forEach(checkbox => checkbox.checked = isChecked);
        submenuCheckboxes.forEach(checkbox => checkbox.checked = isChecked);
        lastmenuCheckboxes.forEach(checkbox => checkbox.checked = isChecked);
        submenuActionCheckboxes.forEach(checkbox => checkbox.checked = isChecked);
    });

    // Handle menu checkbox change
    menuCheckboxes.forEach(menuCheckbox => {
        menuCheckbox.addEventListener('change', () => {
            checkSelectAllStatus();
        });
    });

    // Handle submenu checkbox change
    submenuCheckboxes.forEach(submenuCheckbox => {
        submenuCheckbox.addEventListener('change', () => {
            checkSelectAllStatus();
        });
    });

    // Handle lastmenu checkbox change
    lastmenuCheckboxes.forEach(lastmenuCheckbox => {
        lastmenuCheckbox.addEventListener('change', () => {
            checkSelectAllStatus();
        });
    });

    // Handle submenu action checkbox change
    submenuActionCheckboxes.forEach(submenuActionCheckbox => {
        submenuActionCheckbox.addEventListener('change', () => {
            checkSelectAllStatus();
        });
    });

    // Check select all status
    function checkSelectAllStatus() {
        const allChecked = Array.from(menuCheckboxes).every(checkbox => checkbox.checked) &&
                           Array.from(submenuCheckboxes).every(checkbox => checkbox.checked) &&
                           Array.from(lastmenuCheckboxes).every(checkbox => checkbox.checked) &&
                           Array.from(submenuActionCheckboxes).every(checkbox => checkbox.checked);
        selectAllCheckbox.checked = allChecked;
    }
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
      const dashboardButtons = document.querySelectorAll('.dashboards_add');
      const dashboardLastButtons = document.querySelectorAll('.dashboards_add_last');

      dashboardButtons.forEach(button => {
          button.addEventListener('click', function(event) {
              event.preventDefault();
              const targetId = this.getAttribute('data-target');
              const targetElement = document.querySelector(targetId);

              if (targetElement.classList.contains('show')) {
                  $(targetId).collapse('hide');
              } else {
                  $(targetId).collapse('show');
              }

              // Toggle class mdi-chevron-down & mdi-chevron-up
              this.querySelector('i').classList.toggle('mdi-chevron-down');
              this.querySelector('i').classList.toggle('mdi-chevron-up');
          });
      });

      dashboardLastButtons.forEach(button => {
          button.addEventListener('click', function(event) {
              event.preventDefault();
              const targetId = this.getAttribute('data-target');
              const targetElement = document.querySelector(targetId);

              if (targetElement.classList.contains('show')) {
                  $(targetId).collapse('hide');
              } else {
                  $(targetId).collapse('show');
              }

              // Toggle class mdi-chevron-down & mdi-chevron-up
              this.querySelector('i').classList.toggle('mdi-chevron-down');
              this.querySelector('i').classList.toggle('mdi-chevron-up');
          });
      });
  });
</script>

<script>
  $(document).ready(function() {
      // Hide or show the div based on the selected radio button
      $('input[name="manage_branch"]').on('change', function() {
          if ($('#view_mm').is(':checked')) {
              $('#accessHeadDiv').show(); // Show the div when "Multi Manage" is selected
          } else {
              $('#accessHeadDiv').hide(); // Hide the div when "Single Manage" is selected
          }
      });

      // Trigger change event on page load to check the initial state
      $('input[name="manage_branch"]:checked').trigger('change');
  });
</script>
<script>
        $(document).ready(function() {

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

             $('#entity_name').on('change', function() {
                var entity_id = $(this).val();
                var roleDropdown = $('#business_user_role');

                roleDropdown.empty().append('<option value="">Select Role</option>');

                if (entity_id) {
                    // Fetch and populate states based on selected country
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
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching Role:', error);
                        }
                    });

                }


            });

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
@endsection
