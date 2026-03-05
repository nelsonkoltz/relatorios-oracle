<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class RelatorioController extends Controller
{

    public function compras(Request $request)
    {

        $tipo_item = Arr::wrap($request->tipo_item);
        $filial = Arr::wrap($request->filial);
        $setor = Arr::wrap($request->setor);
        $grupo = Arr::wrap($request->grupo);
        $subgrupo = Arr::wrap($request->subgrupo);
        $deposito = Arr::wrap($request->deposito);

        /*
        =====================
        BASE QUERY
        =====================
        */

        $base = DB::connection('oracle')
            ->table('VW_PI_CCSTC02_FECH_ITEM as x')
            ->leftJoin('tcli_deposito_setor as d', 'd.cd_deposito', '=', 'x.DEPOSITO')
            ->leftJoin('vw_f7_setor_custo as y', 'y.cd_setor_custo', '=', 'd.cd_setor_custo')
            ->whereRaw("x.ANO_MES >= TO_CHAR(ADD_MONTHS(SYSDATE,-12),'YYYYMM')");

        /*
        =====================
        FILTROS
        =====================
        */

        if (!empty($tipo_item)) {
            $base->whereIn('x.TIPO_ITEM', $tipo_item);
        }

        if (!empty($filial)) {
            $base->whereIn('x.FILIAL', $filial);
        }

        if (!empty($setor)) {
            $base->whereIn('y.DS_SETOR', $setor);
        }

        if (!empty($grupo)) {
            $base->whereIn('x.DESCR_GRUPO_INSUMO', $grupo);
        }

        if (!empty($subgrupo)) {
            $base->whereIn('x.DESCR_SUBGRUPO_INSUMO', $subgrupo);
        }

        if (!empty($deposito)) {
            $base->whereIn('x.DEPOSITO', $deposito);
        }

        /*
        =====================
        GRAFICO
        =====================
        */

        $grafico = (clone $base)
            ->select(
                'x.ANO_MES',
                DB::raw('SUM(x.VALOR_COMPRA) VALOR_COMPRA')
            )
            ->groupBy('x.ANO_MES')
            ->orderBy('x.ANO_MES')
            ->get();


        /*
        =====================
        TOP ITENS PAGINADO
        =====================
        */

        $top = (clone $base)
            ->select(
                'x.DESCR_ITEM',
                DB::raw('SUM(x.VALOR_COMPRA) TOTAL')
            )
            ->groupBy('x.DESCR_ITEM')
            ->orderByDesc('TOTAL')
            ->paginate(10)
            ->withQueryString();

        return view('relatorios.compras', compact(
            'grafico',
            'top'
        ));
    }
}