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

}
