<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>3D Pinball: Space Cadet</title>
    <link rel="icon" type="image/png" href="icon-192.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#030712">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            user-select: none;
            -webkit-user-select: none;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #0c0f20;
            background-image: radial-gradient(circle at 50% 50%, #11152a 0%, #0c0f20 65%, #080a16 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ARCADE PHYSICAL CABINET WRAPPER - SEAMLESS BLEND */
        .arcade-cabinet {
            position: relative;
            width: min(97vh, 97vw);
            height: min(97vh, 97vw);
            max-width: 99vw;
            max-height: 99vh;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* OVERLAY BEZEL IMAGE */
        .cabinet-overlay-img {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 10;
            object-fit: fill;
        }

        /* SCREEN VIEWPORT */
        .screen-viewport {
            position: absolute;
            top: 26.6%;
            left: 20.6%;
            width: 58.8%;
            height: 48.0%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 5;
            border-radius: 2px;
        }

        canvas.emscripten {
            border: 0 none;
            background-color: #000;
            width: 100% !important;
            height: 100% !important;
            display: block;
            image-rendering: pixelated;
            image-rendering: -moz-crisp-edges;
            image-rendering: crisp-edges;
            object-fit: fill;
        }

        /* GLASS REFLECTION */
        .glass-reflection {
            position: absolute;
            top: 0; left: 0; right: 0; height: 45%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.01) 40%, transparent 60%);
            pointer-events: none;
            z-index: 9;
        }

        /* INTERACTIVE PHYSICAL BUTTONS ON THE DECK */
        .deck-btn-left, .deck-btn-right, .deck-btn-launch {
            position: absolute;
            z-index: 20;
            cursor: pointer;
            touch-action: manipulation;
            border-radius: 12px;
        }

        .deck-btn-left {
            top: 76%; left: 14%;
            width: 33%; height: 19%;
        }

        .deck-btn-left:active, .deck-btn-left.active {
            background: radial-gradient(circle, rgba(56, 189, 248, 0.35) 0%, transparent 70%);
        }

        .deck-btn-launch {
            top: 76%; left: 43%;
            width: 14%; height: 14%;
            border-radius: 50%;
        }

        .deck-btn-launch:active, .deck-btn-launch.active {
            background: radial-gradient(circle, rgba(16, 185, 129, 0.45) 0%, transparent 70%);
        }

        .deck-btn-right {
            top: 76%; left: 54%;
            width: 33%; height: 19%;
        }

        .deck-btn-right:active, .deck-btn-right.active {
            background: radial-gradient(circle, rgba(236, 72, 153, 0.35) 0%, transparent 70%);
        }

        /* CLICKABLE 4U.IA.BR MARQUEE */
        .marquee-link {
            position: absolute;
            top: 4.5%;
            left: 18%;
            width: 64%;
            height: 15%;
            z-index: 25;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .marquee-link:hover {
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
            background: rgba(56, 189, 248, 0.05);
        }

        /* LOADING */
        #status-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
            z-index: 7;
        }

        #status {
            color: #38bdf8;
            font-family: 'Press Start 2P', monospace;
            font-size: 9px;
            line-height: 1.8;
            margin-bottom: 12px;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
        }

        progress {
            accent-color: #38bdf8;
            width: 160px;
            height: 10px;
            border-radius: 6px;
            overflow: hidden;
        }
    </style>
</head>
<body>

