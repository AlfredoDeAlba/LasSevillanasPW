<?php

declare(strict_types=1);

use function App\Lib\createCartItem;
use function App\Lib\errorResponse;
use function App\Lib\getPDO;
use function App\Lib\jsonResponse;

require_once __DIR__ . '/../lib/cart.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    errorResponse('Método no permitido', 405);
}

handleCreateOrder();

function handleCreateOrder(): void
{
    $input = normalizeInput($_POST);

    $pdo = getPDO();
    $pdo->beginTransaction();

    try {
        $cart = createCartItem($input['id_producto'], $input['cantidad']);

        $couponId = null;
        if ($input['coupon_code'] !== null) {
            $couponId = findCouponId($input['coupon_code']);
            if ($couponId === null) {
                throw new \RuntimeException('El cupón proporcionado no existe.');
            }
        }

        $stmt = $pdo->prepare('INSERT INTO pedido (id_carrito, id_cupon, precio_subtotal, precio_total, pago_completado, descuento_aplicado, estado_envio, direccion, cod_post, nom_cliente, num_cel) VALUES (:id_carrito, :id_cupon, :precio_subtotal, :precio_total, :pago_completado, :descuento_aplicado, :estado_envio, :direccion, :cod_post, :nom_cliente, :num_cel)');
        $stmt->execute([
            ':id_carrito' => $cart['id'],
            ':id_cupon' => $couponId,
            ':precio_subtotal' => $cart['subtotal'],
            ':precio_total' => $cart['subtotal'],
            ':pago_completado' => 0,
            ':descuento_aplicado' => 0,
            ':estado_envio' => 'pendiente',
            ':direccion' => $input['direccion'],
            ':cod_post' => $input['cod_post'],
            ':nom_cliente' => $input['nom_cliente'],
            ':num_cel' => $input['num_cel'],
        ]);

        $orderId = $pdo->lastInsertId();
        $pdo->commit();

        $order = findOrderWithDetails((string) $orderId);

        jsonResponse([
            'message' => 'Pedido registrado correctamente.',
            'data' => [
                'order' => $order,
                'cart' => $cart,
                'customer_email' => $input['email'],
            ],
        ], 201);
    } catch (\Throwable $exception) {
        $pdo->rollBack();
        errorResponse($exception->getMessage());
    }
}

function normalizeInput(array $input): array
{
    $productId = trim((string) ($input['id_producto'] ?? ''));
    $quantity = (int) ($input['cantidad'] ?? 0);
    $email = trim((string) ($input['email'] ?? ''));
    $nomCliente = trim((string) ($input['nom_cliente'] ?? ''));
    $direccion = trim((string) ($input['direccion'] ?? ''));
    $codPost = trim((string) ($input['cod_post'] ?? ''));
    $numCel = trim((string) ($input['num_cel'] ?? ''));
    $couponCode = trim((string) ($input['coupon_code'] ?? ''));

    if ($productId === '') {
        errorResponse('Falta el identificador del producto.');
    }

    if ($quantity <= 0) {
        errorResponse('La cantidad debe ser mayor a cero.');
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        errorResponse('Correo electrónico inválido.');
    }

    if ($nomCliente === '') {
        errorResponse('El nombre del cliente es obligatorio.');
    }

    if ($direccion === '') {
        errorResponse('La dirección es obligatoria.');
    }

    if ($codPost === '' || !preg_match('/^\d{4,6}$/', $codPost)) {
        errorResponse('Código postal inválido.');
    }

    if ($numCel === '' || !preg_match('/^\d{10}$/', $numCel)) {
        errorResponse('Número de contacto inválido.');
    }

    return [
        'id_producto' => $productId,
        'cantidad' => $quantity,
        'email' => $email,
        'nom_cliente' => $nomCliente,
        'direccion' => $direccion,
        'cod_post' => $codPost,
        'num_cel' => $numCel,
        'coupon_code' => $couponCode !== '' ? $couponCode : null,
    ];
}

function findCouponId(string $code): ?string
{
    $stmt = getPDO()->prepare('SELECT id_cupon FROM cupones WHERE codigo = :codigo LIMIT 1');
    $stmt->execute([':codigo' => $code]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : null;
}

function findOrderWithDetails(string $orderId): array
{
    $sql = <<<SQL
SELECT p.id_pedido,
       p.id_carrito,
       p.id_cupon,
       p.precio_subtotal,
       p.precio_total,
       p.pago_completado,
       p.descuento_aplicado,
       p.estado_envio,
       p.direccion,
       p.cod_post,
       p.nom_cliente,
       p.num_cel,
       p.fecha_compra,
       c.codigo AS codigo_cupon
FROM pedido p
LEFT JOIN cupones c ON c.id_cupon = p.id_cupon
WHERE p.id_pedido = :id
LIMIT 1
SQL;

    $stmt = getPDO()->prepare($sql);
    $stmt->execute([':id' => $orderId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new \RuntimeException('No se pudo recuperar el pedido.');
    }

    return [
        'id' => (string) ($row['id_pedido'] ?? ''),
        'cartId' => (string) ($row['id_carrito'] ?? ''),
        'couponId' => isset($row['id_cupon']) ? (string) $row['id_cupon'] : null,
        'couponCode' => $row['codigo_cupon'] ?? null,
        'subtotal' => isset($row['precio_subtotal']) ? (float) $row['precio_subtotal'] : 0.0,
        'total' => isset($row['precio_total']) ? (float) $row['precio_total'] : 0.0,
        'discount' => isset($row['descuento_aplicado']) ? (float) $row['descuento_aplicado'] : 0.0,
        'paid' => (bool) ($row['pago_completado'] ?? false),
        'status' => $row['estado_envio'] ?? 'pendiente',
        'address' => $row['direccion'] ?? '',
        'postalCode' => $row['cod_post'] ?? '',
        'customer' => $row['nom_cliente'] ?? '',
        'phone' => $row['num_cel'] ?? '',
        'createdAt' => $row['fecha_compra'] ?? null,
    ];
}
