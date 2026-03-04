<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function consumo()
    {

        $dados = DB::connection('oracle')->select("
            SELECT y.ds_setor,
                   x.*
            FROM VW_PI_CCSTC02_FECH_ITEM x
            LEFT JOIN tcli_deposito_setor d 
                   ON d.cd_deposito = x.DEPOSITO
            LEFT JOIN vw_f7_setor_custo y 
                   ON y.cd_setor_custo = d.cd_setor_custo
            WHERE FILIAL IN (1,2)
            AND ANO_MES >= 202310
            FETCH FIRST 100 ROWS ONLY
        ");

        return view('relatorios.consumo', compact('dados'));

    }

    public function compras(Request $request)
    {
        // Filtros (opcionais)
        $filiais = $request->input('filiais', [1, 2]);
        $anoMesMin = $request->input('ano_mes_min', 202310);

        // Query agregada para gráfico (SUM por ANO_MES)
        $sql = "
            SELECT 
                x.ANO_MES,
                SUM(x.VALOR_COMPRA) AS VALOR_COMPRA
            FROM VW_PI_CCSTC02_FECH_ITEM x
            LEFT JOIN tcli_deposito_setor d 
                   ON d.cd_deposito = x.DEPOSITO
            LEFT JOIN vw_f7_setor_custo y 
                   ON y.cd_setor_custo = d.cd_setor_custo
            WHERE x.FILIAL IN (" . implode(',', $filiais) . ")
              AND x.ANO_MES >= :ano_mes_min
            GROUP BY x.ANO_MES
            ORDER BY x.ANO_MES
        ";

        $dadosGrafico = DB::connection('oracle')->select($sql, [
            'ano_mes_min' => $anoMesMin
        ]);

        // Tabela de apoio (ex.: top itens comprados)
        $sqlTop = "
            SELECT 
                x.DESCR_ITEM,
                SUM(x.VALOR_COMPRA) AS TOTAL_COMPRA
            FROM VW_PI_CCSTC02_FECH_ITEM x
            WHERE x.FILIAL IN (" . implode(',', $filiais) . ")
              AND x.ANO_MES >= :ano_mes_min
            GROUP BY x.DESCR_ITEM
            ORDER BY TOTAL_COMPRA DESC
            FETCH FIRST 10 ROWS ONLY
        ";

        $topItens = DB::connection('oracle')->select($sqlTop, [
            'ano_mes_min' => $anoMesMin
        ]);

        return view('relatorios.compras', compact('dadosGrafico', 'topItens', 'filiais', 'anoMesMin'));
    }
}