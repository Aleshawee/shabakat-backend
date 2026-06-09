<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $categories = Category::where('is_active', true)
            ->orderBy('points', 'asc')
            ->get();

        return response()->json(['categories' => $categories]);
    }
}
