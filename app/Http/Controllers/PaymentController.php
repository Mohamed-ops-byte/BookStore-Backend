<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Charge;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set Stripe API key from environment
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Initiate Stripe Payment Intent
     * POST /api/payments/create-intent
     */
    public function createPaymentIntent(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1', // بالـ cents
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            
            // تحقق من أن الطلب يخص المستخدم الحالي أو أنه admin
            if ($order->user_id !== $user->id && !$user->$user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية للوصول لهذا الطلب',
                ], 403);
            }

            // Create Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) $validated['amount'],
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'order_number' => $order->order_number,
                ],
                'description' => 'طلب #' . $order->order_number,
            ]);

            // Update order with payment status
            $order->update([
                'payment_status' => 'processing',
                'transaction_id' => $paymentIntent->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء نية الدفع بنجاح',
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في إنشاء نية الدفع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm Payment
     * POST /api/payments/confirm
     */
    public function confirmPayment(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            // Retrieve payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status === 'succeeded') {
                $order = Order::findOrFail($validated['order_id']);

                // Update order payment details
                $order->update([
                    'payment_status' => 'completed',
                    'payment_method' => 'stripe',
                    'amount_paid' => $paymentIntent->amount / 100, // Convert from cents
                    'payment_date' => now(),
                    'payment_details' => [
                        'charge_id' => $paymentIntent->latest_charge,
                        'currency' => $paymentIntent->currency,
                        'receipt_email' => $paymentIntent->receipt_email,
                    ],
                    'status' => 'processing', // Update order status to processing
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تأكيد الدفع بنجاح',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_status' => $order->payment_status,
                        'amount_paid' => $order->amount_paid,
                    ],
                ]);
            } else {
                // Payment not yet succeeded
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تأكيد الدفع بعد',
                    'status' => $paymentIntent->status,
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تأكيد الدفع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Payment Status
     * GET /api/payments/status/{paymentIntentId}
     */
    public function getPaymentStatus($paymentIntentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paymentIntent->id,
                    'status' => $paymentIntent->status,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في الحصول على حالة الدفع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook handler for Stripe events
     * POST /api/webhooks/stripe
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload = @json_decode(file_get_contents('php://input'), true);
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                file_get_contents('php://input'),
                $sig_header,
                env('STRIPE_WEBHOOK_SECRET')
            );
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // Handle event types
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                
                if (isset($paymentIntent->metadata->order_id)) {
                    $order = Order::find($paymentIntent->metadata->order_id);
                    if ($order) {
                        $order->update([
                            'payment_status' => 'completed',
                            'status' => 'processing',
                        ]);
                    }
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                
                if (isset($paymentIntent->metadata->order_id)) {
                    $order = Order::find($paymentIntent->metadata->order_id);
                    if ($order) {
                        $order->update([
                            'payment_status' => 'failed',
                        ]);
                    }
                }
                break;

            default:
                // Unhandled event type
                break;
        }

        return response('Webhook processed', 200);
    }

    /**
     * Get Publishable Key
     * GET /api/payments/publishable-key
     */
    public function getPublishableKey()
    {
        return response()->json([
            'success' => true,
            'publishable_key' => env('STRIPE_PUBLIC_KEY'),
        ]);
    }
}
