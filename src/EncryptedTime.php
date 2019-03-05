<?php

namespace Spatie\Honeypot;

use Carbon\Carbon;

class EncryptedTime
{
    /** @var string */
    protected $encryptedTime;

    /**
     * @param \Carbon\Carbon|\Carbon\CarbonInterface $carbon
     *
     * @return static
     */
    public static function create($carbon)
    {
        $encryptedTime = app('encrypter')->encrypt($carbon->timestamp);

        return new static($encryptedTime);
    }

    public function __construct(string $encryptedTime)
    {
        $this->encryptedTime = $encryptedTime;

        $timestamp = app('encrypter')->decrypt($encryptedTime);

        $this->carbon = Carbon::createFromTimestamp($timestamp);
    }

    public function isFuture(): bool
    {
        return $this->carbon->isFuture();
    }

    public function __toString()
    {
        return $this->encryptedTime;
    }
}
