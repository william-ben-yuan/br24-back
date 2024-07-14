<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompanyRepository
{
    public function getAllCompanies(): Collection
    {
        // Cache the companies forever
        return Cache::rememberForever('companies', function () {
            return Company::with(['contacts' => function ($query) {
                $query->orderBy('name', 'asc');
            }])->get();
        });
    }

    /**
     * Get a company.
     * 
     * @param int $companyId
     * @return Company
     */
    public function show(int $companyId): Company
    {
        // Cache the company for 60 minutes
        return Cache::remember('company.' . $companyId, 60, function () use ($companyId) {
            // If you want to use tags, you can use the following code instead of the above code
            //return Cache::tags(['companies'])->remember("company.{$id}", 60*60, function () use ($id) {

            return Company::with(['contacts' => function ($query) {
                $query->orderBy('name', 'asc');
            }])->findOrFail($companyId);
        });
    }

    /**
     * Create a company.
     * 
     * @param array $request
     * @return Company
     */
    public function create(array $request): Company
    {
        $company = DB::transaction(function () use ($request) {
            $company = Company::create($request);
            $company->contacts()->createMany($request['contacts']);
            return $company;
        });

        $this->clearCompaniesCache();
        return $company;
    }

    /**
     * Update a company.
     * 
     * @param array $request
     * @param int $companyId
     * @return Company
     */
    public function update(int $companyId, array $request): Company
    {
        $company = DB::transaction(function () use ($companyId, $request) {
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

            return $company;
        });

        $this->clearCompaniesCache($companyId);
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
        $result = DB::transaction(function () use ($companyId) {
            $company = Company::findOrFail($companyId);
            $company->contacts()->delete();
            return $company->delete();
        });
        $this->clearCompaniesCache($companyId);
        return $result;
    }

    protected function clearCompaniesCache(int $id = null)
    {
        Cache::forget("companies");
        if ($id) {
            Cache::forget("company.{$id}");
        }
        // If you want to use tags, you can use the following code instead of the above code, but it will clear all companies cache
        // Cache::tags(['companies'])->flush();
    }
}
