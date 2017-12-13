<?php

namespace pulledbits\View;


class DirectoryTest extends \PHPUnit\Framework\TestCase
{

    public function testLoad_When_ExistingTemplateFile_Expect_ValidTemplate() {
        $layoutFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '') . '.php';

        $templateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, basename($layoutFilename, '.php') . '.') . '.php';
        $templateIdentifier = basename($templateFilename, '.php');

        file_put_contents($layoutFilename, '<?php print "Hello World";');
        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir());

        $this->assertEquals(new File\Template(new File\Layout($layoutFilename), $templateFilename), $object->load($templateIdentifier));

        unlink($layoutFilename);
    }

}
