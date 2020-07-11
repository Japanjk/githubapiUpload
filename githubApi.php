
<?php
/*
 * @Author: xiflys
 * @Date: 2020-07-11 14:07:00 
 * @Last Modified by: xiflys
 * @Last Modified time: 2020-07-11 14:28:08
 */



Class GithubUp{
    
    private static $obj = null;
    #定义 需求参数
    protected static $user;
    protected static $repo;
    protected static $token;
    protected static $mail;
    protected static $repos;
    protected static $timezones;
    protected static $gitapi;
    
    
    private function __construct(array $git)
    {
        # 接收需求参数
        static::$user = $git['user'];
        static::$repo = $git['repo'];
        static::$token = $git['token'];
        static::$mail = $git['mail'];
        static::$repos = $git['repos'];
        static::$timezones = $git['timezones'];
        static::$gitapi = $git['gitapi'];
        # 设置时区
        date_default_timezone_set(static::$timezones);
    }
    
    
    /**
     * 验证，上传类
     *
     * @return void
     */
    public function validate(){
        
        function sj(int $code,string $msg,array $arrs=array(
            'msg'=>'null'
        )){
            $arr = [
                'code'=>$code,
                'msg'=>$msg,
                'content'=>$arrs
            ];
            return $arr;
        }

        if(!$this->isCurl()){
            return $this->json(sj(500,'请开启curl扩展'));
        }
        
        if(!$this->isPost()){
            return $this->json(sj(501,'仅支持Post提交'));
        }

        if(!$this->Fileexits()){
            return $this->json(sj(502,'没有上传文件'));
        }


        $filename=$this->fileIn($_FILES['file']['name']);
        
        $base64 = $this->base64c($_FILES['file']['tmp_name']);

        $ress = $this->curl_url($filename,$base64);
        
        # 验证是否上传成功
        if(empty(json_decode($ress,true)['content']['name'])){
            return $this->json([
                'code'=>503,
                'msg'=>'上传文件失败'
            ]);
        }
        $urls = "https://cdn.jsdelivr.net/gh/".static::$user."/".static::$repo.'@'.static::$repos.'/'.$filename;
        $flysurls = "https://cdn.staticaly.com/gh/".static::$user."/".static::$repo.'/'.static::$repos.'/'.$filename;
        return $this->json(sj(200,'success',array(
            'cn_url'=>$urls,
            'fly_url'=>$flysurls,
        )));
    }

    /**
     * 获取文件信息，生成随机文件名
     *
     * @param [type] $file
     * @return void
     */
    protected function fileIn($file){
        $name = date('Y').'/'.date('m').'/'.date('d').'/'.date('H').'/'.time().uniqid();
        $hz = pathinfo($file,PATHINFO_EXTENSION);
        return $name.'.'.$hz;
    }

    /**
     * 图片转base64
     *
     * @param [type] $img_file
     * @return void
     */
    protected function base64c($img_file){
        $base64code = base64_encode(file_get_contents($img_file));
        return $base64code;
    }

    /**
     * github 上传api
     *
     * @param string $filename
     * @param string $base64
     * @return void
     */
    protected function curl_url(string $filename,string $base64){
        $arr = [
            'message'=>'静态文件',
            'committer'=>[
                'name'=>static::$user,
                'email'=>static::$mail,
                'branch'=>static::$repos,
            ],
            "content"=> $base64
        ];

        $url = "https://api.git.sdut.me/repos/".static::$user."/".static::$repo."/contents/".$filename;
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS =>$this->json($arr),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "User-Agent:postman",
            "Authorization:token ".static::$token
        ),
        
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    /**
     * 判断文件是否上传
     *
     * @return void
     */
    protected function Fileexits(){
        @$file = $_FILES['file'];
        if(empty($file['name'])&&$file['name'] == ''){
            return false;
        }else{
            return true;
        }
    }

    /**
     * curl 扩展是否开启
     *
     * @return boolean
     */
    protected function isCurl(){
        if(is_callable('curl_init')){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 是否是Post提交
     *
     * @return boolean
     */
    public function isPost(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            return true;
        }else{
            return false;
        }
    }

    protected function json(array $arr){
        return json_encode($arr);
    }

    /**
     * 禁止new对象
     *
     * @param array $git
     * @return void
     */
    public static function getInstance(array $git=array())
	{
		if(!self::$obj instanceof self)
		{
			self::$obj = new self($git);
		}
		return self::$obj;
	}
}
