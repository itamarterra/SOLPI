<?php

declare(strict_types=1);

/**
 * SOLPI - Janela de Importação Profissional
 * Versão 3.0 Pro - Estável & Segura
 */

// 1. Localização da Raiz do GLPI
// Se rodar via public/solpi-import.php, a raiz é o pai.
$root = dirname(__DIR__);
if (!file_exists($root . '/inc/includes.php')) {
    $root = '/var/www/glpi';
}

require_once $root . '/vendor/autoload.php';

// Inicializa o Kernel do GLPI 10 para definir constantes e ambiente
if (class_exists('Glpi\\Kernel\\Kernel')) {
    $glpi_kernel = new \Glpi\Kernel\Kernel();
    $glpi_kernel->boot();
    if (method_exists('Config', 'loadLegacyConfiguration')) {
        Config::loadLegacyConfiguration();
    }
}

require_once $root . '/inc/includes.php';

// 2. Carrega dependências do plugin
$loader = $root . '/plugins/solpi/vendor/autoload.php';
if (file_exists($loader)) {
    require_once $loader;
}

// 3. Verifica Sessão de forma segura
if (Session::getLoginUserID() === false) {
    // Se não estiver logado, redireciona ou avisa de forma amigável via GLPI
    Html::header("Acesso Negado", $_SERVER['PHP_SELF']);
    echo "<div class='container text-center mt-5'><div class='alert alert-warning'><h3>Sessão não encontrada.</h3><p>Por favor, faça login no GLPI e tente novamente.</p></div></div>";
    Html::footer();
    exit;
}

use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;

$uploadDir = $root . "/files/_tmp/solpi_import/";
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

// --- Funções Auxiliares ---
function solpi_is_safe_url($url) {
    $parts = parse_url($url);
    if (!$parts || !isset($parts['host'])) return false;
    $ip = gethostbyname($parts['host']);
    // Bloqueia localhost, IPs privados e reservados
    return !preg_match('/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|169\.254\.)/', $ip);
}

function solpi_fetch($url) {
    if (!solpi_is_safe_url($url)) return "Erro: URL não permitida por motivos de segurança.";
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>10, CURLOPT_MAXREDIRS=>3]);
    $r = curl_exec($ch); curl_close($ch);
    return $r;
}

function solpi_parse($txt) {
    $txt = trim($txt); if(!$txt) return [];
    if (str_starts_with($txt, 'Erro:')) return []; // Trata falha de fetch
    if (stripos($txt, '<table') !== false) {
        $txt = preg_replace('/<\/tr>/i', "\n", $txt);
        $txt = str_replace(['</td>','</th>'], "\t", strip_tags($txt, '<td><th>'));
        $txt = strip_tags($txt);
    }
    $lines = array_values(array_filter(explode("\n", str_replace("\r", "", $txt))));
    if(!$lines) return [];
    $sep = str_contains($lines[0], "\t") ? "\t" : (str_contains($lines[0], ";") ? ";" : ",");
    $head = array_map('trim', str_getcsv(array_shift($lines), $sep));
    $res = [];
    foreach($lines as $l) {
        $data = str_getcsv($l, $sep); $row = [];
        foreach($head as $i=>$h) { $row[$h] = trim((string)($data[$i]??'')); }
        if(array_filter($row)) $res[] = $row;
    }
    return $res;
}

$step = $_GET['step'] ?? 'upload';
$msg = ''; $rows = []; $headers = []; $mapping = []; $tmpFile = '';

