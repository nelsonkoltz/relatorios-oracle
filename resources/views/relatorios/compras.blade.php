<link rel="stylesheet" href="{{ asset('css/compras.css') }}">

<x-menu />

<div class="page">

    <div class="container">

        <form method="GET" class="filtros">

            <h3 class="filtro-titulo">Filtros</h3>


            {{-- =====================
            TIPO ITEM
            ===================== --}}
            <div class="filtro-box">

                <b>Tipo Item</b>

                @foreach($lista_tipo_item as $i)

                    <label>
                        <input type="checkbox" name="tipo_item[]" value="{{ $i }}" {{ in_array($i, $tipo_item ?? []) ? 'checked' : '' }}>
                        {{ $i }}
                    </label>

                @endforeach

            </div>


            {{-- =====================
            FILIAL
            ===================== --}}
            <div class="filtro-box">

                <b>Filial</b>

                @foreach($lista_filial as $f)

                    <label>
                        <input type="checkbox" name="filial[]" value="{{ $f }}" {{ in_array($f, $filial ?? []) ? 'checked' : '' }}>
                        {{ $f }}
                    </label>

                @endforeach

            </div>


            {{-- =====================
            SETOR
            ===================== --}}
            <div class="filtro-box scroll">

                <b>Setor</b>

                @foreach($lista_setor as $s)

                    <label>
                        <input type="checkbox" name="setor[]" value="{{ $s }}" {{ in_array($s, $setor ?? []) ? 'checked' : '' }}>
                        {{ $s }}
                    </label>

                @endforeach

            </div>


            {{-- =====================
            GRUPO
            ===================== --}}
            <div class="filtro-box scroll">

                <b>Grupo</b>

                @foreach($lista_grupo as $g)

                    <label>
                        <input type="checkbox" name="grupo[]" value="{{ $g }}" {{ in_array($g, $grupo ?? []) ? 'checked' : '' }}>
                        {{ $g }}
                    </label>

                @endforeach

            </div>


            {{-- =====================
            SUBGRUPO
            ===================== --}}
            <div class="filtro-box scroll">

                <b>Sub Grupo</b>

                @foreach($lista_subgrupo as $sg)

                    <label>
                        <input type="checkbox" name="subgrupo[]" value="{{ $sg }}" {{ in_array($sg, $subgrupo ?? []) ? 'checked' : '' }}>
                        {{ $sg }}
                    </label>

                @endforeach

            </div>


            {{-- =====================
            DEPOSITO
            ===================== --}}
            <div class="filtro-box scroll">

                <b>Depósito</b>

                @foreach($lista_deposito as $d)

                    <label>
                        <input type="checkbox" name="deposito[]" value="{{ $d }}" {{ in_array($d, $deposito ?? []) ? 'checked' : '' }}>
                        {{ $d }}
                    </label>

                @endforeach

            </div>


            {{-- =====================
            BOTÕES
            ===================== --}}
            <div class="filtro-botoes">

                <button type="submit" class="btn-filtrar">
                    Filtrar
                </button>

                <a href="{{ url()->current() }}" class="btn-limpar">
                    Limpar
                </a>

            </div>

        </form>



        {{-- =====================
        CONTEUDO
        ===================== --}}
        <div class="conteudo">


            {{-- =====================
            GRAFICO
            ===================== --}}
            <div class="grafico-box">

                <h3>Compras últimos 12 meses</h3>

                <div class="grafico-wrapper">
                    <canvas id="graficoCompras"></canvas>
                </div>

            </div>



            {{-- =====================
            TABELA
            ===================== --}}
            <div class="tabela-box">

                <h3>Top itens comprados</h3>

                <table>

                    <thead>

                        <tr>
                            <th>Item</th>
                            <th>Total</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($top as $t)

                            <tr>

                                <td>{{ $t->descr_item }}</td>

                                <td class="valor">
                                    {{ number_format($t->total, 2, ',', '.') }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="2" style="text-align:center">
                                    Nenhum resultado encontrado
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>


                <div class="paginacao">

                    {{ $top->onEachSide(1)->links('pagination::simple-bootstrap-4') }}

                </div>

            </div>


        </div>

    </div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    const labels = @json($grafico->pluck('ano_mes'));

    const valores = @json($grafico->pluck('valor_compra'));

    new Chart(
        document.getElementById('graficoCompras'),
        {

            type: 'bar',

            data: {

                labels: labels,

                datasets: [
                    {
                        label: 'Compras por mês',
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
                    legend: { display: false }
                },

                scales: {
                    y: {
                        beginAtZero: true
                    }
                }

            }

        }
    )

</script>