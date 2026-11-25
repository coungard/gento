<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Part;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->active()
            ->roots()
            ->ordered()
            ->withPartCounts()
            ->with(['children' => fn ($query) => $query->active()->ordered()])
            ->get();

        $featured = Part::query()
            ->active()
            ->featured()
            ->with('category')
            ->orderBy('price')
            ->limit(8)
            ->get();

        $latest = Part::query()
            ->active()
            ->with('category')
            ->latest('id')
            ->limit(4)
            ->get();

        return view('home', [
            'categories' => $categories,
            'featured' => $featured,
            'latest' => $latest,
        ]);
    }
}
