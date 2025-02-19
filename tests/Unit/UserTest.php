<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notifiable;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Models\User as BaseUser;
use InternetGuru\LaravelUser\Traits\SocialiteAuth;
use InternetGuru\LaravelUser\Traits\TokenAuth;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCreation()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function testUserRole()
    {
        $user = User::factory()->create([
            'role' => Role::ADMIN,
        ]);

        $this->assertEquals('admin', $user->role->value);
    }

    public function testUserTraits()
    {
        $this->assertInstanceOf(BaseUser::class, new User);
        $traits = class_uses(BaseUser::class);

        $this->assertContains(Authenticatable::class, $traits);
        $this->assertContains(Authorizable::class, $traits);
        $this->assertContains(HasFactory::class, $traits);
        $this->assertContains(Notifiable::class, $traits);
        $this->assertContains(SocialiteAuth::class, $traits);
        $this->assertContains(TokenAuth::class, $traits);
    }
}
