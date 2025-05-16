<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registro - OUR CENTER</title>
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
    <!-- Manifiesto para PWA -->
    <link rel="manifest" href="./images/favicon/site.webmanifest">
    <!-- Meta tag para el color de la barra de dirección en móviles -->
    <meta name="theme-color" content="#0a1b5c">
    <!-- Para Microsoft Edge y IE -->
    <meta name="msapplication-TileColor" content="#0a1b5c">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animate CSS -->
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <!-- Hamburgers CSS -->
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <!-- Select2 CSS -->
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <!-- Util CSS -->
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <!-- Toastify JS (para notificaciones) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        :root {
            --primary-color: #0a1b5c;    /* Azul oscuro principal */
            --secondary-color: #e60000;  /* Rojo */
            --light-color: #ffffff;      /* Blanco */
            --accent-color:rgb(255, 0, 0);     /* Azul más claro */
            --text-color: #333333;
            --border-color: #e0e0e0;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .limiter {
            width: 100%;
            margin: 0 auto;
        }
        
        .container-register {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), #183884);
        }
        
        .wrap-register {
            width: 960px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        .register-pic {
            width: 100%;
            text-align: center;
            padding: 30px 0;
        }
        
        .register-pic img {
            max-height: 150px;
            transition: transform 0.3s;
        }
        
        .register-pic img:hover {
            transform: scale(1.05);
        }
        
        .register-form {
            width: 100%;
        }
        
        .register-form-title {
            font-size: 28px;
            color: var(--primary-color);
            line-height: 1.2;
            text-align: center;
            font-weight: 700;
            width: 100%;
            display: block;
            padding-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .wrap-input {
            position: relative;
            width: 100%;
            z-index: 1;
            margin-bottom: 20px;
        }
        
        .input {
            font-size: 15px;
            line-height: 1.5;
            color: var(--text-color);
            display: block;
            width: 100%;
            background: #f7f7f7;
            height: 50px;
            border-radius: 25px;
            padding: 0 30px 0 53px;
            border: 1px solid var(--border-color);
            transition: all 0.4s;
        }
        
        .input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 8px rgba(30, 136, 229, 0.4);
        }
        
        .focus-input {
            display: block;
            position: absolute;
            border-radius: 25px;
            bottom: 0;
            left: 0;
            z-index: -1;
            width: 100%;
            height: 100%;
            box-shadow: 0px 0px 0px 0px;
            color: var(--accent-color);
        }
        
        .symbol-input {
            font-size: 15px;
            display: flex;
            align-items: center;
            position: absolute;
            border-radius: 25px;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding-left: 25px;
            pointer-events: none;
            color: #666666;
            transition: all 0.4s;
        }
        
        .input:focus + .focus-input + .symbol-input {
            color: var(--accent-color);
            padding-left: 22px;
        }
        
        .container-register-form-btn {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding-top: 20px;
        }
        
        .register-form-btn {
            font-size: 15px;
            line-height: 1.5;
            color: #fff;
            text-transform: uppercase;
            width: 100%;
            height: 50px;
            border-radius: 25px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 25px;
            transition: all 0.4s;
            border: none;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .register-form-btn:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .register-form-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .text-center {
            text-align: center;
        }
        
        .p-t-12 {
            padding-top: 12px;
        }
        
        .p-t-30 {
            padding-top: 30px;
        }
        
        .txt1 {
            font-size: 13px;
            color: #666666;
            line-height: 1.5;
        }
        
        .txt2 {
            font-size: 13px;
            color: var(--accent-color);
            line-height: 1.5;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .txt2:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .login-link {
            display: inline-block;
            color: #666;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .login-link:hover {
            color: var(--primary-color);
        }
        
        .login-link i {
            margin-left: 5px;
            transition: transform 0.3s;
        }
        
        .login-link:hover i {
            transform: translateX(5px);
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .form-col {
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 15px;
            padding-left: 15px;
        }
        
        .password-requirements {
            margin-top: 15px;
            background-color: #f7f7f7;
            border-radius: 8px;
            padding: 15px;
            font-size: 0.85rem;
        }
        
        .password-requirements p {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .password-requirements ul {
            padding-left: 25px;
            margin-bottom: 0;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
            color: #777;
            transition: color 0.3s ease;
        }
        
        .password-requirements li.valid {
            color: #4caf50;
        }
        
        .password-requirements li.valid::before {
            content: "✓ ";
        }
        
        .select {
            font-size: 15px;
            line-height: 1.5;
            color: var(--text-color);
            display: block;
            width: 100%;
            background: #f7f7f7;
            height: 50px;
            border-radius: 25px;
            padding: 0 30px 0 53px;
            border: 1px solid var(--border-color);
            transition: all 0.4s;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 16px;
        }
        
        .select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 8px rgb(252, 0, 0);
        }
        
        .form-check {
            padding-left: 30px;
            margin-bottom: 12px;
        }
        
        .form-check-input {
            margin-top: 0.3rem;
            margin-left: -30px;
            width: 18px;
            height: 18px;
        }
        
        .required {
            color: var(--secondary-color);
            margin-left: 3px;
        }
        
        /* Nuevos estilos para mejoras */
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #777;
            cursor: pointer;
            font-size: 16px;
            z-index: 10;
        }
        
        .password-strength-meter {
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .password-strength-meter div {
            height: 100%;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .strength-label {
            font-size: 0.75rem;
            margin-top: 5px;
            text-align: right;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .input-feedback {
            font-size: 0.8rem;
            margin-top: 5px;
            margin-left: 10px;
            transition: all 0.3s;
        }
        
        .valid-feedback {
            color: var(--success-color);
            display: none;
        }
        
        .invalid-feedback {
            color: var(--danger-color);
            display: none;
        }
        
        .input.is-valid {
            border-color: var(--success-color);
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%234caf50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        .input.is-invalid {
            border-color: var(--danger-color);
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23f44336" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        .location-btn {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #777;
            cursor: pointer;
            font-size: 16px;
            z-index: 10;
        }
        
        #map-container {
            height: 0;
            overflow: hidden;
            transition: height 0.3s ease;
        }
        
        #map-container.show {
            height: 300px;
            margin-bottom: 20px;
        }
        
        #map {
            width: 100%;
            height: 100%;
            border-radius: 10px;
        }
        
        .loading-indicator {
            display: none;
            position: absolute;
            right: 55px;
            top: 15px;
            color: var(--primary-color);
            z-index: 10;
        }
        
        .input-with-loading {
            padding-right: 75px;
        }
        
        .birthday-alert {
            display: none;
            margin-top: 10px;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .steps-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }
        
        .step-number {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #666;
            font-weight: 600;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: var(--success-color);
            color: white;
        }
        
        .step-label {
            font-size: 14px;
            color: #666;
            transition: all 0.3s;
        }
        
        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .step.completed .step-label {
            color: var(--success-color);
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn-prev {
            background-color: #e0e0e0;
            color: #666;
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-prev:hover {
            background-color: #d0d0d0;
        }
        
        .btn-next {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-next:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }
        
        .btn-next:disabled, .btn-prev:disabled {
            background: #cccccc;
            color: #666;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .form-col {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .steps-indicator {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .step {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="limiter">
        <div class="container-register">
            <div class="wrap-register">
                <div class="register-pic js-tilt" data-tilt>
                    <img src="../images/logo.webp" alt="IMG">
                </div>

                <form id="registrationForm" class="register-form validate-form" method="POST" action="procesar_registro.php" novalidate>
                    <span class="register-form-title">
                        Crea tu cuenta en OUR CENTER
                    </span>
                    
                    <!-- Indicador de pasos -->
                    <div class="steps-indicator">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Información Personal</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Contacto</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Seguridad</div>
                        </div>
                    </div>
                    
                    <!-- Sección 1: Información Personal -->
                    <div class="form-section active" data-section="1">
                        <h3 class="section-title"><i class="fas fa-user-circle"></i> Información Personal</h3>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="wrap-input validate-input" data-validate="El nombre es requerido">
                                    <input class="input" type="text" id="nombre" name="nombre" placeholder="Nombre">
                                    <span class="focus-input"></span>
                                    <span class="symbol-input">
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                    </span>
                                    <div class="input-feedback" id="nombre-feedback"></div>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="wrap-input validate-input" data-validate="El apellido es requerido">
                                    <input class="input" type="text" id="apellido" name="apellido" placeholder="Apellido">
                                    <span class="focus-input"></span>
                                    <span class="symbol-input">
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                    </span>
                                    <div class="input-feedback" id="apellido-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="wrap-input validate-input" data-validate="El tipo de documento es requerido">
                                    <select class="select" id="tipo_documento" name="tipo_documento">
                                        <option value="" selected disabled>Tipo de Documento</option>
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="PAS">Pasaporte</option>
                                    </select>
                                    <span class="focus-input"></span>
                                    <span class="symbol-input">
                                        <i class="fa fa-id-card" aria-hidden="true"></i>
                                    </span>
                                    <div class="input-feedback" id="tipo_documento-feedback"></div>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="wrap-input validate-input" data-validate="El número de documento es requerido">
                                    <input class="input" type="text" id="documento_identidad" name="documento_identidad" placeholder="Número de Documento">
                                    <span class="focus-input"></span>
                                    <span class="symbol-input">
                                        <i class="fa fa-id-badge" aria-hidden="true"></i>
                                    </span>
                                    <div class="input-feedback" id="documento_identidad-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wrap-input validate-input" data-validate="La fecha de nacimiento es requerida">
                            <input class="input" type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                            <span class="focus-input"></span>
                            <span class="symbol-input">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                            </span>
                            <div class="input-feedback" id="fecha_nacimiento-feedback"></div>
                            <div class="birthday-alert" id="birthday-alert">
                                <i class="fas fa-exclamation-triangle"></i> Debes ser mayor de 18 años para registrarte.
                            </div>
                        </div>
                        
                        <div class="navigation-buttons">
                            <button type="button" class="btn-prev" id="prev-1" disabled>Anterior</button>
                            <button type="button" class="btn-next" id="next-1">Siguiente</button>
                        </div>
                    </div>
                    
                    <!-- Sección 2: Información de Contacto -->
                    <div class="form-section" data-section="2">
                        <h3 class="section-title"><i class="fas fa-envelope"></i> Información de Contacto</h3>
                        <div class="wrap-input validate-input" data-validate="El email válido es requerido: ex@abc.xyz">
                            <input class="input input-with-loading" type="email" id="email" name="email" placeholder="Correo Electrónico">
                            <span class="focus-input"></span>
                            <span class="symbol-input">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </span>
                            <span class="loading-indicator" id="email-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            <div class="input-feedback" id="email-feedback"></div>
                        </div>
                        
                        <div class="wrap-input validate-input" data-validate="El teléfono es requerido">
                            <input class="input" type="tel" id="telefono" name="telefono" placeholder="Teléfono">
                            <span class="focus-input"></span>
                            <span class="symbol-input">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                            </span>
                            <span class="location-btn" id="get-location" title="Usar mi ubicación">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div class="input-feedback" id="telefono-feedback"></div>
                        </div>
                        
                        <!-- Contenedor del mapa -->
                        <div id="map-container">
                            <div id="map"></div>
                        </div>
                        
                        <div class="navigation-buttons">
                            <button type="button" class="btn-prev" id="prev-2">Anterior</button>
                            <button type="button" class="btn-next" id="next-2">Siguiente</button>
                        </div>
                    </div>
                    
                    <!-- Sección 3: Seguridad -->
                    <div class="form-section" data-section="3">
                        <h3 class="section-title"><i class="fas fa-lock"></i> Seguridad</h3>
                        <div class="wrap-input validate-input" data-validate="La contraseña es requerida">
                            <input class="input" type="password" id="password" name="password" placeholder="Contraseña">
                            <span class="focus-input"></span>
                            <span class="symbol-input">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                            <span class="password-toggle" id="password-toggle">
                                <i class="fa fa-eye"></i>
                            </span>
                            <div class="input-feedback" id="password-feedback"></div>
                        </div>
                        
                        <div class="password-strength-meter">
                            <div id="strength-bar"></div>
                        </div>
                        <div class="strength-label" id="strength-text">Sin contraseña</div>
                        
                        <div class="password-requirements">
                            <p><i class="fas fa-info-circle"></i> La contraseña debe tener:</p>
                            <ul>
                                <li id="length">Al menos 8 caracteres</li>
                                <li id="letter">Al menos una letra minúscula</li>
                                <li id="capital">Al menos una letra mayúscula</li>
                                <li id="number">Al menos un número</li>
                                <li id="special">Al menos un carácter especial</li>
                            </ul>
                        </div>
                        
                        <div class="wrap-input validate-input"data-validate="La confirmación de la contraseña es requerida">
                            <input class="input" type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar Contraseña">
                            <span class="focus-input"></span>
                            <span class="symbol-input">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                            <div class="input-feedback" id="confirm_password-feedback"></div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms">
                            <label class="form-check-label" for="accept_terms">
                                Acepto los <a href="terminos_condiciones.html" class="txt2">Términos y Condiciones</a>
                            </label>
                            <div class="input-feedback" id="accept_terms-feedback"></div>
                        </div>

                        <div class="container-register-form-btn">
                            <button type="button" class="register-form-btn" id="prev-3">
                                Anterior
                            </button>
                            <button type="submit" class="register-form-btn" id="register-btn">
                                Registrar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="text-center p-t-30 txt2">
                    ¿Ya tienes una cuenta?
                    <a class="login-link" href="login.html">
                        Iniciar Sesión <i class="fa fa-long-arrow-alt-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <!-- Select2 JS -->
    <script src="vendor/select2/select2.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Main JS -->
    <script src="js/main.js"></script>
</body>
</html>
