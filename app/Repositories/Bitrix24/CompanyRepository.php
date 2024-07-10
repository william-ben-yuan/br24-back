<?php

namespace App\Repositories\Bitrix24;

class CompanyRepository extends BaseRepository
{
    private $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        parent::__construct();
        $this->contactRepository = $contactRepository;
    }

    /**
     * Get all companies.
     * 
     * @return array
     */
    public function getAllCompanies(): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/rest/crm.company.list", [
            'query' => ['auth' => $accessToken],
        ]);

        $companies = $this->decodeResponse($response);
        foreach ($companies as $key => $company) {
            $companies[$key]['contacts'] = $this->contactRepository->getContacts($company['ID']);
        }
        return $this->arrayKeysToLower($companies);
    }

    /**
     * Create a company.
     * 
     * @param array $companyData
     * @return int
     */
    public function create(array $companyData): int
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

        $companyId = $this->decodeResponse($companyResponse);
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

        return $companyId;
    }

    /**
     * Get a company.
     * 
     * @param int $companyId
     * @return array
     */
    public function show(int $companyId): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/rest/crm.company.get", [
            'query' => ['auth' => $accessToken, 'id' => $companyId],
        ]);

        $response = $this->decodeResponse($response);
        $response['EMAIL'] = $response['EMAIL'][0]['VALUE'];
        $response['CITY'] = $response['UF_CRM_1720529084'];
        $response['UF'] = $response['UF_CRM_1720527892434'];
        $response['CNPJ'] = $response['UF_CRM_1720528969'];
        $response['contacts'] = $this->contactRepository->getContactsDetails($companyId);
        return $this->arrayKeysToLower($response);
    }

    /**
     * Update a company.
     * 
     * @param array $companyData
     * @param int $companyId
     * @return bool
     */
    public function update(array $companyData, int $companyId): bool
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.company.update", [
            'query' => ['auth' => $accessToken],
            'json' => ['id' => $companyId, 'fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF_CRM_1720527892434' => $companyData['uf'],
                'UF_CRM_1720529084' => $companyData['city'],
                'UF_CRM_1720528969' => $companyData['cnpj'],
            ]]
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Delete a company.
     * 
     * @param int $companyId
     * @return bool
     */
    public function delete(int $companyId): bool
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.company.delete", [
            'query' => ['auth' => $accessToken, 'id' => $companyId],
        ]);

        return $this->decodeResponse($response);
    }
}
