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
        $response = $this->makeBitrix24ApiCall('crm.contact.list', 'GET', ['filter' => ['COMPANY_ID' => $companyId]]);
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
        $response = $this->makeBitrix24ApiCall('crm.contact.get', 'GET', ['id' => $contactId]);
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
        $response = $this->makeBitrix24ApiCall('crm.contact.add', 'POST', ['fields' => $contactData]);
        return $this->decodeResponse($response);
    }

    /**
     * Update a contact.
     * 
     * @param int $contactId
     * @param array $contactData
     * @return array
     */
    public function updateContact(int $contactId, array $contactData): bool
    {
        $response = $this->makeBitrix24ApiCall('crm.contact.update', 'POST', ['id' => $contactId, 'fields' => $contactData]);
        return $this->decodeResponse($response);
    }

    /**
     * Delete a contact.
     * 
     * @param int $contactId
     */
    public function deleteContact(int $contactId): void
    {
        $this->makeBitrix24ApiCall('crm.contact.delete', 'POST', ['id' => $contactId]);
    }
}
