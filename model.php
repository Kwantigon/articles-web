<?php

class Model {
	private $dbConnection;
	
	function initialize() {
		require('./db-config.php');
		//var_dump($db_config);
		//print("<br>");
		$this->dbConnection = new mysqli($db_config['server'], $db_config['login'], $db_config['password'], $db_config['database']);
		if ($this->dbConnection->connect_error) {
			throw new ErrorException('Could not connect to the database.');
		}
	}

	function __destruct() {
		$this->dbConnection->close();
	}

	function getArticles(/*$limit, $offset*/) {
		$query = "SELECT " . DatabaseConstants::ARTICLES_ID_COLUMN . ", " . DatabaseConstants::ARTICLES_NAME_COLUMN
				. " FROM " . DatabaseConstants::ARTICLES_TABLE . ";";
				//. " LIMIT $limit OFFSET $offset;";
		// $limit and $offset would be used for pagination.
		// But the assignments asks that pagination be done on the client side using JavaScript.
		console_log("Select articles query: $query");

		// Not using $this->dbConnection->prepare() because there is no user input here.
		if ($result = $this->dbConnection->query($query)) {
			//print('Query result: '); var_dump($result);
			//print("<br>");
			$articlesPreview = array();
			while ($row = $result->fetch_assoc()) {
				$articleId = $row[DatabaseConstants::ARTICLES_ID_COLUMN];
				$articleName = $row[DatabaseConstants::ARTICLES_NAME_COLUMN];
				$article = new Article();
				$article->setId($articleId);
				$article->setName($articleName);
				array_push($articlesPreview, $article);
			}
		} else {
			console_log("Could not SELECT from the database.");
			console_log($this->dbConnection->error);
		}

		return $articlesPreview;
	}

