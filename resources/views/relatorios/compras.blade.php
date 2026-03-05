<link rel="stylesheet" href="{{ asset('css/compras.css') }}">

<div class="page">

    <h2 class="titulo">Relatório de Compras</h2>

    <div class="container">

        <form method="GET" class="filtros">

            <h3 class="filtro-titulo">Filtros</h3>

            <div class="filtro-box">

                <b>Tipo Item</b>

                <label>
                    <input type="checkbox" name="tipo_item[]" value="5" {{ in_array('5', request()->input('tipo_item', [])) ? 'checked' : '' }}>
                    5
                </label>

                <label>
                    <input type="checkbox" name="tipo_item[]" value="6" {{ in_array('6', request()->input('tipo_item', [])) ? 'checked' : '' }}>
                    6
                </label>

                <label>
                    <input type="checkbox" name="tipo_item[]" value="9" {{ in_array('9', request()->input('tipo_item', [])) ? 'checked' : '' }}>
                    9
                </label>

                <label>
                    <input type="checkbox" name="tipo_item[]" value="10" {{ in_array('10', request()->input('tipo_item', [])) ? 'checked' : '' }}>
                    10
                </label>

            </div>


            <div class="filtro-box">

                <b>Filial</b>

                <label>
                    <input type="checkbox" name="filial[]" value="1" {{ in_array('1', request()->input('filial', [])) ? 'checked' : '' }}>
                    1
                </label>

                <label>
                    <input type="checkbox" name="filial[]" value="2" {{ in_array('2', request()->input('filial', [])) ? 'checked' : '' }}>
                    2
                </label>

            </div>


            <div class="filtro-box scroll">

                <b>Setor</b>

                <label>
                    <input type="checkbox" name="setor[]" value="ADMINISTRACAO" {{ in_array('ADMINISTRACAO', request()->input('setor', [])) ? 'checked' : '' }}>
                    ADMINISTRACAO
                </label>

                <label>
                    <input type="checkbox" name="setor[]" value="FIACAO CN" {{ in_array('FIACAO CN', request()->input('setor', [])) ? 'checked' : '' }}>
                    FIACAO CN
                </label>

                <label>
                    <input type="checkbox" name="setor[]" value="FIACAO NT" {{ in_array('FIACAO NT', request()->input('setor', [])) ? 'checked' : '' }}>
                    FIACAO NT
                </label>

                <label>
                    <input type="checkbox" name="setor[]" value="MALHARIA" {{ in_array('MALHARIA', request()->input('setor', [])) ? 'checked' : '' }}>
                    MALHARIA
                </label>

                <label>
                    <input type="checkbox" name="setor[]" value="TINTURARIA" {{ in_array('TINTURARIA', request()->input('setor', [])) ? 'checked' : '' }}>
                    TINTURARIA
                </label>

            </div>


            <div class="filtro-box scroll">

                <b>Grupo</b>

                <label>
                    <input type="checkbox" name="grupo[]" value="IMOBILIZADO" {{ in_array('IMOBILIZADO', request()->input('grupo', [])) ? 'checked' : '' }}>
                    IMOBILIZADO
                </label>

                <label>
                    <input type="checkbox" name="grupo[]" value="INSUMOS PARA PRODUÇÃO" {{ in_array('INSUMOS PARA PRODUÇÃO', request()->input('grupo', [])) ? 'checked' : '' }}>
                    INSUMOS PARA PRODUÇÃO
                </label>

                <label>
                    <input type="checkbox" name="grupo[]" value="MATERIAL DE MANUTENÇÃO" {{ in_array('MATERIAL DE MANUTENÇÃO', request()->input('grupo', [])) ? 'checked' : '' }}>
                    MATERIAL DE MANUTENÇÃO
                </label>

            </div>


            <div class="filtro-box scroll">

                <b>Sub Grupo</b>

                <label>
                    <input type="checkbox" name="subgrupo[]" value="MANUTENÇÃO DE MÁQUINAS" {{ in_array('MANUTENÇÃO DE MÁQUINAS', request()->input('subgrupo', [])) ? 'checked' : '' }}>
                    MANUTENÇÃO DE MÁQUINAS
                </label>

                <label>
                    <input type="checkbox" name="subgrupo[]" value="MANUTENÇÃO DE PROCESSO" {{ in_array('MANUTENÇÃO DE PROCESSO', request()->input('subgrupo', [])) ? 'checked' : '' }}>
                    MANUTENÇÃO DE PROCESSO
                </label>

                <label>
                    <input type="checkbox" name="subgrupo[]" value="MANUTENÇÃO PREDIAL" {{ in_array('MANUTENÇÃO PREDIAL', request()->input('subgrupo', [])) ? 'checked' : '' }}>
                    MANUTENÇÃO PREDIAL
                </label>

                <label>
                    <input type="checkbox" name="subgrupo[]" value="MANUTENÇÃO VEICULO" {{ in_array('MANUTENÇÃO VEICULO', request()->input('subgrupo', [])) ? 'checked' : '' }}>
                    MANUTENÇÃO VEICULO
                </label>

            </div>


            <div class="filtro-box">

                <b>Depósito</b>

                <label>
                    <input type="checkbox" name="deposito[]" value="142" {{ in_array('142', request()->input('deposito', [])) ? 'checked' : '' }}>
                    142
                </label>

                <label>
                    <input type="checkbox" name="deposito[]" value="143" {{ in_array('143', request()->input('deposito', [])) ? 'checked' : '' }}>
                    143
                </label>

            </div>


            <div class="filtro-botoes">

                <button type="submit" class="btn-filtrar">Filtrar</button>

                <a href="{{ url()->current() }}" class="btn-limpar">Limpar</a>

            </div>

        </form>


        <div class="conteudo">

            <div class="grafico-box">

                <h3>Compras últimos 12 meses</h3>

                <div class="grafico-wrapper">
                    <canvas id="graficoCompras"></canvas>
                </div>

            </div>


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

                        @foreach($top as $t)

                            <tr>
                                <td>{{ $t->descr_item }}</td>
                                <td class="valor">{{ number_format($t->total, 2, ',', '.') }}</td>
                            </tr>

                        @endforeach

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

    const labels = [
        @foreach($grafico as $g)
            "{{ $g->ano_mes }}",
        @endforeach
];

    const valores = [
        @foreach($grafico as $g)
            {{ $g->valor_compra }},
        @endforeach
];

    new Chart(
        document.getElementById('graficoCompras'),
        {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Compras por mês',
                    data: valores,
                    backgroundColor: '#2f5597',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
</script>