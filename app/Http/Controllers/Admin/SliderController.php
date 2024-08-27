<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->paginate(5);
        return view('admin.slider.index', compact('sliders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2000',
            'link' => 'required',
        ]);

        // Upload Image
        $image = $request->file('image');
        $image->storeAs('public/sliders', $image->hashName());

        // Save to Database
        $slider = Slider::create([
            'image' => $image->hashName(),
            'link' => $request->link,
        ]);

        return $slider
            ? to_route('admin.slider.index')->with(['success' => 'Data Berhasil Disimpan!'])
            : to_route('admin.slider.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    public function destroy($id)
    {
        // Mencari slider berdasarkan ID
        $slider = Slider::findOrFail($id);

        // Hapus Gambar
        Storage::disk('local')->delete('public/sliders/' . basename($slider->image));
        // Hapus Slider dari Database
        $slider->delete();

        return $slider
            ? response()->json(['status' => 'success'])
            : response()->json(['status' => 'error']);
    }
}
