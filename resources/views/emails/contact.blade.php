<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Form Submission</title>
</head>
<body>
    <h2>New Contact Form Submission</h2>
    
    <p><strong>Name:</strong> {{ $data['name'] ?? 'N/A' }}</p>
    <p><strong>Email:</strong> {{ $data['email'] ?? 'N/A' }}</p>
    
    @if(isset($data['phone']))
        <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
    @endif
    
    @if(isset($data['subject']))
        <p><strong>Subject:</strong> {{ $data['subject'] }}</p>
    @endif
    
    @if(isset($data['message']))
        <h3>Message:</h3>
        <p>{{ $data['message'] }}</p>
    @endif
    
    <p><em>Submitted at: {{ now()->format('F j, Y g:i A') }}</em></p>
</body>
</html>
