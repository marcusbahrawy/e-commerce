<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\OrderRepository;
use App\Services\PaymentService;
use App\Support\Mail;

class StripeWebhookController
{
    public function __construct(
        private PaymentService $paymentService,
        private OrderRepository $orderRepo
    ) {
    }

    public function handle(Request $request, array $params): Response
    {
        $payload = file_get_contents('php://input');
        if ($payload === false || $payload === '') {
            return Response::html('', 400);
        }
        $signature = $request->header('Stripe-Signature') ?? '';
        if ($signature === '') {
            return Response::html('', 400);
        }
        $event = $this->paymentService->parseWebhook($payload, $signature);
        if ($event === null) {
            return Response::html('', 400);
        }
        if ($event->type === 'checkout.session.completed') {
            $sessionId = $event->data->object->id ?? '';
            if ($sessionId !== '') {
                $orderId = $this->paymentService->handleCheckoutCompleted($sessionId);
                if ($orderId !== null) {
                    $order = $this->orderRepo->findById($orderId);
                    if ($order !== null) {
                        Mail::sendOrderConfirmation($order);
                    }
                }
            }
        }
        return Response::html('', 200);
    }
}
