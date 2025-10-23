<?php
// استفاده از هدر جیسان برای نمایش بهتر خروجی در مرورگر
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ۱. فایل کلاس اصلی را که درست کار کرد، فراخوانی کن
// ! مطمئن شوید که نام فایل دقیقاً SepidarApiClient.php است
require_once __DIR__ . '/../SepidarApiClient.php';

// ! مقادیر زیر را با اطلاعات واقعی خود جایگزین کنید
$sepidarApiUrl = 'http://127.0.0.1:7373/api'; // آدرس وب‌سرویس سپیدار
$sepidarSerial = '1024dbfe';          // سریال سپیدار
$sepidarGenVer = '110';              // ورژن مستندات
$sepidarUsername = 'web';              // نام کاربری
$sepidarPassword = 'web';              // رمز عبور

try {
    // استفاده از namespace صحیح
    $client = new \App\Sepidar\SepidarApiClient($sepidarApiUrl, $sepidarSerial, $sepidarGenVer);

    // --- گام ۱: لاگین ---
    echo "--- 1. Testing Login ---\n";
    $loginResult = $client->login($sepidarUsername, $sepidarPassword);
    
    // نمایش نتیجه لاگین
    print_r($loginResult);
    echo "\n-------------------------\n";

    // --- گام ۲: دریافت آیتم‌ها (فقط اگر لاگین موفق بود) ---
    if ($loginResult['success']) {
        
        echo "--- 2. Testing Get Items (using token) ---\n";
        $itemsResult = $client->getItems();
        
        // نمایش نتیجه آیتم‌ها
        print_r($itemsResult);
        echo "\n-------------------------\n";
        
        if($itemsResult['success']) {
            echo "\n✅✅✅ تبریک! لیست آیتم‌ها با موفقیت دریافت شد.\n";
            // برای دیدن خود داده‌ها:
            // print_r($itemsResult['data']);
        } else {
            echo "\n❌❌❌ خطا در دریافت آیتم‌ها. پاسخ سرور:\n";
            print_r($itemsResult); // چاپ خطای دریافتی
        }

    } else {
        echo "Login failed. Cannot fetch items.";
    }


} catch (Exception $e) {
    // خطاهای اساسی مثل نبودن cURL
    echo json_encode(['success' => false, 'message' => 'Exception', 'error' => $e->getMessage()]);
}
?>

