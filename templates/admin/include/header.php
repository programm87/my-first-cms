<div id="adminHeader">
    <h2>Widget News Admin</h2>
    <p>You are logged in as <b><?php echo htmlspecialchars( $_SESSION['username']) ?></b>.
        <a href="admin.php?action=listArticles">Edit Articles</a> 
        <a href="admin.php?action=listCategories">Edit Categories</a>
        <a href="admin.php?action=listSubcategories">Подкатегории</a>
        <a href="admin.php?action=logout"?>Log Out</a>
        <a href="admin.php?action=listUsers">Пользователи</a>
    </p>
</div>
