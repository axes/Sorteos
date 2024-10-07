<?php
require_once __DIR__ . '/../../config/database.php';

class Sorteo
{
    public static function crear($nombre)
    {
        global $pdo;
        try {
            $sql = "INSERT INTO sorteos (nombre) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre]);
            return $pdo->lastInsertId(); // Devolver el ID del sorteo creado
        } catch (PDOException $e) {
            echo "Error al crear el sorteo: " . $e->getMessage();
            return false;
        }
    }

    public static function obtenerPorId($sorteo_id)
    {
        global $pdo;
        $sql = "SELECT * FROM sorteos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerTodos()
    {
        global $pdo;
        $sql = "SELECT id, nombre FROM sorteos";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existe($nombre)
    {
        global $pdo;
        $sql = "SELECT id FROM sorteos WHERE nombre = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre]);
        return $stmt->fetch();
    }

    public static function realizar($sorteo_id, $tipo_sorteo)
    {
        global $pdo;

        // Obtener participantes que no hayan sido ganadores (ganador = 0) ni "al agua" (al_agua = 0) ni anulados (ganador != -1) ni baneados (baneado = 0)
        $sql = "SELECT p.* FROM participantes p
                INNER JOIN participacion pa ON p.id = pa.participante_id
                WHERE pa.sorteo_id = ? AND pa.ganador = 0 AND pa.al_agua = 0 AND p.baneado = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($participantes) == 0) {
            throw new Exception("No hay participantes disponibles para el sorteo.");
        }

        shuffle($participantes); // Mezclar los participantes

        // Inicializar arreglo de resultados
        $resultados = [];

        switch ($tipo_sorteo) {
            case '1 ganador':
                $resultados['ganadores'] = array_slice($participantes, 0, 1);
                break;

            case '2 ganadores':
                $resultados['ganadores'] = array_slice($participantes, 0, 2);
                break;

            case '3 ganadores':
                $resultados['ganadores'] = array_slice($participantes, 0, 3);
                break;

            case '2 al agua, 1 ganador':
                if (count($participantes) < 3) {
                    throw new Exception("No hay suficientes participantes para este tipo de sorteo.");
                }
                // Seleccionar dos "al agua" y un ganador
                $resultados['al_agua'] = array_slice($participantes, 0, 2);
                $resultados['ganadores'] = array_slice($participantes, 2, 1);
                break;

            default:
                throw new Exception("Tipo de sorteo no válido.");
        }

        return $resultados;
    }

    public static function obtenerGanadores($sorteo_id)
    {
        global $pdo;
        $sql = "SELECT p.*, pa.lugar FROM participantes p
                INNER JOIN participacion pa ON p.id = pa.participante_id
                WHERE pa.sorteo_id = ? AND pa.ganador = 1
                ORDER BY pa.lugar ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerGanadoresAnulados($sorteo_id)
    {
        global $pdo;
        $sql = "SELECT p.*, pa.lugar FROM participantes p
            INNER JOIN participacion pa ON p.id = pa.participante_id
            WHERE pa.sorteo_id = ? AND pa.ganador = -1
            ORDER BY pa.lugar ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerAlAgua($sorteo_id)
    {
        global $pdo;
        $sql = "SELECT p.* FROM participantes p
                INNER JOIN participacion pa ON p.id = pa.participante_id
                WHERE pa.sorteo_id = ? AND pa.al_agua = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function registrarGanadores($sorteo_id, $resultados)
    {
        global $pdo;

        // Registrar participantes "al agua"
        if (isset($resultados['al_agua'])) {
            foreach ($resultados['al_agua'] as $al_agua) {
                $participante_id = $al_agua['id'];
                // Actualizar la participación para marcar al participante como "al agua"
                $sql = "UPDATE participacion SET al_agua = 1 WHERE sorteo_id = ? AND participante_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$sorteo_id, $participante_id]);
            }
        }

        // Registrar ganadores
        $lugar = 1;
        foreach ($resultados['ganadores'] as $ganador) {
            $participante_id = $ganador['id'];
            // Actualizar la participación para marcar al participante como ganador y asignar lugar
            $sql = "UPDATE participacion SET ganador = 1, lugar = ? WHERE sorteo_id = ? AND participante_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$lugar, $sorteo_id, $participante_id]);
            $lugar++;
        }
    }

    public static function anularGanador($sorteo_id, $participante_id)
    {
        global $pdo;
        $sql = "UPDATE participacion SET ganador = -1 WHERE sorteo_id = ? AND participante_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id, $participante_id]);
    }


    public static function reemplazarGanador($sorteo_id, $participante_id_anulado)
    {
        global $pdo;
        // Obtener el lugar del ganador anulado
        $sql = "SELECT lugar FROM participacion WHERE sorteo_id = ? AND participante_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id, $participante_id_anulado]);
        $lugarAnulado = $stmt->fetchColumn();

        if (!$lugarAnulado) {
            throw new Exception("No se pudo obtener el lugar del ganador anulado.");
        }

        // Obtener participantes disponibles para reemplazar
        $sql = "SELECT p.* FROM participantes p
                INNER JOIN participacion pa ON p.id = pa.participante_id
                WHERE pa.sorteo_id = ? AND pa.ganador = 0 AND pa.al_agua = 0 and p.baneado = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($participantes) == 0) {
            throw new Exception("No hay más participantes disponibles para reemplazar.");
        }

        // Seleccionar un nuevo ganador al azar
        shuffle($participantes);
        $nuevoGanador = $participantes[0];

        // Registrar el nuevo ganador con el mismo lugar
        $sql = "UPDATE participacion SET ganador = 1, lugar = ? WHERE sorteo_id = ? AND participante_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$lugarAnulado, $sorteo_id, $nuevoGanador['id']]);

        return $nuevoGanador;
    }

    public static function publicar($sorteo_id)
    {
        global $pdo;
        $sql = "UPDATE sorteos SET publicado = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
    }

    public static function despublicar($sorteo_id)
    {
        global $pdo;
        $sql = "UPDATE sorteos SET publicado = 0 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
    }


    public static function cerrarSorteo($sorteo_id)
    {
        global $pdo;
        $sql = "UPDATE sorteos SET cerrado = 1, fecha_cierre = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
    }
}
