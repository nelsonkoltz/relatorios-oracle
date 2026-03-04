<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Compras</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h2>Compras por Mês</h2>

<canvas id="graficoCompras" width="900" height="350"></canvas>

<script>
    const labels = [
        @foreach($dadosGrafico as $d)
            "{{ $d->ano_mes }}",
        @endforeach
    ];

    const valores = [
        @foreach($dadosGrafico as $d)
            {{ $d->valor_compra }},
        @endforeach
    ];

    const ctx = document.getElementById('graficoCompras');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Valor de Compras',
                data: valores
            }]
        },
        options: {
            responsive: true
        }
    });
</script>

<h3>Top 10 Itens Comprados</h3>

<table border="1" cellpadding="6">
<tr>
    <th>Item</th>
    <th>Total Compra</th>
</tr>

@foreach($topItens as $i)
<tr>
    <td>{{ $i->descr_item }}</td>
    <td>{{ number_format($i->total_compra, 2, ',', '.') }}</td>
</tr>
@endforeach

</table>

</body>
</html>