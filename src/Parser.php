<?php
namespace HtmlParser;

use tidyNode;
use tidy;
/**
 * Copyright (c) 2013, 俊杰Jerry
 * All rights reserved.
 * @description: html解析器
 * @author: 俊杰Jerry<bupt1987@gmail.com>
 * @date: 2013-6-10
 * @version: 1.0
 */
class Parser implements ParserInterface {

	private $tidy_node = null;
	private $find_rs = array();

	/**
	 * @param tidyNode|string $tidy_node
	 * @param $config_options
	 * @param $encoding
	 */
	public function __construct($tidy_node = null, $config_options = array(), $encoding = 'utf8'){
		if($tidy_node !== null){
			if($tidy_node instanceof tidyNode){
				$this->tidy_node = $tidy_node;
			}else{
				$tidy = new tidy();
				$tidy->parseString($tidy_node, $config_options, $encoding);
				$this->tidy_node = $tidy->html();
			}
		}
	}

	public function __destruct(){
		$this->clearNode($this->tidy_node);
	}

	public function __get($name){
		if(isset($this->tidy_node->attribute [$name])){
			return $this->tidy_node->attribute [$name];
		}
		if(isset($this->tidy_node->$name)){
			return $this->tidy_node->$name;
		}
		return false;
	}

	/**
	 * 广度优先查询
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return self|self[]
	 */
	public function find($selector, $idx = null){
		if(empty($this->tidy_node->child)){
			return false;
		}
		$selectors = $this->parse_selector ( $selector );
		if (($count = count ( $selectors )) === 0){
			return false;
		}
		$found = array();
		for($c = 0; $c < $count; $c ++) {
			if (($level = count ( $selectors [$c] )) === 0){
				return false;
			}
			$need_to_search = $this->tidy_node->child;
			$search_level = 1;
			while (!empty($need_to_search)){
				$temp = array();
				foreach ($need_to_search as $search){
					if($search_level >= $level){
						$rs = $this->seek ($search, $selectors [$c], $level - 1 );
						if($rs !== false && $idx !== null){
							if($idx == count($found)){
								return new self($rs);
							}else{
								$found[] = new self($rs);
							}
						}elseif($rs !== false){
							$found[] = new self($rs);
						}
					}
					$temp[] = $search;
					array_shift($need_to_search);
				}
				foreach ($temp as $temp_val){
					if(!empty($temp_val->child)){
						foreach ($temp_val->child as $key => $val){
							$temp_val->child[$key]->parent = $temp_val;
							$need_to_search[] = $temp_val->child[$key];
						}
					}
				}
				$search_level++;
			}
		}
		if($idx !== null){
			if($idx < 0){
				$idx = count($found) + $idx;
			}
			if(isset($found[$idx])){
				return $found[$idx];
			}else{
				return false;
			}
		}
		return $found;
	}


	/**
	 * 深度优先查询
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return self|self[]
	 */
	public function find2($selector, $idx = null){
		if(empty($this->tidy_node->child)){
			return false;
		}
		$selectors = $this->parse_selector ( $selector );
		if (($count = count ( $selectors )) === 0){
			return false;
		}
		for($c = 0; $c < $count; $c ++) {
			if (($level = count ( $selectors [$c] )) === 0){
				return false;
			}
			$this->search($this->tidy_node, $idx, $selectors [$c], $level);
		}
		$found = $this->find_rs;
		$this->find_rs = array();
		if($idx !== null){
			if($idx < 0){
				$idx = count($found) + $idx;
			}
			if(isset($found[$idx])){
				return $found[$idx];
			}else{
				return false;
			}
		}
		return $found;
	}

	/**
	 * 返回文本信息
	 * @return string
	 */
	public function getPlainText(){
		return $this->text($this->tidy_node);
	}
	
	/**
	 * 获取html的元属值
	 * @param string $name
	 * @return string|null
	 */
	public function getAttr($name) {
		return isset($this->tidy_node->attribute [$name]) ? $this->tidy_node->attribute [$name] : null;
	}

