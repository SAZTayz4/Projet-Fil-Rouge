<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');



// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'checkmykicks');

// Connexion à la base de données
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']));
    }
    return $conn;
}

// Gestion des requêtes
function handleRequest() {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api/', '', $path);

    switch ($path) {
        case 'blog/articles':
            if ($method === 'GET') {
                getBlogArticles();
            }
            break;
        case 'auth/login':
            if ($method === 'POST') {
                handleLogin();
            }
            break;
        case 'auth/register':
            if ($method === 'POST') {
                handleRegister();
            }
            break;
        case 'user/profile':
            if ($method === 'GET') {
                handleGetProfile();
            }
            break;
        case 'drops':
            if ($method === 'GET') {
                handleGetDrops();
            }
            break;
        case 'spotcheck/analyze':
            if ($method === 'POST') {
                handleSpotCheck();
            }
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint non trouvé']);
    }
}

// Récupération des articles du blog
function getBlogArticles() {
    $conn = getDBConnection();
    $query = "SELECT * FROM articles ORDER BY date_publication DESC";
    $result = $conn->query($query);
    
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    
    echo json_encode(['success' => true, 'articles' => $articles]);
}

// Gestion de la connexion
function handleLogin() {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, md5($password));
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
}

// Gestion de l'inscription
function handleRegister() {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, md5($password));

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
    }
}

// Récupération du profil utilisateur
function handleGetProfile() {
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        return;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT name, email, date_inscription FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    }
}

// Récupération des drops
function handleGetDrops() {
    $conn = getDBConnection();
    $query = "SELECT * FROM drops ORDER BY date_sortie ASC";
    $result = $conn->query($query);
    
    $drops = [];
    while ($row = $result->fetch_assoc()) {
        $drops[] = $row;
    }
    
    echo json_encode(['success' => true, 'drops' => $drops]);
}

// Analyse SpotCheck
function handleSpotCheck() {
    if (!isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'message' => 'Aucune image fournie']);
        return;
    }

    $image = $_FILES['image'];
    $uploadDir = '../uploads/';
    $fileName = uniqid() . '_' . $image['name'];
    $uploadPath = $uploadDir . $fileName;

    if (!move_uploaded_file($image['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement de l\'image']);
        return;
    }

    // Ici, vous pouvez ajouter la logique d'analyse d'image
    // Pour l'instant, on simule une réponse
    echo json_encode([
        'success' => true,
        'result' => [
            'authenticity' => 'Authentique',
            'confidence' => '95%',
            'details' => [
                'Logo correctement positionné',
                'Coutures conformes',
                'Matériaux authentiques'
            ]
        ]
    ]);
}

// Exécution de la gestion des requêtes
handleRequest(); 