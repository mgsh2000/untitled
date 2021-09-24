<?php


class Answer
{
    var $text;
    var $chat_id;
    var $bot_url;
    var $db;
   var $url_pdf="";
     var  $bot_dl_url="https://api.telegram.org/file/bot1880352321:AAEdEIQqIC8usrtGf7JM6qNaVIBLlhUJLDI" ;

    /**
     * Answer constructor.
     */
    public function __construct($bot_url, $text, $chat_id)
    {
        require_once("Db.php");
        require_once("image.php");
        $this->text = $text;
        $this->chat_id = $chat_id;
        $this->bot_url = $bot_url;
        $this->db = new Db($this->chat_id);

    }


    function select_answer($text)
    {

        $key1 = 'جزوه';
        $key2 = 'ارتباط با ما';
        $key3 = 'نظرسنجی';
        $key4 = 'comscaner';
        $key5 = 'درباره ما';
        $key6 = 'ارسال جزوه';
        $reply_keyboard = [
            [$key5, $key2],
            [$key6, $key1],
            [$key4, $key3],

        ];
        $reply_kb_options = [
            'keyboard' => $reply_keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];


        switch ($text) {
            case "/start" :
                $this->show_menu($reply_kb_options);
                break;
            case "ارتباط با ما":
                $this->contect_us();
                $this->db->step_user("contect_us",$this->chat_id);
                break;
            case "نظرسنجی":
                $this->Poll();
                $this->db->step_user("Poll",$this->chat_id);
                break;
            case "comscaner":
                $this->send_message("لطفا عکس هاتون رو بفرستید و بعد از اتمام کلمه پایان رو بفرستید");
                $this->db->step_user("comscaner",$this->chat_id);
                break;
            case "جزوه":
                $this->Handout();
                $this->db->step_user("Handout",$this->chat_id);
                break;
            case "درباره ما":
                $this->Abot_us();
                $this->db->step_user("Abot_us",$this->chat_id);
                break;
            case "ارسال جزوه":
                $this->send_Handout();
                $this->db->step_user("send_Handout",$this->chat_id);
                break;

            case"پایان":
                $this->comscaner();
                break;



        }
    }

    function Poll()
    {
        $this->send_message($this->url_pdf);

    }

    function comscaner()
    {
        $name_pdf="pdf/".$this->chat_id.".pdf";
        $img=$this->db->get_url_photo($this->chat_id);
        $this->send_message($name_pdf);
        new image($img,$this->chat_id);
        $url = bot_url . "/sendDocument";
        $post_params = [
            'chat_id' => $GLOBALS['chat_id'],
            'document' => new CURLFILE(realpath("$name_pdf")),
            'caption' => "خدمت شما ",  // optional
            'parse_mode' => 'HTML',

        ];
        $this->send_reply($url, $post_params);


    }

function   save_photo($update_array1){
    $update_array =$update_array1;

    $diff_size_count = sizeof($update_array["message"]["photo"]);

    for($i = $diff_size_count - 1 ; $i >= 0 ; $i--) {

        $file_size = $update_array["message"]["photo"][$i]["file_size"];

        if($file_size < 1000000) {  // 1 MB

            $file_id = $update_array["message"]["photo"][$i]["file_id"];
            break;
        }
    }

    $url = bot_url . "/getFile";
    $post_params = [ 'file_id' => $file_id ];
    $result = $this->send_reply($url, $post_params);

    $result_array = json_decode($result, true);
    $file_path    = $result_array["result"]["file_path"];

    $url = $this->bot_dl_url . "/$file_path";
    $file_data = file_get_contents($url);
    $url_img=$this->db->url_photo($this->chat_id,$this->chat_id);
    $img_path =$url_img;
    $my_file  = fopen($img_path, 'w');
    fwrite($my_file, $file_data);
    fclose($my_file);
$this->send_message("تصویر با موفقیت آپلود شد");
}




    function Handout()
    {
        $result = $this->db->query();
        foreach ($result as $value){

            $this->send_pdf($value[0], $value[1]);
        }


    }

    function send_Handout()
    {
        $force_reply_options = ['force_reply' => true];
        $json_fr = json_encode($force_reply_options);
        $reply = "<b>" . "به صورت گفته شده اطلاعات خواسته شده را وارد کنید" . "</b>" . "\n" .
            "name,category,reshte,";
        $this->send_message_reply($reply, $json_fr);
    }

    function get_info_Handout($txt){
        $info=explode(",",$txt);
        $this->url_pdf="file/".$info[0].".pdf";

        $this->db->insert_Handout($info,$this->chat_id);
        $this->send_message("لطفا فایل پی دی اف را ارسال کنید");
    }


