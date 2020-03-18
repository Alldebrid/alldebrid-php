<?php

use mageekguy\atoum\reports;
use mageekguy\atoum\reports\coverage;
use mageekguy\atoum\writers\std;

$script->bootstrapFile(__DIR__ . DIRECTORY_SEPARATOR . 'tests/bootstrap.php');

// $extension = new reports\extension($script);
// $extension->addToRunner($runner);

// $script->addDefaultReport();

// $coverage = new coverage\html();
// $coverage->addWriter(new std\out());
// $coverage->setOutPutDirectory(__DIR__ . '/tests');

// $runner->addReport($coverage);