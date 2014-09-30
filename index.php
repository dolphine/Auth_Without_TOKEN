<?php

$postStr = file_get_contents("php://input");

$signs = array('signature' => $_REQUEST['signature'],
               'timestamp' => $_REQUEST['timestamp'],
               'nonce'     => $_REQUEST['nonce'],
               );
$signs_string = array();
if (is_array($signs) && count($signs)) {
    foreach ($signs as $key=>$value) {
        $signs_string[] = $key . '=' . $value;
    }
}
$signs_string = implode('&', $signs_string);

$url = 'http://' . $_GET['domain'] . '/' . $_GET['page'] . '?' . $signs_string;
if (isset($_REQUEST['echostr']) && $_REQUEST['echostr']) {
    $fields = array(
                    'echostr'   => $_REQUEST['echostr'],
                    );
    $query_string = array();
    if (is_array($fields) && count($fields)) {
        foreach ($fields as $key=>$value) {
            $query_string[] = $key . '=' . $value;
        }
    }
    $query_string = implode('&', $query_string);

    $url .= '&' . $query_string;
    $result = file_get_contents($url);
    echo $result;
} else {
    $post = http_post($url, $postStr);

    include_once ('./guest/config.php');
    include_once (WEIXIN_PATH . '/class/wechat.class.php');    

    $weObj = new Wechat();
    $rev = $weObj->getRev();
    $type = $rev->getRevType();
    $revEvent = $rev->getRevEvent();
    $event = $revEvent['event'];
    $fromUserName = $rev->getRevFrom();
    switch($type) {
    case Wechat::MSGTYPE_TEXT:
        $content = $rev->getRevContent();
        if ($content == '我要上网') {
            $text = "<a href='" . SERVER_HOST . "/guest/sdk/weixin/redirct.php?fromUserName=" . $fromUserName . "'>点击上网</a>";
            $weObj->text($text)->reply();
            exit;
        } else {
            echo $post;
            exit;
       }
        break;
    case Wechat::MSGTYPE_EVENT:
        if ($event == 'subscribe') {  //关注微信操作
            $weObj->text('Thanks for subscribing!')->reply();
        } else if ($event == 'unsubscribe') {  //取消关注微信操作
            //取消上网权限
            $sql = "select * from " . WEIXIN_TABLE . " WHERE `fromUserName` = '{$fromUserName}'";
            $res = $mysql->query($sql, 'all');
            if (is_array($res) && count($res) > 0) {
                //删除数据
                $sql = "DELETE FROM " . WEIXIN_TABLE . " WHERE `fromUserName` = '{$fromUserName}'";
                $mysql->query($sql);
                foreach ($res as $key => $value) {
                    UniFi::sendUnauthorization($value['Mac_ID']);
                    sleep(5);
                }
            }

        }
        break;
    case Wechat::MSGTYPE_IMAGE:

        break;
    default:
        $weObj->text("help info")->reply();
        break;
    }
    echo $post;
}


function http_post($url,$param){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
    }
    if (is_string($param)) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach($param as $key=>$val){
            $aPOST[] = $key."=".urlencode($val);
        }
        $strPOST =  join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    curl_setopt($oCurl, CURLOPT_HEADER, true);
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}


function log_data($data) {
    $fp = fopen('data.txt', 'a+');
    fwrite($fp, date('H:i:s') . '-- ' . $data);
    fwrite($fp, "\n\n");
    fclose($fp);
}