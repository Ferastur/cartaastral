<?php
/**
 * ============================================================================
 * ASTRALISTA PRO - Sistema de Análisis y Diagnóstico Psicoastrológico
 * Motor Monolítico Integral: Estado Celeste, Estado Terrestre y Aspectos
 * ============================================================================
 */

// ============================================================================
// 1. MOTOR DE EFEMÉRIDES Y ESTADO CELESTE
// ============================================================================

class AstroEngine {
    public static $zodiacSigns = [
        ['name' => 'Aries', 'symbol' => '♈', 'element' => 'Fuego', 'modality' => 'Cardinal', 'ruler' => 'Marte', 'color' => '#ef4444'],
        ['name' => 'Tauro', 'symbol' => '♉', 'element' => 'Tierra', 'modality' => 'Fijo', 'ruler' => 'Venus', 'color' => '#10b981'],
        ['name' => 'Géminis', 'symbol' => '♊', 'element' => 'Aire', 'modality' => 'Mutable', 'ruler' => 'Mercurio', 'color' => '#f59e0b'],
        ['name' => 'Cáncer', 'symbol' => '♋', 'element' => 'Agua', 'modality' => 'Cardinal', 'ruler' => 'Luna', 'color' => '#3b82f6'],
        ['name' => 'Leo', 'symbol' => '♌', 'element' => 'Fuego', 'modality' => 'Fijo', 'ruler' => 'Sol', 'color' => '#f97316'],
        ['name' => 'Virgo', 'symbol' => '♍', 'element' => 'Tierra', 'modality' => 'Mutable', 'ruler' => 'Mercurio', 'color' => '#059669'],
        ['name' => 'Libra', 'symbol' => '♎', 'element' => 'Aire', 'modality' => 'Cardinal', 'ruler' => 'Venus', 'color' => '#eab308'],
        ['name' => 'Escorpio', 'symbol' => '♏', 'element' => 'Agua', 'modality' => 'Fijo', 'ruler' => 'Plutón', 'color' => '#2563eb'],
        ['name' => 'Sagitario', 'symbol' => '♐', 'element' => 'Fuego', 'modality' => 'Mutable', 'ruler' => 'Júpiter', 'color' => '#dc2626'],
        ['name' => 'Capricornio', 'symbol' => '♑', 'element' => 'Tierra', 'modality' => 'Cardinal', 'ruler' => 'Saturno', 'color' => '#047857'],
        ['name' => 'Acuario', 'symbol' => '♒', 'element' => 'Aire', 'modality' => 'Fijo', 'ruler' => 'Urano', 'color' => '#ca8a04'],
        ['name' => 'Piscis', 'symbol' => '♓', 'element' => 'Agua', 'modality' => 'Mutable', 'ruler' => 'Neptuno', 'color' => '#1d4ed8']
    ];

    public static function getJulianDay($year, $month, $day, $hour, $min, $tz) {
        $decHour = $hour + ($min / 60.0) - $tz;
        if ($month <= 2) { $year -= 1; $month += 12; }
        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);
        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + ($decHour / 24.0) + $b - 1524.5;
    }

    public static function calculateAscendant($jd, $lat, $lon) {
        $t = ($jd - 2451545.0) / 36525.0;
        $gmst = 280.46061837 + 360.98564736629 * ($jd - 2451545.0) + 0.000387933 * $t * $t;
        $gmst = fmod($gmst, 360.0); if ($gmst < 0) $gmst += 360.0;

        $lst = fmod($gmst + $lon, 360.0); if ($lst < 0) $lst += 360.0;
        $eps = 23.4392911 - 0.0130042 * $t;

        $radLST = deg2rad($lst);
        $radEps = deg2rad($eps);
        $radLat = deg2rad($lat);

        $y = -cos($radLST);
        $x = sin($radLST) * cos($radEps) + tan($radLat) * sin($radEps);
        $asc = rad2deg(atan2($y, $x));
        $asc = fmod($asc + 180.0, 360.0);
        if ($asc < 0) $asc += 360.0;
        return $asc;
    }

    public static function getPlanetaryPositions($jd) {
        $d = $jd - 2451545.0;
        $t = $d / 36525.0;

        // Sol
        $L0 = 280.46646 + 36000.76983 * $t;
        $M = 357.52911 + 35999.05029 * $t;
        $sunLong = fmod($L0 + (1.914602 - 0.004817 * $t) * sin(deg2rad($M)), 360.0);
        if ($sunLong < 0) $sunLong += 360.0;

        // Luna
        $l0 = 218.316 + 13.176396 * $d;
        $m_moon = 134.963 + 13.064993 * $d;
        $moonLong = fmod($l0 + 6.289 * sin(deg2rad($m_moon)) - 1.274 * sin(deg2rad($m_moon - 2 * ($sunLong - $l0))), 360.0);
        if ($moonLong < 0) $moonLong += 360.0;

        // Planetas Personales, Sociales y Transpersonales
        $mercLong = fmod($sunLong + 24.5 * sin(deg2rad(252.25 + 149472.67 * $t)), 360.0); if ($mercLong < 0) $mercLong += 360.0;
        $venLong  = fmod($sunLong + 39.8 * sin(deg2rad(181.98 + 58517.81 * $t)), 360.0); if ($venLong < 0) $venLong += 360.0;
        $marsLong = fmod(355.43 + 19140.30 * $t + 10.6 * sin(deg2rad(19.37 + 19139.86 * $t)), 360.0); if ($marsLong < 0) $marsLong += 360.0;
        $jupLong  = fmod(34.35 + 3034.90 * $t + 5.5 * sin(deg2rad(20.45 + 3034.6 * $t)), 360.0); if ($jupLong < 0) $jupLong += 360.0;
        $satLong  = fmod(50.08 + 1222.11 * $t + 6.3 * sin(deg2rad(317.0 + 1221.0 * $t)), 360.0); if ($satLong < 0) $satLong += 360.0;
        $uraLong  = fmod(314.05 + 428.46 * $t + 3.1 * sin(deg2rad(142.2 + 428.4 * $t)), 360.0); if ($uraLong < 0) $uraLong += 360.0;
        $nepLong  = fmod(304.35 + 218.46 * $t + 2.0 * sin(deg2rad(256.2 + 218.4 * $t)), 360.0); if ($nepLong < 0) $nepLong += 360.0;
        $pluLong  = fmod(238.93 + 145.18 * $t + 4.5 * sin(deg2rad(14.8 + 145.2 * $t)), 360.0); if ($pluLong < 0) $pluLong += 360.0;

        // Detección de Retrogradación (aproximación por elongación respecto al Sol)
        $isRetro = function($pDeg, $sunDeg, $isOuter = true) {
            $diff = fmod($pDeg - $sunDeg + 360, 360);
            return $isOuter ? ($diff > 120 && $diff < 240) : ($diff > 15 && $diff < 35);
        };

        return [
            ['name' => 'Sol', 'glyph' => '☉', 'category' => 'Personal', 'deg' => $sunLong, 'retro' => false, 'function' => 'Identidad, propósito, vitalidad y voluntad consciente.'],
            ['name' => 'Luna', 'glyph' => '☽', 'category' => 'Personal', 'deg' => $moonLong, 'retro' => false, 'function' => 'Seguridad ontológica y respuesta bio-emocional.'],
            ['name' => 'Mercurio', 'glyph' => '☿', 'category' => 'Personal', 'deg' => $mercLong, 'retro' => $isRetro($mercLong, $sunLong, false), 'function' => 'Procesamiento cognitivo, comunicación y análisis.'],
            ['name' => 'Venus', 'glyph' => '♀', 'category' => 'Personal', 'deg' => $venLong, 'retro' => $isRetro($venLong, $sunLong, false), 'function' => 'Escala de valores, vinculación afectiva y estética.'],
            ['name' => 'Marte', 'glyph' => '♂', 'category' => 'Personal', 'deg' => $marsLong, 'retro' => $isRetro($marsLong, $sunLong), 'function' => 'Afirmación personal, impulso de acción y deseo.'],
            ['name' => 'Júpiter', 'glyph' => '♃', 'category' => 'Social', 'deg' => $jupLong, 'retro' => $isRetro($jupLong, $sunLong), 'function' => 'Expansión, sistema de creencias y sentido de vida.'],
            ['name' => 'Saturno', 'glyph' => '♄', 'category' => 'Social', 'deg' => $satLong, 'retro' => $isRetro($satLong, $sunLong), 'function' => 'Estructura, límites, responsabilidad y maduración.'],
            ['name' => 'Urano', 'glyph' => '♅', 'category' => 'Transpersonal', 'deg' => $uraLong, 'retro' => $isRetro($uraLong, $sunLong), 'function' => 'Revolución interior, originalidad y ruptura de estructuras.'],
            ['name' => 'Neptuno', 'glyph' => '♆', 'category' => 'Transpersonal', 'deg' => $nepLong, 'retro' => $isRetro($nepLong, $sunLong), 'function' => 'Imaginación, espiritualidad e intuición trascendente.'],
            ['name' => 'Plutón', 'glyph' => '♇', 'category' => 'Transpersonal', 'deg' => $pluLong, 'retro' => $isRetro($pluLong, $sunLong), 'function' => 'Transformación profunda, gestión de la sombra y poder.']
        ];
    }

    public static function getZodiacInfo($degree) {
        $norm = fmod($degree, 360.0);
        if ($norm < 0) $norm += 360.0;
        $signIndex = (int)floor($norm / 30);
        $signDeg = $norm - ($signIndex * 30);
        $degInt = floor($signDeg);
        $minInt = floor(($signDeg - $degInt) * 60);

        return [
            'sign' => self::$zodiacSigns[$signIndex]['name'],
            'symbol' => self::$zodiacSigns[$signIndex]['symbol'],
            'element' => self::$zodiacSigns[$signIndex]['element'],
            'modality' => self::$zodiacSigns[$signIndex]['modality'],
            'ruler' => self::$zodiacSigns[$signIndex]['ruler'],
            'color' => self::$zodiacSigns[$signIndex]['color'],
            'signIndex' => $signIndex,
            'degree_in_sign' => sprintf("%02d° %02d'", $degInt, $minInt),
            'raw_deg' => $norm
        ];
    }

    public static function evaluateDignity($planetName, $signName) {
        $table = [
            'Sol'      => ['dom' => ['Leo'], 'exalt' => ['Aries'], 'fall' => ['Libra'], 'det' => ['Acuario']],
            'Luna'     => ['dom' => ['Cáncer'], 'exalt' => ['Tauro'], 'fall' => ['Escorpio'], 'det' => ['Capricornio']],
            'Mercurio' => ['dom' => ['Géminis', 'Virgo'], 'exalt' => ['Virgo'], 'fall' => ['Piscis'], 'det' => ['Sagitario', 'Piscis']],
            'Venus'    => ['dom' => ['Tauro', 'Libra'], 'exalt' => ['Piscis'], 'fall' => ['Virgo'], 'det' => ['Aries', 'Escorpio']],
            'Marte'    => ['dom' => ['Aries', 'Escorpio'], 'exalt' => ['Capricornio'], 'fall' => ['Cáncer'], 'det' => ['Libra', 'Tauro']],
            'Júpiter'  => ['dom' => ['Sagitario', 'Piscis'], 'exalt' => ['Cáncer'], 'fall' => ['Capricornio'], 'det' => ['Géminis', 'Virgo']],
            'Saturno'  => ['dom' => ['Capricornio', 'Acuario'], 'exalt' => ['Libra'], 'fall' => ['Aries'], 'det' => ['Cáncer', 'Leo']],
            'Urano'    => ['dom' => ['Acuario'], 'exalt' => ['Escorpio'], 'fall' => ['Tauro'], 'det' => ['Leo']],
            'Neptuno'  => ['dom' => ['Piscis'], 'exalt' => ['Cáncer', 'Leo'], 'fall' => ['Capricornio'], 'det' => ['Virgo']],
            'Plutón'   => ['dom' => ['Escorpio'], 'exalt' => ['Aries', 'Piscis'], 'fall' => ['Libra'], 'det' => ['Tauro']]
        ];

        if (!isset($table[$planetName])) return ['status' => 'Peregrino', 'nature' => 'Neutral', 'desc' => 'Expresión condicionada por el aprendizaje del escenario.'];
        $d = $table[$planetName];

        if (in_array($signName, $d['dom'])) {
            return ['status' => 'Domicilio', 'nature' => 'Fluida / Autoridad', 'desc' => 'El planeta posee autoridad natural. La energía fluye sin obstáculos y en total alineación identitaria.'];
        }
        if (in_array($signName, $d['exalt'])) {
            return ['status' => 'Exaltación', 'nature' => 'Potenciada', 'desc' => 'Energía en intensidad elevada y fecunda; gran capacidad de logro que requiere vigilancia de excesos.'];
        }
        if (in_array($signName, $d['det'])) {
            return ['status' => 'Exilio', 'nature' => 'Fricción Consciente', 'desc' => 'Opera a contracorriente en terreno ajeno. Desafío madurativo que exige esfuerzo consciente.'];
        }
        if (in_array($signName, $d['fall'])) {
            return ['status' => 'Caída', 'nature' => 'Aprendizaje Evolutivo', 'desc' => 'Expresión irregular y costosa. Invita a desarrollar maestría técnica deliberada.'];
        }
        return ['status' => 'Peregrino', 'nature' => 'Adaptativa', 'desc' => 'Energía flexible que se moldea según los aspectos y el escenario de la casa.'];
    }

    public static function getHouses($ascDegree) {
        $houses = [];
        for ($i = 0; $i < 12; $i++) {
            $hDeg = fmod($ascDegree + ($i * 30), 360.0);
            $houses[$i + 1] = [
                'number' => $i + 1,
                'degree' => $hDeg,
                'zodiac' => self::getZodiacInfo($hDeg),
                'planets' => []
            ];
        }
        return $houses;
    }

    public static function calculateAspects($planets) {
        $aspectRules = [
            ['name' => 'Conjunción', 'symbol' => '☌', 'angle' => 0,   'orb' => 8.0, 'color' => '#eab308', 'desc' => 'Fusiona las energías en una unidad de acción indivisible.'],
            ['name' => 'Sextil',     'symbol' => '⚹', 'angle' => 60,  'orb' => 6.0, 'color' => '#06b6d4', 'desc' => 'Abre oportunidades que exigen una activación deliberada.'],
            ['name' => 'Cuadratura', 'symbol' => '□', 'angle' => 90,  'orb' => 8.0, 'color' => '#ef4444', 'desc' => 'Fricciona las funciones, generando el movimiento para el logro sólido.'],
            ['name' => 'Trígono',    'symbol' => '△', 'angle' => 120, 'orb' => 8.0, 'color' => '#10b981', 'desc' => 'Facilita el flujo de talentos innatos (atención a la complacencia).'],
            ['name' => 'Oposición',  'symbol' => '☍', 'angle' => 180, 'orb' => 8.0, 'color' => '#f97316', 'desc' => 'Polariza la experiencia, invitando a la integración de lógicas opuestas.']
        ];

        $aspects = [];
        $count = count($planets);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $p1 = $planets[$i];
                $p2 = $planets[$j];
                $diff = abs($p1['deg'] - $p2['deg']);
                if ($diff > 180) $diff = 360 - $diff;

                foreach ($aspectRules as $rule) {
                    $orbDist = abs($diff - $rule['angle']);
                    if ($orbDist <= $rule['orb']) {
                        $aspects[] = [
                            'p1' => $p1,
                            'p2' => $p2,
                            'name' => $rule['name'],
                            'symbol' => $rule['symbol'],
                            'color' => $rule['color'],
                            'desc' => $rule['desc'],
                            'orb' => round($orbDist, 2),
                            'exactAngle' => round($diff, 1)
                        ];
                        break;
                    }
                }
            }
        }
        return $aspects;
    }
}

