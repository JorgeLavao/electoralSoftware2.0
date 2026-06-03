<?php

namespace App\Services\ClientesMas;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ClientesMasMessagingClient
{
    public const PROVIDER_MOX = 'mox';
    public const PROVIDER_AWS_SES = 'aws_ses';

    public function sendEmail(
        string $recipient,
        string $subject,
        ?string $body = null,
        ?string $htmlBody = null,
        array $metadata = [],
        ?string $externalId = null,
        ?string $provider = null
    ): array {
        $provider = $this->normalizeProvider($provider);
        $metadata = $this->metadataForProvider($provider, $metadata);

        $payload = array_filter([
            'channel' => 'email',
            'provider' => $provider,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'html_body' => $htmlBody,
            'external_id' => $externalId ?: (string) Str::uuid(),
            'metadata' => $metadata,
        ], fn ($value) => $value !== null);

        if ($provider === self::PROVIDER_MOX) {
            $payload['provider_credentials'] = [
                self::PROVIDER_MOX => $this->moxCredentials(),
            ];
        }

        return $this->post('/messages', $payload, 'send individual email');
    }

    public function createCampaign(string $name, array $metadata = [], ?string $provider = null): array
    {
        $provider = $this->normalizeProvider($provider);

        return $this->post('/campaigns', [
            'name' => $name,
            'channel' => 'email',
            'metadata' => array_merge([
                'source' => 'smartelect',
                'provider' => $provider,
            ], $metadata),
        ], 'create email campaign');
    }

    public function createBatch(int|string $campaignId, array $metadata = []): array
    {
        return $this->post("/campaigns/{$campaignId}/batches", [
            'metadata' => $metadata,
        ], 'create email batch');
    }

    public function importBatchMessages(int|string $batchId, array $messages, ?string $provider = null): array
    {
        if (count($messages) > 1000) {
            throw new InvalidArgumentException('Clientes Mas only allows up to 1000 messages per import.');
        }

        $provider = $this->normalizeProvider($provider);

        $payloadMessages = collect($messages)
            ->map(fn (array $message) => $this->batchMessagePayload($message, $provider))
            ->all();

        return $this->post("/batches/{$batchId}/messages/import", [
            'messages' => $payloadMessages,
        ], 'import email batch messages');
    }

    public function dispatchBatch(int|string $batchId, int $chunkSize = 1000, ?string $provider = null): array
    {
        $provider = $this->normalizeProvider($provider);
        $payload = ['chunk_size' => $chunkSize];

        if ($provider === self::PROVIDER_MOX) {
            $payload['provider_credentials'] = [
                self::PROVIDER_MOX => $this->moxCredentials(),
            ];
        }

        return $this->post("/batches/{$batchId}/dispatch", $payload, 'dispatch email batch');
    }

    public function message(int|string $messageId): array
    {
        return $this->get("/messages/{$messageId}", 'get message');
    }

    public function messageEvents(int|string $messageId): array
    {
        return $this->get("/messages/{$messageId}/events", 'get message events');
    }

    public function campaignMetrics(int|string $campaignId): array
    {
        return $this->get("/campaigns/{$campaignId}/metrics", 'get campaign metrics');
    }

    public function batchMetrics(int|string $batchId): array
    {
        return $this->get("/batches/{$batchId}/metrics", 'get batch metrics');
    }

    public function pauseCampaign(int|string $campaignId): array
    {
        return $this->post("/campaigns/{$campaignId}/pause", [], 'pause campaign');
    }

    public function resumeCampaign(int|string $campaignId): array
    {
        return $this->post("/campaigns/{$campaignId}/resume", [], 'resume campaign');
    }

    public function cancelCampaign(int|string $campaignId): array
    {
        return $this->post("/campaigns/{$campaignId}/cancel", [], 'cancel campaign');
    }

    public function pauseBatch(int|string $batchId): array
    {
        return $this->post("/batches/{$batchId}/pause", [], 'pause batch');
    }

    public function resumeBatch(int|string $batchId): array
    {
        return $this->post("/batches/{$batchId}/resume", [], 'resume batch');
    }

    public function cancelBatch(int|string $batchId): array
    {
        return $this->post("/batches/{$batchId}/cancel", [], 'cancel batch');
    }

