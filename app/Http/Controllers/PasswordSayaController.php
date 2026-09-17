<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AturanAkun;
use App\Support\SesiAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordSayaController extends Controller
{
    public function edit()
    {
        return view('profil.password');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'current_password' => [
                'required',
                'string',
                'current_password:web',
            ],
            'password' => [
                ...AturanAkun::password(),
                'different:current_password',
            ],
        ], [
            'current_password.current_password' => (
                'Password lama tidak sesuai.'
            ),
            'password.min' => 'Password baru minimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.different' => (
                'Password baru harus berbeda dari password lama.'
            ),
        ]);

        SesiAkun::pastikanDatabase();

        DB::transaction(function () use ($request, $data) {
            $akun = User::query()
                ->lockForUpdate()
                ->findOrFail($request->user()->id);

            if (
                ! Hash::check(
                    $data['current_password'],
                    $akun->password
                )
            ) {
                throw ValidationException::withMessages([
                    'current_password' => (
                        'Password telah berubah. Masukkan password terbaru.'
                    ),
                ]);
            }

            $akun->password = Hash::make($data['password']);
            $akun->remember_token = Str::random(60);
            $akun->save();

            SesiAkun::hapus($akun->id);
        });

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'status',
                'Password berhasil diganti. Silakan login dengan password baru.'
            );
    }
}