<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function receiveFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);
    
        $user = $request->user();
        if ($user) {
            $user->fcm_token = $validated['fcm_token'];
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'FCM token saved successfully'
            ], 200);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }
}
