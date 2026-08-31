<?php
declare(strict_types=1); require_once __DIR__.'/product_auth.php'; $pdo=db();if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed.');}verify_csrf();$id=(int)($_POST['id']??0);if($id>0){$s=$pdo->prepare('DELETE FROM products WHERE id=?');$s->execute([$id]);}header('Location: products.php?success=Product%20deleted%20successfully.');exit;
