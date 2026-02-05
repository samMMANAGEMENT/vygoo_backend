<?php

namespace App\Http\Modules\Entity\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Entity\Services\EntityService;

class EntityController extends Controller
{
    public function __construct(private EntityService $entityService)
    {
    }

    public function obtenerEntidades()
    {
        try {
            $entities = $this->entityService->obtenerEntidades();
            return response()->json($entities, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function crearEntidad(Request $request)
    {
        try {
            $entity = $this->entityService->crearEntidad($request->all());
            return response()->json($entity, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerEntidad($id)
    {
        try {
            $entity = $this->entityService->obtenerEntidad($id);
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function modificarEntidad(Request $request, $id)
    {
        try {
            $entity = $this->entityService->modificarEntidad($request->all(), $id);
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarEntidad($id)
    {
        try {
            $this->entityService->eliminarEntidad($id);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function asignarPlan(Request $request, $id)
    {
        try {
            $entity = $this->entityService->asignarPlan($id, $request->get('plan_id'), $request->all());
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
