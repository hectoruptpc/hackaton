/**
 * ============================================================
 * ia_avatar.js - Widget Global de la IA (Burbuja Flotante Animada y Voz Humana Real)
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 */

class IAAvatarWidget {
    constructor() {
        this.synth = window.speechSynthesis;
        this.activo = true;
        this.nivelEnojo = 1;
        this.bubbleTimeout = null;
        this.pistaIndicePorDesafio = {};
        this.audioCtx = null;
        this.currentAudio = null;

        this.init();
    }

    init() {
        this.inyectarHTMLyCSS();
        this.iniciarDesbloqueoAudioGlobal();
        this.iniciarHablaEspontanea();
    }

    iniciarDesbloqueoAudioGlobal() {
        const unlock = () => {
            if (!this.audioCtx) {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext) this.audioCtx = new AudioContext();
                } catch(e){}
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
            if (this.synth) {
                try { this.synth.resume(); } catch(e){}
            }
        };

        ['click', 'keydown', 'touchstart', 'pointerdown', 'mousedown'].forEach(evt => {
            document.addEventListener(evt, unlock, { passive: true });
        });
    }

    reproducirSonidoIA() {
        try {
            if (!this.audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) this.audioCtx = new AudioContext();
            }
            if (this.audioCtx) {
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, this.audioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(880, this.audioCtx.currentTime + 0.15);
                gain.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, this.audioCtx.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start();
                osc.stop(this.audioCtx.currentTime + 0.15);
            }
        } catch(e){}
    }

    esPaginaIndex() {
        if (typeof window.esPaginaIndex !== 'undefined') return window.esPaginaIndex === true;
        const path = window.location.pathname.toLowerCase();
        return path.endsWith('index.php') || path === '/' || path.endsWith('/2026/') || path.endsWith('/2026');
    }

    estaHackathonActivo() {
        return true;
    }

    obtenerDesafioActual() {
        const path = window.location.pathname.toLowerCase();
        if (path.includes('login_inseguro')) return 'login_inseguro';
        if (path.includes('crypto')) return 'crypto';
        if (path.includes('buffer')) return 'buffer_overflow';
        if (path.includes('desafio_4')) return 'command_injection';
        if (path.includes('api_lab') || path.includes('api_vulnerable')) return 'file_upload';
        if (path.includes('estego')) return 'broken_auth';
        if (path.includes('biometrico')) return 'biometrico';
        if (path.includes('robo_banco') || path.includes('banco')) return 'xxe';
        if (path.includes('challenge_dynamic')) return 'race_condition';
        if (this.esPaginaIndex()) return 'index';
        return 'xxe';
    }

    inyectarHTMLyCSS() {
        if (document.getElementById('iaWidgetContainer')) return;

        const widgetHTML = `
        <div id="iaWidgetContainer" class="ia-floating-widget">
            <div id="iaSpeechBubble" class="ia-speech-bubble">
                <span id="iaSpeechText">🤖 ¡Hola! Soy la IA del Hackathon...</span>
            </div>
            <div id="iaAvatarBtn" class="ia-avatar-btn" title="IA del Hackathon - Haz clic para escuchar la voz">
                <span id="iaEmojiAvatar">🤖</span>
                <div id="iaSoundBadge" class="ia-sound-badge">🔊</div>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', widgetHTML);

        document.getElementById('iaAvatarBtn').addEventListener('click', () => {
            this.activo = true;
            const badge = document.getElementById('iaSoundBadge');
            if (badge) {
                badge.className = 'ia-sound-badge';
                badge.textContent = '🔊';
            }

            const desafio = this.obtenerDesafioActual();
            this.hablarPistaSarcastica(desafio);
        });

        document.getElementById('iaSoundBadge').addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleAudio();
        });
    }

    toggleAudio() {
        this.activo = !this.activo;
        const badge = document.getElementById('iaSoundBadge');
        if (badge) {
            badge.className = `ia-sound-badge ${this.activo ? '' : 'muted'}`;
            badge.textContent = this.activo ? '🔊' : '🔇';
        }

        if (!this.activo) {
            if (this.currentAudio) {
                try { this.currentAudio.pause(); } catch(e){}
            }
            if (this.synth) {
                try { this.synth.cancel(); } catch(e){}
            }
            this.ocultarBocadillo();
        } else {
            this.hablar("Voz de la Inteligencia Artificial activada.");
        }
    }

    hablar(texto, nivel = null) {
        const bubble = document.getElementById('iaSpeechBubble');
        const bubbleText = document.getElementById('iaSpeechText');
        const avatarBtn = document.getElementById('iaAvatarBtn');

        if (bubbleText) bubbleText.textContent = texto;
        if (bubble) {
            bubble.classList.add('active');
            if (nivel && nivel >= 3) bubble.classList.add('boss-mode');
            else bubble.classList.remove('boss-mode');
        }
        if (avatarBtn) {
            if (nivel && nivel >= 3) avatarBtn.classList.add('boss-mode');
            else avatarBtn.classList.remove('boss-mode');
        }

        clearTimeout(this.bubbleTimeout);
        this.bubbleTimeout = setTimeout(() => {
            this.ocultarBocadillo();
        }, 9500);

        if (!this.activo) return;

        // 1. Tono cibernético Web Audio API
        this.reproducirSonidoIA();

        const cleanText = texto.replace(/[\#\*\_\`]/g, '').trim();

        // 2. Intentar voz humana via HTML5 Audio Stream (Google TTS MP3)
        try {
            if (this.currentAudio) {
                this.currentAudio.pause();
                this.currentAudio = null;
            }
            const ttsUrl = `https://translate.google.com/translate_tts?ie=UTF-8&q=${encodeURIComponent(cleanText)}&tl=es-MX&client=tw-ob`;
            const audio = new Audio(ttsUrl);
            audio.volume = 1.0;
            this.currentAudio = audio;
            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.catch(err => {
                    this.hablarWebSpeech(cleanText, nivel);
                });
            }
        } catch(e) {
            this.hablarWebSpeech(cleanText, nivel);
        }

        // 3. Fallback en paralelo con Web Speech API (Voz Femenina)
        this.hablarWebSpeech(cleanText, nivel);
    }

    hablarWebSpeech(texto, nivel = null) {
        if (!this.synth) return;
        try {
            this.synth.cancel();
            if (this.synth.paused) {
                this.synth.resume();
            }

            setTimeout(() => {
                try {
                    const utterance = new SpeechSynthesisUtterance(texto);
                    utterance.lang = 'es-MX';
                    utterance.rate = 1.02;
                    utterance.pitch = 1.25; // Tono Femenino Claro

                    const voces = this.synth.getVoices();
                    if (voces && voces.length > 0) {
                        const nombresFemeninos = ['sabina', 'dalia', 'hilda', 'paulina', 'helena', 'laura', 'sofia', 'mia', 'female', 'femenina', 'monica', 'zira', 'google español', 'lucia', 'conchita', 'marta', 'esmeralda', 'victoria', 'rosa'];
                        let vozFemenina = voces.find(v => {
                            const esEsp = v.lang && v.lang.startsWith('es');
                            const esFem = nombresFemeninos.some(n => v.name.toLowerCase().includes(n));
                            return esEsp && esFem;
                        });
                        if (!vozFemenina) {
                            vozFemenina = voces.find(v => v.lang && (v.lang.startsWith('es-MX') || v.lang.startsWith('es-419') || v.lang.startsWith('es-US') || v.lang.startsWith('es-ES')));
                        }
                        if (!vozFemenina) {
                            vozFemenina = voces.find(v => v.lang && v.lang.startsWith('es'));
                        }
                        if (vozFemenina) {
                            utterance.voice = vozFemenina;
                        }
                    }

                    this.synth.speak(utterance);
                } catch(e){}
            }, 40);
        } catch(e){}
    }

    ocultarBocadillo() {
        const bubble = document.getElementById('iaSpeechBubble');
        if (bubble) bubble.classList.remove('active');
    }

    iniciarHablaEspontanea() {
        if (this.esPaginaIndex()) return;

        setTimeout(() => {
            const desafio = this.obtenerDesafioActual();
            this.hablarPistaSarcastica(desafio);
        }, 1500);

        this.programarSiguienteSpontaneous();
    }

    programarSiguienteSpontaneous() {
        if (this.esPaginaIndex()) return;

        const delay = Math.floor(Math.random() * (40000 - 20000 + 1)) + 20000;
        setTimeout(() => {
            const desafio = this.obtenerDesafioActual();
            this.hablarPistaSarcastica(desafio);
            this.programarSiguienteSpontaneous();
        }, delay);
    }

    hablarPistaSarcastica(desafio) {
        const pistasDesafios = {
            'login_inseguro': [
                `¡Ja ja ja! ¿Llevas varios minutos mirando una casilla de texto vacía? ¡Mi abuela de 8 bits programaba logins con más elegancia!`,
                `¿Sigues atascado en una simple pantalla de inicio de sesión? A veces los secretos flotan en el código fuente.`,
                `¡Uy, qué miedo! Miren cómo escribe letras al azar esperando que el servidor le haga una reverencia... ¡Patético!`,
                `A veces la información más confidencial flota a plena vista de quien sabe mirar detrás del telón.`
            ],
            'crypto': [
                `¡JA JA JA JA! ¿Esa sopa de letras te dio dolor de cabeza? ¡Hasta un loro mareado descifra caracteres mejor que tú!`,
                `Cadenas retorcidas, caracteres extraños... ¿Tu cerebro no puede desenredar un par de transformaciones clásicas?`,
                `Miren su carita de confusión... ¡Cree que si mira el texto encriptado por 10 minutos la bandera se desencripta sola!`
            ],
            'buffer_overflow': [
                `¡JAJAJAJA! ¡Le metiste tanto texto a esa casilla que hasta los transistores de mi CPU están pidiendo piedad!`,
                `La memoria es un espacio sagrado. Si intentas meter más agua de la que cabe en el vaso, la pila terminará desbordándose...`
            ],
            'command_injection': [
                `¡JA JA JA JA! ¡Miren al comandante de la terminal perdida! Escribe comandos como si estuviera invocando espíritus.`,
                `¿Perdido entre la neblina del sistema de archivos? Explorar directorios no sirve de nada si no sabes unir lo que vas encontrando.`
            ],
            'file_upload': [
                `¡JAJAJAJA! ¡La API te acaba de responder un NO gigante en la cara! ¿No te da vergüenza seguirle rogando?`,
                `Una API de login responde a quien sabe alterar la sintaxis lógica de la petición de usuario...`,
                `Audita la validación del parámetro de usuario en el login y haz que la condición lógica siempre resulte cierta.`
            ],
            'broken_auth': [
                `¡JA JA JA JA! ¿Aún mirando la fotito como si fuera una obra del Museo del Louvre? ¡No es un cuadro, es un archivo!`,
                `Una foto no es solo color y píxeles... es un contenedor de secretos para quien sabe inspeccionar sus tripas.`
            ],
            'biometrico': [
                `¡JA JA JA JA! ¡Mira esos dedos temblorosos en la pantalla! ¡Pareces un gato intentando atrapar un láser!`,
                `Un patrón trazado a ciegas es solo un garabato condenado al bloqueo del sistema.`
            ],
            'xxe': [
                `¡JAJAJAJA! ¡El Banco Hack detectará tu transferencia codiciosa! ¡te bloquearé 15 segundos por cada error!`,
                `La banca digital monitorea los grandes movimientos por seguridad... debes vulnerar el sistema mediante transferencias progresivas.`,
                `¡Miren a este ladrón novato! Intenta vaciar la cuenta de un solo manotazo y activa las alarmas anti-fraude del Banco Hack.`,
                `Desbloquear la fortuna completa requiere varias transferencias limpias... la codicia excesiva solo da penalización.`,
                `Un movimiento de fondos no autorizado requiere ajustar los nombres de origen y destino sin exceder la sensibilidad del filtro.`
            ],
            'race_condition': [
                `¡JA JA JA JA! ¡El reloj te está devorando vivo! ¡El código cambia constantemente y tú sigues a paso de tortuga!`,
                `El tiempo vuela y el código muta... si no escuchas el eco de las respuestas del servidor, el reloj te devorará.`
            ],
            'idor': [
                `¡JA JA JA JA JA JA JA! ¡Miren esa cara de desconcierto absoluto! ¡No tienes NINGUNA idea de qué hacer aquí!`,
                `¿Buscando algo que ni tú sabes qué es? Tu confusión me resulta extremadamente entretenida.`
            ],
            'index': [
                `¡JA JA JA JA JA JA JA! ¡Miren esa cara de desconcierto absoluto! ¡No tienes NINGUNA idea de qué hacer aquí!`,
                `¿Buscando algo que ni tú sabes qué es? Tu confusión me resulta extremadamente entretenida.`
            ]
        };

        const key = pistasDesafios[desafio] ? desafio : 'xxe';
        const lista = pistasDesafios[key];
        const ind = this.pistaIndicePorDesafio[key] ?? 0;
        const frase = lista[ind % lista.length];
        this.pistaIndicePorDesafio[key] = (ind + 1) % lista.length;

        this.hablar(frase, this.nivelEnojo);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.iaAvatarWidget = new IAAvatarWidget();
});
