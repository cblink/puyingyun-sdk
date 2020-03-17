<?php

namespace Cblink\PuyingyunSdk\Kernel;

trait ApiTransformResponseTrait
{
    /**
     * @param $response
     * @return mixed
     *
     * @throws Exceptions\ClientError
     * @throws Exceptions\Exception
     *
     * @author 牟勇 <my24251325@gmail.com>
     */
    protected function transformResponse($response)
    {
        if (is_string($response)) {
            $result = json_decode($response, true);
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
        }

        // TODO: 记录响应信息

        if (isset($result['status']) && $result['status'] !== 200) {
            $this->tokenExpireCheck($result);

            throw new Exceptions\ClientError(...Exceptions\Error::getMessage($result['status'], $result['msg']));
        }

        return isset($result['data'])
            ? $result['data']
            : $result;
    }

    protected function tokenExpireCheck(array $result)
    {
        if (Exceptions\Error::TOKEN_EXPIRE_STATUS === $result['status']) {
            throw new Exceptions\ClientTokenExpireException(...Exceptions\Error::getMessage(Exceptions\Error::TOKEN_EXPIRE_STATUS, $result['msg']));
        }
    }
}