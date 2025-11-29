<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\RoutePricing;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // <--- JANGAN LUPA INI BUAT GENERATE STRING

// Import Library Midtrans
use Midtrans\Config;
use Midtrans\Snap;

// Import PDF & QR
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // 1. VALIDASI INPUT (SEKARANG MENERIMA ARRAY JADWAL)
        $validator = Validator::make($request->all(), [
            'schedule_ids'   => 'required|array|min:1', // Bisa 1 (Sekali jalan) atau 2 (PP)
            'schedule_ids.*' => 'exists:schedules,id',  // Pastikan ID jadwal valid
            'passengers'     => 'required|array|min:1',
            'passengers.*.name' => 'required|string',
            'passengers.*.identity_number' => 'required|string',
            'passengers.*.ticket_category_id' => 'required|exists:ticket_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. MULAI TRANSAKSI DATABASE
        try {
            return DB::transaction(function () use ($request) {
                
                // GENERATE BATCH ID (Satu ID untuk rombongan tiket ini)
                // Contoh: TRX-170123456-XYZA
                $batchId = 'TRX-' . now()->timestamp . '-' . strtoupper(Str::random(4));
                
                $totalTransactionAmount = 0; // Total harga gabungan (Pergi + Pulang)
                $createdBookings = []; // Simpan data booking untuk respon
                $user = $request->user();
                $totalPassengers = count($request->passengers);

                // --- LOOPING SETIAP JADWAL YANG DIPILIH ---
                foreach ($request->schedule_ids as $scheduleId) {
                    
                    // --- PERBAIKAN DI SINI (PESSIMISTIC LOCKING) ---
                    // "lockForUpdate" akan menahan proses lain yang mau akses baris ini
                    // sampai transaksi ini selesai (commit/rollback).
                    $schedule = Schedule::lockForUpdate()->find($scheduleId);

                    // Cek Kuota per Jadwal
                    if ($schedule->quota_passenger_left < $totalPassengers) {
                        throw new \Exception("Maaf, kuota baru saja habis untuk jadwal: " . $schedule->ship->name);
                    }

                    // A. Buat Booking Header
                    $booking = Booking::create([
                        'user_id'        => $user->id,
                        'schedule_id'    => $schedule->id,
                        'booking_code'   => 'BOOK-' . strtoupper(Str::random(8)),
                        'batch_id'       => $batchId, // <--- PENANDA KELOMPOK
                        'total_amount'   => 0, // Nanti diupdate
                        'payment_status' => 'pending',
                        'payment_method' => 'midtrans',
                    ]);

                    $subTotal = 0; // Harga total untuk booking INI saja

                    // B. Loop Penumpang (Logic Lama Tetap Dipakai)
                    foreach ($request->passengers as $pax) {
                        $pricing = RoutePricing::where('route_id', $schedule->route_id)
                            ->where('category_id', $pax['ticket_category_id'])
                            ->first();

                        if (!$pricing) {
                            throw new \Exception("Harga tiket kategori ini tidak ditemukan di rute tersebut.");
                        }

                        $subTotal += $pricing->price;

                        Passenger::create([
                            'booking_id'         => $booking->id,
                            'ticket_category_id' => $pax['ticket_category_id'],
                            'name'               => $pax['name'],
                            'identity_number'    => $pax['identity_number'],
                            'gender'             => $pax['gender'] ?? null,
                            'age'                => $pax['age'] ?? null,
                        ]);
                    }

                    // C. Update Booking Ini & Kurangi Kuota
                    $booking->update(['total_amount' => $subTotal]);
                    $schedule->decrement('quota_passenger_left', $totalPassengers);

                    // Tambahkan ke Total Transaksi Gabungan
                    $totalTransactionAmount += $subTotal;
                    $createdBookings[] = $booking;
                }

                // --- MIDTRANS LOGIC (BAYAR TOTAL SATU BATCH) ---
                
                // 1. Config
                Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
                Config::$isSanitized = true;
                Config::$is3ds = true;

                // 2. Params (Order ID pakai BATCH ID)
                $params = [
                    'transaction_details' => [
                        'order_id' => $batchId, // <--- PENTING: Bayar per Batch
                        'gross_amount' => (int) $totalTransactionAmount,
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email'      => $user->email,
                        'phone'      => $user->phone,
                    ],
                ];

                // 3. Get Token
                $snapToken = Snap::getSnapToken($params);

                // 4. Update Token ke SEMUA Booking di batch ini
                Booking::where('batch_id', $batchId)->update(['snap_token' => $snapToken]);

                // --- SELESAI ---

                return response()->json([
                    'status'     => 'success',
                    'message'    => 'Booking berhasil. Silakan bayar.',
                    'snap_token' => $snapToken,
                    'batch_id'   => $batchId
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // --- FUNGSI LAIN TETAP SAMA ---

    public function index(Request $request)
    {
        $bookings = Booking::with(['schedule.route.origin', 'schedule.route.destination', 'passengers'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $bookings
        ]);
    }

    public function downloadTicket($code)
    {
        $booking = Booking::with(['schedule.ship', 'schedule.route.origin', 'schedule.route.destination', 'passengers.ticket_category'])
            ->where('booking_code', $code)
            ->firstOrFail();

        if ($booking->payment_status !== 'paid') {
            return response()->json(['message' => 'Tiket belum lunas.'], 403);
        }

        // Generate QR SVG
        $qrcode = base64_encode(QrCode::format('svg')->size(100)->generate($booking->booking_code));

        $pdf = Pdf::loadView('pdf.ticket', compact('booking', 'qrcode'));

        return $pdf->download('E-Ticket-' . $booking->booking_code . '.pdf');
    }
}