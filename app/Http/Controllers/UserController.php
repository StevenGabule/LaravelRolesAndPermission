<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
  public function index(Request $request)
  {
    $data = User::orderBy('id', 'desc')->paginate(5);
    return response()->json(['data' => $data]);
  }

  public function store(StoreRequest $request)
  {
    $input = $request->all();
    $input['password'] = Hash::make($input['password']);

    $user = User::create($input);
    $user->assignRole($request->input('roles'));

    return response()->json(['success' => true], 201);
  }

  public function show(int $id)
  {
    $user = User::findOrFail($id);
    return response()->json(['user' => $user]);
  }

  public function edit(int $id)
  {
    $user = User::find($id);
    $roles = Role::pluck('name', 'name')->all();
    $userRole = $user->roles->pluck('name', 'name')->all();

    return response()->json([
      'user' => $user,
      'roles' => $roles,
      'userRole' => $userRole
    ]);
  }

  public function update(Request $request, int $id)
  {
    $this->validate($request, [
      'name' => 'required',
      'email' => 'required|email|unique:users,email,' . $id,
      'password' => 'same:confirm-password',
      'roles' => 'required',
    ]);

    $input = $request->all();
    if (!empty($input['password'])) {
      $input['password'] = Hash::make($input['password']);
    } else {
      $input = Arr::except($input, array('password'));
    }

    $user = User::find($id);
    $user->update($input);

    DB::table('model_has_roles')->where('model_id', $id)->delete();

    $user->assignRole($request->input('roles'));

    return response()->json(['success' => true], 201);
  }

  public function destroy(int $id)
  {
    User::find($id)->delete();
    return response()->json([], 204);
  }


}
