<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class ContactRepository
{
    public function getAllContacts(Company $company): Collection
    {
        return $company->contacts;
    }

    public function create(array $request, Company $company): Contact
    {
        $contact = new Contact($request);
        $company->contacts()->save($contact);
        return $contact;
    }

    public function find(int $id): ?Contact
    {
        return Contact::find($id);
    }

    public function update(array $request, Contact $contact): Contact
    {
        $contact->update($request);
        return $contact;
    }

    public function delete(Contact $contact): void
    {
        $contact->delete();
    }
}
