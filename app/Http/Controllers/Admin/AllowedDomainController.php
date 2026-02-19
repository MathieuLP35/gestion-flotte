<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowedDomain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AllowedDomainController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('allowed_domains.view');

        return Inertia::render('Admin/Domains/Index', [
            'domains' => AllowedDomain::orderBy('name')->get(),
            'can' => [
                'create' => Gate::allows('allowed_domains.create'),
                'edit' => Gate::allows('allowed_domains.edit'),
                'delete' => Gate::allows('allowed_domains.delete'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('allowed_domains.create');

        $request->validate([
            'name' => 'required|string|unique:allowed_domains,name|lowercase',
        ]);

        AllowedDomain::create($request->all());

        return back()->with('success', 'Domaine ajouté.');
    }

    public function edit(AllowedDomain $domain)
    {
        $this->authorize('allowed_domains.edit');

        return Inertia::render('Admin/Domains/Edit', [
            'domain' => $domain,
        ]);
    }

    public function update(Request $request, AllowedDomain $domain)
    {
        $this->authorize('allowed_domains.edit');

        $request->validate([
            'name' => 'required|string|unique:allowed_domains,name,'.$domain->id.'|lowercase',
        ]);

        $domain->update($request->only('name'));

        return redirect()->route('admin.domains.index')->with('success', 'Domaine mis à jour.');
    }

    public function destroy(AllowedDomain $domain)
    {
        $this->authorize('allowed_domains.delete');

        $domain->delete();

        return back();
    }
}
