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


    /*
    =========================
    FUNÇÃO DE FILTROS
    =========================
    */

    private function aplicarFiltros($query, $filtros)
    {

        $query->when($filtros['tipo_item'], function ($q) use ($filtros) {
            $q->whereIn('x.TIPO_ITEM', $filtros['tipo_item']);
        });

        $query->when($filtros['filial'], function ($q) use ($filtros) {
            $q->whereIn('x.FILIAL', $filtros['filial']);
        });

        $query->when($filtros['setor'], function ($q) use ($filtros) {
            $q->whereIn('y.DS_SETOR', $filtros['setor']);
        });

        $query->when($filtros['grupo'], function ($q) use ($filtros) {
            $q->whereIn('x.DESCR_GRUPO_INSUMO', $filtros['grupo']);
        });

        $query->when($filtros['subgrupo'], function ($q) use ($filtros) {
            $q->whereIn('x.DESCR_SUBGRUPO_INSUMO', $filtros['subgrupo']);
        });

        $query->when($filtros['deposito'], function ($q) use ($filtros) {
            $q->whereIn('x.DEPOSITO', $filtros['deposito']);
        });

        if (isset($filtros['item'])) {
            $query->when($filtros['item'], function ($q) use ($filtros) {
                $q->whereIn('x.DESCR_ITEM', $filtros['item']);
            });
        }

        return $query;
    }


    /*
    =========================
    RELATORIO COMPRAS
    =========================
    */

    public function compras(Request $request)
    {

        $filtrosSelecionados = [

            'tipo_item' => array_filter(Arr::wrap($request->tipo_item)),
            'filial' => array_filter(Arr::wrap($request->filial)),
            'setor' => array_filter(Arr::wrap($request->setor)),
            'grupo' => array_filter(Arr::wrap($request->grupo)),
            'subgrupo' => array_filter(Arr::wrap($request->subgrupo)),
            'deposito' => array_filter(Arr::wrap($request->deposito)),

        ];


        /*
        =========================
        BASE QUERY
        =========================
        */

        $base = $this->relatorio->baseQuery();

        $this->aplicarFiltros($base, $filtrosSelecionados);


        /*
        =========================
        GRAFICO
        =========================
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
        =========================
        TOP ITENS
        =========================
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
        =========================
        FILTROS DINAMICOS
        =========================
        */

        $filtros = $this->relatorio->filtros();


        return view('relatorios.compras', array_merge(

            $filtros,

            $filtrosSelecionados,

            compact(
                'grafico',
                'top'
            )

        ));
    }



    /*
    =========================
    RELATORIO ESTOQUE FINAL
    =========================
    */

    public function estoqueFinal(Request $request)
    {

        $filtrosSelecionados = [

            'tipo_item' => array_filter(Arr::wrap($request->tipo_item)),
            'filial' => array_filter(Arr::wrap($request->filial)),
            'setor' => array_filter(Arr::wrap($request->setor)),
            'grupo' => array_filter(Arr::wrap($request->grupo)),
            'subgrupo' => array_filter(Arr::wrap($request->subgrupo)),
            'deposito' => array_filter(Arr::wrap($request->deposito)),
            'item' => array_filter(Arr::wrap($request->item)),

        ];


        /*
        =========================
        BASE QUERY
        =========================
        */

        $base = $this->relatorio->baseQuery();

        $this->aplicarFiltros($base, $filtrosSelecionados);


        /*
        =========================
        GRAFICO
        =========================
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
        =========================
        AGRUPAMENTO
        =========================
        */

        $agrupado = (clone $base)

            ->select(
                'x.DESCR_ITEM',
                'x.ANO_MES',
                DB::raw('SUM(x.VALOR_ATUAL) as TOTAL')
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
        =========================
        FILTROS DINAMICOS
        =========================
        */

        $filtros = $this->relatorio->filtros();


        return view('relatorios.estoque_final', array_merge(

            $filtros,

            $filtrosSelecionados,

            compact(
                'grafico',
                'tabela'
            )

        ));
    }
}