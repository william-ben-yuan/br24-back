<?php

namespace App\Repositories\Bitrix24;

class ContactRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createContact(array $contactData): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.add", [
            'query' => ['auth' => $accessToken],
            'json' => ['fields' => $contactData]
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function updateContact(int $contactId, array $contactData): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.update", [
            'query' => ['auth' => $accessToken],
            'json' => ['id' => $contactId, 'fields' => $contactData]
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function deleteContact(int $contactId): void
    {
        $accessToken = $this->getAccessToken();

        $this->httpClient->post("{$this->bitrix24BaseUrl}/rest/crm.contact.delete", [
            'query' => ['auth' => $accessToken, 'id' => $contactId],
        ]);
    }
}
