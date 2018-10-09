<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\exception\HttpException;

class Route
{
    // 路由规则
    private static $childrules = [
        'get'     => [],
        'post'    => [],
        'put'     => [],
        'delete'  => [],
        'patch'   => [],
        'head'    => [],
        'options' => [],
        '*'       => [],
        'alias'   => [],
        'domain'  => [],
        'pattern' => [],
        'name'    => [],
    ];

    // REST路由操作方法定义
    private static $rest = [
        'index'  => ['get', '', 'index'],
        'create' => ['get', '/create', 'create'],
        'edit'   => ['get', '/:id/edit', 'edit'],
        'read'   => ['get', '/:id', 'read'],
        'save'   => ['post', '', 'save'],
        'update' => ['put', '/:id', 'update'],
        'delete' => ['delete', '/:id', 'delete'],
    ];

    // 不同请求类型的方法前缀
    private static $methodPrefix = [
        'get'    => 'get',
        'post'   => 'post',
        'put'    => 'put',
        'delete' => 'delete',
        'patch'  => 'patch',
    ];

    // 子域名
    private static $subDomain = '';
    // 域名绑定
    private static $bind = [];
    // 当前分组信息
    private static $group = [];
    // 当前子域名绑定
    private static $domainBind;
    private static $domainRule;
    // 当前域名
    private static $domain;
    // 当前路由执行过程中的参数
    private static $option = [];

    /**
     * 注册变量规则
     * @access public
     * @param string|array $name 变量名
     * @param string       $childrule 变量规则
     * @return void
     */
    public static function pattern($name = null, $childrule = '')
    {
        if (is_array($name)) {
            self::$childrules['pattern'] = array_merge(self::$childrules['pattern'], $name);
        } else {
            self::$childrules['pattern'][$name] = $childrule;
        }
    }

    /**
     * 注册子域名部署规则
     * @access public
     * @param string|array $domain  子域名
     * @param mixed        $childrule    路由规则
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function domain($domain, $childrule = '', $option = [], $pattern = [])
    {
        if (is_array($domain)) {
            foreach ($domain as $key => $item) {
                self::domain($key, $item, $option, $pattern);
            }
        } elseif ($childrule instanceof \Closure) {
            // 执行闭包
            self::setDomain($domain);
            call_user_func_array($childrule, []);
            self::setDomain(null);
        } elseif (is_array($childrule)) {
            self::setDomain($domain);
            self::group('', function () use ($childrule) {
                // 动态注册域名的路由规则
                self::registerRules($childrule);
            }, $option, $pattern);
            self::setDomain(null);
        } else {
            self::$childrules['domain'][$domain]['[bind]'] = [$childrule, $option, $pattern];
        }
    }

    private static function setDomain($domain)
    {
        self::$domain = $domain;
    }

    /**
     * 设置路由绑定
     * @access public
     * @param mixed  $bind 绑定信息
     * @param string $type 绑定类型 默认为module 支持 namespace class controller
     * @return mixed
     */
    public static function bind($bind, $type = 'module')
    {
        self::$bind = ['type' => $type, $type => $bind];
    }

    /**
     * 设置或者获取路由标识
     * @access public
     * @param string|array $name  路由命名标识 数组表示批量设置
     * @param array        $value 路由地址及变量信息
     * @return array
     */
    public static function name($name = '', $value = null)
    {
        if (is_array($name)) {
            return self::$childrules['name'] = $name;
        } elseif ('' === $name) {
            return self::$childrules['name'];
        } elseif (!is_null($value)) {
            self::$childrules['name'][strtolower($name)][] = $value;
        } else {
            $name = strtolower($name);
            return isset(self::$childrules['name'][$name]) ? self::$childrules['name'][$name] : null;
        }
    }

    /**
     * 读取路由绑定
     * @access public
     * @param string $type 绑定类型
     * @return mixed
     */
    public static function getBind($type)
    {
        return isset(self::$bind[$type]) ? self::$bind[$type] : null;
    }

    /**
     * 导入配置文件的路由规则
     * @access public
     * @param array  $childrule 路由规则
     * @param string $type 请求类型
     * @return void
     */
    public static function import(array $childrule, $type = '*')
    {
        // 检查域名部署
        if (isset($childrule['__domain__'])) {
            self::domain($childrule['__domain__']);
            unset($childrule['__domain__']);
        }

        // 检查变量规则
        if (isset($childrule['__pattern__'])) {
            self::pattern($childrule['__pattern__']);
            unset($childrule['__pattern__']);
        }

        // 检查路由别名
        if (isset($childrule['__alias__'])) {
            self::alias($childrule['__alias__']);
            unset($childrule['__alias__']);
        }

        // 检查资源路由
        if (isset($childrule['__rest__'])) {
            self::resource($childrule['__rest__']);
            unset($childrule['__rest__']);
        }

        self::registerRules($childrule, strtolower($type));
    }

    // 批量注册路由
    protected static function registerRules($childrules, $type = '*')
    {
        foreach ($childrules as $key => $val) {
            if (is_numeric($key)) {
                $key = array_shift($val);
            }
            if (empty($val)) {
                continue;
            }
            if (is_string($key) && 0 === strpos($key, '[')) {
                $key = substr($key, 1, -1);
                self::group($key, $val);
            } elseif (is_array($val)) {
                self::setRule($key, $val[0], $type, $val[1], isset($val[2]) ? $val[2] : []);
            } else {
                self::setRule($key, $val, $type);
            }
        }
    }

