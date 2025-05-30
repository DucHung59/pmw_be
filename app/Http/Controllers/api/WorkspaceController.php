<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceInvite;
use App\Mail\WorkspaceInviteMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class WorkspaceController extends Controller
{
    //
    function create(Request $request)
    {
        // Validate the request
        $request->validate([
            'workspaceName' => 'required|string|max:255',
        ]);

        // Create a new workspace
        $workspace = Workspace::create([
            'workspace_name' => $request->workspaceName
        ]);

        $workspaceMember = WorkspaceMember::create([
            'user_id' => Auth::id(),
            'workspace_id' => $workspace->id,
            'role' => 'admin'
        ]);

        return response()->json([
            'message' => 'Workspace created successfully',
            'workspace' => $workspace,
            'workspace_member' => $workspaceMember
        ]);
    }

    function getWorkspaceByUser(Request $request)
    {
        $workspaceMember = WorkspaceMember::where('user_id', Auth::id())->first();
        if ($workspaceMember) {
            $workspace = Workspace::find($workspaceMember->workspace_id);
        } else {
            $workspace = null;
        }

        return response()->json([
            'message' => 'Workspaces retrieved successfully',
            'workspace' => $workspace,
            'role' => $workspaceMember ? $workspaceMember->role : null
        ]);
    }

    function getAllMembers(Request $request)
    {
        // Validate the request
        $request->validate([
            'workspaceId' => 'required|integer|exists:tblWorkspaces,id',
        ]);

        // Get all members of the workspace
        $members = WorkspaceMember::where('workspace_id', $request->workspaceId)
            ->with(['user:id,username,email'])
            ->get(['user_id', 'role', 'created_at']);

        return response()->json([
            'message' => 'Workspace members retrieved successfully',
            'members' => $members
        ]);
    }

    function sendInvite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'workspaceId' => 'required|exists:tblWorkspaces,id',
            'role' => 'required|string',
        ]);

        $existingInvite = WorkspaceInvite::where('email', $request->email)
            ->first();

        if ($existingInvite) {
            return response()->json([
                'message' => 'An invite has already been sent to this email',
                'invite' => $existingInvite,
            ], 409);
        }

        $token = Str::uuid();

        $invite = WorkspaceInvite::create([
            'email' => $request->email,
            'workspace_id' => $request->workspaceId,
            'role' => $request->role,
            'token' => $token,
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(3),
        ]);

        $inviteUrl = env('FRONTEND_URL') . '/invite/' . $token;

        Mail::to($request->email)->send(new WorkspaceInviteMail($inviteUrl));

        return response()->json([
            'message' => 'Invite sent successfully',
            'invite' => $invite,
        ]);
    }


    public function acceptInvite(Request $request)
    {
        $invite = WorkspaceInvite::where('token', $request->token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'You must be logged in to accept the invitation.',
                'status' => 'unauthenticated',
                'invite_token' => $invite->token,
                'invite_email' => $invite->email,
            ], 401);
        }

        // Check trùng email với lời mời
        if ($user->email !== $invite->email) {
            abort(403, 'This invitation was not sent to your email.');
        }

        // Check nếu user đã là thành viên
        $alreadyMember = WorkspaceMember::where('workspace_id', $invite->workspace_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$alreadyMember) {
            $workspaceMember = WorkspaceMember::create([
                'user_id' => $user->id,
                'workspace_id' => $invite->workspace_id,
                'role' => $invite->role,
            ]);
        }

        $workspace = Workspace::find($invite->workspace_id);
        if (!$workspace) {
            return response()->json([
                'message' => 'Workspace not found',
                'status' => 'not_found',
            ], 404);
        }

        $invite->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Invite accepted successfully',
            'invite' => $invite,
            'workspace' => $workspace,
            'role' => $workspaceMember->role,
        ]);
    }

    public function getAllInvites(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required',
            'search_status' => 'nullable|string|max:255',
        ]);

        $invites = WorkspaceInvite::where('workspace_id', $request->workspace_id)
            ->when($request->search_status, function ($query) use ($request) {
                return $query->where('status', $request->search_status);
            })
            ->with(['inviter:id,username,email'])
            ->get();

        return response()->json([
            'message' => 'Invites retrieved successfully',
            'invites' => $invites,
        ]);
    }
}
