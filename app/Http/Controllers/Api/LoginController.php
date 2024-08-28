<?php

namespace App\Http\Controllers\Api;

use App\Models\Donatur;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Kondisi jika validasi tidak terpenuhi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Pengecekan data donatur berdasarkan email
        $donatur = Donatur::where('email', $request->email)->first();

        // Apakah tidak ada donatur || ada donatur tapi pass-nya tidak sesuai dengan yang ada di dalam database
        if (!$donatur || !Hash::check($request->password, $donatur->password)) {
            // Jika salah satu dari 2 kondisi bernilai false
            return response()->json([
                'success' => false,
                'message' => 'Login Failed!',
            ], 401);
        }

        // Jika ada donatur dan pass-nya sesuai dengan yang ada di dalam database, maka akan mengembalikan sebuah response success dengan menampilkan data donatur yang login dan sekaligus melakukan generate token
        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil!',
            'data' => $donatur,
            'token' => $donatur->createToken('authToken')->accessToken
        ], 200);
    }

    public function logout(Request $request)
    {
        // Hapus data token dari user yang login
        $removeToken = $request->user()->tokens()->delete();

        // Jika proses hapus data token berhasil, maka akan mengembalikan sebuah response success
        if ($removeToken) {
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',
            ]);
        }
    }
}
