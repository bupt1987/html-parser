HtmlParser
===============
[![Total Downloads](https://img.shields.io/badge/downloads-9.4k-green.svg)](https://packagist.org/packages/bupt1987/html-parser)
[![Build Status](https://api.travis-ci.org/bupt1987/html-parser.svg)](https://travis-ci.org/bupt1987/html-parser)  

php html解析工具，类似与PHP Simple HTML DOM Parser。
由于基于php模块dom，所以在解析html时的效率比 PHP Simple HTML DOM Parser 快好几倍。


注意：html代码必须是utf-8编码字符，如果不是请转成utf-8  
      如果有乱码的问题参考：http://www.fwolf.com/blog/post/314  

现在支持composer

"require": {"bupt1987/html-parser": "dev-master"}

加载composer  
require 'vendor/autoload.php';

================================================================================
##### *Example*
~~~
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
    <div id="test1">测试1</div>
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
?>
~~~

基础用法
================================================================================
~~~
// 查找所有a标签
$ret = $html->find('a');

// 查找a标签的第一个元素
$ret = $html->find('a', 0);

// 查找a标签的倒数第一个元素
$ret = $html->find('a', -1); 

// 查找所有含有id属性的div标签
$ret = $html->find('div[id]');

// 查找所有含有id属性为foo的div标签
$ret = $html->find('div[id=foo]'); 
~~~

高级用法
================================================================================
~~~
// 查找所有id=foo的元素
$ret = $html->find('#foo');

// 查找所有class=foo的元素
$ret = $html->find('.foo');

// 查找所有拥有 id属性的元素
$ret = $html->find('*[id]'); 

// 查找所有 anchors 和 images标记 
$ret = $html->find('a, img'); 

// 查找所有有"title"属性的anchors and images 
$ret = $html->find('a[title], img[title]');
~~~

层级选择器
================================================================================
~~~
// Find all <li> in <ul> 
$es = $html->find('ul li');

// Find Nested <div> tags
$es = $html->find('div div div'); 

// Find all <td> in <table> which class=hello 
$es = $html->find('table.hello td');

// Find all td tags with attribite align=center in table tags 
$es = $html->find('table td[align=center]'); 
~~~

嵌套选择器
================================================================================
~~~
// Find all <li> in <ul> 
foreach($html->find('ul') as $ul) 
{
       foreach($ul->find('li') as $li) 
       {
             // do something...
       }
}

// Find first <li> in first <ul> 
$e = $html->find('ul', 0)->find('li', 0);
~~~

属性过滤
================================================================================
~~~
支持属性选择器操作:

过滤	描述
[attribute]	匹配具有指定属性的元素.
[!attribute]	匹配不具有指定属性的元素。
[attribute=value]	匹配具有指定属性值的元素
[attribute!=value]	匹配不具有指定属性值的元素
[attribute^=value]	匹配具有指定属性值开始的元素
[attribute$=value]	匹配具有指定属性值结束的元素
[attribute*=value]	匹配具有指定属性的元素,且该属性包含了一定的值
~~~

Dom扩展用法
===============================================================================
~~~
获取dom通过扩展实现更多的功能，详见：http://php.net/manual/zh/book.dom.php

/**
 * @var \DOMNode
 */
$oHtml->node

$oHtml->node->childNodes
$oHtml->node->parentNode
$oHtml->node->firstChild
$oHtml->node->lastChild
等等...

~~~
