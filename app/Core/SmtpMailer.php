<?php

declare(strict_types=1);

namespace App\Core;

final class SmtpMailer
{
    public function __construct(
        private string $host,
        private int $port,
        private string $encryption, // none|tls|ssl
        private ?string $username,
        private ?string $password,
        private string $fromEmail,
        private string $fromName
    ) {}

    public function send(string $toEmail, string $subject, string $htmlBody): void
    {
        $socket = $this->connect();

        $this->expect($socket, 220);
        $this->write($socket, 'EHLO localhost');
        $this->readMultiline($socket);

        if ($this->encryption === 'tls') {
            $this->write($socket, 'STARTTLS');
            $this->expect($socket, 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('Falha ao iniciar TLS');
            }
            $this->write($socket, 'EHLO localhost');
            $this->readMultiline($socket);
        }

        if ($this->username !== null && $this->password !== null && $this->username !== '') {
            $this->write($socket, 'AUTH LOGIN');
            $this->expect($socket, 334);
            $this->write($socket, base64_encode($this->username));
            $this->expect($socket, 334);
            $this->write($socket, base64_encode($this->password));
            $this->expect($socket, 235);
        }

        $from = $this->fromEmail;
        $this->write($socket, 'MAIL FROM:<' . $from . '>');
        $this->expect($socket, 250);

        $this->write($socket, 'RCPT TO:<' . $toEmail . '>');
        $this->expect($socket, 250);

        $this->write($socket, 'DATA');
        $this->expect($socket, 354);

        $headers = [];
        $headers[] = 'From: ' . $this->encodeHeader($this->fromName) . ' <' . $this->fromEmail . '>';
        $headers[] = 'To: <' . $toEmail . '>';
        $headers[] = 'Subject: ' . $this->encodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody;
        $data = str_replace("\n.", "\n..", $data);

        $this->writeRaw($socket, $data . "\r\n.\r\n");
        $this->expect($socket, 250);

        $this->write($socket, 'QUIT');
        fclose($socket);
    }

    private function connect()
    {
        $transport = ($this->encryption === 'ssl') ? 'ssl://' : '';
        $remote = $transport . $this->host . ':' . $this->port;

        $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            throw new \RuntimeException('Falha ao conectar SMTP: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($socket, 15);
        return $socket;
    }

    private function write($socket, string $line): void
    {
        $this->writeRaw($socket, $line . "\r\n");
    }

    private function writeRaw($socket, string $data): void
    {
        $len = strlen($data);
        $sent = 0;
        while ($sent < $len) {
            $w = fwrite($socket, substr($data, $sent));
            if ($w === false) {
                throw new \RuntimeException('Falha ao escrever no SMTP');
            }
            $sent += $w;
        }
    }

    private function expect($socket, int $code): void
    {
        $line = $this->readLine($socket);
        $got = (int)substr($line, 0, 3);
        if ($got !== $code) {
            throw new \RuntimeException('SMTP inesperado. Esperado ' . $code . ', obtido: ' . $line);
        }
    }

    private function readLine($socket): string
    {
        $line = fgets($socket);
        if ($line === false) {
            throw new \RuntimeException('Falha ao ler resposta SMTP');
        }
        return rtrim($line, "\r\n");
    }

    private function readMultiline($socket): void
    {
        while (true) {
            $line = $this->readLine($socket);
            if (strlen($line) < 4) {
                break;
            }
            if ($line[3] !== '-') {
                break;
            }
        }
    }

    private function encodeHeader(string $text): string
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
}
