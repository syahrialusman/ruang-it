<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * Kontroler untuk mengelola profil user
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profile user
     * Menampilkan informasi user seperti nama, email, dan avatar
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $chatHistories = $user->chatHistories()
            ->with('conversation')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('profile.index', compact('user', 'chatHistories'));
    }

    /**
     * Menampilkan form edit profile
     * 
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Memperbarui data profile user
     * Termasuk upload avatar jika ada
     * 
     * @param Request $request Request yang berisi data profile baru
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:1024'] // Max 1MB
        ]);

        $user = Auth::user();
        $data = $request->only(['name', 'email', 'phone', 'bio']);

        // Upload avatar baru jika ada
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $user->id . '.' . $avatar->getClientOriginalExtension();
            
            // Hapus avatar lama jika ada
            if ($user->avatar && file_exists(public_path('images/' . $user->avatar))) {
                unlink(public_path('images/' . $user->avatar));
            }
            
            // Simpan avatar baru langsung ke public/images
            $avatar->move(public_path('images'), $avatarName);
            $data['avatar'] = $avatarName;
        }

        // Update data user
        $user->update($data);

        return redirect()->route('profile.index')->with('success', 'Profile berhasil diperbarui!');
    }

    /**
     * Memperbarui password user
     * 
     * @param Request $request Request yang berisi password lama dan baru
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required']
        ]);

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai'
            ]);
        }

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Menghapus akun user
     * Termasuk menghapus avatar dan semua data terkait
     * 
     * @param Request $request Request yang berisi password untuk konfirmasi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password tidak cocok']);
        }

        // Hapus avatar jika ada
        if ($user->avatar) {
            $oldAvatarPath = public_path('images/' . $user->avatar);
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        // Hapus user dan semua data terkait
        $user->delete();

        return redirect()->route('login')->with('success', 'Akun berhasil dihapus.');
    }
}
