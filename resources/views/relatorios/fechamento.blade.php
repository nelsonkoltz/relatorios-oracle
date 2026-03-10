<link rel="stylesheet" href="{{ asset('css/fechamento.css') }}">

<x-menu />

<div class="page">

<h2>Fechamento de Estoque</h2>

<table class="tabela">

<thead>

<tr>

<th>Valores</th>

@foreach($dados as $setor)

<th>{{ $setor->DS_SETOR }}</th>

@endforeach

<th>Total</th>

</tr>

</thead>

<tbody>

@foreach($linhas as $campo => $titulo)

<tr>

<td class="titulo">{{ $titulo }}</td>

@php $total = 0; @endphp

@foreach($dados as $setor)

@php

$valor = $setor->$campo ?? 0;

$total += $valor;

@endphp

<td>{{ number_format($valor,2,',','.') }}</td>

@endforeach

<td class="total">

{{ number_format($total,2,',','.') }}

</td>

</tr>

@endforeach

</tbody>

</table>

</div>