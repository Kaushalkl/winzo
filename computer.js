const dices = document.getElementsByClassName('dice');
const p1Dice = document.getElementById('p1-dice');
const redsMoveToken = document.getElementById('redPlayerToken');
const bluesMoveToken = document.getElementById('bluePlayerToken');
const greensMoveToken = document.getElementById('greenPlayerToken');
const yellowsMoveToken = document.getElementById('yellowPlayerToken');
const home = document.getElementById('home');
const diceValue = document.getElementsByClassName('roll-value');
const redToken = document.querySelectorAll('.redToken');
const blueToken = document.querySelectorAll('.blueToken');
const greenToken = document.querySelectorAll('.greenToken');
const yellowToken = document.querySelectorAll('.yellowToken');
const redHome = document.getElementById('redHome');
const greenHome = document.getElementById('greenHome');
const blueHome = document.getElementById('blueHome');
const yellowHome = document.getElementById('yellowHome');
const cubePath= document.querySelectorAll('.cube-move-spot');
const redStartSpot = document.querySelectorAll('.redPath0');
const blueStartSpot = document.querySelectorAll('.bluePath0');
const greenStartSpot = document.querySelectorAll('.greenPath0');
const yellowStartSpot = document.querySelectorAll('.yellowPath0');

// Audio files
const roll = new Audio('Rolling Dice - Sound ! Notification Tone.mp3');
const move = new Audio('Untitled_Project_V1.mp3');
const won = new Audio('won.mp3');
const kill = new Audio('open-hat-snake-100639.mp3');

// Game state variables
let playerWons = 0;
let redWon = false;
let greenWon = false;
let blueWon = false;
let yellowWon = false;

let won1st = false;
let won2nd = false;
let won3rd = false;

let playerCount;
let redPlaying;
let greenPlaying;
let yellowPlaying;
let bluePlaying;

// AI flags
let redComputer = false;
let greenComputer = false;
let yellowComputer = false;
let blueComputer = false;

let floatToken = 0;
let playersMove = 0;
let nCanWon;
let theEnd = false;
let homeChance = false;
let killed = false;

let icons = document.querySelectorAll(`.player-container img`);
let diceOutcome;
let Ndice = Array.from(dices);
[Ndice[1], Ndice[2]] = [Ndice[2], Ndice[1]];
[Ndice[2], Ndice[3]] = [Ndice[3], Ndice[2]];

let tokens = [redToken, greenToken, yellowToken, blueToken];
let token;

// AI decision making delays (in milliseconds)
const AI_THINK_TIME = 800;
const AI_MOVE_TIME = 1000;

// Get data from setup window
function gettingData() {
    const urlQueries = new URLSearchParams(window.location.search);
    playerCount = +urlQueries.get("nPlaying") || 4;
    redPlaying = urlQueries.get("redPlaying") === "true" || true;
    greenPlaying = urlQueries.get("greenPlaying") === "true" || true;
    yellowPlaying = urlQueries.get("yellowPlaying") === "true" || true;
    bluePlaying = urlQueries.get("bluePlaying") === "true" || true;

    redComputer = urlQueries.get("redComputer") === "true" || false;
    greenComputer = urlQueries.get("greenComputer") === "true" || true;
    yellowComputer = urlQueries.get("yellowComputer") === "true" || true;
    blueComputer = urlQueries.get("blueComputer") === "true" || true;
    
    nCanWon = playerCount - 1;
    gameloop();
}

// Initialize game
gettingData();
showingTokens();

// Show/hide tokens based on player active
function showingTokens() {
    if (!redPlaying) redStartSpot.forEach(spot => spot.innerHTML = "");
    if (!greenPlaying) greenStartSpot.forEach(spot => spot.innerHTML = "");
    if (!yellowPlaying) yellowStartSpot.forEach(spot => spot.innerHTML = "");
    if (!bluePlaying) blueStartSpot.forEach(spot => spot.innerHTML = "");
}

