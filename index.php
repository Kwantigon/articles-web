<?php
ini_set('display_errors', 1);
error_reporting(-1);
require('./model.php');
require('./view.php');
require('./controller.php');
require('./ArticleClass.php');
require('./Constants.php');

function bad_request_response_then_exit($responseCode, $errorMessage) {
	// Should rename this to 'error_response_then_exit'
	http_response_code($responseCode);
	print($errorMessage . "<br>");
	exit();
	//header("Location: ~")
}

function console_log($message) {
	echo("<script>console.log(\"$message\");</script>");
}

function get_query_string() {
	//var_dump($_GET);
	//print("<br>");
	if (isset($_GET['page'])) {
		return htmlspecialchars($_GET['page']);
	} else {
		//print('404: Invalid query value.');
	}
}

function main() {
	console_log("index.php::main() started");
	$controller = new Controller();
	//
	/*$modelDir = __DIR__ . '/model';
	$viewsDir = __DIR__ . '/views';
	console_log("Model dir: $modelDir | Views dir: $viewsDir<br>");*/

	$queryString = get_query_string();
	//print("queryString = $queryString<br>");
	console_log("queryString: $queryString");
	$queryFields = explode('/', $queryString);
	//var_dump($queryFields);
	//print("<br>");
	$requestedPage = $queryFields[0];
	console_log("requestedPage: $requestedPage");

	switch ($requestedPage) {
		case Constants::ARTICLES_URL:
			$controller->serveRequest_getArticles();
			break;
		case Constants::ARTICLE_URL:
			$articleId = $queryFields[1];
			//print("Article id = $articleId<br>");
			$utmSource = null;
			if (isset($_GET[Constants::UTM_SOURCE])) {
				$utmSource = $_GET[Constants::UTM_SOURCE];
			}
			if (!isset($utmSource) || !preg_match("/^[a-z0-9]{1,64}$/", $utmSource)) {
				$utmSource = null;
			}
			if ($articleId == null) {
				bad_request_response_then_exit(400, 'Article ID is missing.');
			}
			console_log("Requested article with ID $articleId");
			switch ($_SERVER['REQUEST_METHOD']) {
				case 'GET':
					//$controller->serveRequest_getArticle($articleId);
					$controller->serveRequest_getArticleWithUtmSource($articleId, $utmSource);
					break;
				case 'DELETE':
					$controller->serveRequest_deleteArticle($articleId);
					break;
				default:
					$requestMethod = $_SERVER['REQUEST_METHOD'];
					bad_request_response_then_exit(400,"Unsupported request method: $requestMethod");
			}
			break;
		case Constants::ARTICLE_CREATE_URL:
			switch ($_SERVER['REQUEST_METHOD']) {
				case 'GET':
					$controller->serveRequest_getCreateArticle();
					break;
				case 'POST':
					$controller->serveRequest_createNewArticle($_POST);
					break;
				default:
					$requestMethod = $_SERVER['REQUEST_METHOD'];
					bad_request_response_then_exit(400,"Unsupported request method: $requestMethod");
			}
			break;
		case Constants::ARTICLE_EDIT_URL:
			$articleId = $queryFields[1];
			if ($articleId == null) {
				bad_request_response_then_exit(400, 'Article ID is missing.');
			}
			console_log("Requested editing of article with ID $articleId.");

			switch ($_SERVER["REQUEST_METHOD"]) {
				case 'GET':
					$controller->serveRequest_getArticleEdit($articleId);
					break;
				case 'POST':
					// When the "SAVE" button is pressed in the article-edit page.
					$controller->serveRequest_postArticleEdit($articleId, $_POST);
					break;
				default:
					$requestMethod = $_SERVER['REQUEST_METHOD'];
					bad_request_response_then_exit(400,"Unsupported request method: $requestMethod");
			}
			break;
		case 'addTestArticles': // For debugging
			$articleCount = $queryFields[1];
			$numberFrom = $queryFields[2];
			console_log("Adding test articles - count = $articleCount; from = $numberFrom");
			$controller->createTestArticles($articleCount, $numberFrom);
			break;
		default:
			bad_request_response_then_exit(404, "Page /$requestedPage not found.");
			break;
	}
}


main();
?>