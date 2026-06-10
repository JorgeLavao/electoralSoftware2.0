<?php

namespace Tests\Unit;

use App\Services\ClientesMas\ClientesMasMessagingClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientesMasMessagingClientTest extends TestCase
{
    public function test_it_sends_utility_email_with_aws_ses_payload(): void
    {
        Http::fake([
            'app.clientesmas.com/api/messaging/utility/email' => Http::response(['id' => 123], 201),
        ]);

        config([
            'services.clientes_mas.api_key' => 'test-key',
            'services.clientes_mas.email_provider' => 'aws_ses',
            'services.clientes_mas.from_name' => 'SmartElect',
        ]);

        $response = app(ClientesMasMessagingClient::class)->sendUtilityEmail([
            'recipient' => 'cliente@example.com',
            'subject' => 'Asunto',
            'body' => 'Texto',
            'html_body' => '<p>Texto</p>',
            'metadata' => ['cliente_id' => 1],
        ]);

        $this->assertSame(['id' => 123], $response);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->hasHeader('X-Api-Key', 'test-key')
                && $payload['provider'] === 'aws_ses'
                && $payload['recipient'] === 'cliente@example.com'
                && $payload['metadata']['from_name'] === 'SmartElect'
                && ! isset($payload['provider_credentials']);
        });
    }

    public function test_it_sends_mox_credentials_for_utility_email(): void
    {
        Http::fake([
            'app.clientesmas.com/api/messaging/utility/email' => Http::response(['id' => 456], 201),
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

        app(ClientesMasMessagingClient::class)->sendUtilityEmail([
            'recipient' => 'cliente@example.com',
            'subject' => 'Asunto',
            'body' => 'Texto',
            'html_body' => '<p>Texto</p>',
        ]);

        Http::assertSent(function ($request) {
            $credentials = $request->data()['provider_credentials']['mox'] ?? [];

            return ($request->data()['provider'] ?? null) === 'mox'
                && $credentials['from_name'] === 'SmartElect'
                && $credentials['from_address'] === 'correo@example.com'
                && $credentials['auth_user'] === 'correo@example.com'
                && $credentials['auth_password'] === 'secret';
        });
    }

    public function test_it_sends_bulk_utility_email_payload(): void
    {
        Http::fake([
            'app.clientesmas.com/api/messaging/utility/email/bulk' => Http::response(['batch_id' => 55], 201),
        ]);

        config([
            'services.clientes_mas.api_key' => 'test-key',
            'services.clientes_mas.email_provider' => 'ses',
        ]);

        $response = app(ClientesMasMessagingClient::class)->sendBulkUtilityEmails([
            'subject' => 'Invitacion',
            'html_body' => '<p>Hola</p>',
            'metadata' => ['purpose' => 'invitation'],
            'recipients' => [
                ['email' => 'persona1@example.com'],
                ['email' => 'persona2@example.com'],
            ],
        ]);

        $this->assertSame(['batch_id' => 55], $response);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $payload['provider'] === 'aws_ses'
                && $payload['metadata']['purpose'] === 'invitation'
                && count($payload['recipients']) === 2;
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
                && $message['metadata']['from_name'] === 'SmartElect';
        });
    }
}
