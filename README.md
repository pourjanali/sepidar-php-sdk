# 🐘 Sepidar PHP SDK

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.0%2B-blue.svg" alt="PHP Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/Dependencies-Zero-brightgreen.svg" alt="Dependencies"></a>
  <a href="#"><img src="https://img.shields.io/badge/License-MIT-darkgreen.svg" alt="License"></a>
  <a href="#"><img src="https://img.shields.io/badge/Persistence-Enabled-success.svg" alt="Persistence"></a>
</p>

<p align="center">
 A dependency-free, lightweight PHP SDK for seamless integration with the Sepidar API with built-in authentication persistence.
</p>
<p align="center">
یک SDK سبک و بدون وابستگی برای اتصال آسان به وب‌سرویس سپیدار در PHP با قابلیت ذخیره‌سازی وضعیت احراز هویت
</p>

---

## 🔗 Related Resources

- 🌐 **Swagger API Docs (v111):** [https://pourjanali.github.io/sepidar-api-docs](https://pourjanali.github.io/sepidar-api-docs)  
- 📘 **API Docs Repository:** [https://github.com/pourjanali/sepidar-api-docs](https://github.com/pourjanali/sepidar-api-docs)

---

This SDK provides a straightforward and hassle-free way to connect your PHP applications to the Sepidar accounting system's web services.  
It's designed to be portable, with **zero external dependencies**, making it perfect for any PHP environment — including shared hosting or projects without Composer.

**✨ NEW: Built-in Authentication Persistence** - The SDK now automatically saves and reuses authentication tokens between requests, eliminating redundant device registration and login operations.

این SDK یک راهکار ساده و بی‌دردسر برای اتصال برنامه‌های PHP شما به وب‌سرویس سیستم حسابداری سپیدار فراهم می‌کند.  
این پکیج با هدف پرتابل بودن و **بدون هیچ‌گونه وابستگی خارجی** طراحی شده و برای هر نوع محیط PHP، از جمله هاست‌های اشتراکی یا پروژه‌هایی که از Composer استفاده نمی‌کنند، ایده‌آل است.

**✨ جدید: ذخیره‌سازی خودکار وضعیت احراز هویت** - اکنون SDK به طور خودکار توکن‌های احراز هویت را بین درخواست‌ها ذخیره و استفاده مجدد می‌کند و از انجام عملیات تکراری ثبت دستگاه و لاگین جلوگیری می‌کند.

---

## ✨ Key Features (ویژگی‌های کلیدی)

- **✅ Zero Dependencies:** No need for Guzzle, phpseclib, or even Composer. Just standard PHP extensions (cURL, OpenSSL, SimpleXML).  
- **🚀 Lightweight & Fast:** A single class that handles everything, ensuring minimal overhead.  
- **💾 Smart Authentication Persistence:** Automatically caches device registration, public keys, and login tokens for 23 hours.  
- **🔄 Optimized Performance:** Skips redundant operations on subsequent requests using cached authentication state.  
- **🔒 Full Authentication Flow:** Manages the entire Sepidar authentication process — including device registration, public key extraction, and RSA encryption for secure requests.  
- **🔧 Easy to Use:** A simple and intuitive API lets you get started in minutes.  
- **🌐 Framework-Agnostic:** Works with any PHP project, whether it's plain PHP, WordPress, or any other framework.  
- **📱 State Management:** Built-in methods to check, clear, and manage authentication state.

---

## 🛠️ Installation (نصب)

### 1. Manual (روش دستی)
The easiest way is to download the `SepidarApiClient.php` file and include it in your project.

ساده‌ترین روش، دانلود فایل `SepidarApiClient.php` و فراخوانی آن در پروژه شماست.

```php
require_once 'path/to/SepidarApiClient.php';
```

### 2. Composer (روش پیشنهادی)
You can also install the package via Composer.

شما می‌توانید این پکیج را از طریق Composer نیز نصب کنید.
```bash
composer require pourjanali/sepidar-php-sdk
```

> 💡 **راهنمای استفاده در لاراول (Laravel)**
>
> اگر از فریم‌ورک لاراول استفاده می‌کنید، یک ریپازیتوری جداگانه حاوی نمونه کدها و سرویس‌های آماده (Service Classes) برای راه‌اندازی سریع‌تر آماده کرده‌ام. این فایل‌ها نحوه استفاده از این SDK در محیط لاراول (با `Http`, `Cache` و `Storage`) را نشان می‌دهند.
>
> 🔗 **[مشاهده نمونه کد و راهنمای لاراول](https://github.com/pourjanali/sepidar-laravel)**

---

## 🚀 Quick Start (شروع سریع)

### 🎯 **Recommended: Using Persistence (پیشنهادی: استفاده از ذخیره‌سازی)**

Use `example-with-persistence.php` for production environments - it automatically manages authentication state between requests.

از `example-with-persistence.php` برای محیط‌های تولید استفاده کنید - این فایل به طور خودکار وضعیت احراز هویت را بین درخواست‌ها مدیریت می‌کند.

```php
<?php
require_once 'SepidarApiClient.php';

use App\Sepidar\SepidarApiClient;

$config = [
    'api_url' => 'http://127.0.0.1:7373/api',
    'serial' => 'YOUR_SEPIDAR_SERIAL', 
    'version' => '110',
    'username' => 'web',
    'password' => 'web',
    'storage_path' => __DIR__ . '/sepidar_cache/' // Optional
];

try {
    // Initialize with persistence
    $client = new SepidarApiClient(
        $config['api_url'],
        $config['serial'],
        $config['version'],
        $config['storage_path']
    );
    
    // Check current authentication status
    $status = $client->getAuthStatus();
    echo "Auth Status: " . print_r($status, true);
    
    // Login (only if not already logged in)
    $loginResult = $client->login($config['username'], $config['password']);
    
    if ($loginResult['success']) {
        echo "✅ " . $loginResult['message'] . "\n";
        
        // Fetch items using cached authentication
        $itemsResult = $client->getItems();
        
        if ($itemsResult['success']) {
            echo "🎉 Items fetched successfully! Count: " . count($itemsResult['data']) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

### 📋 Basic Usage (استفاده پایه)

```php
<?php
require_once 'SepidarApiClient.php';

use App\Sepidar\SepidarApiClient;

$client = new SepidarApiClient(
    'http://127.0.0.1:7373/api',
    'YOUR_SEPIDAR_SERIAL',
    '110'
);

// Login handles the entire auth process automatically
$loginResult = $client->login('web', 'web');

if ($loginResult['success']) {
    // Fetch items
    $items = $client->getItems();
    print_r($items);
}
```

---

## 🔐 Authentication Persistence Features (ویژگی‌های ذخیره‌سازی احراز هویت)

### How It Works (نحوه کارکرد)

1. **First Request**: Full authentication flow (device registration → key extraction → login)
2. **Subsequent Requests**: Uses cached authentication state
3. **Automatic Expiry**: Tokens are valid for 23 hours
4. **Serial-based Storage**: Each serial has its own cached state

### State Management Methods (متدهای مدیریت وضعیت)

```php
// Check authentication status
$status = $client->getAuthStatus();
// Returns: ['device_registered' => true, 'logged_in' => true, 'has_public_key' => true]

// Check if device is registered
$isRegistered = $client->isDeviceRegistered();

// Check if user is logged in  
$isLoggedIn = $client->isLoggedIn();

// Force fresh login (clear cache and login again)
$client->forceLogin('username', 'password');

// Clear all cached authentication
$client->clearAuthState();

// Get cached token
$token = $client->getCachedToken();
```

---

## 📚 Available Methods (متدهای اصلی)

### Core Methods (متدهای اصلی)
- `login(string $username, string $password): array` - Authenticates with Sepidar (uses cache if available)
- `getItems(): array` - Fetches a list of items (requires successful login)
- `getAuthenticatedHeaders(): array` - Returns headers for authenticated requests

### State Management Methods (متدهای مدیریت وضعیت)
- `getAuthStatus(): array` - Returns current authentication status
- `isDeviceRegistered(): bool` - Checks if device is registered
- `isLoggedIn(): bool` - Checks if user is logged in
- `getCachedToken(): ?string` - Returns cached authentication token
- `clearAuthState(): void` - Clears all cached authentication data
- `forceLogin(string $username, string $password): array` - Forces fresh login (ignores cache)

---

## 🎨 Example Files (فایل‌های نمونه)

### 1. `example-with-persistence.php` ✅ **Recommended for Production**
**Complete example with authentication persistence, status monitoring, and cache management UI.**

مثال کامل با ذخیره‌سازی وضعیت احراز هویت، مانیتورینگ وضعیت و رابط کاربری مدیریت کش.

**Features:**
- ✅ Automatic authentication state caching
- ✅ Visual status monitoring
- ✅ Cache management buttons (clear cache, force login)
- ✅ Token expiry handling (23 hours)
- ✅ Beautiful Persian UI with Vazirmatn font

### 2. `example-simple.php`
**Basic example for quick testing and understanding the core flow.**

مثال ساده برای تست سریع و درک جریان اصلی.

### 3. `example-get-items.php`
**JSON-focused example for API integrations and command-line usage.**

مثال متمرکز بر JSON برای یکپارچه‌سازی با API و استفاده در خط فرمان.

---

## ⚙️ Configuration Options (تنظیمات)

### Constructor Parameters (پارامترهای سازنده)

```php
$client = new SepidarApiClient(
    string $baseUrl,      // Sepidar API base URL
    string $serial,       // Your Sepidar serial
    string $generationVersion, // API version (e.g., '110')
    string $storagePath = null // Optional: custom cache directory
);
```

### Storage Path (مسیر ذخیره‌سازی)

- **Default**: System temp directory (`sys_get_temp_dir() . '/sepidar_auth/'`)
- **Custom**: Any writable directory path
- **Serial-based**: Each serial gets its own cache file

---

## 🔧 Troubleshooting (عیب‌یابی)

### Common Issues (مشکلات رایج)

1. **"cURL extension is not enabled"**
   - Enable `extension=curl` in `php.ini`

2. **"OpenSSL extension is not enabled"**  
   - Enable `extension=openssl` in `php.ini`

3. **Authentication failures**
   - Verify Sepidar service is running on `http://127.0.0.1:7373`
   - Check if port 7373 is accessible
   - Use `clearAuthState()` to reset cached authentication

4. **File permission issues**
   - Ensure cache directory is writable
   - Check `storage_path` permissions

### Testing with XAMPP (تست با XAMPP)

1. Place files in `C:\xampp\htdocs\sepidar-test\`
2. Access via `http://localhost/sepidar-test/example-with-persistence.php`
3. Ensure extensions are enabled in `php.ini`

---

## 🚀 Performance Benefits (مزایای عملکردی)

| Operation | Without Persistence | With Persistence |
|-----------|-------------------|------------------|
| Device Registration | Every request | First request only |
| Public Key Extraction | Every request | First request only |
| Login | Every request | When token expires |
| API Calls | Full auth flow | Direct call with cached headers |

---

## 🤝 Contributing (مشارکت)

Contributions are welcome! Please feel free to submit a pull request or open an issue for any bugs or feature requests.

از مشارکت شما استقبال می‌کنیم! لطفاً برای ارسال درخواست Pull یا ثبت ایشو (برای باگ‌ها یا درخواست ویژگی‌های جدید) آزاد باشید.

## 📄 License (مجوز)

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

این پروژه تحت مجوز MIT منتشر شده است. برای جزئیات بیشتر به فایل [LICENSE](LICENSE) مراجعه کنید.

---

## 🆕 What's New in v2.0

- **Smart Authentication Caching**: Automatic persistence of device registration and login tokens
- **State Management API**: New methods to check and manage authentication state  
- **Performance Optimization**: Skip redundant operations on subsequent requests
- **Enhanced Examples**: New `example-with-persistence.php` with complete UI
- **Token Expiry Handling**: Automatic token validation and refresh logic

**بررسی سریع**: برای شروع، فایل `example-with-persistence.php` را اجرا کنید - این فایل تمام قابلیت‌های جدید از جمله ذخیره‌سازی خودکار وضعیت، مانیتورینگ و مدیریت کش را نمایش می‌دهد.
