<?php

namespace Cody\Rhinoshield;

use Exception;

class ItsthisforthatProvider implements SentenceProvider
{
    private $url = "https://itsthisforthat.com/api.php?text";

    public function getSentence(): string
    {
        try {
            $response = file_get_contents($this->url);
            if ($response === FALSE) {
                throw new Exception("無法取得 API 回應");
            }
            return $response;
        } catch (Exception $e) {
            throw new Exception("API 請求失敗: " . $e->getMessage());
        }
    }
}