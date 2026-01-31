<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';


$search = $_GET['search'] ?? '';
$curPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;

$pageItems = 3;
if ($curPage > 0 && $search !== '') {

    $sqlCount = 'SELECT COUNT(DISTINCT l.id) as total FROM lots l
            WHERE l.end_at > NOW()
            AND MATCH(l.title, l.description) AGAINST(?)';

    $stmtCount = dbGetPrepareStmt($con, $sqlCount, [$search]);
    mysqli_stmt_execute($stmtCount);
    $resultCount = mysqli_stmt_get_result($stmtCount);
    $lotsCount = mysqli_fetch_assoc($resultCount)['total'];
    $pagesCount = (int)ceil($lotsCount / $pageItems);
    $offset = ($curPage - 1) * $pageItems;
    $pages = range(1, $pagesCount);
    $sql = 'SELECT
            l.id,
            l.title,
            l.price_start,
            l.img_url,
            l.end_at,
             c.title AS category,
            COALESCE(MAX(b.price), l.price_start) AS current_price
        FROM lots l
        JOIN categories c ON c.id = l.category_id
        LEFT JOIN bets b ON b.lot_id = l.id
        WHERE
            MATCH(l.title, l.description) AGAINST (? IN BOOLEAN MODE)
            AND l.end_at > NOW()
        GROUP BY l.id, l.created_at
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?';


    $stmt = dbGetPrepareStmt($con, $sql, [$search, $pageItems, $offset]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

mysqli_close($con);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);

$lotsBlock = includeTemplate('lots-list.php', [
    'lots' => $lots ?? [],
]);
$pagination = includeTemplate('pagination.php', [
    'pages' => $pages ?? [],
    'curPage' => $curPage,
]);

$content = includeTemplate('search.php', [
    'title' => $search,
    'searchString' => $search,
    'lotsBlock' => $lotsBlock,
    'pagination' => $pagination,
    'lotsCount' => $lotsCount ?? 0
]);

print includeTemplate('layout.php', [
    'titlePage' => 'Главная',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
]);
