<?php

namespace pulledbits\View;


class DirectoryTest extends \PHPUnit\Framework\TestCase
{

    public function testLoad_When_ExistingTemplateFile_Expect_ValidTemplate() {
        $templateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'tt_') . '.php';
        $templateIdentifier = basename($templateFilename, '.php');

        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir());

        $this->assertEquals(new File\Template($templateFilename), $object->load($templateIdentifier));
    }

}
