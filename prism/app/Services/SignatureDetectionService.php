<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Client for the Python microservice's signature endpoints.
 *
 * Degrades gracefully like MarketScopingService: every call returns null when
 * the service is down so signing can fall back to manual confirmation —
 * a signature upload must never hard-fail because port 5001 is offline.
 */
class SignatureDetectionService
{
    private const SERVICE_URL = 'http://localhost:5001';

    private bool $available = true;

    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Detect signature regions in a photo of a signed document.
     *
     * @param  string $imageContents raw image bytes (jpeg/png)
     * @return array{detected: bool, confidence: float, boxes: array, blurred_image_b64?: string}|null
     *         null when the service is unreachable or errored
     */
    public function detect(string $imageContents): ?array
    {
        try {
            $response = Http::timeout(25)
                ->attach('image', $imageContents, 'document.jpg')
                ->post(self::SERVICE_URL . '/detect-signature');

            if (!$response->successful() || !is_bool($response->json('detected'))) {
                $this->available = false;
                return null;
            }

            return $response->json();
        } catch (\Throwable) {
            $this->available = false;
            return null;
        }
    }

    /**
     * Blur caller-specified regions (manual fallback / re-blur).
     *
     * @param  string $imageContents raw image bytes
     * @param  array  $boxes         [{x,y,w,h}] — fractions of image size when <= 1.0
     * @return string|null           blurred JPEG bytes, or null when unavailable
     */
    public function blurRegion(string $imageContents, array $boxes): ?string
    {
        try {
            $response = Http::timeout(25)->post(self::SERVICE_URL . '/blur-region', [
                'image_b64' => base64_encode($imageContents),
                'boxes'     => $boxes,
            ]);

            $blurred = $response->successful() ? $response->json('blurred_image_b64') : null;
            if (!is_string($blurred) || $blurred === '') {
                $this->available = false;
                return null;
            }

            return base64_decode($blurred);
        } catch (\Throwable) {
            $this->available = false;
            return null;
        }
    }
}
