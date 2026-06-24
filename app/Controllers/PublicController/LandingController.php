<?php

namespace App\Controllers\PublicController;

use App\Controllers\BaseController;
use App\Models\UnitLayananModel;
use App\Models\PeriodeModel;
use App\Models\InstansiModel;

class LandingController extends BaseController
{
    /**
     * Get translation helper method
     * 
     * @param string $key Translation key
     * @return string Translated text
     */
    protected function getTranslation(string $key): string
    {
        return __lang($key);
    }

    /**
     * Display the public landing page
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        // Load models
        $instansiModel = new InstansiModel();
        $unitModel = new UnitLayananModel();
        $periodeModel = new PeriodeModel();

        // Get instansi data (assuming single instansi for now)
        $instansi = $instansiModel->first() ?? [
            'nama' => $this->getTranslation('default_instansi_name'),
            'deskripsi' => $this->getTranslation('default_instansi_description'),
            'logo' => 'default_logo.png',
            'alamat' => $this->getTranslation('default_instansi_address'),
            'telepon' => $this->getTranslation('default_instansi_phone'),
            'email' => $this->getTranslation('default_instansi_email'),
            'website' => $this->getTranslation('default_instansi_website')
        ];

        // Get active periods
        $activePeriode = $periodeModel->where('status', 'active')
                                       ->orWhere('tanggal_selesai >=', date('Y-m-d'))
                                       ->findAll();

        $activePeriodeIds = array_column($activePeriode, 'id');

        // Get unit layanan with active periods
        $unitLayanan = [];
        if (!empty($activePeriodeIds)) {
            $unitLayanan = $unitModel->whereIn('periode_id', $activePeriodeIds)
                                     ->where('status', 'active')
                                     ->findAll();
        }

        // Prepare SEO data
        $seoData = [
            'title' => $this->getTranslation('page_title') . ' - ' . $instansi['nama'],
            'description' => $instansi['deskripsi'] ?? $this->getTranslation('page_description'),
            'keywords' => $this->getTranslation('page_keywords'),
            'canonical' => current_url(),
            'og_image' => base_url('uploads/' . ($instansi['logo'] ?? 'default_logo.png')),
        ];

        // Prepare JSON-LD structured data
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => $instansi['nama'],
            'description' => $instansi['deskripsi'],
            'url' => base_url(),
            'logo' => base_url('uploads/' . ($instansi['logo'] ?? 'default_logo.png')),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $instansi['telepon'],
                'email' => $instansi['email'],
                'contactType' => 'customer service'
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $instansi['alamat'],
                'addressCountry' => 'ID'
            ]
        ];

        $data = [
            'instansi' => $instansi,
            'unitLayanan' => $unitLayanan,
            'seo' => $seoData,
            'jsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            'currentLocale' => get_current_locale(),
            'supportedLocales' => ['id' => 'Indonesia', 'en' => 'English']
        ];

        return view('public/landing', $data);
    }

    /**
     * Change language/locale
     * 
     * @param string $locale Locale code
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function setLanguage($locale)
    {
        $validLocales = ['id', 'en'];
        
        if (in_array($locale, $validLocales)) {
            set_locale($locale);
        }

        // Redirect back to the previous page or home
        return redirect()->back();
    }

    /**
     * Display public dashboard (transparency)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function dashboard()
    {
        // This would typically show aggregated IKM results
        $data = [
            'title' => $this->getTranslation('dashboard_title'),
            'currentLocale' => get_current_locale(),
            'supportedLocales' => ['id' => 'Indonesia', 'en' => 'English']
        ];

        return view('public/dashboard', $data);
    }
}
