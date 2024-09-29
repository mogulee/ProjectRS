<?php

namespace Cody\Rhinoshield;

use Exception;

class DailySentenceService
{
    /**
     * @throws Exception
     */
    public function getSentence(): string
    {
        $url = "http://metaphorpsum.com/sentences/3";

        try {
            $response = file_get_contents($url);
            if ($response === FALSE) {
                throw new Exception("無法取得 API 回應");
            }
            return $response;
        } catch (Exception $e) {
            throw new Exception("API 請求失敗: " . $e->getMessage());
        }
    }
}