    /**
     * 注册路由规则
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param string       $type    请求类型
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function rule($childrule, $route = '', $type = '*', $option = [], $pattern = [])
    {
        $group = self::getGroup('name');

        if (!is_null($group)) {
            // 路由分组
            $option  = array_merge(self::getGroup('option'), $option);
            $pattern = array_merge(self::getGroup('pattern'), $pattern);
        }

        $type = strtolower($type);

        if (strpos($type, '|')) {
            $option['method'] = $type;
            $type             = '*';
        }
        if (is_array($childrule) && empty($route)) {
            foreach ($childrule as $key => $val) {
                if (is_numeric($key)) {
                    $key = array_shift($val);
                }
                if (is_array($val)) {
                    $route    = $val[0];
                    $option1  = array_merge($option, $val[1]);
                    $pattern1 = array_merge($pattern, isset($val[2]) ? $val[2] : []);
                } else {
                    $option1  = null;
                    $pattern1 = null;
                    $route    = $val;
                }
                self::setRule($key, $route, $type, !is_null($option1) ? $option1 : $option, !is_null($pattern1) ? $pattern1 : $pattern, $group);
            }
        } else {
            self::setRule($childrule, $route, $type, $option, $pattern, $group);
        }

    }

    /**
     * 设置路由规则
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param string       $type    请求类型
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @param string       $group   所属分组
     * @return void
     */
    protected static function setRule($childrule, $route, $type = '*', $option = [], $pattern = [], $group = '')
    {
        if (is_array($childrule)) {
            $name = $childrule[0];
            $childrule = $childrule[1];
        } elseif (is_string($route)) {
            $name = $route;
        }
        if (!isset($option['complete_match'])) {
            if (Config::get('route_complete_match')) {
                $option['complete_match'] = true;
            } elseif ('$' == substr($childrule, -1, 1)) {
                // 是否完整匹配
                $option['complete_match'] = true;
            }
        } elseif (empty($option['complete_match']) && '$' == substr($childrule, -1, 1)) {
            // 是否完整匹配
            $option['complete_match'] = true;
        }

        if ('$' == substr($childrule, -1, 1)) {
            $childrule = substr($childrule, 0, -1);
        }

        if ('/' != $childrule || $group) {
            $childrule = trim($childrule, '/');
        }
        $vars = self::parseVar($childrule);
        if (isset($name)) {
            $key    = $group ? $group . ($childrule ? '/' . $childrule : '') : $childrule;
            $suffix = isset($option['ext']) ? $option['ext'] : null;
            self::name($name, [$key, $vars, self::$domain, $suffix]);
        }
        if (isset($option['modular'])) {
            $route = $option['modular'] . '/' . $route;
        }
        if ($group) {
            if ('*' != $type) {
                $option['method'] = $type;
            }
            if (self::$domain) {
                self::$childrules['domain'][self::$domain]['*'][$group]['rule'][] = ['rule' => $childrule, 'route' => $route, 'var' => $vars, 'option' => $option, 'pattern' => $pattern];
            } else {
                self::$childrules['*'][$group]['rule'][] = ['rule' => $childrule, 'route' => $route, 'var' => $vars, 'option' => $option, 'pattern' => $pattern];
            }
        } else {
            if ('*' != $type && isset(self::$childrules['*'][$childrule])) {
                unset(self::$childrules['*'][$childrule]);
            }
            if (self::$domain) {
                self::$childrules['domain'][self::$domain][$type][$childrule] = ['rule' => $childrule, 'route' => $route, 'var' => $vars, 'option' => $option, 'pattern' => $pattern];
            } else {
                self::$childrules[$type][$childrule] = ['rule' => $childrule, 'route' => $route, 'var' => $vars, 'option' => $option, 'pattern' => $pattern];
            }
            if ('*' == $type) {
                // 注册路由快捷方式
                foreach (['get', 'post', 'put', 'delete', 'patch', 'head', 'options'] as $method) {
                    if (self::$domain && !isset(self::$childrules['domain'][self::$domain][$method][$childrule])) {
                        self::$childrules['domain'][self::$domain][$method][$childrule] = true;
                    } elseif (!self::$domain && !isset(self::$childrules[$method][$childrule])) {
                        self::$childrules[$method][$childrule] = true;
                    }
                }
            }
        }
    }

    /**
     * 设置当前执行的参数信息
     * @access public
     * @param array $options 参数信息
     * @return mixed
     */
    protected static function setOption($options = [])
    {
        self::$option[] = $options;
    }

    /**
     * 获取当前执行的所有参数信息
     * @access public
     * @return array
     */
    public static function getOption()
    {
        return self::$option;
    }

    /**
     * 获取当前的分组信息
     * @access public
     * @param string $type 分组信息名称 name option pattern
     * @return mixed
     */
    public static function getGroup($type)
    {
        if (isset(self::$group[$type])) {
            return self::$group[$type];
        } else {
            return 'name' == $type ? null : [];
        }
    }

    /**
     * 设置当前的路由分组
     * @access public
     * @param string $name    分组名称
     * @param array  $option  分组路由参数
     * @param array  $pattern 分组变量规则
     * @return void
     */
    public static function setGroup($name, $option = [], $pattern = [])
    {
        self::$group['name']    = $name;
        self::$group['option']  = $option ?: [];
        self::$group['pattern'] = $pattern ?: [];
    }

    /**
     * 注册路由分组
     * @access public
     * @param string|array   $name    分组名称或者参数
     * @param array|\Closure $routes  路由地址
     * @param array          $option  路由参数
     * @param array          $pattern 变量规则
     * @return void
     */
    public static function group($name, $routes, $option = [], $pattern = [])
    {
        if (is_array($name)) {
            $option = $name;
            $name   = isset($option['name']) ? $option['name'] : '';
        }
        // 分组
        $currentGroup = self::getGroup('name');
        if ($currentGroup) {
            $name = $currentGroup . ($name ? '/' . ltrim($name, '/') : '');
        }
        if (!empty($name)) {
            if ($routes instanceof \Closure) {
                $currentOption  = self::getGroup('option');
                $currentPattern = self::getGroup('pattern');
                self::setGroup($name, array_merge($currentOption, $option), array_merge($currentPattern, $pattern));
                call_user_func_array($routes, []);
                self::setGroup($currentGroup, $currentOption, $currentPattern);
                if ($currentGroup != $name) {
                    self::$childrules['*'][$name]['route']   = '';
                    self::$childrules['*'][$name]['var']     = self::parseVar($name);
                    self::$childrules['*'][$name]['option']  = $option;
                    self::$childrules['*'][$name]['pattern'] = $pattern;
                }
            } else {
                $item          = [];
                $completeMatch = Config::get('route_complete_match');
                foreach ($routes as $key => $val) {
                    if (is_numeric($key)) {
                        $key = array_shift($val);
                    }
                    if (is_array($val)) {
                        $route    = $val[0];
                        $option1  = array_merge($option, isset($val[1]) ? $val[1] : []);
                        $pattern1 = array_merge($pattern, isset($val[2]) ? $val[2] : []);
                    } else {
                        $route = $val;
                    }

                    $options  = isset($option1) ? $option1 : $option;
                    $patterns = isset($pattern1) ? $pattern1 : $pattern;
                    if ('$' == substr($key, -1, 1)) {
                        // 是否完整匹配
                        $options['complete_match'] = true;
                        $key                       = substr($key, 0, -1);
                    } elseif ($completeMatch) {
                        $options['complete_match'] = true;
                    }
                    $key    = trim($key, '/');
                    $vars   = self::parseVar($key);
                    $item[] = ['rule' => $key, 'route' => $route, 'var' => $vars, 'option' => $options, 'pattern' => $patterns];
                    // 设置路由标识
                    $suffix = isset($options['ext']) ? $options['ext'] : null;
                    self::name($route, [$name . ($key ? '/' . $key : ''), $vars, self::$domain, $suffix]);
                }
                self::$childrules['*'][$name] = ['rule' => $item, 'route' => '', 'var' => [], 'option' => $option, 'pattern' => $pattern];
            }

            foreach (['get', 'post', 'put', 'delete', 'patch', 'head', 'options'] as $method) {
                if (!isset(self::$childrules[$method][$name])) {
                    self::$childrules[$method][$name] = true;
                } elseif (is_array(self::$childrules[$method][$name])) {
                    self::$childrules[$method][$name] = array_merge(self::$childrules['*'][$name], self::$childrules[$method][$name]);
                }
            }

        } elseif ($routes instanceof \Closure) {
            // 闭包注册
            $currentOption  = self::getGroup('option');
            $currentPattern = self::getGroup('pattern');
            self::setGroup('', array_merge($currentOption, $option), array_merge($currentPattern, $pattern));
            call_user_func_array($routes, []);
            self::setGroup($currentGroup, $currentOption, $currentPattern);
        } else {
            // 批量注册路由
            self::rule($routes, '', '*', $option, $pattern);
        }
    }

