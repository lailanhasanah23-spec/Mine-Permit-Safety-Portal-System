<?php

namespace App\Services;

use App\Models\GoogleToken;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;

class GoogleMailService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client;
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(route('admin.google.callback'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl(string $purpose = 'gmail')
    {
        $this->configureScopes($purpose);

        return $this->client->createAuthUrl();
    }

    public function authenticate($code, $userId = null, string $purpose = 'gmail')
    {
        $this->configureScopes($purpose);
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
        if (isset($accessToken['error'])) {
            throw new \Exception('Google Auth Error: '.($accessToken['error_description'] ?? $accessToken['error']));
        }

        $this->saveToken($accessToken, $userId, $purpose);

        return $accessToken;
    }

    public function saveToken(array $token, $userId = null, string $purpose = 'gmail')
    {
        $data = [
            'access_token' => $token['access_token'],
            'expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
        ];

        // IMPORTANT: Google only sends refresh_token on FIRST authorization.
        // Subsequent refreshes only return a new access_token.
        // Always preserve the existing refresh_token if not in the new response.
        if (isset($token['refresh_token'])) {
            $data['refresh_token'] = $token['refresh_token'];
        }

        GoogleToken::updateOrCreate(
            ['service_name' => $this->serviceName($purpose), 'user_id' => $userId],
            $data
        );
    }

    public function getClient($userId = null, string $purpose = 'gmail')
    {
        $this->configureScopes($purpose);

        $token = GoogleToken::where('service_name', $this->serviceName($purpose))
            ->where('user_id', $userId)
            ->first();

        if (! $token) {
            $context = $userId ? " (User ID: $userId)" : ' (System)';
            $label = $purpose === 'drive' ? 'Google Drive' : 'Gmail';
            throw new \Exception("Akun $label belum terhubung$context. Silakan tautkan akun Anda di menu yang sesuai.");
        }

        // Use expiry_date (epoch ms) which is what the Google SDK natively understands.
        $this->client->setAccessToken([
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expiry_date' => $token->expires_at ? ($token->expires_at->timestamp * 1000) : 0,
            'created' => $token->updated_at->timestamp,
        ]);

        if ($this->client->isAccessTokenExpired()) {
            if ($token->refresh_token) {
                try {
                    Log::info('Refreshing Google token for '.($userId ?: 'system'));
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($token->refresh_token);

                    if (isset($newToken['error'])) {
                        $err = $newToken['error_description'] ?? $newToken['error'];
                        throw new \Exception("Refresh token gagal: $err. Sesi Anda mungkin telah dicabut oleh Google.");
                    }

                    $this->saveToken($newToken, $userId, $purpose);
                    $this->client->setAccessToken($newToken);
                } catch (\Exception $e) {
                    Log::error('Google Token Refresh Error for '.($userId ?: 'system').': '.$e->getMessage());
                    throw new \Exception('Sesi Google Drive berakhir dan tidak dapat diperbarui otomatis. Silakan hubungkan ulang akun Anda.');
                }
            } else {
                throw new \Exception('Sesi Google Drive berakhir dan tidak tersedia refresh token. Silakan hubungkan ulang akun Anda.');
            }
        }

        return $this->client;
    }

    public function sendRawEmail($rawMessage)
    {
        $client = $this->getClient(null, 'gmail');
        $service = new Gmail($client);

        $message = new Message;
        $message->setRaw(strtr(base64_encode($rawMessage), ['+' => '-', '/' => '_', '=' => '']));

        try {
            return $service->users_messages->send('me', $message);
        } catch (\Exception $e) {
            Log::error('Gmail Send Error: '.$e->getMessage());
            throw $e;
        }
    }

    protected function configureScopes(string $purpose): void
    {
        $this->client->setScopes([]);

        if ($purpose === 'drive') {
            $this->client->addScope(Drive::DRIVE_FILE);
            $this->client->addScope(Drive::DRIVE_METADATA_READONLY);

            return;
        }

        $this->client->addScope(Gmail::GMAIL_SEND);
    }

    protected function serviceName(string $purpose): string
    {
        return $purpose === 'drive' ? 'drive' : 'gmail';
    }
}
