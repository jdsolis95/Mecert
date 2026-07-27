@extends('reportes.layout')

@section('contenido')
    @if (count($filas) === 0)
        <div class="sin-datos">Sin datos para los filtros seleccionados.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Colaborador</th>
                    <th>Tipo de certificado</th>
                    <th>Fecha emisión</th>
                    <th>Fecha vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        <td>{{ $fila['cedula'] }}</td>
                        <td>{{ $fila['colaborador'] }}</td>
                        <td>{{ $fila['tipo_certificado'] }}</td>
                        <td>{{ $fila['fecha_emision'] }}</td>
                        <td>{{ $fila['fecha_vencimiento'] }}</td>
                        <td>{{ $fila['estado'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totales">
            <strong>Total de certificados: {{ $totales['total'] }}</strong><br>
            Vigentes: {{ $totales['vigentes'] }} &middot;
            Por vencer: {{ $totales['por_vencer'] }} &middot;
            Vencidos: {{ $totales['vencidos'] }}
        </div>
    @endif
@endsection
