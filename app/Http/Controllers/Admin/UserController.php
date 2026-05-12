<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $admin = LegacyAuth::user();
        $users = User::orderBy('full_name')->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'admin' => $admin,
            'userRole' => 'admin', // Middleware ensures this is 'admin'
        ]);
    }

    public function create()
    {
        $admin = LegacyAuth::user();

        return view('admin.users.create', [
            'admin' => $admin,
            'userRole' => 'admin',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:120',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'she', 'hrga', 'tod'])],
        ]);

        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
            'must_change_password' => true, // Force password change on first login
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $admin = LegacyAuth::user();
        $user = User::findOrFail($id);

        return view('admin.users.edit', [
            'user' => $user,
            'admin' => $admin,
            'userRole' => 'admin',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:120',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'she', 'hrga', 'tod'])],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
            $data['must_change_password'] = true;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $admin = LegacyAuth::user();
        if ($id === (int) ($admin['id'] ?? 0)) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
