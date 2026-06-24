<?php

namespace App\Helpers;

/**
 * IKM Helper - Helper functions untuk Aplikasi IKM
 * Berisi fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

if (!function_exists('formatIKMScore')) {
    /**
     * Format IKM Score dengan predikat sesuai PermenPANRB 14/2017
     *
     * @param float $score Nilai IKM (0-100)
     * @return array ['score' => float, 'predicate' => string, 'color' => string]
     */
    function formatIKMScore(float $score): array
    {
        $predicates = [
            ['min' => 88.31, 'max' => 100.00, 'predicate' => 'A', 'label' => 'Sangat Baik', 'color' => 'success'],
            ['min' => 66.14, 'max' => 88.30, 'predicate' => 'B', 'label' => 'Baik', 'color' => 'info'],
            ['min' => 43.97, 'max' => 66.13, 'predicate' => 'C', 'label' => 'Kurang', 'color' => 'warning'],
            ['min' => 0.00, 'max' => 43.96, 'predicate' => 'D', 'label' => 'Sangat Kurang', 'color' => 'danger'],
        ];

        foreach ($predicates as $p) {
            if ($score >= $p['min'] && $score <= $p['max']) {
                return [
                    'score' => number_format($score, 2),
                    'predicate' => $p['predicate'],
                    'label' => $p['label'],
                    'color' => $p['color'],
                ];
            }
        }

        return [
            'score' => number_format($score, 2),
            'predicate' => '-',
            'label' => 'Tidak Valid',
            'color' => 'secondary',
        ];
    }
}

if (!function_exists('generateNIK')) {
    /**
     * Generate NIK dummy untuk testing
     *
     * @return string 16 digit NIK
     */
    function generateNIK(): string
    {
        $provinsi = str_pad(random_int(11, 99), 2, '0', STR_PAD_LEFT);
        $kabupaten = str_pad(random_int(1, 99), 2, '0', STR_PAD_LEFT);
        $kecamatan = str_pad(random_int(1, 99), 2, '0', STR_PAD_LEFT);
        $tanggal = str_pad(random_int(1, 31), 2, '0', STR_PAD_LEFT);
        $bulan = str_pad(random_int(1, 12), 2, '0', STR_PAD_LEFT);
        $tahun = substr(date('Y'), -2);
        $unique = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return "{$provinsi}{$kabupaten}{$kecamatan}{$tanggal}{$bulan}{$tahun}{$unique}";
    }
}

