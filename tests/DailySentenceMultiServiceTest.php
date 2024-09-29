<?php

use Cody\Rhinoshield\DailySentenceMultiService;
use Cody\Rhinoshield\Factory;
use PHPUnit\Framework\TestCase;


/**
 * @method expectException(string $class)
 * @method assertNotEmpty(string $sentence)
 */
class DailySentenceMultiServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetSentenceFromMetaphorpsum(): void
    {
        $provider = Factory::createProvider('metaphorpsum');
        $service = new DailySentenceMultiService($provider);
        $sentence = $service->getSentence();
        $this->assertNotEmpty($sentence);
    }

    /**
     * @throws \Exception
     */
    public function testGetSentenceFromItsthisforthat(): void
    {
        $provider = Factory::createProvider('itsthisforthat');
        $service = new DailySentenceMultiService($provider);
        $sentence = $service->getSentence();
        $this->assertNotEmpty($sentence);
    }

    /**
     * @throws Exception
     */
    public function testUnknownProviderType(): void
    {
        $this->expectException(Exception::class);
        Factory::createProvider('unknown');
    }
}