<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Invitación a campaña - SmartElect</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F3F3F3" style="margin:0; padding:0;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; max-width:600px; background-color:#FFFFFF; border:1px solid #DDDDDD;">
                    <tr>
                        <td align="center" style="padding:25px 20px; border-bottom:3px solid #f34e64; font-family:Arial, Helvetica, sans-serif;">
                            <a href="https://smarselect.example.com" style="text-decoration:none; font-size:24px; font-weight:bold; color:#111111;">
                                Smart<span style="color:#f34e64;">E</span>lect
                            </a>
                            <div style="font-size:13px; color:#555555; margin-top:6px;">
                                Plataforma para campañas políticas inteligentes
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:25px 20px; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#222222; line-height:1.5;">
                            <div style="font-size:18px; font-weight:bold; margin-bottom:15px;">
                                ¡Hola {{ $name }}!
                            </div>

                            <p style="margin:0 0 15px 0;">
                                <strong style="color:#f34e64;">{{ $campaign->candidate_name }}</strong> ha creado una campaña en <strong>SmartElect</strong> y te ha invitado a formar parte de su equipo.
                            </p>
                            <table width="100%" cellpadding="10" cellspacing="0" border="0"
                                style="margin:18px 0; background-color:#FAFAFA; border:1px solid #E0E0E0;">
                                <tr>
                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#222222;">
                                        <strong>Campaña:</strong> {{ $campaign->name }}<br>
                                        <strong>Candidato:</strong> {{ $campaign->candidate_name }}<br>
                                        <strong>Cargo:</strong> {{ $campaign->position }}<br>
                                        <strong>Distrito / Región:</strong> [Nombre del Distrito o Región]<br>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 10px 0;">
                                Al unirte al equipo tendrás acceso a herramientas para:
                            </p>
                            <ul style="margin:0 0 20px 20px; padding:0;">
                                <li>Coordinarte con otros voluntarios y equipos de trabajo</li>
                                <li>Acceder a materiales y recursos de campaña</li>
                                <li>Participar en actividades y eventos organizados</li>
                                <li>Seguir el progreso de la campaña en tiempo real</li>
                            </ul>
                            <table width="100%" cellpadding="10" cellspacing="0" border="0"
                                style="margin:20px 0; background-color:#FFF4F6; border-left:4px solid #f34e64;">
                                <tr>
                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#333333;">
                                        <strong>Tu participación puede marcar la diferencia.</strong><br>
                                        El equipo de campaña confía en tu apoyo para lograr el mejor resultado posible.
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 15px 0;">
                                Para aceptar esta invitación y unirte oficialmente a la campaña, haz clic en el siguiente botón:
                            </p>
                            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:20px 0 25px 0; width:100%">
                                <tr>
                                    <td align="center" bgcolor="#f34e64" style="padding:12px 25px; text-align:center;">
                                        <a href="{{ route('campaign.accept-invitation', $token) }}" target="_blank"
                                        style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:bold; color:#FFFFFF; text-decoration:none; display:block; text-align:center;">
                                            ACEPTAR INVITACIÓN Y UNIRME A LA CAMPAÑA
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 18px 0;">
                                <strong>SmartElect</strong> es la plataforma que ayuda a candidatos, equipos y voluntarios a organizar campañas políticas de forma clara, ordenada y eficiente.
                            </p>
                            <p style="margin:0 0 5px 0; font-style:italic;">
                                ¡Esperamos contar contigo en esta misión!
                            </p>
                            <p style="margin:0; font-style:italic;">
                                Atentamente,<br>
                                El equipo de campaña de <strong>{{ $campaign->candidate_name }}</strong><br>
                                SmartElect
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:15px 20px; background-color:#FAFAFA; border-top:1px solid #E0E0E0; font-family:Arial, Helvetica, sans-serif; font-size:11px; color:#777777;">
                            <div style="margin-bottom:5px;">
                                © 2025 SmartElect. Todos los derechos reservados.
                            </div>
                            <div>
                                Este mensaje fue enviado porque fuiste invitado a una campaña en SmartElect.
                                Si no deseas recibir más comunicaciones, haz clic
                                <a href="https://smarselect.example.com/unsubscribe" style="color:#777777; text-decoration:underline;">aquí</a>.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</html>

