<?php
namespace HtmlParser;

/**
 * Copyright (c) 2013, 俊杰Jerry
 * All rights reserved.
 *
 * @description: html解析器
 * @author     : 俊杰Jerry<bupt1987@gmail.com>
 * @date       : 2013-6-10
 */
class ParserDom {

	/**
	 * @var \DOMNode
	 */
	public $node;

	/**
	 * @var array
	 */
	private $_lFind = [];

	/**
	 * @param \DOMNode|string $node
	 * @throws \Exception
	 */
	public function __construct($node = NULL) {
		if ($node !== NULL) {
			if ($node instanceof \DOMNode) {
				$this->node = $node;
			} else {
				$dom = new \DOMDocument();
				$dom->preserveWhiteSpace = FALSE;
				$dom->strictErrorChecking = FALSE;
				if (@$dom->loadHTML($node)) {
					$this->node = $dom;
				} else {
					throw new \Exception('load html error');
				}
			}
		}
	}

	/**
	 * 初始化的时候可以不用传入html，后面可以多次使用
	 * @param null $node
	 * @throws \Exception
	 */
	public function load($node = NULL) {
		if ($node instanceof \DOMNode) {
			$this->node = $node;
		} else {
			$dom = new \DOMDocument();
			$dom->preserveWhiteSpace = FALSE;
			$dom->strictErrorChecking = FALSE;
			if (@$dom->loadHTML($node)) {
				$this->node = $dom;
			} else {
				throw new \Exception('load html error');
			}
		}
	}

	/**
	 * @codeCoverageIgnore
	 * @param string $name
	 * @return mixed
	 */
	function __get($name) {
		switch ($name) {
			case 'outertext':
				return $this->outerHtml();
			case 'innertext':
				return $this->innerHtml();
			case 'plaintext':
				return $this->getPlainText();
			case 'href':
				return $this->getAttr("href");
			case 'src':
				return $this->getAttr("src");
			default:
				return NULL;
		}
	}

	/**
	 * 深度优先查询
	 *
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return self|self[]
	 */
	public function find($selector, $idx = NULL) {
		if (empty($this->node->childNodes)) {
			return FALSE;
		}
		$selectors = $this->parse_selector($selector);
		if (($count = count($selectors)) === 0) {
			return FALSE;
		}
		for ($c = 0; $c < $count; $c++) {
			if (($level = count($selectors [$c])) === 0) {
				return FALSE;
			}
			$this->search($this->node, $idx, $selectors [$c], $level);
		}
		$found = $this->_lFind;
		$this->_lFind = [];
		if ($idx !== NULL) {
			if ($idx < 0) {
				$idx = count($found) + $idx;
			}
			if (isset($found[$idx])) {
				return $found[$idx];
			} else {
				return FALSE;
			}
		}
		return $found;
	}

	/**
	 * 返回文本信息
	 *
	 * @return string
	 */
	public function getPlainText() {
		return $this->text($this->node);
	}

	/**
	 * 获取innerHtml
	 * @return string
	 */
	public function innerHtml() {
		$innerHTML = "";
		$children = $this->node->childNodes;
		foreach ($children as $child) {
			$innerHTML .= $this->node->ownerDocument->saveHTML($child) ?: '';
		}
		return $innerHTML;
	}

	/**
	 * 获取outerHtml
	 * @return string|bool
	 */
	public function outerHtml() {
		$doc = new \DOMDocument();
		$doc->appendChild($doc->importNode($this->node, TRUE));
		return $doc->saveHTML($doc);
	}


	/**
	 * 获取html的元属值
	 *
	 * @param string $name
	 * @return string|null
	 */
	public function getAttr($name) {
		$oAttr = $this->node->attributes->getNamedItem($name);
		if (isset($oAttr)) {
			return $oAttr->nodeValue;
		}
		return NULL;
	}

	/**
	 * 匹配
	 *
	 * @param string $exp
	 * @param string $pattern
	 * @param string $value
	 * @return boolean|number
	 */
	private function match($exp, $pattern, $value) {
		$pattern = strtolower($pattern);
		$value = strtolower($value);
		switch ($exp) {
			case '=' :
				return ($value === $pattern);
			case '!=' :
				return ($value !== $pattern);
			case '^=' :
				return preg_match("/^" . preg_quote($pattern, '/') . "/", $value);
			case '$=' :
				return preg_match("/" . preg_quote($pattern, '/') . "$/", $value);
			case '*=' :
				if ($pattern [0] == '/') {
					return preg_match($pattern, $value);
				}
				return preg_match("/" . $pattern . "/i", $value);
		}
		return FALSE;
	}

