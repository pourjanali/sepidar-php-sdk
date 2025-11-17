<?php
// --- گام ۱: تنظیمات PHP ---
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

// --- گام ۲: فراخوانی منطق اصلی ---
require_once __DIR__ . '/../SepidarApiClient.php';

// ! مقادیر زیر را با اطلاعات واقعی خود جایگزین کنید
$sepidarApiUrl = 'http://127.0.0.1:7373/api'; // آدرس وب‌سرویس سپیدار
$sepidarSerial = '1024668d';          // سریال سپیدار
$sepidarGenVer = '110';              // ورژن مستندات
$sepidarUsername = 'web';              // نام کاربری
$sepidarPassword = 'web';              // رمز عبور

// مسیر ذخیره‌سازی دلخواه (اختیاری)
$storagePath = __DIR__ . '/sepidar_cache/';

// --- گام ۳: اجرای منطق و ذخیره خروجی‌ها ---
$outputs = [];
$finalMessage = '';

try {
    // ساخت کلاینت با مسیر ذخیره‌سازی
    $client = new \App\Sepidar\SepidarApiClient($sepidarApiUrl, $sepidarSerial, $sepidarGenVer, $storagePath);

    // نمایش وضعیت فعلی
    $authStatus = $client->getAuthStatus();
    
    ob_start();
    echo "🔍 وضعیت احراز هویت فعلی:\n";
    print_r($authStatus);
    $outputs['status'] = ob_get_clean();

    // --- اگر قبلاً لاگین نکرده، لاگین کن ---
    if (!$authStatus['logged_in']) {
        $outputs['login'] = "🔄 در حال لاگین... (اولین بار)\n";
        
        $loginResult = $client->login($sepidarUsername, $sepidarPassword);
        
        ob_start();
        print_r($loginResult);
        $outputs['login'] .= ob_get_clean();

        if ($loginResult['success']) {
            $outputs['login'] .= "\n✅ لاگین موفق و توکن ذخیره شد\n";
        } else {
            $outputs['login'] .= "\n❌ خطا در لاگین\n";
        }
    } else {
        $outputs['login'] = "✅ از توکن ذخیره شده استفاده می‌شود (لاگین مجدد لازم نیست)\n";
        
        // تست دریافت هدرها با توکن موجود
        $headersResult = $client->getAuthenticatedHeaders();
        ob_start();
        print_r($headersResult);
        $outputs['headers'] = ob_get_clean();
    }

    // --- تست دریافت آیتم‌ها ---
    $itemsResult = $client->getItems();
    
    ob_start();
    print_r($itemsResult);
    $outputs['items'] = ob_get_clean();
    
    if($itemsResult['success']) {
        $finalMessage = '✅✅✅ تبریک! لیست آیتم‌ها با موفقیت دریافت شد.';
        
        // نمایش تعداد آیتم‌های دریافتی
        $itemsCount = is_array($itemsResult['data']) ? count($itemsResult['data']) : 0;
        $finalMessage .= " (تعداد آیتم‌ها: $itemsCount)";
    } else {
        $finalMessage = '❌❌❌ خطا در دریافت آیتم‌ها.';
        
        // اگر خطا به دلیل انقضای توکن باشد، پیشنهاد لاگین مجدد
        if (strpos($itemsResult['message'] ?? '', '401') !== false) {
            $finalMessage .= " ممکن است توکن منقضی شده باشد. از forceLogin() استفاده کنید.";
        }
    }

    // نمایش وضعیت نهایی
    $finalStatus = $client->getAuthStatus();
    ob_start();
    echo "🔍 وضعیت نهایی احراز هویت:\n";
    print_r($finalStatus);
    $outputs['final_status'] = ob_get_clean();

} catch (Exception $e) {
    $outputs['error'] = 'Exception: ' . $e->getMessage();
}
?>

<!-- --- گام ۴: نمایش خروجی در HTML --- -->
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست اتصال به سپیدار با ذخیره‌سازی وضعیت</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap');
        
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            margin: 0;
            color: white;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        .test-section {
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
        }
        .test-section:last-child {
            border-bottom: none;
        }
        .test-section h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #34495e;
            border-right: 4px solid #3498db;
            padding-right: 10px;
        }
        
        pre.debug-output {
            background-color: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.9rem;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-x: auto;
            direction: ltr;
            text-align: left;
        }
        
        .status-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            font-size: 1.1rem;
        }
        .status-success {
            background-color: #e6f7f0;
            color: #0d683b;
            border: 1px solid #b7e4ca;
        }
        .status-error {
            background-color: #fdf0f0;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }
        
        .cache-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>تست اتصال به وب‌سرویس سپیدار (با ذخیره‌سازی وضعیت)</h1>
            <p>وضعیت احراز هویت بین درخواست‌ها ذخیره می‌شود</p>
        </div>

        <!-- وضعیت اولیه -->
        <?php if (!empty($outputs['status'])): ?>
        <div class="test-section">
            <h2>وضعیت اولیه احراز هویت</h2>
            <div class="cache-info">
                💾 مسیر ذخیره‌سازی: <?php echo htmlspecialchars($storagePath); ?>
            </div>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['status']); ?></pre>
        </div>
        <?php endif; ?>

        <!-- بخش لاگین -->
        <?php if (!empty($outputs['login'])): ?>
        <div class="test-section">
            <h2>فرآیند لاگین</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['login']); ?></pre>
        </div>
        <?php endif; ?>

        <!-- بخش هدرها -->
        <?php if (!empty($outputs['headers'])): ?>
        <div class="test-section">
            <h2>هدرهای احراز هویت شده</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['headers']); ?></pre>
        </div>
        <?php endif; ?>

        <!-- بخش آیتم‌ها -->
        <?php if (!empty($outputs['items'])): ?>
        <div class="test-section">
            <h2>دریافت آیتم‌ها</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['items']); ?></pre>
        </div>
        <?php endif; ?>

        <!-- وضعیت نهایی -->
        <?php if (!empty($outputs['final_status'])): ?>
        <div class="test-section">
            <h2>وضعیت نهایی</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['final_status']); ?></pre>
        </div>
        <?php endif; ?>

        <!-- پیام نهایی -->
        <?php if (!empty($finalMessage)): ?>
            <?php $statusClass = (strpos($finalMessage, '✅') !== false) ? 'status-success' : 'status-error'; ?>
            <div class="test-section">
                 <div class="status-message <?php echo $statusClass; ?>">
                    <?php echo $finalMessage; ?>
                 </div>
            </div>
        <?php endif; ?>

        <!-- دکمه‌های مدیریت -->
        <div class="test-section" style="text-align: center;">
            <h2>مدیریت وضعیت ذخیره‌سازی</h2>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button onclick="location.reload()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🔄 بارگذاری مجدد (استفاده از کش)
                </button>
                <button onclick="window.location.href='?clear_cache=1'" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🗑️ پاک کردن کش
                </button>
                <button onclick="window.location.href='?force_login=1'" style="padding: 10px 20px; background: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🔐 لاگین اجباری
                </button>
            </div>
        </div>

    </div>

    <?php
    // مدیریت درخواست‌های مدیریت کش
    if (isset($_GET['clear_cache'])) {
        $client->clearAuthState();
        echo "<script>alert('کش پاک شد!'); window.location.href = window.location.pathname;</script>";
    }
    
    if (isset($_GET['force_login'])) {
        $client->clearAuthState();
        echo "<script>alert('وضعیت پاک شد! صفحه در حال بارگذاری مجدد است...'); window.location.href = window.location.pathname;</script>";
    }
    ?>

</body>
</html>