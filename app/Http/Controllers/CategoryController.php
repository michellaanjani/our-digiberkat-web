<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.golang_api.url'), '/');
    }

    public function index()
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/categories");

        $categories = $response->successful() ? $response->json()['data'] : [];

        return view('categories.index', compact('categories'));
    }

    public function show($id)
    {
        $categoryRes = Http::timeout(10)->get("{$this->baseUrl}/categories");

        if (!$categoryRes->successful()) {
            Session::flash('error', 'Gagal terhubung ke server kategori.');
            return redirect()->route('categories.index');
        }

        $categoryData = $categoryRes->json()['data'] ?? [];
        $category = collect($categoryData)->firstWhere('id', (int)$id);

        if (!$category) {
            Session::flash('error', 'Kategori tidak ditemukan.');
            return redirect()->route('categories.index');
        }

        $products = [];
        $productRes = Http::timeout(10)->get("{$this->baseUrl}/products/{$id}");

        if ($productRes->successful()) {
            $products = $productRes->json()['data'] ?? [];
        } else {
            Session::flash('warning', 'Gagal mengambil produk untuk kategori ini.');
        }

        return view('categories.show', compact('category', 'products'));
    }

    // public function show($id)
    // {
    //     $categoryRes = Http::timeout(10)->get("{$this->baseUrl}/categories");

    //     if (!$categoryRes->successful()) {
    //         Session::flash('error', 'Gagal terhubung ke server kategori.');
    //         return redirect()->route('categories.index');
    //     }

    //     $categoryData = $categoryRes->json()['data'] ?? [];
    //     $category = collect($categoryData)->firstWhere('id', (int)$id);

    //     if (!$category) {
    //         Session::flash('error', 'Kategori tidak ditemukan.');
    //         return redirect()->route('categories.index');
    //     }

    //     $productRes = Http::timeout(10)->get("{$this->baseUrl}/products/{$id}");

    //     if (!$productRes->successful()) {
    //         Session::flash('warning', 'Gagal mengambil produk untuk kategori ini.');
    //         $products = [];
    //     } else {
    //         $products = $productRes->json()['data'] ?? [];

    //         if (empty($products)) {
    //             Session::flash('info', 'Kategori ini belum memiliki produk.');
    //         }
    //     }

    //     return view('categories.show', compact('category', 'products'));
    // }
}