	function getArticle($articleId) {
		$query = "SELECT * FROM " . DatabaseConstants::ARTICLES_TABLE
				. " WHERE " . DatabaseConstants::ARTICLES_ID_COLUMN . " = ?;";
		console_log("Select article query: $query");
		$stmt = $this->dbConnection->prepare($query);
		$stmt->bind_param("i", $articleId);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			// I expect only 1 row.
			$row = $result->fetch_assoc();
			$articleId = $row[DatabaseConstants::ARTICLES_ID_COLUMN];
			$articleName = $row[DatabaseConstants::ARTICLES_NAME_COLUMN];
			$articleContent = $row[DatabaseConstants::ARTICLES_CONTENT_COLUMN];
			$article = new Article();
			$article->setId($articleId);
			$article->setName($articleName);
			$article->setContent($articleContent);
			return $article;
		} else {
			console_log("The database returned $result->num_rows rows");
			return null;
		}
	}

	function deleteArticle($articleId) {
		$query = "DELETE FROM " . DatabaseConstants::ARTICLES_TABLE
				. " WHERE " . DatabaseConstants::ARTICLES_ID_COLUMN . " = ?;";
		console_log("Delete article query: $query");
		$stmt = $this->dbConnection->prepare($query);
		$stmt->bind_param("i", $articleId);
		$stmtSuccess = $stmt->execute();
		return $stmtSuccess;
	}

	function addArticle($articleName, $articleContent) {
		$query = "INSERT INTO " . DatabaseConstants::ARTICLES_TABLE . " VALUES (NULL, ?, ?);";
		console_log("Insert article query: $query [$articleName, $articleContent]");
		$stmt = $this->dbConnection->prepare($query);
		$stmt->bind_param("ss", $articleName, $articleContent);
		$stmtSuccess = $stmt->execute();
		return $stmtSuccess;
	}

	function addArticleWithNameOnly($articleName) : int {
		$query = "INSERT INTO " . DatabaseConstants::ARTICLES_TABLE . " VALUES (NULL, ?, '');";
		console_log("Insert article query: $query [$articleName]");
		$stmt = $this->dbConnection->prepare($query);
		$stmt->bind_param("s", $articleName);
		$stmtSuccess = $stmt->execute();
		if ($stmtSuccess === false) {
			return false;
		}
		$newArticleId = $this->dbConnection->insert_id;
		console_log("ID of the newly created article is: $newArticleId");
		return $newArticleId;
	}

	function editArticle($articleId, $name, $content) {
		$query = "UPDATE " . DatabaseConstants::ARTICLES_TABLE 
				. " SET " . DatabaseConstants::ARTICLES_NAME_COLUMN . " = ?"
						. ", " . DatabaseConstants::ARTICLES_CONTENT_COLUMN . " = ?"
				. " WHERE " . DatabaseConstants::ARTICLES_ID_COLUMN . " = ?";
		console_log("Update article query: $query [$articleId, $name, $content]");
		$stmt = $this->dbConnection->prepare($query);
		$stmt->bind_param("ssi", $name, $content, $articleId);
		$stmtSuccess = $stmt->execute();
		return $stmtSuccess;
	}

	function updateUtmAssociation($articleId, $utmSource) {
		$query = "SELECT * FROM " . DatabaseConstants::ASSOCIATION_TABLE
				. " WHERE " . DatabaseConstants::ASSOCIATION_TABLE_ARTICLE_ID_COLUMN . " = ?"
				. " AND " . DatabaseConstants::ASSOCIATION_TABLE_UTM_SOURCE_NAME_COLUMN . " = ?";
		console_log("Select utm_association query: $query");
		$selectStmt = $this->dbConnection->prepare($query);
		$selectStmt->bind_param("is", $articleId, $utmSource);
		$selectStmt->execute();
		$result = $selectStmt->get_result();
		if ($result->num_rows > 1) {
			console_log('DATABASE ERROR: $result->num_rows > 1');
		}
		if ($result->num_rows == 0) {
			console_log("Will insert a new utm source");
			$insertQuery = "INSERT INTO " . DatabaseConstants::ASSOCIATION_TABLE . " VALUES (NULL, ?, ?, 1)";
			$insertStmt = $this->dbConnection->prepare($insertQuery);
			$insertStmt->bind_param("is", $articleId, $utmSource);
			$success = $insertStmt->execute();
			if ($success) {
				return 1;
			} else {
				return false;
			}
		} else {
			// Expecting only 1 row
			$row = $result->fetch_assoc();
			$associationId = $row[DatabaseConstants::ASSOCIATION_TABLE_ASSOCIATION_ID_COLUMN];
			$associationCount = $row[DatabaseConstants::ASSOCIATION_TABLE_ASSOCIATION_UTM_ACCESS_COUNT];
			$sourceName = $row[DatabaseConstants::ASSOCIATION_TABLE_UTM_SOURCE_NAME_COLUMN];
			console_log("[associationId: $associationId; associationCount: $associationCount; utmSource: $sourceName]");
			$associationCount++;
			console_log("Count increased by one: $associationCount");
			$updateQuery = "UPDATE " . DatabaseConstants::ASSOCIATION_TABLE
							. " SET " . DatabaseConstants::ASSOCIATION_TABLE_ASSOCIATION_UTM_ACCESS_COUNT . " = ?"
							. " WHERE " . DatabaseConstants::ASSOCIATION_TABLE_ASSOCIATION_ID_COLUMN . " = $associationId";
			console_log("Update query: $updateQuery");
			$updateStmt = $this->dbConnection->prepare($updateQuery);
			$updateStmt->bind_param("i", $associationCount);
			$success = $updateStmt->execute();
			if ($success) {
				console_log("Update succeeded");
				return $associationCount;
			} else {
				return false;
			}
		}
	}

	function getUtmAssociations($articleId) {
		$query = "SELECT * FROM " . DatabaseConstants::ASSOCIATION_TABLE
				. " WHERE " . DatabaseConstants::ASSOCIATION_TABLE_ARTICLE_ID_COLUMN . " = ?";
		$stmt = $this->dbConnection->prepare($query);
		$stmt->bind_param("i", $articleId);
		$stmt->execute();
		$result = $stmt->get_result();
		$utmCounts = [];
		while ($row = $result->fetch_assoc()) {
			$utmCounts[$row[DatabaseConstants::ASSOCIATION_TABLE_UTM_SOURCE_NAME_COLUMN]]
				= $row[DatabaseConstants::ASSOCIATION_TABLE_ASSOCIATION_UTM_ACCESS_COUNT];
		}
		return $utmCounts;
	}

	/* Unused functions
	function editArticleName($articleId, $name) {
		if (!isset($name) || is_null($name) || strlen($name) > DatabaseConstants::ARTICLE_NAME_MAX_LENGTH) {
			// Do nothing
			print("Article name is not set or null or too long.<br>");
			return;
		}

		$query = "UPDATE " . DatabaseConstants::ARTICLES_TABLE 
				. " SET " . DatabaseConstants::ARTICLES_NAME_COLUMN . " = ?"
				. " WHERE " . DatabaseConstants::ARTICLES_ID_COLUMN . " = ?";

		$stmt = $this->dbConnection->prepare("$query");
		$stmt->bind_param("si", $name, $articleId); // "si" means first parameter is s=string, second is i=int

		$result = $stmt->get_result();
	}

	function editArticleContent($articleId, $content) {
		if (strlen($content) > DatabaseConstants::ARTICLE_CONTENT_MAX_LENGTH) {
			// Do nothing
			print("Article content is not set or null or too long.<br>");
			return;
		}
		if (!isset($content) || is_null($content)) {
			$content = '';
		}

		$query = "UPDATE " . DatabaseConstants::ARTICLES_TABLE 
				. " SET " . DatabaseConstants::ARTICLES_CONTENT_COLUMN . " = '$content'"
				. " WHERE " . DatabaseConstants::ARTICLES_ID_COLUMN . " = $articleId";
		$result = $this->dbConnection->query($query);
	}
	*/
}

class DatabaseConstants {
	const ARTICLES_TABLE = 'Articles';
	const ARTICLES_NAME_COLUMN = 'article_name';
	const ARTICLES_CONTENT_COLUMN = 'article_content';
	const ARTICLES_ID_COLUMN = 'article_id';
	const ARTICLE_NAME_MAX_LENGTH = 32;
	const ARTICLE_CONTENT_MAX_LENGTH = 1024;
	const ASSOCIATION_TABLE = 'utm_source_article_association';
	const ASSOCIATION_TABLE_ASSOCIATION_ID_COLUMN = 'association_id';
	const ASSOCIATION_TABLE_ARTICLE_ID_COLUMN = 'association_article_id';
	const ASSOCIATION_TABLE_UTM_SOURCE_NAME_COLUMN = 'association_utm_name';
	const ASSOCIATION_TABLE_ASSOCIATION_UTM_ACCESS_COUNT = 'association_utm_access_count';
}

?>