<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->get('/api/contacts/' . $contact->id,[
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'first_name' => 'test',
                'last_name' => 'test',
                'email' => 'test@example.com',
                'phone' => '111111'
            ]
        ]);
    }
    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->get('/api/contacts/' . $contact->id + 1,[
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found']
            ]
        ]);
    }
    public function testGetOtherUserContact()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->get('/api/contacts/' . $contact->id,[
            'Authorization' => 'test2'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found']
            ]
        ]);
    }
    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->put('/api/contacts/' . $contact->id,
        [
            'first_name' => 'test2',
            'last_name' => 'test2',
            'email' => 'test2@example.com',
            'phone' => '111112'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'first_name' => 'test2',
                'last_name' => 'test2',
                'email' => 'test2@example.com',
                'phone' => '111112'
            ]
        ]);
    }
    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->put('/api/contacts/' . $contact->id,
        [
            'first_name' => '',
            'last_name' => 'test2',
            'email' => 'test2@example.com',
            'phone' => '111112'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'first_name' => ['The first name field is required.'],
            ]
        ]);
    }
    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->delete('/api/contacts/' . $contact->id,[],[
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }
    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class,ContactSeeder::class]);
        $contact = Contact::limit(1)->first();
        $this->delete('/api/contacts/' . $contact->id + 1,[],[
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found']
            ]
        ]);
    }
    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class,SearchSeeder::class]);
        $response = $this->get('/api/contacts?name=first', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();


        // Log::channel('stderr')->info(json_encode($response,JSON_PRETTY_PRINT));
        self::assertEquals(10,count($response['data']));
        self::assertEquals(20,$response['meta']['total']);
    }
    public function testSearchByLastName()
    {
        $this->seed([UserSeeder::class,SearchSeeder::class]);
        $response = $this->get('/api/contacts?name=last', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();


        // Log::channel('stderr')->info(json_encode($response,JSON_PRETTY_PRINT));
        self::assertEquals(10,count($response['data']));
        self::assertEquals(20,$response['meta']['total']);
    }
    public function testSearchByEmail()
    {
        $this->seed([UserSeeder::class,SearchSeeder::class]);
        $response = $this->get('/api/contacts?email=test', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();


        // Log::channel('stderr')->info(json_encode($response,JSON_PRETTY_PRINT));
        self::assertEquals(10,count($response['data']));
        self::assertEquals(20,$response['meta']['total']);
    }
    public function testSearchByPhone()
    {
        $this->seed([UserSeeder::class,SearchSeeder::class]);
        $response = $this->get('/api/contacts?phone=11111', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();


        // Log::channel('stderr')->info(json_encode($response,JSON_PRETTY_PRINT));
        self::assertEquals(10,count($response['data']));
        self::assertEquals(20,$response['meta']['total']);
    }
    public function testSearchNotFound()
    {
        $this->seed([UserSeeder::class,SearchSeeder::class]);
        $response = $this->get('/api/contacts?name=wrong', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();


        // Log::channel('stderr')->info(json_encode($response,JSON_PRETTY_PRINT));
        self::assertEquals(0,count($response['data']));
        self::assertEquals(0,$response['meta']['total']);
    }
    public function testSearchWithPage()
    {
        $this->seed([UserSeeder::class,SearchSeeder::class]);
        $response = $this->get('/api/contacts?size=5&page=2', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();


        Log::channel('stderr')->info(json_encode($response,JSON_PRETTY_PRINT));
        self::assertEquals(5,count($response['data']));
        self::assertEquals(20,$response['meta']['total']);
        self::assertEquals(2,$response['meta']['current_page']);
    }
}
