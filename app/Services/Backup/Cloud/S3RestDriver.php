<?php

namespace App\Services\Backup\Cloud;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Exception;

class S3RestDriver
{
    protected string $accessKey;
    protected string $secretKey;
    protected string $region;
    protected string $bucket;
    protected ?string $endpoint;
    protected bool $usePathStyle;
    protected Client $client;

    public function __construct(
        string $accessKey,
        string $secretKey,
        string $bucket,
        string $region = 'us-east-1',
        ?string $endpoint = null,
        bool $usePathStyle = false
    ) {
        $this->accessKey = trim($accessKey);
        $this->secretKey = trim($secretKey);
        $this->bucket = trim($bucket);
        $this->region = !empty($region) ? trim($region) : 'us-east-1';
        $this->endpoint = !empty($endpoint) ? rtrim(trim($endpoint), '/') : null;
        $this->usePathStyle = $usePathStyle;

        $this->client = new Client([
            'http_errors' => false,
            'timeout'     => 120,
            'connect_timeout' => 15,
        ]);
    }

    /**
     * Build base URL for the bucket request.
     */
    protected function getBaseUrl(string $path = ''): string
    {
        $path = ltrim($path, '/');

        if (!empty($this->endpoint)) {
            if ($this->usePathStyle) {
                // e.g. https://minio.example.com/my-bucket/path/file.zip
                return $this->endpoint . '/' . rawurlencode($this->bucket) . ($path !== '' ? '/' . $this->encodePath($path) : '');
            } else {
                // e.g. https://my-bucket.endpoint.com/path/file.zip
                $parsed = parse_url($this->endpoint);
                $scheme = $parsed['scheme'] ?? 'https';
                $host = $parsed['host'] ?? '';
                $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                return $scheme . '://' . $this->bucket . '.' . $host . $port . ($path !== '' ? '/' . $this->encodePath($path) : '');
            }
        }

        // Standard AWS S3 URL
        if ($this->usePathStyle || strpos($this->bucket, '.') !== false) {
            return "https://s3.{$this->region}.amazonaws.com/" . rawurlencode($this->bucket) . ($path !== '' ? '/' . $this->encodePath($path) : '');
        }

        return "https://{$this->bucket}.s3.{$this->region}.amazonaws.com" . ($path !== '' ? '/' . $this->encodePath($path) : '');
    }

    protected function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    /**
     * Create AWS SigV4 signed request.
     */
    protected function createSignedRequest(string $method, string $url, array $queryParams = [], $body = null, array $headers = []): Request
    {
        $uriParts = parse_url($url);
        $host = $uriParts['host'] . (isset($uriParts['port']) ? ':' . $uriParts['port'] : '');
        $path = $uriParts['path'] ?? '/';

        $now = gmdate('Ymd\THis\Z');
        $dateOnly = gmdate('Ymd');

        // Prepare query string
        ksort($queryParams);
        $canonicalQueryParts = [];
        foreach ($queryParams as $k => $v) {
            $canonicalQueryParts[] = rawurlencode($k) . '=' . rawurlencode((string)$v);
        }
        $canonicalQueryString = implode('&', $canonicalQueryParts);
        $fullUrl = $url . ($canonicalQueryString !== '' ? '?' . $canonicalQueryString : '');

        // Calculate payload hash
        if ($body !== null && is_string($body)) {
            $payloadHash = hash('sha256', $body);
        } elseif ($body !== null && is_resource($body)) {
            // For streaming upload
            $stream = Utils::streamFor($body);
            $payloadHash = hash('sha256', (string) $stream);
            $stream->rewind();
            $body = $stream;
        } else {
            $payloadHash = hash('sha256', '');
        }

        $headers['host'] = $host;
        $headers['x-amz-date'] = $now;
        $headers['x-amz-content-sha256'] = $payloadHash;

        // Canonical headers
        $canonicalHeaders = [];
        $signedHeaderNames = [];
        foreach ($headers as $name => $value) {
            $lowerName = strtolower(trim($name));
            $canonicalHeaders[$lowerName] = trim((string)$value);
            $signedHeaderNames[] = $lowerName;
        }
        sort($signedHeaderNames);
        ksort($canonicalHeaders);

        $canonicalHeaderString = '';
        foreach ($canonicalHeaders as $name => $value) {
            $canonicalHeaderString .= $name . ':' . $value . "\n";
        }
        $signedHeadersList = implode(';', $signedHeaderNames);

        // Canonical Request
        $canonicalRequest = strtoupper($method) . "\n"
            . $path . "\n"
            . $canonicalQueryString . "\n"
            . $canonicalHeaderString . "\n"
            . $signedHeadersList . "\n"
            . $payloadHash;

        // String to sign
        $credentialScope = "{$dateOnly}/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n"
            . $now . "\n"
            . $credentialScope . "\n"
            . hash('sha256', $canonicalRequest);

        // Calculate signature
        $kDate    = hash_hmac('sha256', $dateOnly, 'AWS4' . $this->secretKey, true);
        $kRegion  = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$credentialScope}, SignedHeaders={$signedHeadersList}, Signature={$signature}";
        $headers['Authorization'] = $authorization;

