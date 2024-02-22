<!DOCTYPE html>
<html>
<head>
	<title>Articles</title>
	<link rel="stylesheet" href="./views_articles.css"></link>
<head>
<body>
	<h1>Articles</h1>
	<hr>
	<ul id="articles-list">
	<?php
		console_log("Server name: " . $_SERVER['SERVER_NAME']);
		console_log("Server URI: " . $_SERVER['REQUEST_URI']);
		$articleIndex = 0;
		foreach ($articlesArray as $article) {
			//console_log($article);
			$articleId = $article->getId();
			$articleName = htmlspecialchars($article->getName());
			$articleHref = View::getArticleRequestUrl($articleId);
			$editHref = View::getEditArticleRequestUrl($articleId);
			//console_log("Article edit link: $editHref | article view link: $articleHref");
			
			print("<li class=\"article-list-item\" article-id=\"$articleId\">");
			print("<span class=\"li-article-name\">$articleName</span>");
			print('<div class="li-controls">');
			print("<a href=\"$articleHref\" class=\"show-article-anchor\">Show</a>");
			print("<a href=\"$editHref\" class=\"edit-article-anchor\">Edit</a>");
			print("<button class=\"delete-article-button\" onclick=\"sendDeleteRequest(this)\">Delete</button>");
			print('</div>');
			print("</li>");
			$articleIndex++;
		}
	?>
	</ul>
	<hr>
	<nav id="page-navigation">
		<button id="previous-btn" class="nav-button">Previous</button>
		<div>Page</div><div id="page-number"></div>
		<button id="next-btn" class="nav-button">Next</button>
		<button id="create-article-btn" class="nav-button">Create article</button>
	</nav>
	<dialog id="create-article-dialog">
		<form action="/~16291355/article-create" method="post" id="articleCreationForm">
			<label for="articleName" id="article-name-label">Article name:</label>
			<input type="text" id="articleName" name="articleName" maxlength="32" size="30"
					placeholder="Specify a non-empty name" required onkeyup="disableButtonIfEmptyName()">
			<br>
			<div id="form-buttons">
				<input type="submit" id="saveButton" value="Create" class="form-button">
				<input type="button" id="cancelButton" value="Cancel" class="form-button">
			</div>
		</form>
	</dialog>
</body>
<script src="./common-js-functions.js"></script>
<script>
	document.getElementById("create-article-btn").addEventListener("click", () => {
		const form = document.getElementById("create-article-dialog").showModal();
	});
	document.getElementById("cancelButton").addEventListener("click", () => {
		document.getElementById("create-article-dialog").close();
	});
	document.getElementById("articleName").addEventListener("keyup", () => disableButtonIfEmptyName());
	document.getElementById("saveButton").addEventListener("click", () => validateArticleName());

	const MAX_PER_PAGE = 10;
	let articlesArr = Array.from(document.getElementById("articles-list").querySelectorAll("li"));
	let maxPages = Math.ceil(articlesArr.length / MAX_PER_PAGE);
	let currentPage = 1;
	console.log("articlesArr.length = " + articlesArr.length + "; maxPages = " + maxPages);
	document.getElementById("previous-btn").addEventListener("click", () => {
		console.log("Clicked on previous.");
		console.log("currentPage = " + currentPage);
		if (currentPage > 1) {
			currentPage--;
			showPage(currentPage);
		}
	});
	document.getElementById("next-btn").addEventListener("click", () => {
		console.log("Clicked on next.");
		console.log("currentPage = " + currentPage);
		if (currentPage < maxPages) {
			currentPage++;
			showPage(currentPage);
		}
	});

	window.addEventListener("load", () => {
		showPage(1);
	});
		
	function showPage(pageNumber) {
		if (pageNumber < 1 || pageNumber > maxPages) {
			console.log("Invalid pageNumber value: " + pageNumber);
			return;
		}
		console.log("showPage: articlesArr.length = " + articlesArr.length);

		let pageLowerRange = (pageNumber - 1) * MAX_PER_PAGE;
		let pageUpperRange = pageNumber * MAX_PER_PAGE;
		console.log("pageLowerRange: " + pageLowerRange);
		console.log("pageUpperRange: " + pageUpperRange);
		articlesArr.forEach((listItem, index) => {
			listItem.classList.add("display-none");
			if (pageLowerRange <= index && index < pageUpperRange) {
				listItem.classList.remove("display-none");
			}
		});

		// Display the page number
		document.getElementById("page-number").textContent = " " + pageNumber + " out of " + maxPages;

		// Disable and enable buttons if necessary.
		let prevButton = document.getElementById("previous-btn");
		if (pageNumber === 1) {
			prevButton.classList.add("visibility-hidden");
			prevButton.setAttribute("disabled", true);
		} else {
			prevButton.classList.remove("visibility-hidden");
			prevButton.removeAttribute("disabled");
		}

		let nextButton = document.getElementById("next-btn");
		if (pageNumber === maxPages) {
			nextButton.classList.add("visibility-hidden");
			nextButton.setAttribute("disabled", true);
		} else {
			nextButton.classList.remove("visibility-hidden");
			nextButton.removeAttribute("disabled");
		}
	}

	async function sendDeleteRequest(button) {
		console.log("----- sendDeleteRequest() -----");
		console.log("articlesArr.length = " + articlesArr.length);

		const listItem = button.parentElement.parentElement; // parent is <div>; parent.parent is <li>
		//console.log("button.parentElement = " + listItem);
		const articleId = listItem.getAttribute("article-id");
		const articleIndex = articlesArr.indexOf(listItem);
    	console.log("sendDeleteRequest was called for article with ID " + articleId + " at index " + articleIndex);
    	let url = "/~16291355/article/" + articleId;
    	console.log("URL for deletion: " + url);
		articlesArr[articleIndex].classList.add("display-none");
		let oldArticlesArr = articlesArr;
		articlesArr = oldArticlesArr.toSpliced(articleIndex, 1);
    	const deleteResult = await fetch(url, {method: "DELETE"})
                    	       		.then(response => {
                	           		    if (!response.ok) {
											window.alert("There was an error when trying to delete the article");
											articlesArr = oldArticlesArr;
											maxPages = Math.ceil(articlesArr.length / MAX_PER_PAGE);
											if (currentPage > maxPages) {
												currentPage = maxPages;
											}
											showPage(currentPage);
										}
            	               		})
        	                   		.catch(function (err) {
    	                       		    console.log("There was an error when trying to delete the article.");
                            		    console.log(err);
                            		});
		
		// Update the list being shown to the user.
		maxPages = Math.ceil(articlesArr.length / MAX_PER_PAGE);
		if (currentPage > maxPages) {
			currentPage = maxPages;
		}
		showPage(currentPage);
	}

	function deleteArticleButtonHandler(button) {
		console.log("----------");
		console.log("Delete article button handler");
		const listItem = button.parentElement;
		const articleId = listItem.getAttribute("article-id");
		const articleIndex = articlesArr.indexOf(listItem);
		console.log("Should delete article with ID " + articleId + " at index " + articleIndex);
		console.log("----------");
	}
</script>
</html>