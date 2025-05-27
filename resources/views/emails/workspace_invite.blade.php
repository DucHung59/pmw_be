<!DOCTYPE html>
<html>
<head>
    <style>
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .email-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            color: white;
            background-color:rgb(36, 179, 172);
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <h2>You've been invited to workspace!</h2>
            <p>Click the button below to accept the invitation:</p>
            
            <div style="margin: 20px 0;">
                <a href="{{ $inviteUrl }}" class="btn">Accept Invitation</a>
            </div>

            <div class="footer">
                Thanks,<br>
                PMW
            </div>
        </div>
    </div>
</body>
</html>
