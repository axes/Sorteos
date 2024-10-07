<?php
require_once __DIR__ . '/../../config/database.php';

class Participante
{
    public static function guardarOAsociar($nombre, $email, $rut, $sorteo_id)
    {
        global $pdo;

        // Verificar si el participante ya existe por email o RUT
        $sql = "SELECT id FROM participantes WHERE email = ? OR rut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $rut]);
        $participante = $stmt->fetch();

        if ($participante) {
            // Si el participante ya existe, asociarlo al sorteo
            self::asociarConSorteo($participante['id'], $sorteo_id);
        } else {
            // Crear un nuevo participante
            $id_temporal = uniqid();
            $sql = "INSERT INTO participantes (nombre, email, rut, id_temporal) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $rut, $id_temporal]);

            $participante_id = $pdo->lastInsertId();
            // Asociar el nuevo participante al sorteo
            self::asociarConSorteo($participante_id, $sorteo_id);
        }
    }

    public static function obtenerPorSorteo($sorteo_id)
    {
        global $pdo;
        $sql = "SELECT p.id, p.nombre, p.email, p.rut, p.baneado FROM participantes p
                INNER JOIN participacion pa ON p.id = pa.participante_id
                WHERE pa.sorteo_id = ? AND p.baneado = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sorteo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function asociarConSorteo($participante_id, $sorteo_id)
    {
        global $pdo;

        // Verificar si ya está asociado al sorteo
        $sql = "SELECT * FROM participacion WHERE participante_id = ? AND sorteo_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$participante_id, $sorteo_id]);

        if (!$stmt->fetch()) {
            // Si no está asociado, crear la relación
            $sql = "INSERT INTO participacion (participante_id, sorteo_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$participante_id, $sorteo_id]);
        }
    }

    public static function banear($participante_id)
    {
        global $pdo;
        $sql = "UPDATE participantes SET baneado = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$participante_id]);
    }

    public static function desbanear($participante_id)
    {
        global $pdo;
        $sql = "UPDATE participantes SET baneado = 0 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$participante_id]);
    }


    public static function cargarDesdeCSV($archivo, $sorteo_id)
    {
        global $pdo;
        $totalProcesados = 0;
        $totalNuevos = 0;

        if (($handle = fopen($archivo, 'r')) !== false) {
            $isFirstRow = true;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Saltar la primera fila (encabezado)
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }

                // Verificar que la fila tenga al menos 3 columnas
                if (count($data) < 3) {
                    continue; // Saltar filas incompletas
                }

                $nombre = trim($data[0]);
                $email = trim($data[1]);
                $rut = normalizarRut($data[2]);

                // Validar datos requeridos
                if (empty($nombre) || empty($rut)) {
                    continue; // Saltar si faltan datos esenciales
                }

                $totalProcesados++;

                // Verificar si el participante ya existe por RUT
                $stmt = $pdo->prepare("SELECT id, baneado FROM participantes WHERE rut = ?");
                $stmt->execute([$rut]);
                $participante = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($participante) {
                    if ($participante['baneado']) {
                        // Saltar participantes baneados
                        continue;
                    }
                    $participante_id = $participante['id'];
                } else {
                    // Insertar nuevo participante si no está baneado
                    $stmt = $pdo->prepare("INSERT INTO participantes (nombre, email, rut) VALUES (?, ?, ?)");
                    $stmt->execute([$nombre, $email, $rut]);
                    $participante_id = $pdo->lastInsertId();
                    $totalNuevos++;
                }

                // Verificar si la participación ya existe
                $stmt = $pdo->prepare("SELECT 1 FROM participacion WHERE sorteo_id = ? AND participante_id = ?");
                $stmt->execute([$sorteo_id, $participante_id]);
                $existeParticipacion = $stmt->fetchColumn();

                if (!$existeParticipacion) {
                    // Asociar participante al sorteo
                    $stmt = $pdo->prepare("INSERT INTO participacion (sorteo_id, participante_id) VALUES (?, ?)");
                    $stmt->execute([$sorteo_id, $participante_id]);
                }
            }
            fclose($handle);
        }
        return ['totalProcesados' => $totalProcesados, 'totalNuevos' => $totalNuevos];
    }

    // Método usado en ApiController
    public static function obtenerConcursosPorRut($rut)
    {
        global $pdo;
        try {
            // Incluir el nombre y el email del participante en la consulta
            $sql = "SELECT p.nombre AS participante_nombre, p.email AS participante_email, 
                           s.nombre, s.fecha_cierre, pa.ganador, pa.lugar
                    FROM participantes p
                    INNER JOIN participacion pa ON p.id = pa.participante_id
                    INNER JOIN sorteos s ON pa.sorteo_id = s.id
                    WHERE p.rut = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rut]);
            $concursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Formatear la salida
            foreach ($concursos as &$concurso) {
                $concurso['ganador'] = $concurso['ganador'] == 1 ? "Sí" : ($concurso['ganador'] == -1 ? "Anulado" : "No");
                $concurso['lugar'] = $concurso['lugar'] ? "{$concurso['lugar']}º lugar" : "No aplicado";
                $concurso['fecha_cierre'] = $concurso['fecha_cierre'] ?: "Fecha no disponible";
            }
    
            return $concursos;
        } catch (PDOException $e) {
            error_log('Error en obtenerConcursosPorRut: ' . $e->getMessage());
            return false;
        }
    }
}
// Metodos de utilidades

function normalizarRut($rut)
{
    // Eliminar puntos, guiones y espacios
    $rut = str_replace(['.', '-', ' '], '', $rut);
    // Convertir a mayúsculas
    $rut = strtoupper($rut);
    return $rut;
}
