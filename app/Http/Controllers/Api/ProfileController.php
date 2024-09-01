<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donatur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        // Tampilkan data profile yang sedang login
        return response()->json([
            'success' => true,
            'message' => 'Data Profile',
            'data' => auth()->guard('api')->user(), // `guard('api')` karena profile yang login tersebut menggunakan guard ini
        ], 200);
    }

    public function update(Request $request)
    {
        // Validasi nama
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            // Kembalikan pesan error dengan kode 400 (Bad Request)
            return response()->json($validator->errors(), 400);
        }

        // Ambil data profile yang sedang login
        $donatur = Donatur::find(auth()->guard('api')->user()->id);

        // Update dengan avatar jika ada
        if ($request->file('avatar')) {
            // Hapus image lama terlebih dahulu
            Storage::disk('local')->delete('public/donaturs/' . basename($donatur->image));

            // Lalu upload image baru
            $image = $request->file('avatar');
            $image->storeAs('public/donaturs', $image->hashName());

            // Update
            $donatur->update([
                'name' => $request->name,
                'avatar' => $image->hashName(),
            ]);
        }

        // Jika tidak ada request file dengan nama `avatar`, update tanpa avatar
        $donatur->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Profile Berhasil Disimpan!',
            'data' => $donatur,
        ], 201);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed'
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $donatur = Donatur::find(auth()->guard('api')->user()->id);
        $donatur->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password Berhasil Disimpan!',
            'data' => $donatur,
        ], 201);
    }
}
