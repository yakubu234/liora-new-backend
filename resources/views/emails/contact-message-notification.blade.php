<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Message</title>
</head>
<body>
    <p>You have a message from {{ $fullName }}.</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Phone:</strong> {{ $phone }}</p>
    <p><strong>Event Type:</strong> {{ $eventType }}</p>
    <p><strong>Preferred Date:</strong> {{ $eventDate }}</p>
    <h3 style="color: #666;">The Message</h3>
    <p>{!! nl2br(e($bodyMessage)) !!}</p>
</body>
</html>
