<?php
require_once '../conn.php';
require_once 'header.php';
?>
<style>
    .chat-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f8f9fa;
        transition: all .3s ease-in-out;
    }
    .chat-box {
        width: 90%;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translate(-50%, 0);
        transition: all .3s ease-in-out;
    }
    #chatlog {
        max-height: 600px;
        overflow-y: auto;
        padding: 10px;
        background: #e6e6e6;
        transition: all .3s ease-in-out;

        display: flex;
        flex-direction: column-reverse;
    
        
    

    }
    .input-group {
        display: flex;
        padding: 10px;
        border: 1px solid #ccc;
    }
    #message {
        flex-grow: 1;
        border-radius: 5px;
    }
    .btn-primary {
        margin-left: 20px;
    }
    @media (max-width: 768px) {
        .chat-container {
            height: auto;
        }
        .chat-box {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            border-radius: 0;
            transform: none;
        }
    }
</style>
<div class="chat-container">
    <div class="chat-box">
        <div id="chatlog" ><p style="text-align: center;">Messages are loading</p></div>
        <div class="input-group">
            <textarea id="message" class="form-control" rows="3"></textarea>
            <button class="btn btn-primary" onclick="document.getElementById('message').style.animation = 'pulse 0.5s ease-in-out'; sendMessage(); document.getElementById('message').focus(); return false;">
                <i class="fa fa-paper-plane"></i> Send
            </button>
            <style>
                @keyframes pulse {
                    0% {
                        transform: scale(1);
                        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
                    }
                    50% {
                        transform: scale(1.1);
                        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
                    }
                    100% {
                        transform: scale(1);
                        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
                    }
                }
            </style>
        </div>
    </div>
</div>

<script>
    function sendMessage() {
        var message = document.getElementById('message').value;
        document.getElementById('message').value = '';

        if(message.trim() !== '') {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "getchat.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('chatlog').innerHTML = xhr.responseText;
                    document.getElementById('message').value = '';
            document.getElementById('message').style.animation = 'pulse 0.5s ease-in-out';
                }
            };
            xhr.send("message=" + encodeURIComponent(message));
        }
    }

    function getChatLog() {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "getchat.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('chatlog').innerHTML = xhr.responseText;
                console.log(xhr.responseText);
            }
        };
        xhr.send("get=true");
    }
    getChatLog();
    setInterval(getChatLog, 5000);
</script>



</div>
    </div>

 
</body>
</html>