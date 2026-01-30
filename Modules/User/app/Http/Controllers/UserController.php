<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Company\Models\Company;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $users = User::query()
            ->with(['roles', 'company'])
            ->latest()
            ->get()
            ->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company' => $user->company,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('User::Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        return Inertia::render('User::Create', [
            'roles' => Role::all(),
            'companies' => Company::all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->assignRole($data['role']);

        return redirect()->route('user.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $user->load(['roles']);

        return Inertia::render('User::Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $user->company_id,
                'role' => $user->roles->first()?->name,
            ],
            'roles' => Role::all(),
            'companies' => Company::all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$data['role']]);

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Cannot self-delete.');
        }
        $user->delete();

        return redirect()->route('user.index')->with('success', 'User deleted successfully.');
    }
}
