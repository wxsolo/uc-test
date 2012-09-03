<?php 
/** 
 * edit by solo
 * $Id:Curl.class.php 
 *  
 * CURL HTTP工具类 
 *  
 * 支持以下功能： 
 * 1：支持proxy代理连接 
  *  
 */  
  
class Curl{  
  
    private $ch = null;              //CURL句柄  
    private $info = array();         //CURL执行前后所设置或服务器端返回的信息  
  
    //CURL SETOPT 信息  
    private $setopt = array(  
     'port'=>80,                     //访问的端口,http默认是 80  
     'userAgent'=>'',                //客户端 USERAGENT,如:"Mozilla/4.0",为空则使用用户的浏览器  
     'timeOut'=>30,                  //连接超时时间  
     'useCookie'=>true,              //是否使用 COOKIE 建议打开，因为一般网站都会用到  
     'ssl'=>false,                   //是否支持SSL  
     'gzip'=>true,                   //客户端是否支持 gzip压缩  
  
     'proxy'=>false,                 //是否使用代理  
     'proxyType'=>'HTTP',            //代理类型,可选择 HTTP 或 SOCKS5  
     'proxyHost'=>'123.110.89.248',  //代理的主机地址  
     'proxyPort'=>8909,              //代理主机的端口  
     'proxyAuth'=>false,             //代理是否要身份认证(HTTP方式时)  
     'proxyAuthType'=>'BASIC',       //认证的方式.可选择 BASIC 或 NTLM 方式  
     'proxyAuthUser'=>'user',        //认证的用户名  
     'proxyAuthPwd'=>'password',     //认证的密码  
    );  

    /** 
     * 构造函数 
     */  
    public function Curl($setopt=array()){  
        
       

        $this->setopt = array_merge($this->setopt,$setopt);       //合并用户的设置和系统的默认设置  
  
        function_exists('curl_init') || die('CURL Library Not Loaded');     //如果没有安装CURL则终止程序  
  
        $this->ch = curl_init();     //初始化  
  
        curl_setopt($this->ch, CURLOPT_PORT, $this->setopt['port']);  //设置CURL连接的端口  
  
        //使用代理  
        if($this->setopt['proxy']){  
            $proxyType = $this->setopt['proxyType']=='HTTP' ? CURLPROXY_HTTP : CURLPROXY_SOCKS5;  
            curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxyType);  
            curl_setopt($this->ch, CURLOPT_PROXY, $this->setopt['proxyHost']);  
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $this->setopt['proxyPort']);  
                  
            //代理要认证  
            if($this->setopt['proxyAuth']){  
                $proxyAuthType = $this->setopt['proxyAuthType']=='BASIC' ? CURLAUTH_BASIC : CURLAUTH_NTLM;  
                curl_setopt($this->ch, CURLOPT_PROXYAUTH, $proxyAuthType);  
                $user = "[{$this->setopt['proxyAuthUser']}]:[{$this->setopt['proxyAuthPwd']}]";  
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $user);  
            }  
        }  
  
        //curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);    //启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器  
  
        //打开的支持SSL  
        if($this->setopt['ssl']){  
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);   //不对认证证书来源的检查  
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, true);    //从证书中检查SSL加密算法是否存在  
        }  
  
        //$header[]= 'Expect:';   //设置http头,支持lighttpd服务器的访问  
       // curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);  
        //$userAgent = $this->setopt['userAgent'] ? $this->setopt['userAgent'] : $_SERVER['HTTP_USER_AGENT'];       //设置 HTTP USERAGENT  
        //curl_setopt($this->ch, CURLOPT_USERAGENT, $userAgent);  
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT,0); //设置连接等待时间,0不等待  
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->setopt['timeOut']);            //设置curl允许执行的最长秒数  
  
        curl_setopt($this->ch, CURLOPT_HEADER, true);            //是否将头文件的信息作为数据流输出(HEADER信息),这里保留报文  
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ;   //获取的信息以文件流的形式返回，而不是直接输出。  
        //curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true) ;  
    }

        /** 
     * 得到错误信息 
     * 
     * @return string 
     */  
    public function error(){  
        return curl_error($this->ch);  
    }  
  
    /** 
     * 得到错误代码 
     * 
     * @return int 
     */  
    public function errno(){  
        return curl_errno($this->ch);  
    }  
  
    /** 
     * 得到发送请求前和请求后所有的服务器信息和服务器Header信息: 
     * [before] ：请求前所设置的信息 
     * [after] :请求后所有的服务器信息 
     * [header] :服务器Header报文信息 
     * 
     * @return array 
     */  
    public function getInfo(){  
        return $this->info;  
    }  
  
    /** 
     * 析构函数 
     * 
     */  
    public function __destruct(){
        curl_close($this->ch);  
    }  

     /** 
     * 发出请求 
     * 
     */  
    public function get($url) 
    {
        curl_setopt($this->ch,CURLOPT_URL,$url);

        $this->info['before'] = curl_getinfo($this->ch);              //得到所有设置的信息  
        $result = curl_exec($this->ch);                                  //开始执行请求  
        if($result === false)
        {
            curl_close($this->ch);
             return false;  
        }
       // $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE); //得到报文头  
       // $this->info['header'] = substr($result, 0, $headerSize);  
  
        //$result = substr($result, $headerSize);                         //去掉报文头  
        $this->info['after'] = curl_getinfo($this->ch);                   //得到所有包括服务器返回的信息  
  
        //如果请求成功  
        if($this->errno() == 0){ //&& $this->info['after']['http_code'] == 200  
            return $result;  
        }else{  
            return false;  
        }  

    }


}

/*
//使用代理  
$setopt = array('proxy'=>true,'proxyHost'=>'','proxyPort'=>'');  
$cu = new Curl();  
//得到 baidu 的首页内容  
echo $cu->get('http://www.baidu.com/'); 

//得到所有调试信息  
echo 'ERRNO='.$cu->errno();  
echo 'ERROR='.$cu->error();  
$info = $cu->getinfo();
print_r($info['after']);
*/