<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules; 

class UserController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Account deleted successfully.');
    }
}
