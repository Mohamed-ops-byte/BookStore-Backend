<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateUniqueOrderNumber();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_method',
        'payment_status',
        'shipping_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'city',
        'address',
        'postal_code',
        'notes',
        'items_count',
        'items',
        'totals',
        'shipping',
        'transaction_id',
        'amount_paid',
        'payment_details',
        'payment_date',
    ];

    protected $casts = [
        'items' => 'array',
        'totals' => 'array',
        'shipping' => 'array',
        'payment_details' => 'array',
        'payment_date' => 'datetime',
    ];

    public static function generateUniqueOrderNumber(): string
    {
        $date = now()->format('Ymd');

        for ($i = 0; $i < 10; $i++) {
            $random = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = "ORD-{$date}-{$random}";

            if (!self::where('order_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'ORD-' . $date . '-' . uniqid();
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
