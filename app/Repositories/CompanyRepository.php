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

    public function show(Company $company): Company
    {
        return $company->load(['contacts' => function ($query) {
            $query->orderBy('name', 'asc');
        }]);
    }

    public function create(array $request): Company
    {
        $company = Company::create($request);
        $company->contacts()->createMany($request['contacts']);
        return $company;
    }

    public function update(array $request, Company $company): Company
    {
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

    public function delete(Company $company): void
    {
        $company->delete();
    }
}
