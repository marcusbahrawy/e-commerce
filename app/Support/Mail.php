<?php

declare(strict_types=1);

namespace App\Support;

final class Mail
{
    /** Send order confirmation email to customer after payment. */
    public static function sendOrderConfirmation(array $order): void
    {
        $to = trim($order['email'] ?? '');
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $publicId = $order['public_id'] ?? '';
        $totalOre = (int) ($order['total_ore'] ?? 0);
        $totalFormatted = Money::format($totalOre);
        $baseUrl = rtrim(Env::string('APP_URL', ''), '/');
        $accountUrl = $baseUrl . '/konto/ordre';
        $subject = 'Ordrebekreftelse ' . $publicId . ' — Motorleaks';
        $body = "Hei,\n\n"
            . "Takk for bestillingen! Betalingen er mottatt.\n\n"
            . "Ordrenummer: " . $publicId . "\n"
            . "Total: " . $totalFormatted . "\n\n"
            . "Du kan se ordredetaljer her: " . $accountUrl . "\n\n"
            . "— Motorleaks";
        $from = Env::string('MAIL_FROM', 'noreply@motorleaks.no');
        $headers = 'From: ' . $from . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
        @mail($to, $subject, $body, $headers);
    }
}
