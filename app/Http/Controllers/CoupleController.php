<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoupleController extends Controller
{
    /**
     * Connect the authenticated user with a partner using their pairing code.
     */
    public function connect(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isCoupled()) {
            return back()->withErrors(['pairing_code' => 'Anda sudah terhubung dengan pasangan. Putuskan sambungan terlebih dahulu jika ingin mengganti pasangan.']);
        }

        $validated = $request->validate([
            'pairing_code' => 'required|string|max:20',
        ]);

        $code = strtoupper(trim($validated['pairing_code']));

        /** @var User|null $partner */
        $partner = User::where('pairing_code', $code)->first();

        if (! $partner) {
            return back()->withErrors(['pairing_code' => 'Kode pasangan tidak ditemukan. Pastikan kode sudah benar.']);
        }

        if ($partner->id === $user->id) {
            return back()->withErrors(['pairing_code' => 'Anda tidak dapat menghubungkan dengan kode akun Anda sendiri.']);
        }

        if ($partner->isCoupled()) {
            return back()->withErrors(['pairing_code' => 'Akun '.$partner->name.' sudah terhubung dengan pasangan lain.']);
        }

        $couple = Couple::create([
            'name' => $user->name.' & '.$partner->name,
            'connected_at' => now(),
        ]);

        $user->update(['couple_id' => $couple->id]);
        $partner->update(['couple_id' => $couple->id]);

        return back()->with('success', 'Selamat! Akun Anda berhasil terhubung dengan '.$partner->name.' 💕');
    }

    /**
     * Disconnect the current active couple relationship.
     */
    public function disconnect(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isCoupled()) {
            return back()->withErrors(['couple' => 'Anda saat ini tidak memiliki pasangan yang terhubung.']);
        }

        $partner = $user->partner();
        $couple = $user->couple;

        $user->update(['couple_id' => null]);
        if ($partner) {
            $partner->update(['couple_id' => null]);
        }

        if ($couple) {
            $couple->delete();
        }

        return back()->with('success', 'Koneksi akun couple berhasil diputuskan.');
    }
}
