<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\Company\Http\Requests\StoreCompanyRequest;
use Modules\Company\Http\Requests\UpdateCompanyRequest;
use Modules\Company\Models\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->hasRole('superadmin')) {
            abort(403);
        }

        $query = Company::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('code', 'like', '%'.$request->search.'%');
        }

        $companies = $query->paginate(10)->withQueryString();

        return Inertia::render('Company::Index', [
            'companies' => $companies,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (! Auth::user()->hasRole('superadmin')) {
            abort(403);
        }

        return Inertia::render('Company::Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        if (! Auth::user()->hasRole('superadmin')) {
            abort(403);
        }

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('companies', 'public');
            $data['logo'] = $path;
        }

        Company::create($data);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        if (! Auth::user()->hasRole('superadmin') && Auth::user()->company_id !== $company->id) {
            abort(403);
        }

        return Inertia::render('Company::Show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        if (! Auth::user()->hasRole('superadmin')) {
            // According to the request, company admin might only see details.
            // If they can't "mengelola" (manage), they probably can't edit.
            abort(403);
        }

        return Inertia::render('Company::Edit', [
            'company' => $company,
            'logo_url' => $company->logo ? Storage::url($company->logo) : null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        if (! Auth::user()->hasRole('superadmin')) {
            abort(403);
        }

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $path = $request->file('logo')->store('companies', 'public');
            $data['logo'] = $path;
        }

        $company->update($data);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        if (! Auth::user()->hasRole('superadmin')) {
            abort(403);
        }

        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->back()->with('success', 'Company deleted successfully.');
    }
}
