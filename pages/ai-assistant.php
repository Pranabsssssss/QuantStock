<?php
/**
 * QuantStock — AI Assistant Chat Page
 */

$pdo = Database::getInstance();
$stmt = $pdo->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
$stmt->execute([$user['id']]);
$chatHistory = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Quantum AI Advisor</h2>
        <p>Ask questions about your inventory and get data-driven insights</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-secondary" onclick="clearChat()">
            <i data-lucide="trash-2"></i>
            <span>Clear Chat</span>
        </button>
    </div>
</div>

<div class="chat-container">
    <!-- Messages -->
    <div class="chat-messages" id="chatMessages">
        <?php if (empty($chatHistory)): ?>
            <div class="chat-message">
                <div class="chat-avatar chat-avatar-ai">
                    <i data-lucide="bot"></i>
                </div>
                <div class="chat-bubble chat-bubble-ai">
                    <p>Hello! I'm your Quantum AI Advisor. I analyze your real inventory data to provide actionable insights. Ask me anything about your business!</p>
                    <p style="margin-top:0.5rem; font-size:0.8rem; color:var(--text-tertiary);">Try asking one of the suggested questions below.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($chatHistory as $msg): ?>
                <div class="chat-message <?= $msg['role'] === 'user' ? 'chat-message-user' : '' ?>">
                    <div class="chat-avatar <?= $msg['role'] === 'user' ? 'chat-avatar-user' : 'chat-avatar-ai' ?>">
                        <?php if ($msg['role'] === 'user'): ?>
                            <span><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></span>
                        <?php else: ?>
                            <i data-lucide="bot"></i>
                        <?php endif; ?>
                    </div>
                    <div class="chat-bubble <?= $msg['role'] === 'user' ? 'chat-bubble-user' : 'chat-bubble-ai' ?>">
                        <?php
                        if ($msg['role'] === 'assistant') {
                            $parsed = json_decode($msg['message'], true);
                            if ($parsed && isset($parsed['response'])) {
                                echo '<p>' . nl2br(e($parsed['response'])) . '</p>';
                                if (!empty($parsed['key_metrics'])) {
                                    echo '<div class="chat-metrics">';
                                    foreach ($parsed['key_metrics'] as $m) {
                                        echo '<div class="chat-metric"><div class="chat-metric-label">' . e($m['label'] ?? '') . '</div><div class="chat-metric-value">' . e($m['value'] ?? '') . '</div></div>';
                                    }
                                    echo '</div>';
                                }
                                if (!empty($parsed['action_items'])) {
                                    echo '<div class="chat-actions">';
                                    foreach ($parsed['action_items'] as $a) {
                                        echo '<span class="chat-action">' . e($a) . '</span>';
                                    }
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>' . nl2br(e($msg['message'])) . '</p>';
                            }
                        } else {
                            echo '<p>' . nl2br(e($msg['message'])) . '</p>';
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Suggestions -->
    <div class="chat-suggestions" id="chatSuggestions">
        <button class="chat-suggestion" onclick="sendSuggestion(this)">What should I order next week?</button>
        <button class="chat-suggestion" onclick="sendSuggestion(this)">Which products are overstocked?</button>
        <button class="chat-suggestion" onclick="sendSuggestion(this)">How can I improve profit margins?</button>
        <button class="chat-suggestion" onclick="sendSuggestion(this)">What's my inventory health status?</button>
        <button class="chat-suggestion" onclick="sendSuggestion(this)">Show me sales trends</button>
    </div>

    <!-- Input -->
    <div class="chat-input-area">
        <textarea class="chat-input" id="chatInput" placeholder="Ask about your inventory, sales, or business strategy..." rows="1" onkeydown="handleChatKeydown(event)"></textarea>
        <button class="chat-send" id="chatSendBtn" onclick="sendChatMessage()">
            <i data-lucide="send"></i>
        </button>
    </div>
</div>
