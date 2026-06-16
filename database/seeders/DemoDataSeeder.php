<?php

namespace Database\Seeders;

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Enums\TestType;
use App\Enums\VacancyApplicantStatus;
use App\Enums\VacancyStatus;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\User;
use App\Models\Vacancy;
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
            ['name' => 'María Entrevistadora', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $entrevistador->assignRole('Entrevistador');

        $entrevistador2 = User::firstOrCreate(
            ['email' => 'carlos.entrevistador@sistema-seleccion.test'],
            ['name' => 'Carlos López', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $entrevistador2->assignRole('Entrevistador');

        // ─── Tests ──────────────────────────────────────────────
        $testDigitacion = Test::create([
            'name' => 'Test de velocidad de digitación',
            'description' => 'Prueba de 1 minuto para medir palabras por minuto.',
            'type' => TestType::NUMERIC,
            'max_score' => 100,
            'evaluation_criteria' => 'https://www.typing.com/es/student/typing-test/1-minute/es',
        ]);

        $testCognitivo = Test::create([
            'name' => 'Evaluación cognitiva',
            'description' => 'Test de funciones cognitivas: memoria, atención y razonamiento.',
            'type' => TestType::NUMERIC,
            'max_score' => 100,
            'evaluation_criteria' => 'https://www.cognifit.com/aplicaciones/html5/public/assessment/ASSESSMENT~@~THE_FLOWERS',
        ]);

        $testPapi = Test::create([
            'name' => 'Test de personalidad laboral (PAPI)',
            'description' => 'Evaluación de rasgos de personalidad en contexto laboral.',
            'type' => TestType::TEXT,
            'max_score' => 100,
            'evaluation_criteria' => 'https://www.personalitieslab.com/es/test-papi',
        ]);

        // ─── Vacancies ──────────────────────────────────────────
        $vacancies = [
            ['position' => 'Asesor de ventas', 'location' => 'Pereira', 'requirements' => "Experiencia en ventas mínimo 1 año\nManejo de CRM\nHabilidades de comunicación\nDisponibilidad horaria"],
            ['position' => 'Técnico', 'location' => 'Cartagena', 'requirements' => "Técnico o tecnólogo en área afín\nExperiencia en mantenimiento\nConocimientos eléctricos básicos\nVehículo propio"],
            ['position' => 'Técnico', 'location' => 'Barranca', 'requirements' => "Técnico en mecánica o afines\nExperiencia en industria\nManejo de herramientas\nDisponibilidad para turnos rotativos"],
            ['position' => 'Comunicador social', 'location' => 'Barranca', 'requirements' => "Profesional en comunicación social\nExperiencia en manejo de redes\nRedacción de contenidos\nCreatividad y proactividad"],
            ['position' => 'Bodega', 'location' => 'Cartagena', 'requirements' => "Experiencia en manejo de inventarios\nConocimiento en logística\nManejo de montacargas (deseable)\nDisponibilidad inmediata"],
            ['position' => 'Bodega', 'location' => 'Barranca', 'requirements' => "Experiencia en almacén o bodega\nControl de entradas y salidas\nOrganización y orden\nTrabajo en equipo"],
            ['position' => 'Asesor de ventas', 'location' => 'Barranca', 'requirements' => "Experiencia en ventas comerciales\nAtención al cliente\nManejo de facturación\nOrientación a resultados"],
            ['position' => 'Técnico', 'location' => 'Valledupar', 'requirements' => "Formación técnica en área industrial\nExperiencia en campo\nManejo de instrumentos de medición\nCarnet de conducir"],
        ];

        $createdVacancies = [];
        foreach ($vacancies as $v) {
            $createdVacancies[] = Vacancy::create([
                'position' => $v['position'],
                'location' => $v['location'],
                'requirements' => $v['requirements'],
                'status' => VacancyStatus::OPEN,
                'min_grade' => 60.00,
                'created_by' => $admin->id,
            ]);
        }

        // Attach all 3 tests to all vacancies
        $allTests = [$testDigitacion, $testCognitivo, $testPapi];
        foreach ($createdVacancies as $v) {
            $v->tests()->attach($testDigitacion->id, ['weight' => 33.34]);
            $v->tests()->attach($testCognitivo->id, ['weight' => 33.33]);
            $v->tests()->attach($testPapi->id, ['weight' => 33.33]);
        }

        // ─── Applicants ─────────────────────────────────────────
        $applicants = [
            ['name' => 'Andrés Gutiérrez', 'phone' => '+573001112233', 'email' => 'andres.gutierrez@email.com', 'address' => 'Pereira', 'created_by' => $entrevistador->id],
            ['name' => 'Diana Martínez', 'phone' => '+573002223344', 'email' => 'diana.martinez@email.com', 'address' => 'Cartagena', 'created_by' => $entrevistador->id],
            ['name' => 'Carlos Ramírez', 'phone' => '+573003334455', 'email' => 'carlos.ramirez@email.com', 'address' => 'Barranca', 'created_by' => $entrevistador2->id],
            ['name' => 'Laura Hernández', 'phone' => '+573004445566', 'email' => 'laura.hernandez@email.com', 'address' => 'Barranca', 'created_by' => $entrevistador->id],
            ['name' => 'Miguel Ángel Torres', 'phone' => '+573005556677', 'email' => 'miguel.torres@email.com', 'address' => 'Cartagena', 'created_by' => $entrevistador2->id],
            ['name' => 'Sofía Rincón', 'phone' => '+573006667788', 'email' => 'sofia.rincon@email.com', 'address' => 'Valledupar', 'created_by' => $entrevistador->id],
            ['name' => 'Jorge Castro', 'phone' => '+573007778899', 'email' => 'jorge.castro@email.com', 'address' => 'Pereira', 'created_by' => $entrevistador2->id],
            ['name' => 'Valentina Ospina', 'phone' => '+573008889900', 'email' => 'valentina.ospina@email.com', 'address' => 'Barranca', 'created_by' => $entrevistador->id],
        ];

        $createdApplicants = [];
        foreach ($applicants as $a) {
            $createdApplicants[] = Applicant::create($a);
        }

        // Associate applicants to vacancies (some random distribution)
        $associations = [
            [0, 0], [0, 6],           // Andrés → Asesor Pereira + Asesor Barranca
            [1, 1], [1, 4],           // Diana → Técnico Cartagena + Bodega Cartagena
            [2, 2],                   // Carlos → Técnico Barranca
            [3, 3], [3, 5], [3, 6],  // Laura → Comunicador + Bodega Barranca + Asesor Barranca
            [4, 1],                   // Miguel → Técnico Cartagena
            [5, 7],                   // Sofía → Técnico Valledupar
            [6, 0],                   // Jorge → Asesor Pereira
            [7, 5],                   // Valentina → Bodega Barranca
        ];

        foreach ($associations as [$ai, $vi]) {
            $createdVacancies[$vi]->applicants()->attach($createdApplicants[$ai]->id, [
                'status' => collect(['registered', 'registered', 'in_interview', 'evaluated'])->random(),
            ]);
        }

        // ─── Test Results ──────────────────────────────────────
        foreach ($createdVacancies as $vacancy) {
            foreach ($vacancy->applicants as $applicant) {
                foreach ($allTests as $test) {
                    // 50% chance of having a result
                    if (rand(0, 1)) {
                        TestResult::create([
                            'test_id' => $test->id,
                            'applicant_id' => $applicant->id,
                            'vacancy_id' => $vacancy->id,
                            'evaluator_id' => collect([$entrevistador->id, $entrevistador2->id])->random(),
                            'score' => rand(50, 95),
                        ]);
                    }
                }
            }
        }

        $this->command?->info('✅ Datos de demostración creados exitosamente.');
        $this->command?->info('');
        $this->command?->info('📋 Resumen:');
        $this->command?->info('   - 8 vacantes colombianas');
        $this->command?->info('   - 3 pruebas (digitación, cognitiva, PAPI)');
        $this->command?->info('   - 8 postulantes');
        $this->command?->info('   - Puntajes de prueba generados aleatoriamente');
        $this->command?->info('   - 3 usuarios (1 Admin, 2 Entrevistadores)');
        $this->command?->info('');
        $this->command?->info('🔑 Credenciales:');
        $this->command?->info('   Admin: admin@sistema-seleccion.test / password');
        $this->command?->info('   Entrevistador 1: entrevistador@sistema-seleccion.test / password');
        $this->command?->info('   Entrevistador 2: carlos.entrevistador@sistema-seleccion.test / password');
    }
}
