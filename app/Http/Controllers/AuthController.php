<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Operator;
use Exception;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('operator_id')) {
            return redirect()->route('dashboard');
        }

        $coordinators = Operator::where('role', 'coordinator')->orderBy('name')->get();
        
        // Only list karyawan that have email for the bypass (or all if none linked yet)
        $staffs = Operator::where('role', 'karyawan')->whereNotNull('email')->orderBy('name')->get();
        if ($staffs->isEmpty()) {
            $staffs = Operator::where('role', 'karyawan')->orderBy('name')->take(20)->get();
        }

        $googleEnabled = env('GOOGLE_AUTH_ENABLED', false);

        return view('auth.login', compact('coordinators', 'staffs', 'googleEnabled'));
    }

    public function loginBypass(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:operators,id',
        ]);

        $operator = Operator::findOrFail($request->operator_id);

        session([
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'operator_vendor' => $operator->vendor,
            'operator_role' => $operator->role,
        ]);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk sebagai ' . $operator->name);
    }

    public function redirectToGoogle()
    {
        if (!env('GOOGLE_AUTH_ENABLED', false)) {
            return redirect()->route('login')->withErrors('Google Login saat ini dinonaktifkan.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();
            
            // 1. Check if email is already registered
            $operator = Operator::where('email', $email)->first();

            if ($operator) {
                // If found, log in directly
                session([
                    'operator_id' => $operator->id,
                    'operator_name' => $operator->name,
                    'operator_vendor' => $operator->vendor,
                    'operator_role' => $operator->role,
                ]);

                return redirect()->route('dashboard')->with('success', 'Berhasil masuk via Google sebagai ' . $operator->name);
            }

            // 2. If email is not registered:
            // Store google email in session temporary key and redirect to setup profile
            session(['temp_google_email' => $email]);
            return redirect()->route('login.setup-profile');

        } catch (Exception $e) {
            return redirect()->route('login')->withErrors('Gagal masuk menggunakan Google: ' . $e->getMessage());
        }
    }

    /**
     * Show setup profile screen for first-time Google sign in.
     */
    public function showSetupProfile()
    {
        if (!session()->has('temp_google_email')) {
            return redirect()->route('login')->withErrors('Harap masuk menggunakan Google terlebih dahulu.');
        }

        // Get all karyawan operators who DO NOT have an email linked yet
        $availableOperators = Operator::where('role', 'karyawan')
            ->whereNull('email')
            ->orderBy('vendor')
            ->orderBy('name')
            ->get();

        $googleEmail = session('temp_google_email');

        return view('auth.setup-profile', compact('availableOperators', 'googleEmail'));
    }

    /**
     * Link Google email to selected Operator profile.
     */
    public function saveSetupProfile(Request $request)
    {
        if (!session()->has('temp_google_email')) {
            return redirect()->route('login')->withErrors('Sesi Google kadaluarsa. Harap masuk kembali.');
        }

        $request->validate([
            'operator_id' => 'required|exists:operators,id',
        ]);

        $operator = Operator::where('role', 'karyawan')
            ->whereNull('email')
            ->findOrFail($request->operator_id);

        // Save email to database
        $operator->update([
            'email' => session('temp_google_email')
        ]);

        // Clear temporary session email
        session()->forget('temp_google_email');

        // Log in operator
        session([
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'operator_vendor' => $operator->vendor,
            'operator_role' => $operator->role,
        ]);

        return redirect()->route('dashboard')->with('success', 'Akun Google berhasil dihubungkan. Selamat bekerja, ' . $operator->name . '!');
    }

    public function logout()
    {
        session()->forget(['operator_id', 'operator_name', 'operator_vendor', 'operator_role']);
        return redirect()->route('login')->with('success', 'Berhasil keluar dari sistem.');
    }
}
