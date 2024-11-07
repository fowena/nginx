<?php

// Defina o domínio permitido (altere para o domínio do seu frontend)
$dominioPermitido = 'https://meu-frontend.up.railway.app';  // Substitua pelo seu domínio real

// Verifique se o cabeçalho "Origin" está presente na requisição
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origem = $_SERVER['HTTP_ORIGIN'];

    // Verifique se a origem da requisição é o domínio permitido
    if ($origem === $dominioPermitido) {
        // Permite a requisição apenas se a origem for permitida
        header('Access-Control-Allow-Origin: ' . $dominioPermitido);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    } else {
        // Se o domínio não for permitido, retorne um erro
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['message' => 'Acesso negado: Domínio não permitido']);
        exit;
    }
}

// Função para validar CPF
function validaCPF($strCPF)
{
    $soma = 0;
    $resto = 0;
    $strCPF = preg_replace('/\D/', '', $strCPF); // Remove todos os caracteres não numéricos

    if (strlen($strCPF) != 11 || $strCPF == "00000000000") {
        return false;
    }

    for ($i = 1; $i <= 9; $i++) {
        $soma += (int)$strCPF[$i - 1] * (11 - $i);
    }

    $resto = ($soma * 10) % 11;
    if ($resto == 10 || $resto == 11) $resto = 0;
    if ($resto != (int)$strCPF[9]) return false;

    $soma = 0;
    for ($i = 1; $i <= 10; $i++) {
        $soma += (int)$strCPF[$i - 1] * (12 - $i);
    }

    $resto = ($soma * 10) % 11;
    if ($resto == 10 || $resto == 11) $resto = 0;
    return $resto == (int)$strCPF[10];
}

// Função para fazer a requisição à API externa
function buscarDadosCPF($cpf)
{
    $url = "https://typebots.dgsistemas.xyz:8080/api/typebot?cpf=" . $cpf;

    // Inicializa a requisição cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Verifica o método da requisição (GET)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['cpf'])) {
    $cpf = $_GET['cpf'];

    // Valida o CPF
    if (validaCPF($cpf)) {
        // Busca os dados do CPF
        $dadosCPF = buscarDadosCPF($cpf);

        if ($dadosCPF && isset($dadosCPF['CPF'])) {
            echo json_encode($dadosCPF);
        } else {
            echo json_encode(['message' => 'Dados do CPF não encontrados!']);
        }
    } else {
        echo json_encode(['message' => 'CPF inválido!']);
    }
} else {
    echo json_encode(['message' => 'Requisição inválida!']);
}