        return new Request($method, $fullUrl, $headers, $body);
    }

    /**
     * Test connection to the S3 bucket.
     */
    public function testConnection(): array
    {
        try {
            $url = $this->getBaseUrl();
            $request = $this->createSignedRequest('GET', $url, ['list-type' => '2', 'max-keys' => '1']);
            $response = $this->client->send($request);

            $status = $response->getStatusCode();
            if ($status >= 200 && $status < 300) {
                return ['success' => true, 'message' => 'Connected successfully to cloud storage bucket!'];
            }

            $body = (string) $response->getBody();
            $errorMsg = "HTTP {$status}";
            if (preg_match('/<Message>(.*?)<\/Message>/', $body, $m)) {
                $errorMsg .= ': ' . html_entity_decode($m[1]);
            }

            return ['success' => false, 'message' => 'Cloud connection failed: ' . $errorMsg];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Connection error: ' . $e->getMessage()];
        }
    }

    /**
     * Upload a local file to S3 bucket.
     */
    public function putObject(string $localFilePath, string $cloudPath): bool
    {
        if (!file_exists($localFilePath)) {
            throw new Exception("Local file not found: {$localFilePath}");
        }

        $url = $this->getBaseUrl($cloudPath);
        $handle = fopen($localFilePath, 'rb');
        if (!$handle) {
            throw new Exception("Could not open local file for reading: {$localFilePath}");
        }

        $mime = str_ends_with(strtolower($cloudPath), '.zip') ? 'application/zip' : 'application/sql';
        $headers = [
            'content-type' => $mime,
            'content-length' => (string) filesize($localFilePath),
        ];

        try {
            $request = $this->createSignedRequest('PUT', $url, [], $handle, $headers);
            $response = $this->client->send($request);
            $status = $response->getStatusCode();

            if ($status >= 200 && $status < 300) {
                return true;
            }

            $body = (string) $response->getBody();
            throw new Exception("S3 upload failed ({$status}): " . $body);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    /**
     * Download a cloud object to local file path.
     */
    public function getObject(string $cloudPath, string $localSavePath): bool
    {
        $url = $this->getBaseUrl($cloudPath);
        $request = $this->createSignedRequest('GET', $url);
        $response = $this->client->send($request, ['sink' => $localSavePath]);

        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            return true;
        }

        throw new Exception("S3 download failed with status {$status}");
    }

    /**
     * Delete an object from the S3 bucket.
     */
    public function deleteObject(string $cloudPath): bool
    {
        $url = $this->getBaseUrl($cloudPath);
        $request = $this->createSignedRequest('DELETE', $url);
        $response = $this->client->send($request);

        $status = $response->getStatusCode();
        return ($status >= 200 && $status < 300) || $status === 404;
    }

    /**
     * List backup objects in the S3 bucket.
     */
    public function listObjects(string $prefix = ''): array
    {
        $url = $this->getBaseUrl();
        $params = ['list-type' => '2'];
        if (!empty($prefix)) {
            $params['prefix'] = $prefix;
        }

        $request = $this->createSignedRequest('GET', $url, $params);
        $response = $this->client->send($request);

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            return [];
        }

        $body = (string) $response->getBody();
        $items = [];

        // Parse XML response
        try {
            $xml = simplexml_load_string($body);
            if ($xml && isset($xml->Contents)) {
                foreach ($xml->Contents as $content) {
                    $key = (string) $content->Key;
                    $filename = basename($key);
                    if (empty($filename) || str_ends_with($key, '/')) {
                        continue;
                    }

                    $size = (int) $content->Size;
                    $modified = (string) $content->LastModified;

                    $items[] = [
                        'key'           => $key,
                        'filename'      => $filename,
                        'size_bytes'    => $size,
                        'formatted_size'=> $this->formatSize($size),
                        'last_modified' => date('Y-m-d H:i:s', strtotime($modified)),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Error parsing XML
        }

        return $items;
    }

    /**
     * Format bytes to readable string.
     */
    protected function formatSize(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}

