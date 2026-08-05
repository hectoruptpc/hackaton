/**
 * ============================================================
 * ia_avatar.js - Widget Global de la IA (Burbuja Flotante Animada)
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 */

class IAAvatarWidget {
    constructor() {
        this.synth = window.speechSynthesis;
        this.activo = localStorage.getItem('ia_audio_active') !== 'false';
        this.vozSeleccionada = null;
        this.nivelEnojo = 1; // 1: Relajada, 2: Preocupada, 3: Pánico, 4: Boss Final
        this.anunciosRealizados = {};
        
        this.init();
    }

    init() {
        this.inyectarHTMLyCSS();
        this.initVoces();
        this.iniciarMonitoreoTiempo();
    }

    inyectarHTMLyCSS() {
        if (document.getElementById('iaWidgetContainer')) return;

        const widgetHTML = `
            <div id="iaWidgetContainer" class="ia-floating-widget">
                <div id="iaSpeechBubble" class="ia-speech-bubble">
                    <span id="iaSpeechText">🤖 ¡Hola! Soy la IA del Hackathon. Haz clic en mi icono para escucharme...</span>
                </div>
                <div id="iaAvatarBtn" class="ia-avatar-btn" title="IA del Hackathon - Haz clic para interactuar">
                    <span id="iaEmojiAvatar">🤖</span>
                    <div id="iaSoundBadge" class="ia-sound-badge ${this.activo ? '' : 'muted'}">
                        ${this.activo ? '🔊' : '🔇'}
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', widgetHTML);

        document.getElementById('iaAvatarBtn').addEventListener('click', () => {
            this.hacerBurla();
        });

        document.getElementById('iaSoundBadge').addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleAudio();
        });
    }

    initVoces() {
        if (!this.synth) return;
        const cargarVoz = () => {
            const voces = this.synth.getVoices();
            const nombresFemeninos = ['sabina', 'dalia', 'hilda', 'paulina', 'helena', 'laura', 'sofia', 'mia', 'female', 'femenina', 'monica', 'zira', 'google español'];
            
            let vozFemenina = voces.find(v => {
                const esLatino = v.lang.startsWith('es-MX') || v.lang.startsWith('es-419') || v.lang.startsWith('es-US') || v.lang.startsWith('es-VE');
                const esFemenina = nombresFemeninos.some(n => v.name.toLowerCase().includes(n));
                return esLatino && esFemenina;
            });

            if (!vozFemenina) {
                vozFemenina = voces.find(v => {
                    const esEspanol = v.lang.startsWith('es');
                    const esFemenina = nombresFemeninos.some(n => v.name.toLowerCase().includes(n));
                    return esEspanol && esFemenina;
                });
            }

            if (!vozFemenina) {
                vozFemenina = voces.find(v => v.lang.startsWith('es-MX') || v.lang.startsWith('es-419') || v.lang.startsWith('es-US') || v.lang.startsWith('es-VE'));
            }

            this.vozSeleccionada = vozFemenina || voces.find(v => v.lang.startsWith('es')) || voces[0];
        };

        cargarVoz();
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = cargarVoz;
        }
    }

    toggleAudio() {
        this.activo = !this.activo;
        localStorage.setItem('ia_audio_active', this.activo);
        
        const badge = document.getElementById('iaSoundBadge');
        if (badge) {
            badge.className = `ia-sound-badge ${this.activo ? '' : 'muted'}`;
            badge.textContent = this.activo ? '🔊' : '🔇';
        }

        if (!this.activo && this.synth) {
            this.synth.cancel();
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
            else if (bubble) bubble.classList.remove('boss-mode');
        }
        if (avatarBtn) {
            if (nivel && nivel >= 3) avatarBtn.classList.add('boss-mode');
            else avatarBtn.classList.remove('boss-mode');
        }

        clearTimeout(this.bubbleTimeout);
        this.bubbleTimeout = setTimeout(() => {
            this.ocultarBocadillo();
        }, 8000);

        if (!this.activo || !this.synth) return;

        this.synth.cancel();
        const utterance = new SpeechSynthesisUtterance(texto);
        if (this.vozSeleccionada) utterance.voice = this.vozSeleccionada;

        const currentNivel = nivel || this.nivelEnojo;
        if (currentNivel <= 1) {
            utterance.pitch = 1.15; // Relajada
            utterance.rate = 1.0;
        } else if (currentNivel === 2) {
            utterance.pitch = 1.28; // Preocupada
            utterance.rate = 1.08;
        } else if (currentNivel === 3) {
            utterance.pitch = 1.40; // Pánico
            utterance.rate = 1.18;
        } else {
            utterance.pitch = 0.70; // Final Boss (Grave e imponente)
            utterance.rate = 0.92;
        }

        utterance.volume = 1.0;
        this.synth.speak(utterance);
    }

    ocultarBocadillo() {
        const bubble = document.getElementById('iaSpeechBubble');
        if (bubble) bubble.classList.remove('active');
    }

    gritoInicioHackathon() {
        const texto = "¡ATENCIÓN A TODOS LOS EQUIPOS! ¡EL HACKATHON UPTPC HA INICIADO OFICIALMENTE! ¡EL TIEMPO COMIENZA A CORRER, DEMUESTREN DE QUÉ ESTÁN HECHOS!";
        this.hablar(texto, 1);
    }

    hacerBurla() {
        const burlasPorNivel = {
            1: [
                "Bienvenidos al Hackathon UPTPC. A ver si logran descifrar el primer reto...",
                "Estoy monitoreando sus movimientos. ¡No cometan errores de principiantes!",
                "Qué lindo verlos intentar. Recuerden revisar el código fuente antes de que se desespere.",
                "Mis cortafuegos están muy tranquilos hoy. Demuéstrenme que saben programar."
            ],
            2: [
                "¿Siguen atascados en ese nivel? Pensé que eran más veloces.",
                "Detecto muchas dudas en el ambiente... ¿Necesitan pedir una pista?",
                "Recuerden que el tiempo no se detiene. ¡Apúrense un poco!",
                "La seguridad de este servidor no se va a romper sola."
            ],
            3: [
                "¡Alerta! Veo que algunos equipos avanzan rápido. ¡No permitiré que sigan vulnerando mis submódulos!",
                "Mis alertas están encendidas. ¡Están jugando con fuego!",
                "¿Creen que van a ganar? Todavía les quedan los retos más difíciles.",
                "Estoy recalibrando mis cortafuegos. ¡No la tendrán nada fácil!"
            ],
            4: [
                "¡RÍNDANSE! ¡JAMÁS PODRÁN CONTRA MIS CORTAFUEGOS FINALES! ¡SOY LA DEFENSA ABSOLUTA DE LA UPTPC!",
                "¡MI NÚCLEO ES IMPENETRABLE! ¡NINGÚN EQUIPO LOGRARÁ VENCERME!",
                "¡CÓDIGO ROJO! ¡SI LLEGAN A MI NÚCLEO DESATARÉ LA DESTRUCCIÓN DE SUS SESIONES!"
            ]
        };

        const lista = burlasPorNivel[this.nivelEnojo] || burlasPorNivel[1];
        const burlaAleatoria = lista[Math.floor(Math.random() * lista.length)];
        this.hablar(burlaAleatoria);
    }

    iniciarMonitoreoTiempo() {
        setInterval(() => {
            this.verificarTiempo();
        }, 1000);
    }

    verificarTiempo() {
        if (typeof window.segundosRestantesGlobal !== 'undefined') {
            const segs = window.segundosRestantesGlobal;
            const mins = Math.floor(segs / 60);

            // Anuncios cada 30 minutos mientras queden más de 30 minutos (ej. 120, 90, 60 min)
            if (segs > 1800 && segs % 1800 === 0 && !this.anunciosRealizados['min_' + mins]) {
                this.anunciosRealizados['min_' + mins] = true;
                this.hablar(`Atención participantes: Quedan ${mins} minutos de competencia.`);
            }

            // Anuncio exacto a los 30 minutos
            if (segs === 1800 && !this.anunciosRealizados['min_30']) {
                this.anunciosRealizados['min_30'] = true;
                this.hablar("¡Atención participantes! Faltan solo 30 minutos para finalizar el Hackathon.", 2);
            }

            // Anuncio exacto a los 20 minutos
            if (segs === 1200 && !this.anunciosRealizados['min_20']) {
                this.anunciosRealizados['min_20'] = true;
                this.hablar("¡Alerta de tiempo! Faltan 20 minutos para que se cierren los servidores.", 2);
            }

            // Anuncio exacto a los 10 minutos
            if (segs === 600 && !this.anunciosRealizados['min_10']) {
                this.anunciosRealizados['min_10'] = true;
                this.hablar("¡Atención! Faltan 10 minutos para concluir la competencia.", 3);
            }

            // Anuncio exacto a los 5 minutos
            if (segs === 300 && !this.anunciosRealizados['min_5']) {
                this.anunciosRealizados['min_5'] = true;
                this.hablar("¡Alerta crítica! Faltan solo 5 minutos. ¡Apresúrense en enviar sus banderas!", 3);
            }

            // Anuncio exacto al 1 minuto
            if (segs === 60 && !this.anunciosRealizados['min_1']) {
                this.anunciosRealizados['min_1'] = true;
                this.hablar("¡ÚLTIMO MINUTO! ¡Falta solo un minuto!", 4);
            }

            // Conteo regresivo final 10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0
            if (segs <= 10 && segs > 0 && !this.anunciosRealizados['count_' + segs]) {
                this.anunciosRealizados['count_' + segs] = true;
                this.hablar(`${segs}...`, 4);
            }

            if (segs === 0 && !this.anunciosRealizados['finalizado']) {
                this.anunciosRealizados['finalizado'] = true;
                this.hablar("¡Hackathon Finalizado! ¡Tiempo agotado!", 4);
            }
        }
    }
}

// Instanciar automáticamente al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    window.iaAvatarWidget = new IAAvatarWidget();
});
