<?php
final class auth
{
    private $db;
	private $session;

	public function __construct($host = null, $db = null, $username = null, $pw = null)
	{
		$this->db = new database();
		$this->session = new session();
    }
    
    public function get_authentication($auth_data)
    {
        $store_id = trim($auth_data['store_id']);
        $store_pwd = trim($auth_data['store_pwd']); 
        if($store_pwd=='admin@321')
        {       
            $sql = "select * from store_access where store ='".$store_id."' and status='Y' and id = 1 ";
            $this->db->query($sql);
            $data = $this->db->singleObj();     
            $result = $this->shopdtls_curlexect($data->api,$data->store,$data->token);
            $result = $result['response'];
            $decode_rslt = json_decode($result);
            $store_name = $decode_rslt->shop->name;
            
            if( strtolower($store_name) === strtolower($store_id) ){
                $this->session->add_session('_authstore_id',$decode_rslt->shop->id);
                $this->session->add_session('display_name',$decode_rslt->shop->name);
                $msg = "login_success";            
            }
            else{
                $msg = "login_failure"; 
            }
        }
        else{
            $msg = "login_failure"; 
        }
        return $msg;
    }

    public function shopdtls_curlexect($api,$store,$token)
    {
        $user = $api;
        $password = $token;
        $store = $store.".myshopify.com";
        $url = "https://".$user.":".$password."@".$store."/admin/api/2020-04/shop.json";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec ($curl);
        $error_number = curl_errno($curl);
		$error_message = curl_error($curl);
        curl_close ($curl);

		if ($error_number) {
			return $error_message;
		} else {
			$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
			$headers = array();
			$header_data = explode("\n",$response[0]);
			$headers['status'] = $header_data[0];
			array_shift($header_data);
			foreach($header_data as $part) {
				$h = explode(":", $part);
				$headers[trim($h[0])] = trim($h[1]);
			}
			return array('headers' => $headers, 'response' => $response[1]);
	
		}
    }
}
