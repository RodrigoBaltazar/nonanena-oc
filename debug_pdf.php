<?php
session_start();
require_once 'config.php';

// Verificar se usuário está logado
requireLogin();

// Conectar ao banco
$db = new PDO('sqlite:' . DB_PATH);
$produtos = $db->query("SELECT * FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Obter preço por kg da sessão ou usar padrão
$preco_por_kg = isset($_SESSION['preco_por_kg']) ? $_SESSION['preco_por_kg'] : 15.00;

echo "<h1>Debug PDF - Dados dos Produtos</h1>";
echo "<p><strong>Preço por kg da sessão:</strong> R$ " . number_format($preco_por_kg, 2, ',', '.') . "</p>";
echo "<p><strong>Total de produtos:</strong> " . count($produtos) . "</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

// Verificar se há dados na sessão
echo "<h2>Dados da Sessão</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (empty($produtos)) {
    echo "<p>Nenhum produto encontrado no banco de dados.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Nome</th><th>Tipo</th><th>Preço</th><th>Peso (g)</th><th>Created At</th>";
    echo "</tr>";
    
    foreach ($produtos as $produto) {
        echo "<tr>";
        echo "<td>" . $produto['id'] . "</td>";
        echo "<td>" . htmlspecialchars($produto['nome']) . "</td>";
        echo "<td>" . $produto['tipo'] . "</td>";
        echo "<td>R$ " . number_format($produto['preco'], 2, ',', '.') . "</td>";
        echo "<td>" . (isset($produto['peso_gramas']) ? number_format($produto['peso_gramas'], 0, ',', '.') . 'g' : 'N/A') . "</td>";
        echo "<td>" . $produto['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h2>Estrutura da Tabela</h2>";
$result = $db->query("PRAGMA table_info(produtos)");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Coluna</th><th>Tipo</th><th>Not Null</th><th>Default</th></tr>";
foreach ($columns as $column) {
    echo "<tr>";
    echo "<td>" . $column['name'] . "</td>";
    echo "<td>" . $column['type'] . "</td>";
    echo "<td>" . ($column['notnull'] ? 'Sim' : 'Não') . "</td>";
    echo "<td>" . ($column['dflt_value'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
