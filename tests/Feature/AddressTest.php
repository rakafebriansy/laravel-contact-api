<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AddressTest extends TestCase
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
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();

        $this->post('/api/contacts/'.$contact->id.'/addresses',
        [
            'street' => 'test',
            'city' => 'test',
            'province' => 'test',
            'country' => 'test',
            'postal_code' => '213123',
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(201)->assertJson([
            'data' => [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => '213123',
            ]
        ]);
    }
    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();

        $this->post('/api/contacts/'.$contact->id.'/addresses',
        [
            'street' => 'test',
            'city' => 'test',
            'province' => 'test',
            'postal_code' => '213123',
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'country' => ['The country field is required.'],
            ]
        ]);
    }
    public function testCreateNotFound()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();

        $this->post('/api/contacts/'.($contact->id + 1).'/addresses',
        [
            'street' => 'test',
            'city' => 'test',
            'province' => 'test',
            'country' => 'test',
            'postal_code' => '213123',
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found'],
            ]
        ]);
    }
}
