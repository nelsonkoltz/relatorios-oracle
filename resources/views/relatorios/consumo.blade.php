<h2>Relatório Consumo</h2>

<table border="1">
<tr>
    <th>Setor</th>
    <th>Filial</th>
    <th>Item</th>
    <th>Ano/Mês</th>
</tr>

@foreach($dados as $d)
<tr>
    <td>{{ $d->ds_setor }}</td>
    <td>{{ $d->filial }}</td>
    <td>{{ $d->descr_item }}</td>
    <td>{{ $d->ano_mes }}</td>
</tr>
@endforeach

</table>