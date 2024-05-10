<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

//php vendor/bin/phpunit tests/Feature/UserTest.php

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM addresses');
        DB::delete('DELETE FROM contacts');
        DB::delete('DELETE FROM users');
    }
    public function testRegisterSuccess()
    {
        $this->post('/api/users',[
            'username' => 'raka',
            'password' => '12345',
            'name' => 'Raka Febrian Syahputra'
        ])->assertStatus(201)->assertJson([
            'data' => [
                'username' => 'raka',
                'name' => 'Raka Febrian Syahputra'
            ]
        ]);
    }
    public function testRegisterFailed()
    {
        $this->post('/api/users',[
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'username' => ["The username field is required."],
                'password' => ["The password field is required."],
                'name' => ["The name field is required."]
            ]
        ]);
    }
    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users',[
            'username' => 'raka',
            'password' => '12345',
            'name' => 'Raka Febrian Syahputra'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'username' => ["username already registered"],
            ]
        ]);
    }
    public function testLoginSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/users/login',[
            'username' => 'test',
            'password' => 'test',
        ])->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test',
            ],
        ]);
        $user = User::where('username','test')->first();
        self::assertNotNull($user->token);
    }
    public function testLoginFailed()
    {
        $this->post('/api/users/login',[
            'username' => 'test',
            'password' => 'test',
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => ['username or password wrong'],
            ],
        ]);
    }
    public function testLoginFailedWrongPassword()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/users/login',[
            'username' => 'test',
            'password' => '12',
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => ['username or password wrong'],
            ],
        ]);
    }
    public function testGetSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->get('/api/users/current',[
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test'
            ]
        ]);
    }
    public function testGetUnauthorized()
    {
        $this->seed(UserSeeder::class);
        $this->get('/api/users/current')
        ->assertStatus(401)->assertJson([
            'errors' => [
                'message' => [
                    'unauthorized'
                ]
            ]
        ]);
    }
    public function testGetInvalidToken()
    {
        $this->seed(UserSeeder::class);
        $this->get('/api/users/current',[
            'Authorization' => 'testwrong'
        ])
        ->assertStatus(401)->assertJson([
            'errors' => [
                'message' => [
                    'unauthorized'
                ]
            ]
        ]);
    }
    public function testUpdateNameSuccess()
    {
        $this->seed(UserSeeder::class);

        $old_user = User::where('username','test')->first();
        $this->patch('/api/users/current',
        [
            'name' => 'Raka'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'Raka'
            ]
        ]);
        $new_user = User::where('username','test')->first();
        self::assertNotEquals($old_user->name,$new_user->name);
    }
    public function testUpdatePasswordSuccess()
    {
                $this->seed(UserSeeder::class);

        $old_user = User::where('username','test')->first();
        $this->patch('/api/users/current',
        [
            'password' => 'baru'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test'
            ]
        ]);
        $new_user = User::where('username','test')->first();
        self::assertNotEquals($old_user->password,$new_user->password);
    }
    public function testUpdateFailed()
    {
        $this->seed(UserSeeder::class);

        $this->patch('/api/users/current',
        [
            'name' => 'RakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRakaRaka'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'name' => ['The name field must not be greater than 100 characters.'],
            ]
        ]);
    }
    public function testLogoutSuccess()
    {
        $this->seed(UserSeeder::class);
        
        $this->delete(uri:'/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);
        $user = User::where('username','test')->first();
        self::assertNull($user->token);
    }
    public function testLogoutFailed()
    {
        $this->seed(UserSeeder::class);
        
        $this->delete(uri:'/api/users/logout', headers:[
            'Authorization' => 'wrong'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => ['unauthorized']
            ]
        ]);
        $user = User::where('username','test')->first();
        self::assertNotNull($user->token);
    }
}
