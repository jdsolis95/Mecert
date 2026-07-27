@extends('reportes.layout')

@section('contenido')
    @if (count($filas) === 0)
        <div class="sin-datos">Sin datos para los filtros seleccionados.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Cédula</th>
                    <th>Fecha/hora ingreso</th>
                    <th>Fecha/hora salida</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        <td>{{ $fila['usuario'] }}</td>
                        <td>{{ $fila['cedula'] }}</td>
                        <td>{{ $fila['fecha_ingreso'] }}</td>
                        <td>{{ $fila['fecha_salida'] }}</td>
                        <td>{{ $fila['ip'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totales">
            <strong>Total de accesos: {{ $totales['total'] }}</strong><br>
            Con sesión cerrada: {{ $totales['cerradas'] }} &middot;
            Con sesión activa: {{ $totales['activas'] }}
        </div>
    @endif
@endsection
