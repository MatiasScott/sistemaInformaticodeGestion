<?php

/**
 * Catálogo del "Modelo de Evaluación Externa 2024 con fines de acreditación de los
 * Institutos Superiores Técnicos y Tecnológicos" (CACES).
 *
 * Las fórmulas cuantitativas se escriben con variables en MAYÚSCULAS (solo letras) para que el
 * sistema las detecte y evalúe. Los indicadores porcentuales se expresan como fracción
 * (p. ej. 0.50 = 50 %), igual que su valor estándar.
 */

const MODELO_CACES_CODIGO_PREFIJO = 'ISTS-';

const MODELO_CACES_CRITERIOS = [
    1 => '1. ORGANIZACIÓN',
    2 => '2. INFRAESTRUCTURA',
    3 => '3. PROFESORES',
    4 => '4. DOCENCIA',
    5 => '5. INVESTIGACIÓN + DESARROLLO E INNOVACIÓN',
    6 => '6. VINCULACIÓN CON LA SOCIEDAD',
];

const MODELO_CACES_SUBCRITERIOS = [
    '1.1' => '1.1 Planificación y desarrollo',
    '1.2' => '1.2 Gestión social',
    '2.1' => '2.1 Infraestructura básica',
    '3.1' => '3.1 Selección y formación previa',
    '3.2' => '3.2 Organización y desarrollo',
    '3.3' => '3.3 Remuneraciones',
    '4.1' => '4.1 Formación académica',
    '4.2' => '4.2 Informatización del proceso de enseñanza',
    '4.3' => '4.3 Formación ciudadana',
    '4.4' => '4.4 Formación práctica',
    '4.5' => '4.5 Biblioteca',
    '5.1' => '5.1 I+D y publicaciones científicas y técnicas',
    '5.2' => '5.2 Innovación',
    '6.1' => '6.1 Planificación y ejecución de la vinculación',
    '6.2' => '6.2 Presencia en la comunidad',
];

// Variables compartidas por varias fórmulas.
const MODELO_CACES_VARS = [
    'NTC' => 'Número de profesores a tiempo completo',
    'NMT' => 'Número de profesores a medio tiempo',
    'NTP' => 'Número de profesores a tiempo parcial',
    'TP' => 'Total de profesores',
    'NAM' => 'Número de asignaturas de la malla',
];

/**
 * numero => [numeración, nombre, tipo, fórmula, estándar, variables]
 * Para los cualitativos la fórmula es null: se califican con la escala cualitativa.
 */
