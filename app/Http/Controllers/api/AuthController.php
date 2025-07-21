<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

class AuthController extends Controller
{
    //
    public function signin(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user
        if (Auth::attempt($request->only('email', 'password'))) {
            // Generate a new token for the user
            $user = User::where('email', $request->email)->first();
            $workspaceMember = WorkspaceMember::where('user_id', $user->id)->first();
            if ($workspaceMember) {
                $workspace = Workspace::find($workspaceMember->workspace_id);
            } else {
                $workspace = null;
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
                'workspace' => $workspace,
                'role' => $workspaceMember->role ?? null,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function signup(Request $request)
    {
        // Validate the request
        $request->validate([
            'username' => 'required|string|max:255|not_in:admin',
            'email' => 'required|email|unique:tblUsers,email',
            'password' => 'required|string|min:8',
        ]);

        // Create a new user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'verify' => 0,
        ]);

        // Generate a new token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user,
        ])->setStatusCode(201);
    }

    public function signout(Request $request)
    {
        // Revoke the token that was used to authenticate the user
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
