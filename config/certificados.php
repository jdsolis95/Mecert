<?php

return [
    // Meses de antelación para pasar a amarillo antes del vencimiento.
    'meses_alerta' => env('CERTIFICADOS_MESES_ALERTA', 3),

    // Días de gracia después del vencimiento antes de eliminar el certificado automáticamente.
    'dias_gracia_eliminacion' => env('CERTIFICADOS_DIAS_GRACIA_ELIMINACION', 30),
];
