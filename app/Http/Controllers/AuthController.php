<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $account = $validated['role'] === 'admin'
            ? Admin::where('email', $validated['email'])->first()
            : User::where('email', $validated['email'])->first();

        if (! $account || ! Hash::check($validated['password'], $account->password)) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email', 'role');
        }

        $request->session()->regenerate();
        $request->session()->put([
            'auth_id' => $account->id,
            'auth_role' => $validated['role'],
            'auth_name' => $validated['role'] === 'admin' ? $account->nama : $account->name,
        ]);

        return redirect(
            $validated['role'] === 'admin' ? '/admin/dashboard' : '/user/dashboard'
        );
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:admin,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($validated['role'] === 'admin') {
            $admin = Admin::create([
                'nama' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $request->session()->regenerate();
            $request->session()->put([
                'auth_id' => $admin->id,
                'auth_role' => 'admin',
                'auth_name' => $admin->nama,
            ]);

            return redirect('/admin/dashboard');
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $request->session()->regenerate();
            $request->session()->put([
                'auth_id' => $user->id,
                'auth_role' => 'user',
                'auth_name' => $user->name,
            ]);

            return redirect('/user/dashboard');
        }
    }

    public function adminDashboard()
    {
        if (session('auth_role') !== 'admin') {
            return redirect('/login')->withErrors(['email' => 'Silakan login sebagai admin.']);
        }

        return view('auth.admin-dashboard');
    }

    public function userDashboard()
    {
        if (session('auth_role') !== 'user') {
            return redirect('/login')->withErrors(['email' => 'Silakan login sebagai user.']);
        }

        return view('auth.user-dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['auth_id', 'auth_role', 'auth_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
