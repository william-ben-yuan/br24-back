<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Company;
use App\Repositories\ContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ContactRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new ContactRepository();
    }

    public function testGetAllContacts(): void
    {
        $company = Company::factory()->create();
        Contact::factory()->count(3)->for($company)->create();
        $fetchedContacts = $this->repository->getAllContacts($company);

        $this->assertCount(3, $fetchedContacts);
    }

    public function testStore(): void
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->make();

        $createdContact = $this->repository->create($contact->toArray(), $company);

        $this->assertDatabaseHas('contacts', [
            'id' => $createdContact->id,
            'name' => $contact->name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'company_id' => $company->id,
        ]);
    }

    public function testUpdate(): void
    {
        $contact = Contact::factory()->create();
        $newContact = Contact::factory()->make();
        unset($newContact->company_id);

        $updatedContact = $this->repository->update($newContact->toArray(), $contact);

        $this->assertDatabaseHas('contacts', [
            'id' => $updatedContact->id,
            'name' => $updatedContact->name,
            'last_name' => $updatedContact->last_name,
            'email' => $updatedContact->email,
            'phone' => $updatedContact->phone,
            'company_id' => $contact->company_id,
        ]);
    }

    public function testDelete(): void
    {
        $contact = Contact::factory()->create();

        $this->repository->delete($contact);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
