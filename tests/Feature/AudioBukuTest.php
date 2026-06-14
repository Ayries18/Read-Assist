<?php

namespace Tests\Feature;

use App\Models\AudioBuku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudioBookTest extends TestCase
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
}
