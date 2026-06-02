<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubAgentController extends \App\Http\Controllers\Admin\ManagersController
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $this->authorizeManagersAccess($user, 'managers.view');

        $baseQuery = $this->baseSubAgentQuery($user)->withTrashed();
        $subAgents = $baseQuery->orderBy('role_id')->orderBy('id')->get();

        $counts = [
            'all' => (clone $this->baseSubAgentQuery($user))->count(),
            'deleted' => (clone $this->baseSubAgentQuery($user))->onlyTrashed()->count(),
        ];

        return view('agent.sub-agents.index', compact('subAgents', 'counts'));
    }

    public function create()
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.create');

        $roles = $this->subAgentService->roleOptionsForTenant($currentUser);

        return view('agent.sub-agents.form', compact('roles'));
    }

    public function edit($id)
    {
        $currentUser = Auth::user();
        $this->authorizeManagersAccess($currentUser, 'managers.edit');

        $manager = $this->baseSubAgentQuery($currentUser)->with(['role', 'userPermissions'])->findOrFail($id);

        $roles = $this->subAgentService->roleOptionsForTenant($currentUser);

        return view('agent.sub-agents.form', compact('manager', 'roles'));
    }
}
