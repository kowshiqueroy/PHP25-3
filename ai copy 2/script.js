document.addEventListener('DOMContentLoaded', () => {
    const output = document.getElementById('output');
    const input = document.getElementById('input');
    const usernameDisplay = document.getElementById('username-display');
    const logoutButton = document.getElementById('logout-button');

    const printToTerminal = (text, className) => {
        const p = document.createElement('p');
        p.textContent = text;
        if (className) {
            p.className = className;
        }
        output.appendChild(p);
        output.scrollTop = output.scrollHeight;
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

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const userInput = input.value.trim();
            if (userInput) {
                printToTerminal(`You: ${userInput}`);
                
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