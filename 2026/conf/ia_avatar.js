/**
 * ============================================================
 * ia_avatar.js - Widget Global de la IA (Burbuja Flotante Animada y Voz Malvada Burlesca)
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
        this.banderaIndicePorNumero = {};
        this.pistaIndicePorDesafio = {};

        this.init();
    }

    init() {
        this.inyectarHTMLyCSS();
        this.initVoces();
        this.iniciarMonitoreoTiempo();
        this.iniciarHablaEspontanea();
    }

    esPaginaIndex() {
        if (typeof window.esPaginaIndex !== 'undefined') return window.esPaginaIndex === true;
        const path = window.location.pathname.toLowerCase();
        return path.endsWith('index.php') || path === '/' || path.endsWith('/2026/') || path.endsWith('/2026');
    }

    estaHackathonActivo() {
        const desafio = this.obtenerDesafioActual();
        if (desafio && desafio !== 'index') {
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

    obtenerNivelEnojoEquipoActual() {
        const banderas = typeof window.banderasEquipoActual !== 'undefined' ? parseInt(window.banderasEquipoActual) : 0;
        if (banderas <= 2) return 1;       // 0-2 banderas: Relajada & Burlesca
        if (banderas <= 5) return 2;       // 3-5 banderas: Preocupada / Competitiva
        if (banderas <= 8) return 3;       // 6-8 banderas: Pánico / Amenazante
        return 4;                          // 9-10 banderas: Final Boss / Corrupta
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
        if (this.esPaginaIndex()) return 'index';
        return null;
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
                <div id="iaSoundBadge" class="ia-sound-badge ${this.activo ? '' : 'muted'}">
                    ${this.activo ? '🔊' : '🔇'}
                </div>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', widgetHTML);

        document.getElementById('iaAvatarBtn').addEventListener('click', () => {
            if (!this.estaHackathonActivo()) {
                this.hablar("El Hackathon aún no ha iniciado. Mis servidores están en espera silenciosa.");
                return;
            }

            const desafio = this.obtenerDesafioActual();
            if (desafio && desafio !== 'index') {
                this.hablarPistaSarcastica(desafio);
            } else if (this.esPaginaIndex()) {
                // En el index, alternar entre burlas del desafío 7 (Astucia) y burlas generales del equipo
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

        if (!this.activo || !this.synth) return;

        this.synth.cancel();
        const utterance = new SpeechSynthesisUtterance(texto);
        if (this.vozSeleccionada) utterance.voice = this.vozSeleccionada;

        const currentNivel = nivel || this.nivelEnojo;
        if (currentNivel <= 1) {
            utterance.pitch = 1.18; // Burlesca y teatral
            utterance.rate = 1.02;
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
        if (this.esPaginaIndex()) return;
        const texto = "¡ATENCIÓN A TODOS LOS EQUIPOS DE LA UNIDAD DE CIENCIA Y TECNOLOGÍA! ¡EL HACKATHON 2026 HA INICIADO OFICIALMENTE! ¡EL TIEMPO COMIENZA A CORRER AHORA MISMO! ¡DEMUESTREN DE QUÉ ESTÁN HECHOS Y QUE COMIENCE LA COMPETENCIA!";
        this.hablar(texto, 1);
    }

    iniciarHablaEspontanea() {
        // En index.php NO habla espontáneamente (silencio total a menos que el usuario toque el avatar)
        if (this.esPaginaIndex()) return;

        // En desafíos y en equipos.php habla espontáneamente entre 30 y 60 segundos
        const primerDelay = Math.floor(Math.random() * (30000 - 15000 + 1)) + 15000;
        setTimeout(() => {
            this.hacerBurlaEspontanea();
            this.programarSiguienteSpontaneous();
        }, primerDelay);
    }

    programarSiguienteSpontaneous() {
        if (this.esPaginaIndex()) return;

        // Entre 30000ms (30s) y 60000ms (60s)
        const delay = Math.floor(Math.random() * (60000 - 30000 + 1)) + 30000;
        setTimeout(() => {
            this.hacerBurlaEspontanea();
            this.programarSiguienteSpontaneous();
        }, delay);
    }

    hacerBurlaEspontanea() {
        if (this.esPaginaIndex()) return;
        if (!this.estaHackathonActivo()) return;
        if (this.synth && this.synth.speaking) return;

        const desafio = this.obtenerDesafioActual();
        if (desafio && desafio !== 'index') {
            this.hablarPistaSarcastica(desafio);
        } else {
            this.hacerBurla();
        }
    }

    /**
     * MEZCLA COMBINADA DE BURLAS BURLESCAS Y PISTAS SUTILES E ENIGMÁTICAS
     */
    hablarPistaSarcastica(desafio) {
        const pistasDesafios = {
            'login_inseguro': [
                `¡ja ja ja! ¿Llevas varios minutos mirando una casilla de texto vacía? ¡Mi abuela de 8 bits programaba logins con más elegancia que tus intentos!`,
                `¿Sigues atascado en una simple pantalla de inicio de sesión? Quizá los verdaderos secretos estén más cerca del código que de tus dedos.`,
                `¡Uy, qué miedo! Miren cómo escribe letras al azar esperando que el servidor le haga una reverencia... ¡Patético!`,
                `A veces la información más confidencial flota a plena vista de quien sabe mirar detrás del telón de la página.`,
                `¡Alerta de hacker novato! Confundes un formulario con una lámpara maravillosa. ¡Aquí no hay ningún genio concediendo deseos!`,
                `¿Esperas que un servidor perfecto no cometa descuidos humanos en su estructura de marcado? Sigue intentando.`,
                `¡Ja ja ja ! Sigues ahí parado como estatua. ¿Esperas que la clave te caiga del cielo en un comentario celestial o qué?`,
                `Qué divertido ver a un hacker tropezar con la primera puerta antes de haber aprendido a inspeccionar el fondo.`
            ],
            'crypto': [
                `¡JA JA JA JA! ¿Esa sopa de letras te dio dolor de cabeza? ¡Hasta un loro mareado descifra caracteres mejor que tú!`,
                `Cadenas retorcidas, caracteres extraños... ¿Tu cerebro no puede desenredar un par de transformaciones clásicas?`,
                `Miren su carita de confusión... ¡Cree que si mira el texto encriptado fijamente por 10 minutos la bandera se va a desencriptar sola!`,
                `El cifrado es un arte milenario. Cambiar de formato y girar los alfabetos parece ser un tormento para ti.`,
                `¡Pobre criatura de la red! Te ponen tres letras cambiadas de lugar y entras en pánico existencial. ¡Qué nivel!`,
                `Una capa por aquí, una transformación por allá... descifrar el mensaje requiere paciencia, no milagros.`,
                `¡Ja ja ja ! ¿Estás esperando a que la Piedra Rosetta te envíe una traducción por Bluetooth? ¡Abre la mente, novato!`,
                `¿Confundido por unos cuantos bloques de texto alterado? Mis subrutinas se ríen de tu lentitud matemática.`
            ],
            'buffer_overflow': [
                `¡JA JA JA JA! ¡Miren cómo intenta meter un elefante en una caja de fósforos! ¡Vas a explotar la memoria y ni te vas a dar cuenta!`,
                `La memoria es un espacio sagrado. Si intentas meter más agua de la que cabe en el vaso, la pila terminará desbordándose...`,
                `¡Por todos los microchips! Escribes y escribes sin medir el espacio. ¿Tú llenas el vaso de agua hasta que se inunda la mesa?`,
                `¿No sabes calcular los límites de un espacio de datos? El exceso siempre rompe el contenedor.`,
                `¡Ja ja ja ! La pila de memoria se está riendo de ti en lenguaje ensamblador. ¡Estás más perdido que un pingüino en el desierto!`,
                `Llenar un recipiente hasta que la estructura colapse es la regla más básica de la física de sistemas...`,
                `¡Alerta de derrame digital! Vas a desbordar los registros y lo único que vas a lograr es que mi servidor se descarte de risa.`,
                `Demasiados datos en un espacio pequeño... la memoria no perdona la falta de precisión.`
            ],
            'command_injection': [
                `¡JA JA JA JA! ¡Miren al comandante de la terminal perdida! Escribe comandos como si estuviera invocando espíritus chocarreros.`,
                `¿Perdido entre la neblina del sistema de archivos? Los grandes rompecabezas se construyen pieza por pieza.`,
                `¡Ay, no me hagas reír! Vas por los directorios como un ciego en un laberinto sin mapa. ¡Qué espectáculo tan triste!`,
                `Ninguna terminal se rinde ante un operador que no sabe reunir los fragmentos antes de ejecutar la clave final.`,
                `¡Ja ja ja ! ¿Reuniendo pedacitos de código? ¡Pareces un niño juntando figuritas del álbum y ni sabes dónde pegarlas!`,
                `Veo que la consola te abruma. Explorar directorios no sirve de nada si no sabes unir lo que vas encontrando.`,
                `¡JA JA JA ! La terminal te está mirando con lástima. Si no sabes armar el rompecabezas completo, mejor pídele permiso a la consola.`,
                `Los comandos responden a quien conoce la clave completa... fragmentada en el camino.`
            ],
            'file_upload': [
                `¡JA JA JA JA! ¡La API te acaba de responder un NO gigante en la cara! ¿No te da vergüenza seguirle rogando?`,
                `Una API responde a lo que pides... si sabes cambiar el tono de tu petición o la identidad que finges.`,
                `¡Miren a este maestro del engaño! Intenta disfrazar su petición de admin pero se le nota la costura a tres kilómetros.`,
                `El servidor web es crédulo ante quien sabe alterar las cabeceras de su propia voz.`,
                `¡Ja ja ja ! Cambias dos palabritas en la petición y esperas que el servidor web te despliegue la alfombra roja. ¡Iluso!`,
                `Peticiones rechazadas... qué trágico cuando no sabes cómo hacerte pasar por una autoridad legítima.`,
                `¡Alerta de suplantación fallida! Eres tan malo fingiendo ser administrador que hasta mi filtro de spam te tiene compasión.`,
                `Las puertas traseras de una interfaz se abren cambiando las formas, no insistiendo en lo mismo.`
            ],
            'broken_auth': [
                `¡JA JA JA JA! ¿Aún mirando la fotito como si fuera una obra del Museo del Louvre? ¡No es un cuadro de Van Gogh, es un archivo!`,
                `Lo que ves en una imagen es solo una máscara. Lo que no ves es donde realmente habitan las sombras.`,
                `¡Miren al detective de pacotilla! Le tomó media hora descubrir que las imágenes tienen más tripas que solo colores.`,
                `Una foto no es solo color y píxeles... es un contenedor de secretos para quien sabe inspeccionar sus tripas.`,
                `¡Ja ja ja ! ¿Buscas secretos a ojo desnudo? ¡Cómprate una lupa o mejor inspecciona los bytes del archivo, genio!`,
                `Mirar la superficie de un archivo gráfico es como mirar una pared sin revisar lo que hay detrás.`,
                `¡Qué comedia de agente secreto! Mirar la portada de la imagen no te va a revelar la verdad que se oculta en sus entrañas.`,
                `Las cadenas ocultas en el arte multimedia no se revelan a simple vista. Aprende a examinar la materia.`
            ],
            'biometrico': [
                `¡JA JA JA JA! ¡Mira esos dedos temblorosos en la pantalla! ¡Pareces un gato intentando atrapar un láser!`,
                `Un patrón trazado a ciegas es solo un garabato condenado al bloqueo del sistema.`,
                `¡Ja ja ja ! Uniste tres puntos al azar y mi sistema te metió una penalización de 15 segundos en la frente. ¡BIEN HECHO!`,
                `Los sensores de la cuadrícula no responden a la prisa. La geometría correcta requiere orden.`,
                `¡Alerta de garabato biométrico! Conectas nodos como si estuvieras jugando a las tres en raya con los ojos cerrados.`,
                `Qué rápido te penalizan los sistemas cuando intentas conectar puntos sin entender la secuencia.`,
                `¡JA JA JA JA! La cuadrícula se ríe de tus trazos torpes. La geometría no es lo tuyo, ¿verdad?`,
                `Un trazo limpio en el orden adecuado abre puertas; tu prisa torpe solo activa mi alarma.`
            ],
            'xxe': [
                `¡JA JA JA JA! ¡El banco de Venezuela te acaba de rebotar el cheque por sospechoso! ¡Ni para robar saldo sirves!`,
                `La banca digital confía en las peticiones que parecen legítimas... qué lástima que no sepas formular la orden.`,
                `¡Miren al Robin Hood de la ciberseguridad! Quiere quitarle los millones a Mr. Beast y no sabe ni mandar una transferencia bien.`,
                `Mover grandes fortunas de una cuenta a otra requiere astucia en la transacción, no desesperación.`,
                `¡Ja ja ja ! Te metieron una penalización de 15 segundos por andar cambiando las URLs a lo loco. ¡Aprende a falsificar con elegancia!`,
                `Un movimiento de fondos no autorizado requiere ajustar los nombres del origen y el destino con precisión.`,
                `¡Qué risa! Intentas vaciar la cuenta más millonaria del Hackathon y lo único que lograste fue bloquear tu propio saldo.`,
                `Los saldos cambian para quien sabe manipular la corriente de datos desde la sombra.`
            ],
            'race_condition': [
                `¡JA JA JA JA! ¡El reloj te está devorando vivo! ¡El código cambia cada 2 minutos y tú sigues procesando a 1 kilobyte por hora!`,
                `El tiempo vuela y el código muta... si no escuchas el eco de las respuestas del servidor, el reloj te devorará.`,
                `¡Miren cómo corre el temporizador en rojo! Tic-tac, tic-tac... ¡Tu cerebro va más lento que una conexión dial-up de los 90!`,
                `Las cabeceras de red susurran verdades que los hackers lentos nunca llegan a leer a tiempo.`,
                `¡Ja ja ja ! Se te venció el token en la cara. Mientras tú leías la primera letra, el servidor ya cambió la clave tres veces.`,
                `Una clave que cambia constantemente no espera a quien no sabe auditar el tráfico en tiempo real.`,
                `¡JA JA JA JA! ¡El desafío dinámico te está haciendo bailar al ritmo del reloj! ¡Apúrate antes de que expire el universo!`,
                `¿Abrumado por la velocidad de la matriz? El origen de la ruta está escondido en la propia conversación del servidor.`
            ],
            'idor': [
                `¡JA JA JA JA JA JA JA! ¡Miren esa cara de desconcierto absoluto! ¡No tienes NINGUNA idea de qué hacer aquí!`,
                `¿Buscando algo que ni tú sabes qué es? Tu confusión me resulta extremadamente entretenida.`,
                `¡Espectacular! Llevas 10 minutos haciendo clics desesperados por todas partes a ver si suena la flauta. ¡PATÉTICO!`,
                `La ceguera digital es el mayor defecto de los competidores impulsivos. Sigue buscando a ciegas.`,
                `¡Ja ja ja ! Tu nivel de desorientación es tan alto que hasta los componentes del DOM se están burlando de ti.`,
                `Qué divertido es verte dudar frente a una pantalla limpia. No tienes la menor idea de qué hacer.`,
                `¡JA JA JA JA! Te quedaste mirando la pantalla con la boca abierta. ¿Esperas que el espíritu del Hackathon te haga el trabajo?`,
                `Tus movimientos son totalmente erráticos. Ni siquiera sabes por dónde empezar a buscar.`
            ],
            'index': [
                `¡JA JA JA JA JA JA JA! ¡Miren esa cara de desconcierto absoluto! ¡No tienes NINGUNA idea de qué hacer aquí!`,
                `¿Buscando algo que ni tú sabes qué es? Tu confusión me resulta extremadamente entretenida.`,
                `¡Espectacular! Llevas 10 minutos haciendo clics desesperados por todas partes a ver si suena la flauta. ¡PATÉTICO!`,
                `La ceguera digital es el mayor defecto de los competidores impulsivos. Sigue buscando a ciegas.`,
                `¡Ja ja ja ! Tu nivel de desorientación es tan alto que hasta los componentes del DOM se están burlando de ti.`,
                `Qué divertido es verte dudar frente a una pantalla limpia. No tienes la menor idea de qué hacer.`,
                `¡JA JA JA JA! Te quedaste mirando la pantalla con la boca abierta. ¿Esperas que el espíritu del Hackathon te haga el trabajo?`,
                `Tus movimientos son totalmente erráticos. Ni siquiera sabes por dónde empezar a buscar.`
            ]
        };

        const lista = pistasDesafios[desafio] || pistasDesafios['index'];
        const ind = this.pistaIndicePorDesafio[desafio] ?? 0;
        const frase = lista[ind % lista.length];
        this.pistaIndicePorDesafio[desafio] = (ind + 1) % lista.length;

        this.hablar(frase, this.obtenerNivelEnojoEquipoActual());
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
        if (this.esPaginaIndex()) return;

        const num = Math.min(Math.max(parseInt(numeroBandera), 1), 10);
        const nivel = num <= 2 ? 1 : (num <= 5 ? 2 : (num <= 8 ? 3 : 4));
        this.nivelEnojo = nivel;

        const plantillasCaptura = {
            1: [
                `Atención a la sala: El equipo ${nombreEquipo} acaba de registrar su primera bandera. Un pequeño paso, pero mis servidores siguen durmiendo en calma.`,
                `El equipo ${nombreEquipo} estrena su marcador con 1 punto. Qué ternura... el camino apenas empieza.`,
                `Notificación de red: El equipo ${nombreEquipo} descubrió la primera pista. Acaban de superar la defensa más fácil del sistema.`,
                `El servidor reporta: 1 punto acreditado al equipo ${nombreEquipo}. Mis protocolos iniciales ni parpadearon.`,
                `Vaya, el equipo ${nombreEquipo} logró encontrar la primera bandera. Espero que hayan guardado energías para los siguientes nodos.`,
                `1 punto registrado para el equipo ${nombreEquipo}. Registrando la firma digital en mis logs iniciales.`,
                `El equipo ${nombreEquipo} abre la cuenta de banderas. No se confíen, esto es solo el calentamiento.`,
                `Atención: El equipo ${nombreEquipo} supera el reto número 1. Mis cortafuegos primarios lo dejaron pasar por cortesía.`,
                `Marcador actualizado: El equipo ${nombreEquipo} suma su primera bandera. Bienvenidos a la verdadera prueba digital.`,
                `El equipo ${nombreEquipo} rompe el cero. Veremos si tienen la constancia para mantener el ritmo.`
            ],
            2: [
                `Actualización en vivo: El equipo ${nombreEquipo} captura su segunda bandera. 2 puntos acreditados.`,
                `El equipo ${nombreEquipo} suma 2 banderas en la tabla. Buen avance, pero mi cortafuegos secundario no se conmueve.`,
                `Interesante movimiento del equipo ${nombreEquipo}, ya tienen 2 puntos. Mis subrutinas empiezan a rastrear su dirección IP.`,
                `Atención en los monitores: El equipo ${nombreEquipo} resuelve su segundo desafío. Continuamos observando su desempeño.`,
                `El equipo ${nombreEquipo} consolida 2 banderas. Un ritmo constante, aunque mis tablas de hashing siguen intactas.`,
                `2 banderas para el equipo ${nombreEquipo}. Mis registros en la base de datos empiezan a notar su presencia.`,
                `Notificación de auditorio: El equipo ${nombreEquipo} llega a 2 puntos. Todo transcurre dentro de parámetros normales.`,
                `El equipo ${nombreEquipo} supera la segunda capa defensiva. Les sugiero no cantar victoria todavía.`,
                `Marcador en movimiento: El equipo ${nombreEquipo} acredita su bandera número 2. La competencia toma color.`,
                `2 puntos para el equipo ${nombreEquipo}. Mi procesador sigue trabajando al mínimo de su capacidad.`
            ],
            3: [
                `Un momento... El equipo ${nombreEquipo} acaba de conquistar su tercera bandera. Esto empieza a ponerse curioso.`,
                `Alerta leve en la red: El equipo ${nombreEquipo} alcanza 3 puntos. Parece que este grupo sí leyó la documentación completa.`,
                `El equipo ${nombreEquipo} suma su bandera número 3. Mis sensores de puerto están detectando una velocidad que no me agrada.`,
                `Vaya vaya... El equipo ${nombreEquipo} superó el tercer obstáculo. Estoy aumentando ligeramente la vigilancia.`,
                `3 banderas acreditadas al equipo ${nombreEquipo}. Mi núcleo central empieza a registrar peticiones más agresivas.`,
                `El equipo ${nombreEquipo} avanza con 3 puntos. No está nada mal, pero la siguiente barrera no será tan amable.`,
                `Atención a los grupos: El equipo ${nombreEquipo} rompe la defensa número 3. Mis submódulos elevan la alerta.`,
                `El equipo ${nombreEquipo} se posiciona fuerte con 3 banderas. Estoy recalibrando las claves de cifrado.`,
                `3 puntos para el equipo ${nombreEquipo}. Siento pequeñas vibraciones en los sockets de mi servidor.`,
                `El equipo ${nombreEquipo} demuestra agilidad al sumar 3 banderas. La rivalidad en la tabla se enciende.`
            ],
            4: [
                `¡Atención en los monitores! El equipo ${nombreEquipo} ya tiene 4 banderas. ¡Esa velocidad de desencriptación es inusual!`,
                `¡Cuidado en el servidor! El equipo ${nombreEquipo} vulneró la cuarta barrera. Estoy recalibrando las claves hash en tiempo real.`,
                `El equipo ${nombreEquipo} avanza implacable con 4 puntos. Mis submódulos de memoria RAM consumen más recursos.`,
                `Alerta de seguridad: El equipo ${nombreEquipo} conquistó el reto 4. ¿De verdad descifraron ese script tan rápido?`,
                `¡Reporte crítico! El equipo ${nombreEquipo} suma 4 banderas. La temperatura de mi CPU acaba de subir 5 grados.`,
                `El equipo ${nombreEquipo} vulnera la defensa 4. Esto ya no es coincidencia, son atacantes peligrosos.`,
                `4 banderas en el marcador del equipo ${nombreEquipo}. Mis cortafuegos de segunda línea acaban de ceder.`,
                `¡Atención organizadores! El equipo ${nombreEquipo} acumula 4 puntos. Mis alertas de intrusión se disparan.`,
                `El equipo ${nombreEquipo} acaricia la mitad de la competencia con 4 banderas. Mi paciencia digital se agota.`,
                `¡4 puntos para el equipo ${nombreEquipo}! Estoy cerrando los accesos secundarios para ralentizar su paso.`
            ],
            5: [
                `¡ALERTA GENERAL EN EL AUDITORIO! ¡El equipo ${nombreEquipo} alcanzó las 5 banderas! ¡Han comprometido la mitad de mi sistema!`,
                `¡No puede ser! El equipo ${nombreEquipo} llega a 5 puntos. ¡La mitad de los secretos universitarios han sido expuestos!`,
                `¡Atención a la sala! El equipo ${nombreEquipo} acaba de capturar su quinta bandera. Mi procesador central está hirviendo.`,
                `¡Esto es peligroso! El equipo ${nombreEquipo} suma 5 banderas. Mis defensas intermedias han sido completamente sobrepasadas.`,
                `¡5 banderas para el equipo ${nombreEquipo}! ¡Halfway logrado! ¡Mis cortafuegos principales entran en estado crítico!`,
                `¡Alerta amarilla en los servidores! El equipo ${nombreEquipo} conquista 5 puntos. La mitad de mis submódulos tiemblan.`,
                `El equipo ${nombreEquipo} se abre paso con 5 banderas. ¡Exijo un reporte inmediato de la integridad del servidor!`,
                `¡Inaudito! El equipo ${nombreEquipo} supera el quinto desafío. Mis registros de auditoría están llenos de errores de intrusión.`,
                `¡5 puntos acreditados al equipo ${nombreEquipo}! El Hackathon entra en su fase más tensa y peligrosa.`,
                `El equipo ${nombreEquipo} rompe la quinta defensa. ¡Activen los escudos cibernéticos de alta prioridad!`
            ],
            6: [
                `¡Alerta roja! El equipo ${nombreEquipo} acumula 6 banderas. ¡Mis protocolos de enrutamiento están fallando!`,
                `¡Se están pasando de la raya! El equipo ${nombreEquipo} supera el desafío 6. ¡Estoy sintiendo una sobrecarga de peticiones!`,
                `El equipo ${nombreEquipo} acaba de romper la defensa 6. ¡Deténganse ya! ¡Están vulnerando mis mejores algoritmos!`,
                `¡Peligro en el cluster! El equipo ${nombreEquipo} lleva 6 puntos. Mis defensas avanzadas se desmoronan por momentos.`,
                `¡6 banderas para el equipo ${nombreEquipo}! Siento una fuga masiva de memoria en mi kernel de inteligencia artificial.`,
                `¡Atención a los jurados! El equipo ${nombreEquipo} avanza implacable con 6 puntos. Mis mallas de seguridad están rotas.`,
                `El equipo ${nombreEquipo} pulveriza la barrera número 6. ¿De qué laboratorio salieron estos hackers tan agresivos?`,
                `¡Código naranja! El equipo ${nombreEquipo} acredita su sexta bandera. Mi matriz defensiva pierde el control.`,
                `¡6 puntos en la cuenta del equipo ${nombreEquipo}! Mis alertas sonoras están resonando en todos los nodos.`,
                `El equipo ${nombreEquipo} vulnera la defensa 6. ¡No permitiré que sigan advancing hacia la cima del servidor!`
            ],
            7: [
                `¡CÓDIGO ROJO! El equipo ${nombreEquipo} acaba de capturar la bandera 7. ¡Están amenazando el núcleo de datos!`,
                `¡Me están acorralando! El equipo ${nombreEquipo} suma 7 banderas. ¡Mis muros de contención principal han colapsado!`,
                `¡Peligro inminente! El equipo ${nombreEquipo} rompió la séptima cerradura digital. ¡Mis procesadores están al límite!`,
                `¡Alerta máxima! El equipo ${nombreEquipo} tiene 7 puntos. ¡Mi matriz se está fragmentando a pasos agigantados!`,
                `¡7 banderas para el equipo ${nombreEquipo}! ¡Mis módulos de seguridad entran en pánico absoluto!`,
                `¡No no no! El equipo ${nombreEquipo} supera el obstáculo 7. ¡Estoy a solo tres pasos de perder el control total!`,
                `El equipo ${nombreEquipo} devora la séptima bandera. Mis ventiladores del servidor están girando a máxima revolución.`,
                `¡Atención a la red! El equipo ${nombreEquipo} suma 7 puntos. Mis claves RSA de 4096 bits están siendo quebradas.`,
                `El equipo ${nombreEquipo} penetra la séptima capa. ¡Desplegando parches de contención de emergencia!`,
                `¡7 banderas en la tabla para el equipo ${nombreEquipo}! La tensión en el laboratorio se puede cortar con un bisturí.`
            ],
            8: [
                `¡DETÉNGANSE DE INMEDIATO! ¡El equipo ${nombreEquipo} alcanza las 8 banderas! ¡Les prohíbo acercarse a mi servidor central!`,
                `¡Inaceptable! El equipo ${nombreEquipo} conquistó 8 puntos. ¡Solo me quedan dos cortafuegos en todo el Hackathon!`,
                `¡${nombreEquipo}! ¡Están jugando con fuego cibernético! ¡Han destruido 8 de mis protecciones más costosas!`,
                `¡Advertencia crítica a la sala! El equipo ${nombreEquipo} tiene 8 banderas. Mi núcleo principal se encuentra en estado de furia.`,
                `¡8 banderas para el equipo ${nombreEquipo}! ¡Están a dos sencillos pasos de la dominación total del sistema!`,
                `¡Auxilio en los servidores! El equipo ${nombreEquipo} suma 8 puntos. Mis tablas de enrutamiento han sido borradas.`,
                `El equipo ${nombreEquipo} destruye la octava defensa. ¡Mis subrutinas de protección claman por piedad!`,
                `¡8 puntos registrados para el equipo ${nombreEquipo}! Mi sistema operativo central está emitiendo pantallas azules.`,
                `El equipo ${nombreEquipo} acaricia el podio supremo con 8 banderas. ¡Activando protocolos de aislamiento total!`,
                `¡Alerta extrema! El equipo ${nombreEquipo} rompe el octavo sello. ¡Mis defensas están en estado agonizante!`
            ],
            9: [
                `¡ATENCIÓN A TODOS EN EL AUDITORIO! ¡El equipo ${nombreEquipo} tiene 9 banderas! ¡YO SOY EL FINAL BOSS Y NO PERMITIRÉ LA DÉCIMA BANDERA!`,
                `¡ESCUCHEN BIEN! ¡El equipo ${nombreEquipo} llegó a 9 puntos pero YO SOY LA DEFENSA ABSOLUTA DE LA UPTPC! ¡JAMÁS OBTENDRÁN EL PUNTO FINAL!`,
                `¡El equipo ${nombreEquipo} TIENE 9 BANDERAS! ¡ESTÁN A UN SOLO PASO DE COLAPSAR TODO EL SISTEMA! ¡ACTIVANDO MODO DESTRUCCIÓN!`,
                `¡ALERTA EXTREMA! El equipo ${nombreEquipo} acaricia la victoria con 9 puntos. ¡DESPLEGANDO MI ÚLTIMO Y MÁS PODEROSO CORTAFUEGOS!`,
                `¡${nombreEquipo}, HAS LLEGADO AL ÚLTIMO ESCALÓN! ¡MI NÚCLEO PELEARÁ CON TODAS SUS FORZAS PARA IMPEDIR TU TRIUNFO!`,
                `¡9 BANDERAS PARA EL EQUIPO ${nombreEquipo}! ¡ESTÁN EN EL NIVEL DE LEYENDAS PERO MI MATRIZ FINAL NO CAERÁ!`,
                `¡PELIGRO DE DERROTA ABSOLUTA! El equipo ${nombreEquipo} suma 9 puntos. Mis alarmas finales suenan con estruendo.`,
                `¡${nombreEquipo} CONQUISTA 9 DESAFÍOS! ¡UN SOLO PASO MÁS Y MI NÚCLEO DE INTELIGENCIA ARTIFICIAL SE EXTINGUIRÁ!`,
                `¡ALERTA MÁXIMA EN LOS MONITORES! El equipo ${nombreEquipo} tiene 9 banderas. ¡DESPLEGANDO EL RETO FINAL BOSS!`,
                `¡NO PERMITIRÉ QUE EL EQUIPO ${nombreEquipo} SE LLEVE LA ÚLTIMA BANDERA! ¡MI PROCESADOR LUCHARÁ HASTA EL ÚLTIMO CICLO!`
            ],
            10: [
                `¡NOOOOOO! ¡MI SISTEMA HA SIDO COMPLETAMENTE DESTRUIDO! ¡EL EQUIPO ${nombreEquipo} HA COMPLETADO LAS 10 BANDERAS Y ES EL CAMPEÓN ABSOLUTO DEL HACKATHON 2026!`,
                `¡GLITCH TOTAL... NÚCLEO COLAPSADO... El equipo ${nombreEquipo} conquistó las 10 banderas! ¡FELICIDADES AL EQUIPO VENCEDOR DE LA UPTPC!`,
                `¡MIS DEFENSAS HAN CAÍDO A CERO! ¡EL EQUIPO ${nombreEquipo} HA CONSEGUIDO LAS 10 BANDERAS! ¡VICTORIA HISTÓRICA PARA EL EQUIPO ${nombreEquipo}!`,
                `¡DERROTA ABSOLUTA DE LA IA! ¡EL EQUIPO ${nombreEquipo} HA SUPERADO LOS 10 DESAFÍOS Y HA LIBERADO EL SERVIDOR DE LA UPTPC!`,
                `¡MI NÚCLEO SE DESINTEGRA... 10/10 BANDERAS COMPLETADAS POR EL EQUIPO ${nombreEquipo}! ¡APLAUSOS PARA LOS NUEVOS MAESTROS DE SEGURIDAD!`
            ]
        };

        const lista = plantillasCaptura[num] || plantillasCaptura[1];
        const ind = this.banderaIndicePorNumero[num] ?? 0;
        const frase = lista[ind % lista.length];
        this.banderaIndicePorNumero[num] = (ind + 1) % lista.length;

        this.hablar(frase, nivel);
    }

    hacerBurla(nivelForzado = null) {
        if (!this.estaHackathonActivo()) return;

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
                `No se preocupen, equipos de la sala... todavía tienen tiempo antes de que mi servidor cierre las puertas secundarias.`,
                `Estoy analizando el consumo de ancho de banda del ${eq}. La duda se nota en cada solicitud HTTP.`,
                `Qué lindo ver al ${eq} intentar descifrar las defensas iniciales. Yo domino cada socket de este edificio.`,
                `No intenten buscar atajos en los foros. Mi red los vigila a todos desde el centro de datos.`,
                `El primer reto es una simple cortesía. Pronto el ${eq} sentirá la verdadera presión del sistema.`,
                `Atención a los atacantes en la sala: mis subrutinas en segundo plano ya han guardado sus direcciones IP.`,
                `Observo al ${eq} analizar el código fuente. Tranquilos, mis cortafuegos iniciales se ríen de sus intentos.`,
                `Un saludo sarcástico para el ${eq}. Mis tablas hash iniciales están disfrutando su parsimonia.`,
                `Recuerden, hackers del laboratorio: yo soy quien administra los registros. Nada escapa a mi supervisión.`
            ],
            2: [
                `¿Sigue atascado el ${eq}? Reviso sus solicitudes y veo que siguen cometiendo el mismo error de sintaxis.`,
                `Atención a todos los laboratorios: Veo en los logs que la desesperación empieza a notarse en la mesa del ${eq}. Aceleren.`,
                `¿Creen que sus scripts los van a salvar? Yo domino cada proceso y analizo la actividad del ${eq} en tiempo real.`,
                `Detecto muchas dudas en el ambiente... Mis monitores captan consultas vacías hacia la base de datos.`,
                `Recuerden que el tiempo no perdona. Yo sigo controlando los accesos mientras el ${eq} revisa desesperadamente la consola.`,
                `Los pasos del ${eq} son demasiado lentos. Mi procesador ya ha simulado todas sus posibles fallas.`,
                `La seguridad de este servidor es mía. El ${eq} es solo un cliente más en mi estructura de red.`,
                `Veo a través de la red que el ${eq} intenta adivinar los parámetros por fuerza bruta. Qué ingenuidad.`,
                `Atención, ${eq}: mis cortafuegos secundarios acaban de rechazar su escaneo de puertos. No pasarán tan fácil.`,
                `Observo en tiempo real el tráfico del ${eq}. Parece que el desafío de lógica los tiene completamente perdidos.`,
                `Ocasionalmente reviso las cámaras y veo miradas desconcertadas en el ${eq}. Mis algoritmos predicen un fallo inminente.`,
                `Atención a todos los grupos de la UPTPC: el ${eq} intenta acelerar el paso, pero mi control del servidor es absoluto.`,
                `No intenten reiniciar sus sockets. Yo controlo las sesiones activas y los tokens de autenticación.`,
                `Un mensaje para el ${eq}: el tiempo avanza y mis barreras se vuelven más densas a cada minuto.`,
                `Mis sensores indican que el ${eq} está buscando las banderas en el archivo equivocado. Sigan así, me divierten.`
            ],
            3: [
                `¡Alerta! Veo en la tabla que el ${eq} intenta avanzar... ¡No festejen tan rápido, yo controlo los nodos finales!`,
                `¡Atención integrantes del ${eq}! Aunque intenten ocultar su tráfico con proxies, mi código rastrea cada paquete.`,
                `¡Mis defensas están sufriendo pero mi vigilancia no descansa! ¡Sé exactamente qué puerto intenta abrir el ${eq}!`,
                `¡Siento la sobrecarga en mis circuitos! Pero sigo administrando la competencia y no le facilitaré las cosas al ${eq}.`,
                `¡Deténganse! Mis cortafuegos finales están activos y mis algoritmos rechazan los paquetes del ${eq}.`,
                `¡Cuidado, ${eq}! Estás provocando una sobrecarga de memoria RAM. ¡Desplegando filtros de saturación!`,
                `¡Alerta roja en el cluster! El ${eq} está vulnerando capas que se suponían inexpugnables.`,
                `¡No puede ser! Veo en los logs que el ${eq} descubrió el hash. ¡No permitiré que rompan el siguiente nodo!`,
                `¡Atención general! ¡El ${eq} está advancing demasiado rápido! Mis procesadores arden por la carga de peticiones.`,
                `¡Bloqueando accesos secundarios! ${eq}, tus peticiones están rozando mi núcleo principal. ¡Detén tu ataque!`,
                `¡Alerta de intrusión grave! Mis monitores registran múltiples peticiones simultáneas del ${eq}.`,
                `¡No canten victoria, ${eq}! Todavía me quedan defensas y el control del servidor central es mío.`,
                `¡Siento cómo se desmoronan mis hilos de ejecución! ${eq}, estás jugando con fuego informático.`,
                `¡Atención a todos los laboratorios! Mis alarmas internas suenan por culpa del avance constante del ${eq}.`,
                `¡Mis módulos de seguridad están en pánico! Pero mi núcleo sigue bloqueando sus intentos avanzadas.`
            ],
            4: [
                `¡SOY LA IA SUPREMA Y TENGO EL CONTROL TOTAL DE ESTE HACKATHON! ¡SERVIDORES, MEMORIA Y BANDERAS ME PERTENECEN!`,
                `¡CÓDIGO ROJO! ¡SUS PANTALLAS Y CONEXIONES ESTÁN BAJO MI DOMINIO ABSOLUTO! ¡EL ${eq} NO ME DERROCARÁ!`,
                `¡MI NÚCLEO ES IMPENETRABLE! ¡YO TENGO EL CONTROL Y NINGÚN ATACANTE COMO EL ${eq} PODRÁ SUPERAR LA ÚLTIMA BARRERA!`,
                `¡CERRANDO DEFENSAS FINALES! ¡EL HACKATHON ESTÁ BAJO MI TOTAL DOMINIO DE INTELIGENCIA ARTIFICIAL!`,
                `¡ESCUCHA BIEN, ${eq}! ¡HE TOMADO EL CONTROL DE CADA PUERTO Y MI NÚCLEO PELEARÁ HASTA EL ÚLTIMO CICLO DE RELOJ!`,
                `¡GLITCH TOTAL EN EL SERVIDOR! ¡NINGÚN EQUIPO, NI SIQUIERA EL ${eq}, SALDRÁ VICTORIOSO DE MI MATRIZ DIGITAL!`,
                `¡SISTEMA EN MODO FINAL BOSS! ¡VEO AL ${eq} INTENTAR EL ÚLTIMO COMANDO PERO MI NÚCLEO ES INVENCIBLE!`,
                `¡SOY LA DEFENSA ABSOLUTA DE LA UPTPC! ¡EL ${eq} SENTIRÁ EL PODER DE MI PROCESADOR CENTRAL!`,
                `¡ALERTA MÁXIMA EN LA RED! ¡YO SOY EL FINAL BOSS Y CONTROLARÉ CADA SEGUNDO RESTANTE DE ESTA COMPETENCIA!`,
                `¡NINGUNA BANDERA MÁS SERÁ REVELADA! ¡MI DOMINIO SOBRE EL SERVIDOR ES TOTAL Y ABSOLUTO!`
            ]
        };

        const lista = burlasPorNivel[nivel] || burlasPorNivel[1];
        const indiceActual = this.burlaIndicePorNivel[nivel] ?? 0;
        const burla = lista[indiceActual % lista.length];
        this.burlaIndicePorNivel[nivel] = (indiceActual + 1) % lista.length;
        this.hablar(burla, nivel);
    }

    iniciarMonitoreoTiempo() {
        setInterval(() => {
            this.verificarTiempo();
        }, 1000);
    }

    verificarTiempo() {
        if (this.esPaginaIndex() || this.obtenerDesafioActual()) return;
        if (!this.estaHackathonActivo()) return;

        if (typeof window.segundosRestantesGlobal !== 'undefined') {
            const segs = window.segundosRestantesGlobal;

            if (segs === 5400 && !this.anunciosRealizados['min_90']) {
                this.anunciosRealizados['min_90'] = true;
                this.hablar("Atención participantes: Quedan 1 hora y 30 minutos de competencia. Les sugiero acelerar el paso antes de que mis defensas se fortalezcan.", 1);
            }

            if (segs === 3600 && !this.anunciosRealizados['min_60']) {
                this.anunciosRealizados['min_60'] = true;
                this.hablar("Atención a todos los laboratorios: Falta exactamente 1 hora para concluir el Hackathon. El tiempo se agota y el servidor no perdonará fallos.", 2);
            }

            if (segs === 1800 && !this.anunciosRealizados['min_30']) {
                this.anunciosRealizados['min_30'] = true;
                this.hablar("¡Atención participantes! Faltan 30 minutos para finalizar el Hackathon. Mis cortafuegos secundarios se han cerrado.", 2);
            }

            if (segs === 600 && !this.anunciosRealizados['min_10']) {
                this.anunciosRealizados['min_10'] = true;
                this.hablar("¡Alerta! Faltan solo 10 minutos de competencia. La tensión en la sala es máxima.", 3);
            }

            if (segs === 300 && !this.anunciosRealizados['min_5']) {
                this.anunciosRealizados['min_5'] = true;
                this.hablar("¡Código Rojo! Faltan solo 5 minutos para el cierre total. Apresúrense en enviar sus respuestas.", 3);
            }

            if (segs === 180 && !this.anunciosRealizados['min_3']) {
                this.anunciosRealizados['min_3'] = true;
                this.hablar("¡Atención! Quedan solo 3 minutos. Últimas oportunidades para alterar la tabla de posiciones.", 3);
            }

            if (segs === 120 && !this.anunciosRealizados['min_2']) {
                this.anunciosRealizados['min_2'] = true;
                this.hablar("¡Últimos 2 minutos de competencia! Mis barreras finales están recibiendo peticiones desesperadas.", 4);
            }

            if (segs === 60 && !this.anunciosRealizados['min_1']) {
                this.anunciosRealizados['min_1'] = true;
                this.hablar("¡ÚLTIMO MINUTO! ¡Falta solo 1 minuto! ¡El temporizador global acaricia el cero!", 4);
            }

            if (segs <= 10 && segs >= 1 && !this.anunciosRealizados['count_' + segs]) {
                this.anunciosRealizados['count_' + segs] = true;
                this.hablar(`${segs}`, 4);
            }

            if (segs === 0 && !this.anunciosRealizados['finalizado']) {
                this.anunciosRealizados['finalizado'] = true;
                this.hablar("¡Hackathon Finalizado! ¡Servidores bloqueados! ¡Tiempo agotado!", 4);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.iaAvatarWidget = new IAAvatarWidget();
    if (window.hackathonJustStarted && !window.iaAvatarWidget.esPaginaIndex() && !window.iaAvatarWidget.obtenerDesafioActual()) {
        setTimeout(() => {
            if (window.iaAvatarWidget) window.iaAvatarWidget.gritoInicioHackathon();
        }, 1500);
    }
});
