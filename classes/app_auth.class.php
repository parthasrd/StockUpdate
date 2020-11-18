<?php
final class app_auth
{
    private $db;
	private $session;

	public function __construct($host = null, $db = null, $username = null, $pw = null)
	{
		$this->db = new database();
		$this->session = new session();
    }

    public function securiy_access()
    {
        $sql = "select * from store_app_access where app_access_id ='1' and app_access_status ='Y'";
        $this->db->query($sql);
        return $this->db->singleObj();
    }
    
    public function check_store_url($shop)
    {
        $sql = "select * from store_login where login_store_url ='".$shop."' limit 1";
        $this->db->query($sql);
        $cont = $this->db->rowCount();
        if($cont>0){ $rtn = true; } else { $rtn = false; }
        return $rtn;
    }

    public function store_data($val)
    {
        $sql = "select * from store_login where login_store_url ='" . $val . "' limit 1";
        $this->db->query($sql);
        $result = $this->db->singleObj();
        return $result;
    }

    public function store_login($shop,$access_token)
    {
        $sql = "select * from store_login where 
                    login_store_url ='".$shop."' and 
                    login_store_token = '".$access_token."' and 
                    login_store_install_status = 'Y' limit 1";
        $this->db->query($sql);
        $cont = $this->db->rowCount();
        if($cont>0){ 
            $storeDtls = $this->db->singleObj();
            $rtn = $storeDtls->login_id;
            $this->session->add_session('_authstore_auto_id',$rtn);
            $rtn = true; 
        } else { 
            $rtn = false; 
        }
        return $rtn;
    }

    public function check_store_feed($feed_id, $login_store_id) {
        $sql = "select * from sync_feeds where id ='" . $feed_id . "' AND login_store_id ='" . $login_store_id . "' limit 1";
        $this->db->query($sql);
        if ($this->db->singleObj()) {
            return true;
        }
        return false;
    }


    public function new_install($shop,$access_token)
    {
        $sql = "select * from store_login where 
                    login_store_url ='".$shop."' and 
                    login_store_token = '".$access_token."' and
                    login_store_install_status = 'Y' limit 1";
        $this->db->query($sql);
        $cont = $this->db->rowCount();
        if($cont<1){ 
            $store_id = str_replace(".myshopify.com","",$shop);
            $sql = "insert into store_login set 
                            login_store_id ='".$store_id."', 
                            login_store_url ='".$shop."', 
                            login_store_token = '".$access_token."' ";
            $this->db->query($sql);
            $rtn = $this->db->getLastID();
        }
        else{
            $storeDtls = $this->db->singleObj();
            $rtn = $storeDtls->login_id;
        }
        $this->session->add_session('_authstore_auto_id',$rtn);
        return true;
    }

    public function get_installed_token_by_shop($shop)
    {
        $sql = "select * from store_login where login_store_url ='".$shop."' limit 1";
        $this->db->query($sql);
        return $this->db->singleObj();
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
