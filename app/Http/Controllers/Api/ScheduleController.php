<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // 1. Ambil Daftar Pelabuhan (Untuk Dropdown di Frontend)
    public function getPorts()
    {
        $ports = Port::select('id', 'name', 'code', 'location')->get();
        
        return response()->json([
            'status' => 'success',
            'data'   => $ports
        ]);
    }

    // 2. Cari Jadwal Kapal
    public function search(Request $request)
    {
        $originID      = $request->query('origin_port_id');
        $destinationID = $request->query('destination_port_id');
        $date          = $request->query('date');       // Tgl Pergi
        $returnDate    = $request->query('return_date'); // Tgl Pulang (Opsional)

        // 1. Cari Jadwal PERGI (Departures)
        $departures = Schedule::with(['ship', 'route.origin', 'route.destination', 'route.pricings.category'])
            ->whereHas('route', function($q) use ($originID, $destinationID) {
                if ($originID) $q->where('origin_port_id', $originID);
                if ($destinationID) $q->where('destination_port_id', $destinationID);
            })
            ->whereDate('departure_time', $date)
            ->where('status', 'open')
            ->where('quota_passenger_left', '>', 0)
            ->orderBy('departure_time', 'asc')
            ->get();

        // 2. Cari Jadwal PULANG (Returns) - Hanya jika user minta tanggal pulang
        $returns = [];
        if ($returnDate) {
            $returns = Schedule::with(['ship', 'route.origin', 'route.destination', 'route.pricings.category'])
                ->whereHas('route', function($q) use ($originID, $destinationID) {
                    // DIBALIK: Asal jadi Tujuan, Tujuan jadi Asal
                    if ($destinationID) $q->where('origin_port_id', $destinationID);
                    if ($originID) $q->where('destination_port_id', $originID);
                })
                ->whereDate('departure_time', $returnDate)
                ->where('status', 'open')
                ->where('quota_passenger_left', '>', 0)
                ->orderBy('departure_time', 'asc')
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'departures' => $departures,
                'returns'    => $returns
            ]
        ]);
    }
}