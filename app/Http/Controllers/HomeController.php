<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        $query = Buku::query();

        // 1. Buat query pencarian berdasarkan judul, penulis, atau penerbit jika ada input 'search'
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        // 2. Tambahkan filter penerbit dan filter ketersediaan stok jika ada input 'penerbit' / 'status'
        if ($request->filled('penerbit')) {
            $query->where('penerbit', $request->input('penerbit'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'tersedia') {
                $query->where('stok', '>', 0);
            } elseif ($request->input('status') === 'habis') {
                $query->where('stok', '<=', 0);
            }
        }

        // 3. Tambahkan pengurutan data berdasarkan input 'sort' (judul, tahun, stok, default: latest)
        switch ($request->input('sort')) {
            case 'judul':
                $query->orderBy('judul', 'asc');
                break;
            case 'tahun':
                $query->orderBy('tahun_terbit', 'desc');
                break;
            case 'stok':
                $query->orderBy('stok', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $buku = $query->paginate(8)->withQueryString();

        $listPenerbit = Buku::whereNotNull('penerbit')
            ->where('penerbit', '!=', '')
            ->distinct()
            ->orderBy('penerbit')
            ->pluck('penerbit');

        // 6. Return view 'home' dengan data buku dan list penerbit
        return view('home', compact('buku', 'listPenerbit'));
    }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
    public function show(Buku $buku)
    {
        // TODO 3: Kembalikan view 'home_show' membawa data $buku
        return view('home_show', compact('buku'));
    }
}