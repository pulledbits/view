<?php
/**
 * User: hameijer
 * Date: 9-3-17
 * Time: 15:08
 */

namespace File;


use pulledbits\View\File\Template;


class TemplateTest extends \PHPUnit_Framework_TestCase
{

    public function testRender_When_ExistingTemplateWithNoVariables_Expect_ContentsOutputted()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>BlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }

    public function testRender_When_ExistingTemplateWithVariables_Expect_ContentsOutputted()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$foo?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>barBlaBla</html>');
        $object->render([
            'foo' => 'bar'
        ]);

        unlink($templatePath);
    }


    public function testRender_When_ExistingTemplateWithHTMLUnsafeVariables_Expect_ContentsOutputted()
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$foo?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>&lt;bar&gt;BlaBla</html>');
        $object->render([
            'foo' => '<bar>'
        ]);

        unlink($templatePath);
    }

    public function testRender_When_ExistingTemplateWithNoVariablesUsingURLNoHTTPS_Expect_ContentsWithURLOutputted()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>http://example.com/path/to/fileBlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }

    public function testRender_When_ExistingTemplateWithNoVariablesUsingURLHTTPS_Expect_ContentsWithURLOutputted()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS'] = 'on';

        $templatePath = tempnam(sys_get_temp_dir(), 'tt_');
        file_put_contents($templatePath, '<html><?=$this->url(\'/path/to/file\')?>BlaBla</html>');

        $object = new Template($templatePath, sys_get_temp_dir(), sys_get_temp_dir());

        $this->expectOutputString('<html>https://example.com/path/to/fileBlaBla</html>');
        $object->render([]);

        unlink($templatePath);
    }
}
