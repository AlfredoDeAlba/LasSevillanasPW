<?php
namespace App\Lib;
use PDO;
require_once __DIR__ . '/db.php';

// Mapea una fila de la tabla `producto` al formato usado en el frontend/API
function mapProductRow(array $row) : array {
    return [
        'id' => isset($row['id_producto']) ? (string) $row['id_producto'] : null,
        'name' => (string) ($row['nombre'] ?? ''),
        'price' => isset($row['precio']) ? (float) $row['precio'] : 0.0,
        'description' => (string) ($row['descripcion'] ?? ''),
        'image' => $row['foto'] ?? null,
        'stock' => isset($row['stock']) ? (int) $row['stock'] : 0,
        'date' => isset($row['fecha_agregado']) && $row['fecha_agregado'] !== null
            ? strtotime((string) $row['fecha_agregado'])
            : null,
    ];
}

function readProducts() : array {
    $sql = 'SELECT id_producto, nombre, descripcion, stock, precio, foto, fecha_agregado FROM producto ORDER BY id_producto DESC';
    $stmt = getPDO()->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return array_map(static fn($r) => mapProductRow($r), $rows);
}

function findProduct(string $id) : ?array {
    $sql = 'SELECT id_producto, nombre, descripcion, stock, precio, foto, fecha_agregado FROM producto WHERE id_producto = :id LIMIT 1';
    $stmt = getPDO()->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? mapProductRow($row) : null;
}

function upsertProduct(array $payload) : array {
    $id = $payload['id'] ?? null;
    $name = (string) ($payload['name'] ?? '');
    $price = (float) ($payload['price'] ?? 0);
    $description = (string) ($payload['description'] ?? '');
    $stock = (int) ($payload['stock'] ?? 0);
    $image = $payload['image'] ?? null;

    if ($id === null || $id === '' ) {
        $sql = 'INSERT INTO producto (nombre, descripcion, stock, precio, foto) VALUES (:n, :d, :s, :p, :f)';
        $stmt = getPDO()->prepare($sql);
        $stmt->execute([
            ':n' => $name,
            ':d' => $description,
            ':s' => $stock,
            ':p' => $price,
            ':f' => $image,
        ]);
        $newId = (string) getPDO()->lastInsertId();
        return findProduct($newId) ?? [
            'id' => (int) $newId,
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'image' => $image,
            'stock' => $stock,
            'date' => null,
        ];
    }

    $sql = 'UPDATE producto SET nombre = :n, descripcion = :d, stock = :s, precio = :p, foto = :f WHERE id_producto = :id';
    $stmt = getPDO()->prepare($sql);
    $stmt->execute([
        ':n' => $name,
        ':d' => $description,
        ':s' => $stock,
        ':p' => $price,
        ':f' => $image,
        ':id' => $id,
    ]);
    return findProduct((string) $id) ?? [
        'id' => (int) $id,
        'name' => $name,
        'price' => $price,
        'description' => $description,
        'image' => $image,
        'stock' => $stock,
        'date' => null,
    ];
}

function deleteProduct(string $id): void
{
    $stmt = getPDO()->prepare('DELETE FROM producto WHERE id_producto = :id');
    $stmt->execute([':id' => $id]);
}
