<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

}