function modeloCacesIndicadores(): array
{
    $v = MODELO_CACES_VARS;

    return [
        1 => ['1.1.1', 'Planificación estratégica y operativa', 'cualitativo', null, null, []],
        2 => ['1.1.2', 'Relaciones interinstitucionales para el desarrollo', 'cualitativo', null, null, []],
        3 => ['1.1.3', 'Aseguramiento interno de la calidad', 'cualitativo', null, null, []],
        4 => ['1.1.4', 'Sistema informático de gestión', 'cualitativo', null, null, []],
        5 => ['1.2.1', 'Igualdad de oportunidades', 'cualitativo', null, null, []],
        6 => ['1.2.2', 'Ética y transparencia', 'cualitativo', null, null, []],
        7 => ['1.2.3', 'Bienestar psicológico', 'cualitativo', null, null, []],
        8 => ['2.1.1', 'Puestos de trabajo de los profesores', 'cuantitativo',
            'PTD / (NTC + 0.5 * NMT)', 1, [
                'PTD' => 'Número de puestos de trabajo para profesores TC y MT',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'],
            ]],
        9 => ['2.1.2', 'Seguridad y salud ocupacional', 'cualitativo', null, null, []],
        10 => ['2.1.3', 'Accesibilidad física y esparcimiento', 'cualitativo', null, null, []],
        11 => ['2.1.4', 'Ancho de banda', 'cuantitativo',
            'KBPS / (0.5 * NA + 0.8 * NTC + 0.4 * NMT + 0.10 * NTP + 0.15 * NEP)', 500, [
                'KBPS' => 'Ancho de banda contratado (kbps)',
                'NA' => 'Número de personal administrativo',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'], 'NTP' => $v['NTP'],
                'NEP' => 'Número de estudiantes presenciales',
            ]],
        12 => ['3.1.1', 'Selección de profesores', 'cualitativo', null, null, []],
        13 => ['3.1.2', 'Formación de posgrado', 'cuantitativo',
            'PFP / TP', 0.5, [
                'PFP' => 'Profesores con formación de posgrado (maestría o especialización)',
                'TP' => $v['TP'],
            ]],
        14 => ['3.1.3', 'Experiencia profesional práctica de profesores TC de contenidos profesionales', 'cuantitativo',
            'NTCEPP / NTCCP', 0.5, [
                'NTCEPP' => 'Profesores TC de contenidos profesionales con experiencia profesional práctica',
                'NTCCP' => 'Total de profesores TC de contenidos profesionales',
            ]],
        15 => ['3.1.4', 'Ejercicio profesional práctico de profesores MT y TP de contenidos profesionales', 'cuantitativo',
            'PMTTPE / (NMT + NTP)', 1, [
                'PMTTPE' => 'Profesores MT y TP de contenidos profesionales con ejercicio profesional',
                'NMT' => $v['NMT'], 'NTP' => $v['NTP'],
            ]],
        16 => ['3.2.1', 'Titularidad de profesores TC y MT', 'cuantitativo',
            'NTCMTT / (NTC + NMT)', 0.3, [
                'NTCMTT' => 'Número de profesores TC y MT titulares',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'],
            ]],
        17 => ['3.2.2', 'Carga horaria semanal de los profesores TC', 'cuantitativo',
            'NHTCMT / (32 * (NTC + 0.5 * NMT))', 18, [
                'NHTCMT' => 'Número de horas de clase de profesores TC y MT en el periodo',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'],
            ]],
        18 => ['3.2.3', 'Evaluación de los profesores', 'cualitativo', null, null, []],
        19 => ['3.2.4', 'Formación académica en curso y capacitación', 'cualitativo', null, null, []],
        20 => ['3.3.1', 'Remuneración promedio mensual TC', 'cuantitativo',
            '(MSE / 12) / (NTC + 0.5 * NMT)', 1100, [
                'MSE' => 'Monto anual de sueldos de profesores TC y MT (USD)',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'],
            ]],
        21 => ['3.3.2', 'Remuneración promedio por hora TP', 'cuantitativo',
            'VTP / NHC', 13, [
                'VTP' => 'Valor total pagado a profesores TP (USD)',
                'NHC' => 'Número de horas de clase dictadas por profesores TP',
            ]],
        22 => ['4.1.1', 'Programas de estudio de las asignaturas', 'cuantitativo',
            'NPEAV / NAM', 1, [
                'NPEAV' => 'Número de programas de estudio de asignaturas válidos',
                'NAM' => $v['NAM'],
            ]],
        23 => ['4.1.2', 'Afinidad formación-docencia', 'cuantitativo',
            'NHCA / NTHC', 1, [
                'NHCA' => 'Número de horas de clase dictadas con afinidad',
                'NTHC' => 'Número total de horas de clase',
            ]],
        24 => ['4.1.3', 'Seguimiento, control y evaluación del proceso docente', 'cualitativo', null, null, []],
        25 => ['4.1.4', 'Asignaturas con cobertura bibliográfica adecuada', 'cuantitativo',
            'NACBA / NAM', 1, [
                'NACBA' => 'Número de asignaturas con cobertura bibliográfica adecuada',
                'NAM' => $v['NAM'],
            ]],
        26 => ['4.1.5', 'Publicaciones docentes', 'cuantitativo',
            '(4 * NLT + 2 * NCLF + 1 * NG) / (NTC + 0.5 * NMT + 0.25 * NTP)', 0.6, [
                'NLT' => 'Número de libros de texto',
                'NCLF' => 'Número de capítulos de libro o folletos',
                'NG' => 'Número de guías',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'], 'NTP' => $v['NTP'],
            ]],
        27 => ['4.1.6', 'Aulas', 'cuantitativo',
            'NAA / NTA', 1, [
                'NAA' => 'Número de aulas adecuadas',
                'NTA' => 'Número total de aulas',
            ]],
        28 => ['4.1.7', 'Formación complementaria', 'cualitativo', null, null, []],
        29 => ['4.1.8', 'Acompañamiento pedagógico a estudiantes', 'cualitativo', null, null, []],
        30 => ['4.1.9', 'Relación con los graduados', 'cualitativo', null, null, []],
        31 => ['4.2.1', 'Entorno virtual de aprendizaje', 'cualitativo', null, null, []],
        32 => ['4.2.2', 'Informatización en el aprendizaje', 'cualitativo', null, null, []],
        33 => ['4.3.1', 'Educación ambiental y desarrollo sostenible', 'cualitativo', null, null, []],
        34 => ['4.3.2', 'Formación en valores y desarrollo de habilidades blandas', 'cualitativo', null, null, []],
        35 => ['4.4.1', 'Formación práctica en el entorno académico', 'cualitativo', null, null, []],
        36 => ['4.4.2', 'Formación práctica en el entorno laboral real', 'cualitativo', null, null, []],
        37 => ['4.5.1', 'Funcionamiento de la biblioteca', 'cualitativo', null, null, []],
        38 => ['4.5.2', 'Acervo de la biblioteca y relación de la biblioteca con las asignaturas y carreras', 'cualitativo', null, null, []],
        39 => ['5.1.1', 'Investigación y desarrollo', 'cualitativo', null, null, []],
        40 => ['5.1.2', 'Publicaciones y eventos científicos y técnicos', 'cuantitativo',
            '(4 * NLCT + 2 * NAC + 1 * NEF) / (NTC + 0.5 * NMT + 0.25 * NTP)', 0.5, [
                'NLCT' => 'Número de libros científicos o técnicos',
                'NAC' => 'Número de artículos científicos',
                'NEF' => 'Número de ponencias en eventos',
                'NTC' => $v['NTC'], 'NMT' => $v['NMT'], 'NTP' => $v['NTP'],
            ]],
        41 => ['5.2.1', 'Innovación y capacidad de absorción', 'cualitativo', null, null, []],
        42 => ['6.1.1', 'Planificación y ejecución de vinculación con la sociedad', 'cualitativo', null, null, []],
        43 => ['6.2.1', 'Presencia de la institución en la comunidad', 'cualitativo', null, null, []],
    ];
}

function modeloCacesCodigo(int $numero): string
{
    return MODELO_CACES_CODIGO_PREFIJO . str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
}
