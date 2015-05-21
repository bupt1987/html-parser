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
class ParserDom extends ParserAbstract {

	/**
	 * @param \DOMNode|string $node
	 * @throws \Exception
	 */
	public function __construct($node = null) {
		if ($node !== null) {
			if ($node instanceof \DOMNode) {
				$this->node = $node;
			} else {
				$dom = new \DOMDocument();
				$dom->preserveWhiteSpace = false;
				$dom->strictErrorChecking = false;
				if (@$dom->loadHTML($node)) {
					$this->node = $dom;
				} else {
					throw new \Exception('load html error');
				}
			}
		}
	}

	/**
	 * 广度优先查询
	 *
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return ParserDom|ParserDom[]
	 */
	/*public function findBreadthFirst($selector, $idx = null) {
		if (empty($this->node->childNodes)) {
			return false;
		}
		$selectors = $this->parse_selector($selector);
		if (($count = count($selectors)) === 0) {
			return false;
		}
		$found = array();
		for ($c = 0; $c < $count; $c++) {
			if (($level = count($selectors [$c])) === 0) {
				return false;
			}
			$need_to_search = iterator_to_array($this->node->childNodes);
			$search_level = 1;
			while (!empty($need_to_search)) {
				$temp = array();
				foreach ($need_to_search as $search) {
					if ($search_level >= $level) {
						$rs = $this->seek($search, $selectors [$c], $level - 1);
						if ($rs !== false && $idx !== null) {
							if ($idx == count($found)) {
								return new self($rs);
							} else {
								$found[] = new self($rs);
							}
						} elseif ($rs !== false) {
							$found[] = new self($rs);
						}
					}
					$temp[] = $search;
					array_shift($need_to_search);
				}
				foreach ($temp as $temp_val) {
					if (!empty($temp_val->childNodes)) {
						foreach ($temp_val->childNodes as $val) {
							$need_to_search[] = $val;
						}
					}
				}
				$search_level++;
			}
		}
		if ($idx !== null) {
			if ($idx < 0) {
				$idx = count($found) + $idx;
			}
			if (isset($found[$idx])) {
				return $found[$idx];
			} else {
				return false;
			}
		}
		return $found;
	}*/


	/**
	 * 深度优先查询
	 *
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return self|self[]
	 */
	public function find($selector, $idx = null) {
		if (empty($this->node->childNodes)) {
			return false;
		}
		$selectors = $this->parse_selector($selector);
		if (($count = count($selectors)) === 0) {
			return false;
		}
		for ($c = 0; $c < $count; $c++) {
			if (($level = count($selectors [$c])) === 0) {
				return false;
			}
			$this->search($this->node, $idx, $selectors [$c], $level);
		}
		$found = $this->lFind;
		$this->lFind = array();
		if ($idx !== null) {
			if ($idx < 0) {
				$idx = count($found) + $idx;
			}
			if (isset($found[$idx])) {
				return $found[$idx];
			} else {
				return false;
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
		return null;
	}

	/**
	 * 深度查询
	 *
	 * @param \DOMNode $search
	 * @param          $idx
	 * @param          $selectors
	 * @param          $level
	 * @param int      $search_levle
	 * @return bool
	 */
	protected function search(&$search, $idx, $selectors, $level, $search_levle = 0) {
		if ($search_levle >= $level) {
			$rs = $this->seek($search, $selectors, $level - 1);
			if ($rs !== false && $idx !== null) {
				if ($idx == count($this->lFind)) {
					$this->lFind[] = new self($rs);
					return true;
				} else {
					$this->lFind[] = new self($rs);
				}
			} elseif ($rs !== false) {
				$this->lFind[] = new self($rs);
			}
		}
		if (!empty($search->childNodes)) {
			foreach ($search->childNodes as $val) {
				if ($this->search($val, $idx, $selectors, $level, $search_levle + 1)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 获取tidy_node文本
	 *
	 * @param \DOMNode $node
	 * @return string
	 */
	protected function text(&$node) {
		return $node->textContent;
	}

	/**
	 * 匹配节点,由于采取的倒序查找，所以时间复杂度为n+m*l n为总节点数，m为匹配最后一个规则的个数，l为规则的深度,
	 * @codeCoverageIgnore
	 * @param \DOMNode $search
	 * @param array    $selectors
	 * @param int      $current
	 * @return boolean|\DOMNode
	 */
	protected function seek($search, $selectors, $current) {
		if (!($search instanceof \DOMElement)) {
			return false;
		}
		list ($tag, $key, $val, $exp, $no_key) = $selectors [$current];
		$pass = true;
		if ($tag === '*' && !$key) {
			exit('tag为*时，key不能为空');
		}
		if ($tag && $tag != $search->tagName && $tag !== '*') {
			$pass = false;
		}
		if ($pass && $key) {
			if ($no_key) {
				if ($search->hasAttribute($key)) {
					$pass = false;
				}
			} else {
				if ($key != "plaintext" && !$search->hasAttribute($key)) {
					$pass = false;
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
				$pass = false;
			}
		}
		if ($pass) {
			$current--;
			if ($current < 0) {
				return $search;
			} elseif ($this->seek($this->getParent($search), $selectors, $current)) {
				return $search;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 获取父亲节点
	 *
	 * @param \DOMNode $node
	 * @return \DOMNode
	 */
	protected function getParent($node) {
		return $node->parentNode;
	}

}

?>
