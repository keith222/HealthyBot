<?php
class Cancer{
    public $_gender;
    public $_age;
    public $_sgpt;
    public $_hbeag;
    
    public function __construct($gender,$age,$sgpt,$hbeag){
        $this->_gender = $gender;
        $this->_age = $age;
        $this->_sgpt = $sgpt;
        $this->_hbeag = $hbeagl;
    }
    
    public function get_cancer_info(){
        $url = 'https://healthbot-188011.appspot.com/risk';
        $ch = curl_init($url);
        $json = '{"gender":'.$this->_gender.',"age":'.$this->_district.',"sgpt":'.$this->_sgpt.',"hbeag":'.$this->_hbeag.'}';
      
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
            return "發生機率：".$data["res"];
        }
    }
}
?>