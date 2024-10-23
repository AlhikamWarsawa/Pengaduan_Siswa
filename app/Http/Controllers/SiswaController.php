<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Siswa::latest();

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('pelapor', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('terlapor', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('laporan', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->has('kelas') && $request->kelas != 'all') {
            $query->where('kelas', $request->kelas);
        }

        $siswas = $query->paginate(5);

        // Get unique classes for the filter
        $classes = Siswa::distinct('kelas')->pluck('kelas');

        return view('siswa.index', compact('siswas', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view ('siswa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'pelapor' => 'required|string',
            'terlapor' => 'required|string',
            'kelas' => 'required|string',
            'laporan' => 'required|string',
            'bukti' => 'required|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $image = $request->file('bukti');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $buktiPath = '/images/' . $name;
        }

        $siswa = new Siswa([
            'pelapor' => $request->pelapor,
            'terlapor' => $request->terlapor,
            'kelas' => $request->kelas,
            'laporan' => $request->laporan,
            'bukti' => $buktiPath,
            'status' => 'Menunggu',
        ]);

        $siswa->save();

        return redirect()->route('siswa.index')->with('success', 'Pengaduan berhasil ditambahkan.');
    }

    public function selesai($id)
    {
        $pengaduan = Siswa::findOrFail($id);
        $pengaduan->status = 'Selesai';
        $pengaduan->save();

        return redirect()->route('siswa.index')->with('success', 'Status pengaduan berhasil diubah menjadi Selesai.');
    }

    public function destroy(string $id)
    {
        $pengaduan = Siswa::findOrFail($id);

        // Delete the image if it exists
        if ($pengaduan->bukti) {
            Storage::delete('public/' . $pengaduan->bukti);
        }

        // Delete the pengaduan record
        $pengaduan->delete();

        return redirect()->route('guru.index')->with('success', 'Kasus berhasil dihapus');
    }
}
