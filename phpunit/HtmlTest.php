<?php

class HtmlTest extends PHPUnit_Framework_TestCase {

	public function testDom() {
		$sHtml = self::getHtml();
		$oDom = new \HtmlParser\ParserDom($sHtml);
		$this->assertEquals('p4', $oDom->findBreadthFirst('p', -1)->getPlainText());
		$this->assertEquals('p_id', $oDom->findBreadthFirst('p[id]', 0)->getPlainText());
		$this->assertEquals('p_id_2', $oDom->findBreadthFirst('p[id=p_id_2]', 0)->getPlainText());
		$this->assertEquals('p2', $oDom->findBreadthFirst('p[!id]', 1)->getPlainText());
		$this->assertEquals('测试1', $oDom->findBreadthFirst('#test1', 0)->getPlainText());

		$oPClass = $oDom->findBreadthFirst('p.test_class1', 0);

		$this->assertEquals('p1', $oPClass->getPlainText());
		$this->assertEquals('test_class test_class1', $oPClass->getAttr('class'));

		$lCheck = array(
			'p1',
			'p2',
			'p3',
			'p_id',
			'p_id_2',
		);
		$lPTag = $oDom->findBreadthFirst('p.test_class');
		$this->assertEquals(5, count($lPTag));
		$lPText = array();
		foreach ($lPTag as $oPTag) {
			$lPText[] = $oPTag->getPlainText();
		}
		$this->assertEquals($lCheck, $lPText);
	}

	public function testTidy() {
		$sHtml = self::getHtml();
		$oDom = new \HtmlParser\ParserTidy($sHtml);
		$this->assertEquals('p4', $oDom->findBreadthFirst('p', -1)->getPlainText());
		$this->assertEquals('p_id', $oDom->findBreadthFirst('p[id]', 0)->getPlainText());
		$this->assertEquals('p_id_2', $oDom->findBreadthFirst('p[id=p_id_2]', 0)->getPlainText());
		$this->assertEquals('p2', $oDom->findBreadthFirst('p[!id]', 1)->getPlainText());
		$this->assertEquals('测试1', $oDom->findBreadthFirst('#test1', 0)->getPlainText());

		$oPClass = $oDom->findBreadthFirst('p.test_class1', 0);

		$this->assertEquals('p1', $oPClass->getPlainText());
		$this->assertEquals('test_class test_class1', $oPClass->getAttr('class'));

		$lCheck = array(
			'p1',
			'p2',
			'p3',
			'p_id',
			'p_id_2',
		);
		$lPTag = $oDom->findBreadthFirst('p.test_class');
		$this->assertEquals(5, count($lPTag));
		$lPText = array();
		foreach ($lPTag as $oPTag) {
			$lPText[] = $oPTag->getPlainText();
		}
		$this->assertEquals($lCheck, $lPText);
	}

	private static function getHtml() {
		static $sHtml;
		if ($sHtml === null) {
			$sHtml = file_get_contents(__DIR__ . '/test.html');
		}
		return $sHtml;
	}

}
