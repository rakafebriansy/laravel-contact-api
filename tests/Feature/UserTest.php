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
}
