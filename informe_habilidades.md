# Informe de Habilidades - Hackathon 2026

## Resumen ejecutivo
Este informe presenta una visión técnica y actualizada del Hackathon 2026, alineada con los retos que se encuentran implementados en el proyecto. La edición 2026 integra competencias en seguridad web, criptografía, explotación de vulnerabilidades, análisis forense y razonamiento lógico.

---

## Catálogo de desafíos implementados

### 1. Login Inseguro
- **Categoría:** Seguridad Web
- **Descripción:** Este reto evalúa la capacidad del participante para identificar fallos de autenticación y explotar debilidades en la lógica de acceso del sistema.
- **Habilidades requeridas:** análisis de código, evaluación de mecanismos de autenticación, manipulación de formularios y bypass de validaciones básicas.

### 2. Criptografía
- **Categoría:** Criptografía
- **Descripción:** El participante debe descifrar un mensaje oculto mediante técnicas de análisis, transformación de datos y comprensión de estructuras criptográficas elementales.
- **Habilidades requeridas:** decodificación, reconocimiento de patrones, lógica de cifrado y análisis criptográfico básico.

### 3. Buffer Overflow
- **Categoría:** Exploitation / Binary Security
- **Descripción:** Este desafío simula una vulnerabilidad de desbordamiento de buffer y exige la manipulación de entradas para alterar el flujo de ejecución esperado.
- **Habilidades requeridas:** comprensión de memoria, manejo del stack, control de flujo, análisis de registros como EBP y EIP, y explotación básica.

### 4. Análisis de URL
- **Categoría:** Seguridad Web / Red
- **Descripción:** El participante debe recorrer múltiples rutas y analizar la estructura de las solicitudes para identificar la vulnerabilidad subyacente en la aplicación.
- **Habilidades requeridas:** análisis de rutas, inspección de parámetros, deducción de lógica de negocio y navegación por escenarios multi-etapa.

### 5. API REST Vulnerable
- **Categoría:** Seguridad Web / APIs
- **Descripción:** Este reto expone una API con fallos de seguridad en la lógica de acceso y autenticación, permitiendo la exploración de vulnerabilidades de negocio y de control de acceso.
- **Habilidades requeridas:** consumo de endpoints, manejo de métodos HTTP, análisis de respuestas JSON, manipulación de payloads y explotación de lógica débil.

### 6. Esteganografía
- **Categoría:** Forensics / Steganography
- **Descripción:** La información sensible se encuentra oculta dentro de un medio visual, y el participante debe extraerla mediante análisis técnico y observación detallada.
- **Habilidades requeridas:** análisis de imágenes, extracción de datos ocultos, inspección de metadatos y razonamiento forense.

### 7. Astucia
- **Categoría:** Lógica / Exploración
- **Descripción:** Este desafío está orientado a la detección de elementos ocultos o fallas sutiles en la interfaz y la arquitectura del sistema.
- **Habilidades requeridas:** observación precisa, exploración del código, identificación de pistas ocultas y deducción estructural.

### 8. Biométrico
- **Categoría:** Lógica / Reverse Engineering
- **Descripción:** El reto requiere inferir un patrón de desbloqueo a partir de restricciones, pistas y comportamiento del sistema.
- **Habilidades requeridas:** razonamiento lógico, reconocimiento de patrones, combinatoria y análisis de secuencias.

### 9. CSRF (Cross-Site Request Forgery)
- **Categoría:** Seguridad Web / Client-Side Attacks
- **Descripción:** Simula una plataforma bancaria vulnerable a falsificación de peticiones en sitios cruzados, requiriendo forzar transferencias no autorizadas hacia una cuenta controlada por el atacante.
- **Habilidades requeridas:** Ingeniería de vectores de ataque, forjado de solicitudes HTTP, manipulación de cookies de sesión, evasión de controles Cross-Origin y explotación de vulnerabilidades CSRF.

### 10. Código Dinámico
- **Categoría:** Programación / Scripting & Automation
- **Descripción:** Presenta un sistema de autenticación con tokens dinámicos dependientes del tiempo, exigiendo la automatización del proceso de captura y envío para la obtención de la bandera antes de su expiración.
- **Habilidades requeridas:** Scripting automatizado (Python/JavaScript), manejo de peticiones de alta velocidad (HTTP/S), parsing dinámico, manipulación de tokens temporales y lógica de temporización.

---

## Habilidades transversales del 2026

Las competencias más relevantes para esta edición son:
- Seguridad web aplicada y análisis de entradas.
- Criptografía introductoria y descifrado de mensajes.
- Manejo de APIs y comprensión del comportamiento de servicios web.
- Análisis forense básico y extracción de información oculta.
- Razonamiento lógico y resolución de retos de naturaleza multi-etapa.
- Inspección de sistemas, identificación de pistas y experimentación técnica.
- Falsificación de peticiones en entorno web y gestión de cookies/sesiones.
- Automatización mediante scripting para interacciones con tokens temporales y de alta velocidad.

---

## Recomendación de preparación
1. Practicar con ejercicios de inyección, parsing y análisis de datos en formatos estructurados.
2. Fortalecer las bases de ocultación de información y análisis de medios digitales.
3. Reforzar conceptos fundamentales de memoria, explotación y control de flujo.
4. Desarrollar habilidades de deducción para resolver escenarios encadenados y de investigación.
5. Estudiar vectores de ataque del lado del cliente, específicamente la explotación de vulnerabilidades CSRF y mecanismos de protección (tokens anti-CSRF, SameSite).
6. Desarrollar destreza en scripting con librerías de automatización HTTP (ej. `requests` en Python) para el manejo de payloads y tokens dinámicos dependientes del tiempo.

---

## Nota final
Este informe refleja únicamente los retos que se encuentran creados y disponibles en la versión 2026 del proyecto, sin incluir desafíos pendientes ni propuestas no implementadas.

