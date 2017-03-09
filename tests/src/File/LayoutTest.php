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
}