// ============================================================================
// 2. MATRIZ DE DELINEACIÓN PSICOASTROLÓGICA (144 COMBINACIONES CASA/SIGNO)
// ============================================================================

class AstroDelineations {
    public static $ascendantLogic = [
        'Aries' => 'Iniciativa directa, estilo pionero y resolución frontal ante la realidad.',
        'Tauro' => 'Estabilidad, ritmo constante, pragmatismo y construcción de seguridad tangible.',
        'Géminis' => 'Curiosidad ágil, filtro comunicativo y adaptabilidad mediante la interacción verbal.',
        'Cáncer' => 'Filtro sensible, receptividad intuitiva y estilo protector de aproximación.',
        'Leo' => 'Presencia carismática, autoafirmación confiada y liderazgo espontáneo.',
        'Virgo' => 'Enfoque analítico, precisión funcional, prudencia y resolución técnica.',
        'Libra' => 'Mediación diplomática, búsqueda de armonía estética y cooperación vincular.',
        'Escorpio' => 'Intensidad magnética, penetración psicológica y estrategia transformadora.',
        'Sagitario' => 'Entusiasmo expansivo, visión filosófica y actitud de exploración constante.',
        'Capricornio' => 'Madurez sobria, disciplina estructurada y orientación a metas tangibles.',
        'Acuario' => 'Originalidad vanguardista, independencia de criterio y visión comunitaria.',
        'Piscis' => 'Sensibilidad sutil, empatía compasiva y percepción intuitiva holística.'
    ];

    public static $houseThemes = [
        1 => ['name' => 'Casa I', 'title' => 'Identidad y Estilo de Iniciativa', 'scenario' => 'Proyección de la personalidad y portal de acción en la realidad.'],
        2 => ['name' => 'Casa II', 'title' => 'Gestión de Recursos y Autoestima', 'scenario' => 'Sostenibilidad material, escala de valores y autovaloración.'],
        3 => ['name' => 'Casa III', 'title' => 'Pensamiento y Entorno Cercano', 'scenario' => 'Mente cotidiana, comunicación, aprendizaje e intercambios locales.'],
        4 => ['name' => 'Casa IV', 'title' => 'Raíces, Hogar y Mundo Interno', 'scenario' => 'Base emocional, linaje familiar y santuario de repliegue privado.'],
        5 => ['name' => 'Casa V', 'title' => 'Creatividad, Romance y Juego', 'scenario' => 'Capacidad generativa, autoexpresión, hijos y disfrute lúdico.'],
        6 => ['name' => 'Casa VI', 'title' => 'Rutinas, Salud y Servicio Técnico', 'scenario' => 'Eficiencia diaria, hábitos corporales y optimización funcional.'],
        7 => ['name' => 'Casa VII', 'title' => 'Vínculos, Pareja y Compromisos', 'scenario' => 'Encuentro con la alteridad, acuerdos conyugales y sociedades.'],
        8 => ['name' => 'Casa VIII', 'title' => 'Transformación y Bienes Compartidos', 'scenario' => 'Crisis evolutivas, gestión de recursos mutuos y sexualidad profunda.'],
        9 => ['name' => 'Casa IX', 'title' => 'Filosofía, Viajes y Mente Superior', 'scenario' => 'Expansión del horizonte mental, estudios superiores y cosmovisión.'],
        10 => ['name' => 'Casa X', 'title' => 'Vocación y Reputación Pública (MC)', 'scenario' => 'Cima profesional, estatus, autoridad y legado social.'],
        11 => ['name' => 'Casa XI', 'title' => 'Ideales Colectivos y Redes', 'scenario' => 'Grupos de pertenencia, proyectos a futuro y causas compartidas.'],
        12 => ['name' => 'Casa XII', 'title' => 'Inconsciente y Espiritualidad', 'scenario' => 'Integración de la sombra, disolución del ego e introspección profunda.']
    ];

