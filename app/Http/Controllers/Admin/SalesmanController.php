<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class SalesmanController extends Controller
{
    public function __construct(protected UserService $service) {}

    public function index()
    {
        $salesmen = $this->service->list([
            'roles'     => ['salesman'],
            'outlet_id' => auth()->user()->outlet_id,
        ]);

        return view('admin.salesmen.index', compact('salesmen'));
    }

    public function create()
    {
        return view('admin.salesmen.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateSalesman($request);

        try {
            $this->service->create([
                ...$validated,
                'role'      => 'salesman',
                'outlet_id' => auth()->user()->outlet_id,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.salesmen.index')
                         ->with('success', 'Salesman account created successfully.');
    }

    public function edit(User $salesman)
    {
        $this->authorize($salesman);
        return view('admin.salesmen.edit', compact('salesman'));
    }

    public function update(Request $request, User $salesman)
    {
        $this->authorize($salesman);
        $validated = $this->validateSalesman($request, $salesman->id);

        try {
            $this->service->update($salesman, [
                ...$validated,
                'role'      => 'salesman',
                'outlet_id' => auth()->user()->outlet_id,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.salesmen.index')
                         ->with('success', 'Salesman updated successfully.');
    }

    public function destroy(User $salesman)
    {
        $this->authorize($salesman);

        try {
            $this->service->delete($salesman);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.salesmen.index')
                         ->with('success', 'Salesman deleted successfully.');
    }

    public function toggleStatus(User $salesman)
    {
        $this->authorize($salesman);
        $salesman = $this->service->toggleStatus($salesman);
        $label    = $salesman->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Salesman {$label} successfully.");
    }

    private function authorize(User $salesman): void
    {
        try {
            $this->service->authorize(auth()->user(), $salesman);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    private function validateSalesman(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email,' . ($ignoreId ?? 'NULL')],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => [$ignoreId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}