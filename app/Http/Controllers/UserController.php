<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Authorize;
use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class UserController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    #[Authorize('manage-users')]
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $activeUsers = Cache::tags(['users'])->remember('users_active_page_'.request('page', 1), 300, function () {
            return User::select('id', 'name', 'email', 'role', 'is_active', 'created_at')
                ->orderBy('is_active', 'desc')->orderBy('name')->paginate(20);
        });
        $trashedUsers = User::onlyTrashed()->select('id', 'name', 'email', 'role')->get();

        return Inertia::render('Users/Index', [
            'users' => $activeUsers,
            'trashedUsers' => $trashedUsers,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        $user = User::create($request->validated());

        AuditLog::log('user_created', 'User', $user->id, null, $user->toArray());

        Cache::tags(['users'])->flush();

        return redirect()->back()->with('success', 'Usuario creado correctamente');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $old = $user->toArray();
        $data = $request->validated();
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        AuditLog::log('user_updated', 'User', $user->id, $old, $user->fresh()->toArray());

        Cache::tags(['users'])->flush();

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminarte a ti mismo');
        }

        $old = $user->toArray();
        $user->delete();

        AuditLog::log('user_deleted', 'User', $user->id, $old);

        Cache::tags(['users'])->flush();

        return redirect()->back()->with('success', 'Usuario eliminado');
    }

    public function restore($id)
    {
        $this->authorize('restore', User::class);
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        AuditLog::log('user_restored', 'User', $user->id, null, $user->toArray());

        Cache::tags(['users'])->flush();

        return redirect()->back()->with('success', 'Usuario restaurado correctamente');
    }

    public function toggleActive(User $user)
    {
        $this->authorize('update', $user);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes desactivarte a ti mismo');
        }

        $old = $user->toArray();
        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'user_activated' : 'user_deactivated';
        AuditLog::log($action, 'User', $user->id, $old, $user->fresh()->toArray());

        $msg = $user->is_active ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';

        Cache::tags(['users'])->flush();

        return redirect()->back()->with('success', $msg);
    }
}
