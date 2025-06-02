<?php

namespace Tests\Feature;

use App\Services\BeneficioService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficioServiceTest extends TestCase
{
    public function test_procesar_beneficios_filtrados_y_agrupados_por_año()
    {
        $beneficios = [
            [
                'id_filtro' => 1,
                'monto' => 45000,
                'fecha' => '2024-05-10',
                'id_programa' => 130
            ],
            [
                'id_filtro' => 2,
                'monto' => 15000,
                'fecha' => '2023-03-21',
                'id_programa' => 147
            ],
            [
                'id_filtro' => 1,
                'monto' => 1000, // fuera de rango, será descartado
                'fecha' => '2024-01-01',
                'id_programa' => 130
            ]
        ];

        $filtros = [
            [
                'id' => 1,
                'min' => 30000,
                'max' => 50000,
                'id_ficha' => 2042
            ],
            [
                'id' => 2,
                'min' => 10000,
                'max' => 20000,
                'id_ficha' => 922
            ]
        ];

        $fichas = [
            [
                'id' => 2042,
                'nombre' => 'Subsidio Familiar (SUF)',
                'id_programa' => 130,
                'url' => 'subsidio_familiar_suf',
                'categoria' => 'bonos',
                'descripcion' => 'Beneficio económico mensual entregado...'
            ],
            [
                'id' => 922,
                'nombre' => 'Emprende',
                'id_programa' => 147,
                'url' => 'emprende',
                'categoria' => 'trabajo',
                'descripcion' => 'Fondos concursables...'
            ]
        ];

        $servicio = new BeneficioService();
        $resultado = $servicio->procesarBeneficiosDesdePost($beneficios, $filtros, $fichas);

        // Validar estructura
        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado); // 2023 y 2024
        $this->assertEquals(2024, $resultado[0]['year']);
        $this->assertEquals(1, $resultado[0]['num']);
        $this->assertEquals(2023, $resultado[1]['year']);
        $this->assertEquals(1, $resultado[1]['num']);
    }
}
