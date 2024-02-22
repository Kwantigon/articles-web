<?php

Class View {
	function renderArticlePreview($articlesArray) {
		require(ViewConstants::ARTICLES_PAGE_TEMPLATE);
	}

	function renderArticle($article) {
		$articleId = $article->getId();
		$articleName = $article->getName();
		$articleContent = $article->getContent();
		require(ViewConstants::ARTICLE_DISPLAY_TEMPLATE);
	}

	function renderArticleWithUtmSources($article, $utmCounts) {
		$articleId = $article->getId();
		$articleName = $article->getName();
		$articleContent = $article->getContent();
		require(ViewConstants::ARTICLE_DISPLAY_TEMPLATE);
	}

	function renderEditArticle($article) {
		$articleId = $article->getId();
		$articleName = $article->getName();
		$articleContent = $article->getContent();
		require(ViewConstants::ARTICLE_EDIT_TEMPLATE);
	}

	function renderArticleCreationForm() {
		require(ViewConstants::ARTICLE_CREATION_FORM);
	}

	static function getBaseRequestUrl() {
		return explode('/', $_SERVER['REQUEST_URI'])[1];
	}

	static function getArticlesRequestUrl() {
		$baseRequestUrl = View::getBaseRequestUrl();
		return '/' . $baseRequestUrl . '/' . Constants::ARTICLES_URL;
	}

	static function getArticleRequestUrl($articleId) {
		$baseRequestUrl = View::getBaseRequestUrl();
		return '/' . $baseRequestUrl . '/' . Constants::ARTICLE_URL . '/' . $articleId;
	}

	static function getEditArticleRequestUrl($articleId) {
		$baseRequestUrl = View::getBaseRequestUrl();
		return '/' . $baseRequestUrl . '/' . Constants::ARTICLE_EDIT_URL . '/' . $articleId;
	}

	static function getCreateArticleRequestUrl() {
		$baseRequestUrl = View::getBaseRequestUrl();
		return '/' . $baseRequestUrl . '/' . Constants::ARTICLE_CREATE_URL;
	}
}

Class ViewConstants {
	const ARTICLES_PER_PAGE = 10;
	const ARTICLES_PAGE_TEMPLATE = './views_articles.php';
	const ARTICLE_DISPLAY_TEMPLATE = './views_article.php';
	const ARTICLE_EDIT_TEMPLATE = './views_article-edit.php';
	const ARTICLE_CREATION_FORM = './views_article-create.html';
}

?>