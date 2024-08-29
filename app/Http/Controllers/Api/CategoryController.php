<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Ambil semua data category dari database
        $categories = Category::latest()->paginate(12);

        // Return dengan response berbentuk JSON
        return response()->json([
            'success' => true,
            'message' => 'List Data Categories',
            'data' => $categories,
        ], 200);
    }

    public function show($slug)
    {
        // Tampilkan data-data campaign yang sesuai dengan category tersebut
        $category = Category::with(['campaigns.user', 'campaigns.sumDonation'])->where('slug', $slug)->first();

        if ($category) {
            return response()->json([
                'success' => true,
                'message' => 'List Data Campaign Berdasarkan Category: ' . $category->name,
                'data' => $category,
            ], 200);
        }

        // Jika data campaign berdasarkan category tidak ada
        return response()->json([
            'success' => false,
            'message' => 'Data Category Tidak Ditemukan!',
        ], 404);
    }

    public function categoryHome()
    {
        // Ambil 3 data categories
        $categories = Category::latest()->limit(3)->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Category Home',
            'data' => $categories,
        ], 200);
    }
}
