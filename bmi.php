<?php
class BMI {
    public $_height;
    public $_weight;
    
    public function __construct($height,$weight){
        $this->_height = $height;
        $this->_weight = $weight;
    }
    
    public function get_bmi() {
        $url = 'https://healthbot-188011.appspot.com/bmi';
        $ch = curl_init($url);
        $json = '{"height":'.$this->_height.',"weight":'.$this->_weight.'}';
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $data = curl_exec($ch);
        $data = json_decode($data,true);
        curl_close($ch);
        
        if(empty($data)){
            return "很抱歉,找不到相關服務及資料!";
        } else {
            return $data["res"];
        }
    }
}
?>