// Switch between players
function switchPlayer(playersMove) {
    return new Promise((resolve, reject) => {
        icons.forEach(e => e.classList.remove('floating'));
        
        switch (playersMove) {
            case 1:
                if (redPlaying && !redWon) {
                    redsMoveToken.classList.add('floating');
                    resolve(playersMove);
                } else {
                    reject();
                }
                break;
            case 2:
                if (greenPlaying && !greenWon) {
                    greensMoveToken.classList.add('floating');
                    resolve(playersMove);
                } else {
                    reject();
                }
                break;
            case 3:
                if (yellowPlaying && !yellowWon) {
                    yellowsMoveToken.classList.add('floating');
                    resolve(playersMove);
                } else {
                    reject();
                }
                break;
            case 4:
                if (bluePlaying && !blueWon) {
                    bluesMoveToken.classList.add('floating');
                    resolve(playersMove);
                } else {
                    reject();
                }
                break;
        }
    });
}

// Main game loop
function gameloop() {
    switchPlayer(playersMove)
        .then((playersMove) => rolling(playersMove))
        .catch(() => {
            try {
                update();
            } catch (error) {
                if (error instanceof TypeError && error.message === "token is not iterable") {
                    playersMove++;
                    gameloop();
                } else {
                    console.error("Unexpected error:", error);
                }
            }
        });
}

// Dice rolling with AI handling
function rolling(playersMove) {
    return new Promise((resolve) => {
        Ndice.forEach(dice => dice.classList.remove('rolling'));

        let isAI = false;
        switch (playersMove) {
            case 1: isAI = redComputer; break;
            case 2: isAI = greenComputer; break;
            case 3: isAI = yellowComputer; break;
            case 4: isAI = blueComputer; break;
        }

        if (isAI) {
            // AI automatically rolls after thinking time
            setTimeout(() => clickRoll(), AI_THINK_TIME);
        } else {
            // Human player clicks to roll
            Ndice[playersMove - 1].addEventListener('click', clickRoll);
        }

        resolve();
    });
}

// Click and roll dice
function clickRoll() {
    roll.play();
    Ndice[playersMove - 1].classList.add('rolling');
    Ndice[playersMove - 1].removeEventListener('click', clickRoll);

    // Clear all visible dice
    for (let i = 0; i < dices.length; i++) {
        for (let j = 1; j < 7; j++) {
            if (dices[i].querySelector(`#D${j}`).classList.contains('visible-dice'))
                dices[i].querySelector(`#D${j}`).classList.remove('visible-dice');
        }
    }

    // Remove floating class
    switch (playersMove) {
        case 1: redsMoveToken.classList.remove('floating'); break;
        case 2: greensMoveToken.classList.remove('floating'); break;
        case 3: yellowsMoveToken.classList.remove('floating'); break;
        case 4: bluesMoveToken.classList.remove('floating'); break;
    }

    setTimeout(() => {
        const randomInt = Math.floor(Math.random() * 6) + 1;
        diceOutcome = randomInt;
        Ndice[playersMove - 1].querySelector(`#D${randomInt}`).classList.add('visible-dice');
        Ndice[playersMove - 1].classList.remove('rolling');
        tokenFloat(playersMove);
    }, 500);
}

// AI Strategy - Choose best move
function aiChooseMove(availableTokens) {
    const priorities = [];
    
    availableTokens.forEach(t => {
        let priority = 0;
        
        // Check if token is in start area
        if (t.parentElement.classList.contains("disks")) {
            if (diceOutcome === 6) {
                priority = 100; // High priority to get token out
            }
            priorities.push({ token: t, priority });
            return;
        }
        
        // Get current position
        let currentSpot = getCurrentSpot(t);
        let canMove = currentSpot + diceOutcome + 1;
        
        if (canMove <= 58) {
            // Priority 1: Reach home (highest priority)
            if (canMove >= 52 && canMove <= 57) {
                priority += 200;
            }
            
            // Priority 2: Kill opponent's token
            let nextPosition = currentSpot + diceOutcome;
            if (canKillOpponent(nextPosition)) {
                priority += 150;
            }
            
            // Priority 3: Move token that's furthest ahead
            priority += currentSpot;
            
            // Priority 4: Avoid being killed (move from vulnerable spots)
            if (isVulnerablePosition(currentSpot)) {
                priority += 50;
            }
            
            priorities.push({ token: t, priority });
        }
    });
    
    // Sort by priority and return best move
    priorities.sort((a, b) => b.priority - a.priority);
    return priorities.length > 0 ? priorities[0].token : null;
}

