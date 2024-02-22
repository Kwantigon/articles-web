<?php

class Article {
	private $id;
	private $name;
	private $content;

	// ID getter and setter.
	function setId($id) {
		if (is_numeric($id)) {
			$this->id = $id;
		}
	}
	function getId() {
		return $this->id;
	}

	// Name getter and setter.
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}

	// Content getter and setter.
	function getContent() {
		return $this->content;
	}
	function setContent($content) {
		$this->content = $content;
	}

	function __toString() {
		$str = "Article: [ID = $this->id; name = $this->name; content: $this->content]";
		return $str;
	}
}

?>