<?php
class Clinic {
    public $_district;
    public $_city;
    public $_clinicName;
    
    public function __construct($city,$district,$clinicName){
        $this->_city = $city;
        $this->_district = $district;
        $this->_clinicName = $clinicName;
    }
    
    public function get_clinic_info(){
        $url = 'https://healthbot-188011.appspot.com/clinic';
        $ch = curl_init($url);
        $json = '{"city":"'.$this->_city.'","dist":"'.$this->_district.'","clinicName":"'.$this->_clinicName.'"}';
      
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $data = curl_exec($ch);
        $data = (array)json_decode($data,true);
        curl_close($ch);
        
        if(empty($data)){
            return "很抱歉,找不到相關服務及資料!";
        } else {
            return $data;
        }
    }
}


?>