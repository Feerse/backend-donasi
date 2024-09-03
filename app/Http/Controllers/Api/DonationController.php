<?php

namespace App\Http\Controllers\Api;

use Midtrans\Config;
use App\Models\Donation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Midtrans\Snap;

class DonationController extends Controller
{
    public function __construct()
    {
        // Set midtrans configuration
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');
    }

    public function userAuth()
    {
        return auth()->guard('api')->user();
    }

    public function index()
    {
        // Ambil data donations
        $donations = Donation::with('campaign')
            ->where('donatur_id', $this->userAuth()->id)
            ->latest()
            ->paginate(5);

        return response()->json([
            'success' => true,
            'message' => 'List Data Donations: ' . $this->userAuth()->name,
            'data' => $donations,
        ], 200);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            /**
             * Algoritma membuat nomor invoice
             */
            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }

            $no_invoice = 'TRX-' . Str::upper($random);

            // Ambil data campaign
            $campaign = Campaign::where('slug', $request->campaignSlug)->first();

            $donation =  Donation::create([
                'invoice' => $no_invoice,
                'campaign_id' => $campaign->id,
                'donatur_id' => $this->userAuth()->id,
                'amount' => $request->amount,
                'pray' => $request->pray,
                'status' => 'pending',
            ]);

            // Buat sebuah payload
            $payload = [
                'transaction_details' => [
                    'order_id' => $donation->invoice,
                    'gross_amount' => $donation->amount,
                ],
                'customer_details' => [
                    'first_name' => $this->userAuth()->name,
                    'email' => $this->userAuth()->email,
                ]
            ];

            // Generate SNAP_TOKEN berdasarkan data payload di atas
            $snapToken = Snap::getSnapToken($payload);

            // Update attribute snap_token dari data donasi yang di-insert
            $donation->snap_token = $snapToken;
            $donation->save();

            // Buat response dengan key-nya yaitu 'snap_token' dan value-nya adalah SNAP_TOKEN dari Midtrans
            $this->response['snap_token'] = $snapToken;
        });

        return response()->json([
            'success' => true,
            'message' => 'Donasi Berhasil Dibuat!',
            $this->response // <-- data SNAP_TOKEN dari Midtrans
        ]);
    }

    /**
     * Method ini berfungsi untuk menerima response data berupa status pembayaran yang dikirim dari Midtrans
     */
    public function notificationHandler(Request $request)
    {
        // Ambil content JSON yang dikirim melalui Midtrans
        $payload = $request->getContent();
        $notification = json_decode($payload);

        // Buat variable yang isinya adalah data kunci signatur
        $validSignatureKey = hash(
            "sha512",
            $notification->order_id .
                $notification->status_code .
                $notification->gross_amount .
                config('services.midtrans.serverKey')
        );

        if ($notification->signature_key != $validSignatureKey) { // <-- `$notification->signature_key` signature key dari Midtrans
            return response([
                'message' => 'Invalid Signature'
            ], 403);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $orderId = $notification->order_id;
        $fraud = $notification->fraud_status;

        // Data donation
        $data_donation = Donation::where('invoice', $orderId)->first();

        switch ($transaction) {
            case 'capture':
                if ($type == 'credit_card') {
                    $data_donation->update(['status' => ($fraud == 'challenge') ? 'pending' : 'success']);
                }
                break;

            case 'settlement':
                $data_donation->update(['status' => 'success']);
                break;

            case 'pending':
                $data_donation->update(['status' => 'pending']);
                break;

            case 'deny':
            case 'cancel':
                $data_donation->update(['status' => 'failed']);
                break;

            case 'expire':
                $data_donation->update(['status' => 'expired']);
                break;
        }
    }
}
