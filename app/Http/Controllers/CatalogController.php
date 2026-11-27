<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * F1 + F8: daftar preset dengan pencarian (?q=) dan filter gaya (?category=).
     * Jika dipanggil lewat fetch() dari JavaScript, hanya potongan HTML kartu yang dikirim balik.
     */
    public function index(Request $request)
    {
        $keyword  = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        $products = Product::active()
            ->with('images')
            ->category($category)
            ->search($keyword)
            ->orderByDesc('is_featured')   // terlaris tampil paling atas
            ->latest()
            ->get();

        // Permintaan dari JavaScript: kirim kartu saja + jumlah hasil.
        // "Vary" mencegah browser menampilkan potongan HTML ini saat tombol Back ditekan.
        if ($request->ajax()) {
            return response()
                ->view('catalog._grid', compact('products'))
                ->header('X-Total', $products->count())
                ->header('Vary', 'X-Requested-With');
        }

        $categories = Product::active()
            ->select('category')->distinct()->orderBy('category')
            ->pluck('category');

        $testimonials = Testimonial::active()->latest()->take(3)->get();

        return response()
            ->view('catalog.index', compact('products', 'categories', 'keyword', 'category', 'testimonials'))
            ->header('Vary', 'X-Requested-With');
    }

    /** F2: detail preset + galeri sebelum-sesudah. */
    public function show(int $id)
    {
        $product = Product::active()->with('images')->findOrFail($id); // nonaktif -> 404

        $related = Product::active()->with('images')
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->latest()->take(3)->get();

        $testimonials = Testimonial::active()
            ->where('product_id', $product->id)->latest()->take(3)->get();

        return view('catalog.show', compact('product', 'related', 'testimonials'));
    }

    /** F4: halaman statis cara beli, metode bayar, cara pasang preset, dan kontak. */
    public function info()
    {
        return view('catalog.info');
    }
}
