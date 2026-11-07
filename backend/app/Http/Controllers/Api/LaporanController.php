<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public function pendapatan(Request $request)
    {
        return response()->json($this->hitung($this->tahun($request)));
    }

    public function export(Request $request)
    {
        $laporan = $this->hitung($this->tahun($request));

        return response()->streamDownload(function () use ($laporan) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Bulan', 'Pendapatan Sewa', 'Pendapatan Denda', 'Total', 'Jumlah Transaksi']);
            foreach ($laporan['data'] as $row) {
                fputcsv($out, [self::BULAN[$row['bulan']], $row['sewa'], $row['denda'], $row['total'], $row['jumlah_transaksi']]);
            }
            $data = collect($laporan['data']);
            fputcsv($out, ['TOTAL', $data->sum('sewa'), $data->sum('denda'), $laporan['total'], $data->sum('jumlah_transaksi')]);
            fclose($out);
        }, "laporan-pendapatan-{$laporan['tahun']}.csv", ['Content-Type' => 'text/csv']);
    }

    private function tahun(Request $request): int
    {
        $request->validate(['tahun' => 'nullable|integer|min:2000|max:2100']);

        return (int) $request->input('tahun', Carbon::now()->year);
    }

    private function hitung(int $tahun): array
    {
        // Dikelompokkan di PHP (bukan SQL) agar portabel MySQL/sqlite.
        $bayar = Pembayaran::where('status', 'berhasil')
            ->whereYear('tanggal_bayar', $tahun)
            ->get(['jenis', 'jumlah', 'tanggal_bayar'])
            ->groupBy(fn ($p) => (int) Carbon::parse($p->tanggal_bayar)->format('n'));

        $transaksi = Transaksi::where('status', '!=', 'dibatalkan')
            ->whereYear('created_at', $tahun)
            ->get(['created_at'])
            ->groupBy(fn ($t) => (int) $t->created_at->format('n'));

        $data = [];
        for ($b = 1; $b <= 12; $b++) {
            $rows  = $bayar->get($b, collect());
            $sewa  = (int) $rows->where('jenis', 'sewa')->sum('jumlah');
            $denda = (int) $rows->where('jenis', 'denda')->sum('jumlah');

            $data[] = [
                'bulan'            => $b,
                'sewa'             => $sewa,
                'denda'            => $denda,
                'total'            => $sewa + $denda,
                'jumlah_transaksi' => $transaksi->get($b, collect())->count(),
            ];
        }

        return [
            'tahun' => $tahun,
            'data'  => $data,
            'total' => array_sum(array_column($data, 'total')),
        ];
    }
}
