<?php

use App\Models\User;

function livewireUpdate($test)
{
    return $test->withHeaders(['X-Livewire' => 'true'])
        ->postJson(route('default-livewire.update'), ['components' => []]);
}

it('allows 120 page requests per minute and blocks the next one', function () {
    for ($i = 0; $i < 120; $i++) {
        $this->get('/')->assertOk();
    }

    $this->get('/')
        ->assertStatus(429)
        ->assertSee('Zbyt wiele');
});

it('sends rate limit headers', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', 120)
        ->assertHeader('X-RateLimit-Remaining', 119);
});

it('sends Retry-After when the limit is exceeded', function () {
    for ($i = 0; $i < 120; $i++) {
        $this->get('/');
    }

    $this->get('/')
        ->assertStatus(429)
        ->assertHeader('Retry-After')
        ->assertHeader('X-RateLimit-Remaining', 0);
});

it('resets the limit after a minute', function () {
    for ($i = 0; $i < 120; $i++) {
        $this->get('/');
    }
    $this->get('/')->assertStatus(429);

    $this->travel(61)->seconds();

    $this->get('/')->assertOk();
});

it('counts guests separately per IP address', function () {
    for ($i = 0; $i < 120; $i++) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get('/');
    }
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get('/')->assertStatus(429);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->get('/')->assertOk();
});

it('counts authenticated users separately', function () {
    $first = User::factory()->create(['active' => true]);
    $second = User::factory()->create(['active' => true]);

    for ($i = 0; $i < 120; $i++) {
        $this->actingAs($first)->get('/dashboard/profile');
    }
    $this->actingAs($first)->get('/dashboard/profile')->assertStatus(429);

    $this->actingAs($second)->get('/dashboard/profile')->assertOk();
});

it('gives livewire requests their own limit', function () {
    livewireUpdate($this)->assertHeader('X-RateLimit-Limit', 300);
});

it('does not block livewire when the page limit is used up', function () {
    for ($i = 0; $i < 120; $i++) {
        $this->get('/');
    }
    $this->get('/')->assertStatus(429);

    livewireUpdate($this)
        ->assertHeader('X-RateLimit-Limit', 300)
        ->assertHeader('X-RateLimit-Remaining', 299);
});

it('limits auth form submissions to 10 per minute', function (string $method, string $uri) {
    $user = User::factory()->create();

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)->$method($uri)->assertStatus(302);
    }

    $this->actingAs($user)->$method($uri)->assertStatus(429);
})->with([
    'confirm password' => ['post', '/confirm-password'],
    'change password' => ['put', '/password'],
]);

it('limits guest auth form submissions to 10 per minute', function (string $uri) {
    for ($i = 0; $i < 10; $i++) {
        $this->post($uri)->assertStatus(302);
    }

    $this->post($uri)->assertStatus(429);
})->with(['/register', '/login', '/forgot-password', '/reset-password']);

it('keeps a separate auth counter per form', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->post('/register');
    }
    $this->post('/register')->assertStatus(429);

    $this->post('/login')->assertStatus(302);
});

it('does not throttle auth form pages', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->post('/register');
    }
    $this->post('/register')->assertStatus(429);

    $this->get('/register')->assertOk();
});

it('blocks livewire after 300 requests per minute', function () {
    for ($i = 0; $i < 300; $i++) {
        livewireUpdate($this);
    }

    livewireUpdate($this)->assertStatus(429);
    $this->get('/')->assertOk();
});
