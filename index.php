<?php
require_once ("Answer.php");
define('bot_url',"https://api.telegram.org/bot1880352321:AAEdEIQqIC8usrtGf7JM6qNaVIBLlhUJLDI") ;
define('bot_dl_url',"https://api.telegram.org/file/bot1880352321:AAEdEIQqIC8usrtGf7JM6qNaVIBLlhUJLDI") ;
$update = file_get_contents("php://input");
$update_array = json_decode($update, true);
$answer=null;
$text=null ;
if( isset($update_array["message"]["text"])) {
    $chat_id = $update_array["message"]["chat"]["id"];
    $text = $update_array["message"]["text"];
    $answer=new Answer(bot_url,$text,$chat_id);
    $answer->select_answer($text);
 //   $image=new image();

}

else if( isset($update_array["callback_query"]) ) {

    $data              = $update_array["callback_query"]["data"];
    $callback_query_id = $update_array["callback_query"]["id"];
    $chat_id           = $update_array["callback_query"]["message"]["chat"]["id"];

    $answer=new Answer(bot_url,$data,$chat_id);
    $answer->get_point();
}

else if( isset($update_array["message"]["document"]) ){
    $chat_id= $file_id   = $update_array["message"]["chat"]["id"];
    $answer=new Answer(bot_url,"ss",$chat_id);
    $answer->save_pdf($update_array);

}

else if( isset($update_array["message"]["photo"]) ) {
    $chat_id= $file_id   = $update_array["message"]["chat"]["id"];
    $answer=new Answer(bot_url,"ss",$chat_id);
    $answer->save_photo($update_array);

}

if( isset($update_array["message"]["reply_to_message"]) ) {

    $text_replied = $update_array["message"]["reply_to_message"]["text"];
    $answer->get_info_Handout($text);

}






?>
