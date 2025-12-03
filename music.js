// --- Global Background Music Controller ---

if (!window.bgMusic) {
    window.bgMusic = new Audio("background-music.mp3");
    window.bgMusic.loop = true;
    window.bgMusic.volume = 1;

    // Restore mute state
    const isMuted = localStorage.getItem("bgMusicMuted") === "true";
    window.bgMusic.muted = isMuted;

    // --- Try autoplay immediately ---
    window.bgMusic.play().catch(() => {
        // If autoplay blocked, retry on first interaction
        const tryPlay = () => {
            window.bgMusic.play().catch(() => {});
            document.removeEventListener("click", tryPlay);
            document.removeEventListener("touchstart", tryPlay);
        };
        document.addEventListener("click", tryPlay, { once: true });
        document.addEventListener("touchstart", tryPlay, { once: true });
    });
}

// --- Mute Button ---
function createMuteButton() {
    if (document.getElementById("muteBtn")) return;

    let muteBtn = document.createElement("button");
    muteBtn.id = "muteBtn";
    muteBtn.innerText = window.bgMusic.muted ? "🔇" : "🔊";
    muteBtn.style.cssText = 

    // Hover animation
    muteBtn.addEventListener("mouseenter", () => {
        muteBtn.style.transform = "scale(1.1)";
        muteBtn.style.background = "rgba(0, 0, 0, 0.8)";
    });
    muteBtn.addEventListener("mouseleave", () => {
        muteBtn.style.transform = "scale(1)";
        muteBtn.style.background = "rgba(0, 0, 0, 0.6)";
    });

    // Toggle mute on click
    muteBtn.addEventListener("click", () => {
        window.bgMusic.muted = !window.bgMusic.muted;
        muteBtn.innerText = window.bgMusic.muted ? "🔇" : "🔊";
    });

    document.body.appendChild(muteBtn);
}


// --- Smooth Fade Effect ---
function fadeVolume(start, end) {
    let step = (end - start) / 10;
    let current = start;

    let interval = setInterval(() => {
        current += step;
        if ((step > 0 && current >= end) || (step < 0 && current <= end)) {
            current = end;
            clearInterval(interval);
        }
        window.bgMusic.volume = Math.max(0, Math.min(1, current));
    }, 40);
}

// --- Create Button on DOM Ready ---
document.addEventListener("DOMContentLoaded", createMuteButton);
