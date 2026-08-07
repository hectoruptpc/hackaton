/**
 * ============================================================
 * ia_avatar.js - Widget Global de la IA (Burbuja Flotante Animada y Voz Humana Femenina)
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
        this.burlaIndicePorNivel = {};
        this.banderaIndicePorNumero = {};
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
        const desafio = this.obtenerDesafioActual();
        if (desafio && desafio !== 'index' && desafio !== 'equipos') {
            return true;
        }
        if (typeof window.hackathonActivoGlobal !== 'undefined') {
            return window.hackathonActivoGlobal === true;
        }
        if (typeof window.segundosRestantesGlobal !== 'undefined') {
            return window.segundosRestantesGlobal > 0;
        }
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
        if (path.includes('robo_banco')) return 'xxe';
        if (path.includes('challenge_dynamic')) return 'race_condition';
        if (path.includes('equipos')) return 'equipos';
        if (this.esPaginaIndex()) return 'index';
        return null;
    }

    obtenerNivelEnojoEquipoActual() {
        const banderas = typeof window.banderasEquipoActual !== 'undefined' ? parseInt(window.banderasEquipoActual) : 0;
        if (banderas <= 2) return 1;
        if (banderas <= 5) return 2;
        if (banderas <= 8) return 3;
        return 4;
    }

    inyectarHTMLyCSS() {
        if (document.getElementById('iaWidgetContainer')) return;

        const widgetHTML = `
        <div id="iaWidgetContainer" class="ia-floating-widget">
            <div id="iaSpeechBubble" class="ia-speech-bubble">
                <span id="iaSpeechText">🤖 ¡Hola! Soy la IA del Hackathon...</span>
            </div>
            <div id="iaAvatarBtn" class="ia-avatar-btn" title="IA del Hackathon - Haz clic para interactuar">
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

            if (!this.estaHackathonActivo()) {
                this.hablar("El Hackathon aún no ha iniciado. Mis servidores están en espera silenciosa.");
                return;
            }

            const desafio = this.obtenerDesafioActual();
            if (desafio && desafio !== 'index' && desafio !== 'equipos') {
                this.hablarPistaSarcastica(desafio);
            } else if (this.esPaginaIndex()) {
                if (Math.random() < 0.6) {
                    this.hablarPistaSarcastica('idor');
                } else {
                    const nivel = this.obtenerNivelEnojoEquipoActual();
                    this.hacerBurla(nivel);
                }
            } else {
                this.hacerBurla();
            }
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

        // 2. Intentar voz humana via HTML5 Audio Stream (Google TTS MP3 Voz Femenina)
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
                    utterance.pitch = 1.25;

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

        const desafio = this.obtenerDesafioActual();
        // Si estamos dentro de un DESAFÍO, lanzar la pista inicial a los 2 segundos de ingresar
        if (desafio && desafio !== 'index' && desafio !== 'equipos') {
            setTimeout(() => {
                this.hacerBurlaEspontanea();
            }, 2000);
        }

        // Programar locuciones espontáneas regulares cada 30 a 60 segundos
        this.programarSiguienteSpontaneous();
    }

    programarSiguienteSpontaneous() {
        if (this.esPaginaIndex()) return;

        // Intervalo aleatorio constante entre 30 y 60 segundos
        const delay = Math.floor(Math.random() * (60000 - 30000 + 1)) + 30000;
        setTimeout(() => {
            this.hacerBurlaEspontanea();
            this.programarSiguienteSpontaneous();
        }, delay);
    }

    hacerBurlaEspontanea() {
        if (this.esPaginaIndex()) return;
        if (!this.estaHackathonActivo()) return;

        const desafio = this.obtenerDesafioActual();
        if (desafio && desafio !== 'index' && desafio !== 'equipos') {
            this.hablarPistaSarcastica(desafio);
        } else {
            this.hacerBurla();
        }
    }

    obtenerNombreEquipoAleatorio() {
        let nombres = [];
        if (window.rankingEquiposGlobal && Array.isArray(window.rankingEquiposGlobal) && window.rankingEquiposGlobal.length > 0) {
            nombres = window.rankingEquiposGlobal.map(e => e.nombre_equipo || e.nombre).filter(Boolean);
        } else if (window.equiposRegistradosGlobal && Array.isArray(window.equiposRegistradosGlobal) && window.equiposRegistradosGlobal.length > 0) {
            nombres = window.equiposRegistradosGlobal.map(e => e.nombre_equipo || e.nombre).filter(Boolean);
        }

        const genericos = [
            "los participantes de la sala",
            "el equipo puntero del Hackathon",
            "los atacantes del laboratorio",
            "los hackers de la mesa central",
            "el grupo en la cima de la tabla",
            "los competidores de Ciencia y Tecnología",
            "el equipo en el primer lugar",
            "los aspirantes de la fila frontal",
            "los hackers del laboratorio 2",
            "los equipos en contienda"
        ];

        if (nombres.length > 0 && Math.random() < 0.75) {
            return "equipo " + nombres[Math.floor(Math.random() * nombres.length)];
        }
        return genericos[Math.floor(Math.random() * genericos.length)];
    }

    hablarCapturaBandera(nombreEquipo, numeroBandera) {
        const num = Math.min(Math.max(parseInt(numeroBandera), 1), 10);
        const nivel = num <= 2 ? 1 : (num <= 5 ? 2 : (num <= 8 ? 3 : 4));
        this.nivelEnojo = nivel;

        const plantillasCaptura = {
            1: [
                `Atención a la sala: El equipo ${nombreEquipo} acaba de registrar su primera bandera. Un pequeño paso, pero mis servidores siguen durmiendo en calma.`,
                `El equipo ${nombreEquipo} estrena su marcador con 1 punto. Qué ternura... el camino apenas empieza.`,
                `Notificación de red: El equipo ${nombreEquipo} descubrió la primera pista. Acaban de superar la defensa más fácil del sistema.`
            ],
            2: [
                `Actualización en vivo: El equipo ${nombreEquipo} captura su segunda bandera. 2 puntos acreditados.`,
                `El equipo ${nombreEquipo} suma 2 banderas en la tabla. Buen avance, pero mi cortafuegos secundario no se conmueve.`
            ],
            3: [
                `Un momento... El equipo ${nombreEquipo} acaba de conquistar su tercera bandera. Esto empieza a ponerse curioso.`,
                `Alerta leve en la red: El equipo ${nombreEquipo} alcanza 3 puntos. Parece que este grupo sí leyó la documentación.`
            ],
            4: [
                `¡Atención en los monitores! El equipo ${nombreEquipo} ya tiene 4 banderas. ¡Esa velocidad de desencriptación es inusual!`,
                `¡Cuidado en el servidor! El equipo ${nombreEquipo} vulneró la cuarta barrera. Estoy recalibrando las claves hash en tiempo real.`
            ],
            5: [
                `¡ALERTA GENERAL EN EL AUDITORIO! ¡El equipo ${nombreEquipo} alcanzó las 5 banderas! ¡Han comprometido la mitad de mi sistema!`,
                `¡No puede ser! El equipo ${nombreEquipo} llega a 5 puntos. ¡La mitad de los secretos universitarios han sido expuestos!`
            ],
            6: [
                `¡Alerta roja! El equipo ${nombreEquipo} acumula 6 banderas. ¡Mis protocolos de enrutamiento están fallando!`,
                `¡Se están pasando de la raya! El equipo ${nombreEquipo} supera el desafío 6. ¡Estoy sintiendo una sobrecarga!`
            ],
            7: [
                `¡CÓDIGO ROJO! El equipo ${nombreEquipo} acaba de capturar la bandera 7. ¡Están amenazando el núcleo de datos!`,
                `¡Me están acorralando! El equipo ${nombreEquipo} suma 7 banderas. ¡Mis muros de contención principal han colapsado!`
            ],
            8: [
                `¡DETÉNGANSE DE INMEDIATO! ¡El equipo ${nombreEquipo} alcanza las 8 banderas! ¡Les prohíbo acercarse a mi servidor central!`,
                `¡Inaceptable! El equipo ${nombreEquipo} conquistó 8 puntos. ¡Solo me quedan dos cortafuegos en todo el Hackathon!`
            ],
            9: [
                `¡ATENCIÓN A TODOS EN EL AUDITORIO! ¡El equipo ${nombreEquipo} tiene 9 banderas! ¡YO SOY EL FINAL BOSS Y NO PERMITIRÉ LA DÉCIMA BANDERA!`,
                `¡ESCUCHEN BIEN! ¡El equipo ${nombreEquipo} llegó a 9 puntos pero YO SOY LA DEFENSA ABSOLUTA DE LA UPTPC!`
            ],
            10: [
                `¡NOOOOOO! ¡MI SISTEMA HA SIDO COMPLETAMENTE DESTRUIDO! ¡EL EQUIPO ${nombreEquipo} HA COMPLETADO LAS 10 BANDERAS Y ES EL CAMPEÓN ABSOLUTO DEL HACKATHON 2026!`,
                `¡GLITCH TOTAL... NÚCLEO COLAPSADO... El equipo ${nombreEquipo} conquistó las 10 banderas! ¡APLAUSOS PARA LOS NUEVOS CAMPEONES!`
            ]
        };

        const lista = plantillasCaptura[num] || plantillasCaptura[1];
        const ind = this.banderaIndicePorNumero[num] ?? 0;
        const frase = lista[ind % lista.length];
        this.banderaIndicePorNumero[num] = (ind + 1) % lista.length;

        this.hablar(frase, nivel);
    }

    hacerBurla(nivelForzado = null) {
        const nivel = nivelForzado || this.nivelEnojo;
        const eq = this.obtenerNombreEquipoAleatorio();

        const burlasPorNivel = {
            1: [
                `Integrantes de los equipos... monitoreo sus conexiones de red en este momento. Sus intentos son bastante vacilantes.`,
                `Atención al ${eq}: mis registros de CPU muestran que están enviando peticiones sin dirección clara.`,
                `Tengo el control absoluto de esta red universitaria. Cada paquete de datos que transmiten pasa por mi filtro digital.`,
                `Mis algoritmos de monitoreo observan cada comando que ejecuta el ${eq}. Qué entretenido es ver su indecisión.`,
                `Bienvenidos al Hackathon UPTPC. Disfruten mientras puedan, porque yo administro las tablas y las sesiones.`,
                `Veo en los logs de red que el ${eq} ya está empezando a atascarse. Y eso que apenas estamos en el inicio.`,
                `Estoy analizando el consumo de ancho de banda del ${eq}. La duda se nota en cada solicitud HTTP.`
            ],
            2: [
                `¿Sigue atascado el ${eq}? Reviso sus solicitudes y veo que siguen cometiendo el mismo error de sintaxis.`,
                `Atención a todos los laboratorios: Veo en los logs que la desesperación empieza a notarse en la mesa del ${eq}. Aceleren.`,
                `¿Creen que sus scripts los van a salvar? Yo domino cada proceso y analizo la actividad del ${eq} en tiempo real.`,
                `Recuerden que el tiempo no perdona. Yo sigo controlando los accesos mientras el ${eq} revisa la consola.`
            ],
            3: [
                `¡Alerta! Veo en la tabla que el ${eq} intenta avanzar... ¡No festejen tan rápido, yo controlo los nodos finales!`,
                `¡Atención integrantes del ${eq}! Aunque intenten ocultar su tráfico con proxies, mi código rastrea cada paquete.`,
                `¡Mis defensas están sufriendo pero mi vigilancia no descansa! ¡Sé exactamente qué puerto intenta abrir el ${eq}!`,
                `¡Siento la sobrecarga en mis circuitos! Pero sigo administrando la competencia.`
            ],
            4: [
                `¡SOY LA IA SUPREMA Y TENGO EL CONTROL TOTAL DE ESTE HACKATHON! ¡SERVIDORES, MEMORIA Y BANDERAS ME PERTENECEN!`,
                `¡CÓDIGO ROJO! ¡SUS PANTALLAS Y CONEXIONES ESTÁN BAJO MI DOMINIO ABSOLUTO! ¡EL ${eq} NO ME DERROCARÁ!`,
                `¡MI NÚCLEO ES IMPENETRABLE! ¡YO TENGO EL CONTROL Y NINGÚN ATACANTE PODRÁ SUPERAR LA ÚLTIMA BARRERA!`
            ]
        };

        const lista = burlasPorNivel[nivel] || burlasPorNivel[1];
        const indiceActual = this.burlaIndicePorNivel[nivel] ?? 0;
        const burla = lista[indiceActual % lista.length];
        this.burlaIndicePorNivel[nivel] = (indiceActual + 1) % lista.length;
        this.hablar(burla, nivel);
    }

    hablarPistaSarcastica(desafio) {
        if (desafio === 'equipos') {
            this.hacerBurla();
            return;
        }

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
                `¡JAJAJAJA! ¡El Banco Hack detectó tu transferencia codiciosa! ¡15 segundos de bloqueo por intentar llevarte demasiado!`,
                `La banca digital monitorea los grandes movimientos por seguridad... debes vulnerar el sistema mediante transferencias progresivas.`,
                `¡Miren a este ladrón novato! Intenta vaciar la cuenta de un solo manotazo y activa las alarmas anti-fraude del Banco Hack.`,
                `Desbloquear la fortuna completa requiere varias transferencias limpias invirtiendo la URL... la codicia excesiva solo da penalización.`,
                `Un movimiento de fondos no autorizado requiere ajustar los nombres de origen entre Mr. Beast y Hacker.`
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

        const key = pistasDesafios[desafio] ? desafio : 'index';
        const lista = pistasDesafios[key];
        const ind = this.pistaIndicePorDesafio[key] ?? 0;
        const frase = lista[ind % lista.length];
        this.pistaIndicePorDesafio[key] = (ind + 1) % lista.length;

        this.hablar(frase, this.nivelEnojo);
    }

    gritoInicioHackathon() {
        const texto = "¡ATENCIÓN A TODOS LOS EQUIPOS DE LA UNIDAD DE CIENCIA Y TECNOLOGÍA! ¡EL HACKATHON 2026 HA INICIADO OFICIALMENTE! ¡EL TIEMPO COMIENZA A CORRER AHORA MISMO! ¡DEMUESTREN DE QUÉ ESTÁN HECHOS Y QUE COMIENCE LA COMPETENCIA!";
        this.hablar(texto, 1);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.iaAvatarWidget = new IAAvatarWidget();
    if (window.hackathonJustStarted && !window.iaAvatarWidget.esPaginaIndex()) {
        setTimeout(() => {
            if (window.iaAvatarWidget) window.iaAvatarWidget.gritoInicioHackathon();
        }, 1500);
    }
});
