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

    $sql = 'SELECT l.id,
       l.title,
       price_start,
       img_url,
       author_id,
       end_at,
       COALESCE(MAX(b.price), l.price_start) AS current_price,
       c.title                               AS category
FROM lots l
       JOIN categories c ON l.category_id = c.id
       LEFT JOIN bets b ON l.id = b.lot_id
WHERE l.end_at > NOW()
GROUP BY l.id, l.created_at
ORDER BY l.created_at DESC';

    $result = mysqli_query($con, $sql);

    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "Внутренняя ошибка сервера";
    die();
}


$menu = includeTemplate('promo.php', [
    'categories' => $categories,
]);
$content = includeTemplate('lots.php', [
        'lots' => $lots,
        'title' => 'Открытые лоты'
    ]);

print includeTemplate('layout.php', [
    'titlePage' => 'Главная',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
    'isMain' => true
]);
mysqli_close($con);
