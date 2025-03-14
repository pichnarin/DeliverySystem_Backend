<?php


namespace App\Http\Controllers;
use App\Models\RoleChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
class RoleChangeController extends Controller
{

    public function requestRoleChange(Request $request)
{
    $user = JWTAuth::parseToken()->authenticate();

    // Check if the user is authenticated
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    // Check if the user has a role (in case the role is not set)
    if (!$user->role) {
        return response()->json(['error' => 'User role not found'], 400);
    }

    // Check if the user already has the 'driver' role
    if ($user->role->name === 'driver') {
        return response()->json(['message' => 'You are already a driver'], 403);
    }

    // Create a new role change request
    $roleChangeRequest = RoleChangeRequest::create([
        'user_id' => $user->id,
        'requested_role' => $request->requested_role,
        'status' => 'pending'
    ]);

    return response()->json(['message' => 'Role change request submitted successfully'], 201);
}



    public function viewRoleChangeRequests()
    {
        $requests = RoleChangeRequest::with('user')->where('status', 'pending')->get();
        return response()->json($requests);
    }

    public function approveRoleChange(Request $request, $id)
    {
        $roleChangeRequest = RoleChangeRequest::findOrFail($id);
        $user = $roleChangeRequest->user;

        // Approve the request and assign the driver role
        $driverRole = Role::where('name', 'driver')->first();
        $user->update([
            'role_id' => $driverRole->id
        ]);

        // Update the request status
        $roleChangeRequest->update(['status' => 'approved']);

        return response()->json(['message' => 'Role change approved.']);
    }

    public function denyRoleChange(Request $request, $id)
    {
        $roleChangeRequest = RoleChangeRequest::findOrFail($id);

        // Deny the request
        $roleChangeRequest->update(['status' => 'denied']);

        return response()->json(['message' => 'Role change denied.']);
    }

}