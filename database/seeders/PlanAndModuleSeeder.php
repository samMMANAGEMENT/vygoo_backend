<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanAndModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Módulos
        $modules = [
            ['name' => 'Dashboard', 'slug' => 'dashboard', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'POS', 'slug' => 'pos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Servicios', 'slug' => 'services', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pagos', 'slug' => 'payments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gastos', 'slug' => 'expenses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agendas', 'slug' => 'schedules', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Integraciones', 'slug' => 'integrations', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reportes', 'slug' => 'reports', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Configuraciones Avanzadas', 'slug' => 'settings_advanced', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Administración', 'slug' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'API Access', 'slug' => 'api_access', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('modules')->insert($modules);

        // 2. Crear Planes
        $plans = [
            [
                'name' => 'Gratis',
                'slug' => 'free',
                'price' => 0.00,
                'duration' => 3650, // 10 años aprox (ilimitado por lógica de negocio)
                'max_users' => 2,
                'is_default' => true,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Go',
                'slug' => 'go',
                'price' => 9.99,
                'duration' => 30,
                'max_users' => 5,
                'is_default' => false,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Essential',
                'slug' => 'essential',
                'price' => 19.99,
                'duration' => 30,
                'max_users' => 0, // Ilimitado
                'is_default' => false,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price' => 99.99,
                'duration' => 30,
                'max_users' => 0, // Ilimitado
                'is_default' => false,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('plans')->insert($plans);

        // 3. Asignar Módulos a Planes (pivot: plan_module)
        $freePlanId = DB::table('plans')->where('slug', 'free')->value('id');
        $goPlanId = DB::table('plans')->where('slug', 'go')->value('id');
        $essentialPlanId = DB::table('plans')->where('slug', 'essential')->value('id');
        $businessPlanId = DB::table('plans')->where('slug', 'business')->value('id');

        $allModules = DB::table('modules')->get();

        foreach ($allModules as $module) {
            // --- PLAN FREE (Módulos base incluidos en todos los planes) ---
            if (in_array($module->slug, ['dashboard', 'pos', 'services', 'payments', 'settings_advanced'])) {
                $this->assignToPlans($module->id, [$freePlanId, $goPlanId, $essentialPlanId, $businessPlanId]);
            }

            // --- PLAN GO (Añade Gastos, Agendas e Integraciones) ---
            if (in_array($module->slug, ['expenses', 'schedules', 'integrations'])) {
                $this->assignToPlans($module->id, [$goPlanId, $essentialPlanId, $businessPlanId]);
            }

            // --- PLAN ESSENTIAL (Añade Reportes y Administración) ---
            if (in_array($module->slug, ['reports', 'admin'])) {
                $this->assignToPlans($module->id, [$essentialPlanId, $businessPlanId]);
            }

            // --- PLAN BUSINESS (Añade API Access / Acceso Total) ---
            if ($module->slug === 'api_access') {
                $this->assignToPlans($module->id, [$businessPlanId]);
            }
        }
    }

    /**
     * Helper para asignar un módulo a varios planes
     */
    private function assignToPlans(int $moduleId, array $planIds): void
    {
        foreach ($planIds as $planId) {
            DB::table('plan_module')->insert([
                'plan_id' => $planId,
                'module_id' => $moduleId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
