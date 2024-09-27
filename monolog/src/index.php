<?php
//Load Composer's autoloader
require '../vendor/autoload.php';

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$env = parse_ini_file('../.env');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Form data
    $naam = $_POST['naam'] ?? '';
    $email = $_POST['email'] ?? '';
    $klachten = $_POST['klachten'] ?? '';

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    if (!empty($email) && !empty($klachten)) {
        try {

            //Server settings              //Enable verbose debug output
            $mail->isSMTP();                                      //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                 //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                             //Enable SMTP authentication
            $mail->Username   = $env['SMTP-Username'];          //SMTP username
            $mail->Password   = $env['SMTP-Password'];            //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   //Enable TLS encryption
            $mail->Port       = 587;                              //TCP port to connect to

            //Recipients
            $mail->setFrom('example@gmail.com', 'klachten verwerking');
            $mail->addAddress($email, $naam);                            //Add a recipient

            //Content
            $mail->Subject = 'Klachten verwerking';
            $mail->Body    = "<h1>geachte $naam</h1>,\n\nuw klacht is in behandeling.\n\nuw klacht:\n$klachten";                           //Plain-text email body

            //Send the email
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
                // Logging naar info.log met Monolog
                try {
                    // Maak een nieuwe log aan
                    $log = new Logger('klachten');
                    $log->pushHandler(new StreamHandler(__DIR__ . '/info.log', Logger::INFO));
         
                    // Log de informatie van het formulier
                    $log->info('Nieuwe klacht ontvangen', [
                        'naam' => $naam,
                        'email' => $email,
                        'klacht' => $klachten
                    ]);
                } catch (Exception $e) {
                    echo "<p class='error'>Fout bij het loggen van klacht: {$e->getMessage()}</p>";
                }
    } else {
        echo 'Vul alle velden in'; // Form validation error message
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klachtenformulier</title>
</head>
<body>
    <form action="index.php" method="POST">
        <label for="naam">Naam:</label>
        <br>
        <input type="text" name="naam" required>
        <br><br>
        <label for="email">Email:</label>
        <br>
        <input type="email" name="email" required>
        <br><br>
        <label for="klachten">Klacht:</label>
        <br>
        <textarea name="klachten" required></textarea>
        <br><br>
        <input type="submit" value="Verstuur mail">
    </form>
</body>
</html>
