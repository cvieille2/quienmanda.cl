<?php

namespace App\Http\Controllers;

use App\Enums\OutboundClickSource;
use App\Models\Profile;
use App\Services\OutboundClickService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutboundClickController extends Controller
{
    public function __construct(private OutboundClickService $clicks) {}

    public function track(Request $request, Profile $profile): RedirectResponse
    {
        $destination = $this->clicks->resolveDestinationUrl($profile);

        if (! $destination) {
            abort(404, 'Este perfil no tiene un destino público todavía.');
        }

        $source = OutboundClickSource::fromRequestValue($request->query('src'))
            ?? OutboundClickSource::Profile;

        $this->clicks->recordClick($profile, $destination, $source, [
            'session_id' => $request->session()->getId(),
            'referrer' => $request->headers->get('referer'),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
        ]);

        return redirect()->away($destination);
    }
}
