<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AudioBuku;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudioBukuTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_200()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_catalog_page_returns_200()
    {
        AudioBuku::factory()->create();
        $response = $this->get('/katalog-audio');
        $response->assertStatus(200);
    }

    public function test_catalog_shows_empty_state()
    {
        $response = $this->get('/katalog-audio');
        $response->assertSee('Buku tidak ditemukan');
    }

    public function test_book_detail_page_returns_200()
    {
        $book = AudioBuku::factory()->create();
        $response = $this->get("/katalog-audio/{$book->id}");
        $response->assertStatus(200);
        $response->assertSee($book->judul);
    }

    public function test_login_page_returns_200()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_register_page_returns_200()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'role' => 'user',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/user/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'role' => 'user',
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/user/dashboard');
    }

    public function test_login_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'role' => 'user',
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_page_requires_auth()
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    public function test_404_page_is_custom()
    {
        $response = $this->get('/this-page-does-not-exist');
        $response->assertStatus(404);
    }

    public function test_read_assist_page_returns_200()
    {
        $response = $this->get('/read-assist');
        $response->assertStatus(200);
    }

    public function test_admin_dashboard_shows_real_stats()
    {
        Admin::create([
            'nama' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->count(2)->create();
        AudioBuku::factory()->count(3)->create(['audio_status' => 'completed']);
        AudioBuku::factory()->count(1)->create(['audio_status' => 'pending']);
        AudioBuku::factory()->count(1)->create(['audio_status' => 'failed']);

        $this->post('/login', [
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('5'); // total books
        $response->assertSee('3'); // audio completed
        $response->assertSee('Ringkasan Audio');
        $response->assertSee('progres tersimpan');
    }

    public function test_user_dashboard_shows_real_stats()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        AudioBuku::factory()->count(4)->create();
        AudioBuku::factory()->count(2)->create([
            'user_id' => $user->id,
            'audio_status' => 'completed',
        ]);

        $this->post('/login', [
            'role' => 'user',
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response = $this->get('/user/dashboard');
        $response->assertStatus(200);
        $response->assertSee('6'); // total books
        $response->assertSee('2'); // my uploads & completed
        $response->assertSee('Ringkasan Mendengar');
    }

    public function test_login_does_not_create_account_for_hardcoded_email()
    {
        // Backdoor lama: login dengan email hardcoded auto-membuat akun.
        // Setelah diperbaiki, login harus gagal & TIDAK membuat akun baru.
        $response = $this->post('/login', [
            'role' => 'user',
            'email' => 'muwarisin@gmail.com',
            'password' => 'Aris1234',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'muwarisin@gmail.com']);
    }

    public function test_login_is_rate_limited()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        // throttle:5,1 → maksimal 5 percobaan per menit.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'role' => 'user',
                'email' => $user->email,
                'password' => 'wrongpass',
            ]);
        }

        $response = $this->post('/login', [
            'role' => 'user',
            'email' => $user->email,
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(429);
    }

    public function test_password_reset_token_expires()
    {
        $user = User::factory()->create();

        $token = PasswordResetToken::create([
            'email' => $user->email,
            'token' => 'expired-token-abc',
            'role' => 'user',
            'created_at' => now()->subMinutes(120), // lebih dari TTL (60 menit)
        ]);

        $response = $this->get('/reset-password/expired-token-abc');
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_password_reset_token_is_valid_within_ttl()
    {
        $user = User::factory()->create();

        $token = PasswordResetToken::create([
            'email' => $user->email,
            'token' => 'fresh-token-abc',
            'role' => 'user',
            'created_at' => now(),
        ]);

        $response = $this->get('/reset-password/fresh-token-abc');
        $response->assertStatus(200);
    }

    public function test_owner_can_edit_and_update_their_own_book()
    {
        $user = User::factory()->create();
        $book = AudioBuku::factory()->create([
            'user_id' => $user->id,
            'judul' => 'Judul Awal',
        ]);

        $response = $this->withSession([
            'auth_id' => $user->id,
            'auth_role' => 'user',
            'auth_name' => $user->name,
        ])->get("/katalog-audio/{$book->id}/edit");

        $response->assertStatus(200);

        $updateResponse = $this->withSession([
            'auth_id' => $user->id,
            'auth_role' => 'user',
            'auth_name' => $user->name,
        ])->put("/katalog-audio/{$book->id}", [
            'title' => 'Judul Baru Diubah',
            'description' => 'Deskripsi baru',
        ]);

        $updateResponse->assertRedirect("/katalog-audio/{$book->id}");
        $this->assertDatabaseHas('audio_buku', [
            'id' => $book->id,
            'judul' => 'Judul Baru Diubah',
        ]);
    }

    public function test_non_owner_cannot_edit_book()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = AudioBuku::factory()->create([
            'user_id' => $owner->id,
            'judul' => 'Judul Milik Owner',
        ]);

        $response = $this->withSession([
            'auth_id' => $otherUser->id,
            'auth_role' => 'user',
            'auth_name' => $otherUser->name,
        ])->get("/katalog-audio/{$book->id}/edit");

        $response->assertRedirect("/katalog-audio/{$book->id}");
        $response->assertSessionHasErrors('audio');
    }
}
