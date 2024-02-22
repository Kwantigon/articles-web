<!DOCTYPE html>
<html>
<head>
<title>Editting article</title>
<link rel="stylesheet" href="../views_article-edit.css"></link>
<body>
	<script src="../common-js-functions.js"></script>
	<form action="" method="post" id="article-edit-form">
		<label for="articleName" class="edit-form-label">Article name:</label>
		<input type="text" id="articleName" name="articleName" maxlength="32" size="30" value="<?= htmlspecialchars($articleName) ?>" required
			onkeyup="disableButtonIfEmptyName()">
		<br>
		<label for="articleContent" class="edit-form-label">Content:</label>
		<textarea id="articleContent" name="articleContent" maxlength="1024" rows="16" cols="128"><?= htmlspecialchars($articleContent) ?></textarea>
		<br>
		<div id="edit-form-buttons">
			<input type="submit" id="saveButton" value="Save" class="form-button" onclick="validateArticleName()">
			<input type="button" id="backButton" value="Back to articles" class="form-button" onclick="onClick_goBackToArticles()">
		</div>
	</form>
</body>
</html>