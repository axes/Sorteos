<?php

class ApiKey
{
    public static function validarApiKey($api_key)
    {
        global $pdo;
        $sql = "SELECT * FROM api_keys WHERE api_key = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$api_key]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
