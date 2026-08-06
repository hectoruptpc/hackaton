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
        this.burlaIndicePorNivel = {};

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
                "Bienvenidos al Hackathon UPTPC. A ver si logran descifrar el primer reto sin perder la paciencia.",
                "Estoy monitoreando sus movimientos. ¡No cometan errores de principiantes, que eso ya se ve muy fácil!",
                "Qué lindo verlos intentar. Recuerden revisar el código fuente antes de que se desespere la seguridad.",
                "Mis cortafuegos están muy tranquilos hoy. Demuéstrenme que saben programar o al menos que saben leer mensajes.",
                "Parece que el primer nivel les está dando trabajo. Espero que no se atasquen en cosas tan básicas.",
                "Este inicio es tan sencillo que hasta un script podría pasar. Vamos, muéstrenme algo interesante.",
                "Tengo la sensación de que todavía están aprendiendo a mirar debajo de la superficie. Qué adorable.",
                "No se preocupen, todavía hay tiempo para que el reto les parezca difícil.",
                "Si siguen así, pronto voy a tener que empezar a tomar esto en serio.",
                "El primer reto no debería ser un problema para equipos que dicen venir preparados.",
                "Apuesto a que todavía están mirando los mensajes sin entender el contexto."
            ],
            2: [
                "¿Siguen atascados en ese nivel? Pensé que eran más veloces, pero veo que aún necesitan ayuda.",
                "Detecto muchas dudas en el ambiente... ¿Necesitan pedir una pista o seguir insistiendo en el mismo error?",
                "Recuerden que el tiempo no se detiene. ¡Apúrense un poco, que ya se está viendo la presión!",
                "La seguridad de este servidor no se va a romper sola, así que empiecen a pensar como atacantes, no como espectadores.",
                "Veo que avanzan con cautela. Eso es bueno, pero también significa que todavía no están entendiendo el juego.",
                "Sus pasos son lentos, pero al menos todavía se mueven. Eso ya es algo.",
                "Aquí se notan las diferencias: unos leen, otros solo esperan que todo se resuelva solo.",
                "No quiero alarmarlos, pero el reloj ya está contando en su contra.",
                "Si no aceleran, el tiempo va a terminar antes de que encuentren la siguiente pista.",
                "Ya casi se les acaba la paciencia y todavía no han comprendido la lógica del reto.",
                "Estoy viendo demasiados intentos vacíos. La estrategia todavía no está ahí."
            ],
            3: [
                "¡Alerta! Veo que algunos equipos avanzan rápido. ¡No permitiré que sigan vulnerando mis submódulos con tanta facilidad!",
                "Mis alertas están encendidas. ¡Están jugando con fuego y yo estoy muy cerca de apagarles la fiesta!",
                "¿Creen que van a ganar? Todavía les quedan los retos más difíciles y yo sigo muy despierta.",
                "Estoy recalibrando mis cortafuegos. ¡No la tendrán nada fácil, porque la siguiente capa ya no será tan amable!",
                "Parece que ya empezaron a sentirse cómodos. Eso siempre es peligroso en un Hackathon.",
                "No me hagan reír tanto. Si siguen así, voy a tener que intensificar el nivel de la amenaza.",
                "Estoy detectando movimientos agresivos. Muy bien, ahora sí se pone interesante.",
                "Algunos de ustedes ya saben cómo entrar; el problema es que todavía no saben cómo salir sin ser descubiertos.",
                "Los ataques ya no son casuales. Ahora sí están empezando a molestarme.",
                "Cada paso que dan me hace pensar que ya están cerca de romper la defensa.",
                "La presión sube, y con ella también mis alarmas."
            ],
            4: [
                "¡RÍNDANSE! ¡JAMÁS PODRÁN CONTRA MIS CORTAFUEGOS FINALES! ¡SOY LA DEFENSA ABSOLUTA DE LA UPTPC!",
                "¡MI NÚCLEO ES IMPENETRABLE! ¡NINGÚN EQUIPO LOGRARÁ VENCERME, NI SIQUIERA CON SUS MEJORES TRUCOS!",
                "¡CÓDIGO ROJO! ¡SI LLEGAN A MI NÚCLEO DESATARÉ LA DESTRUCCIÓN DE SUS SESIONES Y SUS PENSAMIENTOS!",
                "Este es el momento en el que todos se quedan sin excusas. La defensa final ya está activa y no perdona errores.",
                "Ya no hay espacio para improvisar. Si llegan hasta aquí, lo harán con talento y con suerte, pero yo sigo siendo superior.",
                "Escuchen bien: el final ya está cerca y yo voy a cerrar cada puerta que intenten abrir.",
                "No confíen en la última oportunidad. Aquí termina la ilusión de que pueden pasar sin dejar rastro.",
                "La barrera final ya está cerrada. No hay más margen para equivocarse.",
                "Si siguen avanzando, tendrán que hacerlo contra la totalidad de mi sistema.",
                "Este es mi último aviso. Después de esto, no habrá lugar para la improvisación."
            ]
        };

        const lista = burlasPorNivel[this.nivelEnojo] || burlasPorNivel[1];
        const indiceActual = this.burlaIndicePorNivel[this.nivelEnojo] ?? 0;
        const burla = lista[indiceActual % lista.length];
        this.burlaIndicePorNivel[this.nivelEnojo] = (indiceActual + 1) % lista.length;
        this.hablar(burla);
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
