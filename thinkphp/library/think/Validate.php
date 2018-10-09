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

use think\exception\ClassNotFoundException;

class Validate
{
    // 实例
    protected static $instance;

    // 自定义的验证类型
    protected static $type = [];

    // 验证类型别名
    protected $alias = [
        '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt', '=' => 'eq', 'same' => 'eq',
    ];

    // 当前验证的规则
    protected $childrule = [];

    // 验证提示信息
    protected $message = [];
    // 验证字段描述
    protected $field = [];

    // 验证规则默认提示信息
    protected static $typeMsg = [
        'require'     => ':attribute require',
        'number'      => ':attribute must be numeric',
        'integer'     => ':attribute must be integer',
        'float'       => ':attribute must be float',
        'boolean'     => ':attribute must be bool',
        'email'       => ':attribute not a valid email address',
        'array'       => ':attribute must be a array',
        'accepted'    => ':attribute must be yes,on or 1',
        'date'        => ':attribute not a valid datetime',
        'file'        => ':attribute not a valid file',
        'image'       => ':attribute not a valid image',
        'alpha'       => ':attribute must be alpha',
        'alphaNum'    => ':attribute must be alpha-numeric',
        'alphaDash'   => ':attribute must be alpha-numeric, dash, underscore',
        'activeUrl'   => ':attribute not a valid domain or ip',
        'chs'         => ':attribute must be chinese',
        'chsAlpha'    => ':attribute must be chinese or alpha',
        'chsAlphaNum' => ':attribute must be chinese,alpha-numeric',
        'chsDash'     => ':attribute must be chinese,alpha-numeric,underscore, dash',
        'url'         => ':attribute not a valid url',
        'ip'          => ':attribute not a valid ip',
        'dateFormat'  => ':attribute must be dateFormat of :rule',
        'in'          => ':attribute must be in :rule',
        'notIn'       => ':attribute be notin :rule',
        'between'     => ':attribute must between :1 - :2',
        'notBetween'  => ':attribute not between :1 - :2',
        'length'      => 'size of :attribute must be :rule',
        'max'         => 'max size of :attribute must be :rule',
        'min'         => 'min size of :attribute must be :rule',
        'after'       => ':attribute cannot be less than :rule',
        'before'      => ':attribute cannot exceed :rule',
        'expire'      => ':attribute not within :rule',
        'allowIp'     => 'access IP is not allowed',
        'denyIp'      => 'access IP denied',
        'confirm'     => ':attribute out of accord with :2',
        'different'   => ':attribute cannot be same with :2',
        'egt'         => ':attribute must greater than or equal :rule',
        'gt'          => ':attribute must greater than :rule',
        'elt'         => ':attribute must less than or equal :rule',
        'lt'          => ':attribute must less than :rule',
        'eq'          => ':attribute must equal :rule',
        'unique'      => ':attribute has exists',
        'regex'       => ':attribute not conform to the rules',
        'method'      => 'invalid Request method',
        'token'       => 'invalid token',
        'fileSize'    => 'filesize not match',
        'fileExt'     => 'extensions to upload is not allowed',
        'fileMime'    => 'mimetype to upload is not allowed',
    ];

    // 当前验证场景
    protected $currentScene = null;

    // 正则表达式 regex = ['zip'=>'\d{6}',...]
    protected $regex = [];

    // 验证场景 scene = ['edit'=>'name1,name2,...']
    protected $scene = [];

    // 验证失败错误信息
    protected $error = [];

    // 批量验证
    protected $batch = false;

    /**
     * 构造函数
     * @access public
     * @param array $childrules 验证规则
     * @param array $message 验证提示信息
     * @param array $field 验证字段描述信息
     */
    public function __construct(array $childrules = [], $message = [], $field = [])
    {
        $this->rule    = array_merge($this->rule, $childrules);
        $this->message = array_merge($this->message, $message);
        $this->field   = array_merge($this->field, $field);
    }

