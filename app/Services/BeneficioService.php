<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;


class BeneficioService
{
    /**
     * Procesa beneficios recibidos desde una peticion POST.
     * Filtra segun rangos válidos
     *
     * @param array $beneficios
     * @param array $filtros
     * @param array $fichas
     * @return array
     */
    public function procesarBeneficiosDesdePost(array $beneficios, array $filtros, array $fichas): array
    {
        $beneficios = collect($beneficios);
        $filtros = collect($filtros);
        $fichas = collect($fichas);

        // Enriquecer y filtrar beneficios
        $beneficiosFiltrados = $beneficios->map(function ($beneficio) use ($filtros, $fichas) {
            // Validar campos obligatorios
            if (!isset($beneficio['id_filtro'], $beneficio['monto'], $beneficio['fecha'], $beneficio['id_programa'])) {
                return null;
            }

            // Buscar filtro correspondiente
            $filtro = $filtros->firstWhere('id', $beneficio['id_filtro']);
            if (!$filtro || $beneficio['monto'] < $filtro['min'] || $beneficio['monto'] > $filtro['max']) {
                return null; // Filtrado por rango de monto
            }

            // Obtener ficha asociada
            $ficha = $fichas->firstWhere('id', $filtro['id_ficha']);
            $fechaOriginal = $beneficio['fecha'];
            $fechaRecepcion = date('d/m/Y', strtotime($fechaOriginal));
            $ano = date('Y', strtotime($fechaOriginal));

            return [
                'id_programa' => $beneficio['id_programa'],
                'monto' => $beneficio['monto'],
                'fecha_recepcion' => $fechaRecepcion,
                'fecha' => $fechaOriginal,
                'ano' => $ano,
                'view' => true,
                'ficha' => $ficha,
            ];
        })->filter(); // Eliminar nulos

        // Agrupar por año y estructurar respuesta
        return $beneficiosFiltrados
            ->groupBy('ano')
            ->map(function ($grupo, $ano) {
                return [
                    'year' => (int) $ano,
                    'num' => $grupo->count(),
                    'beneficios' => $grupo->values(),
                ];
            })
            ->sortByDesc('year')
            ->values()
            ->toArray();
    }

    /**
     * Obtiene beneficios reales desde endpoints mock externos.
     * @return Collection
     */
    public function obtenerBeneficiosFiltrados(): Collection
    {
        $beneficios = collect(Http::get('https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02')->json());
        $filtros = collect(Http::get('https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687')->json());
        $fichas = collect(Http::get('https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf')->json());

        return collect(
            $this->procesarBeneficiosDesdePost(
                $beneficios->toArray(),
                $filtros->toArray(),
                $fichas->toArray()
            )
        );
    }
}


