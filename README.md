# ASTRALISTA PRO - Sistema de Análisis y Diagnóstico Psicoastrológico

ASTRALISTA PRO es un motor monolítico integral diseñado para el análisis y diagnóstico psicoastrológico. Su núcleo se centra en el cálculo preciso del estado celeste, el estado terrestre y los aspectos astrológicos, proporcionando una base sólida para interpretaciones avanzadas.

## Características Principales

Este proyecto se concibe como una herramienta fundamental para la astrología computacional, ofreciendo las siguientes capacidades iniciales:

*   **Motor de Efemérides**: Un sistema robusto para calcular el Día Juliano, una base temporal esencial para todas las operaciones astrológicas.
*   **Cálculo del Ascendente**: Determina el signo ascendente con precisión, un pilar fundamental en la carta natal.
*   **Posiciones Planetarias**: Preparado para calcular las posiciones de los planetas, aunque esta funcionalidad está en desarrollo.
*   **Base de Datos de Signos Zodiacales**: Incluye una referencia completa de los 12 signos del zodíaco, con sus propiedades clave (elemento, modalidad, regente, color) para facilitar la interpretación y visualización.
*   **Arquitectura Monolítica**: Integra las distintas secciones (estado celeste, estado terrestre, aspectos) dentro de una única estructura para una gestión simplificada.

## Estructura del Proyecto

El corazón del sistema reside en la clase `AstroEngine`, que encapsula las lógicas de cálculo astronómico:

*   `AstroEngine::$zodiacSigns`: Un array estático que contiene la información detallada de cada signo zodiacal.
*   `AstroEngine::getJulianDay()`: Método para convertir una fecha y hora específicas, junto con la zona horaria, en su equivalente del Día Juliano.
*   `AstroEngine::calculateAscendant()`: Método encargado de determinar el grado exacto del Ascendente para una latitud, longitud y Día Juliano dados.
*   `AstroEngine::getPlanetaryPositions()`: Función en desarrollo para calcular las posiciones celestes de los planetas.

## Tecnologías Utilizadas

*   **PHP**: Lenguaje de programación principal para el desarrollo del motor astrológico.
*   **Algoritmos Astronómicos**: Implementación de fórmulas matemáticas para efemérides y cálculos de posiciones celestes.

## Cómo Empezar

Para poner en marcha ASTRALISTA PRO, simplemente clone el repositorio y asegúrese de tener un entorno PHP configurado.

1.  **Clonar el Repositorio**:
    git clone https://github.com/tu-usuario/astralista-pro.git
    cd astralista-pro

2.  **Configuración del Entorno**:
    Asegúrese de tener PHP (versión 7.4 o superior recomendada) instalado en su sistema.
    Puede usar un servidor web local como Apache o Nginx, o el servidor web integrado de PHP para pruebas:
    php -S localhost:8000

## Uso

Una vez configurado, puede interactuar con el motor ASTRALISTA PRO llamando a los métodos de la clase `AstroEngine` desde sus scripts PHP.

Ejemplo de cálculo del Día Juliano y el Ascendente:

    <?php
    require_once 'index.php'; // Asumiendo que AstroEngine está definido aquí

    // Datos de ejemplo
    $year = 1990;
    $month = 7;
    $day = 20;
    $hour = 14;
    $min = 30;
    $tz = -3.0; // Zona horaria GMT-3
    $lat = -34.6037; // Latitud de Buenos Aires
    $lon = -58.3816; // Longitud de Buenos Aires

    // Calcular Día Juliano
    $jd = AstroEngine::getJulianDay($year, $month, $day, $hour, $min, $tz);
    echo "Día Juliano: " . $jd . "\n";

    // Calcular Ascendente
    $ascendant = AstroEngine::calculateAscendant($jd, $lat, $lon);
    echo "Ascendente: " . round($ascendant, 2) . " grados\n";

    // Acceder a la información de los signos zodiacales
    echo "Signo número 1 (Aries): " . AstroEngine::$zodiacSigns[0]['name'] . " (" . AstroEngine::$zodiacSigns[0]['symbol'] . ")\n";
    ?>

## Contribuciones

Las contribuciones son bienvenidas. Si desea mejorar el proyecto, abrir issues o enviar pull requests, por favor, siga las guías de contribución (a ser definidas).

## Licencia

Este proyecto se distribuye bajo la licencia MIT. Consulte el archivo `LICENSE` (si aplica) para más detalles.
