
<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<!--        <?php echo "<pre>";
            print_r($results);
            print_r($data);
        echo "<pre>"; ?> Данные о массиве $results и типе формы передаются корректно-->
        
	<style> input[type="checkbox"] { width: 30px; position: relative; top: 10px; height: 16px } </style>
        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
            <input type="hidden" name="articleId" value="<?php echo $results['article']->id ?? $_POST['articleId'] ?? '' ?>">

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

            <ul>
                
              <li>
                <label for="active">Article Active</label>
                <input type="hidden" name="active" value="0" style="height: 5em">
                <input type=checkbox name="active" value="1" <?php echo isset($results['article']->active) && $results['article']->active ||
			isset($_POST['active']) && $_POST['active'] ? 'checked' : ""?>>
              </li>

              <li>
                <label for="title">Article Title</label>
                <input type="text" name="title" id="title" placeholder="Name of the article" required autofocus maxlength="255" 
		       value="<?php echo htmlspecialchars($results['article']->title ?? $_POST['title'] ?? '')?>" />
              </li>

              <li>
                <label for="summary">Article Summary</label>
                <textarea name="summary" id="summary" placeholder="Brief description of the article" required maxlength="1000" 
			  style="height: 5em;"><?php echo htmlspecialchars($results['article']->summary ?? $_POST['summary'] ?? '')?></textarea>
              </li>

              <li>
                <label for="content">Article Content</label>
                <textarea name="content" id="content" placeholder="The HTML content of the article" required maxlength="100000" 
			  style="height: 30em;"><?php echo htmlspecialchars($results['article']->content ?? $_POST['content'] ?? '')?></textarea>
              </li>

              <li>
                <label for="categoryId">Article Category</label>
                <select name="categoryId">
                  <option value="0"<?php echo (isset($results['article']->categoryId) || 
		  isset($_POST['categoryId'])) ? "" :  " selected" ?>>Без категории</option>
                <?php foreach ($results['categories'] as $category) { ?>
                  <option value="<?php echo $category->id?>"<?php 
		  echo ($category->id == ($_POST['categoryId'] ?? $results['article']->categoryId)) ?
		  " selected" : "" ?>><?php echo htmlspecialchars($category->name)?></option>
                <?php } ?>
                </select>
              </li>

              <li>
                <label for="subcategoryId">Article Subcategory</label>
		<select name="subcategoryId">
		    <option value="0"<?php echo isset($results['article']->subcategoryId) || 
		    isset($_POST['subcategoryId']) ? "" : " selected"?>>Без подкатегории</option>
		    <?php foreach ($results['group'] as $k => $v) { ?>
		    <optgroup label="<?php echo $k ? $results['categories'][$k]->name : 'Без категории' ?>">
			<?php if ($v===[]) { ?>
			<option disabled>Без подкатегории</option>
			<?php } else { ?>
			    <?php foreach ($v as $id) {?>
			<option value="<?php echo $id ?>"<?php echo ($id == ($_POST['subcategoryId'] ?? $results['article']->subcategoryId)) ?
			" selected" : "" ?>><?php echo htmlspecialchars($results['subcategories'][$id]->name) ?></option>
			    <?php } ?>
			<?php } ?>			
		    </optgroup>
		    <?php } ?>
		</select>
              </li>
              
              <li>
                <label for="authors">Article Authors</label>
		<style> option:checked {background: linear-gradient(#ef7d50, #ef7d50)}</style>
                <select name="authors[]" multiple size="5" style="width:30%">
                <?php foreach ($results['authors'] as $id => $author) { ?>
                  <option value="<?php echo $id?>"<?php 
		  echo in_array($id, $results['article']->authors ?? $_POST['authors'] ?? []) ?
		  " selected" : "" ?>><?php echo htmlspecialchars($author->login)?></option>
                <?php } ?>		
                </select>
              </li>
              
              <li>
                <label for="publicationDate">Publication Date</label>
                                <input type="date" name="publicationDate" id="publicationDate" placeholder="YYYY-MM-DD" required maxlength="10" value="<?php 
		echo $_POST['publicationDate'] ?? 
		    (isset($results['article']->publicationDate) ? date("Y-m-d", $results['article']->publicationDate) : "") ?>" />
              </li>


            </ul>

            <div class="buttons">
              <input type="submit" name="saveChanges" value="Save Changes" />
              <input type="submit" formnovalidate name="cancel" value="Cancel" />
            </div>

        </form>

    <!--<?php echo'<pre>';print_r($results['group']);print_r($_POST);echo'</pre>'; ?>-->
    <?php if (isset($results['article']->id)) { ?>
          <p><a href="admin.php?action=deleteArticle&amp;articleId=<?php echo $results['article']->id ?>" onclick="return confirm('Delete This Article?')">
                  Delete This Article
              </a>
          </p>
    <?php } ?>
	  
<?php include "templates/include/footer.php" ?>

              