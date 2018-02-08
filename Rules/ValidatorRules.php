<?php
/**
 * Desc:
 * User: LiangQi
 * Date: 2018/1/3
 */

namespace Validator\Rules;


/**
 * 各种验证规则
 *
 * Trait ValidatorRules
 * @package Validator\Rules
 */
trait ValidatorRules
{
    /**
     * 验证是否是否必填项
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateRequired($value, $param){
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否是数字
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateNumeric($value, $param){
        return is_numeric($value);
    }

    /**
     * 验证是否是整数值类型
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateInt($value, $param){
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * 正则表达式验证
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateRegex($value, $param)
    {
        if (!is_string($value) && ! is_numeric($value)) {
            return false;
        }
        return preg_match($param, $value) > 0;
    }

    /**
     * 获取字符串长度
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateSize($value, $param){
        return mb_strlen($value) != $param;
    }

    /**
     * 字符串最小长度
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateMin($value, $param){
        if (is_string($value) || is_numeric($value)){
            return mb_strlen($value) >= $param;
        }
        return false;
    }

    /**
     * 字符串最大长度
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateMax($value, $param){
        if (is_string($value) || is_numeric($value)){
            return mb_strlen($value) <= $param;
        }
        return false;
    }

    /**
     * 验证是否是邮箱
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateEmail($value, $param){
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 检测值是否存在
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateIn($value, $param){
        if (!is_string($param)){
            return false;
        }
        $inArr = explode(',', $param);
        return in_array($value, $inArr);
    }

    /**
     * 验证是否为字符串
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateString($value, $param)
    {
        return is_string($value);
    }

    /**
     * 可选参数
     * @param $value
     * @param $param
     * @return bool
     *
     */
    public function validateNullable($value, $param){
       return true;
    }


    /**
     * 验证是否是合法的手机号
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateMobile($value, $param){
        $pattern = '/^1[3|4|5|7|8|9][0-9]{9}$/';
        return preg_match($pattern, $value) > 0;
    }

    /**
     * 验证是否是合法的日期字符串
     *
     * @param $value
     * @param $param
     * @return bool
     */
    public function validateDate($value, $param)
    {

        if ((!is_string($value) && ! is_numeric($value)) || strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

}