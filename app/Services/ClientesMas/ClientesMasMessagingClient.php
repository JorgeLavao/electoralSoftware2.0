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
    private const PROVIDER_ALIASES = [
        'mox' => self::PROVIDER_MOX,
        'aws' => self::PROVIDER_AWS_SES,
        'aws_ses' => self::PROVIDER_AWS_SES,
        'ses' => self::PROVIDER_AWS_SES,
    ];

    public function sendUtilityEmail(array $payload): array
    {
        $payload = $this->emailPayload($payload);
        $this->validateSingleEmailPayload($payload, requireSubject: true);

        try {
            return $this->post('/utility/email', $payload, 'send utility email');
        } catch (ClientesMasMessagingException $exception) {
            if (! $this->shouldFallbackToMessageEndpoint($exception)) {
                throw $exception;
            }

            Log::warning('Clientes Mas utility email endpoint failed; falling back to messages endpoint.', [
                'status' => $exception->status,
                'recipient' => $payload['recipient'] ?? null,
                'purpose' => data_get($payload, 'metadata.purpose'),
            ]);

            return $this->sendMessage(array_merge($payload, [
                'channel' => 'email',
            ]));
        }
    }

    public function sendBulkUtilityEmails(array $payload): array
    {
        $payload = $this->emailPayload($payload);

        if (empty($payload['recipients']) || ! is_array($payload['recipients'])) {
            throw new InvalidArgumentException('Clientes Mas utility bulk email requires recipients.');
        }

        if (count($payload['recipients']) > 10000) {
            throw new InvalidArgumentException('Clientes Mas utility bulk email only allows up to 10000 recipients.');
        }

        $hasGlobalContent = filled($payload['subject'] ?? null)
            && (filled($payload['body'] ?? null) || filled($payload['html_body'] ?? null));

        foreach ($payload['recipients'] as $recipient) {
            if (! is_array($recipient) || blank($recipient['email'] ?? null)) {
                throw new InvalidArgumentException('Clientes Mas utility bulk email requires an email for every recipient.');
            }

            if (! $hasGlobalContent && (
                blank($recipient['subject'] ?? null)
                || (blank($recipient['body'] ?? null) && blank($recipient['html_body'] ?? null))
            )) {
                throw new InvalidArgumentException('Clientes Mas utility bulk email requires global content or content for every recipient.');
            }
        }

        return $this->post('/utility/email/bulk', $payload, 'send bulk utility emails');
    }

    public function sendMessage(array $payload): array
    {
        $payload = ($payload['channel'] ?? null) === 'email'
            ? $this->emailPayload($payload)
            : $payload;

        if (($payload['channel'] ?? null) === 'email') {
            $this->validateSingleEmailPayload($payload, requireSubject: false);
        }

        return $this->post('/messages', $payload, 'send message');
    }

    public function sendEmail(
        string $recipient,
        string $subject,
        ?string $body = null,
        ?string $htmlBody = null,
        array $metadata = [],
        ?string $externalId = null,
        ?string $provider = null
    ): array {
        return $this->sendMessage([
            'channel' => 'email',
            'provider' => $provider,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'html_body' => $htmlBody,
            'external_id' => $externalId ?: (string) Str::uuid(),
            'metadata' => $metadata,
        ]);
    }

    public function createCampaign(array|string $payload, array $metadata = [], ?string $provider = null): array
    {
        if (is_string($payload)) {
            $provider = $this->normalizeProvider($provider);
            $payload = [
                'name' => $payload,
                'channel' => 'email',
                'metadata' => array_merge([
                    'source' => 'smartelect',
                    'provider' => $provider,
                ], $metadata),
            ];
        }

        return $this->post('/campaigns', $payload, 'create campaign');
    }

    public function createBatch(int|string $campaignId, array $metadata = []): array
    {
        $payload = array_key_exists('metadata', $metadata) ? $metadata : ['metadata' => $metadata];

        return $this->post("/campaigns/{$campaignId}/batches", $payload, 'create batch');
    }

    public function importBatchMessages(int|string $batchId, array $payload, ?string $provider = null): array
    {
        $messages = $payload['messages'] ?? $payload;

        if (empty($messages) || ! is_array($messages)) {
            throw new InvalidArgumentException('Clientes Mas batch import requires messages.');
        }

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

    public function dispatchBatch(int|string $batchId, array|int $payload = 1000, ?string $provider = null): array
    {
        if (is_array($payload)) {
            return $this->post("/batches/{$batchId}/dispatch", $payload, 'dispatch batch');
        }

        $provider = $this->normalizeProvider($provider);
        $payload = ['chunk_size' => $payload];

        if ($provider === self::PROVIDER_MOX) {
            $payload['provider_credentials'] = [
                self::PROVIDER_MOX => $this->moxCredentials(),
            ];
        }

        return $this->post("/batches/{$batchId}/dispatch", $payload, 'dispatch email batch');
    }

    public function message(int|string $messageId): array
    {
        return $this->getMessageStatus($messageId);
    }

    public function messageEvents(int|string $messageId): array
    {
        return $this->getMessageEvents($messageId);
    }

    public function getMessageStatus(int|string $messageId): array
    {
        return $this->get("/messages/{$messageId}", 'get message status');
    }

    public function getMessageEvents(int|string $messageId): array
    {
        return $this->get("/messages/{$messageId}/events", 'get message events');
    }

    public function getBatchStatus(int|string $batchId): array
    {
        return $this->get("/batches/{$batchId}", 'get batch status');
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
        return array_filter([
            'external_id' => $message['external_id'] ?? (string) Str::uuid(),
            'channel' => 'email',
            'provider' => $provider,
            'recipient' => $message['recipient'] ?? null,
            'subject' => $message['subject'] ?? null,
            'body' => $message['body'] ?? null,
            'html_body' => $message['html_body'] ?? null,
            'recipient_metadata' => $message['recipient_metadata'] ?? [],
            'metadata' => $this->metadataForProvider($provider, $message['metadata'] ?? []),
        ], fn ($value) => $value !== null);
    }

    private function emailPayload(array $payload): array
    {
        $provider = $this->normalizeProvider($payload['provider'] ?? null);
        $payload['provider'] = $provider;
        $payload['metadata'] = $this->metadataForProvider($provider, $payload['metadata'] ?? []);

        if ($provider === self::PROVIDER_MOX && ! isset($payload['provider_credentials'])) {
            $payload['provider_credentials'] = [
                self::PROVIDER_MOX => $this->moxCredentials(),
            ];
        }

        if ($provider !== self::PROVIDER_MOX) {
            unset($payload['provider_credentials']);
        }

        return array_filter($payload, fn ($value) => $value !== null);
    }

    private function validateSingleEmailPayload(array $payload, bool $requireSubject): void
    {
        if (blank($payload['provider'] ?? null)) {
            throw new InvalidArgumentException('Clientes Mas email requires provider.');
        }

        if (blank($payload['recipient'] ?? null)) {
            throw new InvalidArgumentException('Clientes Mas email requires recipient.');
        }

        if ($requireSubject && blank($payload['subject'] ?? null)) {
            throw new InvalidArgumentException('Clientes Mas email requires subject.');
        }

        if (blank($payload['body'] ?? null) && blank($payload['html_body'] ?? null)) {
            throw new InvalidArgumentException('Clientes Mas email requires body or html_body.');
        }

        if (($payload['provider'] ?? null) === self::PROVIDER_MOX && blank(data_get($payload, 'provider_credentials.mox'))) {
            throw new InvalidArgumentException('Clientes Mas MOX email requires provider_credentials.mox.');
        }
    }

    private function shouldFallbackToMessageEndpoint(ClientesMasMessagingException $exception): bool
    {
        return $exception->status === 404 || ($exception->status !== null && $exception->status >= 500);
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
        $provider = strtolower($provider);

        if (! isset(self::PROVIDER_ALIASES[$provider])) {
            throw new InvalidArgumentException("Unsupported Clientes Mas email provider [{$provider}].");
        }

        return self::PROVIDER_ALIASES[$provider];
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
        $baseUrl = rtrim((string) config('services.clientes_mas.base_url'), '/');

        if (! str_ends_with($baseUrl, '/api/messaging')) {
            $baseUrl .= '/api/messaging';
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
