<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index()
    {
        return view('admin.donation.index');
    }

    public function filter(Request $request)
    {
        $request->validate([
            'date_from' => 'required',
            'date_to' => 'required',
        ]);

        // Get data donation by range date
        $donations = Donation::where('status', 'success')
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to)
            ->get();

        // Get total donation by range date
        $total = Donation::where('status', 'success')
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to)
            ->sum('amount');

        return view('admin.donation.index', compact('donations', 'total'));
    }
}
