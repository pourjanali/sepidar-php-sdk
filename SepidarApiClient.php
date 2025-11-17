<?php

namespace App\Sepidar;

// نیازی به Composer و vendor/autoload.php نیست

class SepidarApiClient
{
    protected string $baseUrl;
    protected string $serial;
    protected string $generationVersion;
    protected string $key; // AES key
    protected int $integrationId;

    // مسیر ذخیره‌سازی وضعیت
    protected string $storagePath;

    // متغیرهای حافظه موقت (به جای فایل)
    private ?string $pem = null;
    private ?string $token = null;
    private bool $isRegistered = false;

    public function __construct(string $baseUrl, string $serial, string $generationVersion, string $storagePath = null)
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('cURL extension is not enabled. Please enable it in your php.ini');
        }
        if (!function_exists('openssl_decrypt')) {
            throw new \Exception('OpenSSL extension is not enabled. Please enable it in your php.ini');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->serial = $serial;
        $this->generationVersion = $generationVersion;

        // ساخت کلید AES و IntegrationID بر اساس سریال
        $this->key = $this->serial . $this->serial;
        $this->integrationId = (int)substr($this->serial, 0, 4);

        // مسیر ذخیره‌سازی وضعیت
        $this->storagePath = $storagePath ?: sys_get_temp_dir() . '/sepidar_auth/';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }

        // بارگذاری وضعیت ذخیره شده
        $this->loadAuthState();
    }

    /**
     * بارگذاری وضعیت احراز هویت از ذخیره‌سازی
     */
    private function loadAuthState(): void
    {
        $stateFile = $this->getStateFilePath();
        
        if (file_exists($stateFile)) {
            $state = json_decode(file_get_contents($stateFile), true);
            
            if ($state && is_array($state)) {
                $this->pem = $state['pem'] ?? null;
                $this->token = $state['token'] ?? null;
                $this->isRegistered = $state['isRegistered'] ?? false;
                
                // بررسی انقضای توکن (اگر بیش از 23 ساعت گذشته، باطل می‌شود)
                $tokenTime = $state['token_time'] ?? 0;
                if ($this->token && (time() - $tokenTime) < 82800) { // 23 ساعت
                    $this->token = $state['token'];
                } else {
                    $this->token = null;
                    $this->saveAuthState();
                }
            }
        }
    }

    /**
     * ذخیره وضعیت احراز هویت
     */
    private function saveAuthState(): void
    {
        $state = [
            'pem' => $this->pem,
            'token' => $this->token,
            'isRegistered' => $this->isRegistered,
            'token_time' => $this->token ? time() : 0,
            'serial' => $this->serial, // برای اطمینان از تطابق سریال
            'saved_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->getStateFilePath(), json_encode($state, JSON_PRETTY_PRINT));
    }

    /**
     * پاک کردن وضعیت احراز هویت
     */
    public function clearAuthState(): void
    {
        $stateFile = $this->getStateFilePath();
        if (file_exists($stateFile)) {
            unlink($stateFile);
        }
        
        $this->pem = null;
        $this->token = null;
        $this->isRegistered = false;
    }

    /**
     * مسیر فایل ذخیره‌سازی وضعیت
     */
    private function getStateFilePath(): string
    {
        $filename = 'sepidar_auth_' . md5($this->serial . $this->baseUrl) . '.json';
        return $this->storagePath . $filename;
    }

    /**
     * بررسی آیا دستگاه قبلاً ثبت شده و کلید عمومی استخراج شده
     */
    public function isDeviceRegistered(): bool
    {
        return $this->isRegistered && $this->pem !== null;
    }

    /**
     * بررسی آیا کاربر لاگین کرده
     */
    public function isLoggedIn(): bool
    {
        return $this->token !== null;
    }

    /**
     * گام ۱: ثبت دستگاه (فقط اگر قبلاً ثبت نشده)
     * @return array
     * @throws \Exception
     */
    public function registerDevice(): array
    {
        // اگر قبلاً ثبت شده، نیازی به ثبت مجدد نیست
        if ($this->isDeviceRegistered()) {
            return [
                'success' => true,
                'message' => 'Device already registered.',
                'from_cache' => true
            ];
        }

        list($cypherBase64, $ivBase64) = $this->encryptAes($this->key, (string)$this->integrationId);

        $payload = [
            'Cypher' => $cypherBase64,
            'IV' => $ivBase64,
            'IntegrationID' => $this->integrationId,
        ];

        $result = $this->httpClientRequest('POST', 'Devices/Register/', [], $payload);

        if ($result['success']) {
            $data = $result['data'];
            
            // ذخیره وضعیت ثبت
            $this->isRegistered = true;
            $this->saveAuthState();
            
            return [
                'success' => true,
                'message' => 'Device registered successfully.',
                'from_cache' => false,
                'cypher' => $data['Cypher'],
                'iv' => $data['IV']
            ];
        } else {
            return $result; // برگرداندن خطای cURL
        }
    }

    /**
     * گام ۲: استخراج کلید عمومی (فقط اگر قبلاً استخراج نشده)
     * @return array
     * @throws \Exception
     */
    protected function extractPublicKey(): array
    {
        // اگر قبلاً استخراج شده، از حافظه استفاده کن
        if ($this->pem !== null) {
            return ['success' => true, 'message' => 'Public key loaded from cache.', 'from_cache' => true];
        }

        // همیشه دستگاه را ثبت کن اگر ثبت نشده
        $regResult = $this->registerDevice();
        if (!$regResult['success']) {
            return $regResult; // برگرداندن خطای رجیستر
        }

        $cypherBase64 = $regResult['cypher'];
        $ivBase64 = $regResult['iv'];

        $cypher = base64_decode($cypherBase64);
        $iv = base64_decode($ivBase64);

        $decrypted = openssl_decrypt($cypher, 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            return ['success' => false, 'message' => '❌ رمزگشایی کلید عمومی ناموفق بود.'];
        }

        $xml = $decrypted;
        // جلوگیری از خطاهای XML
        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($xml);
        if (!$xmlObj || !isset($xmlObj->Modulus) || !isset($xmlObj->Exponent)) {
            return ['success' => false, 'message' => '❌ کلید عمومی در XML یافت نشد.'];
        }

        $modulus_b64 = (string)$xmlObj->Modulus;
        $exponent_b64 = (string)$xmlObj->Exponent;

        // تبدیل Modulus و Exponent به فرمت PEM
        $pem = $this->convertModExpToPem(base64_decode($modulus_b64), base64_decode($exponent_b64));

        if ($pem === false) {
             return ['success' => false, 'message' => '❌ ساخت کلید PEM ناموفق بود.'];
        }

        // ذخیره کلید در حافظه موقت کلاس و ذخیره‌سازی
        $this->pem = $pem;
        $this->saveAuthState();

        return ['success' => true, 'message' => '✅ کلید عمومی استخراج و ذخیره شد.', 'from_cache' => false];
    }

    /**
     * گام ۳: ساخت هدرهای مورد نیاز برای تمام درخواست‌ها
     * @return array
     * @throws \Exception
     */
    protected function generateHeaders(): array
    {
        // چک می‌کنیم آیا کلید در حافظه هست یا نه
        if ($this->pem === null) {
            $extractResult = $this->extractPublicKey();
            if (!$extractResult['success']) {
                $errorBodyJson = json_encode($extractResult['error_body'] ?? 'No response body', JSON_UNESCAPED_UNICODE);
                throw new \Exception($extractResult['message'] . " - Body: " . $errorBodyJson);
            }
        }

        $pemKey = $this->pem;

        // جایگزین Ramsey/Uuid
        $uuid = $this->generateUuidV4();
        $uuidBytes = $this->guidToBytes($uuid);

        // جایگزین phpseclib
        // رمزنگاری با استفاده از OpenSSL داخلی PHP
        $publicKey = openssl_get_publickey($pemKey);
        if($publicKey === false) {
            throw new \Exception('کلید عمومی نامعتبر است.');
        }

        $encrypted = '';
        $success = openssl_public_encrypt($uuidBytes, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        openssl_free_key($publicKey); // آزاد کردن حافظه کلید

        if (!$success) {
            throw new \Exception('رمزنگاری RSA ناموفق بود: ' . openssl_error_string());
        }

        $encArbitraryCode = base64_encode($encrypted);

        return [
            'GenerationVersion' => $this->generationVersion,
            'IntegrationID' => $this->integrationId,
            'ArbitraryCode' => $uuid,
            'EncArbitraryCode' => $encArbitraryCode,
        ];
    }

    /**
     * گام ۴: لاگین و دریافت توکن (فقط اگر قبلاً لاگین نکرده)
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login(string $username, string $password): array
    {
        try {
            // اگر قبلاً لاگین کرده‌اید، از توکن موجود استفاده کنید
            if ($this->isLoggedIn()) {
                return [
                    'success' => true,
                    'message' => 'Already logged in (using cached token).',
                    'from_cache' => true,
                    'data' => ['Token' => $this->token]
                ];
            }

            $headers = $this->generateHeaders();
            $body = [
                'UserName' => $username,
                'PasswordHash' => md5($password),
            ];

            $result = $this->httpClientRequest('POST', 'users/login', $headers, $body);

            if ($result['success']) {
                $data = $result['data'];
                $token = $data['Token'] ?? null;

                if ($token) {
                    // ذخیره توکن در حافظه موقت کلاس و ذخیره‌سازی
                    $this->token = $token;
                    $this->saveAuthState();
                    
                    return [
                        'success' => true,
                        'message' => 'Login successful and token saved.',
                        'from_cache' => false,
                        'data' => $data
                    ];
                } else {
                    return ['success' => false, 'message' => 'Token not found in response'];
                }
            } else {
                return $result; // برگرداندن خطای cURL
            }

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * لاگین اجباری (برای زمانی که می‌خواهید حتماً لاگین تازه انجام شود)
     * @param string $username
     * @param string $password
     * @return array
     */
    public function forceLogin(string $username, string $password): array
    {
        $this->clearAuthState();
        return $this->login($username, $password);
    }

    /**
     * گام ۵: دریافت لیست آیتم‌ها (نیازمند لاگین)
     * @return array
     */
    public function getItems(): array
    {
        try {
            // ۱. هدرهای احراز هویت شده را دریافت کن
            $headersResult = $this->getAuthenticatedHeaders();

            if (empty($headersResult['success'])) {
                // اگر لاگین نکرده باشیم، خطا را برمی‌گرداند
                return $headersResult;
            }

            $headers = $headersResult['headers'];
            
            // ۲. درخواست GET را به اندپوینت Items ارسال کن
            $result = $this->httpClientRequest('GET', 'Items', $headers, []);

            return $result; // بازگرداندن نتیجه (موفق یا ناموفق)

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * گام ۵: ساخت هدرهای احراز هویت شده (با توکن)
     * @return array
     * @throws \Exception
     */
    public function getAuthenticatedHeaders(): array
    {
        // اگر توکن نداریم، خطا برگردان
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'User not logged in. Please login first.'];
        }

        $baseHeaders = $this->generateHeaders();
        $authHeaders = [
            'Authorization' => 'Bearer ' . $this->token
        ];

        return ['success' => true, 'headers' => array_merge($baseHeaders, $authHeaders)];
    }

    /**
     * دریافت وضعیت فعلی احراز هویت
     * @return array
     */
    public function getAuthStatus(): array
    {
        return [
            'device_registered' => $this->isDeviceRegistered(),
            'logged_in' => $this->isLoggedIn(),
            'has_public_key' => $this->pem !== null,
            'storage_path' => $this->storagePath
        ];
    }

    /**
     * دریافت توکن از حافظه موقت
     * @return string|null
     */
    public function getCachedToken(): ?string
    {
        return $this->token;
    }

    // --- توابع کمکی --- (بقیه توابع بدون تغییر می‌مانند)
    
    /**
     * تابع کمکی برای ارسال درخواست‌های cURL (جایگزین Guzzle)
     */
    private function httpClientRequest(string $method, string $endpoint, array $headers = [], array $jsonPayload = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init();

        $curlHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "$key: $value";
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 ثانیه
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // برای تست محلی (در حالت پروداکشن true شود)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // برای تست محلی

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonPayload));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            // این خطا یعنی اصلاً نتوانسته به آدرس وصل شود
            $errorMessage = "❌ خطای cURL: ناتوان در اتصال به $url";
            return [
                'success' => false,
                'status' => 0,
                'message' => $errorMessage,
                'error_details' => $curlError
            ];
        }

        $data = json_decode($responseBody, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'status' => $httpCode,
                'data' => $data
            ];
        } else {
            $errorMessage = "❌ خطا در ارتباط با Sepidar (HTTP Status: $httpCode)";
            return [
                'success' => false,
                'status' => $httpCode,
                'message' => $errorMessage,
                'error_body' => $data ?? $responseBody
            ];
        }
    }

    /**
     * @param \Exception $e
     * @return array
     */
    private function handleError(\Exception $e): array
    {
        $message = $e->getMessage();
        error_log("Sepidar API Error: " . $message);
        
        // اگر پیام خطا از سمت cURL یا HTTP باشد، همان را نمایش بده
        // در غیر این صورت، پیام "خطای داخلی" را بگذار
        if (strpos($message, '❌') === 0) { // Check if it's one of our custom API errors
            $errorMessage = $message;
            $errorDetails = "Exception in " . basename($e->getFile()) . " on line " . $e->getLine();
        } else {
            $errorMessage = '❌ خطای داخلی اسکریپت';
            $errorDetails = $message;
        }

        return [
            'success' => false,
            'status' => $e->getCode(),
            'message' => $errorMessage,
            'error_details' => $errorDetails,
        ];
    }

    /**
     * تابع داخلی PHP برای رمزنگاری
     * @throws \Exception
     */
    private function encryptAes(string $key, string $data): array
    {
        $iv = random_bytes(16);
        $cypher = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return [
            base64_encode($cypher),
            base64_encode($iv),
        ];
    }

    /**
     * @param string $guid
     * @return string
     */
    private function guidToBytes(string $guid): string
    {
        $hex = str_replace('-', '', $guid);
        return pack("H*", $hex);
    }

    /**
     * تابع کمکی برای ساخت UUIDv4 (جایگزین Ramsey/Uuid)
     * @throws \Exception
     */
    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * تابع کمکی برای تبدیل Modulus و Exponent به فرمت PEM (جایگزین phpseclib)
     *
     * @param string $modulus
     * @param string $exponent
     * @return string
     */
    private function convertModExpToPem(string $modulus, string $exponent)
    {
        $components = [
            'modulus' => $modulus,
            'exponent' => $exponent
        ];

        // اگر بیت اول 1 بود، یک 00 اضافه می‌کنیم تا عدد مثبت در نظر گرفته شود
        $components = array_map(function ($value) {
            $value = bin2hex($value);
            if (hexdec($value[0]) >= 8) {
                $value = '00' . $value;
            }
            $value = hex2bin($value);
            return $value;
        }, $components);

        // --- ساختار DER encoding برای RSAPublicKey ---

        // 1. INTEGER (modulus)
        $modulus = "\x02" . $this->encodeLength(strlen($components['modulus'])) . $components['modulus'];
        // 2. INTEGER (exponent)
        $exponent = "\x02" . $this->encodeLength(strlen($components['exponent'])) . $components['exponent'];
        // 3. SEQUENCE (RSAPublicKey)
        $rsaPublicKeyContent = $modulus . $exponent;
        $rsaPublicKey = "\x30" . $this->encodeLength(strlen($rsaPublicKeyContent)) . $rsaPublicKeyContent;

        // --- ساختار DER encoding برای AlgorithmIdentifier ---
        // OID 1.2.840.113549.1.1.1 rsaEncryption (شامل پارامترهای NULL)
        // این رشته هگز کامل و آماده است
        $algorithmIdentifier = hex2bin('300D06092A864886F70D0101010500');

        // --- ساختار DER encoding برای SubjectPublicKeyInfo ---

        // 4. BIT STRING (subjectPublicKey)
        $bitStringContent = "\0" . $rsaPublicKey; // 0x00 برای "بیت‌های استفاده نشده"
        $subjectPublicKey = "\x03" . $this->encodeLength(strlen($bitStringContent)) . $bitStringContent;

        // 5. SEQUENCE (SubjectPublicKeyInfo - کلید نهایی)
        $publicKeyInfoContent = $algorithmIdentifier . $subjectPublicKey;
        $publicKeyInfo = "\x30" . $this->encodeLength(strlen($publicKeyInfoContent)) . $publicKeyInfoContent;

        // تبدیل به فرمت PEM
        $pem = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($publicKeyInfo), 64, "\n") .
            "-----END PUBLIC KEY-----\n";

        return $pem;
    }

    /**
     * تابع کمکی برای انکود کردن طول در فرمت ASN.1
     */
    private function encodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(dechex($length), '0');
        if (strlen($temp) % 2) {
            $temp = '0' . $temp;
        }

        $lengthBytes = hex2bin($temp);
        return chr(0x80 | strlen($lengthBytes)) . $lengthBytes;
    }
}