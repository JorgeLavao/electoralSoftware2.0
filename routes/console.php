<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\ClientesMas\ClientesMasMessagingClient;
use App\Services\ClientesMas\ClientesMasMessagingException;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clientes-mas:test-email {recipient} {--provider=} {--subject=Prueba Clientes Mas}', function (ClientesMasMessagingClient $client) {
    $recipient = (string) $this->argument('recipient');
    $provider = $this->option('provider') ?: null;
    $subject = (string) $this->option('subject');

    try {
        $response = $client->sendEmail(
            recipient: $recipient,
            subject: $subject,
            body: 'Correo de prueba enviado desde SmartElect usando Clientes Mas.',
            htmlBody: '<p>Correo de prueba enviado desde <strong>SmartElect</strong> usando Clientes Mas.</p>',
            metadata: ['source' => 'artisan-test-email'],
            externalId: 'artisan-test-email-'.now()->format('YmdHis'),
            provider: $provider
        );

        $this->info('Correo enviado a Clientes Mas correctamente.');
        $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } catch (ClientesMasMessagingException $exception) {
        $this->error($exception->getMessage());

        if ($exception->status) {
            $this->line('HTTP status: '.$exception->status);
        }

        if ($exception->context) {
            $this->line(json_encode($exception->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return self::FAILURE;
    }

    return self::SUCCESS;
})->purpose('Send a test email through Clientes Mas messaging API');
