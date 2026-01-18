<div x-data="chatbotWidget()" class="fixed bottom-4 right-4 z-50">
{{-- Chat Window --}}
<div 
    x-show="isOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    style="display: none;"
>
    <div class="bg-white rounded-lg shadow-2xl w-96 h-[600px] flex flex-col border border-gray-200">
        {{-- Header --}}
        <div class="bg-primary-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="font-semibold text-lg">Chat with us</h3>
                    <p class="text-sm text-primary-100">We're here to help!</p>
                </div>
                <div class="flex items-center gap-1 ml-2">
                    <span x-show="connectionStatus === 'connected'" 
                          class="w-2 h-2 bg-green-300 rounded-full animate-pulse"
                          title="Connected"></span>
                    <span x-show="connectionStatus === 'disconnected'" 
                          class="w-2 h-2 bg-yellow-300 rounded-full"
                          title="Disconnected"></span>
                    <span x-show="connectionStatus === 'error'" 
                          class="w-2 h-2 bg-red-300 rounded-full"
                          title="Error"></span>
                </div>
            </div>
            <button 
                @click="toggleChat" 
                class="text-white hover:text-primary-200 transition-colors p-1 rounded hover:bg-primary-700"
                aria-label="Close chat"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        {{-- Messages Container --}}
        <div 
            class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
            x-ref="messagesContainer"
        >
            {{-- Welcome Message --}}
            <div x-show="messages.length === 0" class="text-center text-gray-500 py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm">Start a conversation by typing a message below.</p>
            </div>

            {{-- Messages List --}}
            <template x-for="(message, index) in messages" :key="message.id">
                <div 
                    x-show="true"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    :class="message.role === 'user' ? 'ml-auto flex justify-end' : 'mr-auto flex justify-start'"
                    class="max-w-[80%] mb-4"
                    :style="`animation-delay: ${index * 50}ms`"
                >
                    <div 
                        :class="message.role === 'user' 
                            ? 'bg-primary-600 text-white rounded-lg rounded-tr-none' 
                            : message.isError
                                ? 'bg-red-50 border border-red-200 text-red-800 rounded-lg rounded-tl-none'
                                : 'bg-white text-gray-800 border border-gray-200 rounded-lg rounded-tl-none'"
                        class="p-3 shadow-sm"
                    >
                        <p class="text-sm whitespace-pre-wrap" x-text="message.content"></p>
                        <span 
                            :class="message.isError ? 'text-red-600' : 'opacity-70'"
                            class="text-xs mt-1 block"
                            x-text="formatTime(message.created_at)"
                        ></span>
                    </div>
                </div>
            </template>

            {{-- Loading Indicator --}}
            <div x-show="isLoading" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="flex justify-start mb-4"
            >
                <div class="bg-white border border-gray-200 rounded-lg rounded-tl-none p-4 shadow-sm max-w-[80%]">
                    <div class="flex items-center space-x-2">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" 
                                 style="animation-delay: 0ms; animation-duration: 1.4s;"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" 
                                 style="animation-delay: 200ms; animation-duration: 1.4s;"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" 
                                 style="animation-delay: 400ms; animation-duration: 1.4s;"></div>
                        </div>
                        <span class="text-xs text-gray-500 ml-2">Thinking...</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Error Message --}}
        <div x-show="errorMessage" 
             x-transition
             class="mx-4 mt-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
        >
            <div class="flex justify-between items-start">
                <p x-text="errorMessage"></p>
                <button @click="clearError()" class="text-red-500 hover:text-red-700 ml-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Input Form --}}
        <div class="p-4 border-t border-gray-200 bg-white rounded-b-lg">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input 
                    type="text"
                    x-model="inputMessage"
                    @keydown.enter.prevent="sendMessage"
                    placeholder="Type your message..."
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
                    :disabled="isLoading"
                    autocomplete="off"
                >
                <button 
                    type="submit"
                    :disabled="isLoading || !inputMessage.trim()"
                    class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center min-w-[60px]"
                    :class="isLoading ? 'cursor-wait' : ''"
                >
                    <span x-show="!isLoading">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </span>
                    <span x-show="isLoading" class="flex items-center">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Toggle Button --}}
