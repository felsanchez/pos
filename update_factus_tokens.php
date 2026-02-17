<?php
/**
 * Script para actualizar tokens de Factus en la base de datos
 * 
 * INSTRUCCIONES:
 * 1. Reemplaza los valores de $accessToken y $refreshToken con los tokens reales
 * 2. Ejecuta este script: php update_factus_tokens.php
 */

require_once "modelos/conexion.php";

// 🔴 REEMPLAZAR ESTOS VALORES CON LOS TOKENS REALES DE POSTMAN
$accessToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJhMGQ4NTZhOC04YjNiLTRmYjMtYWI2Ni1hMTExZTZhOTE3NmMiLCJqdGkiOiI1ZDNiMTJiYjI0MzNlMTI2YThiMDI0YWIwY2E5NjExOWIzOWM1YzBkMzI2NDFjYzZmZmNiOTE2MjczOWMyODViZmM2YzdhMDNhMjczODliMiIsImlhdCI6MTc2OTg1Nzg0OS4xOTI0NzEsIm5iZiI6MTc2OTg1Nzg0OS4xOTI0NzQsImV4cCI6MTc2OTg2MTQ0OS4xODI0NzEsInN1YiI6IjIxNyIsInNjb3BlcyI6W119.peSTkYmuiH5mAgO5PSwhtNcNVHv4qOsM8OI2ygzNhDAAUgC7Xsd3J4uEJw_Vylh5IG2Jjf5Adwlv7WXNknNyqSx1Y246G1RMRkfKwqHknZH8W9jf0nrwbVDhjt_Yl5sSr6G2T15cHJ1Kk7Iw5BxTGy8IoVkm33A-DDiGojM0pXl_WIuHD_HkeL8cswlmMw8Paw4JqsitUW8_OTHnVIhE-q4l7UNh4LJae861GkF8EkDQGNyesM_yUcukOZFj44wAQ0tgoV9srk6Ek7JJHWhL23ffm87HkCPPenwNaO1v59v1E06EH04-UVK9VPb7_lmTodR7laYBch6TENckrtXSRLY8gL80pKecQs-HQDwdadOBqN8bpHMwSyfBPd63Pg2qwQ9EN9el_0oCAaNm2hyUbLbC6hoLs28qI4NqyfelHGhTYpD7W7i5eSLf5Z2ZmCjRNfT_kPVTqoKKnps00YJHIVfmkC1fNKlGiAF4-k1yz2YbN88Z_IdhgMa_aS8MnMWr1rdVsFnEe-RBFWjQ0P-Jwo_5iE7H7OezbrmNpYCjm4elX4VDIX9-zjudpHqke2gxuzgc8YHguM-P9CS8quLd0tvsGwqmXnsr3FYOqe8ki-2CP_9XrbAvUBDuuZhl1tzydbpAg-6TGAOMH2uHG-2WCDuGLrXv-ARrg6Uy0syVVHw";
$refreshToken = "def50200e59972da848fedc42578331ff60d50a47e4934478dfeb095a2eb4c3e55b8f8206c3ccc6e051d14da676f862aaa74def56587fd84a0701d3d9b8f7f63f45281a67dfb3c0b8434637c6caeb3cbf45d88aa4dfc395e6768d24b204e3018d0761e0d9577837d84f289e260cc1cb12d5622f619f5ec20929f00f5bc78d80087b5cab9f6e538c0a41461e06d3a6135d083060cbd93476b6201912d2d66896826bde9916dbb1022d115c241c649531da5097a5eb825928cf9e6b4a8820e0a81d4c1eff6cb1469da6c84f2326007a629275936c5990ffd90c52fe6126ab36d45f02b54c6e164686a4105e60cfd1d1b225620df066a1b77722aa6f0fd7dc5e52dd83dff7e1fe9dc3fe59789e393e48f78db008588c9621c50b1d886ae8ee752bfff622a5ece4364e147bdc07917479348e3e6582272f99faedb45a2cc855bfcad2f7e24667364b70335a636b2075cee8ac7f919c7115ff274577c0690ea02b010bd7101bfa967723883bfd9eefa015c9a1afbedf0737b13290e46903b58953170abbef43cb5ab";

// Calcular fecha de expiración (1 hora desde ahora)
$fechaExpiracion = date('Y-m-d H:i:s', time() + 3600);

try {
    $db = Conexion::conectar();

    $stmt = $db->prepare("
        UPDATE factus_config 
        SET access_token = :access_token,
            refresh_token = :refresh_token,
            token_expiracion = :token_expiracion
        WHERE id = 1
    ");

    $stmt->bindParam(":access_token", $accessToken, PDO::PARAM_STR);
    $stmt->bindParam(":refresh_token", $refreshToken, PDO::PARAM_STR);
    $stmt->bindParam(":token_expiracion", $fechaExpiracion, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "✅ Tokens actualizados exitosamente\n";
        echo "   Access Token: " . substr($accessToken, 0, 30) . "...\n";
        echo "   Refresh Token: " . substr($refreshToken, 0, 30) . "...\n";
        echo "   Expira: $fechaExpiracion\n";
    } else {
        echo "❌ Error al actualizar tokens\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
