@extends('reportes.layout')

@section('contenido')
    @if (count($filas) === 0)
        <div class="sin-datos">Sin datos para los filtros seleccionados.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Etiquetas</th>
                    <th>Tipo multimedia</th>
                    <th>Fecha creación</th>
                    <th>Estado</th>
                    <th>Fecha vencimiento</th>
                    <th>Vigencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        <td>{{ $fila['id'] }}</td>
                        <td>{{ $fila['titulo'] }}</td>
                        <td>{{ $fila['autor'] }}</td>
                        <td>{{ $fila['etiquetas'] }}</td>
                        <td>{{ $fila['multimedia'] }}</td>
                        <td>{{ $fila['fecha_creacion'] }}</td>
                        <td>{{ $fila['estado'] }}</td>
                        <td>{{ $fila['fecha_vencimiento'] }}</td>
                        <td>{{ $fila['vigencia'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totales">
            <strong>Total de mentorías: {{ $totales['total'] }}</strong><br>
            Activas: {{ $totales['activas'] }} &middot;
            Inactivas: {{ $totales['inactivas'] }} &middot;
            Por vencer: {{ $totales['por_vencer'] }} &middot;
            Vencidas: {{ $totales['vencidas'] }}
        </div>
    @endif
@endsection
