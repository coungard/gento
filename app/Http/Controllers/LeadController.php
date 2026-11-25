<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Http\Middleware\TrackUtm;
use App\Http\Requests\StoreLeadRequest;
use App\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    /**
     * Заявка на обратный звонок с любой страницы сайта.
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $data = $request->validated();

        QuoteRequest::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'comment' => $data['comment'] ?? 'Запрос обратного звонка',
            'status' => LeadStatus::New,
            'source' => 'callback',
            'utm' => session(TrackUtm::SESSION_KEY),
            'ip' => $request->ip(),
        ]);

        return back()->with('lead_sent', 'Заявка принята. Перезвоним в рабочее время.');
    }
}
