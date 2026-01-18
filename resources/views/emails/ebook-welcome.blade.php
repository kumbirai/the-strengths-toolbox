<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome! Your Free eBook is Ready</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        h1 {
            color: #1f2937;
            font-size: 28px;
            margin: 0 0 20px 0;
        }
        .content {
            margin-bottom: 30px;
        }
        p {
            margin: 0 0 15px 0;
            color: #4b5563;
        }
        .download-button {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .download-button:hover {
            background-color: #059669;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .benefits {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .benefits ul {
            margin: 0;
            padding-left: 20px;
        }
        .benefits li {
            margin: 8px 0;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">The Strengths Toolbox</div>
        </div>
        
        <h1>Welcome, {{ $subscriber->first_name }}!</h1>
        
        <div class="content">
            <p>Thank you for signing up to receive our free eBook on strengths-based development!</p>
            
            <p>We're excited to share this comprehensive guide with you. It's packed with practical strategies and insights that will help you transform your team and drive sustainable business growth.</p>
            
            <div class="benefits">
                <p style="font-weight: 600; margin-bottom: 10px;">Your eBook includes:</p>
                <ul>
                    <li>Understanding strengths-based development</li>
                    <li>Practical strategies for team building</li>
                    <li>Tips for improving team performance</li>
                    <li>Real-world case studies and examples</li>
                </ul>
            </div>
            
            <div class="button-container">
                <a href="{{ $downloadUrl }}" class="download-button">Download Your Free eBook</a>
            </div>
            
            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #2563eb;">{{ $downloadUrl }}</p>
            
            <p>We hope you find this resource valuable. If you have any questions or would like to learn more about our programs, feel free to reach out to us.</p>
            
            <p>Best regards,<br>
            <strong>The Strengths Toolbox Team</strong></p>
        </div>
        
        <div class="footer">
            <p>You're receiving this email because you signed up for our free eBook.</p>
            <p>The Strengths Toolbox | Building Strong Teams. Unlocking Strong Profits.</p>
            <!-- Unsubscribe link can be added here in the future -->
        </div>
    </div>
</body>
</html>
