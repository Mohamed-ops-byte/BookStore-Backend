<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = [
            [
                'order_number' => 'ORD-1024',
                'status' => 'قيد التنفيذ',
                'payment_method' => 'بطاقة',
                'shipping_status' => 'قيد الشحن',
                'customer_name' => 'أحمد حمدي',
                'customer_email' => 'ahmed.hamdy@example.com',
                'customer_phone' => '01000001024',
                'city' => 'القاهرة',
                'address' => 'شارع النصر، مدينة نصر',
                'postal_code' => '11511',
                'items_count' => 3,
                'items' => [
                    ['id' => 1, 'title' => 'البؤساء', 'author' => 'فيكتور هيجو', 'quantity' => 1, 'price' => 200, 'category' => 'روايات'],
                    ['id' => 2, 'title' => 'الحرب والسلام', 'author' => 'ليو تولستوي', 'quantity' => 1, 'price' => 250, 'category' => 'روايات'],
                    ['id' => 5, 'title' => 'موجز تاريخ الزمن', 'author' => 'ستيفن هوكينج', 'quantity' => 1, 'price' => 140, 'category' => 'علمية'],
                ],
                'totals' => ['subtotal' => 590, 'shipping' => 0, 'tax' => 59, 'total' => 649],
                'shipping' => [
                    'first_name' => 'أحمد',
                    'last_name' => 'حمدي',
                    'email' => 'ahmed.hamdy@example.com',
                    'phone' => '01000001024',
                    'address' => 'شارع النصر، مدينة نصر',
                    'city' => 'القاهرة',
                    'postal_code' => '11511',
                ],
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'order_number' => 'ORD-1023',
                'status' => 'مكتمل',
                'payment_method' => 'مدفوع',
                'shipping_status' => 'تم التسليم',
                'customer_name' => 'منى السيد',
                'customer_email' => 'mona.alsayed@example.com',
                'customer_phone' => '01000001023',
                'city' => 'الإسكندرية',
                'address' => 'شارع السلطان حسين',
                'postal_code' => '21532',
                'items_count' => 5,
                'items' => [
                    ['id' => 3, 'title' => 'مئة عام من العزلة', 'author' => 'ماركيز', 'quantity' => 2, 'price' => 180, 'category' => 'روايات'],
                    ['id' => 7, 'title' => 'تاريخ موجز للبشرية', 'author' => 'يوفال هراري', 'quantity' => 1, 'price' => 170, 'category' => 'تاريخ'],
                    ['id' => 9, 'title' => 'الذكاء الاصطناعي', 'author' => 'ستيوارت راسل', 'quantity' => 2, 'price' => 190, 'category' => 'تقنية'],
                ],
                'totals' => ['subtotal' => 910, 'shipping' => 0, 'tax' => 91, 'total' => 1001],
                'shipping' => [
                    'first_name' => 'منى',
                    'last_name' => 'السيد',
                    'email' => 'mona.alsayed@example.com',
                    'phone' => '01000001023',
                    'address' => 'شارع السلطان حسين',
                    'city' => 'الإسكندرية',
                    'postal_code' => '21532',
                ],
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'order_number' => 'ORD-1022',
                'status' => 'ملغي',
                'payment_method' => 'مرتجع',
                'shipping_status' => 'ألغي',
                'customer_name' => 'كريم علي',
                'customer_email' => 'karim.ali@example.com',
                'customer_phone' => '01000001022',
                'city' => 'طنطا',
                'address' => 'حي الجامعة',
                'postal_code' => '31716',
                'items_count' => 2,
                'items' => [
                    ['id' => 10, 'title' => 'التفكير السريع والبطيء', 'author' => 'دانيال كانيمان', 'quantity' => 1, 'price' => 160, 'category' => 'تنمية ذاتية'],
                    ['id' => 11, 'title' => 'العادات الذرية', 'author' => 'جيمس كلير', 'quantity' => 1, 'price' => 150, 'category' => 'تنمية ذاتية'],
                ],
                'totals' => ['subtotal' => 310, 'shipping' => 50, 'tax' => 31, 'total' => 391],
                'shipping' => [
                    'first_name' => 'كريم',
                    'last_name' => 'علي',
                    'email' => 'karim.ali@example.com',
                    'phone' => '01000001022',
                    'address' => 'حي الجامعة',
                    'city' => 'طنطا',
                    'postal_code' => '31716',
                ],
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'order_number' => 'ORD-1021',
                'status' => 'قيد التنفيذ',
                'payment_method' => 'بطاقة',
                'shipping_status' => 'تم التجهيز',
                'customer_name' => 'نهى محمود',
                'customer_email' => 'noha.mahmoud@example.com',
                'customer_phone' => '01000001021',
                'city' => 'المنصورة',
                'address' => 'شارع الجمهورية',
                'postal_code' => '35511',
                'items_count' => 4,
                'items' => [
                    ['id' => 12, 'title' => 'فن اللامبالاة', 'author' => 'مارك مانسون', 'quantity' => 2, 'price' => 130, 'category' => 'تنمية ذاتية'],
                    ['id' => 6, 'title' => 'أصل الأنواع', 'author' => 'تشارلز داروين', 'quantity' => 1, 'price' => 160, 'category' => 'علمية'],
                    ['id' => 4, 'title' => 'أمير', 'author' => 'ميكافيللي', 'quantity' => 1, 'price' => 140, 'category' => 'تاريخ'],
                ],
                'totals' => ['subtotal' => 560, 'shipping' => 50, 'tax' => 56, 'total' => 666],
                'shipping' => [
                    'first_name' => 'نهى',
                    'last_name' => 'محمود',
                    'email' => 'noha.mahmoud@example.com',
                    'phone' => '01000001021',
                    'address' => 'شارع الجمهورية',
                    'city' => 'المنصورة',
                    'postal_code' => '35511',
                ],
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'order_number' => 'ORD-1020',
                'status' => 'مكتمل',
                'payment_method' => 'مدفوع',
                'shipping_status' => 'تم التسليم',
                'customer_name' => 'سارة عادل',
                'customer_email' => 'sara.adel@example.com',
                'customer_phone' => '01000001020',
                'city' => 'بني سويف',
                'address' => 'شارع عبد السلام عارف',
                'postal_code' => '61511',
                'items_count' => 3,
                'items' => [
                    ['id' => 8, 'title' => 'نظرية كل شيء', 'author' => 'ستيفن هوكينج', 'quantity' => 1, 'price' => 150, 'category' => 'علمية'],
                    ['id' => 13, 'title' => 'قواعد العشق الأربعون', 'author' => 'إليف شافاق', 'quantity' => 2, 'price' => 170, 'category' => 'روايات'],
                ],
                'totals' => ['subtotal' => 490, 'shipping' => 0, 'tax' => 49, 'total' => 539],
                'shipping' => [
                    'first_name' => 'سارة',
                    'last_name' => 'عادل',
                    'email' => 'sara.adel@example.com',
                    'phone' => '01000001020',
                    'address' => 'شارع عبد السلام عارف',
                    'city' => 'بني سويف',
                    'postal_code' => '61511',
                ],
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
        ];

        Order::query()->insert($orders);
    }
}
