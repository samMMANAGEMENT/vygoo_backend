<?php

namespace App\Http\Modules\Auth\Services;

use App\Models\User;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Operator\Model\Operator;
use App\Http\Modules\Plan\Model\Plan;
use App\Http\Modules\Entity\Services\EntityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private EntityService $entityService)
    {
    }

    /**
     * Login de usuario
     *
     * @param Request $request
     * @return array
     */
    public function login($data)
    {
        // Buscar usuario
        $user = User::where('email', $data['email'])
            ->first();

        // Validar credenciales
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Crear nuevo token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Obtener permisos usando Spatie
        $permissions = $user->getAllPermissions()->pluck('name');
        $roles = $user->roles->pluck('name');

        // Preparar respuesta
        return [
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'operator' => $user->operator ? [
                    'name' => $user->name,
                    'type_document' => $user->operator->type_document,
                    'document' => $user->operator->document,
                    'mobile' => $user->operator->mobile,
                ] : null,
                'permissions' => $permissions,
                'roles' => $roles,
                'password_changed_at' => $user->password_changed_at,
            ],
        ];
    }

    /**
     * Obtener datos del usuario autenticado
     *
     * @param User $user
     * @return array
     */
    public function me($user): array
    {
        // Cargar relaciones
        $user->load(['operador.cargo', 'afiliado']);

        // Obtener permisos
        $permissions = $user->getAllPermissions()->pluck('name');
        $roles = $user->roles->pluck('name');

        return [
            'id' => $user->id,
            'email' => $user->email,
            'operador' => $user->operador ? [
                'nombre_completo' => $user->operador->nombre_completo,
                'tipo_documento_documento' => $user->operador->tipo_documento_documento,
                'cargo' => $user->operador->cargo,
            ] : null,
            'afiliado' => $user->afiliado ? [
                'nombre_completo' => $user->afiliado->nombre_completo,
            ] : null,
            'permissions' => $permissions,
            'roles' => $roles,
            'password_changed_at' => $user->password_changed_at,
        ];
    }

    /**
     * Logout (cerrar sesión)
     *
     * @param User $user
     * @return bool
     */
    public function logout($user): bool
    {
        // Eliminar el token actual
        return $user->currentAccessToken()->delete();
    }

    /**
     * Cambiar contraseña
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function changePassword($user, $data): bool
    {
        // Verificar contraseña actual
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        // Actualizar contraseña
        return $user->update([
            'password' => Hash::make($data['new_password']),
            'password_changed_at' => now(),
        ]);
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
                'document' => $data['document_number'],
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
