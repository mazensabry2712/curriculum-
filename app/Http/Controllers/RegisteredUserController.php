<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $baseSlug = Str::slug($data['organization_name']) ?: 'organization';
            $slug = $baseSlug;
            $suffix = 1;

            while (Organization::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $organization = Organization::create([
                'name' => $data['organization_name'],
                'slug' => $slug,
                'country' => 'EG',
                'timezone' => 'Africa/Cairo',
            ]);

            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $role = Role::firstOrCreate(['name' => 'university-admin', 'guard_name' => 'web']);
            $user->assignRole($role);

            return $user;
        });

        event(new Registered($user));
        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Your organization workspace is ready.');
    }
}