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
        $object->render();
    }

}
