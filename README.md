HtmlParserModel
===============

php html解析工具，类似与PHP Simple HTML DOM Parser。
由于基于php模块tidy，所以在解析html时的效率比 PHP Simple HTML DOM Parser 快2倍多。

================================================================================
##### *Example*
~~~
<?php
$html = '<html>
  <head>
    <title>test</title>
  </head>
  <body>
    <p class="test_class test_class1">p1</p>
    <p class="test_class test_class2">p2</p>
    <p class="test_class test_class3">p3</p>
    <div id="test1">测试1</div>
  </body>
</html>';
$html_dom = new HtmlParserModel();
$html_dom->parseStr($html);
$p_array = $html_dom->find('p.test_class');
$p1 = $html_dom->find('p.test_class1',0);
$div = $html_dom->find('div#test1',0);
foreach ($p_array as $p){
	echo $p->getPlainText();
}
echo $div->getPlainText();
echo $p1->getPlainText();
?>
~~~
