<?php

/**
 * author: NickBai
 * createTime: 2016/12/26 0026 上午 9:11
 *
 */
class Tickets
{
    public $fromStation = null;
    public $toStation = null;
    public $date = null;

    public function __construct($fromStation = null, $toStation = null, $date = null)
    {
        if (!file_exists(ROOT_PATH . '/data/station.json')) {
            $this->parseStation();
        }

        $this->fromStation = $fromStation;
        $this->toStation = $toStation;
        $this->date = $date;
    }

    /**
     * 入口函数
     */
    public function run()
    {
        if (is_null($this->fromStation) || is_null($this->toStation))
            throw new Exception('起始站不能为空!');
        is_null($this->date) && $date = date('Y-m-d');

        $url = 'https://kyfw.12306.cn/otn/leftTicket/queryZ?leftTicketDTO.train_date=' . $this->date . '&leftTicketDTO.from_station=';
        $url .= $this->fromStation . '&leftTicketDTO.to_station=' . $this->toStation . '&purpose_codes=ADULT';
		
        $ticketInfo = $this->curlGet($url, ['date' => $this->date, 'from' => $this->fromStation, 'to' => $this->toStation]);
        return $ticketInfo;
    }

    /**
     * 解析火车站信息
     */
    private function parseStation()
    {
        $url = 'https://kyfw.12306.cn/otn/resources/js/framework/station_name.js?station_version=1.8992';
        $station = $this->curlGet($url, false);

        if (empty($station)) {
            throw new Exception('获取站点信息失败！');
        }

        $delStr = "var station_names ='"; //需要截断的字符
        $station = substr($station, strlen($delStr), strlen($station));

        $station = explode('@', $station);
        $json = [
            'message' => ''
        ];

        foreach ($station as $key => $vo) {
            if (empty($vo)) continue;

            $st = explode('|', $vo);
            $json['value'][] = [
                'stationName' => $st['1'],
                'shortName' => $st['3'],
                'stationFlag' => $st['2']
            ];
        }
        unset($station);

        file_put_contents(ROOT_PATH . '/data/station.json', json_encode($json));
    }

    /**
     * 采集数据
     * @param $url
	 * @param $cookie
     * @param $decode
     */
    private function curlGet($url, $cookie, $decode = true)
    {
        $ch = curl_init();
        $timeout = 5;
        $header = [
            'Accept:*/*',
            'Accept-Charset:GBK,utf-8;q=0.7,*;q=0.3',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:zh-CN,zh;q=0.8,ja;q=0.6,en;q=0.4',
            'Connection:keep-alive',
            'Host:kyfw.12306.cn',
            'Referer:https://kyfw.12306.cn/otn/lcxxcx/init',
			'cookie:JSESSIONID=B9A56BF999E13311F9FFDF2C5DBBABA2; _jc_save_wfdc_flag=dc; route=6f50b51faa11b987e576cdb301e545c4; BIGipServerotn=938476042.24610.0000; BIGipServerpool_passport=384631306.50215.0000; RAIL_EXPIRATION=1579516519048; RAIL_DEVICEID=jVd9iIOY5caqeG4RTQf9lbxg1oI2b_ZnIpWyJlQb-OY369N7NN6ZmZAu-Z5N7MRcxEz60u3AYVSVzcgSxo5zPU1-treFtuNJletOLQ_7rW6p5Y9nSZmRnr2NBfeGUNYuB_cPpmk-hidSKbjHion5Ewi05yFCs3jA; _jc_save_toDate=' 
			. $cookie['date'] . '; _jc_save_fromDate=' . date('Y-m-d') . '; _jc_save_fromStation=' . $cookie['from'] . '; _jc_save_toStation=' . $cookie['to']
        ];
	
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch);

        $decode && $result = json_decode($result, true);

        return $result;
    }

}