<?php

use Cody\Rhinoshield\DailySentenceService;
use PHPUnit\Framework\TestCase;

class DailySentenceServiceTest extends TestCase
{
    public function testGetSentence()
    {
        $service = new DailySentenceService();

        try {
            $sentence = $service->getSentence();
            $this->assertIsString($sentence);
            $this->assertNotEmpty($sentence);
        } catch (Exception $e) {
            $this->fail("Exception thrown: " . $e->getMessage());
        }
    }
}