    public static $houseInSign = [
        1 => [
            'Aries' => 'Te proyectas como un iniciador nato y enérgico, con impulso inmediato a la acción independiente.',
            'Tauro' => 'Presencia serena, pausada y sólida; abordas nuevos proyectos con constancia y búsqueda de seguridad.',
            'Géminis' => 'Proyectas gran agilidad mental, curiosidad y destreza comunicativa en tu primer contacto con el entorno.',
            'Cáncer' => 'Impronta afectuosa, intuitiva y protectora; actúas con cautela previa para resguardar tu espacio íntimo.',
            'Leo' => 'Presencia radiante, digna y magnética; encaras la existencia con necesidad de autoexpresión y calidez.',
            'Virgo' => 'Te perciben como alguien prudente, meticuloso y resolutivo; te aproximas a las metas con orden funcional.',
            'Libra' => 'Proyectas encanto, cortesía y búsqueda de belleza; tu identidad se orienta hacia la conciliación vincular.',
            'Escorpio' => 'Aura magnética e intensa; posees un radar penetrante para leer motivaciones ocultas en cada situación.',
            'Sagitario' => 'Actitud abierta, jovial y optimista; vives la existencia como una exploración y búsqueda de horizontes.',
            'Capricornio' => 'Presencia sobria, madura y estructurada; inspiras autoridad y encaras metas con firme perseverancia.',
            'Acuario' => 'Imagen singular, autónoma y vanguardista; te muestras sin ataduras a moldes tradicionales.',
            'Piscis' => 'Aura permeable, compasiva e intuitiva; captas la atmósfera sutil del entorno antes de actuar.'
        ],
        2 => [
            'Aries' => 'Generación de recursos con audacia y rapidez; riesgo de gastos impulsivos por satisfacer deseos inmediatos.',
            'Tauro' => 'Excelente capacidad para consolidar bienes estables, ahorro a largo plazo y gran sentido del valor tangible.',
            'Géminis' => 'Ingresos diversificados mediante el comercio, comunicación, docencia o proyectos intelectuales.',
            'Cáncer' => 'Seguridad financiera ligada a la tranquilidad del hogar; acumulas recursos para nutrir y proteger.',
            'Leo' => 'Generas ingresos con orgullo creativo y liderazgo; disfrutas gastar con generosidad y distinción.',
            'Virgo' => 'Gestión presupuestaria minuciosa; ingresos por servicios técnicos detallados y economía eficiente.',
            'Libra' => 'Economía favorecida por asociaciones, estética o asesoría; valoras la justicia en cada transacción.',
            'Escorpio' => 'Estrategia financiera hermética y resiliente; capacidad para resurgir con fuerza de crisis materiales.',
            'Sagitario' => 'Confianza próspera hacia el dinero; ganancias vinculadas a la enseñanza, viajes o proyección exterior.',
            'Capricornio' => 'Construcción paciente de patrimonio; máxima prudencia y austeridad productiva ante el gasto.',
            'Acuario' => 'Ingresos por vías innovadoras, tecnológicas o independientes; actitud desapegada de lo material.',
            'Piscis' => 'Relación intuitiva con el dinero; ingresos ligados a actividades vocacionales, artísticas o de ayuda.'
        ],
        3 => [
            'Aries' => 'Pensamiento veloz y directo; debates francos y honestidad frontal al intercambiar ideas.',
            'Tauro' => 'Mente pragmática y reflexiva; asimilas conocimientos con calma para fijarlos de forma sólida.',
            'Géminis' => 'Mente brillante y curiosa; constante necesidad de intercambiar noticias, lecturas y diálogo continuo.',
            'Cáncer' => 'Pensamiento guiado por la memoria y la afectividad; comunicación teñida de cercanía emocional.',
            'Leo' => 'Expresión verbal dramática y elocuente; hablas con convicción y buscas cautivar a tu interlocutor.',
            'Virgo' => 'Pensamiento analítico y ordenado; comunicación precisa enfocada en resolver problemas prácticos.',
            'Libra' => 'Comunicación diplomática y balanceada; buscas la elegancia verbal y evaluar todas las posturas.',
            'Escorpio' => 'Mente investigadora y perspicaz; lenguaje agudo que detecta lo no dicho en el discurso ajeno.',
            'Sagitario' => 'Discurso entusiasta y conceptual; mente orientada a grandes principios morales y filosóficos.',
            'Capricornio' => 'Pensamiento estructurado y riguroso; expresas tus ideas con concisión, seriedad y peso.',
            'Acuario' => 'Mente vanguardista y disruptiva; disfrutas desafiar dogmas con ideas revolucionarias.',
            'Piscis' => 'Pensamiento intuitivo y poético; captas ideas por resonancia estética y sensibilidad metafórica.'
        ],
        4 => [
            'Aries' => 'Hogar dinámico con necesidad de autonomía personal; descargas energía en la vida privada.',
            'Tauro' => 'Buscas un hogar campestre, confortable y pacífico; fuerte apego a las raíces y la tradición familiar.',
            'Géminis' => 'Ambiente doméstico con mucho movimiento y lecturas; afición a mudanzas o cambios de espacio.',
            'Cáncer' => 'Hogar vivido como santuario sagrado; apego nutricio a las memorias y la calidez del clan.',
            'Leo' => 'Orgullo por las raíces familiares; hogar acogedor donde disfrutas ser el centro de los tuyos.',
            'Virgo' => 'Espacio íntimo ordenado, limpio y funcional; ambiente donde priman la salud y las rutinas sanas.',
            'Libra' => 'Búsqueda de armonía y estética en la convivencia; aversión total al conflicto dentro del hogar.',
            'Escorpio' => 'Mundo privado hermético e intenso; dinámicas familiares profundas con vivencias de transformación.',
            'Sagitario' => 'Hogar espacioso y abierto; infancia con sensación de libertad o influencia multicultural.',
            'Capricornio' => 'Educación estructurada y tradicional; construyes tu hogar sobre bases de respeto y orden.',
            'Acuario' => 'Ambiente hogareño liberal e independiente; requieres total autonomía dentro de tu espacio privado.',
            'Piscis' => 'Refugio doméstico tranquilo y sensible; santuario de meditación y desconexión del exterior.'
        ],
        5 => [
            'Aries' => 'Conquistas apasionadas y espontáneas; te motivan los desafíos creativos y los deportes activos.',
            'Tauro' => 'Amores estables y sensoriales; disfrute profundo del arte, la buena mesa y el ocio placentero.',
            'Géminis' => 'Romances estimulados por la afinidad mental; disfrutas de juegos de ingenio y pasatiempos variados.',
            'Cáncer' => 'Afectos románticos tiernos y protectores; entrega afectuosa y protectora en la crianza y el arte.',
            'Leo' => 'Máxima brillantez creativa; vives los romances con intensidad noble y generosidad desbordante.',
            'Virgo' => 'Creaciones que exigen técnica y destreza; reservas tus sentimientos románticos con prudencia.',
            'Libra' => 'Amores galantes e idealistas; búsqueda de refinamiento, equilibrio y estética en cada afición.',
            'Escorpio' => 'Atracciones magnéticas e intensas; creatividad volcada a temas profundos y misteriosos.',
            'Sagitario' => 'Romances libres y divertidos; disfrute de actividades al aire libre, viajes y aventuras creativas.',
            'Capricornio' => 'Afectos que maduran con paciencia; te tomas la creatividad y la paternidad con severo compromiso.',
            'Acuario' => 'Atracción por personalidades singulares; creatividad experimental y actitud no posesiva en el amor.',
            'Piscis' => 'Romances platónicos llenos de magia; gran talento para la música, la poesía y la imaginería mística.'
        ],
        6 => [
            'Aries' => 'Trabajo cotidiano dinámico y proactivo; atención al desgaste físico por sobreesfuerzo impulsivo.',
            'Tauro' => 'Rendimiento laboral firme y metódico; la alimentación equilibrada y el descanso sustentan tu salud.',
            'Géminis' => 'Labores que exigen agilidad mental y multitarea; cuida tu sistema nervioso de la sobreestimulación.',
            'Cáncer' => 'Ambiente laboral de tintes familiares; tu bienestar digestivo refleja de inmediato tu estado anímico.',
            'Leo' => 'Liderazgo en las tareas diarias; necesidad de reconocimiento profesional y cuidado del sistema cardiovascular.',
            'Virgo' => 'Vocación de servicio técnico impecable, orden y análisis; tendencia al perfeccionismo riguroso.',
            'Libra' => 'Búsqueda de armonía con colegas; salud balanceada mediante equilibrio estético y pausas de relax.',
            'Escorpio' => 'Capacidad de concentración profunda y resolución de crisis en el trabajo; gran resistencia física.',
            'Sagitario' => 'Deseo de autonomía en el trabajo; bienestar físico potenciado por el deporte y el aire libre.',
            'Capricornio' => 'Ética laboral inquebrantable y perseverancia; atención a la salud articular, postura y piel.',
            'Acuario' => 'Rutinas de trabajo flexibles con base técnica o digital; salud modulada por la tensión nerviosa.',
            'Piscis' => 'Trabajos con sentido humanitario o artístico; organismo sensible que requiere calma mental.'
        ],
        7 => [
            'Aries' => 'Atraes parejas enérgicas y decididas; requieres honestidad frontal y dinamismo en los acuerdos mutuos.',
            'Tauro' => 'Buscas relaciones leales, estables y seguras; valoras la paz y el compromiso a largo plazo.',
            'Géminis' => 'Te complementas con parejas comunicativas e inteligentes con quienes debatir de todo tema.',
            'Cáncer' => 'Anhelas una unión íntima, protectora y afectuosa; la pareja representa tu hogar emocional.',
            'Leo' => 'Te atraen personas carismáticas y seguras; buscas un vínculo basado en la mutua admiración.',
            'Virgo' => 'Buscas parejas confiables y colaborativas con quienes estructurar un proyecto de vida ordenado.',
            'Libra' => 'Aspiración al ideal de armonía y reciprocidad en el matrimonio; el vínculo conyugal es tu prioridad.',
            'Escorpio' => 'Vínculos de máxima intensidad emocional y lealtad total; rechazas la superficialidad vincular.',
            'Sagitario' => 'Valoras la libertad compartida; necesitas una pareja con quien viajar y expandir horizontes vitales.',
            'Capricornio' => 'Compromiso con personas maduras y responsables; relaciones duraderas que se afianzan con los años.',
            'Acuario' => 'Parejas que son ante todo amigos íntimos; necesitas un vínculo igualitario con espacio para la independencia.',
            'Piscis' => 'Uniones espirituales de gran empatía y entrega; conexión sensible y compasión compartida.'
        ],
        8 => [
            'Aries' => 'Superación valiente de las crisis mediante la acción; vivencia directa e instintiva de la sexualidad.',
            'Tauro' => 'Gestión prudente de recursos conyugales o de socios; búsqueda de seguridad sensorial y material.',
            'Géminis' => 'Curiosidad analítica por la psicología profunda y los misterios; diálogo abierto sobre bienes comunes.',
            'Cáncer' => 'Intuición psíquica profunda; vivencia de la sexualidad como un intercambio de afecto e intimidad.',
            'Leo' => 'Capacidad para renacer con orgullo tras momentos difíciles; generosidad con los recursos compartidos.',
            'Virgo' => 'Análisis exhaustivo de riesgos financieros e inversiones; enfoque metódico ante las crisis personales.',
            'Libra' => 'Equidad en la distribución de bienes conyugales; búsqueda de equilibrio en la entrega íntima.',
            'Escorpio' => 'Máximo poder de regeneración psicológica; vivencia trascendente y transformadora de las crisis.',
            'Sagitario' => 'Confianza y optimismo para superar adversidades; posibles beneficios económicos con el exterior.',
            'Capricornio' => 'Resistencia férrea ante la adversidad; riguroso control de compromisos y finanzas compartidas.',
            'Acuario' => 'Enfoque desapegado ante las crisis; visión innovadora en proyectos e inversiones colectivas.',
            'Piscis' => 'Percepción intuitiva en momentos complejos; sanación espiritual y entrega profunda en la intimidad.'
        ],
        9 => [
            'Aries' => 'Pionero de ideas y convicciones audaces; viajes emprendidos con afán de conquista y autonomía.',
            'Tauro' => 'Filosofía de vida realista y sensata; viajes disfrutados con calma y contacto con la naturaleza.',
            'Géminis' => 'Estudioso de múltiples disciplinas; afición a aprender idiomas y viajar por interés cultural.',
            'Cáncer' => 'Búsqueda de sentido a través de la historia, las tradiciones ancestrales y la memoria colectiva.',
            'Leo' => 'Ideales nobles y carisma espiritual; vocación por enseñar y transmitir sabiduría con autoridad.',
            'Virgo' => 'Estudios superiores orientados a la ciencia y la utilidad concreta; análisis crítico de dogmas.',
            'Libra' => 'Atracción por la justicia universal, el derecho y la estética; viajes con refinamiento y cultura.',
            'Escorpio' => 'Investigación de verdades ocultas; lecturas o viajes que provocan una metamorfosis interior.',
            'Sagitario' => 'Posición cumbre del filósofo natural; sed constante de exploración mundial y sabiduría ética.',
            'Capricornio' => 'Estructuración metódica del conocimiento; respeto por las instituciones académicas clásicas.',
            'Acuario' => 'Pensamiento filosófico progresista y universal; interés por causas humanitarias globales.',
            'Piscis' => 'Espiritualidad mística y devocional; comprensión intuitiva de la unidad de todas las cosas.'
        ],
        10 => [
            'Aries' => 'Destacas como líder o emprendedor pionero; construyes tu reputación con iniciativa y valentía.',
            'Tauro' => 'Éxito profesional alcanzado con constancia en finanzas, arte, gastronomía o bienes de valor.',
            'Géminis' => 'Carrera en comunicación, comercio, periodismo o áreas que exijan agilidad mental y oratoria.',
            'Cáncer' => 'Vocación orientada al cuidado, la educación, la hotelería o el liderazgo protector con impacto social.',
            'Leo' => 'Aspiración a puestos de visibilidad, dirección o artes; ejerces autoridad con brillo y carisma.',
            'Virgo' => 'Reputación como profesional impecable y metódico en medicina, técnica, administración o ciencias.',
            'Libra' => 'Carrera destacada en diplomacia, derecho, diseño, mediación o relaciones públicas.',
            'Escorpio' => 'Poder e influencia en investigación, psicología, finanzas estratégicas o cargos decisorios.',
            'Sagitario' => 'Reconocimiento en el ámbito universitario, editorial, judicial o en entidades de alcance global.',
            'Capricornio' => 'Cumbre profesional forjada paso a paso con máxima disciplina; autoridad respetada y duradera.',
            'Acuario' => 'Vocación en tecnología, ciencia, sociología o movimientos comunitarios y de vanguardia.',
            'Piscis' => 'Realización en el arte, cine, sanación, psicología humanista o instituciones de ayuda social.'
        ],
        11 => [
            'Aries' => 'Amigos dinámicos e independientes; liderazgo en grupos con objetivos ambiciosos.',
            'Tauro' => 'Círculo de amistades sólido y leal; planes a futuro trazados con paciencia y sentido común.',
            'Géminis' => 'Red amplia y variada de contactos sociales; disfrute de reuniones y debates intelectuales.',
            'Cáncer' => 'Amistades tratadas como familia; involucramiento en causas comunitarias protectoras.',
            'Leo' => 'Figura central en tu grupo de amigos; metas a futuro inspiradas en proyectos nobles y creativos.',
            'Virgo' => 'Colaboración en grupos técnicos o de voluntariado práctico; amistades selectas y colaborativas.',
            'Libra' => 'Amistades cultivadas con cortesía y afinidad artística; participación en asociaciones culturales.',
            'Escorpio' => 'Amistades contadas pero con lealtad a toda prueba; grupos donde se viven cambios profundos.',
            'Sagitario' => 'Amigos internacionales y diversos; ideales grupales orientados a la libertad y la justicia.',
            'Capricornio' => 'Círculo selecto de personas con experiencia; metas a futuro planificadas con rigurosa prudencia.',
            'Acuario' => 'Comunidad y trabajo en red como máxima vocación; integración en causas vanguardistas.',
            'Piscis' => 'Solidaridad y empatía con colectivos vulnerables; aspiraciones con fuerte vocación humanitaria.'
        ],
        12 => [
            'Aries' => 'Fuerza interior oculta; necesitas canalizar la impulsividad reprimida mediante el autoconocimiento.',
            'Tauro' => 'Paz encontrada en el retiro sereno en la naturaleza; superación del apego a lo puramente material.',
            'Géminis' => 'Inconsciente poblado de ideas; la meditación y el silencio calman tu sobreestimulación mental.',
            'Cáncer' => 'Memoria ancestral muy sensible; conexión psíquica con las emociones profundas de tu linaje.',
            'Leo' => 'Fuerza creativa secreta; superación del miedo inconsciente a pasar desapercibido.',
            'Virgo' => 'Sanación espiritual a través del servicio desinteresado; liberación de la autoexigencia interna.',
            'Libra' => 'Búsqueda de paz trascendente en la quietud; disolución de la necesidad de aprobación ajena.',
            'Escorpio' => 'Inmenso poder regenerativo interior; capacidad innata para transmutar temores en sabiduría.',
            'Sagitario' => 'Guía espiritual interior y fe intuitiva; refugio en la introspección en momentos de duda.',
            'Capricornio' => 'Maestría forjada en la autosuficiencia silenciosa; madurez espiritual basada en la paciencia.',
            'Acuario' => 'Conexión con el inconsciente colectivo; visiones inspiradoras en momentos de soledad creadora.',
            'Piscis' => 'Acceso directo a la intuición profunda y los sueños lúcidos; comunión con el Todo y compasión universal.'
        ]
    ];
}