// Get current spot number for a token
function getCurrentSpot(token) {
    let matchingClass;
    switch (playersMove) {
        case 1:
            matchingClass = [...token.parentNode.classList].find(c => c.startsWith("redPath"));
            return parseInt(matchingClass.substring(7));
        case 2:
            matchingClass = [...token.parentNode.classList].find(c => c.startsWith("greenPath"));
            return parseInt(matchingClass.substring(9));
        case 3:
            matchingClass = [...token.parentNode.classList].find(c => c.startsWith("yellowPath"));
            return parseInt(matchingClass.substring(10));
        case 4:
            matchingClass = [...token.parentNode.classList].find(c => c.startsWith("bluePath"));
            return parseInt(matchingClass.substring(8));
    }
    return 0;
}

// Check if AI can kill opponent at this position
function canKillOpponent(position) {
    let pathClass;
    switch (playersMove) {
        case 1: pathClass = `.redPath${position}`; break;
        case 2: pathClass = `.greenPath${position}`; break;
        case 3: pathClass = `.yellowPath${position}`; break;
        case 4: pathClass = `.bluePath${position}`; break;
    }
    
    const path = document.querySelector(pathClass);
    if (!path) return false;
    
    const tokensInPath = path.querySelectorAll('img');
    const currentPlayerToken = tokens[playersMove - 1][0].name;
    
    return Array.from(tokensInPath).some(t => t.name !== currentPlayerToken);
}

// Check if position is vulnerable to being killed
function isVulnerablePosition(position) {
    // Check common vulnerable positions (not star spots)
    return position % 13 !== 8 && position % 13 !== 1;
}

// Auto-play for AI with smart decision making
function autoPlay(token) {
    const availableTokens = Array.from(token).filter(t => t.classList.contains('floating'));
    
    if (availableTokens.length === 0) return;
    
    let chosenToken;
    
    // Check if it's AI turn
    let isAI = false;
    switch (playersMove) {
        case 1: isAI = redComputer; break;
        case 2: isAI = greenComputer; break;
        case 3: isAI = yellowComputer; break;
        case 4: isAI = blueComputer; break;
    }
    
    if (isAI) {
        // AI makes smart decision
        chosenToken = aiChooseMove(availableTokens);
        if (!chosenToken && availableTokens.length > 0) {
            chosenToken = availableTokens[0];
        }
        
        // Add delay for AI move
        setTimeout(() => {
            if (chosenToken) {
                chosenToken.click();
            }
        }, AI_MOVE_TIME);
    } else {
        // For single floating token, auto-click (human or AI)
        if (availableTokens.length === 1) {
            setTimeout(() => availableTokens[0].click(), 300);
        }
    }
}

// Add floating class to tokens
function tokenFloat(playersMove) {
    token = tokens[playersMove - 1];
    let skipMove = true;
    
    token.forEach(t => {
        if (t.parentElement.classList.contains("disks") || t.parentElement.classList.contains("tokenHome")) {
            if (t.parentElement.classList.contains("disks")) {
                if (diceOutcome === 6) {
                    skipMove = false;
                    t.classList.add('floating');
                    t.addEventListener('click', openToken);
                }
            }
        } else {
            // Check if token can move
            let currentSpot = getCurrentSpot(t);
            let canMove = currentSpot + diceOutcome + 1;

            if (canMove <= 58) {
                skipMove = false;
                t.classList.add('floating');
                t.addEventListener('click', moveToken);
            }
        }
    });

    canAutoPlay(token);
    
    // Skip move if no token is able to move
    if (skipMove) {
        update();
    }
}

