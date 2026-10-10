<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DeepL API Integration für automatische Übersetzungen.
 *
 * Konfiguration (in .env):
 *   DEEPL_ENABLED=true
 *   DEEPL_API_KEY=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx:fx
 *
 * Kostenfrei bis 500.000 Zeichen/Monat (Free Tier).
 */
class DeepLService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('shop.deepl_api_key', '');

        // Free API Key endet auf ":fx" → andere URL
        $this->apiUrl = str_ends_with($this->apiKey, ':fx')
            ? 'https://api-free.deepl.com/v2/translate'
            : 'https://api.deepl.com/v2/translate';
    }

    /**
     * Text von Deutsch nach Englisch übersetzen.
     */
    public function translateToEnglish(string $text): ?string
    {
        return $this->translate($text, 'DE', 'EN-GB');
    }

    /**
     * Allgemeine Übersetzungsfunktion.
     */
    public function translate(string $text, string $sourceLang, string $targetLang): ?string
    {
        if (!config('shop.deepl_enabled') || empty($this->apiKey)) {
            return null;
        }

        if (empty(trim($text))) {
            return '';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "DeepL-Auth-Key {$this->apiKey}",
            ])->post($this->apiUrl, [
                'text'        => [$text],
                'source_lang' => strtoupper($sourceLang),
                'target_lang' => strtoupper($targetLang),
                'tag_handling' => 'html', // HTML-Tags beibehalten
            ]);

            if ($response->successful()) {
                return $response->json('translations.0.text');
            }

            Log::warning('DeepL Übersetzung fehlgeschlagen', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('DeepL API Fehler: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Artikel-Titel und Beschreibung automatisch übersetzen.
     * Gibt [title_en, description_en] zurück oder [null, null] bei Fehler.
     */
    public function translateArticle(string $title, string $description): array
    {
        $titleEn = $this->translateToEnglish($title);
        $descEn  = $this->translateToEnglish($description);

        return [$titleEn, $descEn];
    }
}