<button 
    @click="toggleChat"
    x-show="!isOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="bg-primary-600 text-white rounded-full p-4 shadow-lg hover:bg-primary-700 transition-colors hover:scale-110"
    aria-label="Open chat"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
    </svg>
    <span x-show="unreadCount > 0" 
          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
          x-text="unreadCount"
    ></span>
</button>
</div>

<script>
// Make chatbotWidget available globally for Alpine.js
window.chatbotWidget = function chatbotWidget() {
    return {
        isOpen: false,
        messages: [],
        inputMessage: '',
        isLoading: false,
        conversationId: null,
        sessionId: null,
        unreadCount: 0,
        errorMessage: null,
        connectionStatus: 'connected',
        api: null,
        
        init() {
            // Initialize API service if available
            if (window.ChatbotAPI) {
                this.api = new ChatbotAPI();
            }
            
            // Get session ID
            this.sessionId = this.getSessionId();
            
            // Generate or retrieve conversation ID
            this.conversationId = this.getConversationId();
            
            // Load conversation history if exists
            if (this.conversationId && this.conversationId !== 'null') {
                this.loadConversationHistory();
            }
            
            // Listen for visibility changes to update unread count
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && !this.isOpen) {
                    // Check for new messages when tab becomes visible
                    this.checkForNewMessages();
                }
            });
            
            // Watch for focus to mark as read
            this.$watch('isOpen', (value) => {
                if (value) {
                    this.unreadCount = 0;
                    this.errorMessage = null;
                }
            });
        },
        
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.scrollToBottom();
                this.unreadCount = 0;
                this.errorMessage = null;
            }
        },
        
        async sendMessage() {
            if (!this.inputMessage.trim() || this.isLoading) return;
            
            const userMessage = this.inputMessage.trim();
            this.inputMessage = '';
            this.errorMessage = null;
            
            // Add user message to UI immediately
            const userMessageObj = {
                id: 'temp_' + Date.now(),
                role: 'user',
                content: userMessage,
                created_at: new Date().toISOString(),
            };
            this.messages.push(userMessageObj);
            
            this.scrollToBottom();
            this.isLoading = true;
            this.connectionStatus = 'connected';
            
            try {
                let response;
                
                if (this.api) {
                    // Use API service if available
                    response = await this.api.sendMessage(
                        userMessage,
                        this.conversationId,
                        this.sessionId
                    );
                } else {
                    // Fallback to direct fetch
                    response = await fetch('/api/chatbot/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message: userMessage,
                            conversation_id: this.conversationId,
                            session_id: this.sessionId,
                        }),
                    });
                    
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                    }
                    
                    response = await response.json();
                }
                
                if (response.success) {
                    // Remove temp message and add real one
                    this.messages = this.messages.filter(m => m.id !== userMessageObj.id);
                    
                    // Add confirmed user message
                    this.messages.push({
                        id: response.user_message_id || Date.now(),
                        role: 'user',
                        content: userMessage,
                        created_at: new Date().toISOString(),
                    });
                    
                    // Add AI response
                    this.messages.push({
                        id: response.ai_message_id || Date.now() + 1,
                        role: 'assistant',
                        content: response.message,
                        created_at: new Date().toISOString(),
                    });
                    
                    // Update conversation ID
                    if (response.conversation_id) {
                        this.conversationId = response.conversation_id;
                        this.saveConversationId(response.conversation_id);
                    }
                    
                    // Handle rate limiting
                    if (response.rate_limit) {
                        this.handleRateLimit(response.rate_limit);
                    }
                } else {
                    // Handle error response
                    this.handleErrorResponse(response, userMessageObj);
                }
            } catch (error) {
                console.error('Chatbot error:', error);
                this.handleError(error, userMessageObj);
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },
        
        handleErrorResponse(response, userMessageObj) {
            // Remove temp message
            this.messages = this.messages.filter(m => m.id !== userMessageObj.id);
            
            // Add error message
            this.messages.push({
                id: Date.now(),
                role: 'assistant',
                content: response.message || 'Sorry, something went wrong.',
                created_at: new Date().toISOString(),
                isError: true,
            });
            
            // Show error message
            if (response.error_code === 'CHATBOT_RATE_LIMIT') {
                this.errorMessage = 'Rate limit exceeded. Please wait before sending another message.';
            } else {
                this.errorMessage = response.message || 'An error occurred. Please try again.';
            }
            
            // Increment unread if widget is closed
            if (!this.isOpen) {
                this.unreadCount++;
            }
        },
        
        handleError(error, userMessageObj) {
            // Remove temp message
            this.messages = this.messages.filter(m => m.id !== userMessageObj.id);
            
            // Add error message
            this.messages.push({
                id: Date.now(),
                role: 'assistant',
                content: 'Sorry, I encountered an error. Please try again.',
                created_at: new Date().toISOString(),
                isError: true,
            });
            
            this.errorMessage = 'Connection error. Please check your internet connection and try again.';
            this.connectionStatus = 'error';
            
            // Increment unread if widget is closed
            if (!this.isOpen) {
                this.unreadCount++;
            }
        },
        
        handleRateLimit(rateLimit) {
            if (rateLimit.remaining === 0) {
                const resetDate = new Date(rateLimit.reset_at);
                const secondsUntilReset = Math.ceil((resetDate - new Date()) / 1000);
                
                this.errorMessage = `Rate limit exceeded. Please wait ${secondsUntilReset} seconds before sending another message.`;
            }
        },
        
        async loadConversationHistory() {
            if (!this.conversationId || this.conversationId === 'null') return;
            
            try {
                let data;
                
                if (this.api) {
                    data = await this.api.getConversation(this.conversationId);
                } else {
                    const response = await fetch(`/api/chatbot/conversation/${this.conversationId}`);
                    
                    if (!response.ok) {
                        throw new Error('Failed to load conversation');
                    }
                    
                    data = await response.json();
                }
                
                if (data.success && data.messages && data.messages.length > 0) {
                    this.messages = data.messages.map(msg => ({
                        id: msg.id,
                        role: msg.role,
                        content: msg.message,
                        created_at: msg.created_at,
                    }));
                    this.scrollToBottom();
                }
            } catch (error) {
                console.error('Failed to load conversation history:', error);
                // Don't show error to user, just start fresh
            }
        },
        
        async checkForNewMessages() {
            if (!this.conversationId || this.isOpen) return;
            
            try {
                let data;
                
                if (this.api) {
                    data = await this.api.getConversation(this.conversationId);
                } else {
                    const response = await fetch(`/api/chatbot/conversation/${this.conversationId}`);
                    if (!response.ok) return;
                    data = await response.json();
                }
                
                if (data.success && data.messages) {
                    const currentMessageCount = this.messages.length;
                    const newMessageCount = data.messages.length;
                    
                    if (newMessageCount > currentMessageCount) {
                        this.unreadCount = newMessageCount - currentMessageCount;
                    }
                }
            } catch (error) {
                // Silently fail
                console.error('Failed to check for new messages:', error);
            }
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        
        getConversationId() {
            const stored = localStorage.getItem('chatbot_conversation_id');
            return stored && stored !== 'null' ? stored : null;
        },
        
        saveConversationId(id) {
            if (id) {
                localStorage.setItem('chatbot_conversation_id', id);
            }
        },
        
        getSessionId() {
            const metaSession = document.querySelector('meta[name=session-id]');
            return metaSession ? metaSession.content : 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },
        
        clearError() {
            this.errorMessage = null;
        },
    };
};
</script>
