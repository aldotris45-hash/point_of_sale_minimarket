<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SettingsService implements SettingsServiceInterface
{
    public function __construct(private readonly CacheRepository $cache) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "settings:" . $key;
        return $this->cache->rememberForever($cacheKey, function () use ($key, $default) {
            if (!Schema::hasTable('settings')) {
                return $default;
            }
            $setting = Setting::query()->where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    public function set(string $key, mixed $value, ?string $group = null, ?string $description = null): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
                'type' => 'json',
                'updated_by' => Auth::id(),
            ]
        );

        $this->cache->forget("settings:" . $key);
        $this->cache->forever("settings:" . $key, $setting->value);

        return $setting;
    }

    public function discountPercent(): float
    {
        $val = $this->get('pos.discount_percent', 0);
        return is_numeric($val) ? (float) $val : (float) ($val['percent'] ?? 0);
    }

    public function taxPercent(): float
    {
        $val = $this->get('pos.tax_percent', 0);
        return is_numeric($val) ? (float) $val : (float) ($val['percent'] ?? 0);
    }

    public function currency(): string
    {
        $val = $this->get('pos.currency', 'IDR');
        return is_string($val) ? $val : (string) ($val['code'] ?? 'IDR');
    }

    public function storeName(): string
    {
        $val = $this->get('store.name', config('app.name', 'POS'));
        return is_string($val) ? $val : (string) ($val['value'] ?? config('app.name', 'POS'));
    }

    public function storeAddress(): string
    {
        $val = $this->get('store.address', '');
        return is_string($val) ? $val : (string) ($val['value'] ?? '');
    }

    public function storePhone(): string
    {
        $val = $this->get('store.phone', '');
        return is_string($val) ? $val : (string) ($val['value'] ?? '');
    }

    public function storeLogoPath(): ?string
    {
        $val = $this->get('store.logo_path', null);
        if ($val === null) return null;
        return is_string($val) ? $val : (string) ($val['value'] ?? null);
    }

    public function receiptNumberFormat(): string
    {
        // Example format: INV-{YYYY}{MM}{DD}-{SEQ:6}
        $val = $this->get('pos.receipt_format', 'INV-{YYYY}{MM}{DD}-{SEQ:6}');
        return is_string($val) ? $val : (string) ($val['value'] ?? 'INV-{YYYY}{MM}{DD}-{SEQ:6}');
    }
}
