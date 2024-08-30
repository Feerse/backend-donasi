<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        // Ambil data campaigns
        $campaigns = Campaign::with(['user', 'sumDonation'])
            // Jika ada request q (search)
            ->when(request()->q, function ($campaigns) {
                $campaigns = $campaigns->where('title', 'like', '%' . request()->q . '%');
            })
            ->latest()->paginate(5);

        return response()->json([
            'success' => true,
            'message' => 'List Data Campaigns',
            'data' => $campaigns,
        ], 200);
    }

    public function show($slug)
    {
        // Ambil data campaign yang sesuai dengan slug
        $campaign = Campaign::with(['user', 'sumDonation'])->where('slug', $slug)->first();

        // Ambil data donation berdasarkan id campaign dan statusnya success
        $donations = Donation::with('donatur')->where('campaign_id', $campaign->id)->where('status', 'success')->latest()->get();

        // Jika data campaign ditemukan
        if ($campaign) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Campaign: ' . $campaign->title,
                'data' => $campaign,
                'donations' => $donations,
            ], 200);
        }

        // Jika campaign tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Data Campaign Tidak Ditemukan',
        ], 404);
    }
}
