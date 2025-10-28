<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'zenwatt'; // Nome do seu banco de dados
$username = 'root'; // Usuário padrão do XAMPP
$password = ''; // Senha padrão do XAMPP é vazia

// Inicializar variáveis
$mensagem = '';
$tipoMensagem = '';

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Não mostrar erro ao usuário para não expor informações sensíveis
    error_log("Erro na conexão: " . $e->getMessage());
}

// Processar o formulário quando for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    // Coletar e sanitizar os dados do formulário
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar-senha'];

    // Verificar se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        $mensagem = "As senhas não coincidem!";
        $tipoMensagem = "erroo";
    } else {
        // Verificar se o email já existe
        try {
            $verificaEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $verificaEmail->execute([$email]);

            if ($verificaEmail->rowCount() > 0) {
                $mensagem = "Este email já está cadastrado!";
                $tipoMensagem = "erro";
            } else {
                // Criptografar a senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Inserir no banco de dados
                $sql = "INSERT INTO usuarios (nome, email, telefone, senha, data_cadastro) 
                        VALUES (?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $telefone, $senha_hash]);


                $mensagem = "Cadastro realizado com sucesso!";
                $tipoMensagem = "sucesso";

                // Limpar o formulário
                $_POST = array();
            }
        } catch (PDOException $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            $mensagem = "Erro ao processar cadastro. Tente novamente.";
            $tipoMensagem = "erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenWatt | Controle de Consumo Elétrico Domiciliar</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/favicon/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/favicon/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/favicon/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="/favicon/favicon-196x196.png" sizes="196x196" />
    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="/favicon/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/favicon/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="/favicon/favicon-128.png" sizes="128x128" />
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="./assets/images/logo-zen.png" alt="WeBrothers Logo" class="logo-img">
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#sobre" class="nav-link">Sobre</a></li>
                <li><a href="#processo2" class="nav-link">Processo</a></li>
            </ul>
            <div class="nav-controls">
                <a href="./pages/cadastro.php" class="login-link nav-link">Cadastro</a>
                <a href="./pages/cadastro.php" class="login-link nav-link">Login</a>

                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="hero">
        <!-- Grid de listras sutis -->
        <div class="grid-overlay">
            <div class="grid-lines horizontal"></div>
            <div class="grid-lines vertical"></div>
        </div>

        <!-- Elementos geométricos modernos -->
        <div class="geometric-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>

        <div class="hero-background">
            <div class="floating-elements">
                <!-- Elementos originais -->
                <div class="floating-element" data-speed="2">
                    <i class="fas fa-code"></i>
                </div>
                <div class="floating-element" data-speed="3">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div class="floating-element" data-speed="1.5">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="floating-element" data-speed="2.5">
                    <i class="fas fa-rocket"></i>
                </div>

                <!-- Novos elementos relacionados a web -->
                <div class="floating-element web-element" data-speed="1.8">
                    <i class="fab fa-html5"></i>
                </div>
                <div class="floating-element web-element" data-speed="2.2">
                    <i class="fab fa-css3-alt"></i>
                </div>
                <div class="floating-element web-element" data-speed="1.7">
                    <i class="fab fa-js-square"></i>
                </div>
                <div class="floating-element web-element" data-speed="2.8">
                    <i class="fab fa-react"></i>
                </div>
                <div class="floating-element web-element" data-speed="1.9">
                    <i class="fas fa-database"></i>
                </div>
                <div class="floating-element web-element" data-speed="2.4">
                    <i class="fas fa-server"></i>
                </div>
                <div class="floating-element web-element" data-speed="1.6">
                    <i class="fas fa-cloud"></i>
                </div>
                <div class="floating-element web-element" data-speed="2.7">
                    <i class="fab fa-node-js"></i>
                </div>
            </div>

            <!-- Partículas flutuantes -->
            <div class="particles">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
            </div>
        </div>

        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="gradient-text">ZenWatt</span>
                    <span class="typewriter" id="typewriter"></span>
                </h1>
                <p class="hero-description">
                    Conheça o <strong>ZenWatt</strong>! O sistema inteligente que ajuda você a economizar na conta de
                    luz, monitorando seus eletrodomésticos e indicando soluções sustentáveis.
                </p>
                <div class="hero-buttons">
                    <button class="btn btn-primary" onclick="scrollToSection('sobre')">
                        <i class="fas fa-rocket"></i>
                        Conhecer Projeto
                    </button>
                    <button class="btn btn-secondary" onclick="scrollToSection('contato')">
                        <i class="fas fa-eye"></i>
                        Realizar Cadastro
                    </button>
                </div>
            </div>
            <img src="./assets/images/fav-zen.png" width="400" alt="">
        </div>

        <!-- Linha curvada decorativa -->
        <div class="curved-line">
            <svg viewBox="0 0 1200 80" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,60 Q600,20 1200,60" stroke="var(--primary-color)" stroke-width="1.5" fill="none"
                    opacity="0.4" />
            </svg>
        </div>
    </section>


    <section id="sobre" class="about">
        <div class="container">

            <div class="title-container">
                <div class="title-wrapper">
                    <span class="heading-start">SOBRE</span>
                    <div class="center-image">
                        <img src="./assets/images/fav-zen.png" alt="About Us Image">
                    </div>
                    <span class="heading-end">NÓS</span>
                </div>
            </div>

            <div class="about-content">
                <div class="about-text">
                    <div class="about-card">
                        <div class="card-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Nossa Missão</h3>
                        <p>Oferecer um sistema simples, inovador e confiável para monitorar, analisar e otimizar o
                            consumo elétrico residencial, incentivando práticas de economia e uso inteligente da
                            energia.</p>
                    </div>
                    <div class="about-card">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Nosso Jeito de Trabalhar</h3>
                        <p>Trabalhamos com inovação, colaboração e responsabilidade socioambiental. Nossa prioridade é
                            unir tecnologia e sustentabilidade para entregar valor real às pessoas e à sociedade, sempre
                            com foco em resultados práticos e acessíveis.</p>
                    </div>
                    <div class="about-card">
                        <div class="card-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Nossa Visão</h3>
                        <p>Ser referência em soluções tecnológicas que promovam eficiência energética, ajudando lares a
                            reduzir custos e contribuindo para um futuro mais sustentável e consciente.</p>
                    </div>
                </div>
                <div class="about-stats">
                    <div class="stat-item">
                        <div class="stat-number" data-target="100">0</div>
                        <div class="stat-label">% Sustentável</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="100">0</div>
                        <div class="stat-label">% Moderno</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="100">0</div>
                        <div class="stat-label">% Inovador</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="100">0</div>
                        <div class="stat-label">% Tecnológico</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="processo2">
        <div class="container">
            <h2 class="section-title">Nosso Processo</h2>
            <div class="timeline">
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>1. Cadastre-se</h3>
                        <p>Crie sua conta e informe os dados básicos da sua residência.</p>
                    </div>
                </div>
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-pencil-alt"></i>
                        </div>
                        <h3>2. Adicione equipamentos</h3>
                        <p>Selecione os eletrodomésticos que você utiliza no dia a dia.</p>
                    </div>
                </div>
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>3. Monitore o consumo</h3>
                        <p>Veja relatórios claros sobre o gasto de energia de cada aparelho.</p>
                    </div>
                </div>

                <div class="timeline-item right">
                    <div class="timeline-content">
                        <div class="timeline-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>4. Economize</h3>
                        <p>Receba sugestões de equipamentos mais eficientes e dicas para reduzir sua conta de luz.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
<section id="cta-processo">
  <div class="container">
    <h2 class="section-title">Transforme seu Consumo em Economia</h2>
    <p class="section-subtitle">
      Cadastre-se agora e descubra como monitorar seus gastos de energia, 
      receber relatórios claros e dicas práticas para reduzir sua conta de luz.
    </p>

    

    <div class="cta-button">
      <a href="./pages/cadastro.php" class="btn-cadastro">Quero Economizar Agora</a>
    </div>
  </div>
</section>


    <!-- Footer -->
    <footer id="footer" class="footer">
        <div class="footer-background">
            <div class="footer-waves">
                <div class="wave wave-1"></div>
                <div class="wave wave-2"></div>
                <div class="wave wave-3"></div>
            </div>
            <div class="floating-particles">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
                <div class="particle particle-5"></div>
            </div>
        </div>

        <div class="container">
            <div class="footer-content">

                <div class="footer-bottom">
                    <div class="footer-bottom-content">
                        <div class="copyright">
                            <p>&copy; 2025 <span class="brand-highlight">ZenWatt</span>. Todos os direitos reservados.
                            </p>
                        </div>
                        <div class="footer-links">
                            <a href="#" class="footer-link">
                                <i class="fas fa-shield-alt"></i>
                                Política de Privacidade
                            </a>
                            <a href="#" class="footer-link">
                                <i class="fas fa-file-contract"></i>
                                Termos de Uso
                            </a>
                        </div>
                    </div>
                    <div class="scroll-to-top" id="scrollToTop">
                        <i class="fas fa-arrow-up"></i>
                        <div class="scroll-ripple"></div>
                    </div>
                </div>
            </div>
    </footer>

    <script src="./assets/js/script.js"></script>

    <script>
        // Máscara de telefone (formato (99) 99999-9999)
        const telefoneInput = document.getElementById('telefone');
        telefoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ""); // só números
            if (value.length > 11) value = value.slice(0, 11); // limita 11 dígitos

            if (value.length <= 10) {
                e.target.value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
            } else {
                e.target.value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, "($1) $2-$3");
            }
        });

        // Função para mostrar/ocultar senha
        function toggleSenha(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector("i");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Validação de confirmação de senha
        document.querySelector('form').addEventListener('submit', function (e) {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar-senha').value;

            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>

    <style>
        .mensagem {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</body>

</html>