<?php

// نمایش تمام خطاها برای دیباگ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// فقط این فایل را require کنید
require_once 'SepidarApiClient.php';

use App\Sepidar\SepidarApiClient;

// ! مقادیر زیر را با اطلاعات واقعی خود جایگزین کنید
$sepidarApiUrl = 'http://127.0.0.1:7373/api'; // آدرس وب‌سرویس سپیدار
$sepidarSerial = '1024dbfe';                     // سریال سپیدار
$sepidarGenVer = '110';                       // ورژن مستندات
$sepidarUsername = 'web';                       // نام کاربری
$sepidarPassword = 'web';                         // رمز عبور

// نمایش زیبا آرایه‌ها
function prettyPrint($data)
{
    echo "<pre style='background-color: #f5f5f5; border: 1px solid #ccc; padding: 10px; border-radius: 5px; direction: ltr; text-align: left;'>";
    print_r($data);
    echo "</pre>";
}

echo "<div style='font-family: sans-serif; direction: rtl; max-width: 800px; margin: auto;'>";
echo "<h1>تست اتصال به وب‌سرویس سپیدار (نسخه بدون Composer)</h1>";

try {
    // ۱. ساخت کلاینت
    $client = new SepidarApiClient($sepidarApiUrl, $sepidarSerial, $sepidarGenVer);
    echo "<p style='color: green; font-weight: bold;'>✅ کلاینت با موفقیت ساخته شد (وابستگی‌های cURL و OpenSSL بررسی شدند).</p>";

    // ۲. تلاش برای لاگین
    // این متد به صورت خودکار مراحل ثبت دستگاه و استخراج کلید را در هر بار اجرا انجام می‌دهد
    echo "<p>در حال تلاش برای لاگین (شامل Register و ExtractPublicKey)...</p>";
    
    $loginResult = $client->login($sepidarUsername, $sepidarPassword);

    if ($loginResult['success']) {
        echo "<h2 style='color: #006400;'>🎉 لاگین با موفقیت انجام شد</h2>";
        prettyPrint($loginResult['data']);

        // ۳. تست دریافت هدرهای احراز هویت شده
        echo "<hr><h2>تست دریافت هدرهای احراز هویت شده:</h2>";
        $headersResult = $client->getAuthenticatedHeaders();
        prettyPrint($headersResult);

    } else {
        echo "<h2 style='color: #a00;'>❌ خطا در عملیات لاگین</h2>";
        prettyPrint($loginResult);
    }

} catch (Exception $e) {
    echo "<h2 style='color: #a00;'>🔥 خطای اساسی رخ داد</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
}
echo "</div>";

