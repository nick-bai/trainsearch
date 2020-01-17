<?php
/**
 * author: NickBai
 * createTime: 2016/12/9 0009 下午 4:19
 */
define( 'ROOT_PATH', __DIR__ );

require_once( ROOT_PATH . '/core/Tickets.php');

$fromStation = trim($_GET['f_s']);
$toStation = trim($_GET['t_s']);
$date = trim($_GET['date']);

$tickets = new Tickets($fromStation, $toStation, $date);
$json = $tickets->run();

/*
*   ["gr_num"]=>高级软卧
*   ["qt_num"]=>其他
*   ["rw_num"]=> 软卧
*   ["rz_num"]=>软座
*   ["tz_num"]=>特等座
*   ["wz_num"]=>无座
*   ["yw_num"]=>硬卧
*   ["yz_num"]=>硬座
*   ["ze_num"]=>二等座
*   ["zy_num"]=> 一等座
*   ["swz_num"]=> 商务座
*/

echo json_encode($json);