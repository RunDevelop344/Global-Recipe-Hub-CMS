<?php include 'header.php'; ?>
<?php include 'connect.php'; ?>

<h2>All Recipes</h2>

<?php
$allowedSorts = ['meal_name', 'created_at', 'updated_at'];

$sort = $_GET['sort'] ?? 'meal_id';
$order = $_GET['order'] ?? 'DESC';
$search = trim($_GET['search'] ?? '');

if (!in_array($sort, $allowedSorts)) {
    $sort = 'meal_id';
}
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';


$sql = "
    
    SELECT 
        MIN(meals.meal_id) AS meal_id,
        meals.meal_name,
        meals.image_url,
        categories.category_name AS category,
        MIN(meals.created_at) AS created_at,
        MAX(meals.updated_at) AS updated_at
    FROM meals
    JOIN categories ON meals.category_id = categories.category_id
";

if ($search !== '') {
    $sql .= " WHERE meals.meal_name LIKE :search OR categories.category_name LIKE :search";
}

$sql .= "
    GROUP BY meals.meal_name, meals.image_url, categories.category_name
    ORDER BY $sort $order
";


// $sql .= " GROUP BY meals.meal_id";
// $sql .= " ORDER BY $sort $order";

$statement = $db->prepare($sql);



if ($search !== '') {
    $statement->bindValue(':search', "%$search%");

}

$statement->execute();
$recipes = $statement->fetchAll(PDO::FETCH_ASSOC);


function sortArrow($column, $currentSort, $currentOrder) {
    if ($column !== $currentSort) return '';
    return $currentOrder === 'ASC' ? ' ↑' : ' ↓';
}

$currentSortName = [
    'meal_name' => 'Title',
    'created_at' => 'Created Date',
    'updated_at' => 'Updated Date'
][$sort] ?? 'Title';
?>

<!-- --- Step 7: Search bar --- -->
<form method="get" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search recipes or categories..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<div class="sort-links" style="margin-bottom: 10px;">
    <strong>Sort by:</strong>
    <?php $toggleOrder = $order === 'ASC' ? 'DESC' : 'ASC'; ?>
    <a href="?sort=meal_name&order=<?= $toggleOrder ?>&search=<?= urlencode($search) ?>">Title<?= sortArrow('meal_name', $sort, $order) ?></a> |
    <a href="?sort=created_at&order=<?= $toggleOrder ?>&search=<?= urlencode($search) ?>">Created Date<?= sortArrow('created_at', $sort, $order) ?></a> |
    <a href="?sort=updated_at&order=<?= $toggleOrder ?>&search=<?= urlencode($search) ?>">Updated Date<?= sortArrow('updated_at', $sort, $order) ?></a>
</div>

<!-- Current Sort Display -->
<p><em>Currently sorted by: <strong><?= $currentSortName ?></strong> (<?= $order ?>)</em></p>

<!-- Recipes Grid -->
<div class="recipes" style="display: flex; flex-wrap: wrap; gap: 15px;">
<?php if (count($recipes) > 0): ?>
    <?php foreach ($recipes as $recipe): ?>
        <div class="recipe-card" style="border: 1px solid #ccc; padding: 10px; width: 220px; border-radius: 8px;">
            <img src="<?= htmlspecialchars($recipe['image_url']) ?>" alt="<?= htmlspecialchars($recipe['meal_name']) ?>" width="200" height="150" style="border-radius: 5px;">
            <h3><?= htmlspecialchars($recipe['meal_name']) ?></h3>
            <p><strong>Category:</strong> <?= htmlspecialchars($recipe['category']) ?></p>
            <p><small>Created: <?= htmlspecialchars($recipe['created_at']) ?></small></p>
            <p><small>Updated: <?= htmlspecialchars($recipe['updated_at']) ?></small></p>
            <a href="post.php?id=<?= $recipe['meal_id'] ?>">View</a>
            <!-- <a href="edit.php?id=
             ">Edit</a> -->
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No recipes found.</p>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>