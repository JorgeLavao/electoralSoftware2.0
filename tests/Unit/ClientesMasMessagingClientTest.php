<?php

namespace Tests\Unit;

use App\Services\ClientesMas\ClientesMasMessagingClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientesMasMessagingClientTest extends TestCase
{
    public function test_it_sends_individual_email_with_aws_ses_payload(): void
    {
        Http::fake([
            'app.clientesmas.com/api/messaging/messages' => Http::response(['id' => 123], 200),
        ]);

        config([
            'services.clientes_mas.api_key' => 'test-key',
            'services.clientes_mas.email_provider' => 'aws_ses',
            'services.clientes_mas.from_name' => 'SmartElect',
        ]);

        $response = app(ClientesMasMessagingClient::class)->sendEmail(
            recipient: 'cliente@example.com',
            subject: 'Asunto',
            body: 'Texto',
            htmlBody: '<p>Texto</p>',
            metadata: ['cliente_id' => 1],
            externalId: 'cliente-001'
        );

        $this->assertSame(['id' => 123], $response);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->hasHeader('X-Api-Key', 'test-key')
                && $payload['channel'] === 'email'
                && $payload['provider'] === 'aws_ses'
                && $payload['recipient'] === 'cliente@example.com'
                && $payload['metadata']['from_name'] === 'SmartElect'
                && ! isset($payload['provider_credentials']);
        });
    }

    public function test_it_sends_mox_credentials_for_individual_email(): void
    {
        Http::fake([
            'app.clientesmas.com/api/messaging/messages' => Http::response(['id' => 456], 200),
        ]);

        config([
            'services.clientes_mas.api_key' => 'test-key',
            'services.clientes_mas.email_provider' => 'mox',
            'services.clientes_mas.mox' => [
                'from_name' => 'SmartElect',
                'from_address' => 'correo@example.com',
                'auth_user' => 'correo@example.com',
                'auth_password' => 'secret',
            ],
        ]);

        app(ClientesMasMessagingClient::class)->sendEmail(
            recipient: 'cliente@example.com',
            subject: 'Asunto',
            body: 'Texto',
            htmlBody: '<p>Texto</p>',
            externalId: 'cliente-001'
        );

        Http::assertSent(function ($request) {
            $credentials = $request->data()['provider_credentials']['mox'] ?? [];

            return ($request->data()['provider'] ?? null) === 'mox'
                && $credentials['from_name'] === 'SmartElect'
                && $credentials['from_address'] === 'correo@example.com'
                && $credentials['auth_user'] === 'correo@example.com'
                && $credentials['auth_password'] === 'secret';
        });
    }

    public function test_it_imports_batch_messages_with_a_limit_of_1000(): void
    {
        Http::fake([
            'app.clientesmas.com/api/messaging/batches/10/messages/import' => Http::response(['imported' => 1], 200),
        ]);

        config([
            'services.clientes_mas.api_key' => 'test-key',
            'services.clientes_mas.email_provider' => 'aws_ses',
            'services.clientes_mas.from_name' => 'SmartElect',
        ]);

        app(ClientesMasMessagingClient::class)->importBatchMessages(10, [[
            'external_id' => 'cliente-001',
            'recipient' => 'cliente@example.com',
            'subject' => 'Asunto',
            'body' => 'Texto',
            'html_body' => '<p>Texto</p>',
            'recipient_metadata' => ['name' => 'Cliente 1'],
            'metadata' => ['cliente_id' => 1],
        ]]);

        Http::assertSent(function ($request) {
            $message = $request->data()['messages'][0] ?? [];

            return $message['provider'] === 'aws_ses'
                && $message['recipient_type'] === 'email'
                && $message['metadata']['from_name'] === 'SmartElect';
        });
    }
}
