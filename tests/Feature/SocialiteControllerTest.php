<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SocialiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_redirects_to_google(): void
    {
        // Mock Socialite::driver('google')->stateless()->redirect() to avoid actual redirects
        Socialite::shouldReceive('driver->stateless->redirect')->andReturn(route('googleLoginCallback'));

        $response = $this->get('/');

        $response->assertStatus(200);

        $domain = config('session.domain');

        if( $domain != 'daliy_report' )
        {
            $response->assertStatus(200);

            $response->assertRedirect(route('googleLoginCallback'));
        }
    }

    public function test_google_login_callback_handles_user_authentication(): void
    {
        // Mock Socialite::driver('google')->stateless()->user() to simulate a user
        $user = User::factory()->make();

        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $response = $this->get('/auth/google/callback');

        $domain = config('session.domain');

        if( $domain != 'daliy_report' )
        {
            $response->assertStatus(200);

            if(User::isValidateEmail($user))
            {
                $response->assertViewIs('welcome');
            }
            else
            {
                $response->assertViewIs('errors.unauthorized');
            }
        }
        else
        {
            $response->assertStatus(200);
        }
    }
}
