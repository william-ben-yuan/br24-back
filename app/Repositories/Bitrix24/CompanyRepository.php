<?php

namespace App\Repositories\Bitrix24;

use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllCompanies(): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/rest/crm.company.list", [
            'query' => ['auth' => $accessToken],
        ]);

        $jsonResponse = json_decode((string) $response->getBody(), true);
        $result = $jsonResponse['result'];
        return $result;
    }

    public function create(array $companyData): array
    {
        $accessToken = $this->getAccessToken();

        $companyResponse = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.company.add", [
            'query' => ['auth' => $accessToken],
            'json' => ['fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF_CRM_1720527892434' => $companyData['uf'],
                'UF_CRM_1720529084' => $companyData['city'],
                'UF_CRM_1720528969' => $companyData['cnpj'],
            ]]
        ]);

        $company = json_decode((string) $companyResponse->getBody(), true);
        $companyId = $company['result'];

        foreach ($companyData['contacts'] as $contact) {
            $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.add", [
                'query' => ['auth' => $accessToken],
                'json' => ['fields' => [
                    'NAME' => $contact['name'],
                    'LAST_NAME' => $contact['last_name'],
                    'PHONE' => [['VALUE' => $contact['phone'], 'VALUE_TYPE' => 'WORK']],
                    'EMAIL' => [['VALUE' => $contact['email'], 'VALUE_TYPE' => 'WORK']],
                    'COMPANY_ID' => $companyId,
                ]]
            ]);
        }

        return $company;
    }

    public function show(Company $company): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/rest/crm.company.get", [
            'query' => ['auth' => $accessToken, 'id' => $company->id],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function update(array $companyData, Company $company): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.company.update", [
            'query' => ['auth' => $accessToken],
            'json' => ['id' => $company->id, 'fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF_CRM_1720527892434' => $companyData['uf'],
                'UF_CRM_1720529084' => $companyData['city'],
                'UF_CRM_1720528969' => $companyData['cnpj'],
            ]]
        ]);



        return json_decode((string) $response->getBody(), true);
    }

    public function delete(Company $company): void
    {
        $accessToken = $this->getAccessToken();

        $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.company.delete", [
            'query' => ['auth' => $accessToken, 'id' => $company->id],
        ]);
    }
}
