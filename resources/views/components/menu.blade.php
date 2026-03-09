<link rel="stylesheet" href="{{ asset('css/menu.css') }}">

<div class="page">

    <div class="menu-container">

        <div class="menu-abas">

            <a href="{{ url('/consulta-sgt') }}" class="aba {{ request()->is('consulta-sgt') ? 'ativa' : '' }}">
                Consulta SGT
            </a>

            <a href="{{ url('/compras') }}" class="aba {{ request()->is('compras') ? 'ativa' : '' }}">
                Compras
            </a>

            <a href="{{ url('/estoque-final') }}" class="aba {{ request()->is('estoque-final') ? 'ativa' : '' }}">
                Posição Estoque Final
            </a>

            <a href="{{ url('/consumo') }}" class="aba {{ request()->is('consumo') ? 'ativa' : '' }}">
                Consumo
            </a>

            <a href="{{ url('/fechamento') }}" class="aba {{ request()->is('fechamento') ? 'ativa' : '' }}">
                Fechamento
            </a>

            <a href="{{ url('/consumo-item') }}" class="aba {{ request()->is('consumo-item') ? 'ativa' : '' }}">
                Consumo por Item
            </a>

            <a href="{{ url('/custo-unitario') }}" class="aba {{ request()->is('custo-unitario') ? 'ativa' : '' }}">
                Custo Unitário
            </a>

        </div>

    </div>

</div>