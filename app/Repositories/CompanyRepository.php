<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository
{
    public function getAllCompanies(): Collection
    {
        return Company::all();
    }

    public function create(array $request): Company
    {
        return Company::create($request);
    }

    public function update(array $request, Company $company): Company
    {
        $company->update($request);
        return $company;
    }

    public function delete(Company $company): void
    {
        $company->delete();
    }
}
