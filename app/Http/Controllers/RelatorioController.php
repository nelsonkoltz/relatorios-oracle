<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class RelatorioController extends Controller
{

    public function compras(Request $request)
    {

        /*
        =====================
        FILTROS
        =====================
        */

        $tipo_item = array_filter(Arr::wrap($request->tipo_item));
        $filial = array_filter(Arr::wrap($request->filial));
        $setor = array_filter(Arr::wrap($request->setor));
        $grupo = array_filter(Arr::wrap($request->grupo));
        $subgrupo = array_filter(Arr::wrap($request->subgrupo));
        $deposito = array_filter(Arr::wrap($request->deposito));


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
        APLICAR FILTROS
        =====================
        */

        $base->when($tipo_item, function ($query) use ($tipo_item) {
            $query->whereIn('x.TIPO_ITEM', $tipo_item);
        });

        $base->when($filial, function ($query) use ($filial) {
            $query->whereIn('x.FILIAL', $filial);
        });

        $base->when($setor, function ($query) use ($setor) {
            $query->whereIn('y.DS_SETOR', $setor);
        });

        $base->when($grupo, function ($query) use ($grupo) {
            $query->whereIn('x.DESCR_GRUPO_INSUMO', $grupo);
        });

        $base->when($subgrupo, function ($query) use ($subgrupo) {
            $query->whereIn('x.DESCR_SUBGRUPO_INSUMO', $subgrupo);
        });

        $base->when($deposito, function ($query) use ($deposito) {
            $query->whereIn('x.DEPOSITO', $deposito);
        });


        /*
        =====================
        GRAFICO (ULTIMOS 12 MESES)
        =====================
        */

        $grafico = (clone $base)
            ->select(
                'x.ANO_MES',
                DB::raw('SUM(x.VALOR_COMPRA) as VALOR_COMPRA')
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
                DB::raw('SUM(x.VALOR_COMPRA) as TOTAL')
            )
            ->groupBy('x.DESCR_ITEM')
            ->orderByDesc('TOTAL')
            ->paginate(10)
            ->withQueryString();


        /*
        =====================
        RETORNO
        =====================
        */

        return view('relatorios.compras', compact(
            'grafico',
            'top',
            'tipo_item',
            'filial',
            'setor',
            'grupo',
            'subgrupo',
            'deposito'
        ));
    }
}