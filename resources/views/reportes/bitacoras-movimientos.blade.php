@extends('reportes.layout')

@section('contenido')
    @if (count($filas) === 0)
        <div class="sin-datos">Sin datos para los filtros seleccionados.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        <td>{{ $fila['fecha'] }}</td>
                        <td>{{ $fila['modulo'] }}</td>
                        <td>{{ $fila['accion'] }}</td>
                        <td>{{ $fila['usuario'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totales">
            <strong>Total de movimientos: {{ $totales['total'] }}</strong><br>
            Creación: {{ $totales['creados'] }} &middot;
            Modificación: {{ $totales['modificados'] }} &middot;
            Eliminación: {{ $totales['eliminados'] }}
        </div>
    @endif
@endsection
