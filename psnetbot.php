<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* AI Chatbox Container */
        #ai-assistant {position: fixed;bottom: 20px;right: 20px;width: 350px;height: 500px;background: #fff;border-radius: 10px;box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);display: none;z-index: 1000;}
        .dark-theme #ai-assistant {background: #2a2a2a;}
        /* Chat Header */
        #ai-assistant .chat-header {background: #1e90ff;color: #fff;padding: 10px;border-radius: 10px 10px 0 0;display: flex;justify-content: space-between;align-items: center;}
        .dark-theme #ai-assistant .chat-header {background: #1e4d46;}
        #ai-assistant .chat-header span {cursor: pointer;}
        /* Chat Body (for Chatbase iframe) */
        #ai-assistant #chat-body {width: 100%;height: calc(100% - 60px);padding: 0;overflow: auto;}
        #ai-assistant #chat-body iframe {width: 100%;height: 100%;border: none;}
        /* Control Buttons */
        .control-buttons {position: fixed;bottom: 90px;right: 20px;display: flex;flex-direction: column;gap: 10px;z-index: 1000;}
        #ai-toggle,
        #theme-toggle {background: rgb(5, 224, 41);color: #fff;border: none;border-radius: 50%;width: 50px;height: 50px;font-size: 20px;cursor: pointer;transition: transform 0.3s;display: flex;align-items: center;justify-content: center;padding: 0;}
        #ai-toggle img {display: block;width: 30px;height: 30px;}
        #ai-toggle:hover,
        #theme-toggle:hover {transform: scale(1.5);}
    </style>
</head>
<body>
    <!-- AI Chatbox -->
    <div id="ai-assistant">
        <div class="chat-header"><span>Chatbase AI Assistant</span><span class="close-btn">×</span>
        </div>
        <div id="chat-body"><!-- GANTI 'YOUR_BOT_ID' DENGAN ID BOT ANDA DARI CHATBASE --><iframe src="https://www.chatbase.co/chatbot-iframe/5VqCHmqo7q_XruFfyjvlp"    width="100%"    style="height: 100%; min-height: 700px"    frameborder="0"></iframe>
        </div>
    </div>

    <!-- JavaScript for toggling chatbox and theme -->
    <script>
    (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="5VqCHmqo7q_XruFfyjvlp";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    
    const crypto = require('crypto');

    const secret = 'rsppZ6hpCv9BSDRQsvHxtQ48y1fXFLo2Ug'; // Your verification secret key
    const userId = current_user.id // A string UUID to identify your user

    const hash = crypto.createHmac('sha256', secret).update(userId).digest('hex');
    </script>
</body>
</html>