    /**
     * 注册路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function any($childrule, $route = '', $option = [], $pattern = [])
    {
        self::rule($childrule, $route, '*', $option, $pattern);
    }

    /**
     * 注册GET路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function get($childrule, $route = '', $option = [], $pattern = [])
    {
        self::rule($childrule, $route, 'GET', $option, $pattern);
    }

    /**
     * 注册POST路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function post($childrule, $route = '', $option = [], $pattern = [])
    {
        self::rule($childrule, $route, 'POST', $option, $pattern);
    }

    /**
     * 注册PUT路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function put($childrule, $route = '', $option = [], $pattern = [])
    {
        self::rule($childrule, $route, 'PUT', $option, $pattern);
    }

    /**
     * 注册DELETE路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function delete($childrule, $route = '', $option = [], $pattern = [])
    {
        self::rule($childrule, $route, 'DELETE', $option, $pattern);
    }

    /**
     * 注册PATCH路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function patch($childrule, $route = '', $option = [], $pattern = [])
    {
        self::rule($childrule, $route, 'PATCH', $option, $pattern);
    }

    /**
     * 注册资源路由
     * @access public
     * @param string|array $childrule    路由规则
     * @param string       $route   路由地址
     * @param array        $option  路由参数
     * @param array        $pattern 变量规则
     * @return void
     */
    public static function resource($childrule, $route = '', $option = [], $pattern = [])
    {
        if (is_array($childrule)) {
            foreach ($childrule as $key => $val) {
                if (is_array($val)) {
                    list($val, $option, $pattern) = array_pad($val, 3, []);
                }
                self::resource($key, $val, $option, $pattern);
            }
        } else {
            if (strpos($childrule, '.')) {
                // 注册嵌套资源路由
                $array = explode('.', $childrule);
                $last  = array_pop($array);
                $item  = [];
                foreach ($array as $val) {
                    $item[] = $val . '/:' . (isset($option['var'][$val]) ? $option['var'][$val] : $val . '_id');
                }
                $childrule = implode('/', $item) . '/' . $last;
            }
            // 注册资源路由
            foreach (self::$rest as $key => $val) {
                if ((isset($option['only']) && !in_array($key, $option['only']))
                    || (isset($option['except']) && in_array($key, $option['except']))) {
                    continue;
                }
                if (isset($last) && strpos($val[1], ':id') && isset($option['var'][$last])) {
                    $val[1] = str_replace(':id', ':' . $option['var'][$last], $val[1]);
                } elseif (strpos($val[1], ':id') && isset($option['var'][$childrule])) {
                    $val[1] = str_replace(':id', ':' . $option['var'][$childrule], $val[1]);
                }
                $item           = ltrim($childrule . $val[1], '/');
                $option['rest'] = $key;
                self::rule($item . '$', $route . '/' . $val[2], $val[0], $option, $pattern);
            }
        }
    }

    /**
     * 注册控制器路由 操作方法对应不同的请求后缀
     * @access public
     * @param string $childrule    路由规则
     * @param string $route   路由地址
     * @param array  $option  路由参数
     * @param array  $pattern 变量规则
     * @return void
     */
    public static function controller($childrule, $route = '', $option = [], $pattern = [])
    {
        foreach (self::$methodPrefix as $type => $val) {
            self::$type($childrule . '/:action', $route . '/' . $val . ':action', $option, $pattern);
        }
    }

    /**
     * 注册别名路由
     * @access public
     * @param string|array $childrule   路由别名
     * @param string       $route  路由地址
     * @param array        $option 路由参数
     * @return void
     */
    public static function alias($childrule = null, $route = '', $option = [])
    {
        if (is_array($childrule)) {
            self::$childrules['alias'] = array_merge(self::$childrules['alias'], $childrule);
        } else {
            self::$childrules['alias'][$childrule] = $option ? [$route, $option] : $route;
        }
    }

    /**
     * 设置不同请求类型下面的方法前缀
     * @access public
     * @param string $method 请求类型
     * @param string $prefix 类型前缀
     * @return void
     */
    public static function setMethodPrefix($method, $prefix = '')
    {
        if (is_array($method)) {
            self::$methodPrefix = array_merge(self::$methodPrefix, array_change_key_case($method));
        } else {
            self::$methodPrefix[strtolower($method)] = $prefix;
        }
    }

    /**
     * rest方法定义和修改
     * @access public
     * @param string|array $name     方法名称
     * @param array|bool   $resource 资源
     * @return void
     */
    public static function rest($name, $resource = [])
    {
        if (is_array($name)) {
            self::$rest = $resource ? $name : array_merge(self::$rest, $name);
        } else {
            self::$rest[$name] = $resource;
        }
    }

    /**
     * 注册未匹配路由规则后的处理
     * @access public
     * @param string $route  路由地址
     * @param string $method 请求类型
     * @param array  $option 路由参数
     * @return void
     */
    public static function miss($route, $method = '*', $option = [])
    {
        self::rule('__miss__', $route, $method, $option, []);
    }

