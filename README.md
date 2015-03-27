HtmlParser
===============

php html解析工具，类似与PHP Simple HTML DOM Parser。
由于基于php模块tidy|dom，所以在解析html时的效率比 PHP Simple HTML DOM Parser 快好几倍。
并提供广度优先查询findBreadthFirst()和深度优先查询findDepthFirst() 两种查询方式，可根据自己的情况选择使用。
因为代码实现的问题，在查询全部时深度优先比广度优先快一点。  

默认使用dom模块，效率比tidy模块更高  

注意：html代码必须是utf-8编码字符，如果不是请转成utf-8  
      如果有乱码的问题参考：http://www.fwolf.com/blog/post/314  

现在支持composer

"require": {"bupt1987/html-parser": "2.0.1"}

加载composer  
require 'vendor/autoload.php';

================================================================================
##### *Example*
~~~
<?php
use HtmlParser\ParserFactory;
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
//也可以 new ParserDom($html) 或者 new ParserTidy($html)
$html_dom = ParserFactory::createParser($html, ParserFactory::TYPE_DOM);
$p_array = $html_dom->findBreadthFirst('p.test_class');
$p1 = $html_dom->findBreadthFirst('p.test_class1',0);
$div = $html_dom->findBreadthFirst('div#test1',0);
foreach ($p_array as $p){
	echo $p->getPlainText();
}
echo $div->getPlainText();
echo $p1->getPlainText();
echo $p1->getAttr('class');
?>
~~~

基础用法
================================================================================
~~~
// 查找所有a标签
$ret = $html->findBreadthFirst('a');

// 查找a标签的第一个元素
$ret = $html->findBreadthFirst('a', 0);

// 查找a标签的倒数第一个元素
$ret = $html->findBreadthFirst('a', -1); 

// 查找所有含有id属性的div标签
$ret = $html->findBreadthFirst('div[id]');

// 查找所有含有id属性为foo的div标签
$ret = $html->findBreadthFirst('div[id=foo]'); 
~~~

高级用法
================================================================================
~~~
// 查找所有id=foo的元素
$ret = $html->findBreadthFirst('#foo');

// 查找所有class=foo的元素
$ret = $html->findBreadthFirst('.foo');

// 查找所有拥有 id属性的元素
$ret = $html->findBreadthFirst('*[id]'); 

// 查找所有 anchors 和 images标记 
$ret = $html->findBreadthFirst('a, img'); 

// 查找所有有"title"属性的anchors and images 
$ret = $html->findBreadthFirst('a[title], img[title]');
~~~

层级选择器
================================================================================
~~~
// Find all <li> in <ul> 
$es = $html->findBreadthFirst('ul li');

// Find Nested <div> tags
$es = $html->findBreadthFirst('div div div'); 

// Find all <td> in <table> which class=hello 
$es = $html->findBreadthFirst('table.hello td');

// Find all td tags with attribite align=center in table tags 
$es = $html->findBreadthFirst('table td[align=center]'); 
~~~

嵌套选择器
================================================================================
~~~
// Find all <li> in <ul> 
foreach($html->findBreadthFirst('ul') as $ul) 
{
       foreach($ul->findBreadthFirst('li') as $li) 
       {
             // do something...
       }
}

// Find first <li> in first <ul> 
$e = $html->findBreadthFirst('ul', 0)->findBreadthFirst('li', 0);
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


