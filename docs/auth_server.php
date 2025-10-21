<?php
// Configurações e Cabeçalhos Iniciais
session_start();
header('Content-Type: application/json');

// --- SIMULAÇÃO DE BANCO DE DADOS (USANDO SESSÃO PHP) ---
// Em um sistema real, isso seria um banco de dados persistente.
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

function userExists($username) {
    return isset($_SESSION['users'][$username]);
}

function getUser($username) {
    return $_SESSION['users'][$username] ?? null;
}

function saveUserCredential($username, $credential) {
    $_SESSION['users'][$username] = [
        'id' => bin2hex(random_bytes(16)), // ID FIDO para o usuário
        'name' => $username,
        'credential' => $credential
    ];
}

// Funções Auxiliares para Conversão
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Recebe e decodifica a entrada JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhuma ação especificada.']);
    exit;
}

$action = $data['action'];

try {
    switch ($action) {
        // ==========================================================
        // ETAPA 1: REGISTRO - START (Gera Desafio)
        // ==========================================================
        case 'register_start':
            $username = $data['username'] ?? '';
            if (empty($username)) {
                throw new Exception("Nome de usuário (email) é obrigatório.");
            }
            if (userExists($username)) {
                throw new Exception("O usuário já possui uma Chave de Acesso registrada. Tente fazer login.");
            }

            // Gera um desafio criptográfico único para esta sessão
            $challenge = random_bytes(32); 
            $_SESSION['challenge'] = base64url_encode($challenge);
            $_SESSION['registration_user'] = $username;

            // Retorna as opções para o navegador (Frontend)
            $responseOptions = [
                'status' => 'ok',
                'publicKey' => [
                    'challenge' => $_SESSION['challenge'], // Base64URL-encoded
                    'rp' => [
                        'name' => 'WebAuthn Demo App',
                        'id' => $_SERVER['HTTP_HOST'] // Domínio do servidor
                    ],
                    'user' => [
                        'id' => base64url_encode(random_bytes(16)), // ID FIDO2 único (Base64URL)
                        'name' => $username,
                        'displayName' => $username
                    ],
                    'pubKeyCredParams' => [
                        ['type' => 'public-key', 'alg' => -7], // ES256
                        ['type' => 'public-key', 'alg' => -257] // RS256
                    ],
                    'timeout' => 60000,
                    'attestation' => 'none'
                ]
            ];
            echo json_encode($responseOptions);
            break;

        // ==========================================================
        // ETAPA 2: REGISTRO - FINISH (Recebe Credencial)
        // ==========================================================
        case 'register_finish':
            $username = $data['username'] ?? '';
            $attestation = $data['attestation'] ?? null;
            $savedChallenge = $_SESSION['challenge'] ?? null;
            $expectedUser = $_SESSION['registration_user'] ?? null;
            
            if (!$attestation || !$savedChallenge || $username !== $expectedUser) {
                 throw new Exception("Dados de sessão ou attestation inválidos/incompletos.");
            }
            
            // 1. **VERIFICAÇÃO DE DESAFIO (SIMPLIFICADA)**: 
            // Em produção, você precisa decodificar e verificar o clientDataJSON.
            // Para esta demo, assumimos que o challenge e o origin foram validados.
            $clientDataJSON = base64url_decode($attestation['response']['clientDataJSON']);
            if (strpos($clientDataJSON, $savedChallenge) === false) {
                 throw new Exception("Falha na validação do desafio criptográfico (Challenge mismatch).");
            }

            // 2. **ARMAZENAMENTO (SIMULAÇÃO)**:
            // Em um sistema real, você extrairia a Chave Pública e o ID do Credential
            // do attestationObject e armazenaria de forma persistente.
            $credentialToSave = [
                'id' => $attestation['rawId'],
                'publicKey' => 'PUBLIC_KEY_SIMULADA', // A chave pública real seria extraída aqui
                'counter' => 0 // Contador de assinatura
            ];
            saveUserCredential($username, $credentialToSave);

            unset($_SESSION['challenge']);
            unset($_SESSION['registration_user']);
            
            echo json_encode(['status' => 'ok', 'message' => 'Credencial salva com sucesso!']);
            break;

        // ==========================================================
        // ETAPA 3: AUTENTICAÇÃO - START (Gera Novo Desafio)
        // ==========================================================
        case 'login_start':
            $username = $data['username'] ?? '';
            $user = getUser($username);

            if (!$user) {
                throw new Exception("Usuário não encontrado. Por favor, registre uma Chave de Acesso primeiro.");
            }

            $challenge = random_bytes(32); 
            $_SESSION['challenge'] = base64url_encode($challenge);
            $_SESSION['login_user'] = $username;

            // Retorna as opções de login
            $responseOptions = [
                'status' => 'ok',
                'publicKey' => [
                    'challenge' => $_SESSION['challenge'],
                    'rpId' => $_SERVER['HTTP_HOST'],
                    'timeout' => 60000,
                    'userVerification' => 'required',
                    'allowCredentials' => [
                        [
                            'type' => 'public-key',
                            // Aqui o ID da credencial registrada é retornado para o navegador
                            'id' => $user['credential']['id'] 
                        ]
                    ]
                ]
            ];
            echo json_encode($responseOptions);
            break;

        // ==========================================================
        // ETAPA 4: AUTENTICAÇÃO - FINISH (Verifica Asserção)
        // ==========================================================
        case 'login_finish':
            $username = $data['username'] ?? '';
            $assertion = $data['assertion'] ?? null;
            $savedChallenge = $_SESSION['challenge'] ?? null;
            $expectedUser = $_SESSION['login_user'] ?? null;
            $user = getUser($username);

            if (!$assertion || !$savedChallenge || $username !== $expectedUser || !$user) {
                throw new Exception("Dados de sessão ou asserção inválidos/incompletos.");
            }

            // 1. **VERIFICAÇÃO DE DESAFIO (SIMPLIFICADA)**:
            $clientDataJSON = base64url_decode($assertion['response']['clientDataJSON']);
            if (strpos($clientDataJSON, $savedChallenge) === false) {
                 throw new Exception("Falha na validação do desafio criptográfico (Challenge mismatch).");
            }
            
            // 2. **VERIFICAÇÃO DE ASSINATURA (SIMULAÇÃO)**:
            // ESTA É A ETAPA MAIS CRÍTICA EM PRODUÇÃO.
            // Aqui, a assinatura no 'response.signature' e o 'authenticatorData'
            // são validados usando a 'publicKey' armazenada. Esta lógica é complexa
            // e deve ser delegada a uma biblioteca WebAuthn PHP de verdade.
            // Para a demo, assumimos que a assinatura é válida.

            // 3. **VERIFICAÇÃO DE CONTADOR (SIMULAÇÃO)**:
            // Verifica o contador para prevenir ataques de replay.
            // if (extrairContador($assertion['response']['authenticatorData']) <= $user['credential']['counter']) { ... }

            // Se todas as verificações (Challenge, Assinatura, Contador) passarem:
            unset($_SESSION['challenge']);
            unset($_SESSION['login_user']);

            echo json_encode(['status' => 'ok', 'message' => 'Autenticação bem-sucedida!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Ação desconhecida.']);
            break;
    }

} catch (Exception $e) {
    // Retorna erro amigável ao frontend
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>