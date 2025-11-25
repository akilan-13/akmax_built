<?php

namespace App\Http\Controllers\control_panel\user_management;

use App\Http\Controllers\Controller;
use App\Models\UserRolePermissionModel;
use App\Models\UserRoleModel;
use App\Models\CompanyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class UserRolePermission extends Controller
{

   public function index()
  {

     $userpermission = UserRolePermissionModel::where('egc_user_role_permission.status', '!=', 2)
      ->join('egc_user_role', 'egc_user_role_permission.role_id', '=', 'egc_user_role.sno')
      ->select(
        'egc_user_role_permission.sno',
        'egc_user_role.sno as role_id',
        'egc_user_role.role_name',
        'egc_user_role_permission.status'
      )
      ->get();

    $path = resource_path('menu/verticalMenu.json');

    // Check if the menu file exists
    if (!File::exists($path)) {
      abort(500, 'Menu file not found.');
    }

    // Read the file content
    $json = File::get($path);

    // Decode JSON to array
    $menu = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      abort(500, 'Error decoding menu JSON file: ' . json_last_error_msg());
    }

    // Fetch the role data
    $roles = UserRolePermissionModel::where('egc_user_role_permission.status', 0)
      ->join('egc_user_role', 'egc_user_role_permission.role_id', '=', 'egc_user_role.sno')
      ->select(
        'egc_user_role_permission.*',
        'egc_user_role.role_name'
      )
      ->get();

    $rolesWithPermissions = [];

    foreach ($roles as $role) {
      $rolePermissions = json_decode($role->permissions, true);
      if (is_string($rolePermissions)) {
        $rolePermissions = json_decode($rolePermissions, true);
      }
      $rolePermissions = is_array($rolePermissions) ? $rolePermissions : [];

      $flattenedPermissions = [];

      foreach ($rolePermissions as $menuItem) {
        $menuName = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['menuName']);
        $menuChecked = $menuItem['menuChecked'] ?? 0;

        if (isset($menuItem['submenu'])) {
          foreach ($menuItem['submenu'] as $submenuItem) {
            $submenuName = preg_replace('/[^a-zA-Z0-9_]/', '', $submenuItem['submenuName']);
            $submenuChecked = $submenuItem['submenuChecked'] ?? 0;

            if (isset($submenuItem['actions'])) {
              foreach ($submenuItem['actions'] as $actionItem) {
                $actionName = preg_replace('/[^a-zA-Z0-9_]/', '', $actionItem['actionName']);
                $actionChecked = $actionItem['actionChecked'];

                $flattenedPermissions[$menuName]['submenu'][$submenuName]['actions'][] = [
                  'actionName' => $actionName,
                  'actionChecked' => $actionChecked
                ];
              }
            }

            $flattenedPermissions[$menuName]['submenu'][$submenuName]['submenuChecked'] = $submenuChecked;
          }
        }

        $flattenedPermissions[$menuName]['menuChecked'] = $menuChecked;
      }

      $rolesWithPermissions[] = [
        'role' => $role,
        'permissions' => $flattenedPermissions
      ];
    }
    // return view('content.user_management.user_role.list', compact('userRole', 'pageConfigs'));
    return view('content.control_panel.user_management.user_role_permission.user_role_list',[
      'menu'=>$menu,
      'rolesWithPermissions'=>$rolesWithPermissions,
      'userpermission'=>$userpermission,
    ]);
  }

  public function indexManangeUser()
  {
    // $userpermission = UserRolePermissionModel::where('eibs_user_role_permission.status', '!=', 2)
    //   ->join('eibs_user_role', 'eibs_user_role_permission.role_id', '=', 'eibs_user_role.sno')
    //   ->select(
    //     'eibs_user_role_permission.sno',
    //     'eibs_user_role.sno as role_id',
    //     'eibs_user_role.role_name',
    //     'eibs_user_role_permission.status'
    //   )
    //   ->get();
    return view('content.control_panel.user_management.manage_users.manage_users_list');
  }

   public function ExportExcel()
  {
    return Excel::download(new UserRoleExport, 'user-role-export.xlsx'); // Adjust filename and extension as needed
  }
  public function List()
  {
    $user = UserRoleModel::where('status', 0)->orderBy('sno', 'desc')->get();

    return  response([
      'status'    => 200,
      'message'   => null,
      'error_msg' => null,
      'data'      => $user
    ], 200);
  }

   


  public function Add(Request $request)
  {
    // return $request;
    $validator = Validator::make($request->all(), [
      'manage_branch' => 'required',
      'permissions' => 'required',
      'permissions.*.menu' => 'required|string',
      'permissions.*.submenu' => 'nullable',
      'permissions.*.submenu.*.name' => 'nullable|string',
      'permissions.*.submenu.*.actions' => 'nullable',
      'permissions.*.submenu.*.actions.*' => 'nullable|string',
      'permissions.*.actions' => 'nullable',
      'permissions.*.actions.*' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors()->all();
      $firstError = $validator->errors()->first();

      session()->flash('toastr', [
        'type' => 'error',
        'message' => $firstError . ' | Detailed errors: ' . implode(', ', $errors)
      ]);

      return redirect()->back()->withInput();
    }

    try {
      $permissionsJson = json_encode($request->permissions);
      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Invalid JSON data');
      }
      
      if( $request->company == 1){
        $role_id = $request->role;
      }else{
         $role_id = $request->business_role;
      }
      $add_user_role_permission = new UserRolePermissionModel();
      $add_user_role_permission->role_id = $request->role;
      $add_user_role_permission->manage_branch = $request->manage_branch;
      if ($add_user_role_permission->manage_branch == 2) {
        $add_user_role_permission->access_head      = $request->access_head;
      } else {
        $add_user_role_permission->access_head      = null;
      }
      $add_user_role_permission->permissions = $permissionsJson;
      $add_user_role_permission->created_by = $request->user()->user_id;
      $add_user_role_permission->updated_by = $request->user()->user_id;

      $add_user_role_permission->save();

      session()->flash('toastr', [
        'type' => 'success',
        'message' => 'User Role Permissions added Successfully!'
      ]);
    } catch (\Exception $e) {
      session()->flash('toastr', [
        'type' => 'error',
        'message' => 'Could not add the User Role Permissions! ' . $e->getMessage()
      ]);
    }

    return redirect('user_management/manage_permission');
  }

  public function users_add (Request $request)
  {

     // Path to the JSON file
      $path = resource_path('menu/verticalMenu.json');
       $management_userRole = UserRoleModel::where('status', 0)->where('company_type',1)->orderBy('sno', 'desc')->get();
       $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();

      // Read the file content
      $json = File::get($path);

      // Decode JSON to array
       $menu = json_decode($json, true);

        // Convert nested submenu to lastmenu
        foreach ($menu['menu'] as &$menuItem) {

            if (isset($menuItem['submenu'])) {
                foreach ($menuItem['submenu'] as &$subItem) {

                    if (isset($subItem['submenu'])) {

                        // Rename submenu → lastmenu
                        $subItem['lastmenu'] = $subItem['submenu'];
                        unset($subItem['submenu']);


                    }
                }
            }
        }

    return view('content.control_panel.user_management.user_role_permission.add_role',[
      'menu' => $menu,
      'management_userRole' => $management_userRole,
      'company_list' => $company_list,
    ]);
  }

  // public function users_edit($id)
  // {


  //   $helper = new \App\Helpers\Helpers();
  //   $decryptedValue = $helper->encrypt_decrypt($id, 'decrypt');

  //    $management_userRole = UserRoleModel::where('status', 0)->where('company_type',1)->orderBy('sno', 'desc')->get();
  //      $company_list = CompanyModel::where('status', 0)->orderBy('sno', 'ASC')->get();
  //   // Check if decryption failed
  //   if ($decryptedValue === false) {
  //     return redirect()->back()->with('error', 'Invalid Entry');
  //   }
  //   $id = $decryptedValue;
  //   // $id = Crypt::decrypt($id);
  //   $path = resource_path('menu/verticalMenu.json');

  //   // Check if the menu file exists
  //   if (!File::exists($path)) {
  //     abort(500, 'Menu file not found.');
  //   }

  //   // Read the file content
  //   $json = File::get($path);

  //   // Decode JSON to array
  //   $menu = json_decode($json, true);
  //   if (json_last_error() !== JSON_ERROR_NONE) {
  //     abort(500, 'Error decoding menu JSON file: ' . json_last_error_msg());
  //   }

  //   // Fetch the role data
  //   $role = UserRolePermissionModel::where('sno', $id)->first();
  //   if (!$role) {
  //     abort(404, 'Role not found');
  //   }

  //   // Decode the JSON permissions string into an array
  //   $rolePermissions = json_decode($role->permissions, true);

  //   // Check if rolePermissions is a JSON string
  //   if (is_string($rolePermissions)) {
  //     $rolePermissions = json_decode($rolePermissions, true);
  //   }
  //   // Ensure rolePermissions is an array
  //   $rolePermissions = is_array($rolePermissions) ? $rolePermissions : [];

  //   // Initialize flattenedPermissions array
  //   $flattenedPermissions = [];

  //   // Process role permissions
  //   foreach ($rolePermissions as $menuItem) {
  //     $menuName = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['menuName']);
  //     $menuChecked = isset($menuItem['menuChecked']) ? $menuItem['menuChecked'] : 0;

  //     if (isset($menuItem['submenu'])) {
  //       foreach ($menuItem['submenu'] as $submenuItem) {
  //         $submenuName = preg_replace('/[^a-zA-Z0-9_]/', '', $submenuItem['submenuName']);
  //         $submenuChecked = isset($submenuItem['submenuChecked']) ? $submenuItem['submenuChecked'] : 0;

  //         if (isset($submenuItem['actions'])) {
  //           foreach ($submenuItem['actions'] as $actionItem) {
  //             $actionName = preg_replace('/[^a-zA-Z0-9_]/', '', $actionItem['actionName']);
  //             $actionChecked = $actionItem['actionChecked'];

  //             $flattenedPermissions[$menuName]['submenu'][$submenuName]['actions'][] = [
  //               'actionName' => $actionName,
  //               'actionChecked' => $actionChecked
  //             ];
  //           }
  //         }

  //         if (isset($submenuItem['radios'])) {
  //           foreach ($submenuItem['radios'] as $radioItem) {
  //             $radioName = preg_replace('/[^a-zA-Z0-9_]/', '', $radioItem['radioName']);
  //             $radioValue = $radioItem['radioValue'];
  //             $checked = $radioItem['checked'];

  //             $flattenedPermissions[$menuName]['submenu'][$submenuName]['radios'][] = [
  //               'radioName' => $radioName,
  //               'radioValue' => $radioValue,
  //               'checked' => $checked
  //             ];
  //           }
  //         }

  //         $flattenedPermissions[$menuName]['submenu'][$submenuName]['submenuChecked'] = $submenuChecked;
  //       }
  //     }

  //     if (isset($menuItem['actions'])) {
  //       foreach ($menuItem['actions'] as $actionItem) {
  //         $actionName = preg_replace('/[^a-zA-Z0-9_]/', '', $actionItem['actionName']);
  //         $actionChecked = $actionItem['actionChecked'];

  //         $flattenedPermissions[$menuName]['actions'][] = [
  //           'actionName' => $actionName,
  //           'actionChecked' => $actionChecked
  //         ];
  //       }
  //     }

  //     if (isset($menuItem['radios'])) {
  //       foreach ($menuItem['radios'] as $radioItem) {
  //         $radioName = preg_replace('/[^a-zA-Z0-9_]/', '', $radioItem['radioName']);
  //         $radioValue = $radioItem['radioValue'];
  //         $checked = $radioItem['checked'];

  //         $flattenedPermissions[$menuName]['radios'][] = [
  //           'radioName' => $radioName,
  //           'radioValue' => $radioValue,
  //           'checked' => $checked
  //         ];
  //       }
  //     }

  //     $flattenedPermissions[$menuName]['menuChecked'] = $menuChecked;
  //   }
  //   // dd($flattenedPermissions['SalesManagement']['submenu']); // or dd($submenuItem);
  //   // dd($flattenedPermissions);

  //   // Pass the menu and permissions data to the view
  //   return view('content.control_panel.user_management.user_role_permission.edit_role',[
  //     'menu' => $menu,
  //     'rolePermissions' => $rolePermissions,
  //     'flattenedPermissions' => $flattenedPermissions,
  //     'role' => $role,
  //     'management_userRole' => $management_userRole,
  //     'company_list' => $company_list,
  //   ]);
  // }

  public function users_edit($id)
{
    $helper = new \App\Helpers\Helpers();
    $decryptedValue = $helper->encrypt_decrypt($id, 'decrypt');

    if ($decryptedValue === false) {
        return redirect()->back()->with('error', 'Invalid Entry');
    }

    $id = $decryptedValue;

    // Load Management Roles & Company list
    $management_userRole = UserRoleModel::where('status', 0)
        ->where('company_type', 1)
        ->orderBy('sno', 'desc')
        ->get();

    $company_list = CompanyModel::where('status', 0)
        ->orderBy('sno', 'ASC')
        ->get();

    // Load Menu JSON
    $path = resource_path('menu/verticalMenu.json');

    if (!File::exists($path)) {
        abort(500, 'Menu file not found.');
    }

    $json = File::get($path);
    $menu = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        abort(500, 'Error decoding menu JSON file: ' . json_last_error_msg());
    }

    /**
     * ---------------------------------------------------------
     * NEW LOGIC — same as users_add():
     * Convert submenu → lastmenu when nested
     * ---------------------------------------------------------
     */
    foreach ($menu['menu'] as &$menuItem) {
        if (isset($menuItem['submenu'])) {
            foreach ($menuItem['submenu'] as &$subItem) {

                if (isset($subItem['submenu'])) {
                    // Rename submenu → lastmenu
                    $subItem['lastmenu'] = $subItem['submenu'];
                    unset($subItem['submenu']);
                }
            }
        }
    }

    // Fetch role data
    $role = UserRolePermissionModel::where('sno', $id)->first();
    if (!$role) {
        abort(404, 'Role not found');
    }

    // Decode role permissions
    $rolePermissions = json_decode($role->permissions, true);
    if (is_string($rolePermissions)) {
        $rolePermissions = json_decode($rolePermissions, true);
    }
    $rolePermissions = is_array($rolePermissions) ? $rolePermissions : [];

    /**
     * ---------------------------------------------------------
     * Flatten permissions so Blade can easily compare
     * (Same logic as before)
     * ---------------------------------------------------------
     */
    $flattenedPermissions = [];

    foreach ($rolePermissions as $menuItem) {

        $menuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $menuItem['menuName']);
        $flattenedPermissions[$menuKey]['menuChecked'] = $menuItem['menuChecked'] ?? 0;

        // -----------------------------
        // SUBMENU LEVEL
        // -----------------------------
        if (!empty($menuItem['submenu'])) {
            foreach ($menuItem['submenu'] as $submenuItem) {

                $submenuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $submenuItem['submenuName']);
                $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['submenuChecked'] =
                    $submenuItem['submenuChecked'] ?? 0;

                // -----------------------------
                // LASTMENU LEVEL
                // -----------------------------
                if (!empty($submenuItem['lastmenu'])) {
                    foreach ($submenuItem['lastmenu'] as $lastmenuItem) {

                        $lastmenuKey = preg_replace('/[^a-zA-Z0-9_]/', '', $lastmenuItem['lastmenuName']);

                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['lastmenuChecked'] =
                            $lastmenuItem['lastmenuChecked'] ?? 0;

                        // -----------------------------
                        // ACTIONS UNDER LASTMENU
                        // -----------------------------
                        if (!empty($lastmenuItem['actions'])) {

                            foreach ($lastmenuItem['actions'] as $actionItem) {

                                $actionKey = preg_replace('/[^a-zA-Z0-9_]/', '', $actionItem['actionName']);

                                $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['lastmenu'][$lastmenuKey]['actions'][] = [
                                    'actionName' => $actionKey,
                                    'actionChecked' => $actionItem['actionChecked'] ?? 0
                                ];
                            }
                        }
                    }
                }

                // -----------------------------
                // SUBMENU DIRECT ACTIONS (if exist)
                // -----------------------------
                if (!empty($submenuItem['actions'])) {

                    foreach ($submenuItem['actions'] as $actionItem) {

                        $actionKey = preg_replace('/[^a-zA-Z0-9_]/', '', $actionItem['actionName']);

                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['actions'][] = [
                            'actionName' => $actionKey,
                            'actionChecked' => $actionItem['actionChecked'] ?? 0
                        ];
                    }
                }

                // -----------------------------
                // SUBMENU RADIOS
                // -----------------------------
                if (!empty($submenuItem['radios'])) {

                    foreach ($submenuItem['radios'] as $radioItem) {

                        $radioKey = preg_replace('/[^a-zA-Z0-9_]/', '', $radioItem['radioName']);

                        $flattenedPermissions[$menuKey]['submenu'][$submenuKey]['radios'][] = [
                            'radioName' => $radioKey,
                            'radioValue' => $radioItem['radioValue'],
                            'checked' => $radioItem['checked']
                        ];
                    }
                }
            }
        }

        // -----------------------------
        // MENU LEVEL ACTIONS
        // -----------------------------
        if (!empty($menuItem['actions'])) {
            foreach ($menuItem['actions'] as $actionItem) {
                $actionKey = preg_replace('/[^a-zA-Z0-9_]/', '', $actionItem['actionName']);
                $flattenedPermissions[$menuKey]['actions'][] = [
                    'actionName' => $actionKey,
                    'actionChecked' => $actionItem['actionChecked'] ?? 0
                ];
            }
        }

        // -----------------------------
        // MENU LEVEL RADIOS
        // -----------------------------
        if (!empty($menuItem['radios'])) {
            foreach ($menuItem['radios'] as $radioItem) {

                $radioKey = preg_replace('/[^a-zA-Z0-9_]/', '', $radioItem['radioName']);
                $flattenedPermissions[$menuKey]['radios'][] = [
                    'radioName' => $radioKey,
                    'radioValue' => $radioItem['radioValue'],
                    'checked' => $radioItem['checked']
                ];
            }
        }
    }


    return view('content.control_panel.user_management.user_role_permission.edit_role', [
        'menu' => $menu,
        'rolePermissions' => $rolePermissions,
        'flattenedPermissions' => $flattenedPermissions,
        'role' => $role,
        'management_userRole' => $management_userRole,
        'company_list' => $company_list,
    ]);
}


  public function edit()
  {
    // $role = UserRoleModel::where('sno', $id)->first();

    // return  response([
    //   'status'    => 200,
    //   'message'   => null,
    //   'error_msg' => null,
    //   'data'      => $role
    // ], 200);

    return view('content.control_panel.user_management.user_role_permission.edit_role');
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'manage_branch' => 'required',
      'permissions' => 'required',
      'permissions.*.menu' => 'required|string',
      'permissions.*.submenu' => 'nullable',
      'permissions.*.submenu.*.name' => 'nullable|string',
      'permissions.*.submenu.*.actions' => 'nullable',
      'permissions.*.submenu.*.actions.*' => 'nullable|string',
      'permissions.*.actions' => 'nullable',
      'permissions.*.actions.*' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors()->all();
      $firstError = $validator->errors()->first();

      session()->flash('toastr', [
        'type' => 'error',
        'message' => $firstError . ' | Detailed errors: ' . implode(', ', $errors)
      ]);
    }

    $rolePermission = UserRolePermissionModel::where('sno', $id)->first();
    try {
      $permissionsJson = json_encode($request->permissions);
      if (json_last_error() !== JSON_ERROR_NONE) {
      }


      $rolePermission->role_id          = $request->role;
      $rolePermission->manage_branch    = $request->manage_branch;
      if ($rolePermission->manage_branch == 2) {
        $rolePermission->access_head      = $request->access_head;
      } else {
        $rolePermission->access_head      = null;
      }

      $rolePermission->permissions      = $permissionsJson;
      $rolePermission->updated_by       = $request->user()->user_id;
      $rolePermission->update();


      session()->flash('toastr', [
        'type' => 'success',
        'message' => 'User Role Permissions Updated Successfully!'
      ]);
    } catch (\Exception $e) {

      session()->flash('toastr', [
        'type' => 'error',
        'message' => 'Could not Updated the User Role Permissions!'
      ]);
    }

    return redirect('user_management/manage_permission');
  }

  public function view()
  {
    return view('content.control_panel.user_management.user_role_permission.view_role');
  }

  public function Delete($id)
  {
    $upd_CourseCategoryModel =  UserRoleModel::where('sno', $id)->first();
    $upd_CourseCategoryModel->status  = 2;
    $upd_CourseCategoryModel->Update();


    return response([
      'status'    => 200,
      'message'   => 'Successfully Deleted!',
      'error_msg' => null,
      'data'      => null,
    ], 200);
  }

  public function Status($id, Request $request)
  {

    $upd_CourseCategoryModel =  UserRoleModel::where('sno', $id)->first();

    $upd_CourseCategoryModel->status = $request->input('status', 0);
    $upd_CourseCategoryModel->update();


    return response([
      'status'    => 200,
      'message'   => 'Successfully Status Updated!',
      'error_msg' => null,
      'data'      => null,
    ], 200);
  }
}