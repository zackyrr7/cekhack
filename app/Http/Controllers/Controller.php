<?php

namespace App\Http\Controllers;

use Gemini\Data\Blob;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Gemini\Enums\MimeType;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function cek(Request $request)
    {
        // Validasi untuk memastikan file yang diunggah adalah gambar
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $json = '{
            "date": "2023-11-24",
            "items": [
                {"name": "Apel", "price": 10000},
                {"name": "Pisang", "price": 8000}
            ],
            "total": 18000
        }';


        $generated = "Berikan data pengeluaran dari nota ini dalam format json
        nama barang,harga,jumlah,total kemudian total pembelian bahasa indonesia dengan format json seperti '$json'";
        $data = $request->file('image');

        // Menggunakan base64_encode pada isi file gambar
        $imageData = base64_encode(file_get_contents($data->getRealPath()));

        // Coba untuk menghasilkan konten dengan Gemini
        try {
            $result = Gemini::geminiFlash()->generateContent([
                $generated,
                new Blob(MimeType::IMAGE_JPEG, data: $imageData)
            ]);

            $text = $result->text();

            return response()->json([
                $text
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses gambar: ' . $e->getMessage()
            ], 500);
        }
    }
}
