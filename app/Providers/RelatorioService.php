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

        $dados = DB::connection('oracle')

            ->table('VW_PI_CCSTC02_FECH_ITEM as x')

            ->leftJoin('tcli_deposito_setor as d', 'd.cd_deposito', '=', 'x.DEPOSITO')

            ->leftJoin('vw_f7_setor_custo as y', 'y.cd_setor_custo', '=', 'd.cd_setor_custo')

            ->select(
                'x.TIPO_ITEM',
                'x.FILIAL',
                'y.DS_SETOR',
                'x.DESCR_GRUPO_INSUMO',
                'x.DESCR_SUBGRUPO_INSUMO',
                'x.DEPOSITO',
                'x.DESCR_ITEM'
            )

            ->distinct()

            ->get();


        return [

            'lista_tipo_item' => $dados->pluck('tipo_item')->unique()->sort()->values(),

            'lista_filial' => $dados->pluck('filial')->unique()->sort()->values(),

            'lista_setor' => $dados->pluck('ds_setor')->unique()->sort()->values(),

            'lista_grupo' => $dados->pluck('descr_grupo_insumo')->unique()->sort()->values(),

            'lista_subgrupo' => $dados->pluck('descr_subgrupo_insumo')->unique()->sort()->values(),

            'lista_deposito' => $dados->pluck('deposito')->unique()->sort()->values(),

            'lista_item' => $dados->pluck('descr_item')->unique()->sort()->values(),

        ];

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

            ->select(
                'x.ANO_MES',
                'x.TIPO_ITEM',
                'x.FILIAL',
                'x.DESCR_ITEM',
                'x.DESCR_GRUPO_INSUMO',
                'x.DESCR_SUBGRUPO_INSUMO',
                'x.DEPOSITO',
                'x.VALOR_COMPRA',
                'x.VALOR_ATUAL',
                'y.DS_SETOR'
            )

            /*
            LIMITA CONSULTA A 12 MESES
            */

            ->whereRaw("x.ANO_MES >= TO_CHAR(ADD_MONTHS(TRUNC(SYSDATE),-12),'YYYYMM')");

    }

}