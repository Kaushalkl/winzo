<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Ludo</title>
    <link rel="icon" href="ludoFacIcon.png" type="image/png">
    <meta name="description" content="">
    <meta content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    name="viewport">

    <!--<link rel="stylesheet" href="index.css">-->
     <!--<link rel="stylesheet" href="music.css">-->

    <style>
        /* --- MUTE BUTTON --- */
#muteBtn {
    position: fixed; /* fixed relative to viewport */
    top: 15px;
    right: 15px;
    background: transparent; /* subtle glass effect */
    border: none;
    border-radius: 50%;
    font-size: 1.5rem;
    width: 50px;
    height: 50px;
    color: #fff;
    box-shadow: 0 0 12px rgba(255,255,255,0.4);
    cursor: pointer;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    transition: transform 0.2s ease,  0.2s ease, box-shadow 0.2s ease;

}

#muteBtn:hover {
    transform: scale(1.1);
    background-color:  transparent;
    box-shadow: 0 0 20px #ff4d4d, 0 0 40px #ff1a1a;
}

/* --- MOBILE RESPONSIVE --- */
@media (max-width: 768px) {
    #muteBtn {
        width: 60px;
        height: 60px;
        font-size: 2rem;
        top: 10px;
        right: 10px;
    }
}

@media (max-width: 480px) {
    #muteBtn {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        top: 10px;
        right: 10px;
    }
}

        @import url('https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;600;700;800;900&display=swap');

/* --- GLOBAL RESET & BASE --- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Urbanist", sans-serif;
}

html, body {
    height: 100%;
    width: 100%;
    background: url(Bg.png) no-repeat center center/cover;
    overflow-x: hidden;
    overflow-y: auto;
}

/* 🌌 PARTICLE BACKGROUND */
.particle-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(#151e2f, #0d1018);
    z-index: -2;
    overflow: hidden;
}

.particle-bg span {
    position: absolute;
    border-radius: 50%;
    animation: floatParticles 10s linear infinite;
}

@keyframes floatParticles {
    0% { transform: translateY(0) translateX(0); opacity: 0.8; }
    50% { transform: translateY(-300px) translateX(100px); opacity: 0.3; }
    100% { transform: translateY(-600px) translateX(0); opacity: 0.8; }
}

/* --- MENU CONTAINER (Thoda niche) --- */
.menuContainer {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start; /* start se align */
    min-height: 100vh;
    padding: 120px 20px 20px; /* top padding thoda niche */
    gap: 20px;
    z-index: 1;
}

/* --- MENU BOX --- */
.menu {
    width: 95%;
    max-width: 400px;
    padding: 20px;
    text-align: center;
    border: 2px solid #ffffff3d;
    border-radius: 16px;
    background: url("Bg.png");
    backdrop-filter: blur(8px);
    box-shadow: 0 8px 24px rgba(214, 20, 20, 0.4);
}

/* MENU TITLE */
.menuTitle {
    color: rgb(220, 25, 25);
    font-size: 22px;
    margin-bottom: 15px;
    position: relative;
}
.menuTitle::after {
    content: "";
    display: block;
    height: 3px;
    width: 50%;
    margin: 8px auto 0;
    background: #8a57ea;
    box-shadow: 0 0 10px #8a57ea;
    border-radius: 4px;
}

/* PLAYER SELECTION */
.choosePlayers {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin: 15px 0;
}

.players {
    cursor: pointer;
    border-radius: 50%;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
}

