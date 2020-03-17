<?php

namespace Cblink\PuyingyunSdk\Printer;

use Cblink\PuyingyunSdk\Kernel\BaseClient;
use Cblink\PuyingyunSdk\Kernel\Exceptions\InvalidArgumentException;

class Client extends BaseClient
{
    public function getPrinterList($offset = 0, $limit = 10, $query = null, $filter = [])
    {
        return $this->doAction('printer_list', compact('offset', 'limit', 'query', 'filter'));
    }

    /**
     * 添加单台打印机.
     *
     * @param array $printer = ['sn' => $sn, 'key' => $key, 'alias' => $alias]
     *
     * @return array|mixed|null
     * @throws InvalidArgumentException
     * @throws \Cblink\PuyingyunSdk\Kernel\Exceptions\MethodRetryTooManyException
     */
    public function addPrinter(array $printer)
    {
        $printers = [
            $printer,
        ];

        return $this->addPrinters($printers);
    }

    /**
     * 批量添加打印机.
     *
     * @param array $data = [
     *                        ['sn' => $sn, 'key' => $key, 'alias' => $alias],
     *                        ['sn' => $sn, 'key' => $key, 'alias' => $alias],
     *                        ...
     *                        ]
     *
     * @return array|mixed|null
     * @throws InvalidArgumentException
     * @throws \Cblink\PuyingyunSdk\Kernel\Exceptions\MethodRetryTooManyException
     */
    public function addPrinters($data = [])
    {
        $printers = [];

        foreach ($data as $index => &$printer) {
            if (empty($printer['sn']) || empty($printer['key'])) {
                throw new InvalidArgumentException("待添加打印机索引 {$index} 缺少必要参数 sn 或 key，请核实");
            }

            if (count($printer) < 3 || empty($printer['alias'])) {
                $printer['alias'] = $printer['sn'];
            }

            $printers[] = sprintf('%s#%s#%s', $printer['sn'], $printer['key'], $printer['alias']);
        }

        return $this->doAction('add_printer', compact('printers'));
    }

    public function removePrinter($printerSn)
    {
        return $this->removePrinters([
            $printerSn,
        ]);
    }

    public function removePrinters($printerSns = [])
    {
        return $this->doAction('remove_printer', $printerSns);
    }

    public function createPrinterTask($sn, $content, $count = 1, $interval = 0, $title = '')
    {
        return $this->doAction('add_task', compact('sn', 'content', 'title', 'content', 'interval'));
    }

    public function getTaskList($offset = 0, $limit = 10, $query = null, $filter = [])
    {
        return $this->doAction('task_list', compact('offset', 'limit', 'query', 'filter'));
    }

    public function getPrinterTaskBySn($sn)
    {
        return $this->doAction('get_task', compact('sn'));
    }

    public function cancelUnprintTaskBySn(string $sn)
    {
        return $this->doAction('remove_task', compact('sn'));
    }

    public function getDeviceStateStatistics()
    {
        return $this->doAction('device_state_statistics');
    }

    public function setPrinterNameBySn($name, $sn)
    {
        return $this->doAction('update_printer', compact('sn', 'name'));
    }

    public function getPrintAmountStatistics($type = 'today')
    {
        return $this->doAction('print_amount_statistics', compact('type'));
    }
}