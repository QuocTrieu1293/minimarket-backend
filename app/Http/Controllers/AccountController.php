<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function login(Request $request) {
        //email, password
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ],
            [
                'email' => 'Sai định dạng email',
                'required' => 'Bắt buộc nhập'
            ]);
        
            $credentials = $request->only('email', 'password') + ['role' => 'customer'];
            // dd($credentials);
        
            if (Auth::attempt($credentials)) {
                // Authentication passed...
                $user = Auth::user();
                // dd($user);
                return new UserResource($user);
            } else {
                // Authentication failed...
                return response()->json(['error' => 'Sai tên đăng nhập hoặc mật khẩu'], 401);
            }
        }catch(Exception $e) {
            $statusCode = ($e instanceof ValidationException)? 422 : 404;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    public function register(Request $request) {
        //email, password, fullname
        try {
            $request->validate([
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'fullname' => 'required'
            ],
            [
                'email' => 'Sai định dạng email',
                'required' => 'Bắt buộc nhập',
                'unique' => 'Email đã tồn tại'
            ]);
            $user = User::create([
                'name' => $request->fullname,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            return new UserResource($user);
        }catch(Exception $e) {
            $statusCode = ($e instanceof ValidationException)? 422 : 404;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    public function getInfo(Request $request, $id) {
        try {
            $user = User::role('customer')->findOrFail($id);
            return new UserResource($user);
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function updateInfo(Request $request, $id) {
        //email, fullname, phone, address
        try {
            $user = User::role('customer')->findOrFail($id);

            $request->validate([
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'fullname' => 'required',
                'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15'
            ],
            [
                'email' => 'Sai định dạng email',
                'required' => 'Bắt buộc nhập',
                'unique' => 'Email đã tồn tại',
                'phone.regex' => 'Sai định dạng sđt'
            ]);

            $user->update([
                'email' => $request->email,
                'name' => $request->fullname,
                'phone' => $request->phone,
                'address' => $request->address
            ]);

            return new UserResource($user);
        }catch(Exception $e) {
            $statusCode = ($e instanceof ValidationException)? 422 : 404;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    
}
