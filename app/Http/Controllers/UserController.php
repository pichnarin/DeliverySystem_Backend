<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // for logging errors
use Illuminate\Support\Facades\DB; // for database queries

class UserController extends Controller
{
    // Retrieve all users
    public function index()
    {
        try {
            $users = User::all();
            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users. Please try again later.'
            ], 500);
        }
    }

    // Retrieve a user by ID
    public function show($id)
    {
        try {
            $user = User::findOrFail($id); // Automatically throws 404 if not found

            return response()->json([
                'status' => 'success',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching user with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch user. Please try again later.'
            ], 500);
        }
    }

    //get user by role name
    public function getUserByRole($role)
    {
        try {
            $users = DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->where('roles.name', $role)
                ->select('users.*')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users. Please try again later.'
            ], 500);
        }
    }

    //get all drivers
    public function getAllDrivers()
    {
        try {
            $drivers = User::where('role_id', 3)->get(); // Assuming role_id 3 is for drivers

            return response()->json([
                'status' => 'success',
                'data' => $drivers
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching drivers: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch drivers. Please try again later.'
            ], 500);
        }
    }

}
