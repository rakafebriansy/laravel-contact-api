<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
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

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class,AddressSeeder::class]);
        $address = Address::limit(1)->first();
        $this->get('/api/contacts/'.$address->contact_id.'/addresses/'.$address->id,[
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => '123123',
            ]
        ]);
    }
    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class,AddressSeeder::class]);
        $address = Address::limit(1)->first();
        $this->get('/api/contacts/'.$address->contact_id.'/addresses/'.$address->id + 1,[
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found']
            ]
        ]);
    }
    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class,AddressSeeder::class]);
        $address = Address::limit(1)->first();
        $this->put('/api/contacts/'.$address->contact_id.'/addresses/'.$address->id,
        [
            'street' => 'update',
            'city' => 'update',
            'province' => 'update',
            'country' => 'update',
            'postal_code' => '212212',
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'street' => 'update',
                'city' => 'update',
                'province' => 'update',
                'country' => 'update',
                'postal_code' => '212212',
            ]
        ]);
    }
    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class,AddressSeeder::class]);
        $address = Address::limit(1)->first();
        $this->put('/api/contacts/'.$address->contact_id.'/addresses/'.$address->id,
        [
            'street' => 'update',
            'city' => 'update',
            'province' => 'update',
            'country' => '',
            'postal_code' => '212212',
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'country' => ['The country field is required.'],
            ]
        ]);
    }
    public function testUpdateNotFound()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class,AddressSeeder::class]);
        $address = Address::limit(1)->first();
        $this->put('/api/contacts/'.$address->contact_id.'/addresses/'.$address->id + 1,
        [
            'street' => 'update',
            'city' => 'update',
            'province' => 'update',
            'country' => 'update',
            'postal_code' => '212212',
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found']
            ]
        ]);
    }
}
