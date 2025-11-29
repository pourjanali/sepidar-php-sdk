<?php
// --- گام ۱: تنظیمات PHP ---
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

// --- گام ۲: فراخوانی منطق اصلی ---
require_once __DIR__ . '/../SepidarApiClient.php';

// تشخیص روش فعال
$activeMethod = $_POST['active_method'] ?? 'login';

// دریافت مقادیر از فرم بر اساس روش فعال
if ($activeMethod === 'login') {
    $sepidarApiUrl = $_POST['api_url'] ?? 'http://127.0.0.1:7373/api';
    $sepidarSerial = $_POST['serial'] ?? '';
    $sepidarGenVer = $_POST['generation_version'] ?? '110';
    $sepidarUsername = $_POST['username'] ?? '';
    $sepidarPassword = $_POST['password'] ?? '';
    
    // کلیدهای مستقیم خالی باشند
    $directIntegrationId = $directArbitraryCode = $directEncArbitraryCode = $directGenerationVersion = $directToken = '';
} else {
    $sepidarApiUrl = $_POST['api_url_direct'] ?? 'http://127.0.0.1:7373/api';
    $directIntegrationId = $_POST['direct_integration_id'] ?? '';
    $directArbitraryCode = $_POST['direct_arbitrary_code'] ?? '';
    $directEncArbitraryCode = $_POST['direct_enc_arbitrary_code'] ?? '';
    $directGenerationVersion = $_POST['direct_generation_version'] ?? '110';
    $directToken = $_POST['direct_token'] ?? '';
    
    // اطلاعات لاگین خالی باشند
    $sepidarSerial = $sepidarUsername = $sepidarPassword = '';
    $sepidarGenVer = '110';
}

// تشخیص استفاده از هر روش
$usingLoginMethod = $activeMethod === 'login' && !empty($sepidarSerial) && !empty($sepidarUsername);
$usingDirectKeys = $activeMethod === 'direct' && !empty($directIntegrationId) && !empty($directToken);

// مسیر ذخیره‌سازی دلخواه (اختیاری)
$storagePath = __DIR__ . '/sepidar_cache/';

// --- گام ۳: اجرای منطق و ذخیره خروجی‌ها ---
$outputs = [];
$finalMessage = '';

