<?php

namespace App\Models;

use CodeIgniter\Model;

class TranslationModel extends Model
{
    protected $table = 'translations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['key', 'locale', 'translation', 'context', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get translation by key and locale
     *
     * @param string $key Translation key
     * @param string $locale Locale code
     * @return string|null Translated text or null if not found
     */
    public function getTranslation(string $key, string $locale): ?string
    {
        $result = $this->where('key', $key)
                       ->where('locale', $locale)
                       ->first();

        return $result ? $result['translation'] : null;
    }

    /**
     * Get all translations for a specific locale
     *
     * @param string $locale Locale code
     * @return array Array of translations [key => translation]
     */
    public function getTranslationsByLocale(string $locale): array
    {
        $results = $this->where('locale', $locale)->findAll();
        
        $translations = [];
        foreach ($results as $row) {
            $translations[$row['key']] = $row['translation'];
        }

        return $translations;
    }

    /**
     * Add or update a translation
     *
     * @param string $key Translation key
     * @param string $locale Locale code
     * @param string $translation Translated text
     * @param string|null $context Context description (optional)
     * @return bool Success status
     */
    public function upsertTranslation(string $key, string $locale, string $translation, ?string $context = null): bool
    {
        $existing = $this->where('key', $key)
                         ->where('locale', $locale)
                         ->first();

        $data = [
            'key' => $key,
            'locale' => $locale,
            'translation' => $translation,
            'context' => $context
        ];

        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($existing['id'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->insert($data);
        }
    }

    /**
     * Clear cache for a specific locale
     * This should be called when translations are updated
     *
     * @param string $locale Locale code
     * @return void
     */
    public function clearCache(string $locale): void
    {
        $cache = \Config\Services::cache();
        // Note: deleteMatching requires Redis or Memcached with proper configuration
        try {
            $cache->deleteMatching("translation_{$locale}_*");
        } catch (\Exception $e) {
            // Fallback: log error or ignore if Redis not properly configured
            log_message('error', 'Failed to clear translation cache: ' . $e->getMessage());
        }
    }

    /**
     * Get supported locales
     *
     * @return array Array of supported locale codes
     */
    public function getSupportedLocales(): array
    {
        return ['id', 'en']; // Can be made dynamic based on database content
    }
}
