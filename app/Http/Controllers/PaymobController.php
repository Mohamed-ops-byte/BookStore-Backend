<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobController extends Controller
{
    private $apiKey;
    private $integrationId;
    private $iframeId;
    private $hmacSecret;
    private $baseUrl = 'https://accept.paymob.com/api';

    public function __construct()
    {
        $this->apiKey = env('PAYMOB_API_KEY');
        $this->integrationId = env('PAYMOB_INTEGRATION_ID');
        $this->iframeId = env('PAYMOB_IFRAME_ID');
        $this->hmacSecret = env('PAYMOB_HMAC_SECRET');
        
        // تسجيل بيانات الإعدادات (للتصحيح فقط)
        Log::warning('Paymob Config Loaded', [
            'has_api_key' => !empty($this->apiKey),
            'has_integration_id' => !empty($this->integrationId),
            'has_iframe_id' => !empty($this->iframeId),
            'has_hmac_secret' => !empty($this->hmacSecret),
        ]);
    }

    /**
     * Step 1: Authentication - Get Auth Token
     */
    private function authenticate()
    {
        // تحقق من بيانات المصادقة
        if (!$this->apiKey || strpos($this->apiKey, 'your_') === 0) {
            Log::error('Paymob API Key not configured properly', [
                'api_key_value' => $this->apiKey,
                'issue' => 'API key is missing or contains placeholder value',
            ]);
            return null;
        }

        try {
            Log::info('Authenticating with Paymob API...');
            
            // Disable SSL verification in development (fixes cURL error 60)
            $response = Http::withOptions([
                'verify' => false, // Skip SSL verification for local development
            ])->post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                Log::info('Paymob Authentication Successful');
                return $response->json()['token'];
            }

            Log::error('Paymob Authentication Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob Authentication Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Step 2: Order Registration
     */
    private function registerOrder($authToken, $amount, $orderId, $orderNumber = null)
    {
        try {
            // Create unique merchant order ID to avoid duplicates
            $merchantOrderId = $orderNumber ?? 'ORD-' . $orderId . '-' . time();
            
            Log::info('Registering order with Paymob', [
                'order_id' => $orderId,
                'merchant_order_id' => $merchantOrderId,
                'amount' => $amount,
                'amount_cents' => $amount * 100,
            ]);

            $response = Http::withOptions(['verify' => false])->post("{$this->baseUrl}/ecommerce/orders", [
                'auth_token' => $authToken,
                'delivery_needed' => 'false',
                'amount_cents' => $amount * 100, // Convert to cents
                'currency' => 'EGP',
                'merchant_order_id' => $merchantOrderId,
            ]);

            if ($response->successful()) {
                $paymobOrderId = $response->json()['id'];
                Log::info('Order registered with Paymob', ['paymob_order_id' => $paymobOrderId]);
                return $paymobOrderId;
            }

            Log::error('Paymob Order Registration Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob Order Registration Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Step 3: Payment Key Generation
     */
    private function getPaymentKey($authToken, $amount, $orderId, $paymobOrderId, $billingData)
    {
        try {
            Log::info('Generating payment key', [
                'paymob_order_id' => $paymobOrderId,
                'amount_cents' => $amount * 100,
            ]);

            $response = Http::withOptions(['verify' => false])->post("{$this->baseUrl}/acceptance/payment_keys", [
                'auth_token' => $authToken,
                'amount_cents' => $amount * 100,
                'expiration' => 3600, // 1 hour
                'order_id' => $paymobOrderId,
                'billing_data' => [
                    'apartment' => $billingData['apartment'] ?? 'NA',
                    'email' => $billingData['email'],
                    'floor' => $billingData['floor'] ?? 'NA',
                    'first_name' => $billingData['first_name'],
                    'street' => $billingData['street'] ?? 'NA',
                    'building' => $billingData['building'] ?? 'NA',
                    'phone_number' => $billingData['phone'],
                    'shipping_method' => 'PKG',
                    'postal_code' => $billingData['postal_code'] ?? 'NA',
                    'city' => $billingData['city'],
                    'country' => $billingData['country'] ?? 'EG',
                    'last_name' => $billingData['last_name'],
                    'state' => $billingData['state'] ?? 'NA',
                ],
                'currency' => 'EGP',
                'integration_id' => $this->integrationId,
            ]);

            if ($response->successful()) {
                $paymentKey = $response->json()['token'];
                Log::info('Payment key generated successfully', ['key_length' => strlen($paymentKey)]);
                return $paymentKey;
            }

            Log::error('Paymob Payment Key Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob Payment Key Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Initiate Paymob Payment
     * POST /api/paymob/initiate
     */
    public function initiatePayment(Request $request)
    {
        Log::info('💳 Paymob payment initiation started', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::warning('❌ Unauthorized payment attempt');
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:1',
                'billing_data' => 'required|array',
                'billing_data.first_name' => 'required|string',
                'billing_data.last_name' => 'required|string',
                'billing_data.email' => 'required|email',
                'billing_data.phone' => 'required|string',
                'billing_data.city' => 'required|string',
            ]);

            Log::info('✅ Validation passed', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation failed', [
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            Log::info('🔍 Looking for order', ['order_id' => $validated['order_id']]);
            $order = Order::findOrFail($validated['order_id']);
            Log::info('✅ Order found', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'current_user_id' => $user->id,
            ]);

            // تحقق من أن الطلب يخص المستخدم الحالي
            if ($order->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية للوصول لهذا الطلب',
                ], 403);
            }

            // Step 1: Get Auth Token
            $authToken = $this->authenticate();
            if (!$authToken) {
                Log::error('🔐 Payment initiation failed: Cannot authenticate with Paymob', [
                    'order_id' => $order->id,
                    'api_key_exists' => !empty($this->apiKey),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في المصادقة مع Paymob - تحقق من بيانات الإعدادات',
                    'debug' => [
                        'issue' => 'Paymob credentials not configured properly',
                        'check' => 'Update PAYMOB_API_KEY in .env file',
                    ]
                ], 500);
            }

            // Step 2: Register Order
            $paymobOrderId = $this->registerOrder(
                $authToken, 
                $validated['amount'], 
                $order->id, 
                $order->order_number
            );
            if (!$paymobOrderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تسجيل الطلب مع Paymob',
                ], 500);
            }

            // Step 3: Get Payment Key
            $paymentKey = $this->getPaymentKey(
                $authToken,
                $validated['amount'],
                $order->id,
                $paymobOrderId,
                $validated['billing_data']
            );

            if (!$paymentKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في إنشاء مفتاح الدفع',
                ], 500);
            }

            // Update order with payment info
            $order->update([
                'payment_status' => 'processing',
                'payment_method' => 'paymob',
                'transaction_id' => $paymobOrderId,
            ]);

            // Return payment key and iframe URL
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء طلب الدفع بنجاح',
                'data' => [
                    'payment_key' => $paymentKey,
                    'iframe_url' => "https://accept.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentKey}",
                    'paymob_order_id' => $paymobOrderId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Paymob Payment Initiation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطأ في إنشاء عملية الدفع',
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }

    /**
     * Handle Paymob Callback
     * POST /api/paymob/callback
     */
    public function handleCallback(Request $request)
    {
        try {
            // Get all request data
            $data = $request->all();

            // Verify HMAC
            if (!$this->verifyHmac($data)) {
                Log::warning('Paymob HMAC Verification Failed');
                return response()->json([
                    'success' => false,
                    'message' => 'HMAC verification failed',
                ], 400);
            }

            $orderId = $data['obj']['order']['merchant_order_id'] ?? null;
            $success = $data['obj']['success'] ?? false;
            $transactionId = $data['obj']['id'] ?? null;
            $amountCents = $data['obj']['amount_cents'] ?? 0;

            if (!$orderId) {
                Log::error('Paymob Callback: Missing order ID');
                return response('Missing order ID', 400);
            }

            $order = Order::find($orderId);
            if (!$order) {
                Log::error("Paymob Callback: Order not found - ID: {$orderId}");
                return response('Order not found', 404);
            }

            if ($success && $data['obj']['pending'] === false) {
                // Payment successful
                $order->update([
                    'payment_status' => 'completed',
                    'status' => 'processing',
                    'amount_paid' => $amountCents / 100,
                    'payment_date' => now(),
                    'payment_details' => [
                        'transaction_id' => $transactionId,
                        'paymob_order_id' => $data['obj']['order']['id'] ?? null,
                        'currency' => $data['obj']['currency'] ?? 'EGP',
                        'payment_method' => $data['obj']['source_data']['type'] ?? 'card',
                    ],
                ]);

                Log::info("Paymob Payment Successful - Order: {$orderId}");
            } else {
                // Payment failed
                $order->update([
                    'payment_status' => 'failed',
                    'payment_details' => [
                        'error' => $data['obj']['data']['message'] ?? 'Payment failed',
                        'transaction_id' => $transactionId,
                    ],
                ]);

                Log::warning("Paymob Payment Failed - Order: {$orderId}");
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Paymob Callback Error: ' . $e->getMessage());
            return response('Error processing callback', 500);
        }
    }

    /**
     * Verify Payment Status
     * GET /api/paymob/verify/{orderId}
     */
    public function verifyPayment($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'amount_paid' => $order->amount_paid,
                    'payment_method' => $order->payment_method,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من حالة الدفع',
            ], 500);
        }
    }

    /**
     * Verify HMAC Signature
     */
    private function verifyHmac($data)
    {
        if (!isset($data['hmac'])) {
            return false;
        }

        $hmacData = [
            $data['obj']['amount_cents'] ?? '',
            $data['obj']['created_at'] ?? '',
            $data['obj']['currency'] ?? '',
            $data['obj']['error_occured'] ?? '',
            $data['obj']['has_parent_transaction'] ?? '',
            $data['obj']['id'] ?? '',
            $data['obj']['integration_id'] ?? '',
            $data['obj']['is_3d_secure'] ?? '',
            $data['obj']['is_auth'] ?? '',
            $data['obj']['is_capture'] ?? '',
            $data['obj']['is_refunded'] ?? '',
            $data['obj']['is_standalone_payment'] ?? '',
            $data['obj']['is_voided'] ?? '',
            $data['obj']['order']['id'] ?? '',
            $data['obj']['owner'] ?? '',
            $data['obj']['pending'] ?? '',
            $data['obj']['source_data']['pan'] ?? '',
            $data['obj']['source_data']['sub_type'] ?? '',
            $data['obj']['source_data']['type'] ?? '',
            $data['obj']['success'] ?? '',
        ];

        $concatenatedString = implode('', $hmacData);
        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $this->hmacSecret);

        return $calculatedHmac === $data['hmac'];
    }

    /**
     * Get Paymob Configuration
     * GET /api/paymob/config
     */
    public function getConfig()
    {
        return response()->json([
            'success' => true,
            'iframe_id' => $this->iframeId,
        ]);
    }

    /**
     * Test Webhook Endpoint (for testing ngrok connection)
     * GET /api/paymob/webhook-test
     */
    public function webhookTest()
    {
        Log::info('Paymob Webhook Test Endpoint Called', [
            'timestamp' => now(),
            'ip' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is working! 🎉',
            'timestamp' => now(),
            'your_ip' => request()->ip(),
            'server_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
