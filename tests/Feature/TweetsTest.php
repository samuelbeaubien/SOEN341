<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Tweets;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TweetsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function test_user_can_view_a_login_form()
    {
        $response = $this->get('/login');
        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }
    
    /** @test */
    public function test_user_can_view_a_register_form()
    {
        $response = $this->get('/register');
        $response->assertSuccessful();
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('i-love-laravel'),
        ]);
        
        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);
        
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
    
    /** @test */
    public function test_user_cannot_view_a_login_form_when_authenticated()
    {
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/home');
    }

    /** @test */
    public function authenticated_user_can_go_to_home()
    {
        //Given we have an authenticated user
        $user = factory(User::class)->create();

        $this->actingAs($user);
        
        //testing home route
        $response = $this->call('GET', '/home');

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function unauthenticated_user_returned_to_login()
    {
        
        //testing home route
        $response = $this->call('GET', '/home');

        $this->assertEquals(302, $response->status());

        $response->assertRedirect(
            '/login'
        );
    }

    /** @test */
    public function authenticated_user_can_get_like()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $response = $this->call('GET', '/like');

        $this->assertEquals(200, $response->status());
            
    }

    /** @test */
    public function authenticated_user_can_post_tweet()
    {

        $user = factory(User::class)->create();

        $this->actingAs($user);

        $tweet = ['tweet' => 'hello'];
     
        $response = $this->call('POST', '/tweet', $tweet);

        $this->assertEquals(1, Tweets::all()->count());
        
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_tweeting()
    {

        $tweet = ['tweet' => 'hello'];
        
        $response = $this->call('POST', '/tweet', $tweet);

        $this->assertEquals(302, $response->status());
        
        $response->assertRedirect(
            '/login'
        );
    }

    /** @test */
    public function unauthenticated_user_cannot_post_tweet()
    {
        $tweet = ['tweet' => 'hello'];
    
        $response = $this->call('POST', '/tweet', $tweet);

        $this->assertEquals(0, Tweets::all()->count());
    }

    /** @test */
    public function authenticated_user_delete_tweet()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $tweet = ['tweet' => 'hello'];
    
        $response = $this->call('POST', '/tweet', $tweet);

        $this->assertEquals(1, Tweets::all()->count());

        $responseDelete = $this->call('POST','/delete/'.$user->id);

        $this->assertEquals(302, $responseDelete->status());

        $this->assertEquals(0, Tweets::all()->count());
 
    }
}

      

