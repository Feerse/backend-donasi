<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Donatur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Set Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:donaturs',
            'password' => 'required|min:8|confirmed',
        ]);

        // Jika Validasi Gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Jika validasi terpenuhi, maka akan melakukan insert data tersebut ke dalam database
        $donatur = Donatur::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Setelah data berhasil disimpan ke dalam database, maka akan mengembalikan response data success dalam format JSON, memberikan informasi dari data yang di-input tersebut dan sekaligus generate sebuah token
        return response()->json([
            'success' => true,
            'message' => 'Register Berhasil!',
            'data' => $donatur,
            'token' => $donatur->createToken('authToken')->accessToken
        ], 201);
    }
}
