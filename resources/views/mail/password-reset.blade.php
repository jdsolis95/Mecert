<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <h2 style="color: #333; margin-bottom: 20px;">Hola {{ $usuario->name }},</h2>

        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
            Un administrador ha solicitado el reset de tu contraseña. Tu contraseña temporal es:
        </p>

        <div style="background-color: #f9f9f9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #333;">
                <strong>Contraseña temporal:</strong>
            </p>
            <p style="margin: 10px 0 0 0; font-family: monospace; font-size: 16px; color: #4CAF50; word-break: break-all;">
                <strong>{{ $temporaryPassword }}</strong>
            </p>
        </div>

        <p style="color: #666; line-height: 1.6; margin: 20px 0;">
            Utiliza esta contraseña para iniciar sesión. Al ingresar, serás requerido a cambiar tu contraseña por una nueva.
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            Si no solicitaste este reset de contraseña, por favor contacta con tu administrador de sistemas.
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 10px;">
            Sistema MeCert - Gestión de Certificados
        </p>
    </div>
</div>
