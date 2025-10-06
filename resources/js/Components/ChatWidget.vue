<template>
    <div class="chat-widget">
        <!-- Chat Toggle Button -->
        <button
            v-if="!isOpen"
            @click="toggleChat"
            class="chat-toggle-btn"
            :class="{ 'has-unread': unreadMessagesCount > 0 }"
        >
            <i class="fas fa-comments"></i>
            <span v-if="unreadMessagesCount > 0" class="unread-badge">{{ unreadMessagesCount }}</span>
        </button>

        <!-- Chat Window -->
        <Transition name="chat-slide">
            <div v-if="isOpen" class="chat-window shadow-lg">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="d-flex align-items-center">
                        <div class="chat-avatar me-3">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Support Boxibox</h6>
                            <small class="text-white-50">
                                <span class="status-indicator"></span>
                                En ligne
                            </small>
                        </div>
                    </div>
                    <button @click="toggleChat" class="btn-close btn-close-white"></button>
                </div>

                <!-- Chat Messages -->
                <div class="chat-messages" ref="messagesContainer">
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>

                    <template v-else>
                        <div v-if="messages.length === 0" class="empty-chat">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Commencez une conversation avec notre support</p>
                        </div>

                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="message"
                            :class="{ 'message-sent': message.from_client, 'message-received': !message.from_client }"
                        >
                            <div class="message-bubble">
                                <div class="message-text">{{ message.message }}</div>
                                <div class="message-time">
                                    {{ formatTime(message.created_at) }}
                                    <i v-if="message.from_client && message.lu" class="fas fa-check-double ms-1 text-primary"></i>
                                    <i v-else-if="message.from_client" class="fas fa-check ms-1"></i>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Quick Replies (optionnel) -->
                <div v-if="showQuickReplies && messages.length === 0" class="quick-replies">
                    <small class="text-muted d-block mb-2">Questions fréquentes :</small>
                    <button
                        v-for="(reply, index) in quickReplies"
                        :key="index"
                        @click="sendQuickReply(reply)"
                        class="btn btn-sm btn-outline-primary me-2 mb-2"
                    >
                        {{ reply }}
                    </button>
                </div>

                <!-- Chat Input -->
                <div class="chat-input">
                    <form @submit.prevent="sendMessage" class="d-flex">
                        <input
                            v-model="newMessage"
                            type="text"
                            class="form-control"
                            placeholder="Écrivez votre message..."
                            :disabled="sending"
                        />
                        <button
                            type="submit"
                            class="btn btn-primary ms-2"
                            :disabled="!newMessage.trim() || sending"
                        >
                            <i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export default {
    props: {
        initialMessages: {
            type: Array,
            default: () => []
        }
    },

    setup(props) {
        const isOpen = ref(false);
        const messages = ref(props.initialMessages || []);
        const newMessage = ref('');
        const loading = ref(false);
        const sending = ref(false);
        const messagesContainer = ref(null);
        const showQuickReplies = ref(true);

        const quickReplies = [
            'Mes codes d\'accès',
            'État de ma facture',
            'Modifier mon contrat',
            'Contacter un conseiller'
        ];

        const unreadMessagesCount = computed(() => {
            return messages.value.filter(m => !m.from_client && !m.lu).length;
        });

        const toggleChat = () => {
            isOpen.value = !isOpen.value;
            if (isOpen.value) {
                loadMessages();
                markAllAsRead();
            }
        };

        const loadMessages = async () => {
            loading.value = true;
            try {
                router.reload({
                    only: ['messages'],
                    preserveScroll: true,
                    onSuccess: (page) => {
                        if (page.props.messages) {
                            messages.value = page.props.messages;
                        }
                        scrollToBottom();
                    },
                    onFinish: () => {
                        loading.value = false;
                    }
                });
            } catch (error) {
                console.error('Erreur lors du chargement des messages:', error);
                loading.value = false;
            }
        };

        const sendMessage = async () => {
            if (!newMessage.value.trim() || sending.value) return;

            sending.value = true;
            const messageText = newMessage.value.trim();
            newMessage.value = '';

            try {
                router.post(route('client.chat.send'), {
                    message: messageText
                }, {
                    preserveScroll: true,
                    onSuccess: (page) => {
                        if (page.props.messages) {
                            messages.value = page.props.messages;
                        }
                        scrollToBottom();
                    },
                    onFinish: () => {
                        sending.value = false;
                    }
                });
            } catch (error) {
                console.error('Erreur lors de l\'envoi du message:', error);
                sending.value = false;
                newMessage.value = messageText; // Restaurer le message en cas d'erreur
            }
        };

        const sendQuickReply = (reply) => {
            newMessage.value = reply;
            sendMessage();
            showQuickReplies.value = false;
        };

        const formatTime = (dateString) => {
            const date = new Date(dateString);
            const now = new Date();
            const diffHours = Math.floor((now - date) / 3600000);

            if (diffHours < 24) {
                return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) +
                       ' ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            }
        };

        const scrollToBottom = () => {
            nextTick(() => {
                if (messagesContainer.value) {
                    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
                }
            });
        };

        const markAllAsRead = () => {
            if (unreadMessagesCount.value > 0) {
                router.post(route('client.chat.mark-all-read'), {}, {
                    preserveScroll: true,
                    onSuccess: (page) => {
                        messages.value.forEach(m => {
                            if (!m.from_client) {
                                m.lu = true;
                            }
                        });
                    }
                });
            }
        };

        // Auto-refresh messages quand le chat est ouvert
        watch(isOpen, (newValue) => {
            if (newValue) {
                const interval = setInterval(() => {
                    if (isOpen.value) {
                        loadMessages();
                    } else {
                        clearInterval(interval);
                    }
                }, 5000); // Rafraîchir toutes les 5 secondes
            }
        });

        onMounted(() => {
            // Vérifier les nouveaux messages toutes les 15 secondes
            setInterval(() => {
                if (!isOpen.value) {
                    router.reload({
                        only: ['messages'],
                        preserveScroll: true,
                        onSuccess: (page) => {
                            if (page.props.messages) {
                                messages.value = page.props.messages;
                            }
                        }
                    });
                }
            }, 15000);
        });

        return {
            isOpen,
            messages,
            newMessage,
            loading,
            sending,
            messagesContainer,
            showQuickReplies,
            quickReplies,
            unreadMessagesCount,
            toggleChat,
            sendMessage,
            sendQuickReply,
            formatTime
        };
    }
};
</script>

