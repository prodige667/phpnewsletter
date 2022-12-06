<?php
include 'main.php';
// NOTICE: THE FOLLOWING PAGE IS UNRESTRICTED AND THEREFORE WE SUGGEST YOU IMPLEMENT RESTRICTIONS BEFORE GOING PRODUCTION
// Get all subscribers from the database
$stmt = $pdo->prepare('SELECT * FROM subscribers');
$stmt->execute();
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Process form
if (isset($_POST['recipient'], $_POST['subject'], $_POST['template'])) {
    // From address
    $from = 'Your Company Name <noreply@yourdomain.com>';
	// Email Headers
	$headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'Return-Path: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
	// Determine the subscriber
    $subscriber = null;
    foreach ($subscribers as $s) {
        if ($s['email'] == $_POST['recipient']) {
            $subscriber = $s;
        }
    }
    // Make sure subscriber exists
    if ($subscriber) {
        // Update the unsubscribe link
        $unsubscribe_link = 'https://yourwebsite.com/unsubscribe.php?email=' . $subscriber['email'] . '&code=' . sha1($subscriber['id'] . $subscriber['email']);
        // Replace the placeholder
        $template = str_replace('%unsubscribe_link%', $unsubscribe_link, $_POST['template']);
        // Send email to user
        if (mail($_POST['recipient'], $_POST['subject'], $template, $headers)) {
            exit('success');
        } else {
            exit('Failed to send newsletter! Please check your SMTP mail server!');
        }
    } else {
        exit('Invalid recipient!');
    }
}
?>
<?php
include 'main.php';
// NOTICE: THE FOLLOWING PAGE IS UNRESTRICTED AND THEREFORE WE SUGGEST YOU IMPLEMENT RESTRICTIONS BEFORE GOING PRODUCTION
// Get all subscribers from the database
$stmt = $pdo->prepare('SELECT * FROM subscribers');
$stmt->execute();
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Send Newsletter</title>
        <link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
		<style> 
        html {
            background-color: #F8F9F9;
            background: linear-gradient(0deg, #f9f8f8 0%, #f9f8f8 80%, #cb5f51 80%, #cb5f51 100%);
            padding: 30px;
            height: 100%;
        }
        </style>
	</head>
	<body>

		<form class="send-newsletter-form" method="post" action="">

			<h1><i class="fa-regular fa-envelope"></i>Send Newsletter</h1>

			<div class="fields">

                <label for="recipients">Recipients</label>
                <div class="multi-select-list">
                    <?php foreach ($subscribers as $subscriber): ?>
                    <label>
                        <input type="checkbox" class="recipient" name="recipients[]" value="<?=$subscriber['email']?>"> <?=$subscriber['email']?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <label for="subject">Subject</label>
                <div class="field">
                    <input type="text" id="subject" name="subject" placeholder="Subject" required>
                </div>

                <label for="template">Email Template</label>
                <div class="field">
                    <textarea id="template" name="template" placeholder="Enter your HTML template code here..." required></textarea>
                </div>

                <div class="responses"></div>

			</div>

			<input id="submit" type="submit" value="Send">

		</form>

        <script>
// Retrieve the form element
const newsletterForm = document.querySelector('.send-newsletter-form');
// Declare variables
let recipients = [], totalRecipients = 0, recipientsProcessed = 0;
// Form submit event
newsletterForm.onsubmit = event => {
    event.preventDefault();
    // Retrieve all recipients and delcare as an array
    recipients = [...document.querySelectorAll('.recipient:checked')];
    // Total number of selected recipients
    totalRecipients = recipients.length;
    // Total number of recipients processed
    recipientsProcessed = 0;
    // Clear the responses (if any)
    document.querySelector('.responses').innerHTML = '';
    // Temporarily disable the submit button
    document.querySelector('#submit').disabled = true;
    // Update the button value
    document.querySelector('#submit').value = `(1/${totalRecipients}) Processing...`;
};
// The below code will send a new email every 3 seconds, but only if the form has been processed
setInterval(() => {
    // If there are recipients...
    if (recipients.length > 0) {
        // Create form data
        let formData = new FormData();
        // Append essential data
        formData.append('recipient', recipients[0].value);
        formData.append('template', document.querySelector('#template').value);
        formData.append('subject', document.querySelector('#subject').value);
        // Use AJAX to process the form
        fetch(newsletterForm.action, {
            method: 'POST',
            body: formData
        }).then(response => response.text()).then(data => {
            // If success
            if (data.includes('success')) {
                // Increment variables
                recipientsProcessed++;
                // Update button value
                document.querySelector('#submit').value = `(${recipientsProcessed}/${totalRecipients}) Processing...`;
                // When all recipients have been processed...
                if (recipientsProcessed == totalRecipients) {
                    // Reset everything
                    newsletterForm.reset();
                    document.querySelector('#submit').disabled = false;
                    document.querySelector('#submit').value = `Submit`;
                    document.querySelector('.responses').innerHTML = 'Newsletter sent successfully!';
                }
            } else {
                // Error
                document.querySelector('.responses').innerHTML = data;
            }
        });
        // Remove the first item from array
        recipients.shift();
    }
}, 3000); // 3000 ms = 3 seconds
</script>

	</body>
</html>