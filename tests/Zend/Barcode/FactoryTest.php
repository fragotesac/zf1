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
 * @package    Zend_Barcode
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Barcode.php';

/**
 * @category   Zend
 * @package    Zend_Barcode
 * @subpackage UnitTests
 * @group      Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_FactoryTest extends PHPUnit\Framework\TestCase
{

    public function testMinimalFactory()
    {
        $this->_checkGDRequirement();

        $renderer = Zend_Barcode::factory('code39');
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Code39);
    }

    public function testMinimalFactoryWithRenderer()
    {
        $renderer = Zend_Barcode::factory('code39', 'pdf');
        $this->assertInstanceOf(Zend_Barcode_Renderer_Pdf::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Code39);
    }

    public function testFactoryWithOptions()
    {
        $this->_checkGDRequirement();

        $options = array('barHeight' => 123);
        $renderer = Zend_Barcode::factory('code39', 'image', $options);
        $this->assertEquals(123, $renderer->getBarcode()->getBarHeight());
    }

    public function testFactoryWithAutomaticExceptionRendering()
    {
        $this->_checkGDRequirement();

        $options = array('barHeight' => - 1);
        $renderer = Zend_Barcode::factory('code39', 'image', $options);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Error);
    }

    /**
     */
    public function testFactoryWithoutAutomaticObjectExceptionRendering()
    {
        $this->expectException(\Zend_Barcode_Object_Exception::class);

        $options = array('barHeight' => - 1);
        $renderer = Zend_Barcode::factory('code39', 'image', $options, array(), false);
    }

    /**
     */
    public function testFactoryWithoutAutomaticRendererExceptionRendering()
    {
        $this->expectException(\Zend_Barcode_Renderer_Exception::class);

        $this->_checkGDRequirement();

        $options = array('imageType' => 'my');
        $renderer = Zend_Barcode::factory('code39', 'image', array(), $options, false);
        $this->markTestIncomplete('Need to throw a configuration exception in renderer');
    }

    public function testFactoryWithZendConfig()
    {
        $this->_checkGDRequirement();

        $config = new Zend_Config(
                array('barcode' => 'code39' ,
                        'renderer' => 'image'));
        $renderer = Zend_Barcode::factory($config);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Code39);

    }

    public function testFactoryWithZendConfigAndObjectOptions()
    {
        $this->_checkGDRequirement();

        $config = new Zend_Config(
                array('barcode' => 'code25' ,
                        'barcodeParams' => array(
                                'barHeight' => 123)));
        $renderer = Zend_Barcode::factory($config);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Code25);
        $this->assertEquals(123, $renderer->getBarcode()->getBarHeight());
    }

    public function testFactoryWithZendConfigAndRendererOptions()
    {
        $this->_checkGDRequirement();

        $config = new Zend_Config(
                array('barcode' => 'code25' ,
                        'rendererParams' => array(
                                'imageType' => 'gif')));
        $renderer = Zend_Barcode::factory($config);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Code25);
        $this->assertSame('gif', $renderer->getImageType());
    }

    public function testFactoryWithoutBarcodeWithAutomaticExceptionRender()
    {
        $this->_checkGDRequirement();

        $renderer = Zend_Barcode::factory(null);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Error);
    }

    public function testFactoryWithoutBarcodeWithAutomaticExceptionRenderWithZendConfig()
    {
        $this->_checkGDRequirement();

        $config = new Zend_Config(array('barcode' => null));
        $renderer = Zend_Barcode::factory($config);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertTrue($renderer->getBarcode() instanceof Zend_Barcode_Object_Error);
    }

    public function testFactoryWithExistingBarcodeObject()
    {
        $this->_checkGDRequirement();

        $barcode = new Zend_Barcode_Object_Code25();
        $renderer = Zend_Barcode::factory($barcode);
        $this->assertSame($barcode, $renderer->getBarcode());
    }

    public function testBarcodeObjectFactoryWithExistingBarcodeObject()
    {
        $barcode = new Zend_Barcode_Object_Code25();
        $generatedBarcode = Zend_Barcode::makeBarcode($barcode);
        $this->assertSame($barcode, $generatedBarcode);
    }

    public function testBarcodeObjectFactoryWithBarcodeAsString()
    {
        $barcode = Zend_Barcode::makeBarcode('code25');
        $this->assertInstanceOf(Zend_Barcode_Object_Code25::class, $barcode);
    }

    public function testBarcodeObjectFactoryWithBarcodeAsStringAndConfigAsArray()
    {
        $barcode = Zend_Barcode::makeBarcode('code25', array('barHeight' => 123));
        $this->assertInstanceOf(Zend_Barcode_Object_Code25::class, $barcode);
        $this->assertSame(123, $barcode->getBarHeight());
    }

    public function testBarcodeObjectFactoryWithBarcodeAsStringAndConfigAsZendConfig()
    {
        $config = new Zend_Config(array('barHeight' => 123));
        $barcode = Zend_Barcode::makeBarcode('code25', $config);
        $this->assertInstanceOf(Zend_Barcode_Object_Code25::class, $barcode);
        $this->assertSame(123, $barcode->getBarHeight());
    }

    public function testBarcodeObjectFactoryWithBarcodeAsZendConfig()
    {
        $config = new Zend_Config(
                array('barcode' => 'code25' ,
                        'barcodeParams' => array(
                                'barHeight' => 123)));
        $barcode = Zend_Barcode::makeBarcode($config);
        $this->assertInstanceOf(Zend_Barcode_Object_Code25::class, $barcode);
        $this->assertSame(123, $barcode->getBarHeight());
    }

    /**
     */
    public function testBarcodeObjectFactoryWithBarcodeAsZendConfigButNoBarcodeParameter()
    {
        $this->expectException(\Zend_Barcode_Exception::class);

        $config = new Zend_Config(
                array(
                        'barcodeParams' => array(
                                'barHeight' => 123)));
        $barcode = Zend_Barcode::makeBarcode($config);
    }

    /**
     */
    public function testBarcodeObjectFactoryWithBarcodeAsZendConfigAndBadBarcodeParameters()
    {
        $this->expectException(\Zend_Barcode_Exception::class);

        $barcode = Zend_Barcode::makeBarcode('code25', null);
    }

    public function testBarcodeObjectFactoryWithNamespace()
    {
        require_once dirname(__FILE__) . '/Object/_files/BarcodeNamespace.php';
        $barcode = Zend_Barcode::makeBarcode('error',
                array(
                        'barcodeNamespace' => 'My_Namespace'));
        $this->assertInstanceOf(My_Namespace_Error::class, $barcode);
    }

    /**
     */
    public function testBarcodeObjectFactoryWithNamespaceButWithoutExtendingObjectAbstract()
    {
        $this->expectException(\Zend_Barcode_Exception::class);

        require_once dirname(__FILE__) . '/Object/_files/BarcodeNamespaceWithoutExtendingObjectAbstract.php';
        $barcode = Zend_Barcode::makeBarcode('error',
                array(
                        'barcodeNamespace' => 'My_Namespace_Other'));
    }

    /**
     */
    public function testBarcodeObjectFactoryWithUnexistantBarcode()
    {
        $this->expectException(\Zend_Exception::class);
        $this->expectExceptionMessage('does not exist or class "Zend_Barcode_Object_Zf123" was not found in the file');

        $barcode = Zend_Barcode::makeBarcode('zf123', array());
    }

    public function testBarcodeRendererFactoryWithExistingBarcodeRenderer()
    {
        $this->_checkGDRequirement();

        $renderer = new Zend_Barcode_Renderer_Image();
        $generatedBarcode = Zend_Barcode::makeRenderer($renderer);
        $this->assertSame($renderer, $generatedBarcode);
    }

    public function testBarcodeRendererFactoryWithBarcodeAsString()
    {
        $this->_checkGDRequirement();

        $renderer = Zend_Barcode::makeRenderer('image');
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
    }

    public function testBarcodeRendererFactoryWithBarcodeAsStringAndConfigAsArray()
    {
        $this->_checkGDRequirement();

        $renderer = Zend_Barcode::makeRenderer('image', array('imageType' => 'gif'));
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertSame('gif', $renderer->getImageType());
    }

    public function testBarcodeRendererFactoryWithBarcodeAsStringAndConfigAsZendConfig()
    {
        $this->_checkGDRequirement();

        $config = new Zend_Config(array('imageType' => 'gif'));
        $renderer = Zend_Barcode::makeRenderer('image', $config);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertSame('gif', $renderer->getimageType());
    }

    public function testBarcodeRendererFactoryWithBarcodeAsZendConfig()
    {
        $this->_checkGDRequirement();

        $config = new Zend_Config(
                array('renderer' => 'image' ,
                        'rendererParams' => array(
                                'imageType' => 'gif')));
        $renderer = Zend_Barcode::makeRenderer($config);
        $this->assertInstanceOf(Zend_Barcode_Renderer_Image::class, $renderer);
        $this->assertSame('gif', $renderer->getimageType());
    }

    /**
     */
    public function testBarcodeRendererFactoryWithBarcodeAsZendConfigButNoBarcodeParameter()
    {
        $this->expectException(\Zend_Barcode_Exception::class);

        $config = new Zend_Config(
                array(
                        'rendererParams' => array(
                                'imageType' => 'gif')));
        $renderer = Zend_Barcode::makeRenderer($config);
    }

    /**
     */
    public function testBarcodeRendererFactoryWithBarcodeAsZendConfigAndBadBarcodeParameters()
    {
        $this->expectException(\Zend_Barcode_Exception::class);

        $renderer = Zend_Barcode::makeRenderer('image', null);
    }

    public function testBarcodeRendererFactoryWithNamespace()
    {
        $this->_checkGDRequirement();

        require_once dirname(__FILE__) . '/Renderer/_files/RendererNamespace.php';
        $renderer = Zend_Barcode::makeRenderer('image',
                array(
                        'rendererNamespace' => 'My_Namespace'));
        $this->assertInstanceOf(My_Namespace_Image::class, $renderer);
    }

    /**
     */
    public function testBarcodeFactoryWithNamespaceButWithoutExtendingRendererAbstract()
    {
        $this->expectException(\Zend_Barcode_Exception::class);

        require_once dirname(__FILE__) . '/Renderer/_files/RendererNamespaceWithoutExtendingRendererAbstract.php';
        $renderer = Zend_Barcode::makeRenderer('image',
                array(
                        'rendererNamespace' => 'My_Namespace_Other'));
    }

    /**
     */
    public function testBarcodeRendererFactoryWithUnexistantRenderer()
    {
        $this->expectException(\Zend_Exception::class);
        $this->expectExceptionMessage('does not exist or class "Zend_Barcode_Renderer_Zend" was not found in the file');

        $renderer = Zend_Barcode::makeRenderer('zend', array());
    }

    public function testProxyBarcodeRendererDrawAsImage()
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped(
                    'GD extension is required to run this test');
        }
        $resource = Zend_Barcode::draw('code25', 'image');

        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->assertTrue(gettype($resource) == 'resource', 'Image must be a resource');
            $this->assertTrue(get_resource_type($resource) == 'gd', 'Image must be a GD resource');
        } else {
            $this->assertInstanceOf(GdImage::class, $resource);
        }
    }

    public function testProxyBarcodeRendererDrawAsPdf()
    {
        Zend_Barcode::setBarcodeFont(dirname(__FILE__) . '/Object/_fonts/Vera.ttf');
        $resource = Zend_Barcode::draw('code25', 'pdf');
        $this->assertInstanceOf(Zend_Pdf::class, $resource);
        Zend_Barcode::setBarcodeFont('');
    }

    public function testProxyBarcodeObjectFont()
    {
        Zend_Barcode::setBarcodeFont('my_font.ttf');
        $barcode = new Zend_Barcode_Object_Code25();
        $this->assertSame('my_font.ttf', $barcode->getFont());
        Zend_Barcode::setBarcodeFont('');
    }

    protected function _checkGDRequirement()
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('This test requires the GD extension');
        }
    }
}
