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
    return view('auth.face-authentication');
  }

  public function verifyFace(Request $request)
  {
    try {
      $response = Http::post($this->apiUrl . '/api/authenticate', [
        'image' => $request->input('image'),
        'user_id' => Auth::id()
      ]);

      if ($response->successful()) {
        $data = $response->json();

        if ($data['status'] === 'success' && $data['verified']) {
          // Face authentication successful
          Session::put('face_authenticated', true);
          return response()->json([
            'status' => 'success',
            'message' => 'Face authentication successful'
          ]);
        }
      }

      return response()->json([
        'status' => 'error',
        'message' => 'Face authentication failed'
      ], 401);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Face authentication service error'
      ], 500);
    }
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
