<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // for logging errors

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
}
