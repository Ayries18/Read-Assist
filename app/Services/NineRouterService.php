<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NineRouterService
{
    protected Client $http;

    protected string $apiUrl;

    protected string $key;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.ninerouter.api_url'), '/').'/';
        $this->key = config('services.ninerouter.key');

        $this->http = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 120,
            'headers' => [
                'Authorization' => 'Bearer '.$this->key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ],
        ]);
    }

    /**
     * Send a chat completion request to NineRouter.
     *
     * @param  string  $model  Model identifier (e.g., 'openai/gpt-4', 'anthropic/claude-3-opus')
     * @param  array  $messages  Array of message objects [{role, content}]
     * @param  array  $options  Additional options (temperature, max_tokens, etc.)
     * @return array Response from the API
     */
    public function chatCompletion(string $model, array $messages, array $options = []): array
    {
        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        return $this->request('POST', 'chat/completions', $payload);
    }

    /**
     * Send a chat completion request with streaming.
     *
     * @param  string  $model  Model identifier
     * @param  array  $messages  Array of message objects
     * @param  callable  $onChunk  Callback function for each chunk
     * @param  array  $options  Additional options
     * @return array Final accumulated response
     */
    public function chatCompletionStream(string $model, array $messages, callable $onChunk, array $options = []): array
    {
        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'stream' => true,
        ], $options);

        $response = $this->http->post('/chat/completions', [
            'json' => $payload,
            'stream' => true,
        ]);

        $body = $response->getBody();
        $fullContent = '';

        while (! $body->eof()) {
            $line = $body->readLine();
            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);
                if ($data === '[DONE]') {
                    break;
                }
                $chunk = json_decode($data, true);
                if (isset($chunk['choices'][0]['delta']['content'])) {
                    $content = $chunk['choices'][0]['delta']['content'];
                    $fullContent .= $content;
                    $onChunk($content, $chunk);
                }
            }
        }

        return ['content' => $fullContent];
    }

    /**
     * List available models from NineRouter.
     */
    public function listModels(): array
    {
        return $this->request('GET', 'models');
    }

    /**
     * Get the dashboard URL for combos/models.
     */
    public function getDashboardUrl(): string
    {
        return config('services.ninerouter.dashboard_url');
    }

    /**
     * Make an HTTP request to the API.
     */
    protected function request(string $method, string $uri, array $data = []): array
    {
        try {
            $options = [];
            if ($method === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }

            $response = $this->http->request($method, $uri, $options);
            $body = $response->getBody()->getContents();

            return json_decode($body, true) ?? ['raw' => $body];
        } catch (GuzzleException $e) {
            Log::error('NineRouter API error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if the API key is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->key);
    }
}