// ============================================================================
// 3. PROCESAMIENTO PRINCIPAL
// ============================================================================

$name = isset($_POST['name']) ? trim($_POST['name']) : 'Alejandro Magno';
$birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : '1995-05-18';
$birthtime = isset($_POST['birthtime']) ? $_POST['birthtime'] : '14:30';
$city = isset($_POST['city']) ? trim($_POST['city']) : 'Madrid, España';
$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 40.4168;
$lon = isset($_POST['lon']) ? floatval($_POST['lon']) : -3.7038;
$tz = isset($_POST['tz']) ? floatval($_POST['tz']) : 2.0;

$dateParts = explode('-', $birthdate);
$timeParts = explode(':', $birthtime);
$year = intval($dateParts[0]);
$month = intval($dateParts[1]);
$day = intval($dateParts[2]);
$hour = intval($timeParts[0]);
$min = intval($timeParts[1]);

$jd = AstroEngine::getJulianDay($year, $month, $day, $hour, $min, $tz);
$ascDegree = AstroEngine::calculateAscendant($jd, $lat, $lon);
$ascInfo = AstroEngine::getZodiacInfo($ascDegree);
$planets = AstroEngine::getPlanetaryPositions($jd);
$houses = AstroEngine::getHouses($ascDegree);
$aspects = AstroEngine::calculateAspects($planets);

