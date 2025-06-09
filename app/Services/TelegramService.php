<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class TelegramService
{
    protected $token;
    protected $chatId;
    protected $baseUrl;
    protected $rateLimit = 30; // requests per minute
    protected $rateLimitWindow = 60; // seconds

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";

        if (!$this->token || !$this->chatId) {
            throw new Exception('Telegram configuration is missing');
        }
    }

    protected function checkRateLimit()
    {
        $key = 'telegram_rate_limit_' . $this->chatId;
        $requests = Cache::get($key, 0);

        if ($requests >= $this->rateLimit) {
            throw new Exception('Rate limit exceeded. Please try again later.');
        }

        Cache::put($key, $requests + 1, $this->rateLimitWindow);
    }

    public function upload($filePath, $filename, $caption = '')
    {
        try {
            $this->checkRateLimit();

            if (!file_exists($filePath)) {
                throw new Exception('File not found');
            }

            $fileSize = filesize($filePath);
            if ($fileSize > 50 * 1024 * 1024) { // 50MB Telegram limit
                throw new Exception('File size exceeds Telegram\'s 50MB limit');
            }

            $response = Http::timeout(30)
                ->attach('document', file_get_contents($filePath), $filename)
                ->post("{$this->baseUrl}/sendDocument", [
                    'chat_id' => $this->chatId,
                    'caption' => $caption,
                ]);

            if (!$response->successful()) {
                Log::error('Telegram upload failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'filename' => $filename
                ]);
                throw new Exception('Failed to upload file to Telegram');
            }

            $data = $response->json();
            if (!isset($data['result']['document']['file_id'])) {
                throw new Exception('Invalid response from Telegram');
            }

            // return $data['result']['document']['file_id'];
            // Log::info('ResponseData', ['response' => $data]);
            return $data['result'];
        }
        catch (Exception $e) {
            Log::error('Telegram upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDownloadUrl($fileId)
    {
        try {
            $this->checkRateLimit();

            // Try to get from cache first
            $cacheKey = 'telegram_file_path_' . $fileId;
            $filePath = Cache::get($cacheKey);

            if (!$filePath) {
                $response = Http::timeout(10)
                    ->get("{$this->baseUrl}/getFile", [
                        'file_id' => $fileId,
                    ]);

                if (!$response->successful()) {
                    Log::error('Telegram getFile failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'file_id' => $fileId
                    ]);
                    throw new Exception('Failed to get file information from Telegram');
                }

                $data = $response->json();
                if (!isset($data['result']['file_path'])) {
                    throw new Exception('Invalid response from Telegram');
                }

                $filePath = $data['result']['file_path'];

                // Cache the file path for 1 hour
                Cache::put($cacheKey, $filePath, 3600);
            }

            return "https://api.telegram.org/file/bot{$this->token}/{$filePath}";
        }
        catch (Exception $e) {
            Log::error('Telegram getDownloadUrl error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteFile($fileId)
    {
        try {
            $this->checkRateLimit();

            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/deleteMessage", [
                    'chat_id' => $this->chatId,
                    'message_id' => $fileId,
                ]);

            if (!$response->successful()) {
                Log::error('Telegram delete failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'file_id' => $fileId
                ]);
                throw new Exception('Failed to delete file from Telegram');
            }

            // Clear cache
            Cache::forget('telegram_file_path_' . $fileId);

            return true;
        }

        catch (Exception $e) {
            Log::error('Telegram delete error: ' . $e->getMessage());
            throw $e;
        }
    }
}
