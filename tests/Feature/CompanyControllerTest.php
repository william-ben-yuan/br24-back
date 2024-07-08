<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function testIndex(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->get('/api/companies');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'email',
                'address',
                'city',
                'uf',
                'cnpj',
                'contacts' => [
                    '*' => [
                        'id',
                        'name',
                        'last_name',
                        'email',
                        'phone',
                    ],
                ],
            ],
        ]);
    }

    public function testShow(): void
    {
        $company = Company::factory()->create();
        Contact::factory()->count(3)->for($company)->create();

        $response = $this->get("/api/companies/{$company->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'id' => $company->id,
            'title' => $company->title,
            'email' => $company->email,
            'address' => $company->address,
            'city' => $company->city,
            'uf' => $company->uf,
            'cnpj' => $company->cnpj
        ]);
        $response->assertJsonCount(3, 'contacts');
        $response->assertJsonStructure([
            'contacts' => [
                '*' => [
                    'id',
                    'name',
                    'last_name',
                    'email',
                    'phone',
                ],
            ],
        ]);
    }

    public function testStore(): void
    {
        $companyData = Company::factory()->make()->toArray();
        $companyData['contacts'] = [Contact::factory()->make()->toArray()];

        $response = $this->post('/api/companies', $companyData);

        $response->assertStatus(Response::HTTP_CREATED);
        foreach ($companyData['contacts'] as $contact) {
            $this->assertDatabaseHas('contacts', $contact);
        }
        unset($companyData['contacts']);
        $this->assertDatabaseHas('companies', $companyData);
    }

    public function testUpdate(): void
    {
        $company = Company::factory()->create();
        $newCompanyData = Company::factory()->make()->toArray();
        $newCompanyData['contacts'] = [Contact::factory()->make()->toArray()];
        $response = $this->put("/api/companies/{$company->id}", $newCompanyData);

        $response->assertStatus(Response::HTTP_OK);
        foreach ($newCompanyData['contacts'] as $contact) {
            $this->assertDatabaseHas('contacts', $contact);
        }
        unset($newCompanyData['contacts']);
        $this->assertDatabaseHas('companies', $newCompanyData);
    }

    public function testDestroy(): void
    {
        $company = Company::factory()->create();

        $response = $this->delete("/api/companies/{$company->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
