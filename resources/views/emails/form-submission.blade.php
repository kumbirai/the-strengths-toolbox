<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New {{ $form->name }} Submission</title>
</head>
<body>
    <h2>New Form Submission: {{ $form->name }}</h2>
    
    <p><strong>Submitted:</strong> {{ $submission->created_at->format('F j, Y g:i A') }}</p>
    
    @if($submission->user_id)
        <p><strong>User ID:</strong> {{ $submission->user_id }}</p>
    @endif
    
    @if($submission->ip_address)
        <p><strong>IP Address:</strong> {{ $submission->ip_address }}</p>
    @endif
    
    <h3>Submission Data:</h3>
    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
        @foreach($data as $key => $value)
            <tr>
                <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
