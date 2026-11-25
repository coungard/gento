<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Services\QuoteCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly QuoteCart $cart) {}

    public function store(Request $request, Part $part): RedirectResponse
    {
        abort_unless($part->is_active, 404);

        $this->cart->add($part, $request->integer('qty', 1));

        return back()->with('status', '«'.$part->name.'» добавлена в заявку.');
    }

    public function update(Request $request, Part $part): RedirectResponse
    {
        $this->cart->update($part, $request->integer('qty', 1));

        return back();
    }

    public function destroy(Part $part): RedirectResponse
    {
        $this->cart->remove($part);

        return back()->with('status', 'Позиция удалена из заявки.');
    }
}
