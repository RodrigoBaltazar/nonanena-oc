<?php
session_start();
require_once 'config.php';

// Verificar se usuário está logado
requireLogin();

// Criar diretório data se não existir
if (!is_dir('data')) {
    mkdir('data', 0755, true);
}

// Inicializar banco SQLite
$db = new PDO('sqlite:' . DB_PATH);

// Criar tabela se não existir
$db->exec("CREATE TABLE IF NOT EXISTS produtos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    tipo TEXT NOT NULL CHECK(tipo IN ('kg', 'unidade')),
    preco REAL NOT NULL,
    peso_gramas INTEGER DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Adicionar coluna peso_gramas se não existir (para tabelas já criadas)
try {
    $db->exec("ALTER TABLE produtos ADD COLUMN peso_gramas INTEGER DEFAULT NULL");
} catch (PDOException $e) {
    // Coluna já existe, ignorar erro
}

// Obter preço por kg da sessão ou usar padrão
$preco_por_kg = isset($_SESSION['preco_por_kg']) ? $_SESSION['preco_por_kg'] : 15.00;

// Obter data da ordem de compra da sessão ou usar data atual
$data_ordem = isset($_SESSION['data_ordem']) ? $_SESSION['data_ordem'] : date('Y-m-d');

// Processar formulário
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if ($_POST['tipo'] == 'kg') {
                    // Para produtos por kg, calcular preço baseado no peso
                    $peso_gramas = intval($_POST['peso_gramas']);
                    $preco = ($peso_gramas / 1000) * $preco_por_kg; // Converter gramas para kg
                    $stmt = $db->prepare("INSERT INTO produtos (nome, tipo, preco, peso_gramas) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_POST['nome'], $_POST['tipo'], $preco, $peso_gramas]);
                } else {
                    // Para produtos por unidade, usar preço direto
                    $stmt = $db->prepare("INSERT INTO produtos (nome, tipo, preco) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['nome'], $_POST['tipo'], $_POST['preco']]);
                }
                break;
            case 'delete':
                $stmt = $db->prepare("DELETE FROM produtos WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
    }
}

// Processar configuração de preço por kg
if (isset($_POST['config_preco_kg'])) {
    $_SESSION['preco_por_kg'] = floatval($_POST['preco_por_kg']);
    $preco_por_kg = $_SESSION['preco_por_kg'];
}

// Processar configuração de data da ordem
if (isset($_POST['config_data_ordem'])) {
    $_SESSION['data_ordem'] = $_POST['data_ordem'];
    $data_ordem = $_SESSION['data_ordem'];
}

// Buscar produtos
$produtos = $db->query("SELECT * FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Produtos</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2196F3">
    <link rel="apple-touch-icon" href="icon-192.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .form-section {
            padding: 30px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .products-section {
            padding: 30px;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #fafafa;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .product-details {
            color: #666;
            font-size: 14px;
        }
        
        .product-price {
            font-weight: 600;
            color: #27ae60;
            font-size: 18px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .product-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1>📦 Gestão de Produtos</h1>
                    <p>Cadastre produtos e gere relatórios em PDF</p>
                </div>
                <div style="text-align: right;">
                    <p style="color: rgba(255,255,255,0.8); margin-bottom: 10px;">
                        Olá, <?= htmlspecialchars($_SESSION['username']) ?>
                    </p>
                    <a href="logout.php" style="color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px; transition: background 0.3s;">
                        🚪 Sair
                    </a>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2>Configuração da Ordem de Compra</h2>
            <div class="form-row">
                <form method="POST" style="margin-bottom: 30px; flex: 1; margin-right: 15px;">
                    <input type="hidden" name="config_preco_kg" value="1">
                    <div class="form-group">
                        <label for="preco_por_kg">Preço por Quilo (R$)</label>
                        <input type="number" id="preco_por_kg" name="preco_por_kg" step="0.01" min="0" value="<?= $preco_por_kg ?>" required>
                        <small style="color: #666; font-size: 12px;">Todos os produtos por kg terão este preço fixo</small>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Preço por Kg</button>
                </form>
                
                <form method="POST" style="margin-bottom: 30px; flex: 1; margin-left: 15px;">
                    <input type="hidden" name="config_data_ordem" value="1">
                    <div class="form-group">
                        <label for="data_ordem">Data da Ordem de Compra</label>
                        <input type="date" id="data_ordem" name="data_ordem" value="<?= $data_ordem ?>" required>
                        <small style="color: #666; font-size: 12px;">Data que aparecerá no PDF da ordem de compra</small>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Data</button>
                </form>
            </div>
        </div>
        
        <div class="form-section">
            <h2>Adicionar Produto</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="nome">Nome do Produto</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" required onchange="togglePrecoField()">
                            <option value="">Selecione...</option>
                            <option value="kg">Por Quilo (kg)</option>
                            <option value="unidade">Por Unidade</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="peso_gramas" id="peso_label" style="display: none;">Peso (gramas)</label>
                        <label for="preco" id="preco_label">Preço</label>
                        <input type="number" id="peso_gramas" name="peso_gramas" min="1" style="display: none;">
                        <input type="number" id="preco" name="preco" step="0.01" min="0">
                        <input type="hidden" id="preco_kg" name="preco_kg" value="<?= $preco_por_kg ?>">
                    </div>
                </div>
                <button type="submit" class="btn">Adicionar Produto</button>
            </form>
        </div>
        
        <div class="products-section">
            <h2>Produtos Cadastrados</h2>
            <?php if (empty($produtos)): ?>
                <div class="empty-state">
                    <p>Nenhum produto cadastrado ainda.</p>
                    <p>Adicione seu primeiro produto acima!</p>
                </div>
            <?php else: ?>
                <?php foreach ($produtos as $produto): ?>
                    <div class="product-item">
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($produto['nome']) ?></div>
                            <div class="product-details">
                                Tipo: <?= $produto['tipo'] == 'kg' ? 'Por Quilo' : 'Por Unidade' ?>
                                <?php if ($produto['tipo'] == 'kg' && isset($produto['peso_gramas'])): ?>
                                    <br>Peso: <?= number_format($produto['peso_gramas'], 0, ',', '.') ?>g
                                    <br><small style="color: #999;">Preço por kg: R$ <?= number_format($preco_por_kg, 2, ',', '.') ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="product-price">
                            <?php if ($produto['tipo'] == 'kg'): ?>
                                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                                <br><small style="color: #999;">(<?= number_format($preco_por_kg, 2, ',', '.') ?>/kg)</small>
                            <?php else: ?>
                                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                            <?php endif; ?>
                        </div>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($produtos)): ?>
                <div class="actions">
                    <a href="gerar_pdf.php?t=<?= time() ?>" class="btn btn-success" target="_blank">📄 Gerar PDF</a>
                    <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Função para controlar campos do formulário
        function togglePrecoField() {
            const tipo = document.getElementById('tipo').value;
            const precoField = document.getElementById('preco');
            const pesoField = document.getElementById('peso_gramas');
            const precoLabel = document.getElementById('preco_label');
            const pesoLabel = document.getElementById('peso_label');
            
            if (tipo === 'kg') {
                // Mostrar campo de peso, esconder campo de preço
                pesoField.style.display = 'block';
                pesoField.required = true;
                precoField.style.display = 'none';
                precoField.required = false;
                pesoLabel.style.display = 'block';
                precoLabel.style.display = 'none';
                pesoField.value = '';
            } else if (tipo === 'unidade') {
                // Mostrar campo de preço, esconder campo de peso
                precoField.style.display = 'block';
                precoField.required = true;
                pesoField.style.display = 'none';
                pesoField.required = false;
                precoLabel.style.display = 'block';
                pesoLabel.style.display = 'none';
                precoField.value = '';
            } else {
                // Esconder ambos os campos
                precoField.style.display = 'none';
                pesoField.style.display = 'none';
                precoField.required = false;
                pesoField.required = false;
                precoLabel.style.display = 'none';
                pesoLabel.style.display = 'none';
            }
        }
        
        // Registrar Service Worker para PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
        
        // Instalar PWA
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // Mostrar botão de instalação
            const installBtn = document.createElement('button');
            installBtn.textContent = '📱 Instalar App';
            installBtn.className = 'btn';
            installBtn.style.marginTop = '20px';
            installBtn.onclick = () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA instalado');
                    }
                    deferredPrompt = null;
                });
            };
            document.querySelector('.header').appendChild(installBtn);
        });
    </script>
</body>
</html>
