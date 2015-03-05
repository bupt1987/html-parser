<?php
/**
 * Description: 
 */
include '../src/ParserInterface.php';
include '../src/ParserAbstract.php';
include '../src/ParserDom.php';

use HtmlParser\ParserDom;

$html = '<html>
  <head>
    <meta charset="utf-8">
    <title>test</title>
  </head>
  <body>
    <p class="test_class test_class1">p1</p>
    <p class="test_class test_class2">p2</p>
    <p class="test_class test_class3">p3</p>
    <p id="p_id" class="test_class test_id">p_id</p>
    <p id="p_id_2" class="test_class test_id">p_id_2</p>
    <p>p4</p>
    <div id="test1">测试1</div>
  </body>
</html>';

$dom = new ParserDom($html);

echo $html . "\n\n\n";

echo "p last one: " . $dom->findBreadthFirst('p', -1)->getPlainText() . "\n";
echo "have id first: " . $dom->findBreadthFirst('p[id]', 0)->getPlainText() . "\n";
echo "hava id p_id_2 first: " . $dom->findBreadthFirst('p[id=p_id_2]', 0)->getPlainText() . "\n";
echo "do not have id second: " . $dom->findBreadthFirst('p[!id]', 1)->getPlainText() . "\n";

echo "get by id test1: " . $dom->findBreadthFirst('#test1',0)->getPlainText() . "\n";

$p1 = $dom->findBreadthFirst('p.test_class1',0);
echo "get by class test_class1: " .  $p1->getPlainText() . "\n";
echo "class: " .  $p1->getAttr('class') . "\n";

$p_array = $dom->findBreadthFirst('p.test_class');
echo "\np list: \n";
foreach ($p_array as $p){
	echo $p->getPlainText() . "\n";
}
echo "\n";

