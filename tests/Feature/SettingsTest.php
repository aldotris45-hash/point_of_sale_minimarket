<?php

namespace Tests\Feature;

use App\Enums\RoleStatus;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        /** @var User $user */
        $user = User::factory()->createOne([
            'role' => RoleStatus::ADMIN->value,
            'password' => 'password',
        ]);
        $this->actingAs($user);
        return $user;
    }

    public function test_admin_can_update_store_information_in_settings(): void
    {
        $this->actingAsAdmin();

        $payload = [
            'store_name' => 'Toko Baru',
            'currency' => 'IDR',
            'discount_percent' => 2.5,
            'tax_percent' => 5,
            'store_address' => 'Jl. Contoh 123',
            'store_phone' => '+628123456789',
            'store_bank_account' => 'BCA 123-456-7890',
            'receipt_format' => 'INV-{YYYY}{MM}{DD}-{SEQ:4}',
        ];

        $res = $this->put(route('pengaturan.update'), $payload);
        $res->assertRedirect(route('pengaturan.index'));
        $res->assertSessionHas('success');

        // Settings are stored with JSON cast, so verify they exist and can be retrieved
        $storeName = Setting::where('key', 'store.name')->firstOrFail();
        $this->assertEquals('Toko Baru', $storeName->value);
        
        $storePhone = Setting::where('key', 'store.phone')->firstOrFail();
        $this->assertEquals('+628123456789', $storePhone->value);
        
        $storeBank = Setting::where('key', 'store.bank_account')->firstOrFail();
        $this->assertEquals('BCA 123-456-7890', $storeBank->value);
    }

    public function test_receipt_displays_updated_bank_and_store_name(): void
    {
        $this->actingAsAdmin();

        // prepare settings directly
        $settings = [
            'store.name' => 'Toko XYZ',
            'store.bank_account' => 'BCA 000-111-222',
        ];
        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val, 'type' => 'json']);
        }
        
        // Clear cache to ensure fresh values are fetched
        Cache::flush();

        // create a basic transaction for receipt
        $trx = Transaction::create([
            'user_id' => User::query()->first()->id ?? 1,
            'invoice_number' => 'R001',
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'amount_paid' => 100,
            'change' => 0,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $page = $this->get(route('transaksi.struk', ['transaction' => $trx->id]));
        $page->assertOk();
        $page->assertSee('Toko XYZ');
        $page->assertSee('BCA 000-111-222');
    }
}