    /**
     * 注册一个自动解析的URL路由
     * @access public
     * @param string $route 路由地址
     * @return void
     */
    public static function auto($route)
    {
        self::rule('__auto__', $route, '*', [], []);
    }

    /**
     * 获取或者批量设置路由定义
     * @access public
     * @param mixed $childrules 请求类型或者路由定义数组
     * @return array
     */
    public static function rules($childrules = '')
    {
        if (is_array($childrules)) {
            self::$childrules = $childrules;
        } elseif ($childrules) {
            return true === $childrules ? self::$childrules : self::$childrules[strtolower($childrules)];
        } else {
            $childrules = self::$childrules;
            unset($childrules['pattern'], $childrules['alias'], $childrules['domain'], $childrules['name']);
            return $childrules;
        }
    }

    /**
     * 检测子域名部署
     * @access public
     * @param Request $request      Request请求对象
     * @param array   $currentRules 当前路由规则
     * @param string  $method       请求类型
     * @return void
     */
    public static function checkDomain($request, &$currentRules, $method = 'get')
    {
        // 域名规则
        $childrules = self::$childrules['domain'];
        // 开启子域名部署 支持二级和三级域名
        if (!empty($childrules)) {
            $host = $request->host(true);
            if (isset($childrules[$host])) {
                // 完整域名或者IP配置
                $item = $childrules[$host];
            } else {
                $rootDomain = Config::get('url_domain_root');
                if ($rootDomain) {
                    // 配置域名根 例如 thinkphp.cn 163.com.cn 如果是国家级域名 com.cn net.cn 之类的域名需要配置
                    $domain = explode('.', rtrim(stristr($host, $rootDomain, true), '.'));
                } else {
                    $domain = explode('.', $host, -2);
                }
                // 子域名配置
                if (!empty($domain)) {
                    // 当前子域名
                    $subDomain       = implode('.', $domain);
                    self::$subDomain = $subDomain;
                    $domain2         = array_pop($domain);
                    if ($domain) {
                        // 存在三级域名
                        $domain3 = array_pop($domain);
                    }
                    if ($subDomain && isset($childrules[$subDomain])) {
                        // 子域名配置
                        $item = $childrules[$subDomain];
                    } elseif (isset($childrules['*.' . $domain2]) && !empty($domain3)) {
                        // 泛三级域名
                        $item      = $childrules['*.' . $domain2];
                        $panDomain = $domain3;
                    } elseif (isset($childrules['*']) && !empty($domain2)) {
                        // 泛二级域名
                        if ('www' != $domain2) {
                            $item      = $childrules['*'];
                            $panDomain = $domain2;
                        }
                    }
                }
            }
            if (!empty($item)) {
                if (isset($panDomain)) {
                    // 保存当前泛域名
                    $request->route(['__domain__' => $panDomain]);
                }
                if (isset($item['[bind]'])) {
                    // 解析子域名部署规则
                    list($childrule, $option, $pattern) = $item['[bind]'];
                    if (!empty($option['https']) && !$request->isSsl()) {
                        // https检测
                        throw new HttpException(404, 'must use https request:' . $host);
                    }

                    if (strpos($childrule, '?')) {
                        // 传入其它参数
                        $array  = parse_url($childrule);
                        $result = $array['path'];
                        parse_str($array['query'], $params);
                        if (isset($panDomain)) {
                            $pos = array_search('*', $params);
                            if (false !== $pos) {
                                // 泛域名作为参数
                                $params[$pos] = $panDomain;
                            }
                        }
                        $_GET = array_merge($_GET, $params);
                    } else {
                        $result = $childrule;
                    }

                    if (0 === strpos($result, '\\')) {
                        // 绑定到命名空间 例如 \app\index\behavior
                        self::$bind = ['type' => 'namespace', 'namespace' => $result];
                    } elseif (0 === strpos($result, '@')) {
                        // 绑定到类 例如 @app\index\controller\User
                        self::$bind = ['type' => 'class', 'class' => substr($result, 1)];
                    } else {
                        // 绑定到模块/控制器 例如 index/user
                        self::$bind = ['type' => 'module', 'module' => $result];
                    }
                    self::$domainBind = true;
                } else {
                    self::$domainRule = $item;
                    $currentRules     = isset($item[$method]) ? $item[$method] : $item['*'];
                }
            }
        }
    }

    /**
     * 检测URL路由
     * @access public
     * @param Request $request     Request请求对象
     * @param string  $url         URL地址
     * @param string  $depr        URL分隔符
     * @param bool    $checkDomain 是否检测域名规则
     * @return false|array
     */
    public static function check($request, $url, $depr = '/', $checkDomain = false)
    {
        //检查解析缓存
        if (!App::$debug && Config::get('route_check_cache')) {
            $key = self::getCheckCacheKey($request);
            if (Cache::has($key)) {
                list($childrule, $route, $pathinfo, $option, $matches) = Cache::get($key);
                return self::parseRule($childrule, $route, $pathinfo, $option, $matches, true);
            }
        }

        // 分隔符替换 确保路由定义使用统一的分隔符
        $url = str_replace($depr, '|', $url);

        if (isset(self::$childrules['alias'][$url]) || isset(self::$childrules['alias'][strstr($url, '|', true)])) {
            // 检测路由别名
            $result = self::checkRouteAlias($request, $url, $depr);
            if (false !== $result) {
                return $result;
            }
        }
        $method = strtolower($request->method());
        // 获取当前请求类型的路由规则
        $childrules = isset(self::$childrules[$method]) ? self::$childrules[$method] : [];
        // 检测域名部署
        if ($checkDomain) {
            self::checkDomain($request, $childrules, $method);
        }
        // 检测URL绑定
        $return = self::checkUrlBind($url, $childrules, $depr);
        if (false !== $return) {
            return $return;
        }
        if ('|' != $url) {
            $url = rtrim($url, '|');
        }
        $item = str_replace('|', '/', $url);
        if (isset($childrules[$item])) {
            // 静态路由规则检测
            $childrule = $childrules[$item];
            if (true === $childrule) {
                $childrule = self::getRouteExpress($item);
            }
            if (!empty($childrule['route']) && self::checkOption($childrule['option'], $request)) {
                self::setOption($childrule['option']);
                return self::parseRule($item, $childrule['route'], $url, $childrule['option']);
            }
        }

        // 路由规则检测
        if (!empty($childrules)) {
            return self::checkRoute($request, $childrules, $url, $depr);
        }
        return false;
    }

