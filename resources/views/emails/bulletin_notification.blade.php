<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Votre bulletin de paie</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; overflow:hidden; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="background-color:#1a5c45; padding: 18px 28px;">
                            <span style="color:#ffffff; font-size: 16px; font-weight: bold;">{{ $centre->nom }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 14px 0; font-size: 14px; line-height: 1.6;">
                                Bonjour {{ $personnel->nom_complet ?? ($personnel->prenoms . ' ' . $personnel->nom) }},
                            </p>
                            <p style="margin: 0 0 14px 0; font-size: 14px; line-height: 1.6;">
                                Veuillez trouver ci-joint, au format PDF, votre bulletin de paie pour la période
                                <strong>{{ $payPeriod->label }}</strong>.
                            </p>
                            <p style="margin: 0 0 14px 0; font-size: 14px; line-height: 1.6;">
                                Ce document est strictement personnel et confidentiel. Nous vous invitons à le
                                conserver pour vos archives.
                            </p>
                            <p style="margin: 0 0 4px 0; font-size: 14px; line-height: 1.6;">
                                Pour toute question relative à ce bulletin, n'hésitez pas à vous rapprocher du
                                {{ $drhInfo['titre'] ?? 'service des Ressources Humaines' }} de votre centre.
                            </p>
                            <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6;">
                                Cordialement,<br>
                                <strong>{{ $centre->nom }}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 28px; background-color:#f9fafb; border-top: 1px solid #e5e7eb;">
                            <p style="margin:0; font-size: 11px; color:#6b7280; line-height: 1.5;">
                                Ce message est envoyé automatiquement, merci de ne pas y répondre directement.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>