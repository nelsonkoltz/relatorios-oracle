<link rel="stylesheet" href="{{ asset('css/estoque_final.css') }}">

<x-menu />

<div class="page">

    <div class="container">

        <form method="GET" class="filtros">

            <h3 class="filtro-titulo">Filtros</h3>


            <div class="filtro-box">

                <b>Tipo Item</b>

                @foreach($lista_tipo_item as $i)

                    <label>
                        <input type="checkbox" name="tipo_item[]" value="{{ $i }}" {{ in_array($i, $tipo_item ?? []) ? 'checked' : '' }}>
                        {{ $i }}
                    </label>

                @endforeach

            </div>


            <div class="filtro-box">

                <b>Filial</b>

                @foreach($lista_filial as $f)

                    <label>
                        <input type="checkbox" name="filial[]" value="{{ $f }}" {{ in_array($f, $filial ?? []) ? 'checked' : '' }}>
                        {{ $f }}
                    </label>

                @endforeach

            </div>



            <div class="filtro-box scroll">

                <b>Setor</b>

                @foreach($lista_setor as $s)

                    <label>
                        <input type="checkbox" name="setor[]" value="{{ $s }}" {{ in_array($s, $setor ?? []) ? 'checked' : '' }}>
                        {{ $s }}
                    </label>

                @endforeach

            </div>



            <div class="filtro-box scroll">

                <b>Grupo</b>

                @foreach($lista_grupo as $g)

                    <label>
                        <input type="checkbox" name="grupo[]" value="{{ $g }}" {{ in_array($g, $grupo ?? []) ? 'checked' : '' }}>
                        {{ $g }}
                    </label>

                @endforeach

            </div>



            <div class="filtro-box scroll">

                <b>Sub Grupo</b>

                @foreach($lista_subgrupo as $sg)

                    <label>
                        <input type="checkbox" name="subgrupo[]" value="{{ $sg }}" {{ in_array($sg, $subgrupo ?? []) ? 'checked' : '' }}>
                        {{ $sg }}
                    </label>

                @endforeach

            </div>



            <div class="filtro-box scroll">

                <b>Depósito</b>

                @foreach($lista_deposito as $d)

                    <label>
                        <input type="checkbox" name="deposito[]" value="{{ $d }}" {{ in_array($d, $deposito ?? []) ? 'checked' : '' }}>
                        {{ $d }}
                    </label>

                @endforeach

            </div>



            <div class="filtro-botoes">

                <button type="submit" class="btn-filtrar">
                    Filtrar
                </button>

                <a href="{{ url()->current() }}" class="btn-limpar">
                    Limpar
                </a>

            </div>

        </form>



        <div class="conteudo">


            <div class="grafico-box">

                <h3>Estoque últimos 12 meses</h3>

                <div class="grafico-wrapper">
                    <canvas id="graficoEstoque"></canvas>
                </div>

            </div>



            <div class="tabela-box">

                <h3>Estoque por Item</h3>

                <table>

                    <thead>

                        <tr>
                            <th>Item</th>
                            <th>Mês</th>
                            <th>Total</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($tabela as $t)

                            <tr>

                                <td>{{ $t->descr_item }}</td>

                                <td>{{ $t->ano_mes }}</td>

                                <td class="valor">

                                    {{ number_format($t->total, 0, ',', '.') }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="3" style="text-align:center">
                                    Nenhum resultado encontrado
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>


                <div class="paginacao">

                    {{ $tabela->onEachSide(1)->links('pagination::simple-bootstrap-4') }}

                </div>


            </div>


        </div>

    </div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    const labels = @json($grafico->pluck('ano_mes'));

    const valores = @json($grafico->pluck('valor'));

    new Chart(
        document.getElementById('graficoEstoque'),
        {

            type: 'bar',

            data: {

                labels: labels,

                datasets: [
                    {
                        label: 'Valor em estoque',
                        data: valores,
                        backgroundColor: '#2f5597',
                        borderRadius: 4
                    }
                ]

            },

            options: {

                responsive: true,
                maintainAspectRatio: false,

                plugins: {

                    legend: { display: false },

                    tooltip: {

                        callbacks: {

                            label: function (context) {

                                let value = Math.round(context.raw);

                                return 'Valor em estoque: ' +
                                    value.toLocaleString('pt-BR');

                            }

                        }

                    }
                },

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            callback: function (value) {

                                return value.toLocaleString('pt-BR', { maximumFractionDigits: 0 });

                            }

                        }

                    }

                }

            }

        }
    );

</script>