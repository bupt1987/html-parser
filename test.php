<?php
require 'vendor/autoload.php';

$url = 'http://www.sina.com.cn/';
$sHtml = file_get_contents($url);

for($i = 0; $i < 10000; $i ++) {
	test($sHtml);
	if($i % 100 == 0) {
		echo $i . ' ';
		echo round(memory_get_usage() / 1024 / 1024, 3) . 'M, ';
		echo round(memory_get_peak_usage() / 1024 / 1024, 3) . 'M' . "\n";
	}
}

echo round(memory_get_usage() / 1024 / 1024, 3) . 'M, ';
echo round(memory_get_peak_usage() / 1024 / 1024, 3) . 'M' . "\n";

function test($sHtml) {
	$oDom = new \HtmlParser\ParserDom($sHtml);
	$oDom->find('ul.uni-blk-list02', 0);
	$oDom->find('a');
	$oDom->find('ul');
	$oDom->find('p');
}
