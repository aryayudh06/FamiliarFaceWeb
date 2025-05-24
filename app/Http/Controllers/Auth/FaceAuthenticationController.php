<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FaceAuthenticationController extends Controller
{
    protected $apiUrl = 'http://localhost:5000'; // Update this with your FastAPI server URL

    public function showFaceAuth()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If user doesn't have 2FA enabled, redirect to dashboard
        if (!$user->has_active_2_f_a) {
            return redirect()->route('dashboard');
        }

        // If user is already face authenticated, redirect to dashboard
        if (Session::get('face_authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('auth.face-authentication');
    }

    public function verifyFace(Request $request)
    {
        $user = Auth::user();

        // If user doesn't have 2FA enabled, return success
        if (!$user->has_active_2_f_a) {
            Session::put('face_authenticated', true);
            return response()->json([
                'status' => 'success',
                'message' => '2FA not enabled, skipping face verification'
            ]);
        }

        $request->validate([
            'verified' => 'required|boolean',
            'label' => 'required|string'
        ]);

        if ($request->verified) {
            Session::put('face_authenticated', true);
            Session::put('face_label', $request->label);

            return response()->json([
                'status' => 'success',
                'message' => 'Face verification successful'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Face verification failed'
        ], 400);
    }

    public function registerFace(Request $request)
    {
        try {
            $response = Http::post($this->apiUrl . '/api/register', [
                'image' => $request->input('image'),
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name
            ]);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Face registered successfully'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Face registration failed'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Face registration service error'
            ], 500);
        }
    }
}
