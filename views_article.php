<!DOCTYPE html>
<html>
	<head>
		<title>Viewing article</title>
		<link rel="stylesheet" href="../views_article.css"></link>
	</head>
<body>
	<div id="article" article-id="<?= $articleId ?>">
		<h1 id="article-name"><?= htmlspecialchars($articleName) ?></h1>
		<p id="article-content"><?= htmlspecialchars($articleContent) ?></p> <!-- htmlspecialchars() doesn't seem necessary -->
		<br>
		<div id="navigation">
			<a href="<?= View::getEditArticleRequestUrl($articleId) ?>">Edit</a>
			<a href="<?= View::getArticlesRequestUrl(); ?>">Back to articles</a>
		</div>
	</div>
	<br>
	<label for="referal-link-user-input">UTM source:</label>
	<input type="text" id="referal-link-user-input"></input>
	<br>
	<p id="generated-referal">
		&lt;a href="<span id="utm-href"></span>"&gt;<?= htmlspecialchars($articleName) ?>&lt;/a&gt;
	</p>
	<script>
		//const segments = new URL(window.location.href).pathname.split('/');
		const articleId = document.getElementById("article").getAttribute("article-id");
		let baseUrl = "https://webik.ms.mff.cuni.cz/~16291355/article/" + articleId;
		//let baseUrl = window.location.origin + segments[0] + "/article/" + articleId;

		document.getElementById("referal-link-user-input").addEventListener("keyup", () => {
			let userInput = document.getElementById("referal-link-user-input").value;
			const utmHref = document.getElementById("utm-href");
			console.log("User input: " + userInput);
			userInput = userInput.trim();
			let regex = new RegExp("^([a-z0-9]{1,64})$");
			if (!regex.test(userInput)) {
				//console.log("Invalid utm source");
				utmHref.textContent = "";
				return;
			}
			utmHref.textContent = baseUrl + "?utm_source=" + userInput;
		});
	</script>
	<?php
		if (isset($utmCounts) && count($utmCounts) > 0) {
			print("<br>");
			print("<hr>");
			print("<table>");
			print("<tr>");
			print("<th>Utm source</th>");
			print("<th>Number of accesses</th>");
			foreach ($utmCounts as $utmSource => $count) {
				print("<tr>");
				print("<th>$utmSource</th>");
				print("<th>$count</th>");
				print("</tr>");
			}
			print("</table>");
		}
	?>
</body>
</html>