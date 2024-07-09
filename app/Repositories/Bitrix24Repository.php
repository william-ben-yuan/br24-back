<?php

namespace App\Repositories;

use App\Models\Company;
use GuzzleHttp\Client;
use App\Models\OauthToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class Bitrix24Repository
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $bitrix24BaseUrl;

    public function __construct()
    {
        $this->clientId = env('BITRIX24_CLIENT_ID');
        $this->clientSecret = env('BITRIX24_CLIENT_SECRET');
        $this->redirectUri = env('BITRIX24_REDIRECT_URI');
        $this->bitrix24BaseUrl = env('BITRIX24_BASE_URL');
    }

    private function getAccessToken(): string
    {
        $token = OauthToken::find(1);

        if ($token && $token->expires_in > now()) {
            return $token->access_token;
        }

        if ($token) {
            return $this->refreshAccessToken($token);
        }

        throw new \Exception('No valid access token found.');
    }
    
    private function refreshAccessToken(OauthToken $token): string
    {
        $http = new Client();
        
        $response = $http->post("{$this->bitrix24BaseUrl}/oauth/token", [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $token->refresh_token,
            ],
        ]);
        
        $data = json_decode((string) $response->getBody(), true);
        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expires_in' => now()->addSeconds($data['expires_in']),
        ]);
        
        return $token->access_token;
    }
    
    public function getAllCompanies(): array
    {
        $http = new Client();
        $accessToken = $this->getAccessToken();

        $response = $http->get("{$this->bitrix24BaseUrl}/rest/crm.company.list", [
            'query' => ['auth' => $accessToken],
        ]);

        $jsonResponse = json_decode((string) $response->getBody(), true);
        $result = $jsonResponse['result'];
        return $result;
    }

    public function create(array $companyData): array
    {
        $http = new Client();
        $accessToken = $this->getAccessToken();

        // Registrar a empresa
        $companyResponse = $http->post("{$this->bitrix24BaseUrl}/rest/crm.company.add", [
            'query' => ['auth' => $accessToken],
            'json' => ['fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF' => $companyData['uf'],
                'CITY' => $companyData['city'],
                'CNPJ' => $companyData['cnpj'],
            ]]
        ]);

        $company = json_decode((string) $companyResponse->getBody(), true);
        $company->id = $company['result'];

        // Registrar os contatos
        foreach ($companyData['contacts'] as $contact) {
            $http->post("{$this->bitrix24BaseUrl}/rest/crm.contact.add", [
                'query' => ['auth' => $accessToken],
                'json' => ['fields' => [
                    'NAME' => $contact['name'],
                    'LAST_NAME' => $contact['last_name'],
                    'PHONE' => [['VALUE' => $contact['phone'], 'VALUE_TYPE' => 'WORK']],
                    'EMAIL' => [['VALUE' => $contact['email'], 'VALUE_TYPE' => 'WORK']],
                    'COMPANY_ID' => $company->id,                
                ]]
            ]);
        }

        return $company;
    }

    public function show(Company $company): array
    {
        $http = new Client();
        $accessToken = $this->getAccessToken();

        $response = $http->get("{$this->bitrix24BaseUrl}/rest/crm.company.get", [
            'query' => ['auth' => $accessToken, 'id' => $company->id],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function update(array $companyData, Company $company): array
    {
        $http = new Client();
        $accessToken = $this->getAccessToken();

        $response = $http->post("{$this->bitrix24BaseUrl}/rest/crm.company.update", [
            'query' => ['auth' => $accessToken],
            'json' => ['id' => $company->id, 'fields' => [
                'TITLE' => $companyData['title'],
                'EMAIL' => [['VALUE' => $companyData['email'], 'VALUE_TYPE' => 'WORK']],
                'ADDRESS' => $companyData['address'],
                'UF' => $companyData['uf'],
                'CITY' => $companyData['city'],
                'CNPJ' => $companyData['cnpj'],
            ]]
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function delete(Company $company): void
    {
        $http = new Client();
        $accessToken = $this->getAccessToken();

        $http->post("{$this->bitrix24BaseUrl}/rest/crm.company.delete", [
            'query' => ['auth' => $accessToken, 'id' => $company->id],
        ]);
    }

    public function handleProviderCallback(Request $request): JsonResponse
    {
        $http = new Client();

        $query = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $request->code,
            'grant_type' => 'authorization_code',
        ]);
        $response = $http->get("{$this->bitrix24BaseUrl}/oauth/token?$query");

        $data = json_decode((string) $response->getBody(), true);

        OauthToken::updateOrCreate(
            ['id' => 1],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_in' => now()->addSeconds($data['expires_in']),
            ]
        );

        return response()->json(['message' => 'Token stored successfully']);
    }

    public function redirectToProvider(): RedirectResponse | null
    {
        $token = OauthToken::find(1);

        if (!$token || $token->expires_in <= now()) {
            $query = http_build_query([
                'client_id' => $this->clientId,
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
            ]);

            return redirect("{$this->bitrix24BaseUrl}/oauth/authorize?$query");
        } 
    }
}
