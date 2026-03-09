<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Providers\RelatorioService;

class RelatorioController extends Controller
{

    protected $relatorio;

    public function __construct(RelatorioService $relatorio)
    {
        $this->relatorio = $relatorio;
    }


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
        BASE QUERY (SERVICE)
        =====================
        */

        $base = $this->relatorio->baseQuery();


        /*
        =====================
        APLICAR FILTROS
        =====================
        */

        $base->when($tipo_item, function ($q) use ($tipo_item) {
            $q->whereIn('x.TIPO_ITEM', $tipo_item);
        });

        $base->when($filial, function ($q) use ($filial) {
            $q->whereIn('x.FILIAL', $filial);
        });

        $base->when($setor, function ($q) use ($setor) {
            $q->whereIn('y.DS_SETOR', $setor);
        });

        $base->when($grupo, function ($q) use ($grupo) {
            $q->whereIn('x.DESCR_GRUPO_INSUMO', $grupo);
        });

        $base->when($subgrupo, function ($q) use ($subgrupo) {
            $q->whereIn('x.DESCR_SUBGRUPO_INSUMO', $subgrupo);
        });

        $base->when($deposito, function ($q) use ($deposito) {
            $q->whereIn('x.DEPOSITO', $deposito);
        });


        /*
        =====================
        GRAFICO
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
        TOP ITENS
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
        FILTROS DINAMICOS
        =====================
        */

        $filtros = $this->relatorio->filtros();


        /*
        =====================
        RETORNO
        =====================
        */

        return view('relatorios.compras', array_merge(

            $filtros,

            compact(
                'grafico',
                'top',
                'tipo_item',
                'filial',
                'setor',
                'grupo',
                'subgrupo',
                'deposito'
            )

        ));

    }



    public function estoqueFinal(Request $request)
    {

        $tipo_item = array_filter(Arr::wrap($request->tipo_item));
        $filial = array_filter(Arr::wrap($request->filial));
        $setor = array_filter(Arr::wrap($request->setor));
        $grupo = array_filter(Arr::wrap($request->grupo));
        $subgrupo = array_filter(Arr::wrap($request->subgrupo));
        $deposito = array_filter(Arr::wrap($request->deposito));
        $item = array_filter(Arr::wrap($request->item));


        /*
        ========================
        BASE QUERY (SERVICE)
        ========================
        */

        $base = $this->relatorio->baseQuery();


        /*
        ========================
        APLICAR FILTROS
        ========================
        */

        $base->when($tipo_item, function ($q) use ($tipo_item) {
            $q->whereIn('x.TIPO_ITEM', $tipo_item);
        });

        $base->when($filial, function ($q) use ($filial) {
            $q->whereIn('x.FILIAL', $filial);
        });

        $base->when($setor, function ($q) use ($setor) {
            $q->whereIn('y.DS_SETOR', $setor);
        });

        $base->when($grupo, function ($q) use ($grupo) {
            $q->whereIn('x.DESCR_GRUPO_INSUMO', $grupo);
        });

        $base->when($subgrupo, function ($q) use ($subgrupo) {
            $q->whereIn('x.DESCR_SUBGRUPO_INSUMO', $subgrupo);
        });

        $base->when($deposito, function ($q) use ($deposito) {
            $q->whereIn('x.DEPOSITO', $deposito);
        });

        $base->when($item, function ($q) use ($item) {
            $q->whereIn('x.DESCR_ITEM', $item);
        });


        /*
        ========================
        GRAFICO
        ========================
        */

        $grafico = (clone $base)

            ->select(
                'x.ANO_MES',
                DB::raw('SUM(x.VALOR_ATUAL) as VALOR')
            )

            ->groupBy('x.ANO_MES')
            ->orderBy('x.ANO_MES')
            ->get();


        /*
        ========================
        SUBQUERY AGRUPADA
        ========================
        */

        $agrupado = (clone $base)

            ->select(
                'x.DESCR_ITEM',
                'x.ANO_MES',
                DB::raw('SUM(x.VALOR_COMPRA) as TOTAL')
            )

            ->groupBy(
                'x.DESCR_ITEM',
                'x.ANO_MES'
            );


        $tabela = DB::connection('oracle')

            ->query()

            ->fromSub($agrupado, 'dados')

            ->orderBy('descr_item')

            ->paginate(20)

            ->withQueryString();


        /*
        ========================
        FILTROS DINAMICOS
        ========================
        */

        $filtros = $this->relatorio->filtros();


        return view('relatorios.estoque_final', array_merge(

            $filtros,

            compact(
                'grafico',
                'tabela',
                'tipo_item',
                'filial',
                'setor',
                'grupo',
                'subgrupo',
                'deposito'
            )

        ));

    }

}