<?php

namespace App\Http\Controllers\Admin;

use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->when(request()->q, function ($campaigns) {
            $campaigns = $campaigns->where('title', 'like', '%' . request()->q . '%');
        })->paginate(10);

        return view('admin.campaign.index', compact('campaigns'));
    }

    /**
     * Create
     */
    public function create()
    {
        $categories = Category::latest()->get();
        return view('admin.campaign.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $this->validateCampaign($request);

        $image = $this->uploadImage($request);
        $campaign = Campaign::create($this->campaignData($request, $image));

        return $campaign
            ? to_route('admin.campaign.index')->with(['success' => 'Data Berhasil Disimpan!'])
            : to_route('admin.campaign.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    /**
     * Update
     */
    public function edit(Campaign $campaign)
    {
        $categories = Category::latest()->get();
        return view('admin.campaign.edit', compact('campaign', 'categories'));
    }
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'image' => 'image|mimes:png,jpg,jpeg',
            'title' => 'required',
            'category_id' => 'required',
            'target_donation' => 'required|numeric',
            'max_date' => 'required',
            'description' => 'required',
        ]);

        if (!$request->hasFile('image')) {
            // Jika image tidak diubah
            $campaign = Campaign::findOrFail($campaign->id);
            $campaign->update([
                'title' => $request->title,
                'slug' => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'target_donation' => $request->target_donation,
                'max_date' => $request->max_date,
                'description' => $request->description,
                'user_id' => auth()->user()->id,
            ]);
        } else {
            // Jika image diubah
            Storage::disk('local')->delete('public/campaigns/' . basename($campaign->image));

            $image = $this->uploadImage($request);

            $campaign = Campaign::findOrFail($campaign->id);
            $campaign->update($this->campaignData($request, $image));
        }

        return $campaign
            ? to_route('admin.campaign.index')->with(['success' => 'Data Berhasil Disimpan!'])
            : to_route('admin.campaign.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    /**
     * Destroy
     */
    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        Storage::disk('local')->delete('public/campaigns/' . basename($campaign->image));
        $campaign->delete();

        return $campaign
            ?   response()->json([
                'status' => 'success'
            ])
            : response()->json([
                'status' => 'error'
            ]);
    }

    /**
     * Helpers
     */
    public function validateCampaign(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg',
            'title' => 'required',
            'category_id' => 'required',
            'target_donation' => 'required|numeric',
            'max_date' => 'required',
            'description' => 'required',
        ]);
    }
    public function campaignData(Request $request, $image)
    {
        return [
            'title' => $request->title,
            'slug' => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'target_donation' => $request->target_donation,
            'max_date' => $request->max_date,
            'description' => $request->description,
            'user_id' => auth()->user()->id,
            'image' => $image->hashName(),
        ];
    }
    public function uploadImage(Request $request)
    {
        $file = $request->file('image');
        $file->storeAs('public/campaigns', $file->hashName());
        return $file;
    }
}
