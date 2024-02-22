<?php
class Controller {
	private $model;
	private $view;
	function __construct() {
		$this->model = new Model();
		$this->model->initialize();
		$this->view = new View();
	}   

	// GET /articles
	function serveRequest_getArticles() {
		console_log('Processing GET /articles');
		//$offset = ($pageNumber - 1) * ViewConstants::ARTICLES_PER_PAGE;
		$articles = $this->model->getArticles(/*ViewConstants::ARTICLES_PER_PAGE, $offset*/);
		//var_dump($articles); print("<br>");
		$this->view->renderArticlePreview($articles);
	}

	// GET /article/{articleId}
	function serveRequest_getArticle($articleId) {
		$article = $this->getArticleAndVerify($articleId);
		$this->view->renderArticle($article);
	}

	// GET /article/{articleId}?utm_source={utmSource}
	function serveRequest_getArticleWithUtmSource($articleId, $utmSource) {
		$article = $this->getArticleAndVerify($articleId);
		if ($utmSource === null) {
			$this->serveRequest_getArticle($articleId);
		} else {
			$associationCount = $this->model->updateUtmAssociation($articleId, $utmSource);
			if ($associationCount === false) {
				bad_request_response_then_exit(500, 'There was an error when communicating with the database');
			}
			$utmCounts = $this->model->getUtmAssociations($articleId);
			$this->view->renderArticleWithUtmSources($article, $utmCounts);
		}
	}
	
	// DELETE /article/{articleId}
	function serveRequest_deleteArticle($articleId) {
		console_log("Deletion of article with ID $articleId was requested.");
		$article = $this->getArticleAndVerify($articleId);
		$result = $this->model->deleteArticle($articleId);
		if ($result === true) {
			http_response_code(200);
		} else {
			http_response_code(500);
		};
	}

	// GET /article-create
	function serveRequest_getCreateArticle() {
		$this->view->renderArticleCreationForm();
	}

	// POST /article-create
	function serveRequest_createNewArticle($postRequestData) {
		$name = $postRequestData['articleName'];
		if (!isset($name) || is_null($name) || strlen($name) > DatabaseConstants::ARTICLE_NAME_MAX_LENGTH) {
			console_log("Article name is not set or null or too long.");
			bad_request_response_then_exit(400, "The article must have a name with at most 32 characters");
		}
		/* $content will be supplied by the user in the next step.
			For now, only get the name and then create the article.
		$content = $postRequestData["articleContent"];
		if (!isset($content) || is_null($content)) {
			$content = "";
		}
		if (strlen($content) > DatabaseConstants::ARTICLE_CONTENT_MAX_LENGTH) {
			// Do nothing
			console_log("Article content is not set or null or too long.");
			return;
		}
		$this->model->addArticle($name, $content);
		*/
		$id = $this->model->addArticleWithNameOnly($name);
		if ($id === false) {
			http_response_code(500);
			return;
		}
		$articleEditLink = View::getEditArticleRequestUrl($id);
		header("Location: $articleEditLink", 302);
	}

	// GET /article-edit/{articleId}
	function serveRequest_getArticleEdit($articleId) {
		$article = $this->getArticleAndVerify($articleId);
		$this->view->renderEditArticle($article);
	}

	// POST /article-edit/{articleId}
	function serveRequest_postArticleEdit($articleId, $postRequestData) {
		$article = $this->getArticleAndVerify($articleId);
		if (!isset($postRequestData['articleName'])) {
			console_log("Article name is empty.");
			bad_request_response_then_exit(400, "The article name is empty. Please provide a valid article name.");
		}
		
		$name = trim($postRequestData['articleName']);
		if ($name === '' || strlen($name) > DatabaseConstants::ARTICLE_NAME_MAX_LENGTH) {
			console_log("Article name is empty or too long.");
			bad_request_response_then_exit(400, "The article name is invalid. Please provide a non-empty name of length at most 32.");
		}
		$content = "";
		if (isset($postRequestData["articleContent"])) {
			$content = trim($postRequestData["articleContent"]);
		}
		if (strlen($content) > DatabaseConstants::ARTICLE_CONTENT_MAX_LENGTH) {
			console_log("Article content is not set or null or too long.");
			return;
		}

		$result = $this->model->editArticle($articleId, $name, $content);
		if ($result === false) {
			http_response_code(500);
			return;
		}
		$articlesUrl = View::getArticlesRequestUrl();
		header("Location: $articlesUrl", 303);
	}

	private function getArticleAndVerify(int $articleId) : Article {
		$article = ($this->model->getArticle($articleId));
		if ($article !== null) {
			return $article;
		} else {
			bad_request_response_then_exit(400, 'Requested article was not found.');
		}
	}

	// Some functions for debugging.

	// GET /addTestArticles
	function createTestArticles($articleCount, $articleNumberInName) {
		for ($i = 0; $i < $articleCount; $i++) {
			$articleName = 'Article number ' . $articleNumberInName;
			$this->model->addArticle($articleName, 'Just some content of article ' . $articleNumberInName);
			$articleNumberInName++;
		}
	}
}
?>