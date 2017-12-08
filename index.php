<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
//header("Content-Type:text/html; charset=utf-8");
require_once('bmi.php');

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
    private $payload;
    
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
            $this->payload = $messagingArray['postback']['payload'];
            
            if($this->payload == 'healthybot'){
                $this->message = "Hi!\\n歡迎來到健康機器人,在這裡您可以進行簡單的身體檢測或查詢各項醫療院所喔!";
                
            }else if($this->payload == 'detection'){
                $this->message = "請輸入身高及體重進行檢測吧! e.g.180/65";
                
            }else if($this->payload == 'search'){
                $this->message = "您可以直接輸入城市、區域或醫療院所名稱進行查詢! 格式：城市,區域,醫院名稱";
                
            }else if($this->payload == 'cancer'){
                $this->message = "請依格式輸入：性別-年齡-??-??";
            }
        }else if(isset($messagingArray['message'])){
            $this->message = $messagingArray['message']['text'];   
        }
        
    }
    
    public function handle_message(){
        if(!empty($this->payload)){
            $this->send_message($this->message);
            return;
        }
        
        if(preg_match('/^[\d]{1,3}\/[\d]{1,3}/', strtolower($this->message))) {
            $heightWeight =  explode('/',$this->message);
            $bmi = new BMI($heightWeight[0], $heightWeight[1]);
            
            $this->message_to_reply = $bmi->get_bmi();
            $bmi = null;
        }else if (preg_match('/.,/', strtolower($this->message))) {
            $clinicInfo = explode(',', $this->message);
            
            if(empty($clinicInfo[0])) $clinicInfo[0] = "";
            if(empty($clinicInfo[1])) $clinicInfo[1] = "";
            if(empty($clinicInfo[2])) $clinicInfo[2] = "";
            
            $clinic = new Clinic($clinicInfo[0],$clinicInfo[1],$clinicInfo[2]);
            $clinicInfo = $clinic->get_clinic_info();
            
            foreach($clinicInfo as $value){
                $this->message_to_reply .= $value[0].'\\n'.$value[1].'\\n'.$value[2].'\\n\\n';
            }
            
            $clinic = null;
        }else if(preg_match('[-]', strtolower($this->message))){
            $cancerInfo = explode('-', $this->message);
            
            if(empty($cancerInfo[0]) || empty($cancerInfo[1]) || empty($cancerInfo[2]) || empty($cancerInfo[1])) {
                $this->message_to_reply = "輸入錯誤，請重新輸入。或是輸入hi重新開始";
            }else {
                $cancer = new Cancer($clinicInfo[0],$clinicInfo[1],$clinicInfo[2],$clinicInfo[3]);
                $this->message_to_reply = $cancer->get_cancer_info();
                $cancer = null;
            }
        }else if(preg_match('[hi|hello|嗨]', strtolower($this->message))){
            $this->message_to_reply = "Hi!\\n歡迎來到健康機器人,在這裡您可以進行簡單的身體檢測或查詢各項醫療院所喔!";
            $this->send_button_message("我想要...");
        }else{
            $this->message_to_reply = '不好意思，暫時無法回答你的問題。可以再多給我一點提示嗎？或者等等小編來回答你。或是輸入hi重新開始';
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
                                },
                                {
                                    "type": "postback",
                                    "title": "癌症機率",
                                    "payload": "cancer"
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
                "text":"'.$message_to_reply.'"
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
        
        if($this->input['entry'][0]['messaging'][0]['postback']['payload'] == 'healthybot'){
            $this->send_button_message("我想要...");
        }
    }
}
?>