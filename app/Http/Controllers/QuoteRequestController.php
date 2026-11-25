<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Http\Middleware\TrackUtm;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\QuoteRequest;
use App\Services\QuoteCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class QuoteRequestController extends Controller
{
    public function __construct(private readonly QuoteCart $cart) {}

    /**
     * Страница заявки: состав корзины и контактная форма.
     */
    public function index(): View
    {
        return view('quote.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    /**
     * Сохранение заявки из корзины.
     */
    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()
                ->route('quote.index')
                ->with('error', 'Добавьте хотя бы одну запчасть, чтобы отправить заявку.');
        }

        $data = $request->validated();

        $quote = DB::transaction(function () use ($data, $items): QuoteRequest {
            $quote = QuoteRequest::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'comment' => $data['comment'] ?? null,
                'status' => LeadStatus::New,
                'source' => 'site',
                'utm' => session(TrackUtm::SESSION_KEY),
                'ip' => request()->ip(),
            ]);

            foreach ($items as $item) {
                $quote->items()->create([
                    'part_id' => $item['part']->getKey(),
                    'sku' => $item['part']->sku,
                    'name' => $item['part']->name,
                    'price' => $item['part']->price,
                    'qty' => $item['qty'],
                ]);
            }

            $quote->recalculateTotal();

            return $quote;
        });

        $this->cart->clear();

        return redirect()
            ->route('quote.index')
            ->with('quote_sent', $quote->number);
    }
}
