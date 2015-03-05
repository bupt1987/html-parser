<?php
namespace HtmlParser;

use tidy;
use tidyNode;

/**
 * Copyright (c) 2013, 俊杰Jerry
 * All rights reserved.
 *
 * @description: html解析器
 * @author     : 俊杰Jerry<bupt1987@gmail.com>
 * @date       : 2013-6-10
 */
class ParserTidy extends ParserAbstract {

	/**
	 * @param tidyNode|string $node
	 */
	public function __construct($node = null) {
		if ($node !== null) {
			if ($node instanceof tidyNode) {
				$this->node = $node;
			} else {
				$tidy = new tidy();
				$tidy->parseString($node, [], 'utf8');
				$this->node = $tidy->html();
			}
		}
	}

	public function __get($name) {
		if (isset($this->node->attribute [$name])) {
			return $this->node->attribute [$name];
		}
		if (isset($this->node->$name)) {
			return $this->node->$name;
		}
		return false;
	}

	/**
	 * 广度优先查询
	 *
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return self|self[]
	 */
	public function findBreadthFirst($selector, $idx = null) {
		if (empty($this->node->child)) {
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
			$need_to_search = $this->node->child;
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
					if (!empty($temp_val->child)) {
						foreach ($temp_val->child as $key => $val) {
							$temp_val->child[$key]->parent = $temp_val;
							$need_to_search[] = $temp_val->child[$key];
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
	}


	/**
	 * 深度优先查询
	 *
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return self|self[]
	 */
	public function findDepthFirst($selector, $idx = null) {
		if (empty($this->node->child)) {
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
		return isset($this->node->attribute [$name]) ? $this->node->attribute [$name] : null;
	}

	/**
	 * 深度查询
	 *
	 * @param     $search
	 * @param     $idx
	 * @param     $selectors
	 * @param     $level
	 * @param int $search_levle
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
		if (!empty($search->child)) {
			foreach ($search->child as $key => $val) {
				$search->child[$key]->parent = $search;
				if ($this->search($search->child[$key], $idx, $selectors, $level, $search_levle + 1)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 获取node文本
	 *
	 * @param tidyNode $node
	 * @return string
	 */
	protected function text(&$node) {
		if (isset($node->plaintext)) {
			return $node->plaintext;
		}
		$node->plaintext = '';
		switch ($node->type) {
			case TIDY_NODETYPE_TEXT :
				$node->plaintext = str_replace(array("\r", "\r\n", "\n", '&nbsp;'), ' ', $node->value);
				return $node->plaintext;
			case TIDY_NODETYPE_COMMENT :
				return $node->plaintext;
		}
		if (strcasecmp($node->name, 'script') === 0) {
			return $node->plaintext;
		}
		if (strcasecmp($node->name, 'style') === 0) {
			return $node->plaintext;
		}
		if (!empty($node->child)) {
			foreach ($node->child as $n) {
				$node->plaintext .= $this->text($n);
			}
			if ($node->name == 'span') {
				$node->plaintext .= ' ';
			}
		}
		return $node->plaintext;
	}

	/**
	 * 匹配节点,由于采取的倒序查找，所以时间复杂度为n+m*l n为总节点数，m为匹配最后一个规则的个数，l为规则的深度,
	 *
	 * @param tidyNode $search
	 * @param array    $selectors
	 * @param int      $current
	 * @return boolean|tidyNode
	 */
	protected function seek($search, $selectors, $current) {
		list ($tag, $key, $val, $exp, $no_key) = $selectors [$current];
		$pass = true;
		if ($tag === '*' && !$key) {
			exit('tag为*时，key不能为空');
		}
		if ($tag && $tag != $search->name && $tag !== '*') {
			$pass = false;
		}
		if ($pass && $key) {
			if ($no_key) {
				if (isset ($search->attribute [$key])) {
					$pass = false;
				}
			} else {
				if ($key != "plaintext" && !isset ($search->attribute [$key])) {
					$pass = false;
				}
			}
		}
		if ($pass && $key && $val && $val !== '*') {
			if ($key == "plaintext") {
				$nodeKeyValue = $this->text($search);
			} else {
				$nodeKeyValue = $search->attribute [$key];
			}
			$check = $this->match($exp, $val, $nodeKeyValue);
			if (!$check && strcasecmp($key, 'class') === 0) {
				foreach (explode(' ', $search->attribute [$key]) as $k) {
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
	 * @param tidyNode $node
	 * @return tidyNode
	 */
	protected function getParent($node) {
		if (isset($node->parent)) {
			return $node->parent;
		} else {
			return $node->getParent();
		}
	}

}

?>
