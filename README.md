# プログラミング学習用チャットボット

## 概要

このプロジェクトは、プログラミング学習者向けのチャットボットです。
学習者の質問に対して直接的な解答ではなく、自分で答えを導き出せるようなヒントやアドバイスを提供します。

## 技術スタック

- **バックエンド**: Laravel v11
- **フロントエンド**: Vanilla JavaScript + Blade Template
- **AI API**: OpenAI API (GPT-3.5-turbo)
- **データベース**: MySQL

## 機能

### 1. 質問応答機能
- 学習者の質問を受け取り、関連するカリキュラム内容を検索
- OpenAI APIを使用してヒント形式の回答を生成
- 直接的な解答ではなく、学習者が自分で考えられるようなアドバイスを提供

### 2. カリキュラム管理
- テキストファイルベースのカリキュラム管理
- キーワード検索による関連コンテンツの自動抽出
- 複数単元にまたがる内容の統合

### 3. シンプルなUI
- チャット形式の直感的なインターフェース
- レスポンシブデザイン
- リアルタイムでの質問・回答

## セットアップ

### 1. 環境準備
```bash
# Composerで依存関係をインストール
composer install

# アプリケーションキーを生成
php artisan key:generate

```

### 2. 環境変数の設定
`.env`ファイルを編集して以下を設定：

```env
# OpenAI API設定
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7
```
- 必ず、Open AIのAPIを作成してください。
- 従量課金なので、使えば使うだけコストがかかります。
- $5から入金ができて、Suicaみたいにチャージして使えます。

### 3. サーバー起動
```bash
php artisan serve
```

http://localhost:8000 でアクセス可能

## ディレクトリ構造

```
├── app/
│   ├── Http/Controllers/
│   │   └── ChatController.php      # チャットAPI制御
│   └── Services/
│       ├── OpenAiService.php       # OpenAI API連携
│       └── CurriculumService.php   # カリキュラム管理
├── config/
│   └── openai.php                  # OpenAI設定
├── resources/views/
│   └── chat/
│       └── index.blade.php         # チャットUI
├── routes/
│   └── web.php                     # ルート定義
└── storage/
    └── curriculum/                 # カリキュラムファイル
        ├── PHP基礎.txt
        ├── JavaScript基礎.txt
        └── Laravel基礎.txt
```

## 使用方法

1. OpenAI APIキーを`.env`ファイルに設定
2. `php artisan serve`でサーバーを起動
3. ブラウザで http://localhost:8000 にアクセス
4. 質問を入力してヒントを受け取る

## 注意事項

- OpenAI APIの使用には料金が発生します
- APIキーは適切に管理してください
- 本プロジェクトは学習目的のプロトタイプです
