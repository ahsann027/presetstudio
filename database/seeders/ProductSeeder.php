<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [name, category, description, count, price, featured, new, cover, before, after]
        $items = [
            ['Warm Aesthetic Pack', 'Warm', 'Nuansa hangat keemasan untuk foto pantai, kafe, dan potret.', 15, 30000, true, false, 1018, 1015, 1016],
            ['Moody Dark Pack', 'Moody', 'Tone gelap dan kontras kuat untuk foto jalanan dan arsitektur.', 10, 25000, false, true, 1011, 1012, 1013],
            ['Bright & Clean Pack', 'Cerah', 'Foto tampak cerah, bersih, dan natural untuk feed produk dan food.', 12, 28000, true, false, 1024, 1025, 1026],
            ['Vintage Film Pack', 'Vintage', 'Efek film analog dengan grain halus dan warna pudar yang nostalgik.', 8, 27000, false, false, 1039, 1040, 1041],
            ['Golden Hour Pack', 'Warm', 'Maksimalkan cahaya senja agar kulit dan langit terlihat hidup.', 10, 32000, false, true, 1050, 1051, 1052],
            ['Pastel Dream Pack', 'Cerah', 'Warna pastel lembut yang pas untuk konten estetik di Instagram.', 14, 29000, false, false, 1060, 1061, 1062],
        ];
        $inactiveName = 'Preset Lama (nonaktif)';

        $this->removeEmptySeedDuplicates([
            ...array_column($items, 0),
            $inactiveName,
        ]);

        foreach ($items as [$name, $category, $description, $count, $price, $featured, $new, $coverId, $beforeId, $afterId]) {
            $product = Product::firstOrNew(['name' => $name]);
            $product->fill([
                'category'       => $category,
                'description'    => $description,
                'price'          => $price,
                'preset_count'   => $count,
                'supported_apps' => 'Lightroom Mobile & Desktop',
                'file_format'    => 'DNG, XMP',
                'file_size'      => rand(8, 30) . ' MB',
                'is_featured'    => $featured,
                'is_new'         => $new,
                'is_active'      => true,
            ]);

            if (blank($product->image_url) || $this->isSeedImage($product->image_url)) {
                $product->image_url = "https://picsum.photos/id/{$coverId}/800/600";
            }

            $product->save();
            $this->replaceSeedGallery($product, $beforeId, $afterId);
        }

        $inactive = Product::firstOrNew(['name' => $inactiveName]);
        $inactive->fill([
            'category'  => 'Warm',
            'price'     => 10000,
            'is_active' => false,
        ]);
        if (blank($inactive->image_url) || $this->isSeedImage($inactive->image_url)) {
            $inactive->image_url = 'https://picsum.photos/id/1018/800/600';
        }
        $inactive->save();

        $first = Product::where('name', 'Warm Aesthetic Pack')->firstOrFail();

        Testimonial::updateOrCreate(
            ['name' => 'Rani, Makassar'],
            ['quote' => 'Foto liburanku langsung kelihatan profesional. Pasangnya gampang banget!', 'product_id' => $first->id, 'is_active' => true],
        );
        Testimonial::updateOrCreate(
            ['name' => 'Dimas, Gowa'],
            ['quote' => 'Bayar lewat QRIS, dua menit file sudah bisa diunduh. Mantap.', 'is_active' => true],
        );
        Testimonial::updateOrCreate(
            ['name' => 'Salsa, Maros'],
            ['quote' => 'Hasilnya konsisten di semua foto feed Instagram-ku.', 'is_active' => true],
        );
    }

    private function removeEmptySeedDuplicates(array $names): void
    {
        $groups = Product::query()
            ->whereIn('name', $names)
            ->withCount(['orders', 'images', 'testimonials'])
            ->get()
            ->groupBy('name');

        foreach ($groups as $products) {
            $canonical = $products
                ->sortBy('id')
                ->sortByDesc('images_count')
                ->sortByDesc('orders_count')
                ->first();

            foreach ($products->where('id', '!=', $canonical->id) as $duplicate) {
                $generatedCover = blank($duplicate->image_url) || $this->isSeedImage($duplicate->image_url);
                $generatedDownload = blank($duplicate->download_url)
                    || $duplicate->download_url === 'https://example.com/contoh-file-preset.zip';

                if ($duplicate->orders_count === 0
                    && $duplicate->images_count === 0
                    && $duplicate->testimonials_count === 0
                    && $generatedCover
                    && $generatedDownload) {
                    $duplicate->delete();
                }
            }
        }
    }

    private function replaceSeedGallery(Product $product, int $beforeId, int $afterId): void
    {
        $generatedImages = $product->images()
            ->where(function ($query) {
                $query->where('before_url', 'like', 'https://picsum.photos/seed/%')
                    ->orWhere('after_url', 'like', 'https://picsum.photos/seed/%')
                    ->orWhere('before_url', 'like', 'https://placehold.co/%')
                    ->orWhere('after_url', 'like', 'https://placehold.co/%');
            })
            ->get();

        foreach ($generatedImages as $generatedImage) {
            $generatedImage->delete();
        }

        if (! $product->images()->exists()) {
            $product->images()->create([
                'before_url' => "https://picsum.photos/id/{$beforeId}/800/600",
                'after_url'  => "https://picsum.photos/id/{$afterId}/800/600",
                'caption'    => 'Contoh hasil',
                'sort_order' => 1,
            ]);
        }
    }

    private function isSeedImage(?string $url): bool
    {
        return Str::startsWith((string) $url, [
            'https://picsum.photos/seed/',
            'https://placehold.co/',
        ]);
    }
}
