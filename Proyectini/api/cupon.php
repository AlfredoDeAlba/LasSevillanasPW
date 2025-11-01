<?php declare(strict_types=1);

require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/db.php';

use function App\Lib\getPDO;
header('Content-Type: application/json');

function sendError(string $message) : void {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}
$input =json_decode(file_get_contents('php://input'),true);
$codigo =trim($input['codigo'] ?? '');

if(empty($codigo)){
    sendError('No se proporciono codigo de cupon.');
}

try{
    $pdo=getPDO();
    $sql=
        "SELECT id_cupon, valor_descuento
         FROM cupones
         WHERE codigo = ?
         AND activo = TRUE
         AND NOW() BETWEEN fecha_inicio AND fecha_final
         LIMIT 1";
    $stmt=$pdo->prepare($sql);
    $stmt->execute([$codigo]);
    $cupon=$stmt->fetch();
    if($cupon){
        echo json_encode([
            'success'=>true,
            'id_cupon'=>(int)$cupon['id_cupon'],
            'descuento'=>(float)$cupon['valor_descuento']
        ]);
    }else{
        sendError('El cupon no es valido o ya expiro.');
    }
}catch(\PDOException $e){
    error_log('Error en Api de cupones: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error'=>'Error al consultar el cupon.']);
}

?>