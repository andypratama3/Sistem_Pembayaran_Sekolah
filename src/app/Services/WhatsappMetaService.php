<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappMetaService
{
    protected $apiUrl;

    protected $phoneId;

    protected $accessToken;

    protected $bisnisId;

    protected $client;

    protected bool $configured = false;

    public function __construct()
    {
        $this->client = new Client;
        $this->apiUrl = \get_system_config('whatsapp_api_url', config('services.whatsapp.api_url', 'https://graph.facebook.com/v24.0'));
        $this->phoneId = \get_system_config('whatsapp_phone_number_id', config('services.whatsapp.phone_id'));
        $this->bisnisId = config('services.whatsapp.business_id');
        $this->accessToken = \get_system_config('whatsapp_access_token', config('services.whatsapp.access_token'));
        $this->configured = ! empty($this->phoneId) && ! empty($this->accessToken);

        Log::channel('whatsapp')->info('WhatsApp Service Initialized', [
            'api_url' => $this->apiUrl,
            'phone_id' => $this->phoneId,
            'business_id' => $this->bisnisId,
            'has_token' => ! empty($this->accessToken),
        ]);

        if (! $this->configured) {
            Log::channel('whatsapp')->warning('WhatsApp service is not fully configured', [
                'has_phone_id' => ! empty($this->phoneId),
                'has_access_token' => ! empty($this->accessToken),
            ]);
        }
    }

    /**
     * Send template message (recommended)
     */
    public function sendTemplate(
        string $phone,
        string $templateName,
        array $parameters = [],
        ?string $imageUrl = null
    ): array {
        try {
            if (! $this->configured) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp service not configured',
                ];
            }

            $phone = $this->formatPhone($phone);

            Log::channel('whatsapp')->info('Sending template', [
                'phone' => $phone,
                'template' => $templateName,
                'parameters' => $parameters,
                'imageUrl' => $imageUrl,
            ]);

            $body = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => 'id'],
                ],
            ];

            $components = [];

            if (! empty($imageUrl)) {
                $components[] = [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => 'image',
                            'image' => [
                                'link' => $imageUrl,
                            ],
                        ],
                    ],
                ];
            }

            if (! empty($parameters)) {
                $bodyParams = array_map(
                    fn ($param) => ['type' => 'text', 'text' => (string) $param],
                    $parameters
                );

                $components[] = [
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ];
            }

            if (! empty($components)) {
                $body['template']['components'] = $components;
            }

            Log::channel('whatsapp')->debug('Request body', $body);

            $response = Http::timeout(30)
                ->withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", $body);

            $responseData = $response->json();

            Log::channel('whatsapp')->info('API Response', [
                'status' => $response->status(),
                'body' => $responseData,
            ]);

            if ($response->successful()) {
                Log::channel('whatsapp')->info('Template sent', [
                    'phone' => $phone,
                    'template' => $templateName,
                    'messageId' => $responseData['messages'][0]['id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Template sent successfully',
                    'data' => $responseData,
                ];
            }

            Log::channel('whatsapp')->error('Template failed', [
                'status' => $response->status(),
                'error' => $responseData,
            ]);

            return [
                'success' => false,
                'message' => 'Template failed to send',
                'error' => $responseData,
                'status' => $response->status(),
            ];

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Template error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send text message (free text)
     */
    public function sendMessage(string $phone, string $message, ?string $imageUrl = null): array
    {
        try {
            if (! $this->configured) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp service not configured',
                ];
            }

            $phone = $this->formatPhone($phone);

            Log::channel('whatsapp')->info('Sending message', [
                'phone' => $phone,
                'message_length' => strlen($message),
                'has_image' => ! is_null($imageUrl),
            ]);

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
            ];

            if ($imageUrl) {
                $payload['type'] = 'image';
                $payload['image'] = [
                    'link' => $imageUrl,
                    'caption' => $message,
                ];
            } else {
                $payload['type'] = 'text';
                $payload['text'] = ['body' => $message];
            }

            $response = Http::timeout(30)
                ->withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::channel('whatsapp')->info('Message sent', [
                    'phone' => $phone,
                    'type' => $payload['type'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $responseData,
                ];
            }

            Log::channel('whatsapp')->error('Message failed', [
                'status' => $response->status(),
                'error' => $responseData,
            ]);

            return [
                'success' => false,
                'message' => 'Message failed to send',
                'error' => $responseData,
                'status' => $response->status(),
            ];

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Message error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to WhatsApp format (62...)
     */
    private function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        if (strlen($cleaned) < 11 || strlen($cleaned) > 14) {
            throw new Exception("Invalid phone number length: $phone");
        }

        if (strpos($cleaned, '62') === 0) {
            if (strlen($cleaned) < 12) {
                throw new Exception("Invalid phone format: $phone");
            }

            return $cleaned;
        }

        if (strpos($cleaned, '0') === 0) {
            $formatted = '62'.substr($cleaned, 1);
            if (strlen($formatted) < 12) {
                throw new Exception("Invalid phone format after conversion: $phone");
            }

            return $formatted;
        }

        $formatted = '62'.$cleaned;
        if (strlen($formatted) < 12) {
            throw new Exception("Invalid phone format after prefixing: $phone");
        }

        return $formatted;
    }

    /**
     * Get all templates from Meta
     */
    public function getTemplates(): array
    {
        try {
            if (! $this->configured || empty($this->bisnisId)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp service not configured',
                    'data' => [],
                ];
            }

            $url = "{$this->apiUrl}/{$this->bisnisId}/message_templates";

            Log::channel('whatsapp')->info('Fetching templates', [
                'url' => $url,
                'business_id' => $this->bisnisId,
            ]);

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $templates = json_decode($response->getBody()->getContents(), true);

            Log::channel('whatsapp')->info('Templates fetched', [
                'count' => count($templates['data'] ?? []),
            ]);

            return $templates;

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Get templates error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get stored WhatsApp messages history from local database.
     */
    public function getMessagesHistory(int $limit = 50): array
    {
        try {
            $messages = DB::table('whatsapp_messages')
                ->orderByDesc('created_at')
                ->limit(max(1, min($limit, 200)))
                ->get();

            return [
                'success' => true,
                'message' => 'Messages history retrieved successfully',
                'data' => $messages,
            ];
        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Get messages history error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve message history',
                'error' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