    private static function getRouteExpress($key)
    {
        return self::$domainRule ? self::$domainRule['*'][$key] : self::$childrules['*'][$key];
    }

    /**
     * 检测路由规则
     * @access private
     * @param Request $request
     * @param array   $childrules   路由规则
     * @param string  $url     URL地址
     * @param string  $depr    URL分割符
     * @param string  $group   路由分组名
     * @param array   $options 路由参数（分组）
     * @return mixed
     */
    private static function checkRoute($request, $childrules, $url, $depr = '/', $group = '', $options = [])
    {
        foreach ($childrules as $key => $item) {
            if (true === $item) {
                $item = self::getRouteExpress($key);
            }
            if (!isset($item['rule'])) {
                continue;
            }
            $childrule    = $item['rule'];
            $route   = $item['route'];
            $vars    = $item['var'];
            $option  = $item['option'];
            $pattern = $item['pattern'];

            // 检查参数有效性
            if (!self::checkOption($option, $request)) {
                continue;
            }

            if (isset($option['ext'])) {
                // 路由ext参数 优先于系统配置的URL伪静态后缀参数
                $url = preg_replace('/\.' . $request->ext() . '$/i', '', $url);
            }

            if (is_array($childrule)) {
                // 分组路由
                $pos = strpos(str_replace('<', ':', $key), ':');
                if (false !== $pos) {
                    $str = substr($key, 0, $pos);
                } else {
                    $str = $key;
                }
                if (is_string($str) && $str && 0 !== stripos(str_replace('|', '/', $url), $str)) {
                    continue;
                }
                self::setOption($option);
                $result = self::checkRoute($request, $childrule, $url, $depr, $key, $option);
                if (false !== $result) {
                    return $result;
                }
            } elseif ($route) {
                if ('__miss__' == $childrule || '__auto__' == $childrule) {
                    // 指定特殊路由
                    $var    = trim($childrule, '__');
                    ${$var} = $item;
                    continue;
                }
                if ($group) {
                    $childrule = $group . ($childrule ? '/' . ltrim($childrule, '/') : '');
                }

                self::setOption($option);
                if (isset($options['bind_model']) && isset($option['bind_model'])) {
                    $option['bind_model'] = array_merge($options['bind_model'], $option['bind_model']);
                }
                $result = self::checkRule($childrule, $route, $url, $pattern, $option, $depr);
                if (false !== $result) {
                    return $result;
                }
            }
        }
        if (isset($auto)) {
            // 自动解析URL地址
            return self::parseUrl($auto['route'] . '/' . $url, $depr);
        } elseif (isset($miss)) {
            // 未匹配所有路由的路由规则处理
            return self::parseRule('', $miss['route'], $url, $miss['option']);
        }
        return false;
    }

    /**
     * 检测路由别名
     * @access private
     * @param Request $request
     * @param string  $url  URL地址
     * @param string  $depr URL分隔符
     * @return mixed
     */
    private static function checkRouteAlias($request, $url, $depr)
    {
        $array = explode('|', $url);
        $alias = array_shift($array);
        $item  = self::$childrules['alias'][$alias];

        if (is_array($item)) {
            list($childrule, $option) = $item;
            $action              = $array[0];
            if (isset($option['allow']) && !in_array($action, explode(',', $option['allow']))) {
                // 允许操作
                return false;
            } elseif (isset($option['except']) && in_array($action, explode(',', $option['except']))) {
                // 排除操作
                return false;
            }
            if (isset($option['method'][$action])) {
                $option['method'] = $option['method'][$action];
            }
        } else {
            $childrule = $item;
        }
        $bind = implode('|', $array);
        // 参数有效性检查
        if (isset($option) && !self::checkOption($option, $request)) {
            // 路由不匹配
            return false;
        } elseif (0 === strpos($childrule, '\\')) {
            // 路由到类
            return self::bindToClass($bind, substr($childrule, 1), $depr);
        } elseif (0 === strpos($childrule, '@')) {
            // 路由到控制器类
            return self::bindToController($bind, substr($childrule, 1), $depr);
        } else {
            // 路由到模块/控制器
            return self::bindToModule($bind, $childrule, $depr);
        }
    }

    /**
     * 检测URL绑定
     * @access private
     * @param string $url   URL地址
     * @param array  $childrules 路由规则
     * @param string $depr  URL分隔符
     * @return mixed
     */
    private static function checkUrlBind(&$url, &$childrules, $depr = '/')
    {
        if (!empty(self::$bind)) {
            $type = self::$bind['type'];
            $bind = self::$bind[$type];
            // 记录绑定信息
            App::$debug && Log::record('[ BIND ] ' . var_export($bind, true), 'info');
            // 如果有URL绑定 则进行绑定检测
            switch ($type) {
                case 'class':
                    // 绑定到类
                    return self::bindToClass($url, $bind, $depr);
                case 'controller':
                    // 绑定到控制器类
                    return self::bindToController($url, $bind, $depr);
                case 'namespace':
                    // 绑定到命名空间
                    return self::bindToNamespace($url, $bind, $depr);
            }
        }
        return false;
    }

    /**
     * 绑定到类
     * @access public
     * @param string $url   URL地址
     * @param string $class 类名（带命名空间）
     * @param string $depr  URL分隔符
     * @return array
     */
    public static function bindToClass($url, $class, $depr = '/')
    {
        $url    = str_replace($depr, '|', $url);
        $array  = explode('|', $url, 2);
        $action = !empty($array[0]) ? $array[0] : Config::get('default_action');
        if (!empty($array[1])) {
            self::parseUrlParams($array[1]);
        }
        return ['type' => 'method', 'method' => [$class, $action], 'var' => []];
    }

    /**
     * 绑定到命名空间
     * @access public
     * @param string $url       URL地址
     * @param string $namespace 命名空间
     * @param string $depr      URL分隔符
     * @return array
     */
    public static function bindToNamespace($url, $namespace, $depr = '/')
    {
        $url    = str_replace($depr, '|', $url);
        $array  = explode('|', $url, 3);
        $class  = !empty($array[0]) ? $array[0] : Config::get('default_controller');
        $method = !empty($array[1]) ? $array[1] : Config::get('default_action');
        if (!empty($array[2])) {
            self::parseUrlParams($array[2]);
        }
        return ['type' => 'method', 'method' => [$namespace . '\\' . Loader::parseName($class, 1), $method], 'var' => []];
    }

