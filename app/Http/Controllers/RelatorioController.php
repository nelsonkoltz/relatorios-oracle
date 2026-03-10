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

        ini_set('memory_limit', '512M');

        /*
        =========================
        CARREGA FILTROS
        =========================
        */

        $filtros = $this->relatorio->filtros();


        /*
        =========================
        BASE QUERY
        =========================
        */

        $base = $this->relatorio->baseQuery();


        /*
        =========================
        APLICA FILTROS
        =========================
        */

        if ($request->ano_mes) {
            $base->whereIn('x.ANO_MES', (array) $request->ano_mes);
        }

        if ($request->tipo_item) {
            $base->whereIn('x.TIPO_ITEM', (array) $request->tipo_item);
        }

        if ($request->grupo) {
            $base->whereIn('x.DESCR_GRUPO_INSUMO', (array) $request->grupo);
        }

        if ($request->deposito) {
            $base->whereIn('x.DEPOSITO', (array) $request->deposito);
        }


        /*
        =========================
        DADOS DO RELATÓRIO
        =========================
        */

        $dados = (clone $base)

            ->select(

                'y.DS_SETOR as ds_setor',

                DB::raw('SUM(x.VALOR_ANTERIOR) valor_anterior'),
                DB::raw('SUM(x.VALOR_COMPRA) valor_compra'),
                DB::raw('SUM(x.VALOR_PRODUCAO) valor_producao'),
                DB::raw('SUM(x.VALOR_ENTRADA_OUTROS) valor_entrada_outros'),
                DB::raw('SUM(x.VALOR_ENTRADA_TRANSFERENCIA) valor_entrada_transferencia'),
                DB::raw('SUM(x.VALOR_SAIDA_TRANSFERENCIA) valor_saida_transferencia'),
                DB::raw('SUM(x.VALOR_SAIDA_OUTROS) valor_saida_outros'),
                DB::raw('SUM(x.VALOR_VENDA) valor_venda'),
                DB::raw('SUM(x.VALOR_CONSUMO) valor_consumo'),
                DB::raw('SUM(x.VALOR_ATUAL) valor_atual')

            )

            ->whereNotNull('y.DS_SETOR')

            ->groupBy('y.DS_SETOR')

            ->orderByRaw("
            CASE
                WHEN y.DS_SETOR = 'ADMINISTRACAO' THEN 1
                WHEN y.DS_SETOR = 'FIACAO NT' THEN 2
                WHEN y.DS_SETOR = 'MALHARIA' THEN 3
                WHEN y.DS_SETOR = 'TINTURARIA' THEN 4
                WHEN y.DS_SETOR = 'FIACAO CN' THEN 5
                ELSE 99
            END
        ")

            ->get();


        /*
        =========================
        LINHAS DA TABELA
        =========================
        */

        $linhas = [

            'valor_anterior' => 'Soma de VALOR_ANTERIOR',
            'valor_compra' => 'Soma de VALOR_COMPRA',
            'valor_producao' => 'Soma de VALOR_PRODUCAO',
            'valor_entrada_outros' => 'Soma de VALOR_ENTRADA_OUTROS',
            'valor_entrada_transferencia' => 'Soma de VALOR_ENTRADA_TRANSFERENCIA',
            'valor_saida_transferencia' => 'Soma de VALOR_SAIDA_TRANSFERENCIA',
            'valor_saida_outros' => 'Soma de VALOR_SAIDA_OUTROS',
            'valor_venda' => 'Soma de VALOR_VENDA',
            'valor_consumo' => 'Soma de VALOR_CONSUMO',
            'valor_atual' => 'Soma de VALOR_ATUAL'

        ];


        /*
        =========================
        ENVIA PARA VIEW
        =========================
        */

        return view(
            'relatorios.fechamento',
            compact('dados', 'linhas', 'filtros')
        );
    }
}