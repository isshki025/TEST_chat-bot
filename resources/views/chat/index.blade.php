<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>プログラミング学習チャットボット</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .stats {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            padding: 1rem;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }

        .message-container {
            display: flex;
            margin-bottom: 1rem;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .message-container.user {
            flex-direction: row-reverse;
        }

        .message-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .message-icon.user {
            background-color: #3498db;
        }

        .message-icon.assistant {
            margin-top: auto;
            padding: 0;
        }

        .message-icon.assistant img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: contain;
        }

        .message-icon.error {
            background-color: #e74c3c;
        }

        .message {
            position: relative;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            max-width: calc(100% - 60px);
            word-wrap: break-word;
        }

        .message.user {
            background-color: #3498db;
            color: white;
            border-bottom-right-radius: 0;
        }

        .message.assistant {
            background-color: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 0;
        }

        .message.error {
            background-color: #e74c3c;
            color: white;
            border-bottom-left-radius: 6px;
        }

        .message pre {
            background-color: rgba(0,0,0,0.1);
            padding: 0.5rem;
            border-radius: 4px;
            overflow-x: auto;
            margin: 0.5rem 0;
        }

        .related-units {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .input-section {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            gap: 1rem;
        }

        .input-section input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .input-section button {
            padding: 0.75rem 1.5rem;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .input-section button:hover {
            background-color: #2980b9;
        }

        .input-section button:disabled {
            background-color: #bdc3c7;
            cursor: not-allowed;
        }

        .loading {
            text-align: center;
            padding: 1rem;
            color: #7f8c8d;
        }

        .welcome-message {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
        }

        .welcome-message h2 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .controls button {
            padding: 0.5rem 1rem;
            background-color: #95a5a6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .controls button:hover {
            background-color: #7f8c8d;
        }

        @media (max-width: 768px) {
            .chat-container {
                padding: 0.5rem;
            }

            .message {
                max-width: calc(100% - 50px);
            }

            .message-icon {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .message-container {
                gap: 0.5rem;
            }

            .input-section {
                flex-direction: column;
            }

            .controls {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>プログラミング学習チャットボット</h1>
        <div class="stats">
            カリキュラム単元数: {{ $stats['total_units'] }} |
            総サイズ: {{ number_format($stats['total_size'] / 1024, 1) }}KB
        </div>
    </div>

    <div class="chat-container">
        <div class="controls">
            <button id="refresh-btn">カリキュラム更新</button>
            <button id="clear-btn">チャット履歴クリア</button>
        </div>

        <div class="chat-messages" id="chat-messages">
            <div class="welcome-message">
                <h2>プログラミング学習のお手伝いをします</h2>
                <p>わからないことがあれば、お気軽に質問してください。<br>
                   直接的な答えではなく、自分で解決できるようなヒントを提供します。</p>
            </div>
        </div>

        <div class="input-section">
            <input type="text" id="question-input" placeholder="質問を入力してください..." maxlength="1000">
            <button id="send-btn">送信</button>
        </div>
    </div>

    <script>
        class ChatBot {
            constructor() {
                this.messagesContainer = document.getElementById('chat-messages');
                this.questionInput = document.getElementById('question-input');
                this.sendBtn = document.getElementById('send-btn');
                this.refreshBtn = document.getElementById('refresh-btn');
                this.clearBtn = document.getElementById('clear-btn');

                this.init();
            }

            init() {
                this.sendBtn.addEventListener('click', () => this.sendQuestion());
                this.questionInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.sendQuestion();
                    }
                });
                this.refreshBtn.addEventListener('click', () => this.refreshCurriculum());
                this.clearBtn.addEventListener('click', () => this.clearChat());

                this.questionInput.focus();
            }

            async sendQuestion() {
                const question = this.questionInput.value.trim();

                if (!question) {
                    return;
                }

                this.addMessage('user', question);
                this.questionInput.value = '';
                this.setLoading(true);

                try {
                    const response = await fetch('/api/chat/ask', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ question })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.addMessage('assistant', data.answer, data.related_units);
                    } else {
                        this.addMessage('error', data.message || 'エラーが発生しました');
                    }
                } catch (error) {
                    this.addMessage('error', 'ネットワークエラーが発生しました');
                    console.error('Error:', error);
                } finally {
                    this.setLoading(false);
                }
            }

            async refreshCurriculum() {
                this.setLoading(true);

                try {
                    const response = await fetch('/api/chat/refresh', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.addMessage('assistant', data.message);
                        location.reload();
                    } else {
                        this.addMessage('error', data.message || '更新に失敗しました');
                    }
                } catch (error) {
                    this.addMessage('error', 'ネットワークエラーが発生しました');
                    console.error('Error:', error);
                } finally {
                    this.setLoading(false);
                }
            }

            clearChat() {
                this.messagesContainer.innerHTML = `
                    <div class="welcome-message">
                        <h2>プログラミング学習のお手伝いをします</h2>
                        <p>わからないことがあれば、お気軽に質問してください。<br>
                           直接的な答えではなく、自分で解決できるようなヒントを提供します。</p>
                    </div>
                `;
            }

            addMessage(type, content, relatedUnits = null) {
                const welcomeMessage = this.messagesContainer.querySelector('.welcome-message');
                if (welcomeMessage) {
                    welcomeMessage.remove();
                }

                // メッセージコンテナの作成
                const messageContainer = document.createElement('div');
                messageContainer.className = `message-container ${type}`;

                // アイコンの作成
                const iconDiv = document.createElement('div');
                iconDiv.className = `message-icon ${type}`;

                if (type === 'user') {
                    iconDiv.textContent = '👤';
                } else if (type === 'assistant') {
                    const iconImg = document.createElement('img');
                    iconImg.src = '/images/icon_ai.png';
                    iconImg.alt = 'AI';
                    iconImg.onerror = function() {
                        // 画像が見つからない場合のフォールバック
                        iconDiv.innerHTML = '🤖';
                    };
                    iconDiv.appendChild(iconImg);
                } else if (type === 'error') {
                    iconDiv.textContent = '⚠️';
                }

                // メッセージの作成
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;

                const contentDiv = document.createElement('div');
                contentDiv.innerHTML = this.formatContent(content);
                messageDiv.appendChild(contentDiv);

                if (relatedUnits && relatedUnits.length > 0) {
                    const unitsDiv = document.createElement('div');
                    unitsDiv.className = 'related-units';
                    unitsDiv.textContent = `関連単元: ${relatedUnits.join(', ')}`;
                    messageDiv.appendChild(unitsDiv);
                }

                // 要素を組み立て
                messageContainer.appendChild(iconDiv);
                messageContainer.appendChild(messageDiv);

                this.messagesContainer.appendChild(messageContainer);
                this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
            }

            formatContent(content) {
                content = content.replace(/\n/g, '<br>');

                content = content.replace(/```(.*?)```/gs, '<pre>$1</pre>');
                content = content.replace(/`(.*?)`/g, '<code style="background-color:rgba(0,0,0,0.1);padding:2px 4px;border-radius:3px;">$1</code>');

                return content;
            }

            setLoading(loading) {
                this.sendBtn.disabled = loading;
                this.questionInput.disabled = loading;
                this.refreshBtn.disabled = loading;

                if (loading) {
                    // ローディングメッセージを吹き出し形式で表示
                    const loadingContainer = document.createElement('div');
                    loadingContainer.className = 'message-container assistant';
                    loadingContainer.id = 'loading-message';

                    const iconDiv = document.createElement('div');
                    iconDiv.className = 'message-icon assistant';
                    const iconImg = document.createElement('img');
                    iconImg.src = '/images/icon_ai.png';
                    iconImg.alt = 'AI';
                    iconImg.onerror = function() {
                        iconDiv.innerHTML = '🤖';
                    };
                    iconDiv.appendChild(iconImg);

                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message assistant';
                    messageDiv.innerHTML = '<span style="opacity: 0.7;">回答を生成中...</span>';

                    loadingContainer.appendChild(iconDiv);
                    loadingContainer.appendChild(messageDiv);

                    this.messagesContainer.appendChild(loadingContainer);
                    this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
                } else {
                    const loadingDiv = document.getElementById('loading-message');
                    if (loadingDiv) {
                        loadingDiv.remove();
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new ChatBot();
        });
    </script>
</body>
</html>