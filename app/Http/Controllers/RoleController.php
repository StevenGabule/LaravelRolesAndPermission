<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
    $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:role-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $roles = Role::orderBy('id', 'desc')->paginate(5);
    return response()->json(['roles' => $roles]);
  }

  public function store(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|unique:roles,name',
      'permission' => 'required'
    ]);

    $role = Role::create(['name' => $request->input('name')]);
    $role->syncPermissions($request->input('permission'));

    return response()->json(['success' => true], 201);
  }

  public function show(int $id)
  {
    $role = Role::find($id);
    $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
      ->where("role_has_permissions.role_id", $id)
      ->get();

    return response()->json(['role' => $role, 'role_permissions' => $rolePermissions]);
  }

  public function edit(int $id)
  {
    $role = Role::find($id);
    $permission = Permission::get();
    $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
      ->pluck("role_has_permissions.permission_id", "role_has_permissions.permission_id")
      ->all();

    return response()->json([
      'role' => $role,
      'permission' => $permission,
      "role_permissions" => $rolePermissions
    ]);
  }

  public function update(Request $request, int $id)
  {
    $this->validate($request, ['name' => 'required', 'permission' => 'required']);

    $role = Role::find($id);
    $role->name = $request->input('name');
    $role->save();

    $role->syncPermissions($request->input('permission'));

    return response()->json(['success' => true],201);
  }

  public function destroy(int $id)
  {
    DB::table('roles')->where('id', $id)->delete();
    return response()->json([], 204);
  }
}
