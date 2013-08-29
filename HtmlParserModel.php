<?php
/**
 * Copyright (c) 2013, 俊杰Jerry
 * All rights reserved.
 * @description: html解析器
 * @author: 俊杰Jerry<bupt1987@gmail.com>
 * @date: 2013-6-10
 * @version: 1.0
 */
class HtmlParserModel {
	
	private $tidy_node = null;
	private $find_rs = array();
	
	public function __construct($tidy_node = null){
		if(!function_exists('tidy_parse_string')){
			exit('tidy模块未加载');
			//此处使用dagger框架
			//throw new BaseModelException('tidy模块未加载', 92000);
		}
		if($tidy_node !== null){
		    if($tidy_node instanceof tidyNode){
		        $this->tidy_node = $tidy_node;
		    }else{
		       $this->parseStr($tidy_node); 
		    }
		}
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
	 * 使用tidy解析html
	 * @param string $str
	 * @param array $option
	 * @param string $encoding
	 */
	public function parseStr($str, $config_options = array(), $encoding = 'utf8'){
		//此处使用dagger框架
		//defined('DAGGER_DEBUG') && $start_time = microtime(true);
		$str = $this->remove_html ( $str, "'<!--(.*?)-->'is" );
// 		$str = $this->remove_html ( $str, "'<!\[CDATA\[(.*?)\]\]>'is" );
		$str = $this->remove_html ( $str, "'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is" );
		$str = $this->remove_html ( $str, "'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is" );
		$str = $this->remove_html ( $str, "'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is" );
		$str = $this->remove_html ( $str, "'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is" );
// 		$str = $this->remove_html ( $str, "'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is" );
/* 		$str = $this->remove_html ( $str, "'(<\?)(.*?)(\?>)'s" );
 		$str = $this->remove_html ( $str, "'(\{\w)(.*?)(\})'s" );*/
		$tidy = tidy_parse_string($str, $config_options, $encoding);
		$this->tidy_node = tidy_get_html($tidy);
		//此处使用dagger框架
		//defined('DAGGER_DEBUG') && BaseModelCommon::debug((round(microtime(true) - $start_time, 6) * 1000) . 'ms', 'html_parser_time');
	}
	
	/**
	 * 广度优先查询
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return multitype:|HtmlParserModel|multitype:array
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
								return new HtmlParserModel($rs);
							}else{
								$found[] = new HtmlParserModel($rs);
							}
						}elseif($rs !== false){
							$found[] = new HtmlParserModel($rs);
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
	 * @return multitype:|HtmlParserModel|multitype:array
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
	 * @return Ambigous <string, mixed, string>
	 */
	public function getPlainText(){
		return $this->text($this->tidy_node);
	}
	
	/**
	 * 深度查询
	 * @param tidyNode $search
	 * @param number|null $idx
	 * @param array $selectors
	 * @param number $level
	 * @param number $search_levle
	 * @return boolean
	 */
	private function search(&$search, $idx, $selectors, $level, $search_levle = 0){
		if($search_levle >= $level){
			$rs = $this->seek ($search, $selectors , $level - 1 );
			if($rs !== false && $idx !== null){
				if($idx == count($this->find_rs)){
					$this->find_rs[] = new HtmlParserModel($rs);
					return true;
				}else{
					$this->find_rs[] = new HtmlParserModel($rs);
				}
			}elseif($rs !== false){
				$this->find_rs[] = new HtmlParserModel($rs);
			}
		}
		if(!empty($search->child)){
			foreach ($search->child as $key => $val){
				$search->child[$key]->parent = $search;
				if($this->search($search->child[$key], $idx, $selectors, $level, $search_levle + 1)){
					return true;
				}
			}
		}else{
			return false;
		}
	}
	
	/**
	 * 获取tidy_node文本
	 * @param tidyNode $tidy_node
	 * @return mixed|string|Ambigous <string, mixed, string>
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
			foreach ( $tidy_node->child as $key => $n ) {
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
			//此处使用dagger框架
			//throw new BaseModelException('tag为*时，key不能为空', 92000);
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
	 * 删除不用的html代码
	 * @param string $str
	 * @param string $pattern
	 * @param bool $remove_tag
	 */
	private function remove_html($str, $pattern) {
		$count = preg_match_all ( $pattern, $str, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
		for($i = $count - 1; $i > - 1; -- $i) {
			$str = substr_replace ( $str, '', $matches [$i] [0] [1], strlen ( $matches [$i] [0] [0] ) );
		}
		return $str;
	}
	
}

?>
