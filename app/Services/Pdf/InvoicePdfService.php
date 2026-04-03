<?php

namespace App\Services\Pdf;

use App\Helpers\Terbilang;
use App\Models\Transaction;
use App\Services\Settings\SettingsServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

class InvoicePdfService implements InvoicePdfServiceInterface
{
    public function __construct(
        private readonly SettingsServiceInterface $settings,
    ) {}

    // ── Public API ─────────────────────────────────────────────────

    /** {@inheritDoc} */
    public function buildViewData(
        Transaction $transaction,
        bool $withStamp = false,
        bool $withSignature = false,
        bool $withTerbilang = false,
    ): array {
        $transaction->loadMissing(['details.product', 'user', 'customer']);

        $pdfStampPath    = $this->resolveAbsolutePath($this->settings->storeStampPath());
        $pdfStampBase64  = $withStamp ? $this->rotateStampToBase64($pdfStampPath) : null;

        $data = [
            'transaction'        => $transaction,
            'store_name'         => $this->settings->storeName(),
            'store_address'      => $this->settings->storeAddress(),
            'store_phone'        => $this->settings->storePhone(),
            'store_bank_account' => $this->settings->storeBankAccount(),
            'store_logo'         => $this->settings->storeLogoPath(),
            'currency'           => $this->settings->currency(),
            'discount_percent'   => $this->settings->discountPercent(),
            'tax_percent'        => $this->settings->taxPercent(),
            'with_signature'     => $withSignature,
            'with_stamp'         => $withStamp,
            'store_signature'    => $this->settings->storeSignaturePath(),
            'store_stamp'        => $this->settings->storeStampPath(),
            'is_pdf'             => true,
            // Resolved absolute paths for DomPDF
            'pdf_logo_path'      => $this->resolveAbsolutePath($this->settings->storeLogoPath()),
            'pdf_signature_path' => $this->resolveAbsolutePath($this->settings->storeSignaturePath()),
            'pdf_stamp_path'     => $pdfStampPath,
            'pdf_stamp_base64'   => $pdfStampBase64,
            // Random offset for organic stamp look
            'stamp_margin_top'   => rand(0, 30) . 'px',
            'stamp_margin_left'  => rand(-70, -10) . 'px',
        ];

        if ($withTerbilang) {
            $data['terbilang'] = Terbilang::rupiah((float) $transaction->total);
        }

        return $data;
    }

    /** {@inheritDoc} */
    public function receiptPdf(Transaction $transaction, bool $withStamp = false, bool $withSignature = false): DomPDF
    {
        $data = $this->buildViewData($transaction, $withStamp, $withSignature, false);

        return Pdf::loadView('transactions.receipt', $data)
            ->setPaper([0, 0, 226.77, 841.89], 'portrait') // ~80mm x ~297mm
            ->setOption(['isRemoteEnabled' => true]);
    }

    /** {@inheritDoc} */
    public function invoicePdf(Transaction $transaction, bool $withStamp = false, bool $withSignature = false): DomPDF
    {
        $data = $this->buildViewData($transaction, $withStamp, $withSignature, true);

        return Pdf::loadView('transactions.print-invoice', $data)
            ->setPaper('a4', 'landscape')
            ->setOption(['isRemoteEnabled' => true]);
    }

    /** {@inheritDoc} */
    public function fakturPdf(Transaction $transaction, bool $withStamp = false, bool $withSignature = false): DomPDF
    {
        $data = $this->buildViewData($transaction, $withStamp, $withSignature, true);

        return Pdf::loadView('transactions.print-faktur', $data)
            ->setPaper('a4', 'landscape')
            ->setOption(['isRemoteEnabled' => true]);
    }

    // ── Private helpers ────────────────────────────────────────────

    /**
     * Resolve a relative storage path to an absolute filesystem path.
     * Path stored as 'storage/assets/images/xxx.png' → 'storage/app/public/assets/images/xxx.png'.
     */
    private function resolveAbsolutePath(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        $stripped = str_replace('storage/', '', $relativePath);
        $absolute = storage_path('app/public/' . $stripped);

        return file_exists($absolute) ? $absolute : null;
    }

    /**
     * Rotate a stamp image by a random angle and return as base64 data URI.
     * Returns null if GD is unavailable or the file doesn't exist.
     */
    private function rotateStampToBase64(?string $absolutePath): ?string
    {
        if (empty($absolutePath) || !function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
            $imgStr = file_get_contents($absolutePath);
            $source = imagecreatefromstring($imgStr);

            if ($source === false) {
                return null;
            }

            $angle = rand(-25, 25);

            imagealphablending($source, false);
            imagesavealpha($source, true);

            $transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
            $rotated = imagerotate($source, $angle, $transparent);

            imagealphablending($rotated, false);
            imagesavealpha($rotated, true);

            ob_start();
            imagepng($rotated);
            $imageData = ob_get_clean();

            $base64 = 'data:image/png;base64,' . base64_encode($imageData);

            imagedestroy($source);
            imagedestroy($rotated);

            return $base64;
        } catch (\Exception $e) {
            // Ignore — use normal path fallback
            return null;
        }
    }
}
