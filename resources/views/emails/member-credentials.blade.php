<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your FamLedger login</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #1a1a1a;">You've been added to {{ $family->name }}</h1>

    <p>Hello{{ $memberName ? ' ' . $memberName : '' }},</p>

    <p>You have been added as a member of <strong>{{ $family->name }}</strong> on FamLedger. Use the credentials below to log in.</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #f5f5f5; border-radius: 8px;">
        <tr>
            <td style="padding: 16px;">
                <p style="margin: 0 0 8px 0;"><strong>Login URL:</strong></p>
                <p style="margin: 0 0 12px 0;"><a href="{{ url()->route('login') }}" style="color: #2563eb;">{{ url()->route('login') }}</a></p>
                <p style="margin: 0 0 8px 0;"><strong>Email:</strong> {{ $email }}</p>
                <p style="margin: 0;"><strong>Password:</strong> <code style="background: #e5e7eb; padding: 2px 6px; border-radius: 4px;">{{ $plainPassword }}</code></p>
            </td>
        </tr>
    </table>

    <p style="color: #6b7280; font-size: 14px;">We recommend changing your password after your first login (Profile → Update Password).</p>

    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>
