<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders with simple filters.
     * Admins see all orders, users see only their own orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        $statusFilter = $request->get('status');
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = (int) $request->get('per_page', 20);
        $perPage = $perPage > 0 ? min($perPage, 100) : 20;

        $query = Order::query();

        // If user is not an admin, only show their own orders
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($statusFilter && $statusFilter !== 'الكل') {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_email', 'LIKE', "%{$search}%");
            });
        }

        $statsCollection = $query->get();

        $orders = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        $stats = [
            'total' => $statsCollection->count(),
            'pending' => $statsCollection->where('status', 'قيد التنفيذ')->count(),
            'completed' => $statsCollection->where('status', 'مكتمل')->count(),
            'canceled' => $statsCollection->where('status', 'ملغي')->count(),
            'revenue' => $statsCollection
                ->where('status', 'مكتمل')
                ->sum(function (Order $order) {
                    return $order->totals['total'] ?? 0;
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        $validated = $request->validate([
            'order_number' => 'nullable|string|max:50|unique:orders,order_number',
            'status' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:100',
            'shipping_status' => 'nullable|string|max:100',
            'customer' => 'required|array',
            'customer.first_name' => 'required|string|max:150',
            'customer.last_name' => 'required|string|max:150',
            'customer.email' => 'required|email',
            'customer.phone' => 'nullable|string|max:50',
            'customer.address' => 'nullable|string|max:255',
            'customer.city' => 'nullable|string|max:120',
            'customer.postal_code' => 'nullable|string|max:50',
            'customer.notes' => 'nullable|string',
            'totals' => 'required|array',
            'totals.subtotal' => 'required|numeric|min:0',
            'totals.shipping' => 'required|numeric|min:0',
            'totals.tax' => 'required|numeric|min:0',
            'totals.total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required',
            'items.*.title' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $items = $validated['items'];
        $customer = $validated['customer'];
        $itemsCount = collect($items)->sum(function ($item) {
            return (int) ($item['quantity'] ?? 0);
        });

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => $validated['order_number'] ?? null,
            'status' => $validated['status'] ?? 'قيد التنفيذ',
            'payment_method' => $validated['payment_method'] ?? 'غير محدد',
            'shipping_status' => $validated['shipping_status'] ?? 'قيد التجهيز',
            'customer_name' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')),
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['phone'] ?? null,
            'city' => $customer['city'] ?? null,
            'address' => $customer['address'] ?? null,
            'postal_code' => $customer['postal_code'] ?? null,
            'notes' => $validated['notes'] ?? $customer['notes'] ?? null,
            'items_count' => $itemsCount,
            'items' => $items,
            'totals' => $validated['totals'],
            'shipping' => $customer,
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ], 201);
    }

    /**
     * Display the specified order.
     * Users can only see their own orders, admins can see all.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        // Check authorization
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا الطلب',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update the specified order.
     * Only admins can update orders.
     */
    public function update(Request $request, Order $order)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        // Check authorization - only admins can update orders
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل الطلبات',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:100',
            'shipping_status' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الطلب بنجاح',
            'data' => $order,
        ]);
    }
}

