<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected $apiKey;
    protected $model;
    protected $maxTokens;
    protected $temperature;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        $this->model = config('openai.model');
        $this->maxTokens = (int) config('openai.max_tokens');
        $this->temperature = (float) config('openai.temperature');
        $this->apiUrl = config('openai.api_url');
    }

    public function generateHint(string $question, string $curriculumContent): array
    {
        try {
            $prompt = $this->buildPrompt($question, $curriculumContent);
            
            Log::info('OpenAI Request Parameters:', [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'max_tokens_type' => gettype($this->maxTokens),
                'temperature' => $this->temperature,
                'temperature_type' => gettype($this->temperature),
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'あなたはプログラミング学習のメンターです。学習者が自分で答えを導き出せるよう、適切なヒントを提供してください。'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null
                ];
            } else {
                Log::error('OpenAI API Error: ' . $response->body());
                return [
                    'success' => false,
                    'error' => 'API request failed'
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Service error occurred'
            ];
        }
    }

    private function buildPrompt(string $question, string $curriculumContent): string
    {
        return "以下のカリキュラム内容を参考に、学習者の質問に答えてください。

【重要なルール】
- 直接的な解答は提供しない
- 使用すべき関数や構文をヒントとして提示
- 考え方の手順を段階的に示す
- 学習者が自分で答えを導き出せるようサポート

【カリキュラム内容】
{$curriculumContent}

【学習者の質問】
{$question}

【回答形式】
1. 問題の理解確認
2. 解決に必要な概念の説明
3. 使用を検討すべき関数・構文の提示
4. 実装のステップ案内

上記の形式で、学習者が自分で解決できるようなヒントを提供してください。";
    }
}