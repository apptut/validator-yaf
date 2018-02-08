<?php
/**
 * Desc: 表单验证封装
 * User: LiangQi
 * Date: 2017/12/13
 */

namespace Validator;


use Validator\Rules\ValidatorRules;

class Validator
{
    use ValidatorRules;

    private $data;
    protected $message = [];
    protected $errors = [];
    protected $rules = [];

    /**
     * 内置验证选项
     *
     * @var array
     */
    protected $innerRules = [
        'Required', // 是否必填项
        'Nullable', // 可选参数

        'In',

        'Numeric', // 数字验证
        'Int',

        'Size',
        'Min',
        'Max',   // 长度验证

        'Email', // 验证是否是合法有效
        'Mobile', // 验证是否是合法的手机号

        'Regex',  // 正则表达式
        'Url',    // 合法url验证
        'String', // 字符串
        'Date'   // 日期格式验证
    ];

    /**
     * Validator constructor.
     *
     * @param array $data 需要验证的数据
     * @param array $rules 使用的验证规则
     * @param array $message 字段提示消息
     */
    public function __construct($data, $rules, $message = [])
    {
        $this->missingCheck($data, $rules);
        $this->data = $data;
        $this->rules = $rules;
        $this->parseMessage($message);

    }

    /**
     * 解析自定义提示数据
     *
     * @param $message
     */
    private function parseMessage($message)
    {
        if ($message) {
            foreach ($message as $key => $value) {
                if (strpos($key, '.') !== false) { // 同一字段是否是具体规则提示
                    $tmpArr = explode('.', $key);
                    $field = $tmpArr[0];
                    $rule = $tmpArr[1];
                    if (in_array(ucfirst($rule), $this->innerRules) && array_key_exists($field, $this->data)) {
                        $this->addMessage($field, $rule, $value);
                    }
                } else {
                    if (array_key_exists($key, $this->data)) {
                        $this->addMessage($key, null, $value);
                    }
                }
            }
        }
    }

    /**
     * 添加字段提示信息
     *
     * @param $field
     * @param $rule
     * @param $message
     */
    private function addMessage($field, $rule, $message)
    {
        if (array_key_exists($field, $this->message)) {
            $customMessage = $this->message[$field];
            if (is_string($customMessage)) {
                $this->message[$field] = $customMessage;
            } else if (is_array($customMessage)) {
                $this->message[$field][$rule] = $message;
            }
        } else {
            if ($rule) {
                $this->message[$field][$rule] = $message;
            } else {
                $this->message[$field] = $message;
            }
        }
    }

    /**
     * 检测验证字段项和验证规则是否匹配
     *
     * @param $data
     * @param $rules
     * @throws ValidatorException
     */
    private function missingCheck($data, $rules)
    {
        foreach ($rules as $key => $item) {
            if (strpos($item, 'nullable') === false && !array_key_exists($key, $data)) {
                throw  new ValidatorException('字段：' . $key . '验证字段不存在！');
            }
        }
    }

    /**
     *
     * @param $data
     * @param $rules
     * @param array $message
     * @return Validator
     */
    public static function make($data, $rules, $message = [])
    {
        $validator = new self($data, $rules, $message);
        $validator->run();
        return $validator;
    }

    public function  run()
    {
        foreach ($this->rules as $key => $item) {
            if (is_string($item)) {
                $this->parse($key, $item);
            } else {
                throw new ValidatorException($item . '：验证规则不合法！');
            }
        }
    }

    private function parse($key, $rules)
    {
        $rulesStr = $rules;
        $rules = explode('|', $rules);
        foreach ($rules as $rule) {
            if (!is_string($rule) || !$rule) {
                continue;
            }

            $flagIndex = strpos($rule, ':');
            $param = '';
            $ruleName = $rule;
            if ($flagIndex) {
                $param = substr($ruleName, $flagIndex + 1);
                $ruleName = substr($ruleName, 0, $flagIndex);
            }

            // 检测验证规则是否存在
            if (!in_array(ucfirst($ruleName), $this->innerRules)) {
                throw new ValidatorException($ruleName . ': 验证规则不存在！');
            }

            if ($this->isVerifiable($key, $rulesStr)){
                $method = "validate{$ruleName}";
                $value = $this->data[$key];
                if (!$this->$method($value, $param)) {
                    $this->addErrors($key, $ruleName, $value);
                }
            }
        }
    }

    private function isVerifiable($key, $rules){
        // 判断当前参数是否是可选参数
        if (strpos($rules, 'nullable') !== false && (!array_key_exists($key, $this->data) || strlen($this->data[$key]) <= 0)) {
            return false;
        }
        return true;
    }

    /**
     * 添加错误信心
     *
     * @param $field
     * @param $rule
     * @param $value
     */
    private function addErrors($field, $rule, $value)
    {
        $customerMessage = $this->getCustomMessage($field, $rule);
        if ($customerMessage) {
            if (array_key_exists($field, $this->errors)) {
                if (empty($this->errors[$field])) {
                    if ($rule) {
                        $this->errors[$field][$rule] = $customerMessage;
                    } else {
                        $this->errors[$field] = [$customerMessage];
                    }
                } else {
                    if ($rule) {
                        $this->errors[$field][$rule] = $customerMessage;
                    } else {
                        $this->errors[$field] = [$customerMessage];
                    }
                }
            } else {
                if ($rule) {
                    $this->errors[$field][$rule] = $customerMessage;
                } else {
                    $this->errors[$field] = [$customerMessage];
                }
            }
        } else {
            $defaultMsg = "字段：" . $field . '; 验证不通过， 对应验证规则：' . $rule . '; 验证值为：' . $value;
            $this->errors[$field] = [$defaultMsg];
            if ($rule) {
                $this->errors[$field][$rule] = $defaultMsg;
            }
        }
    }

    /**
     * 获取用户自定义错误信息
     *
     * @param $field
     * @param $rule
     * @return mixed
     */
    protected function getCustomMessage($field, $rule)
    {
        if (!$this->message) {
            return false;
        }

        if (!array_key_exists($field, $this->message)) {
            return false;
        }

        $message = $this->message[$field];
        if (is_string($message)) {
            return $message;
        } else if (is_array($message) && array_key_exists($rule, $message)) {
            return $message[$rule];
        } else {
            return false;
        }
    }

    /**
     * 检测是否验证失败
     *
     * @return bool
     */
    public function failed()
    {
        return !!$this->errors;
    }

    /**
     * 获取所有错误验证数据
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}