<div class="arcade-cabinet" id="arcadeCabinet">
    <!-- Screen Viewport Inside Arcade Monitor -->
    <div class="screen-viewport" id="pinballWrapper">
        <div class="glass-reflection"></div>
        
        <div id="status-wrapper">
            <div id="status">CARREGANDO SPACE CADET...</div>
            <progress hidden id="progress" max="100" value="0"></progress>
        </div>
        
        <canvas class="emscripten" id="canvas" oncontextmenu="event.preventDefault()" style="display:none" tabindex="-1"></canvas>
    </div>

    <!-- Transparent Bezel Artwork -->
    <img src="cabinet_bezel.webp" class="cabinet-overlay-img" alt="Arcade Cabinet 4U.IA.BR">

    <!-- Clickable Marquee Link to 4u.ia.br -->
    <a href="https://4u.ia.br" target="_blank" rel="noopener" class="marquee-link" title="Acesse 4u.ia.br — Experiências Digitais & IA"></a>

    <!-- Interactive Touch/Click Arcade Controls -->
    <div class="deck-btn-left" id="btn-flipper-left" title="Flipper Esquerdo (Z)"></div>
    <div class="deck-btn-launch" id="btn-launch" title="Lançar Mola (Espaço)"></div>
    <div class="deck-btn-right" id="btn-flipper-right" title="Flipper Direito (/)"></div>
</div>

