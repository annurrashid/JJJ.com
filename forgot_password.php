<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Path to your PHPMailer

require 'db.php'; // Use your existing DB config



$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT Cust_ID FROM customer WHERE Cust_Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tempPassword = bin2hex(random_bytes(4)); // Temporary raw password
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

        // Update DB
        $stmt = $conn->prepare("UPDATE customer SET Cust_Password = ? WHERE Cust_Email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'anakindatinwalker@gmail.com';
            $mail->Password = 'empxabefjnagpnpw'; // Use Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('anakindatinwalker@gmail.com', 'JJJ.com Password Reset');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'JJJ.com | Temporary Password Reset Instructions';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; font-size: 16px;'>
                    <p>Dear Valued Customer,</p>
                    
                    <p>We have received a request to reset the password associated with your account at <strong>JJJ.com</strong>.</p>
            
                    <p>Your temporary password is:</p>
                    <div style='background-color: #f7a1c4; color: #fff; padding: 10px; width: fit-content; border-radius: 6px; font-size: 18px; font-weight: bold;'>
                        $tempPassword
                    </div>
            
                    <p>Please use this password to log in to your account. For your security, we recommend updating your password immediately after logging in.</p>
            
                    <p>If you did not request a password reset, please disregard this email or contact our support team immediately.</p>
            
                    <p>Thank you,<br>
                    The <strong>JJJ.com</strong> Team</p>
            
                    <hr style='margin-top: 30px;'>
                    <p style='font-size: 12px; color: #999;'>This is an automated message. Please do not reply to this email.</p>
                </div>
            ";
            

            $mail->send();
            $message = "Temporary password sent to your email.";
            header("Location: login.php");
            exit();
        } catch (Exception $e) {
            $message = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "Email not found.";
    }
}
?>





<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <div class="wrapper">
<div class="form-box">
  <form method="POST" class="login-container">
    <header>Reset Password</header>
    <?php if ($message): ?>
      <div class="form-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="input-box">
      <input type="email" name="email" class="input-field" placeholder="Enter your email" required>
      <i class="bx bx-envelope"></i>
    </div>
    <div class="input-box">
      <input type="submit" class="submit" value="Send Temporary Password">
    </div>
    <div style="text-align: center; margin-top: 10px; color: white;">
      <a href="login.php" style="color: white;">Back to Login</a>
    </div>
  </form>
</div>
    </div>
</body>
</html>