try {
    // فقط اگر اطلاعات لازم برای یکی از روش‌ها وجود دارد، کلاینت را ایجاد کن
    if ($usingLoginMethod || $usingDirectKeys) {
        // ساخت کلاینت با مسیر ذخیره‌سازی
        if ($usingLoginMethod) {
            $client = new \App\Sepidar\SepidarApiClient($sepidarApiUrl, $sepidarSerial, $sepidarGenVer, $storagePath);
            $outputs['auth_method'] = "🔐 استفاده از روش لاگین معمولی\n";
            $outputs['auth_method'] .= "📋 اطلاعات وارد شده:\n";
            $outputs['auth_method'] .= "- آدرس: " . $sepidarApiUrl . "\n";
            $outputs['auth_method'] .= "- سریال: " . $sepidarSerial . "\n";
            $outputs['auth_method'] .= "- ورژن: " . $sepidarGenVer . "\n";
            $outputs['auth_method'] .= "- کاربری: " . $sepidarUsername . "\n";
        } else {
            // برای روش مستقیم، می‌توانیم از یک سریال ساختگی استفاده کنیم
            $client = new \App\Sepidar\SepidarApiClient($sepidarApiUrl, 'direct_keys_mode', $directGenerationVersion, $storagePath);
            $outputs['auth_method'] = "🔑 استفاده از روش کلیدهای مستقیم\n";
            $outputs['auth_method'] .= "📋 اطلاعات وارد شده:\n";
            $outputs['auth_method'] .= "- آدرس: " . $sepidarApiUrl . "\n";
            $outputs['auth_method'] .= "- IntegrationID: " . $directIntegrationId . "\n";
            $outputs['auth_method'] .= "- GenerationVersion: " . $directGenerationVersion . "\n";
        }

        // تنظیم کلیدهای مستقیم اگر کاربر انتخاب کرده
        if ($usingDirectKeys) {
            $client->setDirectKeys(
                $directIntegrationId,
                $directArbitraryCode,
                $directEncArbitraryCode,
                $directGenerationVersion,
                $directToken
            );
        }

        // نمایش وضعیت فعلی
        $authStatus = $client->getAuthStatus();
        
        ob_start();
        echo "🔍 وضعیت احراز هویت فعلی:\n";
        print_r($authStatus);
        $outputs['status'] = ob_get_clean();

        // --- فرآیند لاگین با نمایش هدرها ---
        if ($usingLoginMethod && !$authStatus['logged_in']) {
            $outputs['login'] = "🔄 در حال لاگین... (اولین بار)\n";
            
            $loginResult = $client->login($sepidarUsername, $sepidarPassword);
            
            ob_start();
            print_r($loginResult);
            $outputs['login'] .= ob_get_clean();

            if ($loginResult['success']) {
                $outputs['login'] .= "\n✅ لاگین موفق و توکن ذخیره شد\n";
                
                // نمایش هدرهای استفاده شده
                if (isset($loginResult['headers_used'])) {
                    $outputs['login'] .= "\n🔐 هدرهای استفاده شده در لاگین:\n";
                    $outputs['login'] .= "GenerationVersion: " . ($loginResult['headers_used']['GenerationVersion'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "IntegrationID: " . ($loginResult['headers_used']['IntegrationID'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "ArbitraryCode: " . ($loginResult['headers_used']['ArbitraryCode'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "EncArbitraryCode: " . ($loginResult['headers_used']['EncArbitraryCode'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "Authorization: " . ($loginResult['headers_used']['Authorization'] ?? 'N/A') . "\n";
                }
            } else {
                $outputs['login'] .= "\n❌ خطا در لاگین\n";
                
                // نمایش هدرهای استفاده شده حتی در صورت خطا
                if (isset($loginResult['headers_used'])) {
                    $outputs['login'] .= "\n🔐 هدرهای استفاده شده در لاگین:\n";
                    $outputs['login'] .= "GenerationVersion: " . ($loginResult['headers_used']['GenerationVersion'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "IntegrationID: " . ($loginResult['headers_used']['IntegrationID'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "ArbitraryCode: " . ($loginResult['headers_used']['ArbitraryCode'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "EncArbitraryCode: " . ($loginResult['headers_used']['EncArbitraryCode'] ?? 'N/A') . "\n";
                    $outputs['login'] .= "Authorization: " . ($loginResult['headers_used']['Authorization'] ?? 'N/A') . "\n";
                }
            }
        } elseif ($usingLoginMethod) {
            $outputs['login'] = "✅ از توکن ذخیره شده استفاده می‌شود (لاگین مجدد لازم نیست)\n";
            
            // تست دریافت هدرها با توکن موجود
            $headersResult = $client->getAuthenticatedHeaders();
            ob_start();
            print_r($headersResult);
            $outputs['login'] .= ob_get_clean();
            
            // نمایش هدرهای احراز هویت شده
            if (isset($headersResult['headers'])) {
                $outputs['login'] .= "\n🔐 هدرهای احراز هویت شده:\n";
                $outputs['login'] .= "GenerationVersion: " . ($headersResult['headers']['GenerationVersion'] ?? 'N/A') . "\n";
                $outputs['login'] .= "IntegrationID: " . ($headersResult['headers']['IntegrationID'] ?? 'N/A') . "\n";
                $outputs['login'] .= "ArbitraryCode: " . ($headersResult['headers']['ArbitraryCode'] ?? 'N/A') . "\n";
                $outputs['login'] .= "EncArbitraryCode: " . ($headersResult['headers']['EncArbitraryCode'] ?? 'N/A') . "\n";
                $outputs['login'] .= "Authorization: " . ($headersResult['headers']['Authorization'] ?? 'N/A') . "\n";
            }
        } else {
            $outputs['login'] = "✅ از کلیدهای مستقیم استفاده می‌شود (لاگین لازم نیست)\n";
            
            // نمایش هدرهای روش مستقیم
            $headersResult = $client->getAuthenticatedHeaders();
            if (isset($headersResult['headers'])) {
                $outputs['login'] .= "\n🔐 هدرهای استفاده شده (کلیدهای مستقیم):\n";
                $outputs['login'] .= "GenerationVersion: " . ($headersResult['headers']['GenerationVersion'] ?? 'N/A') . "\n";
                $outputs['login'] .= "IntegrationID: " . ($headersResult['headers']['IntegrationID'] ?? 'N/A') . "\n";
                $outputs['login'] .= "ArbitraryCode: " . ($headersResult['headers']['ArbitraryCode'] ?? 'N/A') . "\n";
                $outputs['login'] .= "EncArbitraryCode: " . ($headersResult['headers']['EncArbitraryCode'] ?? 'N/A') . "\n";
                $outputs['login'] .= "Authorization: " . ($headersResult['headers']['Authorization'] ?? 'N/A') . "\n";
            }
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
            $finalMessage .= "\nخطا: " . ($itemsResult['message'] ?? 'خطای ناشناخته');
            
            // اگر خطا به دلیل انقضای توکن باشد، پیشنهاد لاگین مجدد
            if (strpos($itemsResult['message'] ?? '', '401') !== false && $usingLoginMethod) {
                $finalMessage .= "\nممکن است توکن منقضی شده باشد. از forceLogin() استفاده کنید.";
            }
        }

        // نمایش وضعیت نهایی
        $finalStatus = $client->getAuthStatus();
        ob_start();
        echo "🔍 وضعیت نهایی احراز هویت:\n";
        print_r($finalStatus);
        $outputs['final_status'] = ob_get_clean();
    } else {
        $outputs['auth_method'] = "ℹ️ لطفاً اطلاعات یکی از روش‌های احراز هویت را وارد کنید.\n";
    }

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
    <title>تست اتصال به سپیدار - دو روش احراز هویت</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap');
        
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            direction: rtr;
        }
        .container {
            max-width: 1100px;
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
        .auth-methods {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .auth-method {
            flex: 1;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .auth-method:hover {
            border-color: #667eea;
        }
        .auth-method.active {
            border-color: #667eea;
            background-color: #f0f4ff;
        }
        .auth-method h3 {
            margin-top: 0;
            color: #34495e;
        }
        .form-section {
            display: none;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-top: 10px;
        }
        .form-section.active {
            display: block;
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
        
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Vazirmatn', sans-serif;
        }
        .submit-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 1rem;
        }
        .required {
            color: #e74c3c;
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }
        .method-info {
            background: #e8f4fd;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-right: 4px solid #3498db;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>تست اتصال به وب‌سرویس سپیدار - دو روش احراز هویت</h1>
            <p>انتخاب کنید از کدام روش برای احراز هویت استفاده کنید</p>
        </div>

        <form method="post" id="auth-form" onsubmit="return validateForm()">
            <!-- Hidden field to track active method -->
            <input type="hidden" name="active_method" id="active_method" value="<?php echo $activeMethod; ?>">
            
            <div class="auth-methods">
                <div class="auth-method <?php echo $activeMethod === 'login' ? 'active' : ''; ?>" onclick="selectAuthMethod('login')">
                    <h3>🔐 روش اول: لاگین معمولی</h3>
                    <p>با نام کاربری و رمز عبور وارد شوید</p>
                </div>
                <div class="auth-method <?php echo $activeMethod === 'direct' ? 'active' : ''; ?>" onclick="selectAuthMethod('direct')">
                    <h3>🔑 روش دوم: کلیدهای مستقیم</h3>
                    <p>با ارسال مستقیم کلیدهای احراز هویت</p>
                </div>
            </div>

            <div id="login-form" class="form-section <?php echo $activeMethod === 'login' ? 'active' : ''; ?>">
                <div class="method-info">
                    <strong>روش لاگین معمولی:</strong> این روش برای زمانی است که می‌خواهید با نام کاربری و رمز عبور وارد سیستم شوید.
                </div>
                
                <h3>📝 اطلاعات اتصال</h3>
                <div class="form-group">
                    <label>آدرس وب‌سرویس سپیدار <span class="required">*</span>:</label>
                    <input type="text" name="api_url" value="<?php echo htmlspecialchars($sepidarApiUrl); ?>" placeholder="مثال: http://127.0.0.1:7373/api">
                    <div class="error-message" id="api_url_error"></div>
                </div>
                <div class="form-group">
                    <label>سریال سپیدار <span class="required">*</span>:</label>
                    <input type="text" name="serial" value="<?php echo htmlspecialchars($sepidarSerial); ?>" placeholder="مثال: 1024668d">
                    <div class="error-message" id="serial_error"></div>
                </div>
                <div class="form-group">
                    <label>ورژن مستندات <span class="required">*</span>:</label>
                    <input type="text" name="generation_version" value="<?php echo htmlspecialchars($sepidarGenVer); ?>" placeholder="مثال: 110">
                    <div class="error-message" id="generation_version_error"></div>
                </div>
                
                <h3>🔐 اطلاعات حساب کاربری</h3>
                <div class="form-group">
                    <label>نام کاربری <span class="required">*</span>:</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($sepidarUsername); ?>" placeholder="مثال: web">
                    <div class="error-message" id="username_error"></div>
                </div>
                <div class="form-group">
                    <label>رمز عبور <span class="required">*</span>:</label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($sepidarPassword); ?>" placeholder="رمز عبور خود را وارد کنید">
                    <div class="error-message" id="password_error"></div>
                </div>
            </div>

            <div id="direct-form" class="form-section <?php echo $activeMethod === 'direct' ? 'active' : ''; ?>">
                <div class="method-info">
                    <strong>روش کلیدهای مستقیم:</strong> این روش برای زمانی است که کلیدهای احراز هویت را از قبل دارید.
                </div>
                
                <h3>📝 اطلاعات اتصال</h3>
                <div class="form-group">
                    <label>آدرس وب‌سرویس سپیدار <span class="required">*</span>:</label>
                    <input type="text" name="api_url_direct" value="<?php echo htmlspecialchars($sepidarApiUrl); ?>" placeholder="مثال: http://127.0.0.1:7373/api">
                    <div class="error-message" id="api_url_direct_error"></div>
                </div>
                
                <h3>🔑 کلیدهای احراز هویت</h3>
                <div class="form-group">
                    <label>IntegrationID <span class="required">*</span>:</label>
                    <input type="text" name="direct_integration_id" value="<?php echo htmlspecialchars($directIntegrationId); ?>" placeholder="شناسه یکپارچه‌سازی">
                    <div class="error-message" id="direct_integration_id_error"></div>
                </div>
                <div class="form-group">
                    <label>ArbitraryCode <span class="required">*</span>:</label>
                    <input type="text" name="direct_arbitrary_code" value="<?php echo htmlspecialchars($directArbitraryCode); ?>" placeholder="کد دلخواه">
                    <div class="error-message" id="direct_arbitrary_code_error"></div>
                </div>
                <div class="form-group">
                    <label>EncArbitraryCode <span class="required">*</span>:</label>
                    <input type="text" name="direct_enc_arbitrary_code" value="<?php echo htmlspecialchars($directEncArbitraryCode); ?>" placeholder="کد دلخواه رمزگذاری شده">
                    <div class="error-message" id="direct_enc_arbitrary_code_error"></div>
                </div>
                <div class="form-group">
                    <label>GenerationVersion <span class="required">*</span>:</label>
                    <input type="text" name="direct_generation_version" value="<?php echo htmlspecialchars($directGenerationVersion); ?>" placeholder="مثال: 110">
                    <div class="error-message" id="direct_generation_version_error"></div>
                </div>
                <div class="form-group">
                    <label>Bearer Token <span class="required">*</span>:</label>
                    <input type="text" name="direct_token" value="<?php echo htmlspecialchars($directToken); ?>" placeholder="توکن دسترسی">
                    <div class="error-message" id="direct_token_error"></div>
                </div>
            </div>

            <div style="padding: 20px; text-align: center;">
                <button type="submit" class="submit-btn">ارسال درخواست</button>
                <button type="button" onclick="clearForm()" style="background: #95a5a6; margin-right: 10px;" class="submit-btn">پاک کردن فرم</button>
            </div>
        </form>

        <!-- نمایش خروجی‌ها -->
        <?php if (!empty($outputs['auth_method'])): ?>
        <div class="test-section">
            <h2>روش احراز هویت انتخاب شده</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['auth_method']); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (!empty($outputs['status'])): ?>
        <div class="test-section">
            <h2>وضعیت اولیه احراز هویت</h2>
            <div class="cache-info">
                💾 مسیر ذخیره‌سازی: <?php echo htmlspecialchars($storagePath); ?>
            </div>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['status']); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (!empty($outputs['login'])): ?>
        <div class="test-section">
            <h2>فرآیند لاگین</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['login']); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (!empty($outputs['headers'])): ?>
        <div class="test-section">
            <h2>هدرهای احراز هویت شده</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['headers']); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (!empty($outputs['items'])): ?>
        <div class="test-section">
            <h2>دریافت آیتم‌ها</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['items']); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (!empty($outputs['final_status'])): ?>
        <div class="test-section">
            <h2>وضعیت نهایی</h2>
            <pre class="debug-output"><?php echo htmlspecialchars($outputs['final_status']); ?></pre>
        </div>
        <?php endif; ?>

        <?php if (!empty($finalMessage)): ?>
            <?php $statusClass = (strpos($finalMessage, '✅') !== false) ? 'status-success' : 'status-error'; ?>
            <div class="test-section">
                 <div class="status-message <?php echo $statusClass; ?>">
                    <?php echo nl2br(htmlspecialchars($finalMessage)); ?>
                 </div>
            </div>
        <?php endif; ?>

        <!-- دکمه‌های مدیریت -->
        <?php if ($usingLoginMethod || $usingDirectKeys): ?>
        <div class="test-section" style="text-align: center;">
            <h2>مدیریت وضعیت ذخیره‌سازی</h2>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button onclick="location.reload()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🔄 بارگذاری مجدد (استفاده از کش)
                </button>
                <button onclick="window.location.href='?clear_cache=1'" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🗑️ پاک کردن کش
                </button>
                <?php if ($usingLoginMethod): ?>
                <button onclick="window.location.href='?force_login=1'" style="padding: 10px 20px; background: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    🔐 لاگین اجباری
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
        let currentMethod = '<?php echo $activeMethod; ?>';
        
        function selectAuthMethod(method) {
            currentMethod = method;
            document.getElementById('active_method').value = method;
            
            // حذف کلاس active از همه روش‌ها
            document.querySelectorAll('.auth-method').forEach(el => {
                el.classList.remove('active');
            });
            
            // اضافه کردن کلاس active به روش انتخاب شده
            event.currentTarget.classList.add('active');
            
            // نمایش فرم مربوطه
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('direct-form').classList.remove('active');
            
            if (method === 'login') {
                document.getElementById('login-form').classList.add('active');
            } else {
                document.getElementById('direct-form').classList.add('active');
            }
            
            // پاک کردن خطاهای قبلی
            clearErrors();
        }

        function validateForm() {
            clearErrors();
            let isValid = true;
            
            if (currentMethod === 'login') {
                // اعتبارسنجی روش لاگین
                const requiredFields = [
                    { id: 'api_url', name: 'آدرس وب‌سرویس' },
                    { id: 'serial', name: 'سریال سپیدار' },
                    { id: 'generation_version', name: 'ورژن مستندات' },
                    { id: 'username', name: 'نام کاربری' },
                    { id: 'password', name: 'رمز عبور' }
                ];
                
                requiredFields.forEach(field => {
                    const input = document.querySelector(`[name="${field.id}"]`);
                    if (!input.value.trim()) {
                        showError(field.id + '_error', `لطفاً ${field.name} را وارد کنید`);
                        isValid = false;
                    }
                });
                
                // اعتبارسنجی URL
                const apiUrl = document.querySelector('[name="api_url"]').value;
                if (apiUrl && !isValidUrl(apiUrl)) {
                    showError('api_url_error', 'آدرس وب‌سرویس معتبر نیست');
                    isValid = false;
                }
                
            } else {
                // اعتبارسنجی روش کلیدهای مستقیم
                const requiredFields = [
                    { id: 'api_url_direct', name: 'آدرس وب‌سرویس' },
                    { id: 'direct_integration_id', name: 'IntegrationID' },
                    { id: 'direct_arbitrary_code', name: 'ArbitraryCode' },
                    { id: 'direct_enc_arbitrary_code', name: 'EncArbitraryCode' },
                    { id: 'direct_generation_version', name: 'GenerationVersion' },
                    { id: 'direct_token', name: 'Bearer Token' }
                ];
                
                requiredFields.forEach(field => {
                    const input = document.querySelector(`[name="${field.id}"]`);
                    if (!input.value.trim()) {
                        showError(field.id + '_error', `لطفاً ${field.name} را وارد کنید`);
                        isValid = false;
                    }
                });
                
                // اعتبارسنجی URL
                const apiUrl = document.querySelector('[name="api_url_direct"]').value;
                if (apiUrl && !isValidUrl(apiUrl)) {
                    showError('api_url_direct_error', 'آدرس وب‌سرویس معتبر نیست');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                alert('لطفاً تمام فیلدهای ضروری را پر کنید');
            }
            
            return isValid;
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        function showError(errorElementId, message) {
            const errorElement = document.getElementById(errorElementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
        }

        function clearForm() {
            // پاک کردن همه فیلدهای فرم
            document.querySelectorAll('input').forEach(input => {
                if (input.type !== 'hidden') {
                    input.value = '';
                }
            });
            
            // بازگشت به روش پیش‌فرض (لاگین)
            selectAuthMethod('login');
            clearErrors();
        }

        // انتخاب خودکار روش بر اساس فیلدهای پر شده
        document.addEventListener('DOMContentLoaded', function() {
            // اگر از قبل روشی انتخاب شده، همان را فعال نگه دار
            selectAuthMethod(currentMethod);
        });
    </script>

    <?php
    // مدیریت درخواست‌های مدیریت کش
    if (isset($_GET['clear_cache']) && ($usingLoginMethod || $usingDirectKeys)) {
        $client->clearAuthState();
        echo "<script>alert('کش پاک شد!'); window.location.href = window.location.pathname;</script>";
    }
    
    if (isset($_GET['force_login']) && $usingLoginMethod) {
        $client->clearAuthState();
        echo "<script>alert('وضعیت پاک شد! صفحه در حال بارگذاری مجدد است...'); window.location.href = window.location.pathname;</script>";
    }
    ?>

</body>
</html>