// Check if token can automatically move
function canAutoPlay(token) {
    floatToken = 0;
    token.forEach(t => {
        if (t.classList.contains('floating')) {
            floatToken++;
        }
    });
    
    console.log("Float token count:", floatToken);
    
    // Always use AI logic for computer players or single token moves
    autoPlay(token);
}

// Get extra chances if get 6
function extraChance() {
    for (const t of token) {
        if (diceOutcome === 6 && t.classList.contains('floating')) {
            return true;
        }
    }
    return false;
}

// Open new token
function openToken() {
    let path;
    let Token = document.getElementById(this.id);
    
    tokens[playersMove - 1].forEach(t => {
        t.removeEventListener('click', openToken);
    });

    // Handle multiple tokens on spot
    cubePath.forEach(path => {
        let images = path.querySelectorAll('img');
        if (images.length > 4) {
            path.classList.add("makeGrid2");
        } else if (images.length > 1) {
            path.classList.add("makeGrid");
        }
    });

    switch (playersMove) {
        case 1:
            path = document.querySelector(`.redPath1`);
            break;
        case 2:
            path = document.querySelector(`.greenPath1`);
            break;
        case 3:
            path = document.querySelector(`.yellowPath1`);
            break;
        case 4:
            path = document.querySelector(`.bluePath1`);
            break;
    }
    
    path.appendChild(Token);
    move.play();
    update();
}

// Move token with animation
function moveToken() {
    let tokenId = this.id;
    let i = getCurrentSpot(this);

    function movingToken(remaining) {
        if (remaining <= 0) {
            let path;
            switch (playersMove) {
                case 1: path = document.querySelector(`.redPath${i}`); break;
                case 2: path = document.querySelector(`.greenPath${i}`); break;
                case 3: path = document.querySelector(`.yellowPath${i}`); break;
                case 4: path = document.querySelector(`.bluePath${i}`); break;
            }

            let Token = document.getElementById(tokenId);
            
            // Update grid classes
            cubePath.forEach(path => {
                let images = path.querySelectorAll('img');
                if (images.length > 4) {
                    path.classList.remove("makeGrid2");
                } else if (images.length > 1) {
                    path.classList.remove("makeGrid");
                }
            });

            move.play();
            path.appendChild(Token);

            cubePath.forEach(path => {
                let images = path.querySelectorAll('img');
                if (images.length > 4) {
                    path.classList.add("makeGrid2");
                } else if (images.length > 1) {
                    path.classList.add("makeGrid");
                }
            });

            if (path.classList.contains("tokenHome") && path.querySelectorAll('img').length < 4) {
                homeChance = true;
            } else {
                killToken(Token, path);
            }
            return;
        }

        i++;
        let nextPath;
        switch (playersMove) {
            case 1: nextPath = document.querySelector(`.redPath${i}`); break;
            case 2: nextPath = document.querySelector(`.greenPath${i}`); break;
            case 3: nextPath = document.querySelector(`.yellowPath${i}`); break;
            case 4: nextPath = document.querySelector(`.bluePath${i}`); break;
        }
        
        let Token = document.getElementById(tokenId);
        nextPath.appendChild(Token);
        setTimeout(() => movingToken(remaining - 1), 300);
    }

    movingToken(diceOutcome);
}

// Kill opponent tokens
function killToken(Token, path) {
    if (!(path.classList.contains('star-place') || path.classList.contains("tokenStart"))) {
        const tokenName = Token.name;
        const tokensInPath = path.querySelectorAll('img');
        
        tokensInPath.forEach(t => {
            const otherTokenName = t.name;
            if (tokenName !== otherTokenName) {
                killed = true;
                let homeSpot;
                switch (otherTokenName) {
                    case "redToken": homeSpot = redStartSpot; break;
                    case "greenToken": homeSpot = greenStartSpot; break;
                    case "yellowToken": homeSpot = yellowStartSpot; break;
                    case "blueToken": homeSpot = blueStartSpot; break;
                }
                
                let killedToken = t;
                [...homeSpot].forEach(s => {
                    if (!s.querySelector('img')) {
                        kill.play();
                        s.appendChild(killedToken);
                        return;
                    }
                });
            }
        });
    }
    update();
}

