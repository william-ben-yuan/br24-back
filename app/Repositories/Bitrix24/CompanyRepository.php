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
     * Get all companies and their contacts.
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
     * Create a company and its contacts.
     * 
     * @param array $companyData
     * @return int
     */
    public function create(array $companyData): int
    {
        $companyResponse = $this->makeBitrix24ApiCall("crm.company.add", 'GET',  [
            'fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF_CRM_1720527892434' => $companyData['uf'],
                'UF_CRM_1720529084' => $companyData['city'],
                'UF_CRM_1720528969' => $companyData['cnpj'],
            ]
        ]);

        $companyId = $this->decodeResponse($companyResponse);
        foreach ($companyData['contacts'] as $contact) {
            $this->makeBitrix24ApiCall("crm.contact.add", 'GET', [
                ['fields' => [
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
     * Get a company and its contacts.
     * 
     * @param int $companyId
     * @return array
     */
    public function show(int $companyId): array
    {
        $response = $this->makeBitrix24ApiCall('crm.company.get', 'GET', ['id' => $companyId]);

        $response = $this->decodeResponse($response);
        $response['EMAIL'] = $response['EMAIL'][0]['VALUE'];
        $response['CITY'] = $response['UF_CRM_1720529084'];
        $response['UF'] = $response['UF_CRM_1720527892434'];
        $response['CNPJ'] = $response['UF_CRM_1720528969'];
        $response['contacts'] = $this->contactRepository->getContactsDetails($companyId);
        return $this->arrayKeysToLower($response);
    }

    /**
     * Update a company and its contacts.
     * 
     * @param array $companyData
     * @param int $companyId
     * @return bool
     */
    public function update(array $companyData, int $companyId): bool
    {
        $response = $this->makeBitrix24ApiCall('crm.company.update', 'POST', [
            'id' => $companyId,
            'fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF_CRM_1720527892434' => $companyData['uf'],
                'UF_CRM_1720529084' => $companyData['city'],
                'UF_CRM_1720528969' => $companyData['cnpj'],
            ]
        ]);

        // Update contacts, split them into two arrays: existing contacts and new contacts
        $exitingContacts = $this->contactRepository->getContacts($companyId);
        $contactsToKeep = [];
        foreach ($companyData['contacts'] as $contact) {
            if (isset($contact['id'])) {
                $this->contactRepository->updateContact(
                    $contact['id'],
                    [
                        'NAME' => $contact['name'],
                        'LAST_NAME' => $contact['last_name'],
                        'PHONE' => [['VALUE' => $contact['phone'], 'VALUE_TYPE' => 'WORK']],
                        'EMAIL' => [['VALUE' => $contact['email'], 'VALUE_TYPE' => 'WORK']],
                    ]
                );
                $contactsToKeep[] = $contact['id'];
            } else {
                $this->makeBitrix24ApiCall('crm.contact.add', 'POST', [
                    'fields' => [
                        'NAME' => $contact['name'],
                        'LAST_NAME' => $contact['last_name'],
                        'PHONE' => [['VALUE' => $contact['phone'], 'VALUE_TYPE' => 'WORK']],
                        'EMAIL' => [['VALUE' => $contact['email'], 'VALUE_TYPE' => 'WORK']],
                        'COMPANY_ID' => $companyId,
                    ]
                ]);
            }
        }

        // Delete contacts that are not in the contactsToKeep array
        foreach ($exitingContacts as $contact) {
            if (!in_array($contact['ID'], $contactsToKeep)) {
                $this->contactRepository->deleteContact($contact['ID']);
            }
        }

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
        $response = $this->makeBitrix24ApiCall('crm.company.delete', 'POST', ['id' => $companyId],);
        return $this->decodeResponse($response);
    }
}