	/**
	 * 深度查询
	 * @param $search
	 * @param $idx
	 * @param $selectors
	 * @param $level
	 * @param int $search_levle
	 * @return bool
	 */
	private function search(&$search, $idx, $selectors, $level, $search_levle = 0){
		if($search_levle >= $level){
			$rs = $this->seek ($search, $selectors , $level - 1 );
			if($rs !== false && $idx !== null){
				if($idx == count($this->find_rs)){
					$this->find_rs[] = new self($rs);
					return true;
				}else{
					$this->find_rs[] = new self($rs);
				}
			}elseif($rs !== false){
				$this->find_rs[] = new self($rs);
			}
		}
		if(!empty($search->child)){
			foreach ($search->child as $key => $val){
				$search->child[$key]->parent = $search;
				if($this->search($search->child[$key], $idx, $selectors, $level, $search_levle + 1)){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 获取tidy_node文本
	 * @param tidyNode $tidy_node
	 * @return string
	 */
	private function text(&$tidy_node){
		if(isset($tidy_node->plaintext)){
			return $tidy_node->plaintext;
		}
		$tidy_node->plaintext = '';
		switch ($tidy_node->type) {
			case TIDY_NODETYPE_TEXT :
				$tidy_node->plaintext = str_replace(array("\r","\r\n","\n",'&nbsp;'), ' ', $tidy_node->value);
				return $tidy_node->plaintext;
			case TIDY_NODETYPE_COMMENT :
				return $tidy_node->plaintext;
		}
		if (strcasecmp ( $tidy_node->name, 'script' ) === 0){
			return $tidy_node->plaintext;
		}
		if (strcasecmp ( $tidy_node->name, 'style' ) === 0){
			return $tidy_node->plaintext;
		}
		if (!empty( $tidy_node->child )) {
			foreach ( $tidy_node->child as $n ) {
				$tidy_node->plaintext .= $this->text($n);
			}
			if ($tidy_node->name == 'span') {
				$tidy_node->plaintext .= ' ';
			}
		}
		return $tidy_node->plaintext;
	}

	/**
	 * 匹配节点,由于采取的倒序查找，所以时间复杂度为n+m*l n为总节点数，m为匹配最后一个规则的个数，l为规则的深度,
	 * @param tidyNode $search
	 * @param array $selectors
	 * @param int $current
	 * @return boolean|tidyNode
	 */
	private function seek(tidyNode $search, $selectors, $current) {
		list ( $tag, $key, $val, $exp, $no_key ) = $selectors [$current];
		$pass = true;
		if ($tag === '*' && !$key) {
			exit('tag为*时，key不能为空');
		}
		if ($tag && $tag != $search->name && $tag !== '*') {
			$pass = false;
		}
		if ($pass && $key) {
			if ($no_key) {
				if (isset ( $search->attribute [$key] )) {
					$pass = false;
				}
			} else {
				if ($key != "plaintext" && ! isset ( $search->attribute [$key] )) {
					$pass = false;
				}
			}
		}
		if ($pass && $key && $val && $val !== '*') {
			if ($key == "plaintext") {
				$nodeKeyValue = $this->text ($search);
			} else {
				$nodeKeyValue = $search->attribute [$key];
			}
			$check = $this->match ( $exp, $val, $nodeKeyValue );
			if (! $check && strcasecmp ( $key, 'class' ) === 0) {
				foreach ( explode ( ' ', $search->attribute [$key] ) as $k ) {
					if (! empty ( $k )) {
						$check = $this->match ( $exp, $val, $k );
						if ($check) {
							break;
						}
					}
				}
			}
			if (! $check) {
				$pass = false;
			}
		}
		if ($pass) {
			$current --;
			if($current < 0){
				return $search;
			}elseif($this->seek ( $this->getParent($search), $selectors,  $current)) {
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
	 * @param tidyNode $node
	 * @return tidyNode
	 */
	private function getParent(tidyNode $node){
		if(isset($node->parent)){
			return $node->parent;
		}else{
			return $node->getParent();
		}
	}

	/**
	 * 匹配
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
				return preg_match ( "/^" . preg_quote ( $pattern, '/' ) . "/", $value );
			case '$=' :
				return preg_match ( "/" . preg_quote ( $pattern, '/' ) . "$/", $value );
			case '*=' :
				if ($pattern [0] == '/') {
					return preg_match ( $pattern, $value );
				}
				return preg_match ( "/" . $pattern . "/i", $value );
		}
		return false;
	}

	/**
	 * 分析查询语句
	 * @param string $selector_string
	 * @return array
	 */
	private function parse_selector($selector_string) {
		$pattern = '/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)["\']?(.*?)["\']?)?\])?([\/, ]+)/is';
		preg_match_all ( $pattern, trim ( $selector_string ) . ' ', $matches, PREG_SET_ORDER );
		$selectors = array ();
		$result = array ();
		foreach ( $matches as $m ) {
			$m [0] = trim ( $m [0] );
			if ($m [0] === '' || $m [0] === '/' || $m [0] === '//')
				continue;
			if ($m [1] === 'tbody')
				continue;
			list ( $tag, $key, $val, $exp, $no_key ) = array ($m [1], null, null, '=',false);
			if (! empty ( $m [2] )) {
				$key = 'id';
				$val = $m [2];
			}
			if (! empty ( $m [3] )) {
				$key = 'class';
				$val = $m [3];
			}
			if (! empty ( $m [4] )) {
				$key = $m [4];
			}
			if (! empty ( $m [5] )) {
				$exp = $m [5];
			}
			if (! empty ( $m [6] )) {
				$val = $m [6];
			}
			// convert to lowercase
			$tag = strtolower ( $tag );
			$key = strtolower ( $key );
			// elements that do NOT have the specified attribute
			if (isset ( $key [0] ) && $key [0] === '!') {
				$key = substr ( $key, 1 );
				$no_key = true;
			}
			$result [] = array ($tag, $key, $val, $exp, $no_key);
			if (trim ( $m [7] ) === ',') {
				$selectors [] = $result;
				$result = array ();
			}
		}
		if (count ( $result ) > 0){
			$selectors [] = $result;
		}
		return $selectors;
	}

	/**
	 * 释放内存
	 * @param $tidyNode
	 */
	private function clearNode(&$tidyNode) {
		if(!empty($tidyNode->child)) {
			foreach($tidyNode->child as $child) {
				$this->clearNode($child);
			}
		}
		unset($tidyNode);
	}

}

?>
