<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function testIndex()
    {
        $company = Company::factory()->create();
        Contact::factory()->count(3)->for($company)->create();

        $response = $this->get("/api/companies/{$company->id}/contacts");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => [
                'name',
                'last_name',
                'email',
                'phone',
                'company_id',
            ],
        ]);
    }

    public function testStore()
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->make()->toArray();
        unset($contact['company_id']);

        $response = $this->post("/api/companies/{$company->id}/contacts", $contact);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('contacts', $contact);
    }

    public function testShow()
    {
        $contact = Contact::factory()->create();

        $response = $this->get("/api/contacts/{$contact->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'company_id' => $contact->company_id,
            'name' => $contact->name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);
    }

    public function testUpdate()
    {
        $contact = Contact::factory()->create();
        $newContact = Contact::factory()->make()->toArray();
        unset($newContact['company_id']);

        $response = $this->put("/api/contacts/{$contact->id}", $newContact);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('contacts', $newContact);
    }

    public function testDestroy()
    {
        $contact = Contact::factory()->create();

        $response = $this->delete("/api/contacts/{$contact->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
