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
        
        $staffs = Operator::where('role', 'karyawan')->orderBy('name')->get();

        $googleEnabled = env('GOOGLE_AUTH_ENABLED', false);

        return view('auth.login', compact('coordinators', 'staffs', 'googleEnabled'));
    }

    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'whatsapp' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->whatsapp);

        if (empty($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp tidak valid.'
            ], 422);
        }

        // Standardize formats for lookup (matching suffix)
        $corePhone = $phone;
        if (str_starts_with($phone, '62')) {
            $corePhone = substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $corePhone = substr($phone, 1);
        }

        $operator = Operator::where(function($q) use ($phone, $corePhone) {
            $q->where('whatsapp', $phone)
              ->orWhere('whatsapp', 'like', '%' . $corePhone);
        })->first();

        if (!$operator) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp Anda belum terdaftar. Silakan hubungi Koordinator.'
            ], 422);
        }

        // Generate temporary magic login token
        $token = \Illuminate\Support\Str::random(40);
        
        // Cache the token to operator mapping for 10 minutes
        \Illuminate\Support\Facades\Cache::put('magic_token_' . $token, $operator->id, now()->addMinutes(10));

        // Construct magic link
        $url = route('login.verify', ['token' => $token]);

        // Send via Wablas
        $wablas = new \App\Services\WablasService();
        $message = "Halo *{$operator->name}*,\n\n"
                 . "Berikut adalah link masuk aman Anda untuk Woundcare Dashboard (berlaku 10 menit):\n"
                 . "{$url}\n\n"
                 . "Silakan klik link di atas untuk masuk secara otomatis. Mohon tidak membagikan link ini kepada siapa pun.";

        $result = $wablas->send($operator->whatsapp, $message);

        if (!($result['status'] ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim pesan WhatsApp: ' . ($result['reason'] ?? 'Kesalahan API Wablas.')
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Link masuk berhasil dikirim ke WhatsApp Anda!'
        ]);
    }

    public function verifyMagicLink(Request $request)
    {
        $token = $request->query('token');

        if (empty($token)) {
            return redirect()->route('login')->withErrors('Token verifikasi kosong.');
        }

        $operatorId = \Illuminate\Support\Facades\Cache::get('magic_token_' . $token);

        if (!$operatorId) {
            return redirect()->route('login')->withErrors('Link masuk tidak valid atau telah kadaluarsa (berlaku 10 menit). Silakan minta link baru.');
        }

        $operator = Operator::find($operatorId);

        if (!$operator) {
            return redirect()->route('login')->withErrors('Profil operator tidak ditemukan.');
        }

        // Remove token immediately to prevent reuse
        \Illuminate\Support\Facades\Cache::forget('magic_token_' . $token);

        // Set session
        session([
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'operator_vendor' => $operator->vendor,
            'operator_role' => $operator->role,
            'operator_whatsapp' => $operator->whatsapp,
        ]);

        // Queue secure remember cookie for 30 days (43200 minutes)
        \Illuminate\Support\Facades\Cookie::queue('remember_operator_id', $operator->id, 43200);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk sebagai ' . $operator->name);
    }

    public function loginByPhone(Request $request)
    {
        $request->validate([
            'whatsapp' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->whatsapp);

        if (empty($phone)) {
            return redirect()->back()->withInput()->withErrors('Nomor WhatsApp tidak valid.');
        }

        // Standardize formats for lookup (matching suffix)
        $corePhone = $phone;
        if (str_starts_with($phone, '62')) {
            $corePhone = substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $corePhone = substr($phone, 1);
        }

        $operator = Operator::where(function($q) use ($phone, $corePhone) {
            $q->where('whatsapp', $phone)
              ->orWhere('whatsapp', 'like', '%' . $corePhone);
        })->first();

        if (!$operator) {
            return redirect()->back()->withInput()->withErrors('Nomor WhatsApp Anda belum terdaftar. Silakan hubungi Koordinator.');
        }

        session([
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'operator_vendor' => $operator->vendor,
            'operator_role' => $operator->role,
            'operator_whatsapp' => $operator->whatsapp,
        ]);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk sebagai ' . $operator->name);
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
            'operator_whatsapp' => $operator->whatsapp,
        ]);

        // Queue secure remember cookie for 30 days (43200 minutes)
        \Illuminate\Support\Facades\Cookie::queue('remember_operator_id', $operator->id, 43200);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk sebagai ' . $operator->name);
    }

    public function loginWithPin(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:operators,id',
            'pin' => 'required|string|size:6',
        ]);

        $operatorId = $request->operator_id;
        $inputPin = $request->pin;

        // Retrieve pin from cache
        $cachedPin = \Illuminate\Support\Facades\Cache::get('emergency_pin_' . $operatorId);

        if (!$cachedPin || $cachedPin !== $inputPin) {
            return redirect()->back()
                ->withInput()
                ->withErrors('PIN Darurat tidak valid atau telah kadaluarsa. Silakan hubungi Koordinator Anda.');
        }

        // Pin is valid, log in the operator
        $operator = Operator::findOrFail($operatorId);

        // Remove the pin from cache immediately to prevent reuse
        \Illuminate\Support\Facades\Cache::forget('emergency_pin_' . $operatorId);

        session([
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'operator_vendor' => $operator->vendor,
            'operator_role' => $operator->role,
            'operator_whatsapp' => $operator->whatsapp,
        ]);

        // Queue secure remember cookie for 30 days (43200 minutes)
        \Illuminate\Support\Facades\Cookie::queue('remember_operator_id', $operator->id, 43200);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk menggunakan PIN Darurat sebagai ' . $operator->name);
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
                    'operator_whatsapp' => $operator->whatsapp,
                ]);

                // Queue secure remember cookie for 30 days (43200 minutes)
                \Illuminate\Support\Facades\Cookie::queue('remember_operator_id', $operator->id, 43200);

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
            'operator_whatsapp' => $operator->whatsapp,
        ]);

        // Queue secure remember cookie for 30 days (43200 minutes)
        \Illuminate\Support\Facades\Cookie::queue('remember_operator_id', $operator->id, 43200);

        return redirect()->route('dashboard')->with('success', 'Akun Google berhasil dihubungkan. Selamat bekerja, ' . $operator->name . '!');
    }

    public function logout()
    {
        session()->forget(['operator_id', 'operator_name', 'operator_vendor', 'operator_role', 'operator_whatsapp']);
        
        // Delete secure remember cookie
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('remember_operator_id'));

        return redirect()->route('login')->with('success', 'Berhasil keluar dari sistem.');
    }
}
