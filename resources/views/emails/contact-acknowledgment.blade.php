<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Contacting The Strengths Toolbox</title>
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
        .info-box {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .contact-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .contact-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">The Strengths Toolbox</div>
        </div>
        
        <h1>Thank You, {{ $data['name'] ?? 'Valued Visitor' }}!</h1>
        
        <div class="content">
            <p>We've received your message and truly appreciate you taking the time to reach out to us.</p>
            
            <p>Your inquiry is important to us, and we're committed to providing you with the best possible response. Our team will review your message and get back to you as soon as possible, typically within 24-48 hours during business days.</p>
            
            <div class="info-box">
                <p style="font-weight: 600; margin-bottom: 10px; color: #1f2937;">What happens next?</p>
                <ul style="margin: 0; padding-left: 20px; color: #4b5563;">
                    <li>We'll carefully review your message</li>
                    <li>One of our team members will respond directly to your inquiry</li>
                    <li>We'll provide personalized guidance based on your needs</li>
                </ul>
            </div>
            
            <p>If your matter is urgent or you need immediate assistance, please don't hesitate to contact us directly:</p>
            
            <div class="contact-info">
                <p><strong>Email:</strong> <a href="mailto:welcome@eberhardniklaus.co.za" style="color: #2563eb; text-decoration: none;">welcome@eberhardniklaus.co.za</a></p>
                <p><strong>Phone:</strong> <a href="tel:+27832948033" style="color: #2563eb; text-decoration: none;">+27 83 294 8033</a></p>
            </div>
            
            <p>In the meantime, we invite you to explore our resources and learn more about how we can help transform your team's performance through strengths-based development.</p>
            
            <p>Best regards,<br>
            <strong>The Strengths Toolbox Team</strong></p>
        </div>
        
        <div class="footer">
            <p>You're receiving this email because you submitted a contact form on our website.</p>
            <p>The Strengths Toolbox | Building Strong Teams. Unlocking Strong Profits.</p>
        </div>
    </div>
</body>
</html>
