<?php
declare(strict_types=1);
require_once __DIR__ . '/product_auth.php';
$pdo = db();
$q=trim($_GET['q']??''); $page=max(1,(int)($_GET['page']??1)); $perPage=10; $where=''; $params=[];
if($q!==''){ $where='WHERE sku LIKE :q OR name LIKE :q'; $params[':q']='%'.$q.'%'; }
$s=$pdo->prepare("SELECT COUNT(*) FROM products $where"); $s->execute($params); $total=(int)$s->fetchColumn(); $pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages); $offset=($page-1)*$perPage;
$s=$pdo->prepare("SELECT id,sku,name,price,created_at FROM products $where ORDER BY id DESC LIMIT :lim OFFSET :off");
foreach($params as $k=>$v)$s->bindValue($k,$v); $s->bindValue(':lim',$perPage,PDO::PARAM_INT); $s->bindValue(':off',$offset,PDO::PARAM_INT); $s->execute(); $products=$s->fetchAll();
$pageTitle='Products'; require __DIR__.'/header.php'; ?>
<div class="card"><div class="page-heading"><div><h1>Products</h1><p>Manage your products.</p></div><a class="button" href="product_create.php">+ Create Product</a></div>
<form method="get" class="search-bar"><input name="q" value="<?= e($q) ?>" placeholder="Search by SKU or product name"><button type="submit">Search</button><?php if($q!==''): ?><a class="button secondary" href="products.php">Clear</a><?php endif; ?></form>
<div class="table-wrap"><table><thead><tr><th>SKU</th><th>Product Name</th><th>Price</th><th>Created</th><th>Actions</th></tr></thead><tbody>
<?php if(!$products): ?><tr><td colspan="5">No products found.</td></tr><?php else: foreach($products as $p): ?><tr><td><?= e($p['sku']) ?></td><td><?= e($p['name']) ?></td><td><?= number_format((float)$p['price'],2) ?></td><td><?= e($p['created_at']) ?></td><td class="actions"><a href="product_view.php?id=<?= (int)$p['id'] ?>">View</a><a href="product_edit.php?id=<?= (int)$p['id'] ?>">Edit</a><form method="post" action="product_delete.php" onsubmit="return confirm('Delete this product?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; endif; ?></tbody></table></div>
<?php if($pages>1): ?><div class="pagination"><?php if($page>1): ?><a href="?q=<?= urlencode($q) ?>&page=<?= $page-1 ?>">« Previous</a><?php endif; ?><?php for($i=1;$i<=$pages;$i++): ?><a class="<?= $i===$page?'active':'' ?>" href="?q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a><?php endfor; ?><?php if($page<$pages): ?><a href="?q=<?= urlencode($q) ?>&page=<?= $page+1 ?>">Next »</a><?php endif; ?></div><?php endif; ?></div>
<?php require __DIR__.'/footer.php'; ?>
