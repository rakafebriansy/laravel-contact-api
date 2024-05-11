<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContactTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM addresses');
        DB::delete('DELETE FROM contacts');
        DB::delete('DELETE FROM users');
    }
    public function testCreateSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts',
        [
            'first_name' => 'Raka',
            'last_name' => 'Febrian',
            'email' => 'raka@gmail.com',
            'phone' => '081242526'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(201)->assertJson([
            'data' => [
                'first_name' => 'Raka',
                'last_name' => 'Febrian',
                'email' => 'raka@gmail.com',
                'phone' => '081242526'
            ]
        ]);
    }
    public function testCreateFailed()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts',
        [
            'first_name' => '',
            'last_name' => 'Febrian',
            'email' => 'raka',
            'phone' => '081242526'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'first_name' => ['The first name field is required.'],
                'email' => ['The email field must be a valid email address.'],
            ]
        ]);
    }
    public function testCreateUnauthorized()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts',
        [
            'first_name' => '',
            'last_name' => 'Febrian',
            'email' => 'raka',
            'phone' => '081242526'
        ],
        [
            'Authorization' => 'wrong'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => ['unauthorized'],
            ]
        ]);
    }
}
