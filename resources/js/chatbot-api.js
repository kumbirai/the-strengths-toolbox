class ChatbotAPI {
    constructor() {
        this.baseUrl = '/api/chatbot';
        this.csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        this.maxRetries = 3;
        this.retryDelay = 1000; // 1 second
    }

    /**
     * Send message to chatbot
     */
    async sendMessage(message, conversationId = null, sessionId = null) {
        const url = `${this.baseUrl}/message`;
        const body = {
            message: message,
            conversation_id: conversationId,
            session_id: sessionId,
        };

        return this.request(url, 'POST', body);
    }

    /**
     * Get conversation
     */
    async getConversation(conversationId) {
        const url = `${this.baseUrl}/conversation/${conversationId}`;
        return this.request(url, 'GET');
    }

    /**
     * Get conversation with pagination
     */
    async getConversationPaginated(conversationId, page = 1, perPage = 20) {
        const url = `${this.baseUrl}/conversation/${conversationId}/messages`;
        const params = new URLSearchParams({ page, per_page: perPage });
        return this.request(`${url}?${params}`, 'GET');
    }

    /**
     * Get conversation summary
     */
    async getConversationSummary(conversationId) {
        const url = `${this.baseUrl}/conversation/${conversationId}/summary`;
        return this.request(url, 'GET');
    }

    /**
     * Make API request with retry logic
     */
    async request(url, method = 'GET', body = null, retryCount = 0) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
            },
        };

        if (body && method !== 'GET') {
            options.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(url, options);

            // Handle non-OK responses
            if (!response.ok) {
                const errorData = await this.parseErrorResponse(response);
                
                // Retry on server errors (5xx)
                if (response.status >= 500 && retryCount < this.maxRetries) {
                    await this.delay(this.retryDelay * (retryCount + 1));
                    return this.request(url, method, body, retryCount + 1);
                }

                throw new APIError(errorData.message || 'Request failed', response.status, errorData);
            }

            return await response.json();
        } catch (error) {
            // Retry on network errors
            if (error instanceof TypeError && retryCount < this.maxRetries) {
                await this.delay(this.retryDelay * (retryCount + 1));
                return this.request(url, method, body, retryCount + 1);
            }

            throw error;
        }
    }

    /**
     * Parse error response
     */
    async parseErrorResponse(response) {
        try {
            return await response.json();
        } catch (e) {
            return {
                message: `HTTP ${response.status}: ${response.statusText}`,
                error_code: 'HTTP_ERROR',
            };
        }
    }

    /**
     * Delay helper
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Check API health
     */
    async checkHealth() {
        try {
            const response = await fetch(`${this.baseUrl}/health`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });
            return response.ok;
        } catch (error) {
            return false;
        }
    }
}

/**
 * API Error class
 */
class APIError extends Error {
    constructor(message, status, data = {}) {
        super(message);
        this.name = 'APIError';
        this.status = status;
        this.data = data;
    }
}

// Export for use in widget
window.ChatbotAPI = ChatbotAPI;
window.APIError = APIError;
