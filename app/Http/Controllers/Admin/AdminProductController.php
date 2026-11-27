<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $keyword  = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->withCount('orders')
            ->search($keyword)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'keyword'));
    }

    public function create()
    {
        $product    = new Product(['is_active' => true, 'preset_count' => 1]);
        $categories = $this->categories();

        return view('admin.products.create', compact('product', 'categories'));
    }

    public function store(ProductRequest $request)
    {
        $data = $this->productData($request);

        if ($request->hasFile('main_image')) {
            $data['image_url'] = $this->storeImage($request->file('main_image'), 'products');
        }

        $product = Product::create($data);
        $this->storeGallery($product, $request);

        return redirect()->route('admin.products.index')->with('status', 'Preset berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load('images');
        $categories = $this->categories();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $this->productData($request);

        if ($request->hasFile('main_image')) {
            $this->deleteImage($product->image_url);                       // hapus sampul lama
            $data['image_url'] = $this->storeImage($request->file('main_image'), 'products');
        }

        $product->update($data);

        // Hapus pasangan foto galeri yang dicentang (dibatasi ke milik produk ini saja).
        $ids = (array) $request->input('remove_images', []);
        $product->images()->whereIn('id', $ids)->get()->each(function ($image) {
            $this->deleteImage($image->before_url);
            $this->deleteImage($image->after_url);
            $image->delete();
        });

        $this->storeGallery($product, $request);

        return redirect()->route('admin.products.index')->with('status', 'Preset berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Pesanan memakai produk ini (relasi restrict) -> sarankan dinonaktifkan agar riwayat pesanan aman.
        if ($product->orders()->exists()) {
            return back()->with('error', 'Preset ini sudah punya pesanan dan tidak bisa dihapus. Nonaktifkan saja lewat menu Edit.');
        }

        foreach ($product->images as $image) {
            $this->deleteImage($image->before_url);
            $this->deleteImage($image->after_url);
        }
        $this->deleteImage($product->image_url);
        $product->delete(); // baris product_images ikut terhapus (cascade)

        return redirect()->route('admin.products.index')->with('status', 'Preset dihapus.');
    }

    /* ---------------- helper ---------------- */

    /** Data teks produk dari form (tanpa file), checkbox diubah menjadi boolean. */
    private function productData(ProductRequest $request): array
    {
        $data = $request->safe()->except(['main_image', 'gallery', 'remove_images']);

        foreach (['is_active', 'is_featured', 'is_new'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return $data;
    }

    /** Simpan pasangan foto baru dari form ke tabel product_images. */
    private function storeGallery(Product $product, Request $request): void
    {
        $order = (int) $product->images()->max('sort_order');

        foreach ((array) $request->file('gallery', []) as $index => $files) {
            $before = $files['before'] ?? null;
            $after  = $files['after'] ?? null;

            if (! $before || ! $after) {
                continue;
            }

            $product->images()->create([
                'before_url' => $this->storeImage($before, 'products/gallery'),
                'after_url'  => $this->storeImage($after, 'products/gallery'),
                'caption'    => $request->input("gallery.{$index}.caption"),
                'sort_order' => ++$order,
            ]);
        }
    }

    /** Simpan ke storage/app/public/{folder}; kembalikan path publik "storage/..." (butuh php artisan storage:link). */
    private function storeImage(UploadedFile $file, string $folder): string
    {
        return 'storage/' . $file->store($folder, 'public'); // nama file diacak otomatis
    }

    /** Hapus file hanya jika memang hasil upload kita (storage/products/...); URL eksternal tidak disentuh. */
    private function deleteImage(?string $url): void
    {
        if ($url && Str::startsWith($url, 'storage/products/')) {
            Storage::disk('public')->delete(Str::after($url, 'storage/'));
        }
    }

    /** Daftar gaya yang sudah ada, untuk saran (datalist) di form. */
    private function categories()
    {
        return Product::query()->select('category')->distinct()->orderBy('category')->pluck('category');
    }
}
