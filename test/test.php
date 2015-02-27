<?php
/**
 * Description:
 */
include '../src/ParserInterface.php';
include '../src/Parser.php';
include '../src/ParserDom.php';

use HtmlParser\ParserInterface;
use HtmlParser\ParserDom;
use HtmlParser\Parser;

$html = file_get_contents('http://www.sina.com.cn/');

//现在支持直接输入html字符串了
$iCount = 10;

$fStartTime = microtime(true);
for ($i = 0; $i < $iCount; $i++) {
	$dom = new ParserDom($html);
//	test($dom);
}
echo "Dom: " . (microtime(true) - $fStartTime) . "\n";


$fStartTime = microtime(true);
for ($i = 0; $i < $iCount; $i++) {
	$dom = new Parser($html);
//	test($dom);
}
echo "Tidy: " . (microtime(true) - $fStartTime) . "\n";

function test(ParserInterface $dom) {
	$dom->find('p', -1);
	$dom->find('p[id]', 0);
	$dom->find('p[id=p_id_2]', 0);
	$dom->find('p[!id]', 1);

	$dom->find('#test1', 0);

	$dom->find('p.test_class1', 0);

	$dom->find('p.test_class');
}
