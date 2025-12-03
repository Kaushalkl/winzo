//dom elemnts 
const redPlayer = document.querySelector(".redPlayer");
const greenPlayer = document.querySelector(".greenPlayer");
const yellowPlayer = document.querySelector(".yellowPlayer");
const bluePlayer = document.querySelector(".bluePlayer");
const playWithAI = document.querySelector("#playWithAI");
const play = document.querySelector("#play");
const menu = document.querySelector(".startMenu");
// Audios ..
const click = new Audio('mixkit-classic-click-1117.wav');
//others
let redPlaying = false;
let greenPlaying = false;
let yellowPlaying = false;
let bluePlaying = false;
let nPlaying = 0;
//selecting click events
redPlayer.addEventListener('click',slected);
greenPlayer.addEventListener('click',slected);
yellowPlayer.addEventListener('click',slected);
bluePlayer.addEventListener('click',slected);

//to start playing game 
play.addEventListener('click',startGame);

//to check if no of player is more than 2 
function canPlay(){
    if(nPlaying>=2){
        return true;
    }else{
        return false;
    }
}


// PLAY WITH AI BUTTON
// ===========================
playWithAI.addEventListener("click", () => {
    click.play();

    // Close menu animation
    menu.style.animation = "closing 0.5s linear";

    // Force all players ON (AI mode)
    redPlaying = greenPlaying = yellowPlaying = bluePlaying = true;
    window.redComputer = true;
    window.greenComputer = true;
    window.yellowComputer = true;
    window.blueComputer = true;
    nPlaying = 4;

    // Redirect to game with all players active
    setTimeout(() => {
        const aiUrl = `computer.html`;
        console.log("AI Mode Redirecting to:", aiUrl);
        window.location.href = aiUrl;
    }, 500);
});

// 🌟 Particle Effect with Ludo Colors
const particleBg = document.querySelector(".particle-bg");
const colors = ["#ff0000", "#0000ff", "#00ff00", "#ffff00", "#ffffff"];

for (let i = 0; i < 100; i++) {
    const particle = document.createElement("span");
    particle.style.left = Math.random() * 100 + "vw";
    particle.style.top = Math.random() * 100 + "vh";
    particle.style.width = particle.style.height = Math.random() * 3 + 1 + "px";
    particle.style.opacity = Math.random();
    particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
    particle.style.animationDuration = 5 + Math.random() * 15 + "s";
    particleBg.appendChild(particle);
}
//new addd
const players = document.querySelectorAll(".players");
const playBtn = document.getElementById("play");
let selectedPlayers = [];

players.forEach(player => {
    player.addEventListener("click", () => {
        player.classList.toggle("selected");
        const color = player.dataset.color;
        if (selectedPlayers.includes(color)) {
            selectedPlayers = selectedPlayers.filter(c => c !== color);
        } else {
            selectedPlayers.push(color);
        }
    });
});

playBtn.addEventListener("click", (e) => {
    e.preventDefault();
    if (selectedPlayers.length < 2) {

    } else {
        console.log("Selected Players:", selectedPlayers);
        window.location.href = "game.html";
    }
}); 
//toggle if already selected then deselected and vica versa
function slected(){
    click.play();
    let playerId=this.id;
    console.log(playerId);
    let player = document.querySelector(`#${playerId}`);
    if(player.classList.contains("selected")){
        nPlaying--;
        switch(playerId){
            case "redPlayer":
                redPlaying=false;
            break;
            case "bluePlayer":
                bluePlaying=false;
            break;
            case "greenPlayer":
                greenPlaying=false;
            break;
            case "yellowPlayer":
                yellowPlaying=false;
            break;
        }
        player.classList.remove("selected");
        console.log("player deseleted",player);

    }else{
        nPlaying++;
        switch(playerId){
            case "redPlayer":
                redPlaying=true;
            break;
            case "bluePlayer":
                bluePlaying=true;
            break;
            case "greenPlayer":
                greenPlaying=true;
            break;
            case "yellowPlayer":
                yellowPlaying=true;
            break;
        }
        player.classList.add("selected");
        console.log("player seleted",player);
    }
    console.log("n playing ",nPlaying);
}

//creating dynamic url with parameters to give data to game board
function generateUrl(){
    let dynamicUrl =`ludo.html?nPlaying=${nPlaying}&redPlaying=${redPlaying}&greenPlaying=${greenPlaying}&yellowPlaying=${yellowPlaying}&bluePlaying=${bluePlaying}`;
    return dynamicUrl;
}

function startGame(){
    if(canPlay()){
        click.play();
        menu.style.animation="closing 0.5s linear ";
      setTimeout(() => {
        console.log(" playing game ");
        let dynamicUrl=generateUrl();
        console.log(dynamicUrl);
        window.location.href = dynamicUrl;
      }, 500);
    }else{
        console.log(" select more then 1 player ");
    }
}
 //Dark Mode Toggle
<button class="dark-mode-toggle">🌙 Dark Mode</button>
// ===== DARK MODE =====
const darkToggle = document.querySelector('.dark-mode-toggle');
if(localStorage.getItem('dark-mode')==='true') {
  document.body.classList.add('dark-mode');
  darkToggle.textContent = '☀️ Light Mode';
}
darkToggle.addEventListener('click', ()=>{
  const dark = document.body.classList.toggle('dark-mode');
  localStorage.setItem('dark-mode', dark);
  darkToggle.textContent = dark ? '☀️ Light Mode' : '🌙 Dark Mode';
});
