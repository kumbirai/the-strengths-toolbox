# Custom AI Provider Setup Guide

This guide explains how to configure the chatbot to use a custom non-OpenAI AI model.

## Overview

The chatbot now supports multiple AI providers through an abstraction layer. You can switch between OpenAI and custom AI providers by changing a configuration setting.

## Quick Start

### 1. Set the AI Provider

In your `.env` file, set:

```env
CHATBOT_AI_PROVIDER=custom
```

### 2. Configure Custom AI Settings

Add these settings to your `.env` file:

```env
# Required
CUSTOM_AI_API_URL=https://your-api-endpoint.com/v1
CUSTOM_AI_MODEL=your-model-name

# Optional
CUSTOM_AI_API_KEY=your-api-key
CUSTOM_AI_MAX_TOKENS=500
CUSTOM_AI_TEMPERATURE=0.7
CUSTOM_AI_TIMEOUT=30
CUSTOM_AI_ENDPOINT=/chat/completions
CUSTOM_AI_REQUEST_FORMAT=openai
CUSTOM_AI_AUTH_HEADER=Authorization
CUSTOM_AI_AUTH_PREFIX=Bearer 
```

## Request Formats

The chatbot supports different request/response formats:

### OpenAI Format (Default)

If your API uses OpenAI-compatible format:

```json
{
  "model": "model-name",
  "messages": [
    {"role": "system", "content": "..."},
    {"role": "user", "content": "..."}
  ],
  "max_tokens": 500,
  "temperature": 0.7
}
```

Response format:
```json
{
  "choices": [{
    "message": {
      "content": "response text"
    }
  }],
  "usage": {
    "total_tokens": 100
  }
}
```

### Anthropic Format

For Anthropic Claude-compatible APIs:

```env
CUSTOM_AI_REQUEST_FORMAT=anthropic
```

### Custom Format

For completely custom formats, you can provide custom payload builders and response parsers in your service provider.

## Examples

### Example 1: OpenAI-Compatible API

```env
CHATBOT_AI_PROVIDER=custom
CUSTOM_AI_API_URL=https://api.example.com/v1
CUSTOM_AI_MODEL=gpt-4
CUSTOM_AI_API_KEY=sk-...
CUSTOM_AI_REQUEST_FORMAT=openai
```

### Example 2: Self-Hosted Model

```env
CHATBOT_AI_PROVIDER=custom
CUSTOM_AI_API_URL=http://localhost:8000/v1
CUSTOM_AI_MODEL=llama-2
CUSTOM_AI_REQUEST_FORMAT=openai
```

### Example 3: API with Custom Auth

```env
CHATBOT_AI_PROVIDER=custom
CUSTOM_AI_API_URL=https://api.example.com
CUSTOM_AI_MODEL=model-name
CUSTOM_AI_API_KEY=your-key
CUSTOM_AI_AUTH_HEADER=X-API-Key
CUSTOM_AI_AUTH_PREFIX=
```

## Switching Back to OpenAI

To switch back to OpenAI:

```env
CHATBOT_AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-3.5-turbo
```

## Testing

After configuration, test the chatbot:

```bash
php artisan tinker
```

```php
$service = app(\App\Services\ChatbotService::class);
$conversation = $service->createConversation('test_' . time());
$response = $service->sendMessage($conversation->id, 'Hello');
dd($response);
```

## Troubleshooting

### Error: "Custom AI API URL is not configured"

Make sure `CUSTOM_AI_API_URL` is set in your `.env` file.

### Error: "Invalid response from Custom AI API"

Check that your API response format matches the expected format for your `CUSTOM_AI_REQUEST_FORMAT` setting.

### Error: "Custom AI API client error"

Check your API endpoint, authentication, and that the model name is correct.

## Advanced Configuration

For completely custom request/response handling, you can extend the `CustomAIClient` class or modify the payload builder and response parser in your service provider.
