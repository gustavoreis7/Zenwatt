<?php
session_start();

// Verificar se o usuário já está logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: usuario.php');
    exit();
}

// Usar caminho absoluto para evitar erros
$base_dir = dirname(__DIR__, 2); // Volta 2 níveis para a pasta PIzenwatt
require_once $base_dir . '/app/config/database.php';

$db = new Database();
$erro = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        try {
            // Buscar usuário pelo email
            $stmt = $db->pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            // Verificar se usuário existe e senha está correta
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                
                // Redirecionar para dashboard
                header('Location: usuario.php');
                exit();
            } else {
                $erro = 'Email ou senha incorretos!';
            }
        } catch (PDOException $e) {
            $erro = 'Erro no sistema. Tente novamente.';
            error_log("Erro login: " . $e->getMessage());
        }
    } else {
        $erro = 'Por favor, preencha todos os campos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZenWatt | Controle de Consumo Elétrico</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../index.php">
                    <img src="../assets/images/logo-zen.png" alt="ZenWatt Logo" class="logo-img">
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link"><i class="fa-solid fa-house"></i></a></li>
                <li><a href="../index.php#contato" class="nav-link">Cadastro</a></li>
            </ul>
        </div>
    </nav>

    <!-- Login Section -->
    <section id="home" class="hero" style="background: url(../assets/images/favicon/banner-login.png) no-repeat center center; background-size: cover; height: 100vh;">
        <div class="login-box">
            <?php if (!empty($erro)): ?>
                <div class="mensagem-erro"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="senha" placeholder="Senha" required>
                    <span class="toggle-password" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
                </div>
                <button type="submit" class="btn">Entrar</button>
                <div class="options" style="align-items: center; justify-content: center;">
                    <p>Não possui cadastro? <a href="../index.php#contato">Clique aqui</a></p>
                    <a href="#">Esqueceu a senha?</a>
                </div>
            </form>
        </div>
    </section>

<style>
  .hero {
    background: url(../banner-login.png) no-repeat center center;
    background-size: cover;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  /* Caixa de login */
  .login-box {
    background: rgba(255, 255, 255, 0.041);
    backdrop-filter: blur(4px);
    border-radius: 20px;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  }

  .login-box form {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Grupos de input */
  .input-group {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.15);
    border-radius: 30px;
    padding: 12px 15px;
    position: relative;
  }

  .input-group .icon {
    margin-right: 10px;
    font-size: 12px;
    flex-shrink: 0;
    color: #fff;
  }

  .input-group input {
    border: none;
    background: transparent;
    outline: none;
    color: #fff;
    font-size: 16px;
    flex: 1;
    min-width: 0;
  }

  .input-group input::placeholder {
    color: rgba(255,255,255,0.7);
  }

  .toggle-password {
    cursor: pointer;
    font-size: 12px;
    margin-left: 10px;
    flex-shrink: 0;
    color: #fff;
  }

  /* Botão */
  .btn {
    background: #04b600;
    border: none;
    border-radius: 30px;
    padding: 12px;
    font-size: 16px;
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
  }

  .btn:hover {
    background:#04ff00;
  }

  /* Opções extras */
  .options {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #ddd;
    flex-wrap: wrap;
    gap: 10px;
  }

  .options a {
    color: #bbb;
    text-decoration: none;
    margin: 0 auto;
  }

  .options a:hover {
    text-decoration: underline;
  }

  label {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  /* Responsividade */
  @media (max-width: 480px) {
    .login-box {
      padding: 25px;
    }

    .input-group {
      padding: 10px 12px;
    }

    .input-group input {
      font-size: 14px;
    }

    .btn {
      font-size: 14px;
      padding: 10px;
    }

        .hero {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-box {
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.18);
        }

        .login-box form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0);
            border-radius: 30px;
            padding: 12px 15px;
            position: relative;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .input-group .icon {
            margin-right: 10px;
            font-size: 16px;
            color: #fff;
            width: 20px;
            text-align: center;
        }

        .input-group input {
            border: none;
            background: transparent;
            outline: none;
            color: #fff;
            font-size: 16px;
            flex: 1;
            min-width: 0;
        }

        .input-group input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .toggle-password {
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
            color: #fff;
        }

        .btn {
            background: var(--gradient-primary);
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .options {
            text-align: center;
            font-size: 14px;
        }

        .options a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        .options a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .mensagem-erro {
            background: rgba(255,0,0,0.2);
            color: #ff6b6b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255,0,0,0.3);
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 25px;
                margin: 0 10px;
            }
        }
      }
</style>

<script>
  function togglePassword() {
    const input = document.getElementById("password");
    const toggle = document.querySelector(".toggle-password i");
    if (input.type === "password") {
      input.type = "text";
      toggle.classList.remove("fa-eye");
      toggle.classList.add("fa-eye-slash");
    } else {
      input.type = "password";
      toggle.classList.remove("fa-eye-slash");
      toggle.classList.add("fa-eye");
    }
  }
</script>

<!-- Importa os ícones do Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script src="../assets/js/login.js"></script>
    
   
</body>
</html>