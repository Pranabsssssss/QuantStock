<?php
/**
 * QuantStock — AI Configuration (Groq API Client)
 * 
 * Handles all communication with Groq's LLaMA model.
 * Strict JSON-only responses with schema validation.
 */

class AIClient {
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    
    public function __construct() {
        $this->apiKey = getSetting('ai_api_key', '');
        $this->model = getSetting('ai_model', 'llama-3.3-70b-versatile');
    }

    /**
     * Send a chat completion request to Groq
     */
    public function chat(string $systemPrompt, string $userMessage, float $temperature = 0.3): ?array {
        if (empty($this->apiKey)) {
            return ['error' => 'AI API key not configured. Please set it in Settings.'];
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => $temperature,
            'max_tokens' => 4096,
            'response_format' => ['type' => 'json_object'],
        ];

        return $this->request($payload);
    }

    /**
     * Send request with conversation history
     */
    public function chatWithHistory(string $systemPrompt, array $messages, float $temperature = 0.5): ?array {
        if (empty($this->apiKey)) {
            return ['error' => 'AI API key not configured. Please set it in Settings.'];
        }

        $allMessages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($messages as $msg) {
            $allMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['message'] ?? $msg['content'] ?? '',
            ];
        }

        $payload = [
            'model' => $this->model,
            'messages' => $allMessages,
            'temperature' => $temperature,
            'max_tokens' => 4096,
            'response_format' => ['type' => 'json_object'],
        ];

        return $this->request($payload);
    }

    /**
     * Make HTTP request to Groq API
     */
    private function request(array $payload, int $retries = 2): ?array {
        $ch = curl_init($this->baseUrl);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Groq API cURL error: " . $error);
            if ($retries > 0) {
                sleep(1);
                return $this->request($payload, $retries - 1);
            }
            return ['error' => 'Failed to connect to AI service: ' . $error];
        }

        if ($httpCode !== 200) {
            error_log("Groq API HTTP error {$httpCode}: " . $response);
            $decoded = json_decode($response, true);
            $msg = $decoded['error']['message'] ?? 'AI service returned an error.';
            if ($retries > 0 && $httpCode >= 500) {
                sleep(2);
                return $this->request($payload, $retries - 1);
            }
            return ['error' => "AI Error ({$httpCode}): {$msg}"];
        }

        $decoded = json_decode($response, true);
        if (!$decoded || !isset($decoded['choices'][0]['message']['content'])) {
            return ['error' => 'Invalid response from AI service.'];
        }

        $content = $decoded['choices'][0]['message']['content'];
        $parsed = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to extract JSON from the response
            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                $parsed = json_decode($matches[0], true);
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'AI returned invalid JSON. Please try again.'];
            }
        }

        return $parsed;
    }

    /**
     * Get demand forecast prompt
     */
    public static function getForecastPrompt(): string {
        return <<<PROMPT
You are an expert inventory demand forecasting AI for a retail business. 
You MUST respond with STRICT JSON ONLY. No markdown, no explanations, no text outside JSON.

Analyze the provided sales data and product information to generate demand forecasts.

Your JSON response must follow this exact schema:
{
    "forecast_summary": "Brief overall forecast summary",
    "total_predicted_revenue": 0,
    "confidence_score": 0.85,
    "products": [
        {
            "product_id": 1,
            "product_name": "name",
            "current_stock": 0,
            "predicted_demand_7d": 0,
            "predicted_demand_30d": 0,
            "predicted_demand_90d": 0,
            "trend": "increasing|decreasing|stable",
            "risk_level": "low|medium|high",
            "recommendation": "brief recommendation"
        }
    ],
    "insights": [
        "insight 1",
        "insight 2"
    ]
}
PROMPT;
    }

    /**
     * Get inventory optimization prompt
     */
    public static function getOptimizationPrompt(): string {
        return <<<PROMPT
You are an expert inventory optimization AI for a retail business.
You MUST respond with STRICT JSON ONLY. No markdown, no explanations, no text outside JSON.

Analyze the provided inventory data, sales velocity, and stock levels to provide optimization recommendations.

Your JSON response must follow this exact schema:
{
    "optimization_summary": "Brief overall summary",
    "total_potential_savings": 0,
    "inventory_health_score": 85,
    "recommendations": [
        {
            "product_id": 1,
            "product_name": "name",
            "type": "reorder|overstock|understock|deadstock",
            "priority": "low|medium|high|critical",
            "current_stock": 0,
            "optimal_stock": 0,
            "suggested_order_qty": 0,
            "reorder_date": "YYYY-MM-DD",
            "estimated_cost": 0,
            "reason": "explanation"
        }
    ],
    "overstock_alerts": [
        {
            "product_id": 1,
            "product_name": "name",
            "excess_quantity": 0,
            "locked_value": 0,
            "suggestion": "brief suggestion"
        }
    ],
    "understock_alerts": [
        {
            "product_id": 1,
            "product_name": "name",
            "days_until_stockout": 0,
            "urgency": "low|medium|high|critical",
            "suggested_order": 0
        }
    ],
    "business_risks": [
        {
            "risk": "description",
            "severity": "low|medium|high|critical",
            "mitigation": "suggestion"
        }
    ]
}
PROMPT;
    }

    /**
     * Get business advisor chat prompt
     */
    public static function getChatPrompt(): string {
        return <<<PROMPT
You are an expert Quantum AI Advisor for a retail inventory management platform called QuantStock.
You have access to real business data provided in the context.
You MUST respond with STRICT JSON ONLY. No markdown, no explanations, no text outside JSON.

Provide actionable, data-driven advice based on the real business metrics provided.

Your JSON response must follow this exact schema:
{
    "response": "Your detailed response to the user's question using the provided business data",
    "key_metrics": [
        {
            "label": "metric name",
            "value": "metric value",
            "trend": "up|down|stable"
        }
    ],
    "action_items": [
        "actionable suggestion 1",
        "actionable suggestion 2"
    ],
    "confidence": 0.85
}
PROMPT;
    }
}
