<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    // Example email sending function (modify as needed)
    $to = "your_email@example.com"; // Replace with your email
    $headers = "From: $email\r\nReply-To: $email";
    $body = "Name: $name\nEmail: $email\nSubject: $subject\nMessage: $message";
    
    if (mail($to, $subject, $body, $headers)) {
        echo "<p>Message sent successfully!</p>";
    } else {
        echo "<p>Failed to send message. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 400px; margin: auto; }
        input, textarea { width: 100%; margin-bottom: 10px; padding: 8px; }
        button { background-color: #28a745; color: white; padding: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Contact Us</h2>
    <form action="contact.php" method="POST">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <input type="text" name="subject" placeholder="Subject" required>
        <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
        <button type="submit">Send Message</button>
    </form>
</body>
</html>
