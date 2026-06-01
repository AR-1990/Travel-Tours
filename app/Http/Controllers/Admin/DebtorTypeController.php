<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\DebtorType;
use App\Services\DebtorTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtorTypeController extends Controller
{
    public function __construct(
        protected DebtorTypeService $debtorTypeService
    ) {}

    protected function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can manage debtor types.');
        }
    }

    public function index()
    {
        $this->authorizeSuperAdmin();
        $debtorTypes = DebtorType::orderBy('name')->get();

        return view('admin.debtor-types.index', compact('debtorTypes'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        return view('admin.debtor-types.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:debtor_types,slug',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        $this->debtorTypeService->create([
            'name' => $request->name,
            'slug' => $request->input('slug'),
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.debtor-types.index')->with('success', 'Debtor type created.');
    }

    public function edit($id)
    {
        $this->authorizeSuperAdmin();
        $debtorType = DebtorType::findOrFail($id);

        return view('admin.debtor-types.edit', compact('debtorType'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdmin();
        $debtorType = DebtorType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:debtor_types,slug,'.$debtorType->id,
            'description' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        $this->debtorTypeService->update($debtorType, [
            'name' => $request->name,
            'slug' => $request->input('slug'),
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.debtor-types.index')->with('success', 'Debtor type updated.');
    }

    public function destroy($id)
    {
        $this->authorizeSuperAdmin();
        $debtorType = DebtorType::findOrFail($id);

        $error = $this->debtorTypeService->tryDelete($debtorType);
        if ($error !== null) {
            return redirect()->route('admin.debtor-types.index')->with('error', $error);
        }

        return redirect()->route('admin.debtor-types.index')->with('success', 'Debtor type deleted.');
    }
}
