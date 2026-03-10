<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RelatorioService
{

    /*
    =========================
    FILTROS DINÂMICOS
    =========================
    */

    public function filtros()
    {
        return Cache::remember('relatorio_filtros', 3600, function () {

            $base = DB::connection('oracle')
                ->table('VW_PI_CCSTC02_FECH_ITEM as x')
                ->leftJoin('tcli_deposito_setor as d', 'd.cd_deposito', '=', 'x.DEPOSITO')
                ->leftJoin('vw_f7_setor_custo as y', 'y.cd_setor_custo', '=', 'd.cd_setor_custo')
                ->whereRaw("x.ANO_MES >= TO_CHAR(ADD_MONTHS(TRUNC(SYSDATE),-12),'YYYYMM')");

            return [

                'lista_ano_mes' => (clone $base)
                    ->select('x.ANO_MES as ano_mes')
                    ->distinct()
                    ->orderByDesc('ano_mes')
                    ->pluck('ano_mes'),

                'lista_tipo_item' => (clone $base)
                    ->select('x.TIPO_ITEM as tipo_item')
                    ->distinct()
                    ->orderBy('tipo_item')
                    ->pluck('tipo_item'),

                'lista_filial' => (clone $base)
                    ->select('x.FILIAL as filial')
                    ->distinct()
                    ->orderBy('x.FILIAL')
                    ->pluck('filial'),

                'lista_setor' => (clone $base)
                    ->select('y.DS_SETOR as ds_setor')
                    ->whereNotNull('y.DS_SETOR')
                    ->distinct()
                    ->orderBy('y.DS_SETOR')
                    ->pluck('ds_setor'),

                'lista_grupo' => (clone $base)
                    ->select('x.DESCR_GRUPO_INSUMO as descr_grupo_insumo')
                    ->distinct()
                    ->orderBy('x.DESCR_GRUPO_INSUMO')
                    ->pluck('descr_grupo_insumo'),

                'lista_subgrupo' => (clone $base)
                    ->select('x.DESCR_SUBGRUPO_INSUMO as descr_subgrupo_insumo')
                    ->distinct()
                    ->orderBy('x.DESCR_SUBGRUPO_INSUMO')
                    ->pluck('descr_subgrupo_insumo'),

                'lista_deposito' => (clone $base)
                    ->select('x.DEPOSITO as deposito')
                    ->distinct()
                    ->orderBy('x.DEPOSITO')
                    ->pluck('deposito'),

                'lista_item' => (clone $base)
                    ->select('x.DESCR_ITEM as descr_item')
                    ->distinct()
                    ->orderBy('x.DESCR_ITEM')
                    ->pluck('descr_item'),

            ];
        });
    }


    /*
    =========================
    BASE QUERY DOS RELATÓRIOS
    =========================
    */

    public function baseQuery()
    {

        return DB::connection('oracle')
            ->table('VW_PI_CCSTC02_FECH_ITEM as x')

            ->leftJoin('tcli_deposito_setor as d', 'd.cd_deposito', '=', 'x.DEPOSITO')

            ->leftJoin('vw_f7_setor_custo as y', 'y.cd_setor_custo', '=', 'd.cd_setor_custo')

            ->whereRaw("x.ANO_MES >= TO_CHAR(ADD_MONTHS(TRUNC(SYSDATE),-12),'YYYYMM')");
    }
}