    /**
     * 绑定到控制器类
     * @access public
     * @param string $url        URL地址
     * @param string $controller 控制器名 （支持带模块名 index/user ）
     * @param string $depr       URL分隔符
     * @return array
     */
    public static function bindToController($url, $controller, $depr = '/')
    {
        $url    = str_replace($depr, '|', $url);
        $array  = explode('|', $url, 2);
        $action = !empty($array[0]) ? $array[0] : Config::get('default_action');
        if (!empty($array[1])) {
            self::parseUrlParams($array[1]);
        }
        return ['type' => 'controller', 'controller' => $controller . '/' . $action, 'var' => []];
    }

    /**
     * 绑定到模块/控制器
     * @access public
     * @param string $url        URL地址
     * @param string $controller 控制器类名（带命名空间）
     * @param string $depr       URL分隔符
     * @return array
     */
    public static function bindToModule($url, $controller, $depr = '/')
    {
        $url    = str_replace($depr, '|', $url);
        $array  = explode('|', $url, 2);
        $action = !empty($array[0]) ? $array[0] : Config::get('default_action');
        if (!empty($array[1])) {
            self::parseUrlParams($array[1]);
        }
        return ['type' => 'module', 'module' => $controller . '/' . $action];
    }

    /**
     * 路由参数有效性检查
     * @access private
     * @param array   $option  路由参数
     * @param Request $request Request对象
     * @return bool
     */
    private static function checkOption($option, $request)
    {
        if ((isset($option['method']) && is_string($option['method']) && false === stripos($option['method'], $request->method()))
            || (isset($option['ajax']) && $option['ajax'] && !$request->isAjax()) // Ajax检测
             || (isset($option['ajax']) && !$option['ajax'] && $request->isAjax()) // 非Ajax检测
             || (isset($option['pjax']) && $option['pjax'] && !$request->isPjax()) // Pjax检测
             || (isset($option['pjax']) && !$option['pjax'] && $request->isPjax()) // 非Pjax检测
             || (isset($option['ext']) && false === stripos('|' . $option['ext'] . '|', '|' . $request->ext() . '|')) // 伪静态后缀检测
             || (isset($option['deny_ext']) && false !== stripos('|' . $option['deny_ext'] . '|', '|' . $request->ext() . '|'))
            || (isset($option['domain']) && !in_array($option['domain'], [$_SERVER['HTTP_HOST'], self::$subDomain])) // 域名检测
             || (isset($option['https']) && $option['https'] && !$request->isSsl()) // https检测
             || (isset($option['https']) && !$option['https'] && $request->isSsl()) // https检测
             || (!empty($option['before_behavior']) && false === Hook::exec($option['before_behavior'])) // 行为检测
             || (!empty($option['callback']) && is_callable($option['callback']) && false === call_user_func($option['callback'])) // 自定义检测
        ) {
            return false;
        }
        return true;
    }

    /**
     * 检测路由规则
     * @access private
     * @param string $childrule    路由规则
     * @param string $route   路由地址
     * @param string $url     URL地址
     * @param array  $pattern 变量规则
     * @param array  $option  路由参数
     * @param string $depr    URL分隔符（全局）
     * @return array|false
     */
    private static function checkRule($childrule, $route, $url, $pattern, $option, $depr)
    {
        // 检查完整规则定义
        if (isset($pattern['__url__']) && !preg_match(0 === strpos($pattern['__url__'], '/') ? $pattern['__url__'] : '/^' . $pattern['__url__'] . '/', str_replace('|', $depr, $url))) {
            return false;
        }
        // 检查路由的参数分隔符
        if (isset($option['param_depr'])) {
            $url = str_replace(['|', $option['param_depr']], [$depr, '|'], $url);
        }

        $len1 = substr_count($url, '|');
        $len2 = substr_count($childrule, '/');
        // 多余参数是否合并
        $merge = !empty($option['merge_extra_vars']);
        if ($merge && $len1 > $len2) {
            $url = str_replace('|', $depr, $url);
            $url = implode('|', explode($depr, $url, $len2 + 1));
        }

        if ($len1 >= $len2 || strpos($childrule, '[')) {
            if (!empty($option['complete_match'])) {
                // 完整匹配
                if (!$merge && $len1 != $len2 && (false === strpos($childrule, '[') || $len1 > $len2 || $len1 < $len2 - substr_count($childrule, '['))) {
                    return false;
                }
            }
            $pattern = array_merge(self::$childrules['pattern'], $pattern);
            if (false !== $match = self::match($url, $childrule, $pattern)) {
                // 匹配到路由规则
                return self::parseRule($childrule, $route, $url, $option, $match);
            }
        }
        return false;
    }

    /**
     * 解析模块的URL地址 [模块/控制器/操作?]参数1=值1&参数2=值2...
     * @access public
     * @param string $url        URL地址
     * @param string $depr       URL分隔符
     * @param bool   $autoSearch 是否自动深度搜索控制器
     * @return array
     */
    public static function parseUrl($url, $depr = '/', $autoSearch = false)
    {

        if (isset(self::$bind['module'])) {
            $bind = str_replace('/', $depr, self::$bind['module']);
            // 如果有模块/控制器绑定
            $url = $bind . ('.' != substr($bind, -1) ? $depr : '') . ltrim($url, $depr);
        }
        $url              = str_replace($depr, '|', $url);
        list($path, $var) = self::parseUrlPath($url);
        $route            = [null, null, null];
        if (isset($path)) {
            // 解析模块
            $module = Config::get('app_multi_module') ? array_shift($path) : null;
            if ($autoSearch) {
                // 自动搜索控制器
                $dir    = APP_PATH . ($module ? $module . DS : '') . Config::get('url_controller_layer');
                $suffix = App::$suffix || Config::get('controller_suffix') ? ucfirst(Config::get('url_controller_layer')) : '';
                $item   = [];
                $find   = false;
                foreach ($path as $val) {
                    $item[] = $val;
                    $file   = $dir . DS . str_replace('.', DS, $val) . $suffix . EXT;
                    $file   = pathinfo($file, PATHINFO_DIRNAME) . DS . Loader::parseName(pathinfo($file, PATHINFO_FILENAME), 1) . EXT;
                    if (is_file($file)) {
                        $find = true;
                        break;
                    } else {
                        $dir .= DS . Loader::parseName($val);
                    }
                }
                if ($find) {
                    $controller = implode('.', $item);
                    $path       = array_slice($path, count($item));
                } else {
                    $controller = array_shift($path);
                }
            } else {
                // 解析控制器
                $controller = !empty($path) ? array_shift($path) : null;
            }
            // 解析操作
            $action = !empty($path) ? array_shift($path) : null;
            // 解析额外参数
            self::parseUrlParams(empty($path) ? '' : implode('|', $path));
            // 封装路由
            $route = [$module, $controller, $action];
            // 检查地址是否被定义过路由
            $name  = strtolower($module . '/' . Loader::parseName($controller, 1) . '/' . $action);
            $name2 = '';
            if (empty($module) || isset($bind) && $module == $bind) {
                $name2 = strtolower(Loader::parseName($controller, 1) . '/' . $action);
            }

            if (isset(self::$childrules['name'][$name]) || isset(self::$childrules['name'][$name2])) {
                throw new HttpException(404, 'invalid request:' . str_replace('|', $depr, $url));
            }
        }
        return ['type' => 'module', 'module' => $route];
    }

