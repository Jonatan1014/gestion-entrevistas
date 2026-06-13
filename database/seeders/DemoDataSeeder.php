<?php

namespace Database\Seeders;

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Enums\TestType;
use App\Enums\VacancyApplicantStatus;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestResult;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Ensure roles exist ─────────────────────────────────
        $this->call(RoleSeeder::class);

        // ─── Users ──────────────────────────────────────────────
        $admin = User::where('email', 'admin@sistema-seleccion.test')->first();

        $entrevistador = User::firstOrCreate(
            ['email' => 'entrevistador@sistema-seleccion.test'],
            [
                'name' => 'María Entrevistadora',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $entrevistador->assignRole('Entrevistador');

        $entrevistador2 = User::firstOrCreate(
            ['email' => 'carlos.entrevistador@sistema-seleccion.test'],
            [
                'name' => 'Carlos López',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $entrevistador2->assignRole('Entrevistador');

        // ─── Vacancies ──────────────────────────────────────────
        $vacancyFullStack = Vacancy::create([
            'position' => 'Desarrollador Full Stack Senior',
            'location' => 'Buenos Aires (Híbrido)',
            'requirements' => "• +5 años de experiencia en desarrollo web\n• Dominio de Laravel y Vue.js\n• Experiencia con bases de datos MySQL\n• Conocimientos de testing automatizado\n• Inglés técnico (lectura/escritura)\n• Capacidad de liderazgo técnico",
            'status' => \App\Enums\VacancyStatus::OPEN,
            'min_grade' => 70.00,
            'created_by' => $admin->id,
        ]);

        $vacancyRRHH = Vacancy::create([
            'position' => 'Analista de Recursos Humanos',
            'location' => 'Córdoba (Remoto)',
            'requirements' => "• Graduado en RRHH, Psicología o afines\n• +2 años en selección de personal\n• Manejo de entrevistas por competencias\n• Conocimiento de legislación laboral argentina\n• Excel avanzado",
            'status' => \App\Enums\VacancyStatus::OPEN,
            'min_grade' => 60.00,
            'created_by' => $admin->id,
        ]);

        $vacancyUX = Vacancy::create([
            'position' => 'Diseñador UX/UI Senior',
            'location' => 'Buenos Aires (Presencial)',
            'requirements' => "• +4 años en diseño de producto digital\n• Portfolio demostrable\n• Figma avanzado\n• Investigación con usuarios\n• Design systems",
            'status' => \App\Enums\VacancyStatus::CLOSED,
            'min_grade' => 75.00,
            'created_by' => $admin->id,
        ]);

        // ─── Applicants ─────────────────────────────────────────
        $applicants = [];

        $applicants['lucia'] = Applicant::create([
            'name' => 'Lucía Fernández',
            'phone' => '+5491123456789',
            'email' => 'lucia.fernandez@email.com',
            'address' => 'Palermo, CABA',
            'created_by' => $entrevistador->id,
        ]);

        $applicants['mateo'] = Applicant::create([
            'name' => 'Mateo Rodríguez',
            'phone' => '+5491133344455',
            'email' => 'mateo.rodriguez@email.com',
            'address' => 'Belgrano, CABA',
            'created_by' => $entrevistador->id,
        ]);

        $applicants['valentina'] = Applicant::create([
            'name' => 'Valentina Gómez',
            'phone' => '+5491155566677',
            'email' => 'valentina.gomez@email.com',
            'address' => 'Villa Urquiza, CABA',
            'created_by' => $entrevistador2->id,
        ]);

        $applicants['santiago'] = Applicant::create([
            'name' => 'Santiago Morales',
            'phone' => '+5491177788899',
            'email' => 'santiago.morales@email.com',
            'address' => 'Núñez, CABA',
            'created_by' => $entrevistador->id,
        ]);

        $applicants['camila'] = Applicant::create([
            'name' => 'Camila Torres',
            'phone' => '+5491199900011',
            'email' => 'camila.torres@email.com',
            'address' => 'Recoleta, CABA',
            'created_by' => $admin->id,
        ]);

        $applicants['juan'] = Applicant::create([
            'name' => 'Juan Pablo Díaz',
            'phone' => '+5491122233344',
            'email' => 'juan.diaz@email.com',
            'address' => 'San Telmo, CABA',
            'created_by' => $entrevistador2->id,
        ]);

        $applicants['florencia'] = Applicant::create([
            'name' => 'Florencia Martínez',
            'phone' => '+5491144455566',
            'email' => 'florencia.martinez@email.com',
            'address' => 'Colegiales, CABA',
            'created_by' => $entrevistador->id,
        ]);

        $applicants['martin'] = Applicant::create([
            'name' => 'Martín Álvarez',
            'phone' => '+5491166677788',
            'email' => 'martin.alvarez@email.com',
            'address' => 'Caballito, CABA',
            'is_blocked' => true,
            'block_reason' => 'Antecedentes falsos verificados en proceso anterior',
            'blocked_by' => $admin->id,
            'blocked_at' => now()->subDays(30),
            'created_by' => $admin->id,
        ]);

        // ─── Tests ──────────────────────────────────────────────
        $testProgramacion = Test::create([
            'name' => 'Prueba Técnica de Programación',
            'description' => 'Evaluación de conocimientos en desarrollo web full stack. Incluye ejercicios de Laravel, Vue.js y diseño de APIs REST.',
            'type' => TestType::NUMERIC,
            'max_score' => 100,
            'evaluation_criteria' => 'Se evalúa corrección del código, patrones de diseño utilizados, manejo de errores y testing.',
        ]);

        $testCompetencias = Test::create([
            'name' => 'Evaluación de Competencias Blandas',
            'description' => 'Entrevista por competencias evaluando trabajo en equipo, comunicación, resolución de conflictos y liderazgo.',
            'type' => TestType::TEXT,
            'max_score' => 50,
            'evaluation_criteria' => 'Escala 1-10 por cada competencia. Se requiere justificación escrita para cada puntaje.',
        ]);

        $testLogico = Test::create([
            'name' => 'Test de Razonamiento Lógico',
            'description' => 'Serie de preguntas de opción múltiple para evaluar capacidad de razonamiento lógico-matemático.',
            'type' => TestType::MULTIPLE_CHOICE,
            'max_score' => 30,
            'evaluation_criteria' => '10 preguntas, 3 puntos cada una. Se requiere 60% (18 pts) para aprobar.',
        ]);

        // Multiple choice questions
        $preguntasLogicas = [
            [
                'question_text' => 'Si todos los A son B, y algunos B son C, ¿qué se puede afirmar?',
                'options' => json_encode([
                    'Todos los A son C',
                    'Algunos A pueden ser C',
                    'Ningún A es C',
                    'Todos los C son A',
                ]),
                'correct_answer_indices' => json_encode([1]),
                'points' => 3,
                'order' => 1,
            ],
            [
                'question_text' => 'Complete la secuencia: 2, 6, 12, 20, __',
                'options' => json_encode([
                    '28',
                    '30',
                    '32',
                    '26',
                ]),
                'correct_answer_indices' => json_encode([1]),
                'points' => 3,
                'order' => 2,
            ],
            [
                'question_text' => 'En una oficina hay 5 empleados. Cada uno saluda a los demás con un apretón de manos. ¿Cuántos apretones hubo?',
                'options' => json_encode([
                    '20',
                    '5',
                    '10',
                    '15',
                ]),
                'correct_answer_indices' => json_encode([2]),
                'points' => 3,
                'order' => 3,
            ],
            [
                'question_text' => '¿Qué número falta? 3 → 10, 5 → 26, 7 → __',
                'options' => json_encode([
                    '42',
                    '50',
                    '48',
                    '52',
                ]),
                'correct_answer_indices' => json_encode([1]),
                'points' => 3,
                'order' => 4,
            ],
            [
                'question_text' => 'Un tren sale a 80 km/h. Otro sale 30 min después a 100 km/h. ¿En cuánto tiempo lo alcanza?',
                'options' => json_encode([
                    '1.5 horas',
                    '2 horas',
                    '2.5 horas',
                    '3 horas',
                ]),
                'correct_answer_indices' => json_encode([1]),
                'points' => 3,
                'order' => 5,
            ],
            [
                'question_text' => 'Si P → Q es verdadero y P es falso, entonces:',
                'options' => json_encode([
                    'Q es verdadero',
                    'Q es falso',
                    'No se puede determinar Q',
                    'La implicación es inválida',
                ]),
                'correct_answer_indices' => json_encode([2]),
                'points' => 3,
                'order' => 6,
            ],
            [
                'question_text' => 'En una carrera, adelantás al segundo. ¿En qué posición estás?',
                'options' => json_encode([
                    'Primero',
                    'Segundo',
                    'Tercero',
                    'Último',
                ]),
                'correct_answer_indices' => json_encode([1]),
                'points' => 3,
                'order' => 7,
            ],
            [
                'question_text' => 'Un rectángulo tiene perímetro 30 cm y su largo es el doble del ancho. ¿Cuál es su área?',
                'options' => json_encode([
                    '40 cm²',
                    '45 cm²',
                    '50 cm²',
                    '60 cm²',
                ]),
                'correct_answer_indices' => json_encode([2]),
                'points' => 3,
                'order' => 8,
            ],
            [
                'question_text' => '¿Cuál es el siguiente número? 1, 1, 2, 3, 5, 8, __',
                'options' => json_encode([
                    '11',
                    '12',
                    '13',
                    '14',
                ]),
                'correct_answer_indices' => json_encode([2]),
                'points' => 3,
                'order' => 9,
            ],
            [
                'question_text' => 'Ana tiene el doble de edad que Bruno. Hace 5 años tenía el triple. ¿Qué edad tiene Ana?',
                'options' => json_encode([
                    '15',
                    '20',
                    '25',
                    '30',
                ]),
                'correct_answer_indices' => json_encode([1]),
                'points' => 3,
                'order' => 10,
            ],
        ];

        foreach ($preguntasLogicas as $pregunta) {
            TestQuestion::create([
                'test_id' => $testLogico->id,
                ...$pregunta,
            ]);
        }

        // ─── Attach tests to vacancies ──────────────────────────
        // Full Stack: Programación 60% + Competencias 40%
        $vacancyFullStack->tests()->attach($testProgramacion->id, ['weight' => 60]);
        $vacancyFullStack->tests()->attach($testCompetencias->id, ['weight' => 40]);

        // RRHH: Competencias 50% + Lógico 50%
        $vacancyRRHH->tests()->attach($testCompetencias->id, ['weight' => 50]);
        $vacancyRRHH->tests()->attach($testLogico->id, ['weight' => 50]);

        // UX (closed): Programación 30% + Competencias 70%
        $vacancyUX->tests()->attach($testProgramacion->id, ['weight' => 30]);
        $vacancyUX->tests()->attach($testCompetencias->id, ['weight' => 70]);

        // ─── Associate applicants to vacancies ──────────────────
        // Full Stack applicants
        $vacancyFullStack->applicants()->attach($applicants['lucia']->id, ['status' => VacancyApplicantStatus::APT->value]);
        $vacancyFullStack->applicants()->attach($applicants['mateo']->id, ['status' => VacancyApplicantStatus::EVALUATED->value]);
        $vacancyFullStack->applicants()->attach($applicants['valentina']->id, ['status' => VacancyApplicantStatus::IN_INTERVIEW->value]);
        $vacancyFullStack->applicants()->attach($applicants['santiago']->id, ['status' => VacancyApplicantStatus::REGISTERED->value]);

        // RRHH applicants
        $vacancyRRHH->applicants()->attach($applicants['camila']->id, ['status' => VacancyApplicantStatus::IN_INTERVIEW->value]);
        $vacancyRRHH->applicants()->attach($applicants['juan']->id, ['status' => VacancyApplicantStatus::APT->value]);
        $vacancyRRHH->applicants()->attach($applicants['florencia']->id, ['status' => VacancyApplicantStatus::NO_APT->value]);
        $vacancyRRHH->applicants()->attach($applicants['valentina']->id, ['status' => VacancyApplicantStatus::REGISTERED->value]);

        // UX (closed) applicants
        $vacancyUX->applicants()->attach($applicants['lucia']->id, ['status' => VacancyApplicantStatus::EVALUATED->value]);
        $vacancyUX->applicants()->attach($applicants['santiago']->id, ['status' => VacancyApplicantStatus::NO_APT->value]);

        // ─── Test Results ──────────────────────────────────────
        // Full Stack — Lucía (apt)
        TestResult::create([
            'test_id' => $testProgramacion->id,
            'applicant_id' => $applicants['lucia']->id,
            'vacancy_id' => $vacancyFullStack->id,
            'evaluator_id' => $entrevistador->id,
            'score' => 88.00,
            'observations' => 'Excelente manejo de Laravel. API REST bien diseñada. Código limpio con tests.',
        ]);
        TestResult::create([
            'test_id' => $testCompetencias->id,
            'applicant_id' => $applicants['lucia']->id,
            'vacancy_id' => $vacancyFullStack->id,
            'evaluator_id' => $entrevistador->id,
            'score' => 42.00,
            'observations' => 'Muy buena comunicación. Demuestra liderazgo en situaciones de equipo. Resuelve conflictos con madurez.',
        ]);

        // Full Stack — Mateo (evaluated)
        TestResult::create([
            'test_id' => $testProgramacion->id,
            'applicant_id' => $applicants['mateo']->id,
            'vacancy_id' => $vacancyFullStack->id,
            'evaluator_id' => $entrevistador2->id,
            'score' => 72.00,
            'observations' => 'Buen código pero falta experiencia en testing. La arquitectura de la API es correcta.',
        ]);
        TestResult::create([
            'test_id' => $testCompetencias->id,
            'applicant_id' => $applicants['mateo']->id,
            'vacancy_id' => $vacancyFullStack->id,
            'evaluator_id' => $entrevistador2->id,
            'score' => 35.00,
            'observations' => 'Trabaja bien en equipo pero le cuesta tomar la iniciativa. Comunicación clara.',
        ]);

        // Full Stack — Valentina (in_interview, solo programación rendida)
        TestResult::create([
            'test_id' => $testProgramacion->id,
            'applicant_id' => $applicants['valentina']->id,
            'vacancy_id' => $vacancyFullStack->id,
            'evaluator_id' => $entrevistador->id,
            'score' => 65.00,
            'observations' => 'Código funcional pero con deuda técnica. No aplica patrones de diseño. Sin tests.',
        ]);

        // RRHH — Juan (apt)
        TestResult::create([
            'test_id' => $testCompetencias->id,
            'applicant_id' => $applicants['juan']->id,
            'vacancy_id' => $vacancyRRHH->id,
            'evaluator_id' => $entrevistador2->id,
            'score' => 45.00,
            'observations' => 'Sobresaliente en todas las competencias. Excelente manejo de situaciones complejas.',
        ]);
        TestResult::create([
            'test_id' => $testLogico->id,
            'applicant_id' => $applicants['juan']->id,
            'vacancy_id' => $vacancyRRHH->id,
            'evaluator_id' => $entrevistador2->id,
            'score' => 27.00,
            'observations' => '9/10 correctas. Razonamiento impecable.',
        ]);

        // RRHH — Florencia (no_apt)
        TestResult::create([
            'test_id' => $testCompetencias->id,
            'applicant_id' => $applicants['florencia']->id,
            'vacancy_id' => $vacancyRRHH->id,
            'evaluator_id' => $entrevistador->id,
            'score' => 20.00,
            'observations' => 'Dificultades serias de comunicación. No demuestra empatía en escenarios de conflicto.',
        ]);
        TestResult::create([
            'test_id' => $testLogico->id,
            'applicant_id' => $applicants['florencia']->id,
            'vacancy_id' => $vacancyRRHH->id,
            'evaluator_id' => $entrevistador->id,
            'score' => 15.00,
            'observations' => '5/10 correctas. Por debajo del mínimo requerido.',
        ]);

        // RRHH — Camila (in_interview, solo competencias rendida)
        TestResult::create([
            'test_id' => $testCompetencias->id,
            'applicant_id' => $applicants['camila']->id,
            'vacancy_id' => $vacancyRRHH->id,
            'evaluator_id' => $entrevistador->id,
            'score' => 38.00,
            'observations' => 'Buena predisposición. Buena comunicación pero necesita desarrollo en resolución de conflictos.',
        ]);

        // UX — Lucía (evaluated)
        TestResult::create([
            'test_id' => $testProgramacion->id,
            'applicant_id' => $applicants['lucia']->id,
            'vacancy_id' => $vacancyUX->id,
            'evaluator_id' => $entrevistador2->id,
            'score' => 50.00,
            'observations' => 'Conocimientos básicos de programación. No es su fuerte principal.',
        ]);

        // ─── Interviews ─────────────────────────────────────────
        // Full Stack interviews
        Interview::create([
            'vacancy_id' => $vacancyFullStack->id,
            'applicant_id' => $applicants['lucia']->id,
            'interviewer_id' => $entrevistador->id,
            'scheduled_at' => now()->subDays(7)->setTime(10, 0),
            'type' => InterviewType::VIRTUAL,
            'link' => 'https://meet.google.com/abc-defg-hij',
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'Candidata excepcional. Alineada con la cultura. Recomiendo contratación inmediata.',
            'completed_at' => now()->subDays(7)->setTime(11, 0),
        ]);

        Interview::create([
            'vacancy_id' => $vacancyFullStack->id,
            'applicant_id' => $applicants['mateo']->id,
            'interviewer_id' => $entrevistador2->id,
            'scheduled_at' => now()->subDays(3)->setTime(14, 0),
            'type' => InterviewType::PRESENCIAL,
            'location_notes' => 'Sala de reuniones 3er piso - Torre Norte',
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'Buen perfil técnico pero le falta experiencia en liderazgo. Segunda entrevista recomendada.',
            'completed_at' => now()->subDays(3)->setTime(15, 0),
        ]);

        Interview::create([
            'vacancy_id' => $vacancyFullStack->id,
            'applicant_id' => $applicants['valentina']->id,
            'interviewer_id' => $entrevistador->id,
            'scheduled_at' => now()->addDays(2)->setTime(9, 0),
            'type' => InterviewType::VIRTUAL,
            'link' => 'https://meet.google.com/xyz-uvwy-123',
            'status' => InterviewStatus::PENDING->value,
        ]);

        Interview::create([
            'vacancy_id' => $vacancyFullStack->id,
            'applicant_id' => $applicants['santiago']->id,
            'interviewer_id' => $entrevistador2->id,
            'scheduled_at' => now()->addDays(5)->setTime(11, 0),
            'type' => InterviewType::PRESENCIAL,
            'location_notes' => 'Sala de reuniones 3er piso - Torre Norte',
            'status' => InterviewStatus::PENDING->value,
        ]);

        // RRHH interviews
        Interview::create([
            'vacancy_id' => $vacancyRRHH->id,
            'applicant_id' => $applicants['camila']->id,
            'interviewer_id' => $entrevistador->id,
            'scheduled_at' => now()->subDays(1)->setTime(15, 0),
            'type' => InterviewType::VIRTUAL,
            'link' => 'https://meet.google.com/rrhh-camila-001',
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'Buena candidata para el puesto de RRHH. Conocimiento sólido de legislación laboral.',
            'completed_at' => now()->subDays(1)->setTime(16, 0),
        ]);

        Interview::create([
            'vacancy_id' => $vacancyRRHH->id,
            'applicant_id' => $applicants['juan']->id,
            'interviewer_id' => $entrevistador2->id,
            'scheduled_at' => now()->subDays(5)->setTime(10, 0),
            'type' => InterviewType::PRESENCIAL,
            'location_notes' => 'Oficina de RRHH - Planta Baja',
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'Impecable. Background en psicología organizacional. Contratación recomendada.',
            'completed_at' => now()->subDays(5)->setTime(11, 30),
        ]);

        Interview::create([
            'vacancy_id' => $vacancyRRHH->id,
            'applicant_id' => $applicants['florencia']->id,
            'interviewer_id' => $entrevistador->id,
            'scheduled_at' => now()->subDays(10)->setTime(9, 0),
            'type' => InterviewType::VIRTUAL,
            'link' => 'https://meet.google.com/rrhh-flor-002',
            'status' => InterviewStatus::CANCELLED->value,
            'cancellation_reason' => 'La candidata no se presentó a la entrevista.',
        ]);

        Interview::create([
            'vacancy_id' => $vacancyRRHH->id,
            'applicant_id' => $applicants['valentina']->id,
            'interviewer_id' => $entrevistador->id,
            'scheduled_at' => now()->addDays(3)->setTime(14, 0),
            'type' => InterviewType::VIRTUAL,
            'link' => 'https://meet.google.com/rrhh-vale-003',
            'status' => InterviewStatus::PENDING->value,
        ]);

        // UX interviews (closed vacancy)
        Interview::create([
            'vacancy_id' => $vacancyUX->id,
            'applicant_id' => $applicants['lucia']->id,
            'interviewer_id' => $entrevistador2->id,
            'scheduled_at' => now()->subDays(14)->setTime(16, 0),
            'type' => InterviewType::PRESENCIAL,
            'location_notes' => 'Sala creativa - 5to piso',
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'La candidata priorizó la vacante de Full Stack. No sigue en este proceso.',
            'completed_at' => now()->subDays(14)->setTime(17, 0),
        ]);

        $this->command?->info('✅ Datos de demostración creados exitosamente.');
        $this->command?->info('');
        $this->command?->info('📋 Resumen:');
        $this->command?->info('   - 3 vacantes (2 abiertas, 1 cerrada)');
        $this->command?->info('   - 8 postulantes (1 bloqueado)');
        $this->command?->info('   - 3 tipos de pruebas (numérica, texto, multiple choice)');
        $this->command?->info('   - 12 resultados de pruebas registrados');
        $this->command?->info('   - 9 entrevistas (5 completadas, 1 cancelada, 3 pendientes)');
        $this->command?->info('   - 3 usuarios (1 Admin, 2 Entrevistadores)');
        $this->command?->info('');
        $this->command?->info('🔑 Credenciales:');
        $this->command?->info('   Admin: admin@sistema-seleccion.test / password');
        $this->command?->info('   Entrevistador 1: entrevistador@sistema-seleccion.test / password');
        $this->command?->info('   Entrevistador 2: carlos.entrevistador@sistema-seleccion.test / password');
    }
}
