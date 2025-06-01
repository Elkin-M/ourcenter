<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']); // Clear the error after retrieving it
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Login -OUR CENTER</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon básico -->
    <link rel="icon" href="./images/favicon/favicon.ico" sizes="any">

    <!-- Favicons para iOS/Safari -->
    <link rel="apple-touch-icon" sizes="180x180" href="./images/favicon/apple-touch-icon.png">

    <!-- Favicons para dispositivos Android -->
    <link rel="icon" type="image/png" sizes="192x192" href="./images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="./images/favicon/android-chrome-512x512.png">

    <!-- Favicons standard -->
    <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon/favicon-16x16.png">

    <!-- Manifiesto para PWA (Progressive Web App) -->
    <link rel="manifest" href="./images/favicon/site.webmanifest">

    <!-- Meta tag para controlar el color de la barra de dirección en móviles -->
    <meta name="theme-color" content="#0a1b5c">

    <!-- Para Microsoft Edge y IE -->
    <meta name="msapplication-TileColor" content="#0a1b5c">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Incluir estilos para el preloader -->
<link rel="stylesheet" type="text/css" href="css/preloader.css">

<!-- Estilo para los iconos sociales -->
<style>
    .social-login {
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    
    .social-icon-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 3px 5px rgba(0,0,0,0.1);
    }
    
    .social-icon-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.2);
    }
    
    .facebook {
        background: #3b5998;
    }
    
    .google {
        background: #db4437;
    }
    
    .twitter {
        background: #1da1f2;
    }
    
    .instagram {
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    }
    
    .create-account-link {
        display: inline-block;
        color: #666;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.3s;
        margin-top: 15px;
    }
    
    .create-account-link:hover {
        color: #333;
    }
    
    .create-account-link i {
        margin-left: 5px;
        transition: transform 0.3s;
    }
    
    .create-account-link:hover i {
        transform: translateX(5px);
    }
    
    /* Estilos para la alerta flotante */
    .floating-alert {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 300px;
        max-width: 80%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border-left: 5px solid #dc3545;
        animation: fadeInDown 0.5s ease-out forwards;
        opacity: 0;
    }

    .floating-alert.alert-danger {
        background-color: #fff;
        color: #721c24;
        border-radius: 4px;
        padding: 15px 20px;
    }

    .floating-alert .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        font-size: 18px;
        color: #721c24;
        background: none;
        border: none;
        padding: 0;
        line-height: 1;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translate(-50%, -30px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
</style>
</head>
<body>
    <!-- Incluir el preloader -->
    <?php include 'templates/preloader.php';?>
    
    <!-- Alerta flotante para mensajes de error -->
    <?php if (!empty($error)): ?>
    <div id="floatingAlert" class="floating-alert alert-danger">
        <button class="close-btn" onclick="closeAlert()">&times;</button>
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="./images/logo.webp" alt="IMG">
				</div>

				<form class="login100-form validate-form" method="POST" action="./config/login.php">
					<span class="login100-form-title">
						Inicia Sesion
					</span>

					<div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
						<input class="input100" type="text" name="email" placeholder="Email">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-envelope" aria-hidden="true"></i>
						</span>
					</div>

					<div class="wrap-input100 validate-input" data-validate = "Password is required">
						<input class="input100" type="password" name="pass" placeholder="Password">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
					</div>
					
					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Login
						</button>
					</div>

					<div class="text-center p-t-12">
						<span class="txt1">
							Forgot
						</span>
						<a class="txt2" href="#">
							Username / Password?
						</a>
					</div>

					<div class="text-center p-t-136">
						<!-- Iconos sociales más amigables -->
						<div class="social-login mb-4">
							<a href="#" class="social-icon-link facebook">
								<i class="fab fa-facebook-f"></i>
							</a>
							<a href="#" class="social-icon-link google">
								<i class="fab fa-google"></i>
							</a>
							<a href="#" class="social-icon-link twitter">
								<i class="fab fa-twitter"></i>
							</a>
							<a href="#" class="social-icon-link instagram">
								<i class="fab fa-instagram"></i>
							</a>
						</div>
						
						<!-- Enlace de creación de cuenta -->
						<a class="create-account-link" href="./templates/registro.php">
							Create your Account
							<i class="fas fa-arrow-right" aria-hidden="true"></i>
						</a>
					</div>
					
				</form>
			</div>
		</div>
	</div>
	
<!--===============================================================================================-->	
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/tilt/tilt.jquery.min.js"></script>
	<script >
		$('.js-tilt').tilt({
			scale: 1.1
		})
	</script>
<!--===============================================================================================-->
	<script src="js/main.js"></script>
    
    <!-- Script para manejar la alerta flotante -->
    <script>
        // Auto-ocultar la alerta después de 5 segundos
        if (document.getElementById('floatingAlert')) {
            setTimeout(function() {
                closeAlert();
            }, 5000);
        }
        
        function closeAlert() {
            const alert = document.getElementById('floatingAlert');
            if (alert) {
                alert.style.animation = 'fadeOut 0.5s forwards';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }
        }
    </script>
    
    <!-- Incluir el script del preloader -->
</body>
</html>