<?php

namespace Cblink\PuyingyunSdk\Kernel\Exceptions;

class Error
{
    const TOKEN_EXPIRE_STATUS = 40312001;

    const ERROR_CODE_INFORMATION = 40013003;

    const ERROR_CODE_ALREADY_BIND_BY_YOU = 40013007;

    const ERROR_CODE_ALREADY_BIND_BY_OTHER = 40013008;

    const ERROR_CODE_NO_BIND_BY_YOU = 403133001;


    const ERROR_MSG_MAP = [
        self::ERROR_CODE_INFORMATION => '信息不正确',
        self::TOKEN_EXPIRE_STATUS => '系统账号认证信息过期',
        self::ERROR_CODE_ALREADY_BIND_BY_YOU => '您已绑定打印机',
        self::ERROR_CODE_ALREADY_BIND_BY_OTHER => '打印机被其他人已绑定',
        self::ERROR_CODE_NO_BIND_BY_YOU => '您未绑定打印机',
    ];

    public static function getMessage($code, $default = '未知错误信息')
    {
        return [self::ERROR_MSG_MAP[$code] ?? $default, $code];
    }
}