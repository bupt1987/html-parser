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
class ParserFactory {

	const TYPE_DOM = 'Dom';
	const TYPE_TIDY = 'Tidy';

	/**
	 * @param        $html
	 * @param string $sType must be Dom or Tidy
	 * @return ParserInterface
	 */
	public static function createParser($html, $sType = self::TYPE_DOM) {
		$sClassName = '\HtmlParser\Parser' . $sType;
		return new $sClassName($html);
	}

}

?>
