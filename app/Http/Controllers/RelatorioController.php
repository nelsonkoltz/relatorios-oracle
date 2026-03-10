<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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


        $cacheKey = 'compras_' . md5(json_encode($filtrosSelecionados));


        $dados = Cache::remember($cacheKey, 300, function () use ($filtrosSelecionados) {

            $base = $this->relatorio->baseQuery();

            /*
            LIMITE 12 MESES
            */
            $base->whereRaw("x.ANO_MES >= TO_CHAR(ADD_MONTHS(SYSDATE,-12),'YYYYMM')");

            $this->aplicarFiltros($base, $filtrosSelecionados);


            $grafico = (clone $base)

                ->select(
                    'x.ANO_MES',
                    DB::raw('SUM(x.VALOR_COMPRA) as VALOR_COMPRA')
                )

                ->groupBy('x.ANO_MES')
                ->orderBy('x.ANO_MES')
                ->get();


            $top = (clone $base)

                ->select(
                    'x.DESCR_ITEM',
                    DB::raw('SUM(x.VALOR_COMPRA) as TOTAL')
                )

                ->groupBy('x.DESCR_ITEM')
                ->orderByDesc('TOTAL')
                ->get();


            return [
                'grafico' => $grafico,
                'top' => $top
            ];
        });


        /*
        PAGINAÇÃO MANUAL
        */

        $pagina = $request->get('page', 1);

        $porPagina = 10;

        $top = collect($dados['top']);

        $topPaginado = new \Illuminate\Pagination\LengthAwarePaginator(

            $top->forPage($pagina, $porPagina),

            $top->count(),

            $porPagina,

            $pagina,

            [
                'path' => request()->url(),
                'query' => request()->query()
            ]

        );


        $filtros = $this->relatorio->filtros();


        return view('relatorios.compras', array_merge(

            $filtros,
            $filtrosSelecionados,

            [
                'grafico' => $dados['grafico'],
                'top' => $topPaginado
            ]

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


        $cacheKey = 'estoque_' . md5(json_encode($filtrosSelecionados));


        $dados = Cache::remember($cacheKey, 300, function () use ($filtrosSelecionados) {

            $base = $this->relatorio->baseQuery();

            /*
            LIMITE 12 MESES
            */
            $base->whereRaw("x.ANO_MES >= TO_CHAR(ADD_MONTHS(SYSDATE,-12),'YYYYMM')");

            $this->aplicarFiltros($base, $filtrosSelecionados);


            $grafico = (clone $base)

                ->select(
                    'x.ANO_MES',
                    DB::raw('SUM(x.VALOR_ATUAL) as VALOR')
                )

                ->groupBy('x.ANO_MES')
                ->orderBy('x.ANO_MES')
                ->get();


            $agrupado = (clone $base)

                ->select(
                    'x.DESCR_ITEM',
                    'x.ANO_MES',
                    DB::raw('SUM(x.VALOR_ATUAL) as TOTAL')
                )

                ->groupBy(
                    'x.DESCR_ITEM',
                    'x.ANO_MES'
                )
                ->get();


            return [
                'grafico' => $grafico,
                'tabela' => $agrupado
            ];
        });


        /*
        PAGINAÇÃO
        */

        $pagina = $request->get('page', 1);

        $porPagina = 20;

        $tabela = collect($dados['tabela']);

        $tabelaPaginada = new \Illuminate\Pagination\LengthAwarePaginator(

            $tabela->forPage($pagina, $porPagina),

            $tabela->count(),

            $porPagina,

            $pagina,

            [
                'path' => request()->url(),
                'query' => request()->query()
            ]

        );


        $filtros = $this->relatorio->filtros();


        return view('relatorios.estoque_final', array_merge(

            $filtros,
            $filtrosSelecionados,

            [
                'grafico' => $dados['grafico'],
                'tabela' => $tabelaPaginada
            ]

        ));

    }

    public function consumo(Request $request)
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
                DB::raw('SUM(x.VALOR_CONSUMO) as consumo')
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
                DB::raw('SUM(x.VALOR_CONSUMO) as consumo')
            )

            ->groupBy(
                'x.DESCR_ITEM',
                'x.ANO_MES'
            );


        /*
        =========================
        TABELA PAGINADA
        =========================
        */

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


        return view('relatorios.consumo', array_merge(

            $filtros,
            $filtrosSelecionados,

            compact(
                'grafico',
                'tabela'
            )

        ));
    }

    public function fechamento(Request $request)
    {

        $base = $this->relatorio->baseQuery();

        /*
        FILTRO MÊS
        */

        if ($request->ano_mes) {
            $base->where('x.ANO_MES', $request->ano_mes);
        }

        /*
        CONSULTA
        */

        $dados = (clone $base)

            ->select(
                'y.DS_SETOR',

                DB::raw('SUM(x.VALOR_COMPRA) as VALOR_COMPRA'),
                DB::raw('SUM(x.VALOR_ATUAL) as VALOR_ATUAL')

            )

            ->groupBy('y.DS_SETOR')

            ->orderBy('y.DS_SETOR')

            ->get();


        /*
        CAMPOS DA TELA
        */

        $linhas = [

            'VALOR_COMPRA' => 'Compras',
            'VALOR_ATUAL' => 'Estoque Atual',

        ];


        return view('relatorios.fechamento', compact(

            'dados',
            'linhas'

        ));
    }
}