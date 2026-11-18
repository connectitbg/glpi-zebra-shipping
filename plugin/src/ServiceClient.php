<?php

namespace GlpiPlugin\Zebrashipping;

final class ServiceClient
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(self::env('ZEBRASHIPPING_SERVICE_URL'), '/');
        $this->token = self::env('ZEBRASHIPPING_API_TOKEN');
        if ($this->baseUrl === '' || $this->token === '') {
            throw new \RuntimeException('Print Service connection is not configured by the server administrator');
        }
        if (!str_starts_with($this->baseUrl, 'http://127.0.0.1:')
            && !str_starts_with($this->baseUrl, 'http://localhost:')) {
            throw new \RuntimeException('Print Service must use a loopback URL');
        }
    }

    private static function env(string $name): string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            $value = $_SERVER[$name] ?? '';
        }
        return is_string($value) ? trim($value) : '';
    }

    public function request(string $method, string $path, ?array $body = null): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('The PHP cURL extension is required');
        }
        $handle = curl_init($this->baseUrl . $path);
        $headers = ['Authorization: Bearer ' . $this->token, 'Accept: application/json'];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS      => 0,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP,
        ]);
        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }
        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $errorNumber = curl_errno($handle);
        curl_close($handle);
        if ($response === false || $errorNumber !== 0) {
            throw new \RuntimeException('Could not connect to the local Print Service');
        }
        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Print Service returned an invalid response');
        }
        if ($status < 200 || $status >= 300) {
            $message = is_string($data['detail'] ?? null) ? $data['detail'] : 'Print Service request failed';
            throw new \RuntimeException($message);
        }
        return $data;
    }

    public function licenseStatus(array $installState, ?string $license = null): array
    {
        return $this->request('POST', '/v1/license/status', [
            'installation_id' => $installState['installation_id'],
            'trial_started_at' => $installState['installed_at'],
            'license_file' => $license ?: null,
        ]);
    }

    public function printers(): array
    {
        return $this->request('GET', '/v1/printers')['printers'] ?? [];
    }

    public function savePrinters(array $printers, array $installState, ?string $license = null): array
    {
        return $this->request('PUT', '/v1/printers', [
            'installation_id' => $installState['installation_id'],
            'trial_started_at' => $installState['installed_at'],
            'license_file' => $license ?: null,
            'printers' => array_values($printers),
        ]);
    }

    public function print(array $payload): array
    {
        return $this->request('POST', '/v1/print', $payload);
    }

    public function updateJob(string $requestId): array
    {
        return $this->request('GET', '/v1/jobs/' . rawurlencode($requestId));
    }

    public function checkPrinter(string $id): array
    {
        return $this->request('POST', '/v1/printers/' . rawurlencode($id) . '/check');
    }
}
