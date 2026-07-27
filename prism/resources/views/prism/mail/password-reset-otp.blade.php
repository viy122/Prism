<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background:#f4f1f1; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f1f1; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="420" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#681012; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:700;">PRISM</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 12px; color:#0f172a; font-size:15px;">You requested to reset your PRISM account password.</p>
                            <p style="margin:0 0 20px; color:#0f172a; font-size:15px;">Use the code below to continue:</p>
                            <div style="text-align:center; margin:24px 0;">
                                <span style="display:inline-block; letter-spacing:6px; font-size:32px; font-weight:800; color:#681012; background:#faf6f6; border:1.5px solid #e8e0e0; border-radius:10px; padding:14px 24px;">
                                    {{ $code }}
                                </span>
                            </div>
                            <p style="margin:0 0 8px; color:#64748b; font-size:13px;">This code expires in {{ $expiresInMinutes }} minutes.</p>
                            <p style="margin:0; color:#64748b; font-size:13px;">If you did not request this, you can safely ignore this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
