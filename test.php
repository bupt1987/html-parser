<?php
require 'vendor/autoload.php';

$html = '<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>test</title>
  </head>
  <body>
    <p class="test_class test_class1">p1</p>
    <p class="test_class test_class2">p2</p>
    <p class="test_class test_class3">p3</p>
    <div id="test1"><span style="display: none">测试1<br/></span><input date=\'"sdfsf"\' name="test" value="123"/>123123</div>
  </body>
</html>';

$html_dom = new \HtmlParser\ParserDom($html);
$p_array = $html_dom->find('p.test_class');
$p1 = $html_dom->find('p.test_class1',0);
$div = $html_dom->find('div#test1',0);
foreach ($p_array as $p){
	echo $p->getPlainText() . "\n";
}
echo $div->getPlainText() . "\n";
echo $p1->getPlainText() . "\n";
echo $p1->getAttr('class') . "\n";

echo "show html:\n";
echo $div->innerHtml() . "\n";
echo $div->outerHtml() . "\n";
