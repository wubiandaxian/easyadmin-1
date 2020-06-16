<?php
// 应用公共文件

use app\common\service\AuthService;
use think\facade\Cache;

if (!function_exists('__url')) {

    /**
     * 构建URL地址
     * @param string $url
     * @param array $vars
     * @param bool $suffix
     * @param bool $domain
     * @return string
     */
    function __url(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        return url($url, $vars, $suffix, $domain)->build();
    }
}

if (!function_exists('password')) {

    /**
     * 密码加密算法
     * @param $value 需要加密的值
     * @param $type  加密类型，默认为md5 （md5, hash）
     * @return mixed
     */
    function password($value)
    {
        $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
        return sha1($value);
    }

}

if (!function_exists('xdebug')) {

    /**
     * debug调试
     * @param string|array $data 打印信息
     * @param string $type 类型
     * @param string $suffix 文件后缀名
     * @param bool $force
     * @param null $file
     */
    function xdebug($data, $type = 'xdebug', $suffix = null, $force = false, $file = null)
    {
        !is_dir(runtime_path() . 'xdebug/') && mkdir(runtime_path() . 'xdebug/');
        if (is_null($file)) {
            $file = is_null($suffix) ? runtime_path() . 'xdebug/' . date('Ymd') . '.txt' : runtime_path() . 'xdebug/' . date('Ymd') . "_{$suffix}" . '.txt';
        }
        file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . "========================= {$type} ===========================" . PHP_EOL, FILE_APPEND);
        $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
        $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
    }
}

if (!function_exists('sysconfig')) {

    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return array|mixed
     */
    function sysconfig($group, $name = null)
    {
        $where = ['group' => $group];
        $value = empty($name) ? Cache::get("sysconfig_{$group}") : Cache::get("sysconfig_{$group}_{$name}");
        if (!empty($value)) {
            return $value;
        }
        if (!empty($name)) {
            $where['name'] = $name;
            $value = \app\admin\model\SystemConfig::where($where)->value('value');
            Cache::tag('sysconfig')->set("sysconfig_{$group}_{$name}", $value, 3600);
        } else {
            $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
            Cache::tag('sysconfig')->set("sysconfig_{$group}", $value, 3600);
        }
        return $value;
    }
}

if (!function_exists('array_format_key')) {

    /**
     * 二位数组重新组合数据
     * @param $array
     * @param $key
     * @return array
     */
    function array_format_key($array, $key)
    {
        $newArray = [];
        foreach ($array as $vo) {
            $newArray[$vo[$key]] = $vo;
        }
        return $newArray;
    }

}

if (!function_exists('auth')) {

    /**
     * auth权限验证
     * @param $node
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function auth($node = null)
    {
        $authService = new AuthService(session('admin.id'));
        $check = $authService->checkNode($node);
        return $check;
    }

}


if (!function_exists('addon')) {

    /**
     * 调取插件功能
     * @param $addonName
     * @param $method
     * @param mixed $parames
     * @return bool
     * @throws \app\common\exception\AddonException
     */
    function addon($addonName, $method, $parames = [])
    {
        $addon = "\\addons\\{$addonName}\\Addon";
        if (!class_exists($addon)) {
            throw new \app\common\exception\AddonException("插件：{$addonName}不存在");
        }
        $addonInstance = new $addon;
        if (!method_exists($addonInstance, $method)) {
            throw new \app\common\exception\AddonException("插件{$addonName}的方法：{$method}不存在");
        }
        $result = call_user_func_array([$addonInstance, $method], $parames);
        return $result;
    }

}

if (!function_exists('get_addon_config')) {

    /**
     * 读取配置文件
     * @param $addonName
     * @param null $name
     * @return mixed|null
     * @throws \app\common\exception\AddonException
     */
    function get_addon_config($addonName, $name = null)
    {
        $addon = "\\addons\\{$addonName}\\Addon";
        if (!class_exists($addon)) {
            throw new \app\common\exception\AddonException("插件：{$addonName}不存在");
        }
        $filepath = ADDONS_PATH . $addonName . DS . "config.php";
        if (!file_exists($filepath)) {
            throw new \app\common\exception\AddonException("插件{$addonName}无法读取配置信息, 配置文件不存在");
        }
        $config = include($filepath);
        if (empty($name)) {
            return $config;
        } else {
            return isset($config[$name]) ? $config[$name] : null;
        }
    }
}

if (!function_exists('set_addon_config')) {

    function set_addon_config($addonName, $data = [])
    {
        $addon = "\\addons\\{$addonName}\\Addon";
        if (!class_exists($addon)) {
            throw new \app\common\exception\AddonException("插件：{$addonName}不存在");
        }
        $filepath = ADDONS_PATH . $addonName . DS . "config.php";
    }
}

if (!function_exists('addons_path')) {

    /**
     * 获取插件地址
     * @return string
     */
    function addons_path()
    {
        return ADDONS_PATH;
    }
}
