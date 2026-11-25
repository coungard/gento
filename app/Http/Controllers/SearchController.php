<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $term = mb_substr(trim($request->string('q')->toString()), 0, 120);

        $parts = null;

        if ($term !== '') {
            $parts = Part::query()
                ->active()
                ->search($term)
                ->with('category')
                ->orderByDesc('is_featured')
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString();
        }

        return view('search.index', [
            'term' => $term,
            'parts' => $parts,
        ]);
    }
}
