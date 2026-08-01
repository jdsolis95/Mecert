    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <h2 style="color: #333; margin-bottom: 20px;">Hola {{ $examen->certificado->colaborador->name }},</h2>

        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
            @if ($examen->estado === 'aprobado')
                El examen de renovación propuesto para tu certificado <strong>{{ $examen->certificado->tipoCertificacion->nombre }}</strong> fue <strong>aprobado</strong>.
            @else
                El examen de renovación propuesto para tu certificado <strong>{{ $examen->certificado->tipoCertificacion->nombre }}</strong> fue <strong>rechazado</strong>.
            @endif
        </p>

        <div style="background-color: #f9f9f9; border-left: 4px solid {{ $examen->estado === 'aprobado' ? '#43a047' : '#e53935' }}; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #333;"><strong>Tipo de certificado:</strong> {{ $examen->certificado->tipoCertificacion->nombre }}</p>
            @if ($examen->estado === 'aprobado')
                <p style="margin: 10px 0 0 0; color: #333;"><strong>Fecha aprobada:</strong> {{ $examen->fecha_aprobada->format('d/m/Y') }}</p>
                @if ($examen->lugar_aprobado)
                    <p style="margin: 10px 0 0 0; color: #333;"><strong>Lugar:</strong> {{ $examen->lugar_aprobado }}</p>
                @endif
            @else
                <p style="margin: 10px 0 0 0; color: #333;"><strong>Fecha propuesta:</strong> {{ $examen->fecha_propuesta->format('d/m/Y') }}</p>
            @endif
            @if ($examen->comentario)
                <p style="margin: 10px 0 0 0; color: #333;"><strong>Comentario:</strong> {{ $examen->comentario }}</p>
            @endif
            <p style="margin: 10px 0 0 0; color: #333;"><strong>Decidido por:</strong> {{ $examen->decididoPor->name }} {{ $examen->decididoPor->primer_apellido }}</p>
        </div>

        <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            Este es un correo automático del módulo de certificados de MeCert.
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 10px;">
            Sistema MeCert - Gestión de Certificados
        </p>
    </div>
</div>