    /**
     * 解析URL的pathinfo参数和变量
     * @access private
     * @param string $url URL地址
     * @return array
     */
    private static function parseUrlPath($url)
    {
        // 分隔符替换 确保路由定义使用统一的分隔符
        $url = str_replace('|', '/', $url);
        $url = trim($url, '/');
        $var = [];
        if (false !== strpos($url, '?')) {
            // [模块/控制器/操作?]参数1=值1&参数2=值2...
            $info = parse_url($url);
            $path = explode('/', $info['path']);
            parse_str($info['query'], $var);
        } elseif (strpos($url, '/')) {
            // [模块/控制器/操作]
            $path = explode('/', $url);
        } else {
            $path = [$url];
        }
        return [$path, $var];
    }

    /**
     * 检测URL和规则路由是否匹配
     * @access private
     * @param string $url     URL地址
     * @param string $childrule    路由规则
     * @param array  $pattern 变量规则
     * @return array|false
     */
    private static function match($url, $childrule, $pattern)
    {
        $m2 = explode('/', $childrule);
        $m1 = explode('|', $url);

        $var = [];
        foreach ($m2 as $key => $val) {
            // val中定义了多个变量 <id><name>
            if (false !== strpos($val, '<') && preg_match_all('/<(\w+(\??))>/', $val, $matches)) {
                $value   = [];
                $replace = [];
                foreach ($matches[1] as $name) {
                    if (strpos($name, '?')) {
                        $name      = substr($name, 0, -1);
                        $replace[] = '(' . (isset($pattern[$name]) ? $pattern[$name] : '\w+') . ')?';
                    } else {
                        $replace[] = '(' . (isset($pattern[$name]) ? $pattern[$name] : '\w+') . ')';
                    }
                    $value[] = $name;
                }
                $val = str_replace($matches[0], $replace, $val);
                if (preg_match('/^' . $val . '$/', isset($m1[$key]) ? $m1[$key] : '', $match)) {
                    array_shift($match);
                    foreach ($value as $k => $name) {
                        if (isset($match[$k])) {
                            $var[$name] = $match[$k];
                        }
                    }
                    continue;
                } else {
                    return false;
                }
            }

            if (0 === strpos($val, '[:')) {
                // 可选参数
                $val      = substr($val, 1, -1);
                $optional = true;
            } else {
                $optional = false;
            }
            if (0 === strpos($val, ':')) {
                // URL变量
                $name = substr($val, 1);
                if (!$optional && !isset($m1[$key])) {
                    return false;
                }
                if (isset($m1[$key]) && isset($pattern[$name])) {
                    // 检查变量规则
                    if ($pattern[$name] instanceof \Closure) {
                        $result = call_user_func_array($pattern[$name], [$m1[$key]]);
                        if (false === $result) {
                            return false;
                        }
                    } elseif (!preg_match(0 === strpos($pattern[$name], '/') ? $pattern[$name] : '/^' . $pattern[$name] . '$/', $m1[$key])) {
                        return false;
                    }
                }
                $var[$name] = isset($m1[$key]) ? $m1[$key] : '';
            } elseif (!isset($m1[$key]) || 0 !== strcasecmp($val, $m1[$key])) {
                return false;
            }
        }
        // 成功匹配后返回URL中的动态变量数组
        return $var;
    }

