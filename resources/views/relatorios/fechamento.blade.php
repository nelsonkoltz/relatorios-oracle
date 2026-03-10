<link rel="stylesheet" href="{{ asset('css/compras.css') }}">
<link rel="stylesheet" href="{{ asset('css/fechamento.css') }}">

<x-menu />

<div class="page">

    <div class="container">

        <!-- FILTROS -->

        <form method="GET" class="filtros">

            <h3 class="filtro-titulo">Filtros</h3>


            <div class="filtro-box scroll">

                <b>ANO_MES</b>

                @foreach($filtros['lista_ano_mes'] as $ano)

                    <label>
                        <input type="checkbox" name="ano_mes[]" value="{{ $ano }}" {{ in_array($ano, request('ano_mes', [])) ? 'checked' : '' }}>
                        {{ $ano }}
                    </label>

                @endforeach

            </div>


            <div class="filtro-box scroll">

                <b>TIPO_ITEM</b>

                @foreach($filtros['lista_tipo_item'] as $tipo)

                    <label>
                        <input type="checkbox" name="tipo_item[]" value="{{ $tipo }}" {{ in_array($tipo, request('tipo_item', [])) ? 'checked' : '' }}>
                        {{ $tipo }}
                    </label>

                @endforeach

            </div>


            <div class="filtro-box scroll">

                <b>GRUPO_INS</b>

                @foreach($filtros['lista_grupo'] as $grupo)

                    <label>
                        <input type="checkbox" name="grupo[]" value="{{ $grupo }}" {{ in_array($grupo, request('grupo', [])) ? 'checked' : '' }}>
                        {{ $grupo }}
                    </label>

                @endforeach

            </div>


            <div class="filtro-box scroll">

                <b>DEPOSITO</b>

                @foreach($filtros['lista_deposito'] as $dep)

                    <label>
                        <input type="checkbox" name="deposito[]" value="{{ $dep }}" {{ in_array($dep, request('deposito', [])) ? 'checked' : '' }}>
                        {{ $dep }}
                    </label>

                @endforeach

            </div>


            <div class="filtro-botoes">

                <button class="btn-filtrar">
                    Filtrar
                </button>

                <a href="{{ route('relatorios.fechamento') }}" class="btn-limpar">
                    Limpar filtros
                </a>

            </div>

        </form>


        <!-- CONTEUDO -->

        <div class="conteudo">

            <div class="tabela-fechamento-box">

                <table class="tabela-fechamento">

                    <thead>

                        <tr>

                            <th>Valores</th>

                            @foreach($dados as $setor)
                                <th>{{ $setor->ds_setor }}</th>
                            @endforeach

                            <th>Total Geral</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($linhas as $campo => $titulo)

                            <tr>

                                <td class="titulo">

                                    {{ $titulo }}

                                </td>

                                @php
                                    $total = 0;
                                @endphp

                                @foreach($dados as $setor)

                                    @php
                                        $valor = (float) ($setor->$campo ?? 0);
                                        $total += $valor;
                                    @endphp

                                    <td>

                                        {{ number_format($valor, 2, ',', '.') }}

                                    </td>

                                @endforeach

                                <td class="total">

                                    {{ number_format($total, 2, ',', '.') }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>