    private function batchMessagePayload(array $message, string $provider): array
    {
        $metadata = $this->metadataForProvider($provider, $message['metadata'] ?? []);

        return array_filter([
            'external_id' => $message['external_id'] ?? (string) Str::uuid(),
            'channel' => 'email',
            'provider' => $provider,
            'recipient' => $message['recipient'] ?? null,
            'subject' => $message['subject'] ?? null,
            'body' => $message['body'] ?? null,
            'html_body' => $message['html_body'] ?? null,
            'recipient_type' => 'email',
            'recipient_metadata' => $message['recipient_metadata'] ?? [],
            'metadata' => $metadata,
        ], fn ($value) => $value !== null);
    }

    private function get(string $path, string $action): array
    {
        try {
            $response = $this->request()->get($this->url($path));
        } catch (ConnectionException $exception) {
            $this->throwConnectionException($action, $exception);
        }

        return $this->handleResponse($response, $action);
    }

    private function post(string $path, array $payload, string $action): array
    {
        try {
            $response = $this->request()->post($this->url($path), $payload);
        } catch (ConnectionException $exception) {
            $this->throwConnectionException($action, $exception);
        }

        return $this->handleResponse($response, $action);
    }

    private function request(): PendingRequest
    {
        $apiKey = config('services.clientes_mas.api_key');

        if (! $apiKey) {
            throw new ClientesMasMessagingException('Clientes Mas API key is not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->withHeaders(['X-Api-Key' => $apiKey])
            ->timeout((int) config('services.clientes_mas.timeout', 15))
            ->retry(
                (int) config('services.clientes_mas.retries', 2),
                (int) config('services.clientes_mas.retry_sleep', 500),
                throw: false
            );
    }

    private function handleResponse(Response $response, string $action): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $status = $response->status();
        $context = [
            'action' => $action,
            'status' => $status,
            'response' => $response->json() ?? $response->body(),
        ];

        $level = $status >= 500 ? 'error' : 'warning';
        Log::log($level, 'Clientes Mas messaging request failed', $context);

        $message = match ($status) {
            401 => 'Clientes Mas rejected the API key.',
            422 => 'Clientes Mas rejected the message payload.',
            default => 'Clientes Mas messaging request failed.',
        };

        throw new ClientesMasMessagingException($message, $status, $context);
    }

    private function throwConnectionException(string $action, ConnectionException $exception): never
    {
        Log::error('Clientes Mas messaging connection failed', [
            'action' => $action,
            'message' => $exception->getMessage(),
        ]);

        throw new ClientesMasMessagingException(
            'Clientes Mas messaging request timed out or could not connect.',
            context: ['action' => $action],
            previous: $exception
        );
    }

    private function normalizeProvider(?string $provider): string
    {
        $provider = $provider ?: config('services.clientes_mas.email_provider', self::PROVIDER_AWS_SES);

        if (! in_array($provider, [self::PROVIDER_MOX, self::PROVIDER_AWS_SES], true)) {
            throw new InvalidArgumentException("Unsupported Clientes Mas email provider [{$provider}].");
        }

        return $provider;
    }

    private function metadataForProvider(string $provider, array $metadata): array
    {
        $metadata = array_merge(['source' => 'smartelect'], $metadata);

        if ($provider === self::PROVIDER_AWS_SES && ! isset($metadata['from_name'])) {
            $metadata['from_name'] = config('services.clientes_mas.from_name');
        }

        return $metadata;
    }

    private function moxCredentials(): array
    {
        $credentials = config('services.clientes_mas.mox', []);

        foreach (['from_name', 'from_address', 'auth_user', 'auth_password'] as $key) {
            if (blank($credentials[$key] ?? null)) {
                throw new ClientesMasMessagingException("Clientes Mas MOX credential [{$key}] is not configured.");
            }
        }

        return [
            'from_name' => $credentials['from_name'],
            'from_address' => $credentials['from_address'],
            'auth_user' => $credentials['auth_user'],
            'auth_password' => $credentials['auth_password'],
        ];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.clientes_mas.base_url'), '/').'/'.ltrim($path, '/');
    }
}
