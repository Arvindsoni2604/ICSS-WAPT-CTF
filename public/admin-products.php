<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_login();
if (!is_super_admin()) { http_response_code(403); exit('Super admin access only. Product changes are restricted to the super admin.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $price = trim((string)($_POST['price'] ?? ''));
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        if ($name === '' || mb_strlen($name) > 140 || $description === '' || $category === '' || mb_strlen($category) > 60 || !is_numeric($price) || (float)$price < 0 || (float)$price > 10000000 || $stock === false || $stock === null || $stock < 0 || $stock > 1000000) {
            flash('Please provide a valid name, description, category, price and stock quantity.');
        } else {
            if ($id > 0) {
                $st = db()->prepare('UPDATE products SET name=?, description=?, price=?, category=?, stock=? WHERE id=?');
                $st->execute([$name, $description, number_format((float)$price, 2, '.', ''), $category, $stock, $id]);
                flash('Product updated.');
            } else {
                $st = db()->prepare('INSERT INTO products (name, description, price, category, stock) VALUES (?,?,?,?,?)');
                $st->execute([$name, $description, number_format((float)$price, 2, '.', ''), $category, $stock]);
                flash('Product added to the shop.');
            }
        }
    } elseif ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        try {
            $st = db()->prepare('DELETE FROM products WHERE id=?'); $st->execute([$id]);
            flash($st->rowCount() ? 'Product removed from the shop.' : 'Product not found.');
        } catch (PDOException $e) {
            flash('This product is referenced by an order and cannot be deleted. Set its stock to 0 instead.');
        }
    }
    header('Location: /admin-products.php'); exit;
}
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$editing = null;
if ($editId) { $q = db()->prepare('SELECT id,name,description,price,category,stock FROM products WHERE id=?'); $q->execute([$editId]); $editing = $q->fetch() ?: null; }
$products = db()->query('SELECT p.id,p.name,p.description,p.price,p.category,p.stock,COUNT(oi.id) AS order_lines FROM products p LEFT JOIN order_items oi ON oi.product_id=p.id GROUP BY p.id ORDER BY p.id DESC')->fetchAll();
$val = static fn($key, $default='') => h((string)($editing[$key] ?? $default));
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shop inventory | ICSS Admin</title><link rel="stylesheet" href="/assets/style.css"></head><body><header class="top"><a class="brand" href="/admin.php"><span class="mark">IC</span><div><b>ICSS ADMIN</b><small>SHOP INVENTORY</small></div></a><nav><a href="/admin.php">Admin access</a><a href="/shop.php">View shop</a><a href="/dashboard.php">Dashboard</a></nav></header><main class="wrap page admin-management-page admin-products-page"><?php show_flash(); ?><p class="eyebrow">ADMINISTRATOR ONLY</p><h1>Shop products</h1><p class="muted">Add products for sale, update descriptions and prices, manage stock, or remove products that have no order history. All items are fictional training-store products; no real payments are processed.</p>
<section class="panel"><h2><?=$editing ? 'Edit product' : 'Add a product'?></h2><form method="post" class="wallet-topup-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$editing ? (int)$editing['id'] : 0?>"><label for="name">Product name</label><input id="name" name="name" maxlength="140" required value="<?=$val('name')?>"><label for="description">Description</label><textarea id="description" name="description" rows="3" required><?=$val('description')?></textarea><label for="category">Category</label><input id="category" name="category" maxlength="60" required value="<?=$val('category')?>"><label for="price">Price (₹)</label><input id="price" name="price" type="number" min="0" max="10000000" step="0.01" required value="<?=$val('price','0.00')?>"><label for="stock">Stock quantity</label><input id="stock" name="stock" type="number" min="0" max="1000000" step="1" required value="<?=$val('stock','20')?>"><div class="admin-actions"><button class="button primary" type="submit"><?=$editing ? 'Save product changes' : 'Add product to shop'?></button><?php if ($editing): ?><a class="button" href="/admin-products.php">Cancel edit</a><?php endif; ?></div></form></section>
<h2>Current inventory</h2><table class="scores"><thead><tr><th>ID</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody><?php foreach ($products as $p): ?><tr><td><?=(int)$p['id']?></td><td><strong><?=h($p['name'])?></strong><br><span class="muted"><?=h($p['description'])?></span></td><td><?=h($p['category'])?></td><td>₹<?=number_format((float)$p['price'],2)?></td><td><?=(int)$p['stock']?></td><td class="admin-actions"><a class="button" href="/admin-products.php?edit=<?=(int)$p['id']?>">Edit</a><form method="post" onsubmit="return confirm('Remove this product from the shop?')"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button class="button" type="submit">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></main><footer>ICSS · Admin inventory management</footer></body></html>
