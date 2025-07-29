document.addEventListener('DOMContentLoaded', () => {
    const output = document.getElementById('output');
    const input = document.getElementById('input');
    const usernameDisplay = document.getElementById('username-display');
    const logoutButton = document.getElementById('logout-button');
    const newChatButton = document.getElementById('new-chat-button');
    const helpButton = document.getElementById('help-button');

    const printToTerminal = (text, className) => {
        const p = document.createElement('p');
        p.textContent = text;
        if (className) {
            p.className = className;
        }
        output.appendChild(p);
        output.scrollTop = output.scrollHeight;
    };

    const startNewConversation = () => {
        output.innerHTML = ''; // Clear chat history
        printToTerminal("Simple AI: Hello! I'm ready to chat.", 'bot-response');
        input.value = '';
        input.focus();
        // Optionally, send a request to the backend to clear session/memory for this user
        fetch('clear_conversation.php', { method: 'POST' })
            .then(response => response.text())
            .then(data => console.log(data))
            .catch(error => console.error('Error clearing conversation:', error));
    };

    // Check login status and display username
    fetch('check_login.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                usernameDisplay.textContent = `Logged in as: ${data.username}`;
                printToTerminal("Simple AI: Hello! I'm ready to chat.", 'bot-response');
            } else {
                window.location.href = 'login.php';
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
            window.location.href = 'login.php';
        });

    logoutButton.addEventListener('click', () => {
        fetch('logout.php')
            .then(() => {
                window.location.href = 'login.php';
            })
            .catch(error => {
                console.error('Error logging out:', error);
            });
    });

    newChatButton.addEventListener('click', startNewConversation);

    helpButton.addEventListener('click', () => {
        printToTerminal("", ''); // Add an empty line for spacing
        printToTerminal("--- Simple AI Help --- ", 'bot-response');
        printToTerminal("\nTo ask a question: Just type your question (e.g., 'Who is Albert Einstein?').", '');
        printToTerminal("\nTo perform a calculation: Type a mathematical expression (e.g., '5+8*2').", '');
        printToTerminal("\nTo teach me something: Use the format 'If I say \'your phrase\', you should say \'my response\'.", '');
        printToTerminal("\nTo tell a story: Type 'tell me a story' or 'write a story'.", '');
        printToTerminal("\nTo set data: If I ask 'Do you want to set any data related to this?', respond 'yes' then provide the data.", '');
        printToTerminal("\nTo get data: Ask 'what is my [memory key]?' (e.g., 'what is my name?').", '');
        printToTerminal("\nTo start a new conversation: Click the 'New Chat' button.", '');
        printToTerminal("\nTo exit: Click the 'Exit' button.", '');
        printToTerminal("\n---------------------", 'bot-response');
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const userInput = input.value.trim();
            if (userInput) {
                printToTerminal(`You: ${userInput}`, 'user-input');
                
                fetch('chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest' // Indicate AJAX request
                    },
                    body: `input=${encodeURIComponent(userInput)}`
                })
                .then(response => {
                    if (response.status === 401) {
                        window.location.href = 'login.php'; // Redirect if unauthorized
                        return Promise.reject('Unauthorized');
                    }
                    return response.text();
                })
                .then(data => {
                    printToTerminal(`Simple AI: ${data}`, 'bot-response');
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (error !== 'Unauthorized') {
                        printToTerminal('Simple AI: Sorry, something went wrong.', 'bot-response');
                    }
                });

                input.value = '';
            }
        }
    });
});