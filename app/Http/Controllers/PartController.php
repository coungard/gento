<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Part;
use Illuminate\Contracts\View\View;

class PartController extends Controller
{
    /**
     * Карточка запчасти. Маршрут привязан через scopeBindings(),
     * поэтому запчасть обязана принадлежать разделу из URL.
     */
    public function show(Category $category, Part $part): View
    {
        abort_unless($part->is_active && $category->is_active, 404);

        $part->load(['category', 'generatorModels.brand', 'crossNumbers']);

        $related = Part::query()
            ->active()
            ->where('category_id', $part->category_id)
            ->whereKeyNot($part->getKey())
            ->with('category')
            ->limit(4)
            ->get();

        $breadcrumbs = collect();

        if ($part->category?->parent) {
            $breadcrumbs->push($part->category->parent);
        }

        return view('catalog.part', [
            'part' => $part,
            'related' => $related,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
