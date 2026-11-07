<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    use RefreshDatabase, BuatData;

    private function dataRegister(string $email): array
    {
        return [
            'name' => 'Siti', 'email' => $email, 'telepon' => '081111111111',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ];
    }

    public function test_register_membuat_user_pelanggan_dan_baris_pelanggan_tertaut(): void
    {
        $this->postJson('/api/auth/register', $this->dataRegister('siti@test.com'))
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'pelanggan']]);

        $user = User::where('email', 'siti@test.com')->firstOrFail();
        $this->assertSame('pelanggan', $user->role);
        $this->assertDatabaseHas('pelanggan', ['user_id' => $user->id, 'email' => 'siti@test.com', 'level' => 'Basic']);
    }

    public function test_register_menautkan_pelanggan_walkin_dengan_email_sama(): void
    {
        $walkin = Pelanggan::create([
            'nama' => 'Siti Walkin', 'email' => 'siti@test.com', 'telepon' => '082222222222', 'level' => 'Silver',
        ]);

        $this->postJson('/api/auth/register', $this->dataRegister('siti@test.com'))->assertCreated();

        $user = User::where('email', 'siti@test.com')->firstOrFail();
        $this->assertSame(1, Pelanggan::where('email', 'siti@test.com')->count());
        $this->assertSame($user->id, $walkin->fresh()->user_id);
    }

    public function test_pelanggan_ke_route_admin_ditolak_403(): void
    {
        Sanctum::actingAs($this->buatPelanggan()->user);

        $this->getJson('/api/kendaraan')->assertForbidden()->assertJson(['message' => 'Akses ditolak.']);
    }

    public function test_admin_ke_route_pelanggan_ditolak_403(): void
    {
        Sanctum::actingAs($this->buatAdmin());

        $this->getJson('/api/akun/transaksi')->assertForbidden()->assertJson(['message' => 'Akses ditolak.']);
    }

    public function test_login_salah_enam_kali_kena_throttle_429(): void
    {
        $this->buatAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => 'admin@test.com', 'password' => 'salah'])
                ->assertStatus(422);
        }

        $this->postJson('/api/auth/login', ['email' => 'admin@test.com', 'password' => 'salah'])
            ->assertStatus(429);
    }
}
