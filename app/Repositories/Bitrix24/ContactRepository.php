<?php

namespace App\Repositories\Bitrix24;

class ContactRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all contacts.
     * 
     * @param int $companyId
     * @return array
     */
    public function getContacts(int $companyId): array
    {
        $accessToken = $this->getAccessToken();

        $query = http_build_query([
            'auth' => $accessToken,
            'filter' => [
                'COMPANY_ID' => $companyId
            ]
        ]);
        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/rest/crm.contact.list?$query");

        return $this->decodeResponse($response);
    }

    /**
     * Get all contacts details.
     * 
     * @param int $companyId
     * @return array
     */
    public function getContactsDetails(int $companyId): array
    {
        $contacts = $this->getContacts($companyId);
        foreach ($contacts as $key => $contact) {
            $contactDetails = $this->showContact($contact['ID']);
            $contacts[$key]['EMAIL'] = $contactDetails['EMAIL'][0]['VALUE'] ?? '';
            $contacts[$key]['PHONE'] = $contactDetails['PHONE'][0]['VALUE'] ?? '';
        }
        return $contacts;
    }

    /**
     * Show a contact.
     * 
     * @param int $contactId
     * @return array
     */
    public function showContact(int $contactId): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/rest/crm.contact.get", [
            'query' => ['auth' => $accessToken, 'id' => $contactId],
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Create a contact.
     * 
     * @param array $contactData
     * @return array
     */
    public function createContact(array $contactData): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.add", [
            'query' => ['auth' => $accessToken],
            'json' => ['fields' => $contactData]
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Update a contact.
     * 
     * @param int $contactId
     * @param array $contactData
     * @return array
     */
    public function updateContact(int $contactId, array $contactData): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.update", [
            'query' => ['auth' => $accessToken],
            'json' => ['id' => $contactId, 'fields' => $contactData]
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Delete a contact.
     * 
     * @param int $contactId
     */
    public function deleteContact(int $contactId): void
    {
        $accessToken = $this->getAccessToken();

        $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.delete", [
            'query' => ['auth' => $accessToken, 'id' => $contactId],
        ]);
    }
}
