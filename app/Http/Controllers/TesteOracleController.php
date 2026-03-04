<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TesteOracleController extends Controller
{
    public function index()
    {
        $dados = DB::select("SELECT SYSDATE FROM DUAL");

        return $dados;
    }
}