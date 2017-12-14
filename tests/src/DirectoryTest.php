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

    public function testLoad_When_HelpersRegisterd_Expect_TemplateWithHelpers() {
        $templateFilename = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'tt_') . '.php';
        $templateIdentifier = basename($templateFilename, '.php');
        $helper = function() {};

        $object = new Directory(sys_get_temp_dir(), sys_get_temp_dir());
        $object->registerHelper('test', $helper);

        $expectedTemplate = new File\Template($templateFilename);
        $expectedTemplate->registerHelper('test', $helper);
        $this->assertEquals($expectedTemplate, $object->load($templateIdentifier));
    }
}
