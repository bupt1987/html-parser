<?php
/**
 * Description:
 */
include '../src/ParserInterface.php';
include '../src/ParserAbstract.php';
include '../src/ParserTidy.php';
include '../src/ParserDom.php';

use HtmlParser\ParserInterface;
use HtmlParser\ParserDom;
use HtmlParser\ParserTidy;

$html = file_get_contents('http://www.sina.com.cn/');

//现在支持直接输入html字符串了
$iCount = 10;

$fStartTime = microtime(true);
for ($i = 0; $i < $iCount; $i++) {
	$dom = new ParserDom($html);
	test($dom);
	echo memory_get_usage() / 1024 / 1024 . "M\n";
	echo memory_get_peak_usage() / 1024 / 1024 . "M\n";
}
echo "Dom: " . (microtime(true) - $fStartTime) . "\n";


$fStartTime = microtime(true);
for ($i = 0; $i < $iCount; $i++) {
	$dom = new ParserTidy($html);
	test($dom);
	echo memory_get_usage() / 1024 / 1024 . "M\n";
	echo memory_get_peak_usage() / 1024 / 1024 . "M\n";
}
echo "Tidy: " . (microtime(true) - $fStartTime) . "\n";

function test(ParserInterface $dom) {
	$dom->findBreadthFirst('p', -1);
	$dom->findBreadthFirst('p[id]', 0);
	$dom->findBreadthFirst('p[id=p_id_2]', 0);
	$dom->findBreadthFirst('p[!id]', 1);

	$dom->findBreadthFirst('#test1', 0);

	$dom->findBreadthFirst('p.test_class1', 0);

	$dom->findBreadthFirst('p.test_class');
}