<style scoped>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.chat-toggle-btn.has-unread {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    border: 2px solid white;
}

.chat-window {
    width: 380px;
    height: 600px;
    background: white;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.status-indicator {
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
    animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    background: #f8f9fa;
}

.empty-chat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
}

.message {
    margin-bottom: 1rem;
    display: flex;
}

.message-sent {
    justify-content: flex-end;
}

.message-received {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 0.75rem 1rem;
    border-radius: 18px;
    word-wrap: break-word;
}

.message-sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-received .message-bubble {
    background: white;
    color: #212529;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-text {
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.7;
    text-align: right;
}

.quick-replies {
    padding: 0.75rem 1rem;
    border-top: 1px solid #dee2e6;
    background: white;
}

.chat-input {
    padding: 1rem;
    background: white;
    border-top: 1px solid #dee2e6;
}

.chat-input .form-control {
    border-radius: 25px;
    border: 1px solid #dee2e6;
    padding: 0.6rem 1rem;
}

.chat-input .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.chat-input .btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Transitions */
.chat-slide-enter-active, .chat-slide-leave-active {
    transition: all 0.3s ease;
}

.chat-slide-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
}

.chat-slide-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
}

/* Scrollbar styling */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive */
@media (max-width: 576px) {
    .chat-window {
        width: calc(100vw - 40px);
        height: calc(100vh - 100px);
    }
}

/* Dark mode support */
.dark-mode .chat-window {
    background: #2b3035;
}

.dark-mode .chat-messages {
    background: #1a1d20;
}

.dark-mode .message-received .message-bubble {
    background: #343a40;
    color: #f8f9fa;
}

.dark-mode .chat-input {
    background: #2b3035;
    border-top-color: #495057;
}

.dark-mode .chat-input .form-control {
    background: #343a40;
    border-color: #495057;
    color: #f8f9fa;
}

.dark-mode .quick-replies {
    background: #2b3035;
    border-top-color: #495057;
}
</style>
