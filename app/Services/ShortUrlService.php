<?php

namespace App\Services;

/**
 * ShortUrlService - Service untuk URL shortener dengan salt + base62 encoding
 */
class ShortUrlService
{
    /**
     * Salt untuk hashing (sebaiknya disimpan di environment variable)
     */
    private string $salt;
    
    /**
     * Karakter untuk base62 encoding
     */
    private const BASE62_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    public function __construct()
    {
        // Gunakan salt dari config atau generate random jika tidak ada
        $this->salt = env('URL_SHORTENER_SALT', bin2hex(random_bytes(16)));
    }
    
    /**
     * Generate short URL code dari data asli
     * 
     * @param string $originalUrl URL asli yang akan dipendekkan
     * @param int|null $idQr ID QR Code (untuk uniqueness)
     * @return string Short URL code (base62 encoded)
     */
    public function generateShortCode(string $originalUrl, ?int $idQr = null): string
    {
        // Combine URL dengan salt dan optional ID untuk uniqueness
        $dataToHash = $originalUrl . $this->salt . ($idQr ?? time() . random_int(1000, 9999));
        
        // Hash dengan SHA-256
        $hash = hash('sha256', $dataToHash);
        
        // Ambil 8 karakter pertama dari hash (cukup untuk ~218 trillion combinations)
        $hashInt = hexdec(substr($hash, 0, 8));
        
        // Encode ke base62
        return $this->encodeBase62($hashInt);
    }
    
    /**
     * Build full short URL dengan UTM tracking parameters
     * 
     * @param string $shortCode Kode short URL
     * @param int $idUnit ID unit layanan
     * @param int|null $idPeriode ID periode survei
     * @param string $periodeName Nama periode untuk campaign tracking
     * @return string Full URL dengan UTM parameters
     */
    public function buildTrackingUrl(
        string $shortCode,
        int $idUnit,
        ?int $idPeriode,
        string $periodeName = ''
    ): string {
        $baseUrl = site_url('q/' . $shortCode);
        
        // Tambahkan UTM tracking parameters
        $params = [
            'source' => 'qr',
            'medium' => 'poster',
            'campaign' => !empty($periodeName) ? $this->slugify($periodeName) : 'periode_' . ($idPeriode ?? $idUnit),
        ];
        
        return $baseUrl . '?' . http_build_query($params);
    }
    
    /**
     * Encode integer ke base62 string
     */
    private function encodeBase62(int $num): string
    {
        $result = '';
        $base = strlen(self::BASE62_CHARS);
        
        while ($num > 0) {
            $remainder = $num % $base;
            $result = self::BASE62_CHARS[$remainder] . $result;
            $num = intdiv($num, $base);
        }
        
        return $result ?: '0';
    }
    
    /**
     * Decode base62 string ke integer (untuk verifikasi jika diperlukan)
     */
    private function decodeBase62(string $str): int
    {
        $result = 0;
        $base = strlen(self::BASE62_CHARS);
        $length = strlen($str);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $str[$i];
            $position = strpos(self::BASE62_CHARS, $char);
            $result = $result * $base + $position;
        }
        
        return $result;
    }
    
    /**
     * Convert string ke slug format untuk UTM campaign
     */
    private function slugify(string $text): string
    {
        // Replace non-letter or digits by hyphen
        $text = preg_replace('~[^\p{L}\p{N}]+~u', '-', $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate hyphens
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);
        
        // Limit length
        return substr($text, 0, 50);
    }
    
    /**
     * Verify short code integrity (optional security check)
     */
    public function verifyShortCode(string $shortCode, string $originalUrl): bool
    {
        // Re-generate expected code dan compare
        $expectedCode = $this->generateShortCode($originalUrl);
        return hash_equals($expectedCode, $shortCode);
    }
}
