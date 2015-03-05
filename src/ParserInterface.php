<?php
namespace HtmlParser;

/**
 * Copyright (c) 2013, 俊杰Jerry
 * All rights reserved.
 * @description: html解析器
 * @author: 俊杰Jerry<bupt1987@gmail.com>
 * @date: 2013-6-10
 * @version: 1.0
 */
interface ParserInterface {

	/**
	 * 广度优先查询
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return ParserInterface|ParserInterface[]
	 */
	public function findBreadthFirst($selector, $idx = null);


	/**
	 * 深度优先查询
	 * @param string $selector
	 * @param number $idx 找第几个,从0开始计算，null 表示都返回, 负数表示倒数第几个
	 * @return ParserInterface|ParserInterface[]
	 */
	public function findDepthFirst($selector, $idx = null);

	/**
	 * 返回文本信息
	 * @return string
	 */
	public function getPlainText();
	
	/**
	 * 获取html的元属值
	 * @param string $name
	 * @return string|null
	 */
	public function getAttr($name);

}

?>
