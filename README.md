# 🐘 Sepidar PHP SDK

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.0%2B-blue.svg" alt="PHP Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/Dependencies-Zero-brightgreen.svg" alt="Dependencies"></a>
  <a href="#"><img src="https://img.shields.io/badge/License-MIT-darkgreen.svg" alt="License"></a>
</p>

<p align="center">
 A dependency-free, lightweight PHP SDK for seamless integration with the Sepidar API.
</p>
<p align="center">
یک SDK سبک و بدون وابستگی برای اتصال آسان به وب‌سرویس سپیدار در PHP
</p>

---

## 🔗 Related Resources

- 🌐 **Swagger API Docs (v111):** [https://pourjanali.github.io/sepidar-api-docs](https://pourjanali.github.io/sepidar-api-docs)  
- 📘 **API Docs Repository:** [https://github.com/pourjanali/sepidar-api-docs](https://github.com/pourjanali/sepidar-api-docs)

---

This SDK provides a straightforward and hassle-free way to connect your PHP applications to the Sepidar accounting system's web services.  
It's designed to be portable, with **zero external dependencies**, making it perfect for any PHP environment — including shared hosting or projects without Composer.

این SDK یک راهکار ساده و بی‌دردسر برای اتصال برنامه‌های PHP شما به وب‌سرویس سیستم حسابداری سپیدار فراهم می‌کند.  
این پکیج با هدف پرتابل بودن و **بدون هیچ‌گونه وابستگی خارجی** طراحی شده و برای هر نوع محیط PHP، از جمله هاست‌های اشتراکی یا پروژه‌هایی که از Composer استفاده نمی‌کنند، ایده‌آل است.

---

## ✨ Key Features (ویژگی‌های کلیدی)

- **✅ Zero Dependencies:** No need for Guzzle, phpseclib, or even Composer. Just standard PHP extensions (cURL, OpenSSL, SimpleXML).  
- **🚀 Lightweight & Fast:** A single class that handles everything, ensuring minimal overhead.  
- **🔒 Full Authentication Flow:** Manages the entire Sepidar authentication process — including device registration, public key extraction, and RSA encryption for secure requests.  
- **🔧 Easy to Use:** A simple and intuitive API lets you get started in minutes.  
- **🌐 Framework-Agnostic:** Works with any PHP project, whether it's plain PHP, WordPress, or any other framework.

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
## 💡 Usage (نحوه استفاده)

Here's a complete example of how to log in and fetch a list of items from Sepidar.

در اینجا یک مثال کامل از نحوه لاگین و دریافت لیست کالاها از سپیدار آورده شده است.

```php
<?php

// 1. Include the client
require_once 'SepidarApiClient.php';

use App\Sepidar\SepidarApiClient; // Make sure the namespace matches

// 2. Your Sepidar Credentials
$sepidarApiUrl = 'http://127.0.0.1:7373/api'; // Sepidar API Base URL
$sepidarSerial = 'YOUR_SEPIDAR_SERIAL';      // Your Sepidar Serial
$sepidarGenVer = '110';                      // API Documentation Version
$sepidarUsername = 'web';                      // Your Username
$sepidarPassword = 'web';                      // Your Password

try {
    // 3. Initialize the Client
    $client = new SepidarApiClient($sepidarApiUrl, $sepidarSerial, $sepidarGenVer);

    // 4. Login to Sepidar
    // This method handles the entire auth process (register, key extraction) automatically
    $loginResult = $client->login($sepidarUsername, $sepidarPassword);

    if ($loginResult['success']) {
        echo "✅ Login Successful!\n";
        print_r($loginResult['data']);

        // 5. Fetch Items (or any other endpoint)
        echo "\nFetching items...\n";
        $itemsResult = $client->getItems();

        if ($itemsResult['success']) {
            echo "🎉 Items fetched successfully!\n";
            // The actual list of items is in $itemsResult['data']
            print_r($itemsResult['data']);
        } else {
            echo "❌ Error fetching items:\n";
            print_r($itemsResult);
        }

    } else {
        echo "❌ Login Failed:\n";
        print_r($loginResult);
    }

} catch (Exception $e) {
    echo "🔥 A critical error occurred: " . $e->getMessage();
}

```

## 📚 Available Methods (متدهای اصلی)

- `login(string $username, string $password): array`: Authenticates with Sepidar and stores the token internally.
- `getItems(): array`: Fetches a list of items (requires successful login).
- `getAuthenticatedHeaders(): array`: Returns the necessary headers for making authenticated requests to other endpoints.

## 🤝 Contributing (مشارکت)

Contributions are welcome! Please feel free to submit a pull request or open an issue for any bugs or feature requests.

از مشارکت شما استقبال می‌کنیم! لطفاً برای ارسال درخواست Pull یا ثبت ایشو (برای باگ‌ها یا درخواست ویژگی‌های جدید) آزاد باشید.

## 📄 License (مجوز)

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

این پروژه تحت مجوز MIT منتشر شده است. برای جزئیات بیشتر به فایل [LICENSE](LICENSE) مراجعه کنید.
