<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Confirme seu cadastro</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; padding: 24px; color: #1f2937;">
    <div style="max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 24px; border: 1px solid #e5e7eb;">
        <h2 style="margin-top: 0; color: #15803d;">MeuBolso</h2>
        <p>Olá, <strong>{{ $nome }}</strong>!</p>
        <p>Para concluir a criação da sua conta e começar a usar o MeuBolso, clique no botão abaixo para confirmar seu e-mail:</p>
        <p style="margin: 24px 0;">
            <a href="{{ $urlConfirmacao }}" style="background-color: #15803d; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                Confirmar meu cadastro
            </a>
        </p>
        <p style="font-size: 13px; color: #6b7280;">Este link expira em 60 minutos. Se você não solicitou este cadastro, pode ignorar este e-mail — nenhuma conta foi criada.</p>
        <p style="font-size: 12px; color: #9ca3af; word-break: break-all;">Se o botão não funcionar, copie e cole este link no navegador:<br>{{ $urlConfirmacao }}</p>
    </div>
</body>
</html>
