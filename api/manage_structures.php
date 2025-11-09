<?php
/**
 * manage_structures.php
 * API backend pour gérer les structures EVE Online.
 * Fichier JSON : ../data/structures.json
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$DATA_FILE = __DIR__ . "/../data/structures.json";

/**
 * 🔒 Vérifie que le JSON est valide et retourne sa structure
 */
function load_json($file) {
    if (!file_exists($file)) {
        return ["structures" => []];
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (!is_array($data)) $data = ["structures" => []];
    if (!isset($data["structures"])) $data["structures"] = [];
    return $data;
}

/**
 * 💾 Sauvegarde propre du JSON
 */
function save_json($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json);
}

// Gestion des méthodes HTTP
$method = $_SERVER["REQUEST_METHOD"];
if ($method === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($method === "GET") {
    $data = load_json($DATA_FILE);
    echo json_encode($data);
    exit;
}

if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || !isset($input["action"])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Requête invalide"]);
        exit;
    }

    $action = $input["action"];
    $data = load_json($DATA_FILE);
    $structures = &$data["structures"];

    // 🔹 Action : ajout ou remplacement
    if ($action === "replace_or_add" && isset($input["data"])) {
        $new = $input["data"];

        $sys = strtolower(trim($new["Nom du système"] ?? ""));
        $name = strtolower(trim($new["Nom de la structure"] ?? ""));

        if (!$sys || !$name) {
            echo json_encode(["success" => false, "error" => "Nom du système ou structure manquant."]);
            exit;
        }

        // Vérifie si la structure existe déjà (même nom + même système)
        $foundIndex = -1;
        foreach ($structures as $i => $s) {
            if (
                strtolower(trim($s["Nom du système"] ?? "")) === $sys &&
                strtolower(trim($s["Nom de la structure"] ?? "")) === $name
            ) {
                $foundIndex = $i;
                break;
            }
        }

        // Si la structure existe, on la remplace
        if ($foundIndex >= 0) {
            $structures[$foundIndex] = $new;
            save_json($DATA_FILE, $data);
            echo json_encode(["success" => true, "replaced" => true]);
            exit;
        }

        // Sinon, on ajoute simplement
        $structures[] = $new;
        save_json($DATA_FILE, $data);
        echo json_encode(["success" => true, "added" => true]);
        exit;
    }

    // 🔹 Action : suppression d’une structure spécifique
    if ($action === "delete" && isset($input["system"]) && isset($input["name"])) {
        $sys = strtolower(trim($input["system"]));
        $name = strtolower(trim($input["name"]));

        $countBefore = count($structures);
        $structures = array_values(array_filter($structures, function ($s) use ($sys, $name) {
            return !(
                strtolower(trim($s["Nom du système"] ?? "")) === $sys &&
                strtolower(trim($s["Nom de la structure"] ?? "")) === $name
            );
        }));

        if (count($structures) < $countBefore) {
            save_json($DATA_FILE, $data);
            echo json_encode(["success" => true, "deleted" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Structure non trouvée"]);
        }
        exit;
    }

    // 🔹 Action inconnue
    echo json_encode(["success" => false, "error" => "Action inconnue"]);
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "error" => "Méthode non autorisée"]);
exit;
