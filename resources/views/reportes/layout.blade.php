<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page {
            margin: 95px 25px 55px 25px;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        header {
            position: fixed;
            top: -95px;
            left: 0;
            right: 0;
            height: 85px;
            text-align: center;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 6px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 35px;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 5px;
        }

        .empresa {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .sistema {
            font-size: 11px;
            color: #4b5563;
        }

        .titulo-reporte {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }

        .filtros {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead {
            display: table-header-group;
        }

        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 2px solid #d1d5db;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }

        tr {
            page-break-inside: avoid;
        }

        .totales {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 2px solid #1f2937;
            font-size: 11px;
        }

        .totales strong {
            font-size: 12px;
        }

        .sin-datos {
            text-align: center;
            padding: 60px 0;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('Helvetica', 'normal');
            $size = 9;
            $texto = "Pag. {PAGE_NUM} de {PAGE_COUNT}";
            $ancho = $fontMetrics->get_text_width($texto, $font, $size);
            $pdf->page_text($pdf->get_width() - $ancho - 25, $pdf->get_height() - 30, $texto, $font, $size, [0.42, 0.45, 0.5]);
        }
    </script>

    <header>
        <div class="empresa">Datagrama Comunicaciones S.A.</div>
        <div class="sistema">Sistema MeCert</div>
        <div class="titulo-reporte">{{ $titulo }}</div>
        <div class="filtros">{{ $filtrosTexto }} &middot; Generado el: {{ now()->format('d/m/Y H:i') }}</div>
    </header>

    <footer>
        Generado por: {{ $generadoPor }}
    </footer>

    <main>
        @yield('contenido')
    </main>
</body>
</html>
