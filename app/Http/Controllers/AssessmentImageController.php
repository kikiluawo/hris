<?php

namespace App\Http\Controllers;

use App\Support\AssessmentImageStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentImageController extends Controller
{
    public function __construct(
        private readonly AssessmentImageStore $store,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'file', 'max:6144', 'extensions:jpg,jpeg,png,webp'],
        ], [
            'image.required' => 'Pilih berkas gambar dulu.',
            'image.image' => 'Berkas harus berupa gambar JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 6 MB.',
            'image.extensions' => 'Format gambar harus JPG, PNG, atau WebP.',
        ], [
            'image' => 'gambar',
        ]);

        $name = $this->store->store($data['image']);

        return response()->json([
            'name' => $name,
            'url' => AssessmentImageStore::url($name),
        ]);
    }
}
