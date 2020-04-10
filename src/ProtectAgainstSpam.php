<?php

namespace Spatie\Honeypot;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Honeypot\SpamResponder\SpamResponder;
use Symfony\Component\HttpFoundation\Response;

class ProtectAgainstSpam
{
    /** @var \Spatie\Honeypot\SpamResponder\SpamResponder */
    protected $spamResponder;

    public function __construct(SpamResponder $spamResponder)
    {
        $this->spamResponder = $spamResponder;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('honeypot.enabled')) {
            return $next($request);
        }

        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        $nameFieldName = config('honeypot.name_field_name');

        if (config('honeypot.randomize_name_field_name')) {
            $nameFieldName = $this->getRandomizedNameFieldName($nameFieldName, $request->all());
        }

        if ($this->continueWithMissingHoneypotFields($request, $nameFieldName)) {
            return $next($request);
        }

        if (! $request->has($nameFieldName)) {
            return $this->respondToSpam($request, $next);
        }

        $honeypotValue = $request->get($nameFieldName);

        if (! empty($honeypotValue)) {
            return $this->respondToSpam($request, $next);
        }

        $validFrom = $request->get(config('honeypot.valid_from_field_name'));

        if (! $validFrom) {
            return $this->respondToSpam($request, $next);
        }

        try {
            $time = new EncryptedTime($validFrom);
        } catch (Exception $decryptException) {
            $time = null;
        }

        if (! $time || $time->isFuture()) {
            return $this->respondToSpam($request, $next);
        }

        return $next($request);
    }

    private function getRandomizedNameFieldName($nameFieldName, $requestFields): ?String
    {
        return collect($requestFields)->filter(function ($value, $key) use ($nameFieldName) {
            return Str::startsWith($key, $nameFieldName);
        })->keys()->first();
    }

    protected function respondToSpam(Request $request, Closure $next): Response
    {
        event(new SpamDetected($request));

        return $this->spamResponder->respond($request, $next);
    }

    private function continueWithMissingHoneypotFields(Request $request, ?string $nameFieldName): bool
    {
        return config('honeypot.check_if_honeypot_fields_are_missing') === false
            && $request->missing($nameFieldName)
            && $request->missing(config('honeypot.valid_from_field_name'));
    }
}
