<?php

/**
 * Language Helper
 * 
 * Fungsi untuk mendapatkan terjemahan berdasarkan kunci dan lokal.
 * Prioritas: Cache Redis -> Database -> Fallback ke kunci asli
 */

if (!function_exists('__lang')) {
    /**
     * Get translation by key and locale
     *
     * @param string $key Translation key
     * @param string|null $locale Locale code (e.g., 'id', 'en'). If null, uses current session locale.
     * @return string Translated text or original key if not found
     */
    function __lang(string $key, ?string $locale = null): string
    {
        // Get locale from session or parameter
        if ($locale === null) {
            $session = \Config\Services::session();
            $locale = $session->get('locale') ?? 'id';
        }

        // Try to get from cache (Redis)
        $cache = \Config\Services::cache();
        $cacheKey = "translation_{$locale}_{$key}";
        $translated = $cache->get($cacheKey);

        if ($translated !== false) {
            return $translated;
        }

        // Get from database
        $translationModel = new \App\Models\TranslationModel();
        $translated = $translationModel->getTranslation($key, $locale);

        if ($translated) {
            // Cache the result for future use (TTL: 1 hour)
            $cache->save($cacheKey, $translated, 3600);
            return $translated;
        }

        // Fallback to key itself
        return $key;
    }
}

if (!function_exists('set_locale')) {
    /**
     * Set locale in session
     *
     * @param string $locale Locale code
     * @return bool Success status
     */
    function set_locale(string $locale): bool
    {
        $session = \Config\Services::session();
        $validLocales = ['id', 'en']; // Add more as needed
        
        if (in_array($locale, $validLocales)) {
            $session->set('locale', $locale);
            
            // Clear translation cache for this locale to force refresh
            $cache = \Config\Services::cache();
            $cache->deleteMatching("translation_{$locale}_*");
            
            return true;
        }
        
        return false;
    }
}

if (!function_exists('get_current_locale')) {
    /**
     * Get current locale from session
     *
     * @return string Current locale code
     */
    function get_current_locale(): string
    {
        $session = \Config\Services::session();
        return $session->get('locale') ?? 'id';
    }
}
