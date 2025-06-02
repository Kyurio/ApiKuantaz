<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BeneficioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // <-- Este es el que te faltaba
use OpenApi\Annotations as OA;

class BeneficioController extends Controller
{
    protected $beneficioService;


    /**
     * @OA\Info(
     *     title="API Beneficios Kuantaz",
     *     version="1.0.0",
     *     description="Documentación de la API técnica para el desafío de Kuantaz",
     *     @OA\Contact(
     *         email="jose@correo.com"
     *     )
     * )
     * 
     * @OA\Server(
     *     url=L5_SWAGGER_CONST_HOST,
     *     description="Servidor API local"
     * )
     */

    public function __construct(BeneficioService $beneficioService)
    {
        $this->beneficioService = $beneficioService;
    }

    /**
     * @OA\Get(
     *     path="/api/beneficios-filtrados",
     *     operationId="obtenerBeneficiosFiltrados",
     *     tags={"Beneficios"},
     *     summary="Obtiene beneficios agrupados y filtrados por año",
     *     description="Este endpoint agrupa los beneficios por año, filtrando por monto según los filtros y anidando la ficha correspondiente.",
     *     @OA\Response(
     *         response=200,
     *         description="Listado de beneficios agrupados",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="year", type="integer", example=2024),
     *                     @OA\Property(property="num", type="integer", example=2),
     *                     @OA\Property(
     *                         property="beneficios",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id_programa", type="integer", example=147),
     *                             @OA\Property(property="monto", type="number", example=40656),
     *                             @OA\Property(property="fecha_recepcion", type="string", example="09/11/2024"),
     *                             @OA\Property(property="fecha", type="string", example="2024-11-09"),
     *                             @OA\Property(property="ano", type="string", example="2024"),
     *                             @OA\Property(property="view", type="boolean", example=true),
     *                             @OA\Property(
     *                                 property="ficha",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=922),
     *                                 @OA\Property(property="nombre", type="string", example="Emprende"),
     *                                 @OA\Property(property="id_programa", type="integer", example=147),
     *                                 @OA\Property(property="url", type="string", example="emprende"),
     *                                 @OA\Property(property="categoria", type="string", example="trabajo"),
     *                                 @OA\Property(property="descripcion", type="string", example="Fondos concursables para nuevos negocios")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function obtenerBeneficiosFiltrados(): JsonResponse
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $this->beneficioService->obtenerBeneficiosFiltrados()
        ]);
    }


    public function testBeneficios(Request $request)
    {
        try {
            $data = $request->only(['beneficios', 'filtros', 'fichas']);

            if (
                !is_array($data['beneficios'] ?? null) ||
                !is_array($data['filtros'] ?? null) ||
                !is_array($data['fichas'] ?? null)
            ) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'message' => 'Petición no válida. Revisa los datos enviados.'
                ], 400);
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $this->beneficioService->procesarBeneficiosDesdePost(
                    $data['beneficios'],
                    $data['filtros'],
                    $data['fichas']
                )
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

}
