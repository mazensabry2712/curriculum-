<?php

it('creates a private organization workspace and university administrator', function () {
    $response = $this->post('/register', [
        'organization_name' => 'Faculty of Computing',
        'name' => 'Workspace Admin',
        'email' => 'admin@computing.test',
        'password' => 'StrongPassword123',
        'password_confirmation' => 'StrongPassword123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('organizations', [
        'name' => 'Faculty of Computing',
        'slug' => 'faculty-of-computing',
    ]);

    $user = \App\Models\User::where('email', 'admin@computing.test')->firstOrFail();
    expect($user->organization_id)->not->toBeNull();
    expect($user->hasRole('university-admin'))->toBeTrue();
    expect($user->is_active)->toBeTrue();
});

it('rejects duplicate email registration', function () {
    $organization = \App\Models\Organization::create([
        'name' => 'Existing Org',
        'slug' => 'existing-org',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
    ]);

    \App\Models\User::create([
        'organization_id' => $organization->id,
        'name' => 'Existing User',
        'email' => 'admin@existing.test',
        'password' => 'StrongPassword123',
    ]);

    $this->from('/register')->post('/register', [
        'organization_name' => 'Another Faculty',
        'name' => 'Second Admin',
        'email' => 'admin@existing.test',
        'password' => 'StrongPassword123',
        'password_confirmation' => 'StrongPassword123',
    ])->assertSessionHasErrors('email');
});

it('does not expose the dashboard to guests', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});