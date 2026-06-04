<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::orderBy('name')->get()->map(fn($brand) => [
            'id'       => $brand->id,
            'name'     => $brand->name,
            'logo_url' => $brand->logo_url,
        ]);

        return response()->json($brands);
    }
}