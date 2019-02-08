<?php
/**
 * 发送电子邮件
 *
 * @param name    回复者名字
 * @param message 回复内容
 * @param title   页面标题
 * @param link    页面链接
 * @param parent  被回复评论 ID
 * @param id      该评论 ID
 *
 * @author   fooleap <fooleap@gmail.com>
 * @version  2018-05-10 23:41:52
 * @link     https://github.com/fooleap/disqus-php-api
 *
 */
namespace Emojione;
date_default_timezone_set("Asia/Shanghai");
require_once('init.php');

// 获取被回复人信息
$curl_url = '/api/3.0/posts/details.json?';
$fields = (object) array(
    'post' => $_POST['parent'],
    'related' => 'thread'
);
$data = curl_get($curl_url, $fields);
$post = post_format($data->response);
$parent_isanon  = $data->response->author->isAnonymous; //是否为访客
$parent_email   = $data->response->author->email; //被回复邮箱
$thread = $data->response->thread;
$parent_link = $data->response->url;
$parent_name    = $post['name']; //被回复人名
$parent_message = $post['message']; //被回复留言

// 获取回复信息
$fields = (object) array(
    'post' => $_POST['id']
);
$data = curl_get($curl_url, $fields);
$post = post_format($data->response);
$reply_name    = $post['name']; //回复者人名
$reply_message = $post['message']; //回复者留言

$content = '<p>' . $parent_name . '，您在<a target="_blank" href="'.$website.'">「'. $forum_data -> forum -> name.'」</a>的评论：</p>';
$content .= $parent_message;
$content .= '<p>' . $reply_name . ' 的回复如下：</p>';
$content .= $reply_message;
$content .= '<p>查看详情及回复请点击：<a target="_blank" href="'.$parent_link. '">'. $thread -> clean_title . '</a></p>';

use PHPMailer;

if( $parent_isanon ){
    // 发送邮件
    require_once('PHPMailer/class.phpmailer.php');
    require_once('PHPMailer/class.smtp.php');
    $mail          = new PHPMailer();
    $mail->CharSet = "UTF-8"; 
    $mail->IsSMTP();
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->Subject = '您在「'.$forum_data -> forum -> name.'」的评论有了新回复';
    $mail->MsgHTML($content);
    $mail->AddAddress($parent_email, $parent_name);
    $from = !SMTP_FROM ? SMTP_USERNAME : SMTP_FROM;
    $from_name = !SMTP_FROMNAME ? $forum_data -> forum -> name : SMTP_FROMNAME;
    $mail->SetFrom($from, $from_name);
    if(!$mail->Send()) {
        echo "发送失败：" . $mail->ErrorInfo;
    } else {
        echo "恭喜，邮件发送成功！";
    }
}
