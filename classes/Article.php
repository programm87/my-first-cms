<?php


/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /**
    * @var int ID статей из базы данны
    */
    public $id = null;

    /**
    * @var int Дата первой публикации статьи
    */
    public $publicationDate = null;

    /**
    * @var string Полное название статьи
    */
    public $title = null;

    /**
    * @var int ID категории статьи
    */
    public $categoryId = null;
    
    /**
    * @var int ID подкатегории статьи
    */
    public $subcategoryId = null;

    /**
    * @var string Краткое описание статьи
    */
    public $summary = null;

    /**
    * @var string HTML содержание статьи
    */
    public $content = null;
    
    public $active = null;
    
    public $clsValuesArray = array();
    
    public $authors = null;
       
    /**
     * Создаст объект статьи
     * 
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data=array())
    {
        
      if (isset($data['id'])) {
          $this->id = (int) $data['id'];
      }
      
      if (isset( $data['publicationDate'])) {
          $this->publicationDate = (string) $data['publicationDate'];     
      }

      //die(print_r($this->publicationDate));

      if (isset($data['title'])) {
          $this->title = $data['title'];        
      }
      
      if (isset($data['categoryId'])) {
          $this->categoryId = (int) $data['categoryId']; 
      }
      
      if (isset($data['subcategoryId'])) {
          $this->subcategoryId = (int) $data['subcategoryId'];      
      }
      
      if (isset($data['summary'])) {
          $this->summary = $data['summary'];         
      }
      
      if (isset($data['content'])) {
          $this->content = $data['content'];  
      }
      
      if (isset($data['active'])) {
          $this->active = (int) $data['active'];  
      }
      
      if (isset($data['authors'])) {
          $this->authors = (array) $data['authors'];  
      }
      
    }
    
    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues ( $params ) {

      // Сохраняем все параметры
      $this->__construct( $params );

      // Разбираем и сохраняем дату публикации
      if ( isset($params['publicationDate']) ) {
        $publicationDate = explode ( '-', $params['publicationDate'] );

        if ( count($publicationDate) == 3 ) {
          list ( $y, $m, $d ) = $publicationDate;
          $this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
        }
      }
    }

    
    /**
    * Возвращаем объект статьи соответствующий заданному ID статьи
    *
    * @param int ID статьи
    * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id, $select=false) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) "
                . "AS publicationDate FROM articles WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $row = $st->fetch();
	$column = $select ? "id" : "login";
	$sql = "SELECT $column FROM authors_articles LEFT JOIN users ON "
		. "authorId = id WHERE authors_articles.articleid = :articleId";
	$st = $conn->prepare($sql);
	$st->bindValue(":articleId", $id, PDO::PARAM_INT);
	$st->execute();
	$row['authors'] = $st->fetchAll(PDO::FETCH_COLUMN);
        $conn = null;
        
        if ($row) { 
            return new Article($row);
        }
    }

//    Функция для пересборки запроса
    protected static function buildWhereConditionForGetLists(...$arr) {      
        $accum = array(); //
        $switch = 0;
        $string = '';
        foreach ($arr as $val) {
            if (!empty($val)) {
                $accum[] = $val;
                $switch += 1;
            }
        }
        if ($switch > 1) {
            $string = implode(" AND ", $accum);
        } elseif ($switch == 1) {
            $string = $accum[0];
        } else {
            return "";
        }  
        return "WHERE {$string}";
    }
    
    
    /**
    * Возвращает все (или диапазон) объекты Article из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param int $categoryId Вернуть статьи только из категории с указанным ID
    * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
    * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
    */
    public static function getList($numRows=1000000, 
            $categoryId=null, $order="publicationDate DESC", $active=false) 
    {
        
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM articles";
        $categoryClause = $categoryId ? "categoryId = :categoryId" : "";
        $activeClause = $active ? "active = :active" : "";
        $assembledRequest = self::buildWhereConditionForGetLists($categoryClause, $activeClause);
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) 
                AS publicationDate
                $fromPart $assembledRequest
                ORDER BY  $order  LIMIT :numRows";
        $st = $conn->prepare($sql);   
        
        echo $sql ."<br>";
        
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        
        if ($categoryId)
            $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
        
        if ($active)
            $st->bindValue( ":active", $active, PDO::PARAM_INT);
       
        $st->execute(); // выполняем запрос к базе данных
       
        $list = array();
        while ($row = $st->fetch()) {
            $article = new Article($row);
            $list += [$article->id => $article];
        }

        $sql = "SELECT login FROM authors_articles LEFT JOIN users ON "
                . "authorId = id WHERE authors_articles.articleId = :articleId";
	foreach ($list as $id => $article) {
	    $st = $conn->prepare($sql);
	    $st->bindValue(":articleId", $id, PDO::PARAM_INT);
	    $st->execute();
	    $article->authors = $st->fetchAll(PDO::FETCH_COLUMN);
	}
        
        // Получаем общее количество статей, которые соответствуют критерию
        $res =  $conn->query("SELECT COUNT(*) $fromPart");
        $res->execute();
        $totalRows = $res->fetchColumn();
        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
    }

    /**
    * Вставляем текущий объект Article в базу данных, устанавливаем его ID
    */
    public function insert() {

        // Есть уже у объекта Article ID?
        if ( !is_null( $this->id ) ) trigger_error ( "Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR );

        // Вставляем статью
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "INSERT INTO articles ( publicationDate, categoryId, subcategoryId, title, summary, content, active ) VALUES ( FROM_UNIXTIME(:publicationDate), :categoryId, :subcategoryId, :title, :summary, :content, :active )";
        $st = $conn->prepare ( $sql );
        $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
        $st->bindValue( ":categoryId", $this->categoryId, $this->categoryId ? PDO::PARAM_INT : PDO::PARAM_NULL);
	$st->bindValue( ":subcategoryId", $this->subcategoryId, $this->subcategoryId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
        $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
        $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
        $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
        $st->execute();
        $this->id = $conn->lastInsertId();
        $sql = "INSERT INTO authors_articles VALUES (:authorId, :articleId)";
	foreach ($this->authors as $id) {
	    $st = $conn->prepare($sql);
	    $st->bindValue(":authorId", $id, PDO::PARAM_INT);
	    $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
	    $st->execute();
	}
        $conn = null;
    }

    /**
    * Обновляем текущий объект статьи в базе данных
    */
    public function update() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::update(): "
              . "Attempt to update an Article object "
              . "that does not have its ID property set.", E_USER_ERROR );

      // Обновляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate),"
              . " categoryId=:categoryId, subcategoryId=:subcategoryId, title=:title, summary=:summary,"
              . " content=:content, active=:active WHERE id = :id";
      
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":categoryId", $this->categoryId, $this->categoryId ? PDO::PARAM_INT : PDO::PARAM_NULL);
      $st->bindValue( ":subcategoryId", $this->subcategoryId, $this->subcategoryId ? PDO::PARAM_INT : PDO::PARAM_NULL);
      $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
      $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
      $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
      $st->execute();
      $sql = "DELETE FROM authors_articles WHERE articleId = :articleId";
      $st = $conn->prepare($sql);
      $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
      $st->execute();
      $sql = "INSERT INTO authors_articles VALUES (:authorId, :articleId)";
      foreach ($this->authors as $id) {
	    $st = $conn->prepare($sql);
	    $st->bindValue(":authorId", $id, PDO::PARAM_INT);
	    $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
	    $st->execute();
      }
      $conn = null;
    }


    /**
    * Удаляем текущий объект статьи из базы данных
    */
    public function delete() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM articles WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

    /**
    * Получаем все статьи по подкатегории
    */
    public static function getBySubcat($numRows=1000000, $subcategoryId=null, 
	    $active=false, $order="publicationDate DESC") {

	$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM articles";
	$subcategoryClause = $subcategoryId ? "WHERE subcategoryId = :subcategoryId" . 
		($active ? "" : " AND active = 1") : ($active ? "" : "WHERE active = 1");    
	if ($subcategoryId === 0) {
	    $subcategoryClause = "WHERE subcategoryId IS NULL AND active = 1";	    
	}
	$sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) 
                AS publicationDate
                $fromPart $subcategoryClause
                ORDER BY  $order  LIMIT :numRows";
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        if ($subcategoryId) {
	    $st->bindValue(":subcategoryId", $subcategoryId, PDO::PARAM_INT);
	    $subcategoryClause = "WHERE subcategoryId = $subcategoryId";
	    if (!$active) {
		$subcategoryClause .= ' AND active = 1';		
	    }
	}
        $st->execute();
        $list = array();
	while ($row = $st->fetch()) {
            $article = new Article($row);
            $list += [$article->id => $article];
        }
        
        $sql = "SELECT login FROM authors_articles LEFT JOIN users ON "
		. "authorId = id WHERE authors_articles.articleId = :articleId";
	foreach ($list as $id => $article) {
	    $st = $conn->prepare($sql);
	    $st->bindValue(":articleId", $id, PDO::PARAM_INT);
	    $st->execute();
	    $article->authors = $st->fetchAll(PDO::FETCH_COLUMN);
	}
        
        // Получаем общее количество статей, которые соответствуют критерию
	$sql = "SELECT COUNT(*) AS totalRows $fromPart $subcategoryClause";
	$totalRows = $conn->query($sql)->fetch();
        $conn = null;
        return array(
	    "results" => $list,
	    "totalRows" => $totalRows[0]
		);
    }
}