// Check if someone won
function isWon() {
    if (redHome.querySelectorAll('img').length === 4 && !redWon) {
        redWon = true;
        playerWons++;
        return ".p1";
    }
    if (greenHome.querySelectorAll('img').length === 4 && !greenWon) {
        greenWon = true;
        playerWons++;
        return ".p2";
    }
    if (blueHome.querySelectorAll('img').length === 4 && !blueWon) {
        blueWon = true;
        playerWons++;
        return ".p3";
    }
    if (yellowHome.querySelectorAll('img').length === 4 && !yellowWon) {
        yellowWon = true;
        playerWons++;
        return ".p4";
    }
}

// Show won crown
function showWon(wonPlayerClass) {
    const showSpot = document.querySelector(wonPlayerClass);
    switch (playerWons) {
        case 1:
            if (!won1st) {
                showSpot.style.display = "flex";
                showSpot.innerHTML = '<img src="Won1st.png" width="100%">';
                won.play();
                won1st = true;
            }
            break;
        case 2:
            if (!won2nd) {
                showSpot.style.display = "flex";
                showSpot.innerHTML = '<img src="Won2nd.png" width="100%">';
                won.play();
                won2nd = true;
            }
            break;
        case 3:
            if (!won3rd) {
                showSpot.style.display = "flex";
                showSpot.innerHTML = '<img src="Won3rd.png" width="100%">';
                won.play();
                won3rd = true;
            }
            break;
    }
}

// End game and show losers
function gameEnds() {
    console.log("Game ends");
    theEnd = true;

    if (redPlaying && !redWon) {
        let looser = document.querySelector('.p1');
        looser.style.display = "flex";
        looser.innerHTML = '<img src="looser.png" width="100%">';
    }
    if (greenPlaying && !greenWon) {
        let looser = document.querySelector('.p2');
        looser.style.display = "flex";
        looser.innerHTML = '<img src="looser.png" width="100%">';
    }
    if (yellowPlaying && !yellowWon) {
        let looser = document.querySelector('.p4');
        looser.style.display = "flex";
        looser.innerHTML = '<img src="looser.png" width="100%">';
    }
    if (bluePlaying && !blueWon) {
        let looser = document.querySelector('.p3');
        looser.style.display = "flex";
        looser.innerHTML = '<img src="looser.png" width="100%">';
    }
}

// Update player move and game state
function update() {
    if (playerWons === nCanWon) {
        gameEnds();
        return;
    }

    if (extraChance() || killed || homeChance) {
        killed = false;
        homeChance = false;
        // Same player continues
    } else {
        // Move to next player
        if (playersMove === 4) {
            playersMove = 1;
        } else {
            playersMove++;
        }
    }

    // Check if someone won
    let wonPlayerClass = isWon();
    if (redWon || blueWon || greenWon || yellowWon) {
        showWon(wonPlayerClass);
    }

    // Update grid classes for multiple tokens
    cubePath.forEach(path => {
        let images = path.querySelectorAll('img');
        if (images.length > 4) {
            path.classList.add("makeGrid2");
        } else if (images.length > 1) {
            path.classList.add("makeGrid");
        } else {
            path.classList.remove("makeGrid");
            path.classList.remove("makeGrid2");
        }
    });

    // Remove floating class and event listeners
    if (token) {
        token.forEach(t => {
            t.classList.remove('floating');
            t.removeEventListener('click', moveToken);
            t.removeEventListener('click', openToken);
        });
    }

    if (!theEnd) {
        gameloop();
    }
}