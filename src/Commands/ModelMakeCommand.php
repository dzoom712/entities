<?php

use \Illuminate\Console\Command;

class ModelMakeCommand extends Command
{
    protected $name = 'symbol:make-model';

    protected $description = '创建模块文件及相关文件';

    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
    }
}