	/**
	 * 分析查询语句
	 *
	 * @param string $selector_string
	 * @return array
	 */
	private function parse_selector($selector_string) {
		$pattern = '/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)["\']?(.*?)["\']?)?\])?([\/, ]+)/is';
		preg_match_all($pattern, trim($selector_string) . ' ', $matches, PREG_SET_ORDER);
		$selectors = [];
		$result = [];
		foreach ($matches as $m) {
			$m [0] = trim($m [0]);
			if ($m [0] === '' || $m [0] === '/' || $m [0] === '//')
				continue;
			if ($m [1] === 'tbody')
				continue;
			list ($tag, $key, $val, $exp, $no_key) = [$m [1], NULL, NULL, '=', FALSE];
			if (!empty ($m [2])) {
				$key = 'id';
				$val = $m [2];
			}
			if (!empty ($m [3])) {
				$key = 'class';
				$val = $m [3];
			}
			if (!empty ($m [4])) {
				$key = $m [4];
			}
			if (!empty ($m [5])) {
				$exp = $m [5];
			}
			if (!empty ($m [6])) {
				$val = $m [6];
			}
			// convert to lowercase
			$tag = strtolower($tag);
			$key = strtolower($key);
			// elements that do NOT have the specified attribute
			if (isset ($key [0]) && $key [0] === '!') {
				$key = substr($key, 1);
				$no_key = TRUE;
			}
			$result [] = [$tag, $key, $val, $exp, $no_key];
			if (trim($m [7]) === ',') {
				$selectors [] = $result;
				$result = [];
			}
		}
		if (count($result) > 0) {
			$selectors [] = $result;
		}
		return $selectors;
	}

	/**
	 * 深度查询
	 *
	 * @param \DOMNode $search
	 * @param          $idx
	 * @param          $selectors
	 * @param          $level
	 * @param int $search_level
	 * @return bool
	 */
	private function search(&$search, $idx, $selectors, $level, $search_level = 0) {
		if ($search_level >= $level) {
			$rs = $this->seek($search, $selectors, $level - 1);
			if ($rs !== FALSE && $idx !== NULL) {
				if ($idx == count($this->_lFind)) {
					$this->_lFind[] = new self($rs);
					return TRUE;
				} else {
					$this->_lFind[] = new self($rs);
				}
			} elseif ($rs !== FALSE) {
				$this->_lFind[] = new self($rs);
			}
		}
		if (!empty($search->childNodes)) {
			foreach ($search->childNodes as $val) {
				if ($this->search($val, $idx, $selectors, $level, $search_level + 1)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * 获取tidy_node文本
	 *
	 * @param \DOMNode $node
	 * @return string
	 */
	private function text(&$node) {
		return $node->textContent;
	}

	/**
	 * 匹配节点,由于采取的倒序查找，所以时间复杂度为n+m*l n为总节点数，m为匹配最后一个规则的个数，l为规则的深度,
	 * @codeCoverageIgnore
	 * @param \DOMNode $search
	 * @param array $selectors
	 * @param int $current
	 * @return boolean|\DOMNode
	 */
	private function seek($search, $selectors, $current) {
		if (!($search instanceof \DOMElement)) {
			return FALSE;
		}
		list ($tag, $key, $val, $exp, $no_key) = $selectors [$current];
		$pass = TRUE;
		if ($tag === '*' && !$key) {
			exit('tag为*时，key不能为空');
		}
		if ($tag && $tag != $search->tagName && $tag !== '*') {
			$pass = FALSE;
		}
		if ($pass && $key) {
			if ($no_key) {
				if ($search->hasAttribute($key)) {
					$pass = FALSE;
				}
			} else {
				if ($key != "plaintext" && !$search->hasAttribute($key)) {
					$pass = FALSE;
				}
			}
		}
		if ($pass && $key && $val && $val !== '*') {
			if ($key == "plaintext") {
				$nodeKeyValue = $this->text($search);
			} else {
				$nodeKeyValue = $search->getAttribute($key);
			}
			$check = $this->match($exp, $val, $nodeKeyValue);
			if (!$check && strcasecmp($key, 'class') === 0) {
				foreach (explode(' ', $search->getAttribute($key)) as $k) {
					if (!empty ($k)) {
						$check = $this->match($exp, $val, $k);
						if ($check) {
							break;
						}
					}
				}
			}
			if (!$check) {
				$pass = FALSE;
			}
		}
		if ($pass) {
			$current--;
			if ($current < 0) {
				return $search;
			} elseif ($this->seek($this->getParent($search), $selectors, $current)) {
				return $search;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * 获取父亲节点
	 *
	 * @param \DOMNode $node
	 * @return \DOMNode
	 */
	private function getParent($node) {
		return $node->parentNode;
	}

}