// AÇÃO: IMPORTAR
if ($step === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mapping = array_flip(array_filter($_POST['mapping'] ?? []));
    $targetFile = $_POST['tmp_file'] ?? '';
    // Proteção contra Path Traversal
    if ($targetFile && str_starts_with($targetFile, $uploadDir) && file_exists($targetFile)) {
        $rows = (str_contains($targetFile, 'xl')) ? (new ExcelParser())->parse($targetFile) : solpi_parse(file_get_contents($targetFile));
        $count = 0;
        foreach ($rows as $r) {
            $desc = $r[$mapping['problema'] ?? ''] ?? '';
            if (!$desc) continue;
            $ticket = new Ticket();
            if ($ticket->add([
                'name'                => mb_strimwidth($desc, 0, 70, '...'),
                'content'             => $desc . "\n\n(Importado via SOLPI)",
                'entities_id'         => $_SESSION['glpiactive_entity'] ?? 0,
                'requesttypes_id'     => 1,
                '_users_id_requester' => Session::getLoginUserID()
            ])) $count++;
        }
        @unlink($targetFile);
        $msg = "Sucesso! $count chamados criados."; $step = 'done';
    } else {
        $msg = "Erro: Arquivo temporário inválido ou expirado."; $step = 'upload';
    }
}

// AÇÃO: ANALISAR
if ($step === 'preview') {
    $paste = $_POST['paste_data'] ?? '';
    $file = $_FILES['source_file'] ?? null;
    try {
        if ($paste) {
            if (filter_var($paste, FILTER_VALIDATE_URL)) {
                $paste = solpi_fetch($paste);
                if (str_starts_with($paste, 'Erro:')) throw new Exception($paste);
            }
            $tmpFile = $uploadDir . uniqid('sp_') . '.txt';
            file_put_contents($tmpFile, $paste);
            $rows = solpi_parse($paste);
        } elseif ($file && $file['error'] === 0) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['csv', 'xls', 'xlsx', 'txt', 'tsv'])) {
                throw new Exception("Tipo de arquivo não permitido.");
            }
            $tmpFile = $uploadDir . uniqid('sp_') . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $tmpFile);
            $rows = (str_contains($tmpFile, 'xl')) ? (new ExcelParser())->parse($tmpFile) : solpi_parse(file_get_contents($tmpFile));
        }
        if ($rows) {
            $headers = array_keys($rows[0]);
            $mapping = (new ColumnDetector())->detect($headers);
        } else { $msg = "Nenhum dado encontrado."; $step = "upload"; }
    } catch (Throwable $e) { $msg = "Erro: " . $e->getMessage(); $step = "upload"; }
}