    function save_pdf($update_array){
        $file_id   = $update_array["message"]["document"]["file_id"];
        $file_type = $update_array["message"]["document"]["mime_type"];
        if($file_type == "application/pdf") {

            $url = bot_url . "/getFile";
            $post_params = [ 'file_id' => $file_id ];
            $result = $this->send_reply($url, $post_params);
            $result_array = json_decode($result, true);

            $file_path    = $result_array["result"]["file_path"];

            $url = $this->bot_dl_url . "/$file_path";
            $file_data = file_get_contents($url);

            $file_path =$this->db->get_url_pdf($this->chat_id);
            $my_file   = fopen($file_path, 'w');
            fwrite($my_file, $file_data);
            fclose($my_file);
            $this->db->update_isset($this->chat_id,$file_path);
            $reply = "فایل پی دی اف با موفقیت آپلود شد";
            $url = $this->bot_url . "/sendMessage";
            $post_params = [ 'chat_id' => $this->chat_id , 'text' => $reply ];
            $this-> send_reply($url, $post_params);
        }
        else {

            $reply = "خطا! لطفا یک فایل پی دی اف ارسال کنید";
            $url = $this->bot_url. "/sendMessage";
            $post_params = [ 'chat_id' => $this->chat_id , 'text' => $reply ];
            $this-> send_reply($url, $post_params);
        }

    }


    function get_point(){
        $result=explode(",",$this->text);
        $result[0]=(int)$result[0];
        $re=$this->db->update_poit($result[1],$result[0],$this->chat_id);
        if($re){
            $this->send_message_alert("نظر شما ثبت شد");
        }else{
            $this->send_message_alert("نظر شما قبلا ثبت شده");

        }


    }//karbar yek bar bishtar na tone braye har jozve




    function send_pdf($url_pdf, $caption)
    {
        $inline_keyboard = [

            [
                [ 'text' => "5" , 'callback_data' => "5,".$url_pdf ]
            ] ,
            [
                [ 'text' => "3" , 'callback_data' => "3,".$url_pdf  ],[ 'text' => "4" , 'callback_data' => "4,".$url_pdf  ]
            ] ,
            [
                [ 'text' => "2" , 'callback_data' => "2,".$url_pdf  ],  [ 'text' => "1" , 'callback_data' => "1,".$url_pdf  ]
            ] ,

        ];

        $inline_kb_options = [
            'inline_keyboard' => $inline_keyboard
        ];
        $json_kb = json_encode($inline_kb_options);

        $url = bot_url . "/sendDocument";
        $post_params = [
            'chat_id' => $GLOBALS['chat_id'],
            'document' => new CURLFILE(realpath($url_pdf)),
            'caption' => $caption,  // optional
            'parse_mode' => 'HTML',
            'reply_markup' => $json_kb

        ];
        $this->send_reply($url, $post_params);
    }







    function send_message($tex)
    {
        $reply = $tex;
        $url = $this->bot_url . "/sendMessage";
        $post_params = ['chat_id' => $GLOBALS['chat_id'], 'text' => $reply, 'parse_mode' => 'HTML'];
        $this->send_reply($url, $post_params);
    }
    function send_message_reply($tex, $json_fr)
    {
        $reply = $tex;
        $url = $this->bot_url . "/sendMessage";
        $post_params = ['chat_id' => $GLOBALS['chat_id'], 'text' => $reply, 'parse_mode' => 'HTML', 'reply_markup' => $json_fr];
        $this->send_reply($url, $post_params);
    }
    function send_message_alert($tex)
    {
        $reply = $tex;
        $url = $this->bot_url . "/sendMessage";
        $post_params = ['chat_id' => $GLOBALS['chat_id'], 'text' => $reply, 'parse_mode' => 'HTML','show_alert' => true];
        $this->send_reply($url, $post_params);
    }//alert kar nemikone

    function show_menu($reply_kb_options)
    {

        $json_kb = json_encode($reply_kb_options);
        $reply = "<b>" . "یکی از گزینه های زیر را انتخاب کنید" . "</b>";
        $url = bot_url . "/sendMessage";
        $post_params = ['chat_id' => $GLOBALS['chat_id'], 'text' => $reply, 'reply_markup' => $json_kb, 'parse_mode' => 'HTML'];
        $this->send_reply($url, $post_params);
    }

    function contect_us()
    {
        $url = $this->bot_url . "/sendContact";
        $post_params = [
            'chat_id' => $this->chat_id,
            'phone_number' => "<b>" . "09131234567" . "</b>",
            'first_name' => "basige",
            'last_name' => "daneshjoii", // optional
            'parse_mode' => 'HTML'
        ];
        $this->send_reply($url, $post_params);
    }

    function Abot_us()
    {
        $about = "<b>" . "بسیج دانشجویی دانشگاه فردوسی" . "</b>";
        $about .= "\n" . "ربات نظر سنجی و دریافت جزوه با ثبت نام در ربات ";
        $this->send_message("$about");
    }

    function send_reply($url, $post_params)
    {

        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true); // get result
        $result = curl_exec($cu);
        curl_close($cu);
        return $result;
    }


}