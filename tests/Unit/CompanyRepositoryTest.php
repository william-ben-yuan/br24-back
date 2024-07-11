<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Contact;
use App\Repositories\CompanyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CompanyRepository();
    }

    public function testGetAllCompanies(): void
    {
        Company::factory()->count(3)->create();

        $fetchedCompanys = $this->repository->getAllCompanies();

        $this->assertCount(3, $fetchedCompanys);
    }

    public function testCreate(): void
    {
        $companyData = Company::factory()->make()->toArray();
        $companyData['contacts'] = [Contact::factory()->make()->toArray()];
        $company = $this->repository->create($companyData);

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function testUpdate(): void
    {
        $company = Company::factory()->create();
        Contact::factory()->for($company)->create();
        $newCompanyData = Company::factory()->make()->toArray();
        $newCompanyData['contacts'] = [Contact::factory()->make()->toArray()];
        $this->put("/api/companies/{$company->id}", $newCompanyData);
        $this->repository->update($company->id, $newCompanyData);

        foreach ($newCompanyData['contacts'] as $contact) {
            $this->assertDatabaseHas('contacts', $contact);
        }
        unset($newCompanyData['contacts']);
        $this->assertDatabaseHas('companies', $newCompanyData);
    }

    public function testDelete(): void
    {
        $company = Company::factory()->create();

        $this->repository->delete($company->id);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
