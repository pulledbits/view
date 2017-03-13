<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 9-3-17
 * Time: 12:23
 */

namespace pulledbits\View\File;


class LayoutTest extends \PHPUnit_Framework_TestCase
{

    public function testRender_When_NoSectionsAndExistingLayout_Expect_LayoutContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html>BlaBla</html>');
        $object = new Layout($layoutPath);

        $this->expectOutputString('<html>BlaBla</html>');
        unset($object);

        unlink($layoutPath);
    }

    public function testRender_When_SectionsAndExistingLayout_Expect_LayoutWithSectionContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?=$this->harvest(\'foo\');?>BlaBla</html>');
        $object = new Layout($layoutPath);
        $object->section('foo', 'bar');

        $this->expectOutputString('<html>barBlaBla</html>');
        unset($object);

        unlink($layoutPath);
    }

    public function testRender_When_SectionOBContentAndExistingLayout_Expect_LayoutWithSectionContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?=$this->harvest(\'foo\');?>BlaBla</html>');
        $object = new Layout($layoutPath);
        $object->section('foo');
        print 'bar';

        $this->expectOutputString('<html>barBlaBla</html>');
        unset($object);


        unlink($layoutPath);
    }


    public function testRender_When_MultipleSectionsOBContentAndExistingLayout_Expect_LayoutWithSectionContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?=$this->harvest(\'title\');?><?=$this->harvest(\'content\');?>BlaBla<?=$this->harvest(\'footer\');?></html>');
        $object = new Layout($layoutPath);
        $object->section('title', 'blabla');
        $object->section('content');
        print 'bar';
        $object->section('footer');
        print '<footer>Blabla</footer>';

        $this->expectOutputString('<html>blablabarBlaBla<footer>Blabla</footer></html>');
        unset($object);


        unlink($layoutPath);
    }

    public function testRender_When_LayoutExtendsOtherLayout_Expect_LayoutContentsPrinted()
    {
        $parentLayoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($parentLayoutPath . '.php', '<html>BlaBla</html>');
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<?php $layout = $this->layout(\'' . basename($parentLayoutPath) . '\'); ?>');
        $object = new Layout($layoutPath);

        $this->expectOutputString('<html>BlaBla</html>');
        unset($object);

        unlink($layoutPath);
        unlink($parentLayoutPath);
    }

    public function testRender_When_LayoutExtendsOtherLayoutUsingHarvest_Expect_LayoutContentsPrinted()
    {
        $parentLayoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($parentLayoutPath . '.php', '<html><title><?=$this->harvest(\'title\');?></title>BlaBla</html>');
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<?php $layout = $this->layout(\'' . basename($parentLayoutPath) . '\'); $layout->section(\'title\', \'Hello World!\'); ?>');
        $object = new Layout($layoutPath);

        $this->expectOutputString('<html><title>Hello World!</title>BlaBla</html>');
        unset($object);

        unlink($layoutPath);
        unlink($parentLayoutPath);
    }
}