if (!function_exists('encryptSensitiveData')) {
    /**
     * Encrypt data sensitif sesuai UU PDP
     *
     * @param string $data Data yang akan dienkripsi
     * @return string Encrypted data (base64)
     */
    function encryptSensitiveData(string $data): string
    {
        $key = config('Encryption')->key;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('decryptSensitiveData')) {
    /**
     * Decrypt data sensitif sesuai UU PDP
     *
     * @param string $encryptedData Data terenkripsi (base64)
     * @return string|false Decrypted data atau false jika gagal
     */
    function decryptSensitiveData(string $encryptedData)
    {
        $key = config('Encryption')->key;
        $data = base64_decode($encryptedData);
        
        if ($data === false) {
            return false;
        }
        
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}

if (!function_exists('maskingData')) {
    /**
     * Masking data untuk tampilan (UU PDP compliance)
     *
     * @param string $data Data asli
     * @param int $visibleAwal Jumlah karakter yang ditampilkan di awal
     * @param int $visibleAkhir Jumlah karakter yang ditampilkan di akhir
     * @return string Data yang sudah di-mask
     */
    function maskingData(string $data, int $visibleAwal = 4, int $visibleAkhir = 4): string
    {
        $length = strlen($data);
        
        if ($length <= ($visibleAwal + $visibleAkhir)) {
            return str_repeat('*', $length);
        }
        
        $masked = substr($data, 0, $visibleAwal);
        $masked .= str_repeat('*', $length - $visibleAwal - $visibleAkhir);
        $masked .= substr($data, -$visibleAkhir);
        
        return $masked;
    }
}

if (!function_exists('formatNIP')) {
    /**
     * Format NIP dengan separator
     *
     * @param string $nip NIP tanpa separator (18 digit)
     * @return string NIP dengan format: XXXX-XXX-XXX-XXX
     */
    function formatNIP(string $nip): string
    {
        $nip = preg_replace('/[^0-9]/', '', $nip);
        
        if (strlen($nip) !== 18) {
            return $nip;
        }
        
        return chunk_split($nip, 4, '-');
    }
}

if (!function_exists('calculateAge')) {
    /**
     * Hitung umur dari tanggal lahir
     *
     * @param string $birthDate Tanggal lahir (YYYY-MM-DD)
     * @return int Umur dalam tahun
     */
    function calculateAge(string $birthDate): int
    {
        $birth = new \DateTime($birthDate);
        $today = new \DateTime('today');
        
        return $today->diff($birth)->y;
    }
}

if (!function_exists('getSurveyStatus')) {
    /**
     * Get status label untuk survey
     *
     * @param string $status Status code
     * @return array ['label' => string, 'color' => string]
     */
    function getSurveyStatus(string $status): array
    {
        $statuses = [
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'published' => ['label' => 'Published', 'color' => 'success'],
            'closed' => ['label' => 'Closed', 'color' => 'danger'],
            'archived' => ['label' => 'Archived', 'color' => 'info'],
        ];
        
        return $statuses[$status] ?? ['label' => ucfirst($status), 'color' => 'secondary'];
    }
}

if (!function_exists('generateConsentToken')) {
    /**
     * Generate unique token untuk consent
     *
     * @return string Token unik
     */
    function generateConsentToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

if (!function_exists('validatePhoneNumber')) {
    /**
     * Validate nomor telepon Indonesia
     *
     * @param string $phone Nomor telepon
     * @return bool True jika valid
     */
    function validatePhoneNumber(string $phone): bool
    {
        // Format: 08xx-xxxx-xxxx atau +62-8xx-xxxx-xxxx
        $pattern = '/^(\+62|62|0)8[1-9][0-9]{1,2}[0-9]{6,8}$/';
        
        return (bool) preg_match($pattern, preg_replace('/[\s\-\(\)]/', '', $phone));
    }
}

if (!function_exists('formatPhoneNumber')) {
    /**
     * Format nomor telepon Indonesia
     *
     * @param string $phone Nomor telepon
     * @return string Formatted phone number
     */
    function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        
        if (strlen($phone) === 11 || strlen($phone) === 12) {
            return preg_replace('/^0(\d{4})(\d{4,})(\d{3,4})$/', '0$1-$2-$3', $phone);
        }
        
        return $phone;
    }
}

if (!function_exists('bytesToHumanReadable')) {
    /**
     * Convert bytes ke human readable format
     *
     * @param int $bytes Size in bytes
     * @param int $precision Decimal places
     * @return string Human readable size
     */
    function bytesToHumanReadable(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('generateQRCode')) {
    /**
     * Generate QR Code data URL untuk survey link
     *
     * @param string $url URL survey
     * @param int $size Size QR code
     * @return string Data URL QR code
     */
    function generateQRCode(string $url, int $size = 200): string
    {
        $apiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($url);
        
        $qrCode = file_get_contents($apiUrl);
        
        return 'data:image/png;base64,' . base64_encode($qrCode);
    }
}

if (!function_exists('getMicrosecondTimestamp')) {
    /**
     * Get timestamp dengan microsecond untuk audit logging
     *
     * @return string Timestamp format: Y-m-d H:i:s.u
     */
    function getMicrosecondTimestamp(): string
    {
        return date('Y-m-d H:i:s.') . substr(microtime(), 2, 6);
    }
}
