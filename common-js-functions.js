class Constants {
    static BASE_URL = "/~16291355";
    static ARTICLES_URL = this.BASE_URL + "/articles";
    static ARTICLE_URL = this.BASE_URL + "/article/"; // An article ID is expected.
}

function disableButtonIfEmptyName() {
    //console.log("Article name: " + document.getElementById("articleName").value);
    let name = document.getElementById("articleName").value.trim();
    if (name === "") {
        console.log("The name field is empty.");
        document.getElementById("saveButton").disabled = true;
    } else {
        document.getElementById("saveButton").disabled = false;
    }
}

function validateArticleName() {
    let name = document.getElementById("articleName").value.trim();
    console.log("Article name: " + name);
    if (name === null || name === "") {
        event.preventDefault();
        console.log("The name field is empty.");
        window.alert("The article name must be filled in");
    } else {
        console.log("Data should be sent.");
        /*
        const formData = new FormData(form);
        let name = formData.get("articleName");
        const segments = new URL(window.location.href).pathname.split('/');
        let articleId = segments.pop();
        if (articleId === "") {
            articleId = segments.pop();
        }
        console.log("Article ID is " + articleId);
        let url = "../article-edit/" + articleId;
        const response = fetch(url, {method: "POST", body: formData, redirect: "follow"})
                            .then(response => {
                                console.log("Location: " + window.location.href);
                                console.log(response);
                                window.location.href = "../articles";
                            })
                            .catch(function (err) {
                                console.log("There was an error when trying to save the edited article.");
                                console.log(err);
                            });

            https://stackoverflow.com/a/65413762
            Do not use fetch if I want to submit the form and then redirect.
            Fetch will only get the response directly in the current page.
            Instead, do validation and then, if there are no errors,
            let the function return and the submit button will send a POST request.
        */
    }
}

function onClick_goBackToArticles() {
    console.log('You clicked on the button \"Back to articles.\"');
    window.location.href = Constants.ARTICLES_URL;
}