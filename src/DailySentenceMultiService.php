<?php

namespace Cody\Rhinoshield;

use Exception;

class DailySentenceMultiService
{
    private SentenceProvider $provider;

    public function __construct(SentenceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getSentence(): string
    {
        return $this->provider->getSentence();
    }
}