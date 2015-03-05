<?php
/**
 * Description:
 */

namespace HtmlParser;

abstract class ParserAbstract implements ParserInterface {

	protected $node = null;
	protected $lFind = array();

	public function __destruct() {
		$this->clearNode($this->node);
	}

	/**
	 * 匹配
	 *
	 * @param string $exp
	 * @param string $pattern
	 * @param string $value
	 * @return boolean|number
	 */
	protected function match($exp, $pattern, $value) {
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
		return false;
	}

	/**
	 * 分析查询语句
	 *
	 * @param string $selector_string
	 * @return array
	 */
	protected function parse_selector($selector_string) {
		$pattern = '/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)["\']?(.*?)["\']?)?\])?([\/, ]+)/is';
		preg_match_all($pattern, trim($selector_string) . ' ', $matches, PREG_SET_ORDER);
		$selectors = array();
		$result = array();
		foreach ($matches as $m) {
			$m [0] = trim($m [0]);
			if ($m [0] === '' || $m [0] === '/' || $m [0] === '//')
				continue;
			if ($m [1] === 'tbody')
				continue;
			list ($tag, $key, $val, $exp, $no_key) = array($m [1], null, null, '=', false);
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
				$no_key = true;
			}
			$result [] = array($tag, $key, $val, $exp, $no_key);
			if (trim($m [7]) === ',') {
				$selectors [] = $result;
				$result = array();
			}
		}
		if (count($result) > 0) {
			$selectors [] = $result;
		}
		return $selectors;
	}

	/**
	 * 释放内存
	 *
	 * @param $node
	 */
	protected function clearNode(&$node) {
		if (!empty($node->child)) {
			foreach ($node->child as $child) {
				$this->clearNode($child);
			}
		}
		unset($node);
	}

	/**
	 * 获取父亲节点
	 *
	 * @param  $node
	 * @return object
	 */
	protected abstract function getParent($node);

	/**
	 * 获取node文本
	 *
	 * @param  $node
	 * @return string
	 */
	protected abstract function text(&$node);

	/**
	 * 匹配节点,由于采取的倒序查找，所以时间复杂度为n+m*l n为总节点数，m为匹配最后一个规则的个数，l为规则的深度,
	 *
	 * @param             $search
	 * @param array       $selectors
	 * @param int         $current
	 * @return boolean|object
	 */
	protected abstract function seek($search, $selectors, $current);

	/**
	 * 深度查询
	 *
	 * @param          $search
	 * @param          $idx
	 * @param          $selectors
	 * @param          $level
	 * @param int      $search_levle
	 * @return bool
	 */
	protected abstract function search(&$search, $idx, $selectors, $level, $search_levle = 0);

}