    /**
     * 解析规则路由
     * @access private
     * @param string $childrule      路由规则
     * @param string $route     路由地址
     * @param string $pathinfo  URL地址
     * @param array  $option    路由参数
     * @param array  $matches   匹配的变量
     * @param bool   $fromCache 通过缓存解析
     * @return array
     */
    private static function parseRule($childrule, $route, $pathinfo, $option = [], $matches = [], $fromCache = false)
    {
        $request = Request::instance();

        //保存解析缓存
        if (Config::get('route_check_cache') && !$fromCache) {
            try {
                $key = self::getCheckCacheKey($request);
                Cache::tag('route_check')->set($key, [$childrule, $route, $pathinfo, $option, $matches]);
            } catch (\Exception $e) {

            }
        }

        // 解析路由规则
        if ($childrule) {
            $childrule = explode('/', $childrule);
            // 获取URL地址中的参数
            $paths = explode('|', $pathinfo);
            foreach ($childrule as $item) {
                $fun = '';
                if (0 === strpos($item, '[:')) {
                    $item = substr($item, 1, -1);
                }
                if (0 === strpos($item, ':')) {
                    $var           = substr($item, 1);
                    $matches[$var] = array_shift($paths);
                } else {
                    // 过滤URL中的静态变量
                    array_shift($paths);
                }
            }
        } else {
            $paths = explode('|', $pathinfo);
        }

        // 获取路由地址规则
        if (is_string($route) && isset($option['prefix'])) {
            // 路由地址前缀
            $route = $option['prefix'] . $route;
        }
        // 替换路由地址中的变量
        if (is_string($route) && !empty($matches)) {
            foreach ($matches as $key => $val) {
                if (false !== strpos($route, ':' . $key)) {
                    $route = str_replace(':' . $key, $val, $route);
                }
            }
        }

        // 绑定模型数据
        if (isset($option['bind_model'])) {
            $bind = [];
            foreach ($option['bind_model'] as $key => $val) {
                if ($val instanceof \Closure) {
                    $result = call_user_func_array($val, [$matches]);
                } else {
                    if (is_array($val)) {
                        $fields    = explode('&', $val[1]);
                        $model     = $val[0];
                        $exception = isset($val[2]) ? $val[2] : true;
                    } else {
                        $fields    = ['id'];
                        $model     = $val;
                        $exception = true;
                    }
                    $where = [];
                    $match = true;
                    foreach ($fields as $field) {
                        if (!isset($matches[$field])) {
                            $match = false;
                            break;
                        } else {
                            $where[$field] = $matches[$field];
                        }
                    }
                    if ($match) {
                        $query  = strpos($model, '\\') ? $model::where($where) : Loader::model($model)->where($where);
                        $result = $query->failException($exception)->find();
                    }
                }
                if (!empty($result)) {
                    $bind[$key] = $result;
                }
            }
            $request->bind($bind);
        }

        if (!empty($option['response'])) {
            Hook::add('response_send', $option['response']);
        }

        // 解析额外参数
        self::parseUrlParams(empty($paths) ? '' : implode('|', $paths), $matches);
        // 记录匹配的路由信息
        $request->routeInfo(['rule' => $childrule, 'route' => $route, 'option' => $option, 'var' => $matches]);

        // 检测路由after行为
        if (!empty($option['after_behavior'])) {
            if ($option['after_behavior'] instanceof \Closure) {
                $result = call_user_func_array($option['after_behavior'], []);
            } else {
                foreach ((array) $option['after_behavior'] as $behavior) {
                    $result = Hook::exec($behavior, '');
                    if (!is_null($result)) {
                        break;
                    }
                }
            }
            // 路由规则重定向
            if ($result instanceof Response) {
                return ['type' => 'response', 'response' => $result];
            } elseif (is_array($result)) {
                return $result;
            }
        }

        if ($route instanceof \Closure) {
            // 执行闭包
            $result = ['type' => 'function', 'function' => $route];
        } elseif (0 === strpos($route, '/') || strpos($route, '://')) {
            // 路由到重定向地址
            $result = ['type' => 'redirect', 'url' => $route, 'status' => isset($option['status']) ? $option['status'] : 301];
        } elseif (false !== strpos($route, '\\')) {
            // 路由到方法
            list($path, $var) = self::parseUrlPath($route);
            $route            = str_replace('/', '@', implode('/', $path));
            $method           = strpos($route, '@') ? explode('@', $route) : $route;
            $result           = ['type' => 'method', 'method' => $method, 'var' => $var];
        } elseif (0 === strpos($route, '@')) {
            // 路由到控制器
            $route             = substr($route, 1);
            list($route, $var) = self::parseUrlPath($route);
            $result            = ['type' => 'controller', 'controller' => implode('/', $route), 'var' => $var];
            $request->action(array_pop($route));
            $request->controller($route ? array_pop($route) : Config::get('default_controller'));
            $request->module($route ? array_pop($route) : Config::get('default_module'));
            App::$modulePath = APP_PATH . (Config::get('app_multi_module') ? $request->module() . DS : '');
        } else {
            // 路由到模块/控制器/操作
            $result = self::parseModule($route, isset($option['convert']) ? $option['convert'] : false);
        }
        // 开启请求缓存
        if ($request->isGet() && isset($option['cache'])) {
            $cache = $option['cache'];
            if (is_array($cache)) {
                list($key, $expire, $tag) = array_pad($cache, 3, null);
            } else {
                $key    = str_replace('|', '/', $pathinfo);
                $expire = $cache;
                $tag    = null;
            }
            $request->cache($key, $expire, $tag);
        }
        return $result;
    }

    /**
     * 解析URL地址为 模块/控制器/操作
     * @access private
     * @param string $url     URL地址
     * @param bool   $convert 是否自动转换URL地址
     * @return array
     */
    private static function parseModule($url, $convert = false)
    {
        list($path, $var) = self::parseUrlPath($url);
        $action           = array_pop($path);
        $controller       = !empty($path) ? array_pop($path) : null;
        $module           = Config::get('app_multi_module') && !empty($path) ? array_pop($path) : null;
        $method           = Request::instance()->method();
        if (Config::get('use_action_prefix') && !empty(self::$methodPrefix[$method])) {
            // 操作方法前缀支持
            $action = 0 !== strpos($action, self::$methodPrefix[$method]) ? self::$methodPrefix[$method] . $action : $action;
        }
        // 设置当前请求的路由变量
        Request::instance()->route($var);
        // 路由到模块/控制器/操作
        return ['type' => 'module', 'module' => [$module, $controller, $action], 'convert' => $convert];
    }

    /**
     * 解析URL地址中的参数Request对象
     * @access private
     * @param string $url 路由规则
     * @param array  $var 变量
     * @return void
     */
    private static function parseUrlParams($url, &$var = [])
    {
        if ($url) {
            if (Config::get('url_param_type')) {
                $var += explode('|', $url);
            } else {
                preg_replace_callback('/(\w+)\|([^\|]+)/', function ($match) use (&$var) {
                    $var[$match[1]] = strip_tags($match[2]);
                }, $url);
            }
        }
        // 设置当前请求的参数
        Request::instance()->route($var);
    }

    // 分析路由规则中的变量
    private static function parseVar($childrule)
    {
        // 提取路由规则中的变量
        $var = [];
        foreach (explode('/', $childrule) as $val) {
            $optional = false;
            if (false !== strpos($val, '<') && preg_match_all('/<(\w+(\??))>/', $val, $matches)) {
                foreach ($matches[1] as $name) {
                    if (strpos($name, '?')) {
                        $name     = substr($name, 0, -1);
                        $optional = true;
                    } else {
                        $optional = false;
                    }
                    $var[$name] = $optional ? 2 : 1;
                }
            }

            if (0 === strpos($val, '[:')) {
                // 可选参数
                $optional = true;
                $val      = substr($val, 1, -1);
            }
            if (0 === strpos($val, ':')) {
                // URL变量
                $name       = substr($val, 1);
                $var[$name] = $optional ? 2 : 1;
            }
        }
        return $var;
    }

    /**
     * 获取路由解析缓存的key
     * @param Request $request
     * @return string
     */
    private static function getCheckCacheKey(Request $request)
    {
        static $key;

        if (empty($key)) {
            if ($callback = Config::get('route_check_cache_key')) {
                $key = call_user_func($callback, $request);
            } else {
                $key = "{$request->host(true)}|{$request->method()}|{$request->path()}";
            }
        }

        return $key;
    }
}
