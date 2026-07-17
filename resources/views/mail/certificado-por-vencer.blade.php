<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <h2 style="color: #333; margin-bottom: 20px;">Hola {{ $certificado->colaborador->name }},</h2>

        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
            @if ($estado === 'rojo')
                Tu certificado <strong>{{ $certificado->tipo_certificado }}</strong> ya venció. Es necesario gestionar su renovación lo antes posible.
            @else
                Tu certificado <strong>{{ $certificado->tipo_certificado }}</strong> está próximo a vencer. Te recomendamos calendarizar el examen de renovación con tiempo.
            @endif
        </p>

        <div style="background-color: #f9f9f9; border-left: 4px solid {{ $estado === 'rojo' ? '#e53935' : '#fbc02d' }}; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #333;"><strong>Colaborador:</strong> {{ $certificado->colaborador->name }} {{ $certificado->colaborador->primer_apellido }}</p>
            <p style="margin: 10px 0 0 0; color: #333;"><strong>Tipo de certificado:</strong> {{ $certificado->tipo_certificado }}</p>
            <p style="margin: 10px 0 0 0; color: #333;"><strong>Fecha de vencimiento:</strong> {{ $certificado->fecha_vencimiento->format('d/m/Y') }}</p>
        </div>

        <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            Este es un correo automático del módulo de certificados de MeCert.
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 10px;">
            Sistema MeCert - Gestión de Certificados
        </p>
    </div>
</div>
