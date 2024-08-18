<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->when(request()->q, function ($categories) {
            $categories = $categories->where('name', 'like', '%' . request()->q . '%');
        })->paginate(10);

        return view('admin.category.index', compact('categories'));
    }

    /**
     * Create
     */
    public function create()
    {
        return view('admin.category.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'name' => 'required|unique:categories'
        ]);

        // Upload Image
        $image = $request->file('image');
        $image->storeAs('public/categories', $image->hashName());

        // Save to Database
        $category = Category::create([
            'image' => $image->hashName(),
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-')
        ]);

        // Kondisi jika sukses disimpan
        return $category
            ? to_route('admin.category.index')->with(['success' => 'Data Berhasil Disimpan!'])
            : to_route('admin.category.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    /**
     * Update
     */
    public function edit(Category $category)
    {
        return view('admin.category.edit', compact('category'));
    }
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'image' => 'image|mimes:jpeg,jpg,png|max:2000',
            'name' => 'required|unique:categories,name,' . $category->id
        ]);

        // Kondisi jika image kosong
        if (!$request->hasFile('image')) {
            // Update data tanpa image
            $category = Category::findOrFail($category->id);
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-')
            ]);
        } else {
            // Hapus image lama
            Storage::disk('local')->delete('public/categories/' . basename($category->image));

            // Upload image baru
            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            // Update dengan image baru
            $category = Category::findOrFail($category->id);
            $category->update([
                'image' => $image->hashName(),
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-')
            ]);
        }

        // Kondisi sukses atau gagal
        return $category
            ? to_route('admin.category.index')->with(['success' => 'Data Berhasil Disimpan!'])
            : to_route('admin.category.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    /**
     * Delete
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        Storage::disk('local')->delete('public/categories/' . basename($category->image));
        $category->delete();

        return $category
            ? response()->json([
                'status' => 'success'
            ])
            : response()->json([
                'status' => 'error'
            ]);
    }
}
