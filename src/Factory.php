<?php

namespace Cody\Rhinoshield;

use Exception;

class Factory
{
    /**
     * @throws Exception
     */
    public static function createProvider(string $providerType): SentenceProvider
    {
        switch ($providerType) {
            case 'metaphorpsum':
                return new MetaphorpsumProvider();
            case 'itsthisforthat':
                return new ItsthisforthatProvider();
            default:
                throw new Exception("未知的提供者類型");
        }
    }
}