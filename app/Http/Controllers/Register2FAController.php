<?php

namespace App\Http\Controllers;

use App\Models\Register2FA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Register2FAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $register2FAs = Auth::user()->register2FAs; // Assuming a hasMany relationship in User model
        return view('auth.two-factor-authentication', compact('register2FAs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('auth.add-two-factor-user');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Auth::user()->register2FAs()->create($request->only('name'));

        return redirect()->route('2fa.index')->with('success', '2FA user added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Register2FA $register2FA)
    {
        // Ensure the user owns the Register2FA entry before deleting
        if ($register2FA->user_id !== Auth::id()) {
            abort(403); // Forbidden
        }

        try {
            // Delete face data from FastAPI server using form data
            $response = Http::asForm()->post('http://localhost:5000/delete-face', [
                'email' => Auth::user()->email,
                'faceName' => $register2FA->name
            ]);

            if (!$response->successful()) {
                return redirect()->route('2fa.index')
                    ->with('error', 'Failed to delete face data from server: ' . ($response->json()['message'] ?? 'Unknown error'));
            }

            // Delete the database record
            $register2FA->delete();

            return redirect()->route('2fa.index')
                ->with('success', '2FA user and face data deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('2fa.index')
                ->with('error', 'An error occurred while deleting the face data: ' . $e->getMessage());
        }
    }
}
