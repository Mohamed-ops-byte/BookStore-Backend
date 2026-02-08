<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "🔄 تحديث أدوار المستخدمين...\n\n";

try {
    // عرض جميع المستخدمين الحاليين
    $users = User::all();
    
    echo "📊 عدد المستخدمين: " . $users->count() . "\n\n";
    
    if ($users->count() > 0) {
        echo "المستخدمون الحاليون:\n";
        echo "ID | الاسم | البريد الإلكتروني | الدور\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($users as $user) {
            echo "{$user->id} | {$user->name} | {$user->email} | " . ($user->role ?? 'user') . "\n";
        }
        
        echo "\n";
        echo "💡 لتعيين مستخدم كمسؤول، أدخل الرقم التعريفي (ID) للمستخدم أو اضغط Enter للتخطي: ";
        
        $handle = fopen("php://stdin", "r");
        $userId = trim(fgets($handle));
        
        if (!empty($userId) && is_numeric($userId)) {
            $user = User::find($userId);
            
            if ($user) {
                $user->role = 'admin';
                $user->save();
                
                echo "✅ تم تعيين {$user->name} كمسؤول بنجاح!\n";
            } else {
                echo "❌ المستخدم غير موجود!\n";
            }
        } else {
            echo "⏭️ تم التخطي. جميع المستخدمين لديهم دور 'user' الافتراضي.\n";
        }
    } else {
        echo "⚠️ لا يوجد مستخدمون في قاعدة البيانات.\n";
    }
    
    echo "\n✅ تم التحديث بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage() . "\n";
}