// Asignación de Estado Celeste y ubicación en Casas (Estado Terrestre)
$sunInfo = null;
$moonInfo = null;

foreach ($planets as $k => $p) {
    $zInfo = AstroEngine::getZodiacInfo($p['deg']);
    $dignity = AstroEngine::evaluateDignity($p['name'], $zInfo['sign']);
    
    // Identificar en qué casa cae el planeta
    $assignedHouse = 1;
    for ($h = 1; $h <= 12; $h++) {
        $hStart = $houses[$h]['degree'];
        $hEnd = fmod($hStart + 30.0, 360.0);
        if ($hStart < $hEnd) {
            if ($p['deg'] >= $hStart && $p['deg'] < $hEnd) { $assignedHouse = $h; break; }
        } else {
            if ($p['deg'] >= $hStart || $p['deg'] < $hEnd) { $assignedHouse = $h; break; }
        }
    }

    $planets[$k]['zodiac'] = $zInfo;
    $planets[$k]['dignity'] = $dignity;
    $planets[$k]['house'] = $assignedHouse;
    $houses[$assignedHouse]['planets'][] = $planets[$k];

    if ($p['name'] === 'Sol') $sunInfo = $zInfo;
    if ($p['name'] === 'Luna') $moonInfo = $zInfo;
}

// Preparación del JSON para Popups interactivos
$interactiveData = [
    'houses' => [],
    'planets' => []
];

foreach ($houses as $hNum => $h) {
    $signName = $h['zodiac']['sign'];
    $rulerName = $h['zodiac']['ruler'];
    
    // Buscar dónde está el planeta regente de esta casa
    $rulerLocation = 'Posición en carta';
    foreach ($planets as $pl) {
        if (strpos($rulerName, $pl['name']) !== false) {
            $rulerLocation = "{$pl['name']} en {$pl['zodiac']['sign']} (Casa {$pl['house']})";
            break;
        }
    }

    $occupants = [];
    foreach ($h['planets'] as $oc) {
        $occupants[] = "{$oc['glyph']} {$oc['name']} ({$oc['zodiac']['degree_in_sign']})";
    }

    $interactiveData['houses'][$hNum] = [
        'number' => $hNum,
        'name' => AstroDelineations::$houseThemes[$hNum]['name'],
        'title' => AstroDelineations::$houseThemes[$hNum]['title'],
        'scenario' => AstroDelineations::$houseThemes[$hNum]['scenario'],
        'sign' => $signName,
        'symbol' => $h['zodiac']['symbol'],
        'deg' => $h['zodiac']['degree_in_sign'],
        'color' => $h['zodiac']['color'],
        'ruler' => $rulerName,
        'rulerLoc' => $rulerLocation,
        'occupants' => empty($occupants) ? 'Sin planetas presentes (se interpreta por su regente)' : implode(', ', $occupants),
        'text' => AstroDelineations::$houseInSign[$hNum][$signName]
    ];
}

foreach ($planets as $p) {
    $interactiveData['planets'][$p['name']] = [
        'name' => $p['name'],
        'glyph' => $p['glyph'],
        'category' => $p['category'],
        'function' => $p['function'],
        'sign' => $p['zodiac']['sign'],
        'symbol' => $p['zodiac']['symbol'],
        'deg' => $p['zodiac']['degree_in_sign'],
        'element' => $p['zodiac']['element'],
        'house' => $p['house'],
        'dignity' => $p['dignity']['status'],
        'nature' => $p['dignity']['nature'],
        'dignityDesc' => $p['dignity']['desc'],
        'retro' => $p['retro'] ? 'Retrógrado (Rx)' : 'Directo',
        'retroDesc' => $p['retro'] ? 'Función gestionada con profunda consciencia reflexiva y maestría técnica.' : 'Expresión directa y orientada a la experiencia presente.',
        'color' => $p['zodiac']['color']
    ];
}

$jsonData = json_encode($interactiveData);

// ============================================================================
// 4. GENERADOR DE LA RUEDA ASTRAL VECTORIAL SVG (SIN DESPLAZAMIENTOS AL HOVER)
// ============================================================================

