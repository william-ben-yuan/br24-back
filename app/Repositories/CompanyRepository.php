<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository
{
    public function getAllCompanies(): Collection
    {
        return Company::with(['contacts' => function ($query) {
            $query->orderBy('name', 'asc');
        }])->get();
    }

    /**
     * Get a company.
     * 
     * @param int $companyId
     * @return Company
     */
    public function show(int $companyId): Company
    {
        $company = Company::findOrFail($companyId);
        return $company->load(['contacts' => function ($query) {
            $query->orderBy('name', 'asc');
        }]);
    }

    /**
     * Create a company.
     * 
     * @param array $request
     * @return Company
     */
    public function create(array $request): Company
    {
        $company = Company::create($request);
        $company->contacts()->createMany($request['contacts']);
        return $company;
    }

    /**
     * Update a company.
     * 
     * @param array $request
     * @param int $companyId
     * @return Company
     */
    public function update(array $request, int $companyId): Company
    {
        $company = Company::findOrFail($companyId);
        $company->update($request);
        $existingContacts = $company->contacts->pluck('id');
        $newContacts = collect($request['contacts'])->pluck('id');
        $contactsToDelete = $existingContacts->diff($newContacts);
        $contactsToCreate = collect($request['contacts'])->whereNotIn('id', $existingContacts);
        $contactsToUpdate = collect($request['contacts'])->whereIn('id', $existingContacts);

        $company->contacts()->whereIn('id', $contactsToDelete)->delete();
        $contactsToUpdate->each(function ($contact) use ($company) {
            $company->contacts()->where('id', $contact['id'])->update($contact);
        });
        $company->contacts()->createMany($contactsToCreate);

        return $company->load(['contacts' => function ($query) {
            $query->orderBy('name', 'asc');
        }]);
    }

    /**
     * Delete a company.
     * 
     * @param int $companyId
     * @return bool
     */
    public function delete(int $companyId): bool
    {
        $company = Company::findOrFail($companyId);
        return $company->delete();
    }
}
