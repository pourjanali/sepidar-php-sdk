<?php
// --- گام ۱: تنظیمات PHP ---
ini_set('display_errors', 1);
// مخفی کردن هشدارهای Deprecated که در عکس شما بود (برای خروجی تمیزتر)
error_reporting(E_ALL & ~E_DEPRECATED);

// --- گام ۲: فراخوانی منطق اصلی ---
// (این کد دقیقاً همان کد فایل قبلی شماست)
require_once __DIR__ . '/../SepidarApiClient.php';

// ! مقادیر زیر را با اطلاعات واقعی خود جایگزین کنید
$sepidarApiUrl = 'http://127.0.0.1:7373/api'; // آدرس وب‌سرویس سپیدار
$sepidarSerial = '1024fb89';          // سریال سپیدار
$sepidarGenVer = '110';              // ورژن مستندات
$sepidarUsername = 'web';              // نام کاربری
$sepidarPassword = 'web';              // رمز عبور

// --- گام ۳: اجرای منطق و ذخیره خروجی‌ها ---
// (به جای echo، خروجی‌ها را در متغیر ذخیره می‌کنیم)
$loginOutput = '';
$headersOutput = ''; // متغیر جدید برای هدرها
$itemsOutput = '';
$finalMessage = '';

try {
    $client = new \App\Sepidar\SepidarApiClient($sepidarApiUrl, $sepidarSerial, $sepidarGenVer);

    // --- تست لاگین ---
    $loginResult = $client->login($sepidarUsername, $sepidarPassword);
    
    // خروجی print_r را در یک متغیر می‌ریزیم
    ob_start();
    print_r($loginResult);
    $loginOutput = ob_get_clean();

    // --- تست دریافت هدرها و آیتم‌ها ---
    if ($loginResult['success']) {
        
        // --- بخش جدید: تست دریافت هدرها ---
        $headersResult = $client->getAuthenticatedHeaders();
        ob_start();
        print_r($headersResult);
        $headersOutput = ob_get_clean();
        // --- پایان بخش جدید ---

        // --- تست دریافت آیتم‌ها ---
        $itemsResult = $client->getItems();
        
        ob_start();
        print_r($itemsResult);
        $itemsOutput = ob_get_clean();
        
        if($itemsResult['success']) {
            $finalMessage = '✅✅✅ تبریک! لیست آیتم‌ها با موفقیت دریافت شد.';
        } else {
            $finalMessage = '❌❌❌ خطا در دریافت آیتم‌ها.';
        }

    } else {
        $finalMessage = 'Login failed. Cannot fetch items.';
    }

} catch (Exception $e) {
    $loginOutput = 'Exception: ' . $e->getMessage();
}
?>

<!-- --- گام ۴: نمایش خروجی در HTML شیک --- -->
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست اتصال به وب‌سرویس سپیدار</title>
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
            overflow: hidden; /* برای اینکه radius کار کند */
        }
        .header {
            padding: 25px 30px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
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
        
        /* این استایل اصلی برای حل مشکل شماست */
        pre.debug-output {
            background-color: #2d3748; /* تم تیره */
            color: #e2e8f0;            /* متن روشن */
            padding: 20px;
            border-radius: 8px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.9rem;
            
            /* جادوی حل مشکل کادر */
            white-space: pre-wrap;   /* خطوط را می‌شکند */
            word-wrap: break-word;   /* کلمات طولانی را می‌شکند */
            
            overflow-x: auto; /* اگر باز هم جا نشد، اسکرول افقی بده */
            direction: ltr;     /* خروجی آرایه بهتر است چپ‌چین باشد */
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
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>تست اتصال به وب‌سرویس سپیدار</h1>
            <p>خروجی تست‌ API سپیدار</p>
        </div>

        <!-- بخش تست لاگین -->
        <div class="test-section">
            <h2>۱.لاگین (Login)</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($loginOutput); ?></pre>
        </div>

        <!-- بخش جدید: تست دریافت هدرها -->
        <?php if (!empty($headersOutput)): ?>
        <div class="test-section">
            <h2>۲.دریافت هدرهای احراز هویت شده</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($headersOutput); ?></pre>
        </div>
        <?php endif; ?>

        <!-- بخش تست دریافت آیتم‌ها -->
        <?php if (!empty($itemsOutput)): ?>
        <div class="test-section">
            <h2>۳.دریافت آیتم‌ها (Get Items)</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($itemsOutput); ?></pre>
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

    </div>

</body>
</html>


