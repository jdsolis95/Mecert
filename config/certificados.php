<?php

return [
    // Validación para el vencimiento de un certificado
    'meses_alerta' => env('CERTIFICADOS_MESES_ALERTA', 3),

    // postarior al vencimiento, se definen días de gracias para el certificado antes de ser eliminado
    'dias_gracia_eliminacion' => env('CERTIFICADOS_DIAS_GRACIA_ELIMINACION', 30),
];
