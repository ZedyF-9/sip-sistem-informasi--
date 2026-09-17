<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AturanAkun;
use App\Support\SesiAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KelolaAkunController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => [
                'nullable',
                Rule::in(array_keys(AturanAkun::ROLES)),
            ],
        ]);

        $q = trim($data['q'] ?? '');
        $role = $data['role'] ?? '';

        $akuns = User::query()
            ->whereIn('role', array_keys(AturanAkun::ROLES))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('akun.index', [
            'akuns' => $akuns,
            'q' => $q,
            'role' => $role,
            'roles' => AturanAkun::ROLES,
        ]);
    }

    public function create()
    {
        return view('akun.create', [
            'roles' => AturanAkun::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'email' => Str::lower(
                trim((string) $request->input('email'))
            ),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'role' => [
                'required',
                Rule::in(array_keys(AturanAkun::ROLES)),
            ],
            'password' => AturanAkun::password(),
        ], [
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $akun = new User();
        $akun->name = $data['name'];
        $akun->email = $data['email'];
        $akun->role = $data['role'];
        $akun->is_active = true;
        $akun->password = Hash::make($data['password']);
        $akun->save();

        return redirect()
            ->route('tu.akun.index')
            ->with('status', 'Akun baru berhasil dibuat dan aktif.');
    }

    public function edit(User $akun)
    {
        $this->pastikanAkunInternal($akun);

        return view('akun.edit', [
            'akun' => $akun,
            'roles' => AturanAkun::ROLES,
        ]);
    }

    public function update(Request $request, User $akun)
    {
        $this->pastikanAkunInternal($akun);
        $this->tolakPerubahanAkunSendiri($request, $akun);

        $request->merge([
            'email' => Str::lower(
                trim((string) $request->input('email'))
            ),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($akun->id),
            ],
            'is_active' => ['required', 'boolean'],
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        SesiAkun::pastikanDatabase();

        DB::transaction(function () use ($akun, $data) {
            $target = User::query()
                ->lockForUpdate()
                ->findOrFail($akun->id);

            $this->pastikanAkunInternal($target);

            if (
                $target->role === 'koordinator_tu'
                && ! (bool) $data['is_active']
            ) {
                throw ValidationException::withMessages([
                    'is_active' => (
                        'Akun Koordinator TU tidak dapat '
                        .'dinonaktifkan melalui halaman ini.'
                    ),
                ]);
            }

            if ($target->email !== $data['email']) {
                $target->email_verified_at = null;
            }

            $target->name = $data['name'];
            $target->email = $data['email'];
            $target->is_active = (bool) $data['is_active'];
            $target->remember_token = Str::random(60);
            $target->save();

            SesiAkun::hapus($target->id);
        });

        return redirect()
            ->route('tu.akun.index')
            ->with(
                'status',
                'Akun diperbarui. Pengguna perlu login kembali.'
            );
    }

    public function resetForm(Request $request, User $akun)
    {
        $this->pastikanAkunInternal($akun);
        $this->tolakPerubahanAkunSendiri($request, $akun);

        return view('akun.reset-password', [
            'akun' => $akun,
        ]);
    }

    public function resetPassword(Request $request, User $akun)
    {
        $this->pastikanAkunInternal($akun);
        $this->tolakPerubahanAkunSendiri($request, $akun);

        $data = $request->validate([
            'current_password' => [
                'required',
                'string',
                'current_password:web',
            ],
            'password' => AturanAkun::password(),
        ], [
            'current_password.current_password' => (
                'Password akun TU yang sedang login tidak sesuai.'
            ),
            'password.min' => 'Password baru minimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        SesiAkun::pastikanDatabase();

        DB::transaction(function () use ($akun, $data) {
            $target = User::query()
                ->lockForUpdate()
                ->findOrFail($akun->id);

            $this->pastikanAkunInternal($target);

            $target->password = Hash::make($data['password']);
            $target->remember_token = Str::random(60);
            $target->save();

            SesiAkun::hapus($target->id);
        });

        return redirect()
            ->route('tu.akun.index')
            ->with(
                'status',
                'Password akun berhasil diganti. Pengguna harus login kembali.'
            );
    }

    private function pastikanAkunInternal(User $akun): void
    {
        abort_unless(
            array_key_exists($akun->role, AturanAkun::ROLES),
            404
        );
    }

    private function tolakPerubahanAkunSendiri(
        Request $request,
        User $akun
    ): void {
        if ((int) $request->user()->id === (int) $akun->id) {
            throw ValidationException::withMessages([
                'akun' => (
                    'Untuk akun sendiri, gunakan menu Ganti Password. '
                    .'Perubahan identitas dilakukan oleh TU lain.'
                ),
            ]);
        }
    }
}