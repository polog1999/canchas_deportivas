<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Código de verificación</title>

</head>

<body style="margin:0;padding:0;background:#f3f6f4;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6f4;padding:24px 12px;">

        <tr>

            <td align="center">

                <table width="520" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;border:1px solid #e2e8f0;">

                    <tr>

                        <td style="background:#1b5e3b;padding:16px 22px;color:#ffffff;">

                            {{-- ENCABEZADO --}}

                            <table cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;">

                                <tr>

                                    {{-- LOGO --}}
                                    <td style="padding:0;vertical-align:middle;">

                                        <a href="{{ url('/') }}" style="text-decoration:none;">

                                            <img
                                                src="{{ $message->embed(public_path('logo_municipal_negro2.png')) }}"
                                                alt="Municipalidad de La Molina"
                                                height="60"
                                                style="height:60px;width:auto;border-radius:8px;display:block;border:0;outline:none;text-decoration:none;"
                                            >

                                        </a>

                                    </td>

                                    {{-- BARRA VERTICAL --}}
                                    <td style="padding:0 16px;vertical-align:middle;">

                                        <div
                                            style="width:2px;height:48px;background-color:#ffffff;"
                                        ></div>

                                    </td>

                                    {{-- CANCHAS DEPORTIVAS --}}
                                    <td style="padding:0;vertical-align:middle;">

                                        <a
                                            href="{{ url('/') }}"
                                            style="text-decoration:none;color:#ffffff;"
                                        >

                                            <span
                                                style="font-family:Arial,Helvetica,sans-serif;font-size:18px;line-height:22px;font-weight:700;color:#ffffff;white-space:nowrap;"
                                            >
                                                Canchas Deportivas
                                            </span>

                                        </a>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>

                    <tr>

                        <td style="padding:22px;font-size:14px;line-height:1.7;">

                            <p style="margin:0 0 12px;">

                                Hola{{ $usuario->nombreCompleto() ? ' '.$usuario->nombreCompleto() : '' }},

                            </p>

                            <p style="margin:0 0 16px;">

                                Recibimos una solicitud para cambiar la contraseña de tu cuenta. Usa este código en el portal:

                            </p>

                            <div style="text-align:center;margin:20px 0;">

                                <span style="display:inline-block;font-size:28px;font-weight:bold;letter-spacing:6px;background:#f8fafc;border:2px dashed #1b5e3b;border-radius:12px;padding:14px 24px;color:#1b5e3b;">

                                    {{ $codigo }}

                                </span>

                            </div>

                            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">

                                El código vence en <strong>30 minutos</strong>.

                            </p>

                            <p style="margin:0;color:#64748b;font-size:12px;">

                                Si no solicitaste este cambio, ignora este mensaje. Tu contraseña actual seguirá vigente.

                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>