    /**
     * 实例化验证
     * @access public
     * @param array     $childrules 验证规则
     * @param array     $message 验证提示信息
     * @param array     $field 验证字段描述信息
     * @return Validate
     */
    public static function make($childrules = [], $message = [], $field = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($childrules, $message, $field);
        }
        return self::$instance;
    }

    /**
     * 添加字段验证规则
     * @access protected
     * @param string|array  $name  字段名称或者规则数组
     * @param mixed         $childrule  验证规则
     * @return Validate
     */
    public function rule($name, $childrule = '')
    {
        if (is_array($name)) {
            $this->rule = array_merge($this->rule, $name);
        } else {
            $this->rule[$name] = $childrule;
        }
        return $this;
    }

    /**
     * 注册验证（类型）规则
     * @access public
     * @param string    $type  验证规则类型
     * @param mixed     $callback callback方法(或闭包)
     * @return void
     */
    public static function extend($type, $callback = null)
    {
        if (is_array($type)) {
            self::$type = array_merge(self::$type, $type);
        } else {
            self::$type[$type] = $callback;
        }
    }

    /**
     * 设置验证规则的默认提示信息
     * @access protected
     * @param string|array  $type  验证规则类型名称或者数组
     * @param string        $msg  验证提示信息
     * @return void
     */
    public static function setTypeMsg($type, $msg = null)
    {
        if (is_array($type)) {
            self::$typeMsg = array_merge(self::$typeMsg, $type);
        } else {
            self::$typeMsg[$type] = $msg;
        }
    }

    /**
     * 设置提示信息
     * @access public
     * @param string|array  $name  字段名称
     * @param string        $message 提示信息
     * @return Validate
     */
    public function message($name, $message = '')
    {
        if (is_array($name)) {
            $this->message = array_merge($this->message, $name);
        } else {
            $this->message[$name] = $message;
        }
        return $this;
    }

    /**
     * 设置验证场景
     * @access public
     * @param string|array  $name  场景名或者场景设置数组
     * @param mixed         $fields 要验证的字段
     * @return Validate
     */
    public function scene($name, $fields = null)
    {
        if (is_array($name)) {
            $this->scene = array_merge($this->scene, $name);
        }if (is_null($fields)) {
            // 设置当前场景
            $this->currentScene = $name;
        } else {
            // 设置验证场景
            $this->scene[$name] = $fields;
        }
        return $this;
    }

    /**
     * 判断是否存在某个验证场景
     * @access public
     * @param string $name 场景名
     * @return bool
     */
    public function hasScene($name)
    {
        return isset($this->scene[$name]);
    }

    /**
     * 设置批量验证
     * @access public
     * @param bool $batch  是否批量验证
     * @return Validate
     */
    public function batch($batch = true)
    {
        $this->batch = $batch;
        return $this;
    }

    /**
     * 数据自动验证
     * @access public
     * @param array     $data  数据
     * @param mixed     $childrules  验证规则
     * @param string    $scene 验证场景
     * @return bool
     */
    public function check($data, $childrules = [], $scene = '')
    {
        $this->error = [];

        if (empty($childrules)) {
            // 读取验证规则
            $childrules = $this->rule;
        }

        // 分析验证规则
        $scene = $this->getScene($scene);
        if (is_array($scene)) {
            // 处理场景验证字段
            $change = [];
            $array  = [];
            foreach ($scene as $k => $val) {
                if (is_numeric($k)) {
                    $array[] = $val;
                } else {
                    $array[]    = $k;
                    $change[$k] = $val;
                }
            }
        }

        foreach ($childrules as $key => $item) {
            // field => rule1|rule2... field=>['rule1','rule2',...]
            if (is_numeric($key)) {
                // [field,rule1|rule2,msg1|msg2]
                $key  = $item[0];
                $childrule = $item[1];
                if (isset($item[2])) {
                    $msg = is_string($item[2]) ? explode('|', $item[2]) : $item[2];
                } else {
                    $msg = [];
                }
            } else {
                $childrule = $item;
                $msg  = [];
            }
            if (strpos($key, '|')) {
                // 字段|描述 用于指定属性名称
                list($key, $title) = explode('|', $key);
            } else {
                $title = isset($this->field[$key]) ? $this->field[$key] : $key;
            }

            // 场景检测
            if (!empty($scene)) {
                if ($scene instanceof \Closure && !call_user_func_array($scene, [$key, $data])) {
                    continue;
                } elseif (is_array($scene)) {
                    if (!in_array($key, $array)) {
                        continue;
                    } elseif (isset($change[$key])) {
                        // 重载某个验证规则
                        $childrule = $change[$key];
                    }
                }
            }

            // 获取数据 支持二维数组
            $value = $this->getDataValue($data, $key);

            // 字段验证
            if ($childrule instanceof \Closure) {
                // 匿名函数验证 支持传入当前字段和所有字段两个数据
                $result = call_user_func_array($childrule, [$value, $data]);
            } else {
                $result = $this->checkItem($key, $value, $childrule, $data, $title, $msg);
            }

            if (true !== $result) {
                // 没有返回true 则表示验证失败
                if (!empty($this->batch)) {
                    // 批量验证
                    if (is_array($result)) {
                        $this->error = array_merge($this->error, $result);
                    } else {
                        $this->error[$key] = $result;
                    }
                } else {
                    $this->error = $result;
                    return false;
                }
            }
        }
        return !empty($this->error) ? false : true;
    }

    /**
     * 根据验证规则验证数据
     * @access protected
     * @param  mixed     $value 字段值
     * @param  mixed     $childrules 验证规则
     * @return bool
     */
    protected function checkRule($value, $childrules)
    {
        if ($childrules instanceof \Closure) {
            return call_user_func_array($childrules, [$value]);
        } elseif (is_string($childrules)) {
            $childrules = explode('|', $childrules);
        }

        foreach ($childrules as $key => $childrule) {
            if ($childrule instanceof \Closure) {
                $result = call_user_func_array($childrule, [$value]);
            } else {
                // 判断验证类型
                list($type, $childrule) = $this->getValidateType($key, $childrule);

                $callback = isset(self::$type[$type]) ? self::$type[$type] : [$this, $type];

                $result = call_user_func_array($callback, [$value, $childrule]);
            }

            if (true !== $result) {
                return $result;
            }
        }

        return true;
    }

    /**
     * 验证单个字段规则
     * @access protected
     * @param string    $field  字段名
     * @param mixed     $value  字段值
     * @param mixed     $childrules  验证规则
     * @param array     $data  数据
     * @param string    $title  字段描述
     * @param array     $msg  提示信息
     * @return mixed
     */
    protected function checkItem($field, $value, $childrules, $data, $title = '', $msg = [])
    {
        // 支持多规则验证 require|in:a,b,c|... 或者 ['require','in'=>'a,b,c',...]
        if (is_string($childrules)) {
            $childrules = explode('|', $childrules);
        }
        $i = 0;
        foreach ($childrules as $key => $childrule) {
            if ($childrule instanceof \Closure) {
                $result = call_user_func_array($childrule, [$value, $data]);
                $info   = is_numeric($key) ? '' : $key;
            } else {
                // 判断验证类型
                list($type, $childrule, $info) = $this->getValidateType($key, $childrule);

                // 如果不是require 有数据才会行验证
                if (0 === strpos($info, 'require') || (!is_null($value) && '' !== $value)) {
                    // 验证类型
                    $callback = isset(self::$type[$type]) ? self::$type[$type] : [$this, $type];
                    // 验证数据
                    $result = call_user_func_array($callback, [$value, $childrule, $data, $field, $title]);
                } else {
                    $result = true;
                }
            }

            if (false === $result) {
                // 验证失败 返回错误信息
                if (isset($msg[$i])) {
                    $message = $msg[$i];
                    if (is_string($message) && strpos($message, '{%') === 0) {
                        $message = Lang::get(substr($message, 2, -1));
                    }
                } else {
                    $message = $this->getRuleMsg($field, $title, $info, $childrule);
                }
                return $message;
            } elseif (true !== $result) {
                // 返回自定义错误信息
                if (is_string($result) && false !== strpos($result, ':')) {
                    $result = str_replace([':attribute', ':rule'], [$title, (string) $childrule], $result);
                }
                return $result;
            }
            $i++;
        }
        return $result;
    }

    /**
     * 获取当前验证类型及规则
     * @access public
     * @param  mixed     $key
     * @param  mixed     $childrule
     * @return array
     */
    protected function getValidateType($key, $childrule)
    {
        // 判断验证类型
        if (!is_numeric($key)) {
            return [$key, $childrule, $key];
        }

        if (strpos($childrule, ':')) {
            list($type, $childrule) = explode(':', $childrule, 2);
            if (isset($this->alias[$type])) {
                // 判断别名
                $type = $this->alias[$type];
            }
            $info = $type;
        } elseif (method_exists($this, $childrule)) {
            $type = $childrule;
            $info = $childrule;
            $childrule = '';
        } else {
            $type = 'is';
            $info = $childrule;
        }

        return [$type, $childrule, $info];
    }

    /**
     * 验证是否和某个字段的值一致
     * @access protected
     * @param mixed     $value 字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @param string    $field 字段名
     * @return bool
     */
    protected function confirm($value, $childrule, $data, $field = '')
    {
        if ('' == $childrule) {
            if (strpos($field, '_confirm')) {
                $childrule = strstr($field, '_confirm', true);
            } else {
                $childrule = $field . '_confirm';
            }
        }
        return $this->getDataValue($data, $childrule) === $value;
    }

    /**
     * 验证是否和某个字段的值是否不同
     * @access protected
     * @param mixed $value 字段值
     * @param mixed $childrule  验证规则
     * @param array $data  数据
     * @return bool
     */
    protected function different($value, $childrule, $data)
    {
        return $this->getDataValue($data, $childrule) != $value;
    }

    /**
     * 验证是否大于等于某个值
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function egt($value, $childrule, $data)
    {
        $val = $this->getDataValue($data, $childrule);
        return !is_null($val) && $value >= $val;
    }

    /**
     * 验证是否大于某个值
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function gt($value, $childrule, $data)
    {
        $val = $this->getDataValue($data, $childrule);
        return !is_null($val) && $value > $val;
    }

    /**
     * 验证是否小于等于某个值
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function elt($value, $childrule, $data)
    {
        $val = $this->getDataValue($data, $childrule);
        return !is_null($val) && $value <= $val;
    }

    /**
     * 验证是否小于某个值
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function lt($value, $childrule, $data)
    {
        $val = $this->getDataValue($data, $childrule);
        return !is_null($val) && $value < $val;
    }

    /**
     * 验证是否等于某个值
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function eq($value, $childrule)
    {
        return $value == $childrule;
    }

    /**
     * 验证字段值是否为有效格式
     * @access protected
     * @param mixed     $value  字段值
     * @param string    $childrule  验证规则
     * @param array     $data  验证数据
     * @return bool
     */
    protected function is($value, $childrule, $data = [])
    {
        switch ($childrule) {
            case 'require':
                // 必须
                $result = !empty($value) || '0' == $value;
                break;
            case 'accepted':
                // 接受
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'alpha':
                // 只允许字母
                $result = $this->regex($value, '/^[A-Za-z]+$/');
                break;
            case 'alphaNum':
                // 只允许字母和数字
                $result = $this->regex($value, '/^[A-Za-z0-9]+$/');
                break;
            case 'alphaDash':
                // 只允许字母、数字和下划线 破折号
                $result = $this->regex($value, '/^[A-Za-z0-9\-\_]+$/');
                break;
            case 'chs':
                // 只允许汉字
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}]+$/u');
                break;
            case 'chsAlpha':
                // 只允许汉字、字母
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u');
                break;
            case 'chsAlphaNum':
                // 只允许汉字、字母和数字
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u');
                break;
            case 'chsDash':
                // 只允许汉字、字母、数字和下划线_及破折号-
                $result = $this->regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u');
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'ip':
                // 是否为IP地址
                $result = $this->filter($value, [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6]);
                break;
            case 'url':
                // 是否为一个URL地址
                $result = $this->filter($value, FILTER_VALIDATE_URL);
                break;
            case 'float':
                // 是否为float
                $result = $this->filter($value, FILTER_VALIDATE_FLOAT);
                break;
            case 'number':
                $result = is_numeric($value);
                break;
            case 'integer':
                // 是否为整型
                $result = $this->filter($value, FILTER_VALIDATE_INT);
                break;
            case 'email':
                // 是否为邮箱地址
                $result = $this->filter($value, FILTER_VALIDATE_EMAIL);
                break;
            case 'boolean':
                // 是否为布尔值
                $result = in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
            case 'file':
                $result = $value instanceof File;
                break;
            case 'image':
                $result = $value instanceof File && in_array($this->getImageType($value->getRealPath()), [1, 2, 3, 6]);
                break;
            case 'token':
                $result = $this->token($value, '__token__', $data);
                break;
            default:
                if (isset(self::$type[$childrule])) {
                    // 注册的验证规则
                    $result = call_user_func_array(self::$type[$childrule], [$value]);
                } else {
                    // 正则验证
                    $result = $this->regex($value, $childrule);
                }
        }
        return $result;
    }

    // 判断图像类型
    protected function getImageType($image)
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($image);
        } else {
            try {
                $info = getimagesize($image);
                return $info ? $info[2] : false;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * 验证是否为合格的域名或者IP 支持A，MX，NS，SOA，PTR，CNAME，AAAA，A6， SRV，NAPTR，TXT 或者 ANY类型
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function activeUrl($value, $childrule)
    {
        if (!in_array($childrule, ['A', 'MX', 'NS', 'SOA', 'PTR', 'CNAME', 'AAAA', 'A6', 'SRV', 'NAPTR', 'TXT', 'ANY'])) {
            $childrule = 'MX';
        }
        return checkdnsrr($value, $childrule);
    }

    /**
     * 验证是否有效IP
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则 ipv4 ipv6
     * @return bool
     */
    protected function ip($value, $childrule)
    {
        if (!in_array($childrule, ['ipv4', 'ipv6'])) {
            $childrule = 'ipv4';
        }
        return $this->filter($value, [FILTER_VALIDATE_IP, 'ipv6' == $childrule ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4]);
    }

    /**
     * 验证上传文件后缀
     * @access protected
     * @param mixed     $file  上传文件
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function fileExt($file, $childrule)
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof File) || !$item->checkExt($childrule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof File) {
            return $file->checkExt($childrule);
        } else {
            return false;
        }
    }

    /**
     * 验证上传文件类型
     * @access protected
     * @param mixed     $file  上传文件
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function fileMime($file, $childrule)
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof File) || !$item->checkMime($childrule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof File) {
            return $file->checkMime($childrule);
        } else {
            return false;
        }
    }

    /**
     * 验证上传文件大小
     * @access protected
     * @param mixed     $file  上传文件
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function fileSize($file, $childrule)
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof File) || !$item->checkSize($childrule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof File) {
            return $file->checkSize($childrule);
        } else {
            return false;
        }
    }

    /**
     * 验证图片的宽高及类型
     * @access protected
     * @param mixed     $file  上传文件
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function image($file, $childrule)
    {
        if (!($file instanceof File)) {
            return false;
        }
        if ($childrule) {
            $childrule                        = explode(',', $childrule);
            list($width, $height, $type) = getimagesize($file->getRealPath());
            if (isset($childrule[2])) {
                $imageType = strtolower($childrule[2]);
                if ('jpeg' == $imageType) {
                    $imageType = 'jpg';
                }
                if (image_type_to_extension($type, false) != $imageType) {
                    return false;
                }
            }

            list($w, $h) = $childrule;
            return $w == $width && $h == $height;
        } else {
            return in_array($this->getImageType($file->getRealPath()), [1, 2, 3, 6]);
        }
    }

    /**
     * 验证请求类型
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function method($value, $childrule)
    {
        $method = Request::instance()->method();
        return strtoupper($childrule) == $method;
    }

    /**
     * 验证时间和日期是否符合指定格式
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function dateFormat($value, $childrule)
    {
        $info = date_parse_from_format($childrule, $value);
        return 0 == $info['warning_count'] && 0 == $info['error_count'];
    }

    /**
     * 验证是否唯一
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则 格式：数据表,字段名,排除ID,主键名
     * @param array     $data  数据
     * @param string    $field  验证字段名
     * @return bool
     */
    protected function unique($value, $childrule, $data, $field)
    {
        if (is_string($childrule)) {
            $childrule = explode(',', $childrule);
        }
        if (false !== strpos($childrule[0], '\\')) {
            // 指定模型类
            $db = new $childrule[0];
        } else {
            try {
                $db = Loader::model($childrule[0]);
            } catch (ClassNotFoundException $e) {
                $db = Db::name($childrule[0]);
            }
        }
        $key = isset($childrule[1]) ? $childrule[1] : $field;

        if (strpos($key, '^')) {
            // 支持多个字段验证
            $fields = explode('^', $key);
            foreach ($fields as $key) {
                $map[$key] = $data[$key];
            }
        } elseif (strpos($key, '=')) {
            parse_str($key, $map);
        } else {
            $map[$key] = $data[$field];
        }

        $pk = isset($childrule[3]) ? $childrule[3] : $db->getPk();
        if (is_string($pk)) {
            if (isset($childrule[2])) {
                $map[$pk] = ['neq', $childrule[2]];
            } elseif (isset($data[$pk])) {
                $map[$pk] = ['neq', $data[$pk]];
            }
        }
        if ($db->where($map)->field($pk)->find()) {
            return false;
        }
        return true;
    }

    /**
     * 使用行为类验证
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return mixed
     */
    protected function behavior($value, $childrule, $data)
    {
        return Hook::exec($childrule, '', $data);
    }

    /**
     * 使用filter_var方式验证
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function filter($value, $childrule)
    {
        if (is_string($childrule) && strpos($childrule, ',')) {
            list($childrule, $param) = explode(',', $childrule);
        } elseif (is_array($childrule)) {
            $param = isset($childrule[1]) ? $childrule[1] : null;
            $childrule  = $childrule[0];
        } else {
            $param = null;
        }
        return false !== filter_var($value, is_int($childrule) ? $childrule : filter_id($childrule), $param);
    }

    /**
     * 验证某个字段等于某个值的时候必须
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function requireIf($value, $childrule, $data)
    {
        list($field, $val) = explode(',', $childrule);
        if ($this->getDataValue($data, $field) == $val) {
            return !empty($value) || '0' == $value;
        } else {
            return true;
        }
    }

    /**
     * 通过回调方法验证某个字段是否必须
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function requireCallback($value, $childrule, $data)
    {
        $result = call_user_func_array($childrule, [$value, $data]);
        if ($result) {
            return !empty($value) || '0' == $value;
        } else {
            return true;
        }
    }

    /**
     * 验证某个字段有值的情况下必须
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function requireWith($value, $childrule, $data)
    {
        $val = $this->getDataValue($data, $childrule);
        if (!empty($val)) {
            return !empty($value) || '0' == $value;
        } else {
            return true;
        }
    }

    /**
     * 验证是否在范围内
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function in($value, $childrule)
    {
        return in_array($value, is_array($childrule) ? $childrule : explode(',', $childrule));
    }

    /**
     * 验证是否不在某个范围
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function notIn($value, $childrule)
    {
        return !in_array($value, is_array($childrule) ? $childrule : explode(',', $childrule));
    }

    /**
     * between验证数据
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function between($value, $childrule)
    {
        if (is_string($childrule)) {
            $childrule = explode(',', $childrule);
        }
        list($min, $max) = $childrule;
        return $value >= $min && $value <= $max;
    }

    /**
     * 使用notbetween验证数据
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function notBetween($value, $childrule)
    {
        if (is_string($childrule)) {
            $childrule = explode(',', $childrule);
        }
        list($min, $max) = $childrule;
        return $value < $min || $value > $max;
    }

    /**
     * 验证数据长度
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function length($value, $childrule)
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string) $value);
        }

        if (strpos($childrule, ',')) {
            // 长度区间
            list($min, $max) = explode(',', $childrule);
            return $length >= $min && $length <= $max;
        } else {
            // 指定长度
            return $length == $childrule;
        }
    }

    /**
     * 验证数据最大长度
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function max($value, $childrule)
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string) $value);
        }
        return $length <= $childrule;
    }

    /**
     * 验证数据最小长度
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function min($value, $childrule)
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string) $value);
        }
        return $length >= $childrule;
    }

    /**
     * 验证日期
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function after($value, $childrule)
    {
        return strtotime($value) >= strtotime($childrule);
    }

    /**
     * 验证日期
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function before($value, $childrule)
    {
        return strtotime($value) <= strtotime($childrule);
    }

    /**
     * 验证有效期
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @return bool
     */
    protected function expire($value, $childrule)
    {
        if (is_string($childrule)) {
            $childrule = explode(',', $childrule);
        }
        list($start, $end) = $childrule;
        if (!is_numeric($start)) {
            $start = strtotime($start);
        }

        if (!is_numeric($end)) {
            $end = strtotime($end);
        }
        return $_SERVER['REQUEST_TIME'] >= $start && $_SERVER['REQUEST_TIME'] <= $end;
    }

    /**
     * 验证IP许可
     * @access protected
     * @param string    $value  字段值
     * @param mixed     $childrule  验证规则
     * @return mixed
     */
    protected function allowIp($value, $childrule)
    {
        return in_array($_SERVER['REMOTE_ADDR'], is_array($childrule) ? $childrule : explode(',', $childrule));
    }

    /**
     * 验证IP禁用
     * @access protected
     * @param string    $value  字段值
     * @param mixed     $childrule  验证规则
     * @return mixed
     */
    protected function denyIp($value, $childrule)
    {
        return !in_array($_SERVER['REMOTE_ADDR'], is_array($childrule) ? $childrule : explode(',', $childrule));
    }

    /**
     * 使用正则验证数据
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则 正则规则或者预定义正则名
     * @return mixed
     */
    protected function regex($value, $childrule)
    {
        if (isset($this->regex[$childrule])) {
            $childrule = $this->regex[$childrule];
        }
        if (0 !== strpos($childrule, '/') && !preg_match('/\/[imsU]{0,4}$/', $childrule)) {
            // 不是正则表达式则两端补上/
            $childrule = '/^' . $childrule . '$/';
        }
        return is_scalar($value) && 1 === preg_match($childrule, (string) $value);
    }

    /**
     * 验证表单令牌
     * @access protected
     * @param mixed     $value  字段值
     * @param mixed     $childrule  验证规则
     * @param array     $data  数据
     * @return bool
     */
    protected function token($value, $childrule, $data)
    {
        $childrule = !empty($childrule) ? $childrule : '__token__';
        if (!isset($data[$childrule]) || !Session::has($childrule)) {
            // 令牌数据无效
            return false;
        }

        // 令牌验证
        if (isset($data[$childrule]) && Session::get($childrule) === $data[$childrule]) {
            // 防止重复提交
            Session::delete($childrule); // 验证完成销毁session
            return true;
        }
        // 开启TOKEN重置
        Session::delete($childrule);
        return false;
    }

    // 获取错误信息
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取数据值
     * @access protected
     * @param array $data 数据
     * @param string $key 数据标识 支持二维
     * @return mixed
     */
    protected function getDataValue($data, $key)
    {
        if (is_numeric($key)) {
            $value = $key;
        } elseif (strpos($key, '.')) {
            // 支持二维数组验证
            list($name1, $name2) = explode('.', $key);
            $value               = isset($data[$name1][$name2]) ? $data[$name1][$name2] : null;
        } else {
            $value = isset($data[$key]) ? $data[$key] : null;
        }
        return $value;
    }

    /**
     * 获取验证规则的错误提示信息
     * @access protected
     * @param string    $attribute  字段英文名
     * @param string    $title  字段描述名
     * @param string    $type  验证规则名称
     * @param mixed     $childrule  验证规则数据
     * @return string
     */
    protected function getRuleMsg($attribute, $title, $type, $childrule)
    {
        if (isset($this->message[$attribute . '.' . $type])) {
            $msg = $this->message[$attribute . '.' . $type];
        } elseif (isset($this->message[$attribute][$type])) {
            $msg = $this->message[$attribute][$type];
        } elseif (isset($this->message[$attribute])) {
            $msg = $this->message[$attribute];
        } elseif (isset(self::$typeMsg[$type])) {
            $msg = self::$typeMsg[$type];
        } elseif (0 === strpos($type, 'require')) {
            $msg = self::$typeMsg['require'];
        } else {
            $msg = $title . Lang::get('not conform to the rules');
        }

        if (is_string($msg) && 0 === strpos($msg, '{%')) {
            $msg = Lang::get(substr($msg, 2, -1));
        } elseif (Lang::has($msg)) {
            $msg = Lang::get($msg);
        }

        if (is_string($msg) && is_scalar($childrule) && false !== strpos($msg, ':')) {
            // 变量替换
            if (is_string($childrule) && strpos($childrule, ',')) {
                $array = array_pad(explode(',', $childrule), 3, '');
            } else {
                $array = array_pad([], 3, '');
            }
            $msg = str_replace(
                [':attribute', ':rule', ':1', ':2', ':3'],
                [$title, (string) $childrule, $array[0], $array[1], $array[2]],
                $msg);
        }
        return $msg;
    }

    /**
     * 获取数据验证的场景
     * @access protected
     * @param string $scene  验证场景
     * @return array
     */
    protected function getScene($scene = '')
    {
        if (empty($scene)) {
            // 读取指定场景
            $scene = $this->currentScene;
        }

        if (!empty($scene) && isset($this->scene[$scene])) {
            // 如果设置了验证适用场景
            $scene = $this->scene[$scene];
            if (is_string($scene)) {
                $scene = explode(',', $scene);
            }
        } else {
            $scene = [];
        }
        return $scene;
    }

    public static function __callStatic($method, $params)
    {
        $class = self::make();
        if (method_exists($class, $method)) {
            return call_user_func_array([$class, $method], $params);
        } else {
            throw new \BadMethodCallException('method not exists:' . __CLASS__ . '->' . $method);
        }
    }
}
