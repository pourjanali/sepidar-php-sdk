# 🚀 Sepidar PHP SDK

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.0%2B-blue.svg" alt="PHP Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/Dependencies-Zero-brightgreen.svg" alt="Dependencies"></a>
  <a href="#"><img src="https://img.shields.io/badge/License-MIT-darkgreen.svg" alt="License"></a>
  <a href="#"><img src="https://img.shields.io/badge/Persistence-Enabled-success.svg" alt="Persistence"></a>
  <a href="#"><img src="https://img.shields.io/badge/Auth%20Methods-2-orange.svg" alt="Authentication Methods"></a>
</p>

<p align="center">
A dependency-free, lightweight PHP SDK for seamless integration with the Sepidar API with built-in authentication persistence and dual authentication methods.
</p>
<p align="center">
یک SDK سبک و بدون وابستگی برای اتصال آسان به وب‌سرویس سپیدار در PHP با قابلیت ذخیره‌سازی وضعیت احراز هویت و دو روش احراز هویت
</p>

---

## 📚 Related Resources

- 🌐 **Swagger API Docs (v111):** [https://pourjanali.github.io/sepidar-api-docs](https://pourjanali.github.io/sepidar-api-docs)  
- 📚 **API Docs Repository:** [https://github.com/pourjanali/sepidar-api-docs](https://github.com/pourjanali/sepidar-api-docs)

---

## 🎯 Overview

This SDK provides a straightforward and hassle-free way to connect your PHP applications to the Sepidar accounting system's web services.  
It's designed to be portable, with **zero external dependencies**, making it perfect for any PHP environment — including shared hosting or projects without Composer.

**✨ NEW: Dual Authentication Methods & Built-in Persistence** - The SDK now supports two authentication approaches and automatically saves authentication state between requests.

این SDK یک راهکار ساده و بی‌دردسر برای اتصال برنامه‌های PHP شما به وب‌سرویس سیستم حسابداری سپیدار فراهم می‌کند.  
این پکیج با هدف پرتابل بودن و **بدون هیچ گونه وابستگی خارجی** طراحی شده و برای هر محیط PHP، از جمله هاست‌های اشتراکی یا پروژه‌هایی که از Composer استفاده نمی‌کنند، ایده‌آل است.

**✨ جدید: دو روش احراز هویت و ذخیره‌سازی خودکار** - اکنون SDK از دو روش احراز هویت پشتیبانی می‌کند و به طور خودکار وضعیت احراز هویت را بین درخواست‌ها ذخیره می‌کند.

---

## 🔐 Authentication Methods (روش‌های احراز هویت)

### Method 1: Traditional Login (روش اول: لاگین سنتی) - **RECOMMENDED**

This method uses username/password to authenticate and automatically handles the complete Sepidar authentication flow:

1. **Device Registration** - Register your application with Sepidar
2. **Public Key Extraction** - Extract and cache the RSA public key
3. **User Login** - Authenticate with credentials
4. **Token Management** - Automatically handle token expiration (23 hours)

```php
$client->login('username', 'password');
```

**✅ Advantages:**
- Standard Sepidar authentication flow
- Automatic token refresh
- Secure credential-based authentication
- Unique user session

**🎯 Recommended For:**
- Single application instances
- Standard web applications
- Scenarios where you control the credentials

### Method 2: Direct Keys (روش دوم: کلیدهای مستقیم)

This method allows you to provide pre-authenticated headers directly:

```php
$client->setDirectKeys(
    $integrationId,
    $arbitraryCode, 
    $encArbitraryCode,
    $generationVersion,
    $bearerToken
);
```

**🔄 Use Case:**
- Multiple application instances connecting to the same Sepidar service
- Distributed systems
- Scenarios where you need to share authentication between applications

**⚠️ Important Note about Sepidar Single-User Limitation:**
The Sepidar web service is inherently **single-user** and maintains data internally. If multiple applications try to register and login separately, you'll encounter conflicts. The direct keys method solves this by allowing shared authentication headers across multiple instances.

سرویس وب سپیدار ذاتاً **تک کاربره** است و داده‌ها را به صورت داخلی نگهداری می‌کند. اگر چندین برنامه سعی کنند به طور جداگانه ثبت و لاگین کنند، با خطا مواجه خواهید شد. روش کلیدهای مستقیم این مشکل را با اجازه دادن به اشتراک‌گذاری هدرهای احراز هویت بین چندین نمونه برنامه حل می‌کند.

---

## 🏆 Recommendation (توصیه)

### **Use Traditional Login Method** (استفاده از روش لاگین سنتی)

We strongly recommend using the **Traditional Login Method** for most use cases because:

1. **Unique User Sessions** - Creates proper user sessions in Sepidar
2. **Standard Flow** - Follows Sepidar's intended authentication process
3. **Automatic Management** - SDK handles all complexity automatically
4. **Security** - Proper credential-based authentication

ما به شدت استفاده از **روش لاگین سنتی** را برای اکثر موارد استفاده توصیه می‌کنیم زیرا:

1. **سشن‌های کاربری یکتا** - سشن‌های کاربری مناسب در سپیدار ایجاد می‌کند
2. **فرآیند استاندارد** - فرآیند احراز هویت مورد نظر سپیدار را دنبال می‌کند
3. **مدیریت خودکار** - SDK تمام پیچیدگی‌ها را به طور خودکار مدیریت می‌کند
4. **امنیت** - احراز هویت مبتنی بر اعتبار مناسب

### **When to Use Direct Keys** (چه زمانی از کلیدهای مستقیم استفاده کنیم)

Only use the direct keys method when:
- You have multiple applications connecting to the same Sepidar instance
- You're building a distributed system
- You need to avoid multiple device registrations

فقط زمانی از روش کلیدهای مستقیم استفاده کنید که:
- چندین برنامه دارید که به یک نمونه سپیدار متصل می‌شوند
- در حال ساخت یک سیستم توزیع شده هستید
- نیاز به جلوگیری از ثبت چندین دستگاه دارید

---

## ⚡ Quick Start (شروع سریع)

### Method 1: Traditional Login (روش لاگین سنتی)

```php
<?php
require_once 'SepidarApiClient.php';

use App\Sepidar\SepidarApiClient;

$config = [
    'api_url' => 'http://127.0.0.1:7373/api',
    'serial' => 'YOUR_SEPIDAR_SERIAL',
    'version' => '110',
    'username' => 'web',
    'password' => 'web'
];

try {
    $client = new SepidarApiClient(
        $config['api_url'],
        $config['serial'], 
        $config['version']
    );
    
    // Login handles entire auth flow automatically
    $loginResult = $client->login($config['username'], $config['password']);
    
    if ($loginResult['success']) {
        echo "✅ Login successful!\n";
        
        // Use authenticated requests
        $items = $client->getItems();
        print_r($items);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

### Method 2: Direct Keys (روش کلیدهای مستقیم)

```php
<?php
require_once 'SepidarApiClient.php';

use App\Sepidar\SepidarApiClient;

$config = [
    'api_url' => 'http://127.0.0.1:7373/api',
    'integration_id' => 'YOUR_INTEGRATION_ID',
    'arbitrary_code' => 'YOUR_ARBITRARY_CODE',
    'enc_arbitrary_code' => 'YOUR_ENC_ARBITRARY_CODE', 
    'generation_version' => '110',
    'bearer_token' => 'YOUR_BEARER_TOKEN'
];

try {
    $client = new SepidarApiClient(
        $config['api_url'],
        'direct_keys_mode', // Dummy serial for direct keys mode
        $config['generation_version']
    );
    
    // Set direct authentication keys
    $client->setDirectKeys(
        $config['integration_id'],
        $config['arbitrary_code'],
        $config['enc_arbitrary_code'],
        $config['generation_version'],
        $config['bearer_token']
    );
    
    // Use authenticated requests directly
    $items = $client->getItems();
    print_r($items);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

---

## 🛠️ Installation (نصب)

### Method 1: Manual Installation (نصب دستی)
```php
require_once 'path/to/SepidarApiClient.php';
```

### Method 2: Composer
```bash
composer require pourjanali/sepidar-php-sdk
```

> ⚠️ **Laravel Integration Guide**
>
> If you're using Laravel framework, we provide separate repository with sample code and ready-to-use Service Classes for faster implementation.
>
> 📋 **[View Laravel Examples & Guide](https://github.com/pourjanali/sepidar-laravel)**

---

## 🎪 Interactive Demo (دموی تعاملی)

We provide a comprehensive demo file `example-with-two-authentication-methods.php` that includes:

- **Dual Method Support** - Switch between both authentication methods
- **Real-time Headers Display** - See exactly what headers are being sent
- **Authentication Status** - Monitor current auth state
- **Cache Management** - Clear cache or force re-login
- **Beautiful Persian UI** - Professional interface with Vazirmatn font

ما یک فایل دموی جامع `example-with-two-authentication-methods.php` ارائه می‌دهیم که شامل:

- **پشتیبانی از هر دو روش** - بین دو روش احراز هویت سوییچ کنید
- **نمایش لحظه‌ای هدرها** - دقیقاً ببینید چه هدرهایی ارسال می‌شوند
- **وضعیت احراز هویت** - وضعیت فعلی احراز هویت را مانیتور کنید
- **مدیریت کش** - کش را پاک کنید یا لاگین مجدد اجباری انجام دهید
- **رابط کاربری فارسی زیبا** - رابط کاربری حرفه‌ای با فونت وزیرمتن

### Running the Demo (اجرای دمو)

1. Place files in your web directory
2. Access via browser: `http://your-server/sepidar-demo/example-with-two-authentication-methods.php`
3. Choose your preferred authentication method
4. Enter your credentials and test the connection

---

## 🔧 Core Features (ویژگی‌های اصلی)

### 🎯 Authentication & Security
- **Dual Auth Methods** - Traditional login + direct keys support
- **Auto Token Management** - 23-hour token lifetime with automatic handling
- **RSA Encryption** - Secure public key encryption for requests
- **Header Validation** - Real-time header display for debugging

### ⚡ Performance & Optimization  
- **Zero Dependencies** - No Guzzle, phpseclib, or Composer required
- **Smart Caching** - Automatic persistence of device registration and tokens
- **State Optimization** - Skip redundant operations on subsequent requests
- **Memory Efficient** - Single class design with minimal overhead

### 🔄 State Management
- **Auth Status Monitoring** - Real-time authentication state tracking
- **Cache Control** - Manual cache clearance and force login options
- **Serial-based Storage** - Separate cache for each Sepidar serial
- **Token Recovery** - Automatic handling of expired tokens

---

## 📖 API Reference (مرجع API)

### Core Methods (متدهای اصلی)

#### Authentication Methods (متدهای احراز هویت)
```php
// Method 1: Traditional Login
$client->login(string $username, string $password): array

// Method 2: Direct Keys  
$client->setDirectKeys(
    string $integrationId,
    string $arbitraryCode,
    string $encArbitraryCode, 
    string $generationVersion,
    string $token
): void

// Force fresh login (ignore cache)
$client->forceLogin(string $username, string $password): array
```

#### API Methods (متدهای API)
```php
// Get items list (requires authentication)
$client->getItems(): array

// Generic GET request
$client->get(string $endpoint): array

// Generic POST request  
$client->post(string $endpoint, array $data = []): array
```

#### State Management (مدیریت وضعیت)
```php
// Check current authentication status
$client->getAuthStatus(): array

// Check if device is registered
$client->isDeviceRegistered(): bool

// Check if user is logged in
$client->isLoggedIn(): bool

// Get cached token
$client->getCachedToken(): ?string

// Clear all authentication cache
$client->clearAuthState(): void
```

---

## 🚨 Troubleshooting (عیب‌یابی)

### Common Issues (مشکلات رایج)

#### Authentication Problems (مشکلات احراز هویت)
- **"Device registration failed"** - Verify Sepidar service is running on port 7373
- **"Invalid credentials"** - Check username/password in Sepidar system
- **"Token expired"** - Use `forceLogin()` or wait for auto-refresh

#### Connection Issues (مشکلات اتصال)
- **"cURL extension not enabled"** - Enable `extension=curl` in php.ini
- **"OpenSSL extension not enabled"** - Enable `extension=openssl` in php.ini  
- **"No host part in URL"** - Verify API URL format (include http://)

#### Cache Issues (مشکلات کش)
- **"Cache not working"** - Check directory permissions for storage path
- **"Multiple device registration"** - Use direct keys method for multiple instances

### Testing Checklist (چک‌لیست تست)

- [ ] Sepidar service running on `http://127.0.0.1:7373`
- [ ] Port 7373 accessible from your application
- [ ] cURL and OpenSSL extensions enabled
- [ ] Valid Sepidar serial and credentials
- [ ] Write permissions for cache directory (if using persistence)

---

## 📊 Performance Comparison (مقایسه عملکرد)

| Operation | Traditional Login | Direct Keys |
|-----------|-------------------|-------------|
| First Request | Full auth flow (reg + login) | Direct API calls |
| Subsequent Requests | Cached headers | Direct API calls |
| Multiple Instances | Conflicts possible | Perfect for scaling |
| Token Management | Automatic (23h) | Manual management |
| Recommended For | Single applications | Distributed systems |

---

## 🔄 Migration Guide (راهنمای مهاجرت)

### From Single to Multiple Instances (از تک نمونه به چند نمونه)

If you're moving from a single application to multiple instances:

1. **Current Setup**: Using Traditional Login in one application
2. **Problem**: Cannot register multiple devices with same credentials
3. **Solution**: Switch to Direct Keys method

```php
// Get current headers from working instance
$headers = $client->getAuthenticatedHeaders();

// Use these headers in other instances
$client->setDirectKeys(
    $headers['IntegrationID'],
    $headers['ArbitraryCode'],
    $headers['EncArbitraryCode'], 
    $headers['GenerationVersion'],
    str_replace('Bearer ', '', $headers['Authorization'])
);
```

---

## 🤝 Contributing (مشارکت)

We welcome contributions! Please feel free to:

- Submit pull requests for new features or bug fixes
- Open issues for any bugs or feature requests
- Improve documentation
- Share your use cases and experiences

از مشارکت شما استقبال می‌کنیم! لطفاً:

- درخواست‌های pull برای ویژگی‌های جدید یا رفع خطاها ارسال کنید
- issue برای باگ‌ها یا درخواست ویژگی‌های جدید ایجاد کنید
- مستندات را بهبود بخشید
- موارد استفاده و تجربیات خود را به اشتراک بگذارید

## 📄 License (مجوز)

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

این پروژه تحت مجوز MIT منتشر شده است. برای جزئیات بیشتر به فایل [LICENSE](LICENSE) مراجعه کنید.

---

## 🎉 What's New in v2.1

- **Dual Authentication Methods** - Traditional login + direct keys support
- **Enhanced Header Display** - Real-time visibility of authentication headers
- **Improved Documentation** - Comprehensive guides for both methods
- **Better Error Handling** - Detailed error messages and troubleshooting
- **Interactive Demo** - Web interface to test both authentication methods

**شروع سریع**: فایل `example-with-two-authentication-methods.php` را اجرا کنید - این فایل تمام قابلیت‌های جدید از جمله پشتیبانی از دو روش احراز هویت، نمایش لحظه‌ای هدرها و رابط مدیریت کش را نمایش می‌دهد.

---

## 📞 Support & Community (پشتیبانی و جامعه)

- **GitHub Issues**: [Report bugs or request features](https://github.com/pourjanali/sepidar-php-sdk/issues)
- **Documentation**: [Complete API documentation](https://pourjanali.github.io/sepidar-api-docs)
- **Examples**: [Working code examples](https://github.com/pourjanali/sepidar-php-sdk/tree/main/examples)

**یادآوری مهم**: همیشه از روش لاگین سنتی استفاده کنید مگر اینکه واقعاً نیاز به اتصال چندین برنامه به یک نمونه سپیدار داشته باشید. این روش استانداردتر، امن‌تر و بهتر مدیریت می‌شود.

**Important Reminder**: Always use the Traditional Login method unless you genuinely need multiple applications connecting to the same Sepidar instance. This method is more standard, secure, and better managed.
```

## Key Improvements Made:

### 1. **Clear Dual Method Explanation**
- Clearly explains both authentication methods
- Provides specific use cases for each
- Highlights the single-user limitation of Sepidar

### 2. **Professional Recommendation**
- Strongly recommends traditional login method
- Explains WHY it's the preferred approach
- Only suggests direct keys for specific distributed scenarios

### 3. **Comprehensive Technical Details**
- Complete code examples for both methods
- API reference with all available methods
- Migration guide for scaling applications

### 4. **Enhanced Troubleshooting**
- Specific solutions for common problems
- Testing checklist
- Performance comparison table

### 5. **Better Organization**
- Clear section headings in both English and Persian
- Logical flow from basic to advanced topics
- Visual badges and formatting for better readability

### 6. **Practical Guidance**
- When to use each method
- How to migrate between methods
- Real-world scenarios and solutions

This README now provides a complete professional guide that helps developers understand both authentication methods, make informed decisions, and implement the SDK successfully without confusion.