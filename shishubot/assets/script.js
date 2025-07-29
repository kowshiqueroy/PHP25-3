document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatBox = document.querySelector('.chat-box');

    if (chatForm) {
        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const userMessage = chatInput.value.trim();
            if (userMessage === '') return;

            appendMessage('user', userMessage);
            chatInput.value = ''; // Clear input field

            try {
                const response = await fetch('../api/chatHandler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message=${encodeURIComponent(userMessage)}`,
                });

                const data = await response.json();

                if (data.success) {
                    appendMessage('bot', data.bot_response);
                } else {
                    appendMessage('bot', `Error: ${data.message}`);
                }
            } catch (error) {
                console.error('Error:', error);
                appendMessage('bot', 'An unexpected error occurred. Please try again.');
            }
        });
    }

    function appendMessage(sender, message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', `${sender}-message`);

        const timestamp = new Date().toLocaleTimeString();
        const timestampElement = document.createElement('span');
        timestampElement.classList.add('message-timestamp');
        timestampElement.textContent = timestamp;

        let messageContentElement;

        // Handle code blocks (simple markdown-like detection)
        if (message.startsWith('```') && message.endsWith('```')) {
            const codeContent = message.substring(3, message.length - 3).trim();
            const preElement = document.createElement('pre');
            const codeElement = document.createElement('code');
            codeElement.textContent = codeContent;
            preElement.appendChild(codeElement);
            messageContentElement = preElement;
        } else {
            messageContentElement = document.createElement('span');
            messageContentElement.textContent = message;
        }

        messageElement.appendChild(messageContentElement);
        messageElement.appendChild(timestampElement);

        chatBox.appendChild(messageElement);
        chatBox.scrollTop = chatBox.scrollHeight; // Scroll to bottom
    }
});
