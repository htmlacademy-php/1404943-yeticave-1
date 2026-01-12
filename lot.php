<?php

/**
 * @var number $isAuth
 * @var string $userName
 */
include_once __DIR__ . '/init.php';
try {
    $con = mysqli_connect('mysql', 'root', 'root', 'yeticave');

    mysqli_set_charset($con, 'utf8');
    $sql = 'SELECT * FROM categories';
    $result = mysqli_query($con, $sql);

    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $lotId = intval($_GET['id'] ?? 0);
    $sql = "SELECT l.*, c.title AS category_name,
            COALESCE(MAX(b.price), l.price_start) AS current_price,
            (COALESCE(MAX(b.price), l.price_start) + l.price_step) AS min_bid
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bets b ON l.id = b.lot_id
            WHERE l.id = $lotId
            GROUP BY l.id; ";
    $result = mysqli_query($con, $sql);
    $lot = mysqli_fetch_assoc($result);

    if (!$lot) {
        throw new Exception('Такая страница не найдена', 404);
    }
    $title = $lot['title'];
    $content = includeTemplate('lot.php', ['lot' => $lot]);
} catch (Exception $e) {
    if ($e->getCode() == 404) {
        $content = includeTemplate('404.php');
        $title = '404 Страница не найдена';
        http_response_code(404);
    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo "Внутренняя ошибка сервера";
        die();
    }
}

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$content = includeTemplate('lot.php', ['lot' => $lot]);
print includeTemplate('layout.php', [
    'titlePage' => $title,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
    'menu' => $menu,
    'content' => $content,
]);
mysqli_close($con);
