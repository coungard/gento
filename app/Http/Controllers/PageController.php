<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __invoke(Request $request, Page $page): View
    {
        abort_unless(
            $page->is_active && ($page->published_at === null || $page->published_at->isPast()),
            404,
        );

        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
