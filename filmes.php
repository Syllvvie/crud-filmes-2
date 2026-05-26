<?php
// --- CONFIGURAÇÃO DA CONEXÃO (MySQL) ---
$host = 'localhost';
$db   = 'cinema';
$user = 'root'; // ajuste se seu usuário for diferente
$pass = '';     // ajuste se tiver senha
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// --- LÓGICA DE OPERAÇÕES (CONTROLLER) ---

// 1. ADICIONAR OU ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $id = $_POST['id'];
    $titulo = trim($_POST['titulo']);
    $genero = trim($_POST['genero']);

    if (!empty($id)) {
        // EDITAR
        $sql = "UPDATE filmes SET titulo = ?, genero = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $genero, $id]);
    } else {
        // ADICIONAR
        $sql = "INSERT INTO filmes (titulo, genero) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $genero]);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 2. EXCLUIR
if (isset($_GET['excluir'])) {
    $idExcluir = $_GET['excluir'];
    $sql = "DELETE FROM filmes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idExcluir]);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 3. PREPARAR EDIÇÃO
$editFilme = null;
if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];
    $sql = "SELECT * FROM filmes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idEditar]);
    $editFilme = $stmt->fetch();
}

// 4. LISTAR FILMES
$stmt = $pdo->query("SELECT * FROM filmes ORDER BY id DESC");
$filmes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Filmes - MySQL</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; color: #007bff; }
        form { display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; background: #f8f9fa; padding: 15px; border-radius: 5px; }
        input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; flex: 1; min-width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; transition: 0.3s; display: inline-block; }
        .btn-add { background: #28a745; color: white; }
        .btn-edit { background: #ffc107; color: #212529; }
        .btn-del { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .cancel-link { color: #666; font-size: 14px; align-self: center; }
    </style>
</head>
<body>

<div class="container">
    <h2>🎬 Gerenciador de Filmes (MySQL)</h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $editFilme['id'] ?? '' ?>">
        <input type="text" name="titulo" placeholder="Título do Filme" value="<?= htmlspecialchars($editFilme['titulo'] ?? '') ?>" required>
        <input type="text" name="genero" placeholder="Gênero" value="<?= htmlspecialchars($editFilme['genero'] ?? '') ?>" required>
        
        <button type="submit" name="salvar" class="btn btn-add">
            <?= $editFilme ? 'Atualizar' : 'Adicionar' ?>
        </button>

        <?php if ($editFilme): ?>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="cancel-link">Cancelar</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Gênero</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filmes)): ?>
                <tr><td colspan="4" style="text-align:center;">Nenhum filme cadastrado no banco.</td></tr>
            <?php else: ?>
                <?php foreach ($filmes as $f): ?>
                <tr>
                    <td><strong>#<?= (int)$f['id'] ?></strong></td>
                    <td><?= htmlspecialchars($f['titulo']) ?></td>
                    <td><?= htmlspecialchars($f['genero']) ?></td>
                    <td>
                        <a href="?editar=<?= $f['id'] ?>" class="btn btn-edit">Editar</a>
                        <a href="?excluir=<?= $f['id'] ?>" class="btn btn-del" onclick="return confirm('Excluir este filme?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>