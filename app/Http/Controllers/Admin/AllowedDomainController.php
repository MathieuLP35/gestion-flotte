<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowedDomain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AllowedDomainController extends Controller
{
    use AuthorizesRequests;

    /**
     * @return Response
     */
    public function index(): Response
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

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('allowed_domains.create');

        $request->validate([
            'name' => 'required|string|unique:allowed_domains,name|lowercase',
        ]);

        AllowedDomain::create($request->all());

        return back()->with('success', 'Domaine ajouté.');
    }

    /**
     * @param AllowedDomain $domain
     * @return Response
     */
    public function edit(AllowedDomain $domain): Response
    {
        $this->authorize('allowed_domains.edit');

        return Inertia::render('Admin/Domains/Edit', [
            'domain' => $domain,
        ]);
    }

    /**
     * @param Request $request
     * @param AllowedDomain $domain
     * @return RedirectResponse
     */
    public function update(Request $request, AllowedDomain $domain): RedirectResponse
    {
        $this->authorize('allowed_domains.edit');

        $request->validate([
            'name' => 'required|string|unique:allowed_domains,name,'.$domain->id.'|lowercase',
        ]);

        $domain->update($request->only('name'));

        return redirect()->route('admin.domains.index')->with('success', 'Domaine mis à jour.');
    }

    /**
     * @param AllowedDomain $domain
     * @return RedirectResponse
     */
    public function destroy(AllowedDomain $domain): RedirectResponse
    {
        $this->authorize('allowed_domains.delete');

        $domain->delete();

        return back();
    }
}