Html::header("SOLPI Import", $_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --solpi-primary: #4f46e5;
        --solpi-primary-hover: #4338ca;
        --solpi-bg: #f9fafb;
        --solpi-card-bg: #ffffff;
        --solpi-text: #111827;
        --solpi-text-muted: #6b7280;
        --solpi-border: #e5e7eb;
        --solpi-input-bg: #ffffff;
        --solpi-card-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --solpi-card-shadow-hover: 0 25px 50px -12px rgb(0 0 0 / 0.08);
        --solpi-radius: 1rem;
    }
    [data-theme="dark"] {
        --solpi-bg: #030712;
        --solpi-card-bg: #111827;
        --solpi-text: #f9fafb;
        --solpi-text-muted: #9ca3af;
        --solpi-border: #1f2937;
        --solpi-input-bg: #030712;
        --solpi-card-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.3);
        --solpi-card-shadow-hover: 0 25px 50px -12px rgb(0 0 0 / 0.5);
    }
    body { background-color: var(--solpi-bg) !important; color: var(--solpi-text) !important; font-family: 'Inter', system-ui, -apple-system, sans-serif; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .solpi-container { max-width: 1100px; margin: 3rem auto; padding: 0 1.5rem; }
    
    .card { background-color: var(--solpi-card-bg) !important; color: var(--solpi-text) !important; border-radius: var(--solpi-radius) !important; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid var(--solpi-border) !important; }
    .card-shadow { box-shadow: var(--solpi-card-shadow); }
    .card-shadow:hover { box-shadow: var(--solpi-card-shadow-hover); transform: translateY(-4px); }
    
    .btn-primary { background: linear-gradient(135deg, var(--solpi-primary), #6366f1) !important; border: none !important; border-radius: var(--solpi-radius) !important; padding: 0.8rem 2rem !important; font-weight: 600 !important; transition: all 0.3s; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2) !important; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3) !important; opacity: 0.95; }
    
    .upload-area { border: 2px dashed var(--solpi-border) !important; border-radius: var(--solpi-radius) !important; padding: 4rem 2rem !important; background: var(--solpi-input-bg) !important; transition: all 0.3s; cursor: pointer; position: relative; overflow: hidden; }
    .upload-area:hover { border-color: var(--solpi-primary) !important; background: rgba(79, 70, 229, 0.02) !important; }
    .upload-area::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(45deg, transparent, rgba(79, 70, 229, 0.05), transparent); transform: translateX(-100%); transition: 0.6s; }
    .upload-area:hover::after { transform: translateX(100%); }
    
    .form-control, .form-select { background-color: var(--solpi-input-bg) !important; color: var(--solpi-text) !important; border-radius: 0.75rem !important; padding: 0.75rem 1rem !important; border: 1px solid var(--solpi-border) !important; transition: all 0.2s; }
    .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important; border-color: var(--solpi-primary) !important; outline: none; }
    
    /* Stepper */
    .solpi-stepper { display: flex; justify-content: space-between; margin-bottom: 3.5rem; position: relative; }
    .solpi-stepper::before { content: ''; position: absolute; top: 1.25rem; left: 0; width: 100%; height: 2px; background: var(--solpi-border); z-index: 1; }
    .step-item { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; width: 33.33%; }
    .step-dot { width: 2.5rem; height: 2.5rem; background: var(--solpi-card-bg); border: 2px solid var(--solpi-border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 0.75rem; transition: all 0.3s; color: var(--solpi-text-muted); }
    .step-item.active .step-dot { border-color: var(--solpi-primary); background: var(--solpi-primary); color: white; box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.15); }
    .step-item.completed .step-dot { border-color: #10b981; background: #10b981; color: white; }
    .step-label { font-size: 0.75rem; font-weight: 700; color: var(--solpi-text-muted); text-transform: uppercase; letter-spacing: 0.08em; }
    .step-item.active .step-label { color: var(--solpi-primary); }
    
    .table thead th { background-color: rgba(100, 116, 139, 0.05) !important; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.1em; color: var(--solpi-text-muted); padding: 1rem !important; border-bottom: 1px solid var(--solpi-border) !important; }
    .table td { padding: 1rem !important; border-bottom: 1px solid var(--solpi-border) !important; font-size: 0.85rem; }
    
    .theme-toggle { cursor: pointer; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all 0.3s; background: var(--solpi-input-bg); border: 1px solid var(--solpi-border); color: var(--solpi-text); }
    .theme-toggle:hover { background: var(--solpi-border); transform: rotate(12deg); }
    
    #loader-box { backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.8) !important; }
    .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
</style>

<div class="solpi-container">
    <header class="d-flex align-items-center justify-content-between mb-5">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3" style="box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.2);">
                <i class="bi bi-layers-half fs-2 text-primary"></i>
            </div>
            <div>
                <h2 class="fw-black mb-0" style="letter-spacing: -0.04em; color: var(--solpi-text);">SOLPI <span class="text-primary">IMPORT</span></h2>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 me-2" style="font-size: 0.65rem;">v5.0 PRO</span>
                    <p class="small mb-0" style="color: var(--solpi-text-muted); font-weight: 500;">Sistema de Processamento Inteligente</p>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <button id="theme-toggle" class="theme-toggle" title="Alternar modo visual">
                <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
            </button>
        </div>
    </header>

    <div class="solpi-stepper">
        <div class="step-item <?=($step==='upload'?'active':($step==='preview'||$step==='done'?'completed':''))?>">
            <div class="step-dot"><?=($step==='preview'||$step==='done'?'<i class="bi bi-check"></i>':'1')?></div>
            <div class="step-label">Upload</div>
        </div>
        <div class="step-item <?=($step==='preview'?'active':($step==='done'?'completed':''))?>">
            <div class="step-dot"><?=($step==='done'?'<i class="bi bi-check"></i>':'2')?></div>
            <div class="step-label">Mapeamento</div>
        </div>
        <div class="step-item <?=($step==='done'?'active':'')?>">
            <div class="step-dot">3</div>
            <div class="step-label">Concluído</div>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert border-0 card-shadow mb-5 d-flex align-items-center py-4 px-4" style="border-left: 5px solid #10b981 !important; background: var(--solpi-card-bg); color: var(--solpi-text);">
            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                <i class="bi bi-check2-circle fs-4 text-success"></i> 
            </div>
            <span class="fw-semibold"><?=$msg?></span>
        </div>
    <?php endif; ?>

    <?php if ($step === 'done'): ?>
        <div class="card p-5 text-center card-shadow border-0" style="background: linear-gradient(to bottom right, var(--solpi-card-bg), rgba(16, 185, 129, 0.02)) !important;">
            <div class="mb-4">
                <div class="bg-success bg-opacity-10 p-4 rounded-circle d-inline-block shadow-sm">
                    <i class="bi bi-rocket-takeoff text-success" style="font-size: 3.5rem;"></i>
                </div>
            </div>
            <h2 class="fw-black mb-2" style="letter-spacing: -0.02em;">IMPORTAÇÃO CONCLUÍDA</h2>
            <p style="color: var(--solpi-text-muted); font-size: 1.1rem;" class="mb-5">Os dados foram processados com sucesso e os chamados já estão disponíveis no GLPI.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/solpi-import.php" class="btn btn-primary px-5 py-3 shadow">
                    <i class="bi bi-plus-lg me-2"></i>Nova Importação
                </a>
                <a href="/front/ticket.php" class="btn btn-outline-secondary px-5 py-3 border-2 fw-bold" style="border-radius: var(--solpi-radius);">
                    <i class="bi bi-list-task me-2"></i>Ver Chamados
                </a>
            </div>
        </div>

    <?php elseif ($step === 'preview' && $rows): ?>
        <form method="post" action="/solpi-import.php?step=import" onsubmit="document.getElementById('loader-box').style.display='flex'">
            <input type="hidden" name="tmp_file" value="<?=$tmpFile?>">
            <input type="hidden" name="source_name" value="<?=$_POST['source_name'] ?? 'Planilha'?>">
            
            <div class="card card-shadow mb-4 overflow-hidden border-0">
                <div class="card-header bg-transparent border-0 p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-bezier2 me-2 text-primary"></i> Configuração de Mapeamento</h5>
                        <p class="text-muted small mb-0">Relacione as colunas detectadas com os campos oficiais do GLPI.</p>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-lightning-fill me-2"></i> Executar Processamento
                    </button>
                </div>
                
                <div class="p-4 bg-light bg-opacity-50" style="border-top: 1px solid var(--solpi-border); border-bottom: 1px solid var(--solpi-border);">
                    <div class="row g-4">
                        <?php foreach ($headers as $h): ?>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border card-shadow-hover transition-all" style="background: var(--solpi-input-bg) !important;">
                                    <label class="small fw-bold mb-2 d-flex align-items-center" style="color: var(--solpi-text-muted);">
                                        <i class="bi bi-table me-2"></i> <?=$h?>
                                    </label>
                                    <select name="mapping[<?=$h?>]" class="form-select border-0 shadow-sm" style="background-color: var(--solpi-bg) !important;">
                                        <option value="">(Ignorar coluna)</option>
                                        <option value="problema" <?=($mapping[$h]??'')==='problema'?'selected':''?>>Descrição do Problema</option>
                                        <option value="nome" <?=($mapping[$h]??'')==='nome'?'selected':''?>>Solicitante</option>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="p-4">
                    <div class="d-flex align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-eye me-2 text-primary"></i> Prévia dos Dados</h6>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-2"><?=count($rows)?> registros encontrados</span>
                    </div>
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><?php foreach($headers as $h) echo "<th>$h</th>"; ?></tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($rows,0,5) as $r): ?>
                                    <tr>
                                        <?php foreach($headers as $h) {
                                            $val = (string)($r[$h]??'');
                                            echo "<td>".mb_strimwidth($val,0,60,'...')."</td>";
                                        } ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>

    <?php else: ?>
        <div class="row g-4">
            <div class="col-md-7">
                <div class="card p-5 h-100 card-shadow text-center border-0">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-arrow-up text-primary" style="font-size: 4rem; opacity: 0.8;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Importar Arquivo</h4>
                    <p style="color: var(--solpi-text-muted);" class="mb-5">Suporte nativo para Excel (XLSX, XLS) e arquivos CSV.</p>
                    
                    <div class="upload-area mb-4 shadow-sm" onclick="document.getElementById('fIn').click()">
                        <div class="bg-primary bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                            <i class="bi bi-cloud-upload-fill text-primary fs-2"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Arraste ou Clique</h5>
                        <p style="color: var(--solpi-text-muted);" class="small mb-0">Selecione o arquivo para análise imediata</p>
                    </div>
                    
                    <form method="post" action="/solpi-import.php?step=preview" enctype="multipart/form-data" id="fForm">
                        <input type="file" name="source_file" id="fIn" class="d-none" onchange="document.getElementById('fForm').submit()">
                    </form>
                    
                    <div class="d-flex justify-content-center gap-4 py-3 border-top mt-2" style="border-color: var(--solpi-border) !important;">
                        <div class="text-center">
                            <i class="bi bi-filetype-exe fs-4 text-muted mb-1 d-block"></i>
                            <span class="small fw-bold text-muted">EXCEL</span>
                        </div>
                        <div class="text-center">
                            <i class="bi bi-filetype-csv fs-4 text-muted mb-1 d-block"></i>
                            <span class="small fw-bold text-muted">CSV</span>
                        </div>
                        <div class="text-center">
                            <i class="bi bi-table fs-4 text-muted mb-1 d-block"></i>
                            <span class="small fw-bold text-muted">TSV</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card p-5 h-100 card-shadow border-0" style="background: linear-gradient(135deg, var(--solpi-card-bg), rgba(37, 99, 235, 0.02)) !important;">
                    <h4 class="fw-bold mb-3"><i class="bi bi-clipboard-data me-2 text-primary"></i>Entrada Direta</h4>
                    <p style="color: var(--solpi-text-muted);" class="small mb-4">Cole dados tabulares ou uma URL de planilha pública.</p>
                    
                    <form method="post" action="/solpi-import.php?step=preview" class="h-100 d-flex flex-column">
                        <textarea name="paste_data" class="form-control mb-4 flex-grow-1 shadow-sm" 
                                  style="min-height: 200px; resize: none; background-color: var(--solpi-input-bg) !important; border: 1px solid var(--solpi-border) !important;" 
                                  placeholder="Cole as colunas aqui ou a URL do arquivo..."></textarea>
                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-lg">
                            <i class="bi bi-cpu-fill me-2"></i>Analisar com IA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="loader-box" style="position: fixed; top:0; left:0; width:100%; height:100%; z-index:9999; display:none; align-items:center; justify-content:center; flex-direction: column;">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
    <h5 class="mt-4 fw-bold">Processando seus dados...</h5>
    <p class="text-muted">Isso pode levar alguns segundos.</p>
</div>

<script>
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const html = document.documentElement;

    // Função para definir o tema
    const setTheme = (theme) => {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('solpi-theme', theme);
        if (theme === 'dark') {
            themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
        } else {
            themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
        }
    };

    // Inicialização
    const savedTheme = localStorage.getItem('solpi-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    setTheme(savedTheme);

    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });
</script>

</body>
</html>
<?php Html::footer(); ?>
