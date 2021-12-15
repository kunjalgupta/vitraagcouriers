<?php

namespace App\Http\Controllers;

use App\Model\User;
use Mail;
use App\Mail\UserCreated;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserAuthRequest;
use App\Model\BusinessDetail;

class UserAuthController extends Controller
{
    public function createUser(UserAuthRequest $request)
    {
        $user = User::create($request->only(
            'role',
            'name',
            'email',
            'password',
            'mobile',
            'address',
            'pincode',
            'code_number',
            'state',
            'state_name',
            'office_address_id',
            'resident_address_id',
            'business_dtls_id',
            'father_name'
        ));
        //Mail::send(new UserCreated($user));
        return $user;
    }

    public function loginUser(UserAuthRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return [
                'user' => $this->me(),
                'tokenDetails' => $this->respondWithToken($token)
            ];
        }

        return response()->json(['error' => 'UnAuthenticated'], 401);
    }

    public function logoutUser()
    {
        $this->guard()->logout();
        return ['message' => 'Successfully logged out'];
    }

    public function refreshUser()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function me()
    {
        $user = $this->guard()->user();
        $business = BusinessDetail::find($user->business_dtls_id);
        $firmName = !is_null($business) ? $business['firm_name'] : '';
        return [
            'user_id' => $user->id,
            'role' => $user->role,
            'firm_name' => $firmName,
            'email' => $user->email,
            'is_cargo_authorized' => $user->is_cargo_authorized,
            'state' => $user->state,
            'code_number' => $user->code_number,
            'is_admin' => $user->is_admin
        ];
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL()
        ];
    }

    public function guard()
    {
        return Auth::guard();
    }

    public function changePassword(UserAuthRequest $request)
    {
        $user = auth()->user();
        $user->password = $request->password;
        $user->update();
        return [
            'message' => 'Password Changed Successfully',
            'status' => 1,
            'data' => $user->only(['role', 'name', 'email', 'is_cargo_authorized'])
        ];
    }
}
