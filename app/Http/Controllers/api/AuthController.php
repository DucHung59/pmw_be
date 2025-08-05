<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Hash;

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

        // Check password complexity
        $passwordComplexity = $this->getPasswordComplexityScore($request->password);

        // Create a new user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'password_complex' => $passwordComplexity,
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

    public function changePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không đúng',
                'success' => false,
            ], 403);
        }

        User::where('id', Auth::id())->update([
            'password' => bcrypt($request->new_password),
            'password_complex' => $this->getPasswordComplexityScore($request->new_password),
        ]);

        return response()->json([
            'message' => 'Đổi mật khẩu thành công',
            'success' => true,
        ]);
    }

    public function getUserInfo(Request $request)
    {
        $user = $request->user_id;
        
        $userInfo = User::find($user);

        if (!$userInfo) {
            return response()->json([
                'message' => 'User not found',
                'status' => 'not_found',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'user' => $userInfo,
            'success' => true,
            'message' => 'User information retrieved successfully',
        ]);
    }

    private function getPasswordComplexityScore($password)
    {
        $score = 0;

        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
        if (strlen($password) >= 12) $score++;

        return $score;
    }
}