<script>
    var statusElement = document.getElementById("status");
    var progressElement = document.getElementById("progress");
    var statusWrapper = document.getElementById("status-wrapper");
    var canvasElement = document.getElementById("canvas");

    // --- SISTEMA DE SOM AUTOMÁTICO E ROBUSTO ---
    var audioCtx = null;
    var masterMusicGain = null;
    var musicTimer = null;
    var musicStarted = false;

    function ensureAudioRunning() {
        try {
            // 1. Inicia WebAudio Synthwave se ainda não foi iniciado
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                masterMusicGain = audioCtx.createGain();
                masterMusicGain.gain.setValueAtTime(0.35, audioCtx.currentTime);
                masterMusicGain.connect(audioCtx.destination);
            }

            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            if (!musicStarted && audioCtx) {
                musicStarted = true;
                var notes = [146.83, 174.61, 220.00, 261.63, 293.66, 220.00, 174.61, 146.83, 130.81, 164.81, 196.00, 246.94, 261.63, 196.00, 164.81, 130.81];
                var step = 0;

                if (musicTimer) clearInterval(musicTimer);
                musicTimer = setInterval(function() {
                    if (!audioCtx || audioCtx.state !== 'running') {
                        if (audioCtx && audioCtx.state === 'suspended') {
                            audioCtx.resume();
                        }
                        return;
                    }
                    var now = audioCtx.currentTime;
                    
                    // Bass Synth
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    var filter = audioCtx.createBiquadFilter();

                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(notes[step % notes.length], now);

                    filter.type = 'lowpass';
                    filter.frequency.setValueAtTime(650, now);
                    filter.frequency.exponentialRampToValueAtTime(130, now + 0.17);

                    gain.gain.setValueAtTime(0.10, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.17);

                    osc.connect(filter);
                    filter.connect(gain);
                    gain.connect(masterMusicGain);

                    osc.start(now);
                    osc.stop(now + 0.18);

                    // Drum Beat
                    if (step % 4 === 0) {
                        var kickOsc = audioCtx.createOscillator();
                        var kickGain = audioCtx.createGain();
                        kickOsc.frequency.setValueAtTime(110, now);
                        kickOsc.frequency.exponentialRampToValueAtTime(0.01, now + 0.14);
                        kickGain.gain.setValueAtTime(0.15, now);
                        kickGain.gain.exponentialRampToValueAtTime(0.001, now + 0.14);
                        kickOsc.connect(kickGain);
                        kickGain.connect(masterMusicGain);
                        kickOsc.start(now);
                        kickOsc.stop(now + 0.15);
                    }

                    step++;
                }, 180);
            }

            // 2. Destrava também o SDL2 Audio do jogo Space Cadet
            if (typeof Module !== 'undefined' && Module.SDL2 && Module.SDL2.audioContext) {
                if (Module.SDL2.audioContext.state === 'suspended') {
                    Module.SDL2.audioContext.resume();
                }
            }
        } catch(e) {
            console.error("Audio error:", e);
        }
    }

    // Ouvintes globais em modo de captura (capture: true) para capturar o primeiro clique ou tecla
    ['click', 'mousedown', 'keydown', 'touchstart', 'pointerdown'].forEach(function(evt) {
        window.addEventListener(evt, function() {
            ensureAudioRunning();
        }, { capture: true, passive: true });
        document.addEventListener(evt, function() {
            ensureAudioRunning();
        }, { capture: true, passive: true });
    });

    // Haptic Vibration Feedback
    function hapticFeedback() {
        if (navigator.vibrate) {
            try { navigator.vibrate(15); } catch(e) {}
        }
    }

    // Teclas do Jogo (Z = Esq, / = Dir, Espaço = Mola)
    function triggerKey(key, code, keyCode, isDown) {
        var eventType = isDown ? "keydown" : "keyup";
        var event = new KeyboardEvent(eventType, {
            bubbles: true,
            cancelable: true,
            key: key,
            code: code,
            keyCode: keyCode,
            which: keyCode
        });
        window.dispatchEvent(event);
        canvasElement.dispatchEvent(event);
        if (isDown) hapticFeedback();
    }

    function setupButton(id, key, code, keyCode) {
        var btn = document.getElementById(id);
        if (!btn) return;
        var active = false;

        var start = function(e) {
            e.preventDefault();
            ensureAudioRunning();
            if (!active) {
                active = true;
                btn.classList.add('active');
                triggerKey(key, code, keyCode, true);
            }
        };

        var end = function(e) {
            e.preventDefault();
            if (active) {
                active = false;
                btn.classList.remove('active');
                triggerKey(key, code, keyCode, false);
            }
        };

        btn.addEventListener("touchstart", start, { passive: false });
        btn.addEventListener("touchend", end, { passive: false });
        btn.addEventListener("touchcancel", end, { passive: false });
        btn.addEventListener("mousedown", start);
        btn.addEventListener("mouseup", end);
        btn.addEventListener("mouseleave", end);
    }

    setupButton("btn-flipper-left", "z", "KeyZ", 90);
    setupButton("btn-flipper-right", "/", "Slash", 191);
    setupButton("btn-launch", " ", "Space", 32);

    // Módulo Emscripten / WebAssembly
    var Module = {
        preRun: [],
        postRun: [],
        print: function(text) { console.log(text); },
        printErr: function(text) { console.error(text); },
        canvas: (function() {
            canvasElement.addEventListener("webglcontextlost", function(e) {
                alert("WebGL context lost. Recarregue a página.");
                e.preventDefault();
            }, false);
            return canvasElement;
        })(),
        setStatus: function(text) {
            if (!Module.setStatus.last) Module.setStatus.last = { time: Date.now(), text: '' };
            if (text === Module.setStatus.last.text) return;
            var m = text.match(/([^(]+)\((\d+(\.\d+)?)\/(\d+)\)/);
            var now = Date.now();
            if (m && now - Module.setStatus.last.time < 30) return;
            Module.setStatus.last.time = now;
            Module.setStatus.last.text = text;
            if (m) {
                text = m[1];
                progressElement.value = parseInt(m[2]) * 100;
                progressElement.max = parseInt(m[4]) * 100;
                progressElement.hidden = false;
            } else {
                progressElement.value = null;
                progressElement.max = null;
                progressElement.hidden = true;
                if (!text) {
                    canvasElement.style.display = "block";
                    statusWrapper.style.display = "none";
                    canvasElement.focus();
                }
            }
            statusElement.innerHTML = text;
        },
        totalDependencies: 0,
        monitorRunDependencies: function(left) {
            this.totalDependencies = Math.max(this.totalDependencies, left);
            Module.setStatus(left ? 'Preparando... (' + (this.totalDependencies - left) + '/' + this.totalDependencies + ')' : '');
        }
    };

    Module.setStatus("CARREGANDO...");
    window.onerror = function() {
        Module.setStatus("Erro ao carregar o jogo.");
    };

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./sw.js').catch(function() {});
    }
</script>
<script async src="SpaceCadetPinball.js"></script>
</body>
</html>
