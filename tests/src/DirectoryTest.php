<?php

namespace pulledbits\View;


class DirectoryTest extends \PHPUnit\Framework\TestCase
{

    public function testLoad_When_ExistingTemplateFile_Expect_ValidTemplate() {
        $templateFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.php';
        file_put_contents($templateFilename, '<?php print "Hello World";');
        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'layouts');

        $this->assertEquals(new \pulledbits\View\File\Template($object, $templateFilename), $object->load('test'));
    }

}
