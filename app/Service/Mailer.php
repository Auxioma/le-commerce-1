<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Envoi d'e-mails transactionnels (ex. réinitialisation de mot de passe
 * admin) via un client SMTP minimal (sockets), sans dépendance externe.
 * Configuré par les variables MAIL_* du .env (voir .env.example). Si
 * MAIL_HOST est vide (environnement local sans SMTP configuré), l'e-mail
 * est simplement journalisé dans storage/logs/mail.log plutôt qu'envoyé.
 */
final class Mailer
{
    public function send(string $to, string $subject, string $html): bool
    {
        $host     = getenv('MAIL_HOST') ?: '';
        $port     = (int) (getenv('MAIL_PORT') ?: 587);
        $user     = getenv('MAIL_USER') ?: '';
        $pass     = getenv('MAIL_PASS') ?: '';
        $fromMail = getenv('MAIL_FROM') ?: 'noreply@le-commerce-1.immo';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Le Commerce';

        if ($host === '') {
            $this->logInsteadOfSend($to, $subject, $html);
            return true;
        }

        try {
            $this->deliver($host, $port, $user, $pass, $fromMail, $fromName, $to, $subject, $html);
            return true;
        } catch (\Throwable $e) {
            error_log('[Mailer] Échec envoi vers ' . $to . ' : ' . $e->getMessage());
            return false;
        }
    }

    private function deliver(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $fromMail,
        string $fromName,
        string $to,
        string $subject,
        string $html
    ): void {
        $useTls = $port === 465;
        $transport = $useTls ? 'ssl://' . $host : $host;

        $socket = @stream_socket_client($transport . ':' . $port, $errno, $errstr, 10);
        if ($socket === false) {
            throw new \RuntimeException("Connexion SMTP impossible ({$host}:{$port}) : {$errstr}");
        }
        stream_set_timeout($socket, 10);

        $this->expect($socket, 220);
        $this->command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

        if (!$useTls && $port === 587) {
            $this->command($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('Négociation TLS (STARTTLS) échouée.');
            }
            $this->command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);
        }

        if ($user !== '') {
            $this->command($socket, 'AUTH LOGIN', 334);
            $this->command($socket, base64_encode($user), 334);
            $this->command($socket, base64_encode($pass), 235);
        }

        $this->command($socket, 'MAIL FROM:<' . $fromMail . '>', 250);
        $this->command($socket, 'RCPT TO:<' . $to . '>', 250);
        $this->command($socket, 'DATA', 354);

        $headers = implode("\r\n", [
            'From: ' . $this->encodeHeader($fromName) . ' <' . $fromMail . '>',
            'To: <' . $to . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ]);

        $body = preg_replace('/^\.(.*)$/m', '..$1', $html);
        fwrite($socket, $headers . "\r\n\r\n" . $body . "\r\n.\r\n");
        $this->expect($socket, 250);

        $this->command($socket, 'QUIT', 221);
        fclose($socket);
    }

    private function command($socket, string $line, int $expectedCode): void
    {
        fwrite($socket, $line . "\r\n");
        $this->expect($socket, $expectedCode);
    }

    private function expect($socket, int $expectedCode): void
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            // La ligne finale d'une réponse SMTP a un espace après le code (pas un tiret)
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new \RuntimeException("Réponse SMTP inattendue (attendu {$expectedCode}) : {$response}");
        }
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function logInsteadOfSend(string $to, string $subject, string $html): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $entry = sprintf(
            "[%s] À: %s | Sujet: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $html,
            str_repeat('-', 60)
        );

        file_put_contents($logDir . '/mail.log', $entry, FILE_APPEND);
    }
}
