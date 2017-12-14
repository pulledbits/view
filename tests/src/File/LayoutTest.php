<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 9-3-17
 * Time: 12:23
 */

namespace pulledbits\View\File;


class LayoutTest extends \PHPUnit\Framework\TestCase
{

    public function testRender_When_NoSectionsAndExistingLayout_Expect_LayoutContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html>BlaBla</html>');
        $object = new Layout($layoutPath);

        $this->expectOutputString('<html>BlaBla</html>');
        $object->play();
        unlink($layoutPath);
    }

    public function testRender_When_SectionsAndExistingLayout_Expect_LayoutWithSectionContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?=$this->harvest(\'foo\');?>BlaBlo</html>');
        $object = new Layout($layoutPath);

        $object->section('foo', 'bar');
        $this->expectOutputString('<html>barBlaBlo</html>');
        $object->play();

        unlink($layoutPath);
    }

    public function testRender_When_SectionOBContentAndExistingLayout_Expect_LayoutWithSectionContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?=$this->harvest(\'foo\');?>BlaBli</html>');
        $object = new Layout($layoutPath);
        $object->section('foo');
        print 'bar';

        $this->expectOutputString('<html>barBlaBli</html>');
        $object->play();

        unlink($layoutPath);
    }


    public function testRender_When_MultipleSectionsOBContentAndExistingLayout_Expect_LayoutWithSectionContentsPrinted()
    {
        $layoutPath = tempnam(sys_get_temp_dir(), 'lt_');
        file_put_contents($layoutPath, '<html><?=$this->harvest(\'title\');?><?=$this->harvest(\'content\');?>BlaBle<?=$this->harvest(\'footer\');?></html>');
        $object = new Layout($layoutPath);
        $object->section('title', 'blabla');
        $object->section('content');
        print 'bar';
        $object->section('footer');
        print '<footer>Blabla</footer>';

        $this->expectOutputString('<html>blablabarBlaBle<footer>Blabla</footer></html>');
        $object->play();


        unlink($layoutPath);
    }

    public function testRender_When_LayoutExtendsOtherLayout_Expect_LayoutContentsPrinted()
    {
        $parentLayoutPath = tempnam(sys_get_temp_dir(), 'lpt_') . '.php';
        $layoutPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($parentLayoutPath, '.php') . '.' . uniqid() . '.php';

        file_put_contents($parentLayoutPath, '<html>BlaBlu</html>');
        file_put_contents($layoutPath, '<?php ?>');

        $object = Layout::load(sys_get_temp_dir(), basename($layoutPath, '.php'));

        $this->expectOutputString('<html>BlaBlu</html>');
        $object->play();

        unlink($layoutPath);
        unlink($parentLayoutPath);
    }

    public function testRender_When_LayoutExtendsOtherLayoutUsingHarvest_Expect_LayoutContentsPrinted()
    {
        $parentLayoutPath = tempnam(sys_get_temp_dir(), 'lpt_') . '.php';
        $layoutPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($parentLayoutPath, '.php') . '.' . uniqid() . '.php';

        file_put_contents($parentLayoutPath, '<html><title><?=$this->harvest(\'title\');?></title>BlaBla</html>');
        file_put_contents($layoutPath, '<?php $layout->section(\'title\', \'Hëllo World!\'); ?>');
        $object = Layout::load(sys_get_temp_dir(), basename($layoutPath, '.php'));

        $this->expectOutputString('<html><title>Hëllo World!</title>BlaBla</html>');
        $object->play();

        unlink($layoutPath);
        unlink($parentLayoutPath);
    }
}
