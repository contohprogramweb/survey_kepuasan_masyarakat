<?php

namespace App\Services;

use Endroid\QrCode\Builder\QrCodeBuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;

/**
 * QRCodeService - Service untuk generate QR Code dengan Endroid QR Code 5.x
 */
class QRCodeService
{
    /**
     * Ukuran preset dalam pixel
     */
    private const SIZE_PRESETS = [
        'S' => 200,
        'M' => 300,
        'L' => 400,
        'XL' => 500,
    ];
    
    /**
     * Generate QR Code
     * 
     * @param string $data Data yang akan di-encode ke QR Code (URL)
     * @param string $format Format output: svg, png, pdf
     * @param string $sizePreset Ukuran preset: S, M, L, XL
     * @param string|null $logoPath Path ke logo instansi (opsional)
     * @param string|null $label Label teks di bawah QR Code
     * @return array ['content' => string binary, 'mime_type' => string]
     */
    public function generate(
        string $data,
        string $format = 'png',
        string $sizePreset = 'M',
        ?string $logoPath = null,
        ?string $label = null
    ): array {
        // Dapatkan ukuran dari preset
        $size = self::SIZE_PRESETS[$sizePreset] ?? self::SIZE_PRESETS['M'];
        
        // Buat builder
        $builder = new \Endroid\QrCode\Builder\QrCodeBuilder();
        
        $result = $builder
            ->writer($this->getWriter($format))
            ->writerOptions([])
            ->qrCode(
                (new \Endroid\QrCode\QrCode\QrCode())
                    ->setData($data)
                    ->setSize($size)
                    ->setMargin(10)
                    ->setEncoding(new Encoding('UTF-8'))
                    ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                    ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
                    ->setForegroundColor(new Color(0, 0, 0))
                    ->setBackgroundColor(new Color(255, 255, 255))
            )
            ->label($label ? $this->createLabel($label) : null)
            ->logo($logoPath && file_exists($logoPath) ? $this->createLogo($logoPath) : null)
            ->build();
        
        return [
            'content' => $result->getString(),
            'mime_type' => $this->getMimeType($format),
        ];
    }
    
    /**
     * Generate QR Code dan simpan ke file
     * 
     * @param string $data Data URL
     * @param string $filePath Path file tujuan
     * @param string $format Format output
     * @param string $sizePreset Ukuran preset
     * @param string|null $logoPath Path logo
     * @param string|null $label Label teks
     * @return bool True jika berhasil
     */
    public function generateToFile(
        string $data,
        string $filePath,
        string $format = 'png',
        string $sizePreset = 'M',
        ?string $logoPath = null,
        ?string $label = null
    ): bool {
        try {
            $result = $this->generate($data, $format, $sizePreset, $logoPath, $label);
            
            // Pastikan direktori ada
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            return file_put_contents($filePath, $result['content']) !== false;
        } catch (\Exception $e) {
            log_message('error', '[QRCodeService] Generate to file error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get writer berdasarkan format
     */
    private function getWriter(string $format): object
    {
        return match(strtolower($format)) {
            'svg' => new SvgWriter(),
            'pdf' => new \Endroid\QrCode\Writer\PdfWriter(),
            default => new PngWriter(),
        };
    }
    
    /**
     * Get MIME type berdasarkan format
     */
    private function getMimeType(string $format): string
    {
        return match(strtolower($format)) {
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => 'image/png',
        };
    }
    
    /**
     * Create label object
     */
    private function createLabel(string $text): Label
    {
        return Label::create($text)
            ->setTextColor(new Color(0, 0, 0))
            ->setFontPath(FCPATH . 'assets/fonts/arial.ttf')
            ->setFontSize(16);
    }
    
    /**
     * Create logo object
     */
    private function createLogo(string $path): Logo
    {
        return Logo::create($path)
            ->setResizeToWidth(80)
            ->setPunchoutBackground(true);
    }
}
