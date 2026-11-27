<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user() && $this->user()->is_admin);
    }

    public function rules(): array
    {
        // Hanya gambar JPG/PNG/WEBP maksimal 2 MB. SVG sengaja tidak diizinkan (bisa memuat script).
        $image = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

        return [
            'name'           => ['required', 'string', 'max:150'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'price'          => ['required', 'numeric', 'min:0', 'max:99999999'],
            'category'       => ['required', 'string', 'max:50'],
            'preset_count'   => ['required', 'integer', 'min:1', 'max:1000'],
            'supported_apps' => ['nullable', 'string', 'max:150'],
            'file_format'    => ['nullable', 'string', 'max:50'],
            'file_size'      => ['nullable', 'string', 'max:50'],
            'install_guide'  => ['nullable', 'string', 'max:5000'],
            'download_url'   => ['nullable', 'string', 'max:500'],

            // Foto utama (sampul)
            'main_image'     => ['nullable', ...$image],

            // Galeri sebelum-sesudah: tiap baris = sepasang foto. Jika salah satu diisi, yang lain wajib.
            'gallery'                  => ['nullable', 'array', 'max:8'],
            'gallery.*.before'         => ['nullable', 'required_with:gallery.*.after', ...$image],
            'gallery.*.after'          => ['nullable', 'required_with:gallery.*.before', ...$image],
            'gallery.*.caption'        => ['nullable', 'string', 'max:100'],

            // Id foto galeri lama yang akan dihapus (halaman edit)
            'remove_images'   => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'              => 'nama preset',
            'description'       => 'deskripsi',
            'price'             => 'harga',
            'category'          => 'gaya/kategori',
            'preset_count'      => 'jumlah preset',
            'main_image'        => 'foto utama',
            'gallery.*.before'  => 'foto sebelum',
            'gallery.*.after'   => 'foto sesudah',
            'gallery.*.caption' => 'keterangan foto',
        ];
    }

    public function messages(): array
    {
        return [
            'main_image.max'          => 'Foto utama maksimal 2 MB.',
            'gallery.*.before.max'    => 'Foto sebelum maksimal 2 MB.',
            'gallery.*.after.max'     => 'Foto sesudah maksimal 2 MB.',
            '*.mimes'                 => ':attribute harus berformat JPG, PNG, atau WEBP.',
            'gallery.*.before.required_with' => 'Foto sebelum wajib diisi jika foto sesudah dipilih.',
            'gallery.*.after.required_with'  => 'Foto sesudah wajib diisi jika foto sebelum dipilih.',
        ];
    }
}
