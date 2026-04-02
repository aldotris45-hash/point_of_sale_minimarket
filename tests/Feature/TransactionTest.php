<?php

namespace Tests\Feature;

use App\Enums\RoleStatus;
use App\Enums\TransactionStatus;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
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

    private function actingAsCashier(): User
    {
        /** @var User $user */
        $user = User::factory()->createOne([
            'role' => RoleStatus::CASHIER->value,
            'password' => 'password',
        ]);
        $this->actingAs($user);
        return $user;
    }

    private function createTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'user_id' => User::query()->first()->id ?? 1,
            'invoice_number' => 'INV-TEST-' . uniqid(),
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'amount_paid' => 100,
            'change' => 0,
            'payment_method' => 'cash',
            'status' => TransactionStatus::PAID->value,
        ], $overrides));
    }

    public function test_admin_can_soft_delete_transaction(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create(['stock' => 5, 'price' => 100]);
        $trx = $this->createTransaction();
        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
            'total' => 200,
        ]);

        $res = $this->delete(route('transaksi.destroy', $trx));
        $res->assertRedirect();

        // Transaction should still exist in DB but be soft-deleted
        $this->assertSoftDeleted('transactions', ['id' => $trx->id]);

        // Stock should be restored
        $product->refresh();
        $this->assertEquals(7, $product->stock);
    }

    public function test_cashier_cannot_delete_transaction(): void
    {
        $this->actingAsCashier();
        $trx = $this->createTransaction();

        $res = $this->delete(route('transaksi.destroy', $trx));
        $res->assertForbidden();
    }

    public function test_transaction_show_page_accessible(): void
    {
        $this->actingAsAdmin();
        $trx = $this->createTransaction();

        $res = $this->get(route('transaksi.show', $trx));
        $res->assertOk();
        $res->assertSee($trx->invoice_number);
    }

    public function test_transaction_receipt_page_accessible(): void
    {
        $this->actingAsAdmin();
        $trx = $this->createTransaction();

        $res = $this->get(route('transaksi.struk', $trx));
        $res->assertOk();
    }

    public function test_transaction_invoice_page_accessible(): void
    {
        $this->actingAsAdmin();
        $trx = $this->createTransaction();

        $res = $this->get(route('transaksi.invoice', $trx));
        $res->assertOk();
    }

    public function test_transaction_data_endpoint_returns_json(): void
    {
        $this->actingAsAdmin();
        $this->createTransaction(['invoice_number' => 'INV-JSON-001']);

        $res = $this->getJson(route('transaksi.data'));
        $res->assertOk();
        $res->assertJsonStructure(['data']);
    }

    public function test_soft_deleted_transaction_not_shown_in_list(): void
    {
        $this->actingAsAdmin();
        $trx = $this->createTransaction(['invoice_number' => 'INV-DEL-001']);
        $trx->delete(); // soft delete

        $res = $this->getJson(route('transaksi.data'));
        $res->assertOk();

        $data = $res->json('data');
        $invoices = array_column($data, 'invoice');
        // The soft-deleted invoice should NOT appear in the raw text
        foreach ($invoices as $inv) {
            $this->assertStringNotContainsString('INV-DEL-001', strip_tags($inv));
        }
    }
}
