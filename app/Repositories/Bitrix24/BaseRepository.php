<?php

namespace App\Repositories\Bitrix24;

use GuzzleHttp\Client;
use App\Models\OauthToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BaseRepository
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $bitrix24BaseUrl;
    protected $httpClient;

    public function __construct()
    {
        $this->clientId = env('BITRIX24_CLIENT_ID');
        $this->clientSecret = env('BITRIX24_CLIENT_SECRET');
        $this->redirectUri = env('BITRIX24_REDIRECT_URI');
        $this->bitrix24BaseUrl = env('BITRIX24_BASE_URL');
        $this->httpClient = new Client();
    }

    /**
     * Get the access token.
     * 
     * @return string
     */
    protected function getAccessToken(): string
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

    /**
     * Refresh the access token.
     * 
     * @param OauthToken $token
     * @return string
     */
    protected function refreshAccessToken(OauthToken $token): string
    {
        $query = http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
        ]);
        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/oauth/token?$query");

        $data = json_decode((string) $response->getBody(), true);
        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expires_in' => now()->addSeconds($data['expires_in']),
        ]);

        return $token->access_token;
    }

    /**
     * Obtain the user information from Bitrix24.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handleProviderCallback(Request $request): JsonResponse
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $request->code,
            'grant_type' => 'authorization_code',
        ]);
        $response = $this->httpClient->get("{$this->bitrix24BaseUrl}/oauth/token?$query");

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

    /**
     * Redirect the user to the Bitrix24 authentication page.
     * 
     * @return RedirectResponse|null
     */
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

    /**
     * Decode the response from the Bitrix24 API.
     * 
     * @param mixed $response
     * @return mixed
     */
    protected function decodeResponse($response): mixed
    {
        $decodedResponse = json_decode((string) $response->getBody(), true);
        return $decodedResponse['result'] ?? null; // Return null or an appropriate default if 'result' key is not set
    }

    /**
     * Recursively converts all keys in an array to lowercase.
     * (Bitrix24 API return the keys in uppercase)
     * 
     * @param array $inputArray
     * @return array
     */
    function arrayKeysToLower(array $inputArray): array
    {
        $outputArray = [];
        foreach ($inputArray as $key => $value) {
            $key = strtolower($key);
            if (is_array($value)) {
                $value = $this->arrayKeysToLower($value);
            }
            $outputArray[$key] = $value;
        }
        return $outputArray;
    }
}
