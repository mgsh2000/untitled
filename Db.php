<?php


class Db
{

    var $check_user;

    /**
     * Db constructor.
     */
    public function __construct($chat_id)
    {
        $connection = $this->connect_to_db();
        $result= $connection -> query("SELECT * FROM User WHERE chat_id = '$chat_id'");
        $connection -> close();
        if($result->num_rows == 0) {
            $this->check_user=false;
        }
        else {
            $this->check_user=true;
        }

    }


    function insert_Handout($info,$chat_id){
        $connection = $this->connect_to_db();
        $url="file/".$info[0].".pdf";
        $popularity=0;
        $connection->query("INSERT INTO Handout (category,reshte,popularity,sender,name,url,is_set) VALUES
                          ('$info[1]', '$info[2]','$popularity','$chat_id','$info[0]','$url','n')");
        $connection -> close();

        return true;

    }


    function update_poit($url,$point,$chat_id){
        if($this->check_user_point($url,$chat_id)){
            return false;
        }else {
            $connection = $this->connect_to_db();
            $poi = $connection->query("SELECT popularity FROM Handout  WHERE url='$url'");
            $po = $poi->fetch_assoc();
            $point += $po['popularity'];
            $result = $connection->query("UPDATE Handout SET popularity='$point' WHERE url='$url'");
            $connection->close();
            if ($result == 1) {
                $this->user_point_insert($url,$chat_id);
                return true;
            }
        }

    }

    function user_point_insert($url,$chat_id){
        $connection = $this->connect_to_db();
        $connection->query("INSERT INTO Getter (hondout,geter) VALUES ('$url','$chat_id')");
        $connection -> close();
    }
    function check_user_point($url,$chat_id){
        $connection = $this->connect_to_db();
        $result= $connection -> query("SELECT * FROM Getter WHERE geter = '$chat_id' AND hondout='$url' ");
        $connection -> close();
        if($result->num_rows == 0) {
            return false;
        }
        else {
            return true;
        }
    }

    function get_url_pdf($chat_id){
        $connection = $this->connect_to_db();
        $result = $connection -> query("SELECT * FROM Handout WHERE  sender='$chat_id' AND is_set='n'");
        $row=$result->fetch_assoc();
        $url = $row['url'];
        $connection -> close();
        return $url;
    }
    function update_isset($chat_id,$url){
        $connection = $this->connect_to_db();
        $connection -> query("UPDATE Handout SET is_set='y' WHERE url='$url' AND sender='$chat_id' ");
        $connection -> close();

    }

    function url_photo($chat_id,$name){
        $connection = $this->connect_to_db();
        $connection->query("INSERT INTO Image (chat_id,name,url,is_set) VALUES ('$chat_id','$name','','n')");
        $id=$connection->insert_id;
        $url="img/".$name."_".$id.".jpg";
        $connection->query("UPDATE Image SET url='$url' WHERE id='$id'");
        $connection -> close();
        return $url;
    }
    function get_url_photo($chat_id){
        $re=array();
        $connection = $this->connect_to_db();
        $result = $connection -> query("SELECT url FROM Image WHERE chat_id ='$chat_id' AND is_set='n'");
        while ($row = $result -> fetch_assoc()){
            $url    =    $row['url'];
            array_push($re,$url);
            }
        $connection->query("UPDATE Image SET is_set='y' WHERE chat_id='$chat_id'");
        $connection -> close();
        return $re;

    }







    function show_step_user($chat_id){
        $connection = $this->connect_to_db();
        $result = $connection -> query("SELECT step FROM User  WHERE chat_id ='$chat_id'");
        $connection -> close();
        $row=$result->fetch_assoc();
        $step=$row['step'];
        return $step;
    }

    function  query(){
        $re=array();
        $connection = $this->connect_to_db();
        $result = $connection -> query("SELECT * FROM Handout");
        while($row = $result -> fetch_assoc()) {

            $id        = $row['id'];
            $name      = $row['name'];
            $category     = $row['category'];
            $reshte = $row['reshte'];
            $url    =    $row['url'];
            $sende= $row['sender'];
            $popularity=$row['popularity'];
            $reply  = "name:".$name . "\n" ."category:". $category. "\n"."reshte: ".$reshte."\n"."sender: ".$sende."\n"
                ."popularity: ".$popularity;
            array_push($re,[$url,$reply]);
        }
        $connection -> close();

        return $re;
    }

    function step_user($step,$chat_id){

        $connection = $this->connect_to_db();
        if($this->check_user)
            $connection -> query("UPDATE User SET step='$step' WHERE chat_id='$chat_id'");
        else {
            $connection->query("INSERT INTO User (chat_id,step) VALUES ('$chat_id','$step')");
            $this->check_user=true;
        }
        $connection -> close();

    }

    function connect_to_db() {
        $connection = new mysqli("localhost", "botbasig_mgsh", "m!g@s#h$2%0^0&0*", "botbasig_botmgsh");

        if ($connection -> connect_error)
            echo "Failed to connect to db: " . $connection -> connect_error;
        //  $connection -> query("SET NAMES utf8");
        return $connection;
    }

}
//$myfile = fopen("1.txt", "w") or die("Unable to open file!");
//$txt = $step;
//fwrite($myfile, $txt);
//fclose($myfile);