function generateStableAstralWheelSVG($ascDeg, $planets, $houses, $aspects) {
    $cx = 250;
    $cy = 250;
    $rOuter = 232;
    $rSigns = 195;
    $rHouses = 158;
    $rAspectRing = 115;

    $svg = '<svg viewBox="0 0 500 500" class="w-full h-auto max-w-[440px] mx-auto drop-shadow-2xl select-none" id="astralWheelSvg">';
    
    // Círculos concéntricos de base
    $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$rOuter.'" fill="#090d16" stroke="#334155" stroke-width="2"/>';
    $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$rSigns.'" fill="#0f172a" stroke="#475569" stroke-width="1.5"/>';
    $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$rHouses.'" fill="#030712" stroke="#334155" stroke-width="1"/>';
    $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$rAspectRing.'" fill="#020617" stroke="#1e293b" stroke-width="1"/>';

    // 12 Signos
    for ($i = 0; $i < 12; $i++) {
        $startAngle = 180 - ($i * 30);
        $radStart = deg2rad($startAngle);

        $x1 = $cx + $rOuter * cos($radStart);
        $y1 = $cy - $rOuter * sin($radStart);
        $x2 = $cx + $rSigns * cos($radStart);
        $y2 = $cy - $rSigns * sin($radStart);

        $svg .= "<line x1='{$x1}' y1='{$y1}' x2='{$x2}' y2='{$y2}' stroke='#334155' stroke-width='1.5'/>";

        $midRad = deg2rad($startAngle - 15);
        $rGlyph = ($rOuter + $rSigns) / 2;
        $gx = $cx + $rGlyph * cos($midRad);
        $gy = $cy - $rGlyph * sin($midRad) + 5;

        $sign = AstroEngine::$zodiacSigns[$i];
        $svg .= "<text x='{$gx}' y='{$gy}' fill='{$sign['color']}' font-size='16' font-weight='bold' text-anchor='middle' font-family='sans-serif'>{$sign['symbol']}</text>";
    }

    // Cuñas interactivas de las 12 Casas (Clickeables)
    foreach ($houses as $hNum => $h) {
        $houseAngle = 180 - ($hNum - 1) * 30;
        $nextHouseAngle = $houseAngle - 30;
        
        $rad1 = deg2rad($houseAngle);
        $rad2 = deg2rad($nextHouseAngle);

        $p1x = $cx + $rSigns * cos($rad1);
        $p1y = $cy - $rSigns * sin($rad1);
        $p2x = $cx + $rSigns * cos($rad2);
        $p2y = $cy - $rSigns * sin($rad2);
        $p3x = $cx + $rAspectRing * cos($rad2);
        $p3y = $cy - $rAspectRing * sin($rad2);
        $p4x = $cx + $rAspectRing * cos($rad1);
        $p4y = $cy - $rAspectRing * sin($rad1);

        $pathD = "M {$p1x} {$p1y} A {$rSigns} {$rSigns} 0 0 1 {$p2x} {$p2y} L {$p3x} {$p3y} A {$rAspectRing} {$rAspectRing} 0 0 0 {$p4x} {$p4y} Z";

        $svg .= "<path d='{$pathD}' fill='transparent' class='house-wedge cursor-pointer transition-colors duration-150' data-house='{$hNum}' />";

        $isAngular = in_array($hNum, [1, 4, 7, 10]);
        $stroke = $isAngular ? '#38bdf8' : '#334155';
        $strokeW = $isAngular ? '2' : '1';
        $dash = $isAngular ? 'none' : '2,2';

        $svg .= "<line x1='{$p1x}' y1='{$p1y}' x2='{$p4x}' y2='{$p4y}' stroke='{$stroke}' stroke-width='{$strokeW}' stroke-dasharray='{$dash}' pointer-events='none'/>";

        $numRad = deg2rad($houseAngle - 15);
        $rNum = ($rHouses + $rAspectRing) / 2;
        $nx = $cx + $rNum * cos($numRad);
        $ny = $cy - $rNum * sin($numRad) + 3.5;
        $svg .= "<text x='{$nx}' y='{$ny}' fill='#64748b' font-size='10' font-weight='700' text-anchor='middle' font-family='sans-serif' pointer-events='none'>{$hNum}</text>";
    }

    // Estrella Geométrica de Aspectos
    $planetCoords = [];
    foreach ($planets as $idx => $p) {
        $relAngle = 180 - ($p['deg'] - $ascDeg);
        $radP = deg2rad($relAngle);
        $planetCoords[$p['name']] = [
            'x' => $cx + $rAspectRing * cos($radP),
            'y' => $cy - $rAspectRing * sin($radP),
            'color' => ($p['name'] === 'Sol' ? '#f59e0b' : ($p['name'] === 'Luna' ? '#e2e8f0' : ($p['name'] === 'Marte' ? '#ef4444' : '#38bdf8')))
        ];
    }

    foreach ($aspects as $asp) {
        $c1 = $planetCoords[$asp['p1']['name']];
        $c2 = $planetCoords[$asp['p2']['name']];
        $dash = ($asp['name'] === 'Oposición') ? '4,3' : 'none';
        $svg .= "<line x1='{$c1['x']}' y1='{$c1['y']}' x2='{$c2['x']}' y2='{$c2['y']}' stroke='{$asp['color']}' stroke-width='1.3' stroke-dasharray='{$dash}' stroke-opacity='0.85'/>";
    }

    // Planetas Estables Clickeables (SIN DESPLAZAMIENTOS AL HOVER)
    foreach ($planets as $idx => $p) {
        $relAngle = 180 - ($p['deg'] - $ascDeg);
        $radP = deg2rad($relAngle);
        $rPos = ($rSigns + $rHouses) / 2 + (($idx % 2 == 0) ? 6 : -6);

        $px = $cx + $rPos * cos($radP);
        $py = $cy - $rPos * sin($radP) + 5;

        $dotX = $cx + $rAspectRing * cos($radP);
        $dotY = $cy - $rAspectRing * sin($radP);
        $svg .= "<circle cx='{$dotX}' cy='{$dotY}' r='2.5' fill='{$planetCoords[$p['name']]['color']}'/>";

        // Grupo clickeable estático: cambia color de borde pero NO se mueve de lugar
        $svg .= "<g class='planet-btn cursor-pointer' data-planet='{$p['name']}'>";
        $svg .= "<circle cx='{$px}' cy='".($py - 5)."' r='13' fill='#090d16' stroke='{$planetCoords[$p['name']]['color']}' stroke-width='1.5' class='planet-circle' />";
        $svg .= "<text x='{$px}' y='{$py}' fill='{$planetCoords[$p['name']]['color']}' font-size='14' font-weight='bold' text-anchor='middle' font-family='sans-serif' pointer-events='none'>{$p['glyph']}</text>";
        if ($p['retro']) {
            $svg .= "<text x='".($px + 9)."' y='".($py - 9)."' fill='#f43f5e' font-size='8' font-weight='bold' text-anchor='middle' font-family='sans-serif' pointer-events='none'>R</text>";
        }
        $svg .= "</g>";
    }

    // Eje Ascendente (ASC)
    $svg .= "<line x1='".($cx - $rOuter)."' y1='{$cy}' x2='".($cx - $rAspectRing)."' y2='{$cy}' stroke='#e11d48' stroke-width='2.5'/>";
    $svg .= "<polygon points='".($cx - $rOuter + 8).",".($cy - 4)." ".($cx - $rOuter).",{$cy} ".($cx - $rOuter + 8).",".($cy + 4)."' fill='#e11d48'/>";
    $svg .= "<text x='".($cx - $rOuter - 7)."' y='".($cy + 4)."' fill='#e11d48' font-size='10' font-weight='bold' text-anchor='end'>ASC</text>";

    $svg .= '</svg>';
    return $svg;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTRALISTA PRO - Diagnóstico Psicoastrológico Natal</title>
    
    <meta property="og:title" content="ASTRALISTA PRO - Análisis y Diagnóstico Psicoastrológico">
    <meta property="og:description" content="Generador profesional de Cartas Astrales con cálculo de Estado Celeste, Casas y Estrella de Aspectos.">
    <meta property="og:image" content="logo.png">
    <meta property="og:type" content="website">

    <link rel="icon" type="image/png" href="logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #030712;
            color: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(245, 158, 11, 0.06) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.06) 0px, transparent 50%);
        }
        .font-cinzel { font-family: 'Cinzel', serif; }
        #map { height: 230px; width: 100%; border-radius: 0.75rem; z-index: 1; }

        /* Cuñas de casas interactivas */
        .house-wedge:hover {
            fill: rgba(245, 158, 11, 0.2) !important;
        }

        /* Resaltado del Planeta al pasar el ratón (ESTABLE, SIN MOVERSE) */
        .planet-btn:hover .planet-circle {
            stroke: #f59e0b !important;
            stroke-width: 2.5px !important;
            fill: #1e293b !important;
        }

        /* Animaciones del Modal Popup (Expansión y Contracción) */
        @keyframes popupExpand {
            0% { transform: scale(0.7) translateY(15px); opacity: 0; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }
        @keyframes popupContract {
            0% { transform: scale(1) translateY(0); opacity: 1; }
            100% { transform: scale(0.7) translateY(15px); opacity: 0; }
        }

        .modal-animate-in {
            animation: popupExpand 0.24s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        .modal-animate-out {
            animation: popupContract 0.18s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        @media print {
            @page { size: A4 portrait; margin: 8mm 10mm 8mm 10mm; }
            body { background: #ffffff !important; color: #0f172a !important; font-size: 11px !important; }
            .no-print { display: none !important; }
            .print-clean { background: #ffffff !important; border: 1px solid #cbd5e1 !important; color: #0f172a !important; box-shadow: none !important; break-inside: avoid; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="antialiased min-h-screen py-6 px-3 md:px-8">

    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- HEADER WEB -->
        <header class="flex flex-col md:flex-row items-center justify-between gap-4 border-b border-slate-800/80 pb-6 no-print">
            <div class="flex items-center gap-3.5">
                <img src="logo.png" alt="Astralista Logo" onerror="this.src='https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=100&q=80'" class="w-12 h-12 object-contain drop-shadow-[0_0_15px_rgba(245,158,11,0.5)]">
                <div>
                    <h1 class="text-2xl md:text-3xl font-cinzel font-black tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-500">
                        ASTRALISTA PRO
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium">Arquitectura Natal: Diagnóstico de Estados Celestes y Terrestres</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span> Rueda Interactiva & Aspectos
                </span>
            </div>
        </header>

        <!-- FORMULARIO & MAPA INTERACTIVO -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 md:p-8 backdrop-blur shadow-2xl no-print">
            <form method="POST" action="" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div class="space-y-4">
                        <h3 class="text-amber-400 font-cinzel text-base font-bold border-b border-slate-800 pb-2">1. Insumos Natales</h3>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Nombre Completo</label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-slate-100 focus:outline-none focus:border-amber-400 text-sm">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Fecha de Nacimiento</label>
                            <input type="date" name="birthdate" required value="<?php echo htmlspecialchars($birthdate); ?>" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-slate-100 focus:outline-none focus:border-amber-400 text-sm">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Hora Exacta</label>
                            <input type="time" name="birthtime" required value="<?php echo htmlspecialchars($birthtime); ?>" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-slate-100 focus:outline-none focus:border-amber-400 text-sm">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-amber-400 font-cinzel text-base font-bold border-b border-slate-800 pb-2">2. Coordenadas Geográficas</h3>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Buscar Ciudad</label>
                            <div class="flex gap-2">
                                <input type="text" id="citySearch" placeholder="Ej: Barcelona, Buenos Aires, Bogotá..." class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-400 text-sm">
                                <button type="button" onclick="searchLocation()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs transition">Buscar</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Ciudad Seleccionada</label>
                            <input type="text" name="city" id="cityName" required value="<?php echo htmlspecialchars($city); ?>" class="w-full bg-slate-800/70 border border-slate-700 rounded-xl px-4 py-2 text-slate-200 text-sm">
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold">Latitud</label>
                                <input type="number" step="any" name="lat" id="latInput" value="<?php echo $lat; ?>" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-1.5 text-xs text-slate-200">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold">Longitud</label>
                                <input type="number" step="any" name="lon" id="lonInput" value="<?php echo $lon; ?>" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-1.5 text-xs text-slate-200">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold">Zona UTC</label>
                                <input type="number" step="0.5" name="tz" id="tzInput" value="<?php echo $tz; ?>" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-1.5 text-xs text-slate-200">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                            <h3 class="text-amber-400 font-cinzel text-base font-bold">3. Ajuste de Precisión</h3>
                            <span class="text-[10px] text-slate-400">Arrastra el marcador</span>
                        </div>
                        <div id="map"></div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-800">
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold px-8 py-3.5 rounded-xl shadow-lg transition text-sm uppercase tracking-wider">
                        Generar Retrato Energético Completo ✦
                    </button>
                </div>
            </form>
        </div>

        <!-- ================================================================= -->
        <!-- REPORTE DE CARTA ASTRAL PROFESIONAL (COMPATIBLE A4) -->
        <!-- ================================================================= -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-10 shadow-2xl space-y-10 print-clean">
            
            <div class="flex justify-between items-center border-b border-slate-800 pb-4 no-print">
                <div class="text-xs text-slate-400">
                    <span class="text-amber-400 font-bold">Interacción Activa:</span> Haz clic sobre cualquier <strong class="text-slate-200">casa</strong> o <strong class="text-slate-200">planeta</strong> para desplegar su diagnóstico psicológico.
                </div>
                <button onclick="window.print()" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-2.5 rounded-xl text-xs transition flex items-center gap-2 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Imprimir en Hoja A4</span>
                </button>
            </div>

            <!-- ENCABEZADO DEL REPORTE -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-slate-800 pb-6 gap-4">
                <div class="flex items-center gap-4">
                    <img src="logo.png" alt="Logo" onerror="this.src='https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=80&q=80'" class="w-14 h-14 object-contain rounded-xl bg-slate-950 p-1 border border-slate-800">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-amber-500">Informe Psicoastrológico Natal</span>
                        <h2 class="text-2xl md:text-4xl font-cinzel font-black text-slate-100"><?php echo htmlspecialchars($name); ?></h2>
                        <p class="text-xs md:text-sm text-slate-400 mt-0.5">
                            <?php echo date('d \d\e F \d\e Y', strtotime($birthdate)); ?> • <?php echo $birthtime; ?> hs (UTC <?php echo ($tz >= 0 ? "+$tz" : $tz); ?>)
                        </p>
                    </div>
                </div>
                <div class="text-left md:text-right text-xs text-slate-400 space-y-1">
                    <p><strong class="text-slate-200">Lugar:</strong> <?php echo htmlspecialchars($city); ?></p>
                    <p><strong class="text-slate-200">Coordenadas:</strong> <?php echo round($lat, 4); ?>° N, <?php echo round($lon, 4); ?>° E</p>
                    <p><strong class="text-slate-200">Filtro Ascendente (AC):</strong> <?php echo $ascInfo['sign']; ?> (<?php echo $ascInfo['degree_in_sign']; ?>)</p>
                </div>
            </div>

            <!-- SÍNTESIS DE LA TRÍADA ESENCIAL (SOL + LUNA + ASCENDENTE) -->
            <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-5 space-y-4">
                <div class="flex justify-between items-center border-b border-slate-800/80 pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 font-cinzel">Tríada Esencial: Motor de Identidad e Interfaz</h3>
                    <span class="text-[10px] text-slate-400">Núcleo + Sub-sistema Emocional + Filtro de Realidad</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase text-amber-400">Sol (Núcleo)</span>
                            <span class="text-xs font-bold font-mono text-amber-200"><?php echo $sunInfo['symbol']; ?> <?php echo $sunInfo['sign']; ?></span>
                        </div>
                        <p class="text-xs text-slate-300">Propósito central, vitalidad y voluntad consciente enfocada en los atributos de <?php echo $sunInfo['sign']; ?>.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/30 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase text-blue-400">Luna (Seguridad)</span>
                            <span class="text-xs font-bold font-mono text-blue-200"><?php echo $moonInfo['symbol']; ?> <?php echo $moonInfo['sign']; ?></span>
                        </div>
                        <p class="text-xs text-slate-300">Mecanismos de respuesta bio-emocional y refugio nutricio modelados bajo <?php echo $moonInfo['sign']; ?>.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase text-rose-400">Ascendente (Interfaz)</span>
                            <span class="text-xs font-bold font-mono text-rose-200"><?php echo $ascInfo['symbol']; ?> <?php echo $ascInfo['sign']; ?></span>
                        </div>
                        <p class="text-xs text-slate-300"><?php echo AstroDelineations::$ascendantLogic[$ascInfo['sign']]; ?></p>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 1: RUEDA SVG INTERACTIVA Y ESTADO CELESTE -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <!-- Rueda Astral SVG -->
                <div class="lg:col-span-6 flex flex-col items-center justify-center p-3 bg-slate-950/70 rounded-2xl border border-slate-800">
                    <?php echo generateStableAstralWheelSVG($ascDegree, $planets, $houses, $aspects); ?>
                    
                    <div class="grid grid-cols-3 gap-x-4 gap-y-1 mt-3 text-[10px] text-slate-400 border-t border-slate-800 pt-2 w-full text-center">
                        <span class="flex items-center justify-center gap-1.5"><span class="w-2.5 h-0.5 bg-[#10b981]"></span> Trígono (120°)</span>
                        <span class="flex items-center justify-center gap-1.5"><span class="w-2.5 h-0.5 bg-[#ef4444]"></span> Cuadratura (90°)</span>
                        <span class="flex items-center justify-center gap-1.5"><span class="w-2.5 h-0.5 bg-[#f97316]"></span> Oposición (180°)</span>
                        <span class="flex items-center justify-center gap-1.5"><span class="w-2.5 h-0.5 bg-[#06b6d4]"></span> Sextil (60°)</span>
                        <span class="flex items-center justify-center gap-1.5"><span class="w-2.5 h-0.5 bg-[#eab308]"></span> Conjunción (0°)</span>
                        <span class="flex items-center justify-center gap-1.5 font-bold text-rose-400">ASC: Ascendente</span>
                    </div>
                </div>

                <!-- Tabla de Diagnóstico: Estado Celeste & Dignidades -->
                <div class="lg:col-span-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Diagnóstico de Actores (Estado Celeste)</h4>
                        <span class="text-[10px] text-amber-400">10 Cuerpos Planetarios</span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-800/80 text-slate-300 uppercase font-semibold text-[10px]">
                                <tr>
                                    <th class="px-2.5 py-2">Planeta</th>
                                    <th class="px-2.5 py-2">Signo</th>
                                    <th class="px-2.5 py-2">Casa</th>
                                    <th class="px-2.5 py-2">Dignidad Celeste</th>
                                    <th class="px-2.5 py-2 text-right">Mov.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 bg-slate-950/40">
                                <?php foreach ($planets as $p): ?>
                                <tr class="hover:bg-slate-800/40 transition cursor-pointer" onclick="openPlanetModal('<?php echo $p['name']; ?>')">
                                    <td class="px-2.5 py-1.5 font-medium text-slate-200 flex items-center gap-1.5">
                                        <span class="text-amber-400 font-bold"><?php echo $p['glyph']; ?></span>
                                        <?php echo $p['name']; ?>
                                    </td>
                                    <td class="px-2.5 py-1.5 text-slate-300 font-semibold"><?php echo $p['zodiac']['symbol']; ?> <?php echo $p['zodiac']['sign']; ?></td>
                                    <td class="px-2.5 py-1.5 text-slate-400 font-mono">C-<?php echo $p['house']; ?></td>
                                    <td class="px-2.5 py-1.5">
                                        <span class="text-[10px] px-2 py-0.5 rounded font-semibold <?php 
                                            echo ($p['dignity']['status'] === 'Domicilio' || $p['dignity']['status'] === 'Exaltación') ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' :
                                                (($p['dignity']['status'] === 'Exilio' || $p['dignity']['status'] === 'Caída') ? 'bg-rose-950 text-rose-300 border border-rose-800' :
                                                'bg-slate-800 text-slate-300');
                                        ?>">
                                            <?php echo $p['dignity']['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-2.5 py-1.5 text-right font-mono text-[10px]">
                                        <?php if ($p['retro']): ?>
                                            <span class="text-rose-400 font-bold">Rx</span>
                                        <?php else: ?>
                                            <span class="text-slate-500">Dir</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Aspectos Mayores Detectados -->
                    <div class="space-y-1.5">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Diálogo Angular (Aspectos Activos):</span>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach (array_slice($aspects, 0, 6) as $asp): ?>
                            <div class="px-2 py-1 rounded-lg bg-slate-800/80 border border-slate-700 text-[10px] flex items-center gap-1.5">
                                <span style="color: <?php echo $asp['color']; ?>;" class="font-bold"><?php echo $asp['p1']['glyph']; ?> <?php echo $asp['symbol']; ?> <?php echo $asp['p2']['glyph']; ?></span>
                                <span class="text-slate-300"><?php echo $asp['name']; ?></span>
                                <span class="text-slate-500 font-mono">(<?php echo $asp['orb']; ?>°)</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SALTO DE PÁGINA PARA IMPRESIÓN A4 -->
            <div class="page-break"></div>

            <!-- BLOQUE 2: SÍNTESIS EN LOS 3 EJES CLAVE DE IMPACTO -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <h3 class="text-xl font-cinzel font-black text-amber-300 flex items-center gap-2">
                    <span>✦</span> Síntesis Estratégica en Áreas Clave
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Eje 1: Carrera y Vocación -->
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block font-cinzel">1. Carrera y Proyección (X, II, VI)</span>
                        <p class="text-xs text-slate-300 leading-relaxed text-justify">
                            El Medio Cielo en <strong><?php echo $houses[10]['zodiac']['sign']; ?></strong> marca la cúspide vocacional, articulándose con la gestión de recursos (Casa II en <?php echo $houses[2]['zodiac']['sign']; ?>) y la eficiencia técnica cotidiana (Casa VI en <?php echo $houses[6]['zodiac']['sign']; ?>).
                        </p>
                    </div>
                    <!-- Eje 2: Vínculos y Relaciones -->
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400 block font-cinzel">2. Vínculos y Relaciones (VII, VIII)</span>
                        <p class="text-xs text-slate-300 leading-relaxed text-justify">
                            El Descendente en <strong><?php echo $houses[7]['zodiac']['sign']; ?></strong> establece la dinámica de compromisos directos con el Otro, profundizándose en la transformación emocional y fusión de recursos de la Casa VIII en <?php echo $houses[8]['zodiac']['sign']; ?>.
                        </p>
                    </div>
                    <!-- Eje 3: Vida Interior y Raíces -->
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block font-cinzel">3. Vida Interior y Raíces (IV, XII)</span>
                        <p class="text-xs text-slate-300 leading-relaxed text-justify">
                            El Fondo de Cielo en <strong><?php echo $houses[4]['zodiac']['sign']; ?></strong> cimenta el refugio privado y la seguridad ontológica, interactuando con los procesos de integración inconsciente de la Casa XII en <?php echo $houses[12]['zodiac']['sign']; ?>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 3: LAS 12 CASAS (ESTADO TERRESTRE COMPLETO) -->
            <div class="space-y-6 pt-6 border-t border-slate-800">
                <div class="border-b border-slate-800 pb-3 flex justify-between items-end">
                    <div>
                        <h3 class="text-2xl font-cinzel font-black text-amber-300">
                            Escenarios de Experiencia: Las 12 Casas Astrológicas
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Diagnóstico integrado: Cúspide (Tono) + Planeta Regente (Director) + Planetas Presentes (Actores):</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($houses as $hNum => $h): ?>
                    <?php 
                        $signName = $h['zodiac']['sign'];
                        $theme = AstroDelineations::$houseThemes[$hNum];
                        $delineation = AstroDelineations::$houseInSign[$hNum][$signName];
                        $ruler = $h['zodiac']['ruler'];
                    ?>
                    <div onclick="openHouseModal(<?php echo $hNum; ?>)" class="bg-slate-950/60 border border-slate-800/90 rounded-2xl p-4 space-y-2 hover:border-amber-500/50 hover:bg-slate-900/60 transition cursor-pointer">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-cinzel font-bold text-amber-400 block"><?php echo $theme['name']; ?>: <?php echo $theme['title']; ?></span>
                                <span class="text-sm font-bold text-slate-100 flex items-center gap-1.5 mt-0.5">
                                    <span style="color: <?php echo $h['zodiac']['color']; ?>;"><?php echo $h['zodiac']['symbol']; ?></span> 
                                    Cúspide en <?php echo $signName; ?> 
                                    <span class="font-mono text-slate-400 text-xs font-normal">(<?php echo $h['zodiac']['degree_in_sign']; ?>)</span>
                                </span>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded font-semibold bg-slate-800 text-slate-300">Reg: <?php echo $ruler; ?></span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed text-justify">
                            <?php echo $delineation; ?>
                        </p>
                        <?php if (!empty($h['planets'])): ?>
                        <div class="pt-1.5 border-t border-slate-800/60 flex items-center gap-1.5 text-[10px] text-amber-300">
                            <span class="font-bold">Actores presentes:</span>
                            <?php foreach ($h['planets'] as $oc): ?>
                                <span class="bg-slate-900 px-1.5 py-0.5 rounded border border-slate-700"><?php echo $oc['glyph']; ?> <?php echo $oc['name']; ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PIE DE REPORTE -->
            <footer class="pt-6 border-t border-slate-800 text-center text-xs text-slate-500 flex flex-col md:flex-row justify-between items-center gap-2">
                <div class="flex items-center gap-2">
                    <img src="logo.png" alt="Logo" class="w-4 h-4 object-contain">
                    <span>Generado con ASTRALISTA PRO - Sistema de Análisis y Diagnóstico Psicoastrológico.</span>
                </div>
                <span>Documento Natal Confidencial</span>
            </footer>

        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL POPUP (EXPANSIÓN Y CONTRACCIÓN FLUIDA) -->
    <!-- ================================================================= -->
    <div id="astroModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200">
        <div id="modalBox" class="bg-slate-900 border border-amber-500/40 rounded-3xl p-6 md:p-8 max-w-lg w-full shadow-[0_0_50px_rgba(0,0,0,0.8)] relative text-slate-100">
            
            <button onclick="closeModal()" class="absolute top-5 right-5 text-slate-400 hover:text-white bg-slate-800/80 hover:bg-slate-700 rounded-full p-2 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div id="modalBody" class="space-y-4"></div>

            <div class="mt-6 pt-4 border-t border-slate-800 flex justify-end">
                <button onclick="closeModal()" class="bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold px-5 py-2 rounded-xl transition">
                    Cerrar Diagnóstico
                </button>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- JAVASCRIPT: MAPA LEAFLET Y POPUP INTERACTIVO ESTABLE -->
    <!-- ================================================================= -->
    <script>
        const astroData = <?php echo $jsonData; ?>;
        
        let curLat = <?php echo $lat; ?>;
        let curLon = <?php echo $lon; ?>;
        
        const map = L.map('map').setView([curLat, curLon], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([curLat, curLon], { draggable: true }).addTo(map);

        function updateCoords(lat, lon) {
            document.getElementById('latInput').value = lat.toFixed(4);
            document.getElementById('lonInput').value = lon.toFixed(4);
        }

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateCoords(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        async function searchLocation() {
            const query = document.getElementById('citySearch').value;
            if (!query) return;

            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
                const data = await res.json();

                if (data && data.length > 0) {
                    const first = data[0];
                    const newLat = parseFloat(first.lat);
                    const newLon = parseFloat(first.lon);

                    map.setView([newLat, newLon], 10);
                    marker.setLatLng([newLat, newLon]);
                    updateCoords(newLat, newLon);

                    const nameParts = first.display_name.split(',');
                    document.getElementById('cityName').value = nameParts[0] + ', ' + (nameParts.slice(-1)[0] || '').trim();
                    document.getElementById('tzInput').value = Math.round(newLon / 15);
                } else {
                    alert('No se localizó la ciudad ingresada.');
                }
            } catch (err) {
                console.error("Error geocodificando:", err);
            }
        }

        async function reverseGeocode(lat, lon) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                const data = await res.json();
                if (data && data.address) {
                    const place = data.address.city || data.address.town || data.address.village || data.address.county || "Lugar fijado";
                    const country = data.address.country || "";
                    document.getElementById('cityName').value = `${place}, ${country}`;
                }
            } catch(e) {}
        }

        // --- CONTROL DEL MODAL POPUP ---
        const modal = document.getElementById('astroModal');
        const modalBox = document.getElementById('modalBox');
        const modalBody = document.getElementById('modalBody');

        function openHouseModal(hNum) {
            const h = astroData.houses[hNum];
            if (!h) return;

            modalBody.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="text-3xl p-2 rounded-2xl bg-slate-950 border border-slate-800" style="color: ${h.color};">${h.symbol}</span>
                    <div>
                        <span class="text-xs uppercase font-bold tracking-widest text-amber-400">Escenario Terrestre (Casa Astrológica)</span>
                        <h3 class="text-xl font-cinzel font-black text-white">${h.name}: ${h.title}</h3>
                        <p class="text-xs text-slate-400">${h.scenario}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase">Cúspide / Matiz</span>
                        <span class="font-bold text-white">${h.sign} (${h.deg})</span>
                    </div>
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase">Planeta Regente (Director)</span>
                        <span class="font-bold text-amber-300">${h.rulerLoc}</span>
                    </div>
                </div>
                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl text-xs">
                    <span class="text-slate-400 block text-[10px] uppercase">Actores Presentes en este Escenario</span>
                    <span class="font-medium text-slate-200">${h.occupants}</span>
                </div>
                <div class="space-y-1">
                    <h4 class="text-xs uppercase font-bold text-slate-300 tracking-wider">Interpretación Dinámica:</h4>
                    <p class="text-xs md:text-sm text-slate-300 leading-relaxed text-justify bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                        ${h.text}
                    </p>
                </div>
            `;
            showModal();
        }

        function openPlanetModal(pName) {
            const p = astroData.planets[pName];
            if (!p) return;

            modalBody.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="text-3xl p-2 rounded-2xl bg-slate-950 border border-slate-800 font-bold" style="color: ${p.color};">${p.glyph}</span>
                    <div>
                        <span class="text-xs uppercase font-bold tracking-widest text-amber-400">Actor del Psiquismo (${p.category})</span>
                        <h3 class="text-xl font-cinzel font-black text-white">${p.name}</h3>
                        <p class="text-xs text-slate-400">${p.function}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase">Estado Celeste</span>
                        <span class="font-bold text-white">${p.symbol} ${p.sign} (${p.deg})</span>
                        <span class="text-amber-400 text-[11px] block font-semibold">${p.dignity} (${p.nature})</span>
                    </div>
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase">Estado Terrestre</span>
                        <span class="font-bold text-white">Casa ${p.house}</span>
                        <span class="text-rose-400 text-[11px] block font-semibold">${p.retro}</span>
                    </div>
                </div>
                <div class="space-y-2 text-xs text-slate-300 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                    <p><strong>Diagnóstico de Dignidad:</strong> ${p.dignityDesc}</p>
                    <p><strong>Dinámica de Desplazamiento:</strong> ${p.retroDesc}</p>
                </div>
            `;
            showModal();
        }

        function showModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalBox.classList.remove('modal-animate-out');
                modalBox.classList.add('modal-animate-in');
            }, 10);
        }

        function closeModal() {
            modalBox.classList.remove('modal-animate-in');
            modalBox.classList.add('modal-animate-out');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 180);
        }

        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

        document.querySelectorAll('.house-wedge').forEach(wedge => {
            wedge.addEventListener('click', () => {
                openHouseModal(wedge.getAttribute('data-house'));
            });
        });

        document.querySelectorAll('.planet-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openPlanetModal(btn.getAttribute('data-planet'));
            });
        });
    </script>
</body>
</html>