<?php

namespace EasyDb\Exception;
use Exception;
use Throwable;

/**
 * Class DbException
 * @package EasyDb\Exception
 * 异常反馈规范-[草案]
 * 1.编号形式
 * 2.错误消息形式
 */

/**
 * 错误规则：
 * 100 系统层面  如:101 参数错误
 * 200 请求层面  如:201 请求参数错误，但不影响继续执行
 * 300 逻辑层面  如:301 上文参数错误引起的逻辑异常
 */
class DbException extends Exception
{
    static array $CODE = [
        102=>'数据库未初始化',
        103=>'未设置配置数据'
    ];


    public function __construct($message = "", $code = 2, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function point(int $code): DbException
    {
        $message = '未知错误';
        $message = self::$CODE[$code] ?? $message;
        return new self($message,$code);
    }

    public function set(int $code ,string $message){
        self::$CODE[$code] = $message;
    }


}