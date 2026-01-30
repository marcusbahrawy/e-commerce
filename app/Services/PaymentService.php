<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;
use Stripe\StripeClient;
use Stripe\Webhook;

final class PaymentService
{
    private OrderRepository $orderRepo;
    private ?StripeClient $stripe;
    private string $webhookSecret;

    public function __construct(OrderRepository $orderRepo, string $secretKey, string $webhookSecret)
    {
        $this->orderRepo = $orderRepo;
        $this->webhookSecret = $webhookSecret;
        $this->stripe = $secretKey !== '' ? new StripeClient($secretKey) : null;
    }

    /** Create Stripe Checkout Session; returns checkout URL or null if Stripe not configured / total 0. */
    public function createCheckoutSession(int $orderId, string $orderPublicId, int $totalOre, string $email, string $successUrl, string $cancelUrl): ?string
    {
        if ($this->stripe === null || $totalOre < 1) {
            return null;
        }
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'nok',
                    'product_data' => [
                        'name' => 'Ordre ' . $orderPublicId,
                        'description' => 'Motorleaks — delebestilling',
                    ],
                    'unit_amount' => $totalOre,
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'order_public_id' => $orderPublicId,
                'order_id' => (string) $orderId,
            ],
        ]);
        $this->orderRepo->addPayment($orderId, 'stripe', $session->id, $totalOre, 'created');
        return $session->url;
    }

    /** Verify webhook signature and return event, or null if invalid. */
    public function parseWebhook(string $payload, string $signature): ?object
    {
        if ($this->webhookSecret === '') {
            return null;
        }
        try {
            return Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Handle checkout.session.completed — set order paid. Returns order ID on success, null otherwise. */
    public function handleCheckoutCompleted(string $sessionId): ?int
    {
        $orderId = $this->orderRepo->findOrderIdByStripeSessionId($sessionId);
        if ($orderId === null) {
            return null;
        }
        $this->orderRepo->updateOrderStatus($orderId, 'paid', 'paid');
        $this->orderRepo->updatePaymentStatus($orderId, 'captured');
        return $orderId;
    }
}
