<?php

namespace App\Http\Modules\Auth\Services;

use App\Models\User;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Operator\Model\Operator;
use App\Http\Modules\Plan\Model\Plan;
use App\Http\Modules\Entity\Services\EntityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private EntityService $entityService)
    {
    }

    public function login($data)
    {
        return $data;
    }

    public function register($data)
    {
        return DB::transaction(function () use ($data) {

            $entity = Entity::create([
                'name' => "Workspace de " . $data['name'],
                'description' => "main workspace",
                'status' => true,
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'entity_id' => $entity->id,
            ]);

            Operator::create([
                'user_id' => $user->id,
                'type_document' => $data['type_document'] ?? 'CC',
                'document' => $data['document'] ?? 'TEMP_' . time(),
                'mobile' => $data['mobile'] ?? null,
            ]);

            $defaultPlan = Plan::where('is_default', true)->first();

            if ($defaultPlan) {
                $this->entityService->asignarPlan($entity->id, $defaultPlan->id);
            }

            return [
                'user' => $user,
                'entity' => $entity,
                'plan' => $defaultPlan ? $defaultPlan->name : 'No asignado',
                'message' => 'Registro completado con éxito'
            ];
        });
    }
}
