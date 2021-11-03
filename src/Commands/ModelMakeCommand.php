<?php

namespace dongxiannan\entities\Commands;

use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ModelMakeCommand extends Command
{
    protected $name = 'module:create-all';

    protected $description = '创建模块文件及相关文件';

    protected $signature = 'module:create-all {model : 模型} {module : 模块} {--database= : 数据库连接} {--update=false : 是否覆盖已存在文件 true:是 false:否}';

    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
    }

    public function handle()
    {
        $model = $this->argument('model');
        $module = $this->argument('module');
        $database = $this->option('database');
        $update = $this->option('update');
        $namespace = $this->getNamespace($module);
        $tableName = $this->humpToLine($model);//表明
        //创建模块
        $this->createModel($model, $module, $database, $update, $namespace, $tableName);
    }

   /*
    * 创建模块
    */
    protected function createModel($model, $module, $database, $update, $namespace, $tableName){
        $className = $model;
        //获取模板文件
        $template = file_get_contents(dirname(__FILE__) . '/stubs/model.stub');
        $templateMethod = file_get_contents(dirname(__FILE__) . '/stubs/model_method.stub');

        //model文件目录
        $modelPath = 'Modules/'.$module.'/Entities';
        $filePath ='Modules/'.$module.'/Entities/'.$className.'.php';
        if($database){
            $columns =  DB::connection($database)->select('SHOW COLUMNS FROM `' . $tableName . '`');
        }else{
            $columns =  DB::select('SHOW COLUMNS FROM `' . $tableName . '`');
        }

        $columnsIde = '';
        foreach ($columns as $vv) {

            if (strpos($vv->Type, "int") !== false)
                $type = 'int';
            else if (strpos($vv->Type, "varchar") !== false || strpos($vv->Type, "char") !== false || strpos($vv->Type, 'blob') || strpos($vv->Type, "text") !== false) {
                $type = "string";
            } else if (strpos($vv->Type, "decimal") !== false || strpos($vv->Type, "float") !== false || strpos($vv->Type, "double") !== false) {
                $type = "float";
            }
            else{
                $type = 'string';
            }

            $columnsIde .= ' * @property ' . $type . ' $' . $vv->Field.PHP_EOL;
        }

        $columnsIde.=' *';
        $template_temp = $template;
        $source = str_replace('{{class_name}}', $className, $template_temp);
        $source = str_replace('{{table_name}}', $tableName, $source);
        $source = str_replace('{{namespace}}', $namespace, $source);
        $source = str_replace('{{ide_property}}', $columnsIde, $source);
        $source_method=str_replace('{{class_name}}', $namespace.'\\'.$className, $templateMethod);
        $source = str_replace('{{ide_method}}', $source_method, $source);

        //写入文件
        if (!is_dir($modelPath)) {
            $res = mkdir($modelPath, 0755, true);
            if (!$res) $this->error('目录' . $modelPath . ' 无法写入文件,创建' . $className . ' 失败');
        }

        if(File::exists($filePath) && !$update){
            return $this->info($className . ' 类已经存在');
        }
        if (file_put_contents($filePath, $source)) {
            $this->info($className . ' 类添加成功');
        } else {
            $this->error($className . ' 类写入失败');
        }
    }

    /*
     * 获取命名空间
     */
    protected function getNamespace($module)
    {
        return "Modules\\".$module."\Entities";
    }

    /*
     * 下划线转首字母大写驼峰
     */
    protected function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return ucfirst($str);
    }

    /*
     * 驼峰转下划线
     */
    private function humpToLine($str){
        $str = lcfirst($str);
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);
        return $str;
    }
}
