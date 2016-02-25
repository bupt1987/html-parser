#PHP DOM HTML Parser

**PHP DOM HTML Parser** uses built-in [PHP DOM extension](http://php.net/manual/en/book.dom.php) to process your requests, after testing, it is 10x faster than [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/)

PHP DOM extension requires the libxml PHP extension. This means that passing in --enable-libxml is also required, although this is implicitly accomplished because libxml is enabled by default.

***

## Getting Started

```php
$html = '<div>
test this library! <a href="https://github.com/shinbonlin">PHP DOM HTML Parser</a>
</div><div class="example">test string!</div>
```

if you use Namespace
```php
$html_dom = new \HtmlParser\ParserDom($html);
```

If you comment out the Namespace in line:2
```php
$html_dom = new ParserDom($html);
```


## Basic

Find all images
```php
foreach($html_dom->find('img') as $element) {
       echo $element->src . '<br>';
       echo $element->getAttr('src') . '<br>';
}
```

Find all links 
```php
foreach($html_dom->find('a') as $element) {
       echo $element->href . '<br>';
       echo $element->getAttr('href') . '<br>';
}
```

Find all anchors, returns a array of element objects
```php
$ret = $html_dom->find('a');
```

Find (N)th anchor, returns element object or null if not found (zero based)
```php
$ret = $html_dom->find('a', 0);
```

Find lastest anchor, returns element object or null if not found (zero based)
```php
$ret = $html_dom->find('a', -1); 
```

Find all `<div>` with the id attribute
```php
$ret = $html_dom->find('div[id]');
```

Find all `<div>` which attribute id=foo
```php
$ret = $html_dom->find('div[id=foo]');
```
## Advanced

Find all element which id=foo
```php
$ret = $html_dom->find('#foo');
```

Find all element which class=foo
```php
$ret = $html_dom->find('.foo');
```

Find all HTML tags with the id attribute
```php
$ret = $html_dom->find('*[id]');
```

Find all anchors and images
```php
$ret = $html_dom->find('a, img');
```

Find all anchors and images with the "title" attribute
```php
$ret = $html_dom->find('a[title], img[title]');
```
## Descendant Seletors

Find all `<li>` in `<ul> `
```php
$es = $html_dom->find('ul li');
```

Find Nested <div> tags
```php
$es = $html_dom->find('div div div'); 
```

Find all `<td>` in `<table>` which class=hello
```php
$es = $html_dom->find('table.hello td');
```

Find all td tags with attribite align=center in table tags 
```php
$es = $html_dom->find('table td[align=center]');
```


## Modify HTML 

Modify class attribute
```php
$html_dom->find('div', 1)->class = 'bar';
```

Modify inner text (HTML is allowed)
```php
$html_dom->find('div[id=hello]', 0)->innertext = 'foo';
```

Modify outer HTML
```php
// this example will remove (destory) `<a>` element and replace with new element `<h1>` 
$html_dom->find('a', 0)->outertext = '<h1>Title Link</h1>';
```


## Nested Selectors

Find all `<li>` in `<ul>`
```php
foreach($html_dom->find('ul') as $ul) {
       foreach($ul->find('li') as $li) {
             // do something...
       }
}
```

Find first `<li>` in first `<ul>` 
```php
$e = $html_dom->find('ul', 0)->find('li', 0);
```


## Attribute Selectors

Filter | Description
------------ | -------------
[attribute] | Matches elements that have the specified attribute.
[!attribute] | Matches elements that don't have the specified attribute.
[attribute=value] | Matches elements that have the specified attribute with a certain value.
[attribute!=value] | Matches elements that don't have the specified attribute with a certain value.
[attribute^=value] | Matches elements that have the specified attribute and it starts with a certain value.
[attribute$=value] | Matches elements that have the specified attribute and it ends with a certain value.
[attribute*=value] | Matches elements that have the specified attribute and it contains a certain value.


## Magic Attribute

```php
// Example HTML: <div class="blue-color">foo <b>bar</b></div> 
$e = $html_dom->find("div", 0);

echo $e->outertext; // Returns: "<div class="blue-color">foo <b>bar</b></div>"
echo $e->innertext; // Returns: "foo <b>bar</b>"
echo $e->plaintext; // Returns: "foo bar"
echo $e->tag; // Returns: "div" (current tag name)
echo $e->class; // Returns: "blue-color" 
```

Attribute Name | Usage
------------ | -------------
$e->outertext | Read or write the outer HTML text of element.
$e->innertext | Read or write the inner HTML text of element.
$e->plaintext | Read or write the plain text of element.
$e->tag | Read current tag name of element.
$e->src | Read or write  "src" attribute of element.
$e->class | Read or write  "class" attribute of element.
$e->href | Read or write  "href" attribute of element.

Except outertext, innertext and plaintext, you can use $e->attribute_name to read or write attribute of element.


## More Examples

Get a attribute ( If the attribute is non-value attribute (eg. checked, selected...), it will returns true or false)
```php
$value = $e->href;
```

Set a attribute(If the attribute is non-value attribute (eg. checked, selected...), set it's value as true or false)
```php
$e->href = 'my link';
```

Remove a attribute, set it's value as null! 
```php
$e->href = null;
```

Determine whether a attribute exist? 
```php
if (isset($e->href)) echo 'href exist!';
```

Extract contents from HTML
```php
echo $html->plaintext;
```

Wrap a element
```php
$e->outertext = '<div class="wrap">' . $e->outertext . '<div>';
```

Remove a element, set it's outertext as an empty string 
```php
$e->outertext = '';
```

Append a element
```php
$e->outertext = $e->outertext . '<div>foo<div>';
```

Insert a element
```php
$e->outertext = '<div>foo<div>' . $e->outertext;
```

Dumps the internal DOM tree back into string
```php
$str = $html_dom->save();
```

Dumps the internal DOM tree back into a file
```php 
$html_dom->save('result.htm');
```

## Free Memory

Script will free memory automatically, however, if you would like to do it manually
```php 
$html_dom->clear();
```

## Extension

You can use PHP DOM extension as the following code:
```php 
$html_dom->node

$html_dom->node->childNodes
$html_dom->node->parentNode
$html_dom->node->firstChild
$html_dom->node->lastChild
```
For more information, please visit [http://php.net/manual/en/book.dom.php](http://php.net/manual/en/book.dom.php)
