<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
//header("Content-Type:text/html; charset=utf-8");

$index = new Index();
$index->handle_message();
class Index{
    
    //tokens
    private static $access_token = "EAACcOZAMBYLEBAD7s3JSKUsNKGfUBSOgqQVX9USD5n747ex8NaMZCzJWPFEoqmDVh6JMpZCoVeKOcuDtZB8MZAlYJKY70FKZCEmzxxZBfBLhUZBlGd60bPJSMqd1VaGfOvx78KPcAtCMmD4xqD28CB0vErjtBKAHsqmQg8JnMgBY7wZDZD";
    private static $verify_token = "healthy-bank-app-chat-bot";
    
    private $sender;
    private $message;
    private $message_image;
    private $message_to_reply;
    private $input;
    
    public function __construct(){
        $hub_verify_token = null;
        if(isset($_REQUEST['hub_challenge'])) {
            $challenge = $_REQUEST['hub_challenge'];
            $hub_verify_token = $_REQUEST['hub_verify_token'];
        }
        if ($hub_verify_token === self::$verify_token) {
            echo $challenge;
        }
        $this->input = json_decode(file_get_contents('php://input'), true);
        
        $this->sender = $this->input['entry'][0]['messaging'][0]['sender']['id'];
        
        $messagingArray = $this->input['entry'][0]['messaging'][0];
        if(isset($messagingArray['postback'])){
            if($messagingArray['postback']['payload'] == 'healthybot'){
                $this->message = "Hi!\\n歡迎來到健康機器人,在這裡您可以進行簡單的身體檢測或查詢各項醫療院所喔!";
                
            }else if($messagingArray['postback']['payload'] == 'healthybot'){
                $this->message = "請輸入身高及體重進行檢測吧!e.g.180/65";
                
            }
        }else if(isset($messagingArray['message'])){
            $this->message = $messagingArray['message']['text'];   
        }
    }
    
    public function handle_message(){
        if(preg_match('[/]', strtolower($this->message))) {
            $heightWeight =  explode('/',$this->message);
            $bmi = new BMI($heightWeight[0], $heightWeight[0]);
            
            $this->message_to_reply = $bmi->get_bmi();
            $bmi = null;
            
        }else if(preg_match('[hi|hello|嗨]', strtolower($this->message))){
            $this->message_to_reply = "Hi!\\n歡迎來到健康機器人,在這裡您可以進行簡單的身體檢測或查詢各項醫療院所喔!";
    
        }else{
            $this->message_to_reply = '不好意思，暫時無法回答你的問題。可以再多給我一點提示嗎？或者等等小編來回答你。';
        }

        $this->send_message($this->message_to_reply);
        
    }
    
    private function send_button_message($message){
        //API Url
        $url = 'https://graph.facebook.com/v2.11/me/messages?access_token='.self::$access_token;
        $ch = curl_init($url);
        
        $jsonData = '{
                "recipient":{
                    "id":"'.$this->sender.'"
                },
                "message":{
                    "attachment": {
                        "type":"template",
                        "payload":{
                            "template_type":"button",
                            "text":"'.$message.'",
                            "buttons":[
                                {
                                    "type":"postback",
                                    "title":"身體檢測",
                                    "payload":"detection"
                                },
                                {
                                    "type":"postback",
                                    "title":"找醫療院所",
                                    "payload":"search"
                                }
                            ]
                        }
                    }
                }
            }';
            
            $jsonDataEncoded = $jsonData;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            if(!empty($this->input['entry'][0]['messaging'][0]['message']) || !empty($this->input['entry'][0]['messaging'][0]['postback'])){
                $result = curl_exec($ch);
            }
        
    }
    
    private function send_message($message_to_reply){
        //API Url
        $url = 'https://graph.facebook.com/v2.11/me/messages?access_token='.self::$access_token;
        $ch = curl_init($url);
        $jsonData = '{
            "recipient":{
                "id":"'.$this->sender.'"
            },
            "message":{
                "text":"'.$this->message_to_reply.'"
            }
        }';
        //echo $jsonData;
        $jsonDataEncoded = $jsonData;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        if(!empty($this->input['entry'][0]['messaging'][0]['message']) || !empty($this->input['entry'][0]['messaging'][0]['postback'])){
            $result = curl_exec($ch);
        }
        
        $this->send_button_message("我想要...");
    }
}
?>