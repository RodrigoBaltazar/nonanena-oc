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

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'RELATÓRIO DE PRODUTOS', 0, 1, 'C');
$pdf->Ln(10);

// Data de geração
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
$pdf->Ln(5);

if (empty($produtos)) {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 10, 'Nenhum produto cadastrado.', 0, 1, 'C');
} else {
    // Cabeçalho da tabela
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    
    $pdf->Cell(50, 8, 'PRODUTO', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'TIPO', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'PESO', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'PREÇO', 1, 0, 'C', true);
    $pdf->Ln();
    
    // Dados dos produtos
    $pdf->SetFont('helvetica', '', 9);
    $total_produtos = 0;
    $valor_total = 0;
    
    foreach ($produtos as $produto) {
        $pdf->Cell(50, 6, $produto['nome'], 1, 0, 'L');
        $pdf->Cell(30, 6, $produto['tipo'] == 'kg' ? 'Por Quilo' : 'Por Unidade', 1, 0, 'C');
        
        // Mostrar peso baseado no tipo
        if ($produto['tipo'] == 'kg' && isset($produto['peso_gramas'])) {
            $pdf->Cell(25, 6, number_format($produto['peso_gramas'], 0, ',', '.') . 'g', 1, 0, 'C');
        } else {
            $pdf->Cell(25, 6, '-', 1, 0, 'C');
        }
        
        // Mostrar preço baseado no tipo
        if ($produto['tipo'] == 'kg') {
            $pdf->Cell(45, 6, 'R$ ' . number_format($produto['preco'], 2, ',', '.') . ' (R$ ' . number_format($preco_por_kg, 2, ',', '.') . '/kg)', 1, 0, 'R');
        } else {
            $pdf->Cell(45, 6, 'R$ ' . number_format($produto['preco'], 2, ',', '.') . '', 1, 0, 'R');
        }
        
        // Somar ao valor total
        $valor_total += $produto['preco'];
        $total_produtos++;
        
        $pdf->Ln();
    }
    
    // Linha de total
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(60, 6, 'TOTAL DE PRODUTOS:', 1, 0, 'R');
    $pdf->Cell(30, 6, $total_produtos . ' itens', 1, 0, 'C');
    $pdf->Cell(45, 6, 'R$ ' . number_format($valor_total, 2, ',', '.'), 1, 0, 'R');
    $pdf->Ln();
}

// Rodapé
$pdf->SetY(-20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 0, 'C');

// Gerar PDF
$pdf->Output('relatorio_produtos_' . date('Y-m-d_H-i-s') . '.pdf', 'I');
?>
