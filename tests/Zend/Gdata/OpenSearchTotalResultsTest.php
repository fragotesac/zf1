<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata_OpenSearch
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id $
 */

require_once 'Zend/Gdata/Extension/OpenSearchTotalResults.php';
require_once 'Zend/Gdata.php';

/**
 * @category   Zend
 * @package    Zend_Gdata_OpenSearch
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Gdata
 * @group      Zend_Gdata_OpenSearch
 */
class Zend_Gdata_OpenSearchTotalResultsTest extends PHPUnit\Framework\TestCase
{
    protected $openSearchTotalResultsText;
    protected $openSearchTotalResults;

    public function setUp(): void {
        $this->openSearchTotalResultsText = file_get_contents(
                'Zend/Gdata/_files/OpenSearchTotalResultsElementSample1.xml',
                true);
        $this->openSearchTotalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
    }

    public function testEmptyOpenSearchTotalResultsShouldHaveNoExtensionElements() {
        $this->assertIsArray($this->openSearchTotalResults->extensionElements);
        $this->assertTrue(count($this->openSearchTotalResults->extensionElements) == 0);
    }

    public function testEmptyOpenSearchTotalResultsShouldHaveNoExtensionAttributes() {
        $this->assertIsArray($this->openSearchTotalResults->extensionAttributes);
        $this->assertTrue(count($this->openSearchTotalResults->extensionAttributes) == 0);
    }

    public function testSampleOpenSearchTotalResultsShouldHaveNoExtensionElements() {
        $this->openSearchTotalResults->transferFromXML($this->openSearchTotalResultsText);
        $this->assertIsArray($this->openSearchTotalResults->extensionElements);
        $this->assertTrue(count($this->openSearchTotalResults->extensionElements) == 0);
    }

    public function testSampleOpenSearchTotalResultsShouldHaveNoExtensionAttributes() {
        $this->openSearchTotalResults->transferFromXML($this->openSearchTotalResultsText);
        $this->assertIsArray($this->openSearchTotalResults->extensionAttributes);
        $this->assertTrue(count($this->openSearchTotalResults->extensionAttributes) == 0);
    }

    public function testNormalOpenSearchTotalResultsShouldHaveNoExtensionElements() {
        $this->openSearchTotalResults->text = "42";

        $this->assertEquals("42", $this->openSearchTotalResults->text);

        $this->assertCount(0, $this->openSearchTotalResults->extensionElements);
        $newOpenSearchTotalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
        $newOpenSearchTotalResults->transferFromXML($this->openSearchTotalResults->saveXML());
        $this->assertCount(0, $newOpenSearchTotalResults->extensionElements);
        $newOpenSearchTotalResults->extensionElements = array(
                new Zend_Gdata_App_Extension_Element('foo', 'atom', null, 'bar'));
        $this->assertCount(1, $newOpenSearchTotalResults->extensionElements);
        $this->assertEquals("42", $newOpenSearchTotalResults->text);

        /* try constructing using magic factory */
        $gdata = new Zend_Gdata();
        $newOpenSearchTotalResults2 = $gdata->newOpenSearchTotalResults();
        $newOpenSearchTotalResults2->transferFromXML($newOpenSearchTotalResults->saveXML());
        $this->assertCount(1, $newOpenSearchTotalResults2->extensionElements);
        $this->assertEquals("42", $newOpenSearchTotalResults2->text);
    }

    public function testEmptyOpenSearchTotalResultsToAndFromStringShouldMatch() {
        $openSearchTotalResultsXml = $this->openSearchTotalResults->saveXML();
        $newOpenSearchTotalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
        $newOpenSearchTotalResults->transferFromXML($openSearchTotalResultsXml);
        $newOpenSearchTotalResultsXml = $newOpenSearchTotalResults->saveXML();
        $this->assertTrue($openSearchTotalResultsXml == $newOpenSearchTotalResultsXml);
    }

    public function testOpenSearchTotalResultsWithValueToAndFromStringShouldMatch() {
        $this->openSearchTotalResults->text = "42";
        $openSearchTotalResultsXml = $this->openSearchTotalResults->saveXML();
        $newOpenSearchTotalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
        $newOpenSearchTotalResults->transferFromXML($openSearchTotalResultsXml);
        $newOpenSearchTotalResultsXml = $newOpenSearchTotalResults->saveXML();
        $this->assertTrue($openSearchTotalResultsXml == $newOpenSearchTotalResultsXml);
        $this->assertEquals("42", $this->openSearchTotalResults->text);
    }

    public function testExtensionAttributes() {
        $extensionAttributes = $this->openSearchTotalResults->extensionAttributes;
        $extensionAttributes['foo1'] = array('name'=>'foo1', 'value'=>'bar');
        $extensionAttributes['foo2'] = array('name'=>'foo2', 'value'=>'rab');
        $this->openSearchTotalResults->extensionAttributes = $extensionAttributes;
        $this->assertEquals('bar', $this->openSearchTotalResults->extensionAttributes['foo1']['value']);
        $this->assertEquals('rab', $this->openSearchTotalResults->extensionAttributes['foo2']['value']);
        $openSearchTotalResultsXml = $this->openSearchTotalResults->saveXML();
        $newOpenSearchTotalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
        $newOpenSearchTotalResults->transferFromXML($openSearchTotalResultsXml);
        $this->assertEquals('bar', $newOpenSearchTotalResults->extensionAttributes['foo1']['value']);
        $this->assertEquals('rab', $newOpenSearchTotalResults->extensionAttributes['foo2']['value']);
    }

    public function testConvertFullOpenSearchTotalResultsToAndFromString() {
        $this->openSearchTotalResults->transferFromXML($this->openSearchTotalResultsText);
        $this->assertEquals("12", $this->openSearchTotalResults->text);
    }

}