.players img {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.players:hover img {
    transform: scale(1.1);
    box-shadow: 0 0 12px rgba(255, 255, 255, 0.6);
}

/* SELECTED PLAYER GLOW */
.redPlayer.selected img { box-shadow: 0 0 18px 4px #ff0000; transform: scale(1.15); }
.bluePlayer.selected img { box-shadow: 0 0 18px 4px #0000ff; transform: scale(1.15); }
.greenPlayer.selected img { box-shadow: 0 0 18px 4px #00ff00; transform: scale(1.15); }
.yellowPlayer.selected img { box-shadow: 0 0 18px 4px #ffff00; transform: scale(1.15); }

/* --- PLAY BUTTON --- */
.menu-btn {
    width: 80%;
    max-width: 220px;
    padding: 12px 0;
    font-size: 1.2rem;
    font-weight: bold;
    color: rgb(175, 155, 175);
    background: transparent;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
    animation: pulseButton 2s infinite;
}

.menu-btn:hover {
    transform: scale(1.15);
    box-shadow: 0 0 25px #ff4d4d, 0 0 50px #ff1a1a;
}

@keyframes pulseButton {
    0% { transform: scale(1); box-shadow: 0 0 18px #ff4d4d, 0 0 36px #ff1a1a; }
    50% { transform: scale(1.1); box-shadow: 0 0 25px #ff4d4d, 0 0 50px #ff1a1a; }
    100% { transform: scale(1); box-shadow: 0 0 18px #ff4d4d, 0 0 36px #ff1a1a; }
}

/* --- MAIN TITLE & SUBTITLE --- */
.my-3 b {
    color: #ff0000;
    font-size: 32px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    font-weight: bold;
    animation: titleBounce 2s ease-in-out infinite;
}

.my-3 b span {
    font-size: 32px;
    display: inline-block;
    animation: crownTilt 1.5s ease-in-out infinite;
    transform-origin: bottom center;
}

.font-monospace {
    color: #ff4500;
    font-size: 16px;
    text-align: center;
    margin-bottom: 15px;
    font-family: 'Courier New', monospace;
    animation: subtitlePulse 2s ease-in-out infinite alternate;
}

@keyframes titleBounce { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }
@keyframes crownTilt { 0%,50%,100% { transform: rotate(0deg); } 25% { rotate:-10deg; } 75% { rotate:10deg; } }
@keyframes subtitlePulse { 0%,100% { color:#ff4500; transform: scale(1); } 50% { color:#ff6347; transform: scale(1.05); } }

/* --- POPUP --- */
.popup {
    position: fixed;
    inset: 0;
    display: none;
    justify-content: center;
    align-items: center;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.popup-content {
    background: #fff;
    padding: 20px 25px;
    border-radius: 10px;
    text-align: center;
    font-weight: bold;
    animation: scaleUp 0.3s forwards;
}

@keyframes scaleUp { from { transform: scale(0.5); } to { transform: scale(1); } }

#closePopup {
    margin-top: 15px;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    background: #007bff;
    color: #fff;
    cursor: pointer;
}

/* --- RESPONSIVE MEDIA QUERIES --- */
@media (max-width: 768px) {
    .menuContainer { padding: 100px 20px 20px; }
    .menu { width: 90%; padding: 18px; }
    .players img { width: 50px; height: 50px; }
    .menu-btn { width: 90%; font-size: 1rem; padding: 10px 0; }
    .my-3 b, .my-3 b span { font-size: 26px; }
    .font-monospace { font-size: 14px; }
}

@media (max-width: 480px) {
    .menuContainer { padding: 80px 15px 20px; }
    .menu { width: 95%; padding: 15px; }
    .players img { width: 45px; height: 45px; }
    .menu-btn { width: 95%; font-size: 0.9rem; padding: 8px 0; }
    .my-3 b, .my-3 b span { font-size: 22px; }
    .font-monospace { font-size: 12px; }
}

        /* Top-left wallet button styling */
        .wallet-btn-top {
            position: fixed;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg,#ff416c,#ff4b2b);
            color: #000000ff;
            font-weight: 600;
            border: none;
            padding: 10px 15px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.2s ease;
        }
        .wallet-btn-top:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    
    <!-- Wallet Button Top-Left -->
    <button id="walletBtn" class="wallet-btn-top">
        Wallet: <span id="walletBalance">₹000000000.00</span>
    </button>

    <div class="menuContainer">
        <div class="container my-auto">
            <div class="row">

                <div class="col-sm-8 col-md-6 col-lg-5 col-xl-4 startMenu mx-auto text-center"> 
                    <div class="menu">
                        <h1 class="my-3 "><b>👑CLASSIC LUDO</b></h1>
                        <img class="dice" src="diceFront.png" alt="">
                        <h3 class="my-3 font-monospace">PLAYER SELECT</h3>
                        <div class="choosePlayers">
                            <div class="redPlayer players" id="redPlayer"><img src="red token.png" alt=""></div>
                            <div class="greenPlayer players" id="greenPlayer"><img src="green token.png" alt=""></div>
                            <div class="yellowPlayer players" id="yellowPlayer"><img src="yellow token.png" alt=""></div>
                            <div class="bluePlayer players" id="bluePlayer"><img src="blue token.png" alt=""></div>
                        </div>
                        <div class="starmeanu">
                            <!-- Normal Play Button -->
                            <button id="play" class="menu-btn">Play</button>

                            <!-- ✅ New Play with AI Button -->
                            <button id="playWithAI" class="menu-btn ai-btn">Play with AI</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Background Music -->
    <audio id="backgroundMusic" src="background-music.mp3" loop autoplay></audio>

    <script src="music.js" defer></script>
    <script src="index.js" defer></script>

    <script>
        // ===== Wallet Balance =====
        function updateWalletBalance() {
            fetch('wallet_action.php?action=balance')
            .then(res => res.json())
            .then(data => {
                if(data.balance !== undefined){
                    document.getElementById('walletBalance').innerText = '₹' + parseFloat(data.balance).toFixed(2);
                }
            })
            .catch(err => console.log('Wallet fetch error:', err));
        }

        // Call on page load
        updateWalletBalance();

        // Refresh every 10 seconds
        setInterval(updateWalletBalance, 10000);

        // Wallet button click redirects to dashboard
        document.getElementById('walletBtn').addEventListener('click', () => {
            window.location = 'index.php';
        });

        // ===== Music Control =====
        const music = document.getElementById('backgroundMusic');
        music.volume = 0.5; // optional: set initial volume
        music.play().catch(err => console.log('Autoplay prevented:', err));
    </script>
</body>
</html>
