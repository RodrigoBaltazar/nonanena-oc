<?php
session_start();
require_once 'config.php';

// Verificar se usuário está logado
requireLogin();

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Incluir TCPDF
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

// Conectar ao banco
$db = new PDO('sqlite:' . DB_PATH);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buscar produtos com dados frescos
$stmt = $db->prepare("SELECT * FROM produtos ORDER BY nome");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter preço por kg da sessão ou usar padrão
$preco_por_kg = isset($_SESSION['preco_por_kg']) ? $_SESSION['preco_por_kg'] : 15.00;

// Debug: Verificar dados da sessão
error_log("PDF Debug - Preço por kg: " . $preco_por_kg);
error_log("PDF Debug - Total produtos: " . count($produtos));

// Criar PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurações do documento
$pdf->SetCreator('Sistema de Gestão de Produtos');
$pdf->SetAuthor('Sistema de Produtos');
$pdf->SetTitle('Relatório de Produtos');
$pdf->SetSubject('Listagem de Produtos');

// Configurações de margem
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Configurações de fonte
$pdf->SetFont('helvetica', '', 10);

// Adicionar página
$pdf->AddPage();

// Título principal
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 12, 'ORDEM DE COMPRA', 0, 1, 'C');
$pdf->Ln(8);

// Nome do cliente (vem do index.php)
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(102, 102, 102);
$pdf->Cell(0, 6, 'Cliente: ' . (isset($_SESSION['username']) ? $_SESSION['username'] : 'Sistema de Gestão de Produtos'), 0, 1, 'C');
$pdf->Ln(10);

// Data de geração
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 6, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
$pdf->Ln(8);

if (empty($produtos)) {
    $pdf->SetFont('helvetica', 'I', 14);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 15, 'Nenhum produto cadastrado', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 8, 'Adicione produtos no sistema para gerar o relatório', 0, 1, 'C');
} else {
    // Linha separadora
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);
    // Cabeçalho da tabela
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(67, 126, 234); // Azul moderno
    $pdf->SetTextColor(255, 255, 255); // Texto branco
    
    $pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'PRODUTO', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'TIPO', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'PESO', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'PREÇO', 1, 0, 'C', true);
    $pdf->Ln();
    
    // Dados dos produtos
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(51, 51, 51); // Texto escuro
    $total_produtos = 0;
    $valor_total = 0;
    $alternate = false;
    
    foreach ($produtos as $produto) {
        // Alternar cor de fundo para melhor legibilidade
        if ($alternate) {
            $pdf->SetFillColor(248, 249, 250); // Cinza muito claro
        } else {
            $pdf->SetFillColor(255, 255, 255); // Branco
        }
        
        $pdf->Cell(15, 8, $produto['id'], 1, 0, 'C', true);
        $pdf->Cell(40, 8, $produto['nome'], 1, 0, 'L', true);
        $pdf->Cell(25, 8, $produto['tipo'] == 'kg' ? 'Por Quilo' : 'Por Unidade', 1, 0, 'C', true);
        
        // Mostrar peso baseado no tipo
        if ($produto['tipo'] == 'kg' && isset($produto['peso_gramas'])) {
            $pdf->Cell(20, 8, number_format($produto['peso_gramas'], 0, ',', '.') . 'g', 1, 0, 'C', true);
        } else {
            $pdf->Cell(20, 8, '-', 1, 0, 'C', true);
        }
        
        // Mostrar preço baseado no tipo
        if ($produto['tipo'] == 'kg') {
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(40, 8, 'R$ ' . number_format($produto['preco'], 2, ',', '.') . ' (R$ ' . number_format($preco_por_kg, 2, ',', '.') . '/kg)', 1, 0, 'R', true);
        } else {
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(40, 8, 'R$ ' . number_format($produto['preco'], 2, ',', '.') . '', 1, 0, 'R', true);
        }
        
        // Somar ao valor total
        $valor_total += $produto['preco'];
        $total_produtos++;
        $alternate = !$alternate; // Alternar cor
        
        $pdf->Ln();
    }
    
    // Linha de total
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(39, 174, 96); // Verde para destacar
    $pdf->SetTextColor(255, 255, 255); // Texto branco
    $pdf->Cell(80, 10, 'TOTAL DE PRODUTOS:', 1, 0, 'R', true);
    $pdf->Cell(20, 10, $total_produtos . ' itens', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'R$ ' . number_format($valor_total, 2, ',', '.'), 1, 0, 'R', true);
    $pdf->Ln();
}

// Rodapé
$pdf->SetY(-35);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 6, 'www.nonanena.com.br', 0, 0, 'C');
$pdf->Ln(3);
$pdf->Cell(0, 6, 'Desenvolvido por www.rodrigobaltazar.com.br', 0, 0, 'C');
$pdf->Ln(3);
$pdf->Cell(0, 6, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 0, 'C');

// Gerar PDF
$pdf->Output('relatorio_produtos_' . date('Y-m-d_H-i-s') . '.pdf', 'I');
?>
