<?php
/**
 * Lite 使用实例代码：
 * <?php
 * require_once("../WEB-INF/classes/LiteEngine.php");
 * # 通过上下文数据方式传递模板参数：
 * LiteEngine::render("/example/test.xhtml",array("int1"=>1,"text1"=>'1'));
 * 
 * ## 直接通过全局变量传递模板参数：
 * #$int1 = 1;
 * #$text1 = '1';
 * #LiteEngine::render("/example/test.xhtml");
 *
 * @see http://lite.googlecode.com 
 * 
 */
class LiteService{
	private $url = "/WEB-INF/classes/lite/LiteService.php";
	private $root;
	private $litecode;
	/**
	 * Lite 模板编译服务
	 * 编译服务的三个重要设置(debug/root/litecode)不可修改，只能俺TemplateEngine 默认设置
	 */
	function LiteService(){
		global $lite_engine;
		if($lite_engine == null){
			require_once('LiteEngine.php');
			$engine = new LiteEngine();
			$this->root = $engine->root;
			$this->litecode = $engine->litecode;
			$this->url = $_SERVER['SCRIPT_NAME'];
		}else{
			$this->root = $lite_engine->root;
			$this->litecode = $lite_engine->litecode;
			$this->reset(__FILE__);
		}
	}
	/**
	 * 重置URL 地址(有的网站可能配在子目录中[不推荐这种部署方式])
	 */
	private function reset($file){
		$root = @$_SERVER['DOCUMENT_ROOT'] ;
		$url =$this->url;
		if(!$root || 
			 $this->root && realpath($this->root) == realpath($root)
			 && $url && file_exists($root.$url)
			 ){
			return;
		}
		//放在某个子目录下了.
		$begin = strlen($root);
		if(strncmp($file,$root,$begin) ===0){
			$url =substr($file,$begin);
			if(file_exists($root.$url)){
				$this->url = $url;
			}
		}
	}
	public function debug($path,$context){
		global $lite_engine;
		$lite = $lite_engine->litecode.strtr($path,'/','^');
		$fn = 'lite_template'.str_replace(array('.','/','-','!','%'),'_',$path);
		$compile = $lite_engine->autocompile || @$_SERVER['LITE_COMPILE_SERVICE'];
		$debug = @$_COOKIE["LITE_DEBUG"];
		if($debug && preg_match('/^(\w+)(?:[;,\s]+(.*))?/',$debug,$result)){
			switch($result[1]){
			case 'source':
				$file = $lite_engine->root.$path;
				if(file_exists($file)){
					readfile($file);
				}else{
					header('HTTP/1.1 404 Not Found');
					trigger_error("source code not found:$path");
				}
				return;
			case 'model':
				$lite_code = file_get_contents($lite);
				$dataUrl = @$result[2];
				if($dataUrl){
					$p = strpos($dataUrl,$this->url);
					if($p>0){
						$fileUrl = substr($dataUrl,$p+strlen($this->url));
						$fileUrl = $this->root.$fileUrl;
						if(file_exists($fileUrl)){
							$dataUrl = $fileUrl;
						}
					}
					//$dataUrl = str_replace('//localhost/','//127.0.0.1/',$dataUrl);
					$json = file_get_contents($dataUrl);
					$context = json_decode($json,true);
					//TODO:编码转换
				}else{
					header("Content-Type:text/html;charset=UTF-8");
					$featureMap = array();
					$scriptPath = $this->url.'?LITE_ACTION=load&LITE_PATH=/scripts/data-view.js';
					echo "<!DOCTYPE html><html><body>\n",
						"<style>body,html{width:100%;height:100%}</style>\n",
						"<script>",
						"var templatePath ='$path'",
						";\nvar templateModel = ",json_encode($context),
						";\nvar templateFeatureMap = ",json_encode($featureMap),
						";\nvar serviceBase='",$this->url,"'",
						";</script>\n",
						"<script src='$scriptPath'></script>\n",
						"<script>if(!this.DataView && this.\$import){\$import('org.xidea.lite.web.DataView',true);}</script>\n";
						
					
					$model_view_impl = @$_COOKIE["LITE_MODEL_VIEW_IMPL"];
					if($model_view_impl){
						echo "<script src='$model_view_impl'></script>";
					}
					echo "<script>DataView.render(templatePath,templateModel,templateFeatureMap,serviceBase);</script>\n";
					if($model_view_impl){
						echo "<div><a href='#' onclick=\"document.cookie='LITE_MODEL_VIEW_IMPL=;expires='+new Date(0).toGMTString();alert('恢复成功')\">回复默认视图</a></div>";
					}
					echo "<hr>\n<pre>";
					echo json_encode($context);
					echo '<hr/>';
					var_dump($context);
					echo "</pre></body></html>";
					return true;
				}
				break;
			case 'refresh':
				if($compile){
					unlink($lite.'.php');
				}else{
					trigger_error("compile is off !!can not recompile the template:$path");
				}
				break;
			}
			
    	}
    	if($compile){
			$this->compile($path);
		}else{
			//TODO: file check and helper info
		}
    	require_once($lite.'.php');
    	$fn($context);
	}
	public function execute(){
		$lite_action = @$_REQUEST['LITE_ACTION'] ;
		if($lite_action == 'save'){
			$lite_path = @$_REQUEST['LITE_PATH'] ;
			$this->save($lite_path);
		}else if($lite_action == 'load'){//resource
			$lite_path = @$_REQUEST['LITE_PATH'] ;
			$this->load($lite_path);
		}else{//resource
			$this->printResource();
		}
	}
	private function load($path){
		$this->printResource($path);
	}
	private function printResource($pathinfo=null){
		if(!$pathinfo){
			$pathinfo = @$_SERVER['PATH_INFO']?$_SERVER['PATH_INFO']:$_SERVER['ORIG_PATH_INFO'];
		}
		if(strncmp($pathinfo,'/scripts/',9) == 0){
			header("Content-Type:text/javascript;charset=utf-8");
			//echo '!!'.substr($pathinfo,10);
			readfile(dirname(__FILE__).substr($pathinfo,8));
		}else if($pathinfo){
			if(substr($pathinfo,-6) == '.xhtml' || substr($pathinfo,-4) == '.xml' ){
				header("Content-Type:text/json;charset=utf-8");
				$path = $this->root.$pathinfo;
				readfile($path);
			}else if(substr($pathinfo,-5) == '.json'){
				$path = $this->root.$pathinfo;
				header("Content-Type:text/javascript;charset=utf-8");
				header("Content-Length:".filesize($path));
				readfile($path);
			}else{
				echo "not support";
			}
		}else if($lite_action){
			trigger_error("not support action [{$lite_action}]");
			//print_r($_REQUEST);
		}else{
			header("Location:./");
		}
	}
	/**
	 * 1. 存储模板编译结果
	 * 2. 存储模拟数据
	 */
	private function save($lite_path){
		if(preg_match('/\.\./',$lite_path)){
			trigger_error("saved path can not contains '..'(parent dir is not allowed)!");
		}
		if(preg_match('/\.json$/',$lite_path)){
			if($this->saveJSON($lite_path,@base64_decode($_REQUEST['LITE_DATA']),$_REQUEST['LITE_CALLBACK'])){
				return;
			}
		}
		$lite_code = @$_POST['LITE_CODE'];
		$lite_code = base64_decode($lite_code);
		$lite_php = base64_decode($_POST['LITE_PHP']) ;
		$litefile = $this->litecode.'/'.strtr($lite_path,'/','^');
		$phpfile = $litefile.'.php';
		if($lite_code){
			file_put_contents($litefile,$lite_code);
		}
		file_put_contents($phpfile,$lite_php);
		if($lite_php){
			$error = $this->checkError($phpfile);
			echo json_encode(array("success"=>!$error,
					"error"=>$error,
					"phpPath"=>$phpfile));
		}else{
			echo '{"success":false,"error":"php file not found","phpPath":"',
					json_encode($phpfile),'"}';
		}
		flush();
		
	}
	private function saveJSON($path,$data,$callback){
		if(!preg_match('/^\/WEB\-INF\/litecode\/.*\.json/',$path)){
			if(!file_exists($this->root. preg_replace('/.json$/','.xhtml',$path))){
				//trigger_error("$path is not a mock data,can not save as json mock data");
				return false;
			}
		}
		$absPath = $this->root.$path;
		file_put_contents($absPath,$data);
		$rtv = json_encode(array('success'=>true,'absPath'=>$absPath,"path"=>$path));
		if($callback){
			$rtv = "$callback($rtv,true)";
		}
		echo $rtv;
		return true;
	}
	private function checkError($phpfile){
		ob_start();
		require($phpfile);
		$rtv = ob_get_contents();
		ob_clean();
		return $rtv;
	}
	private function loadJavaScriptClass(){
		$serviceBase = $this->url;
		$fns = func_get_args();
		
		$sns = array();
		foreach($fns as $fn){
			array_push($sns,preg_replace('/^.*[\.\:]/','',$fn));
		}
		$checkScript = 'window.'.join($sns,'&& window.');
		$importScript = '$import("'.join($fns,	'",true);$import("').'",true);';
		
		
		echo "<script>if(!($checkScript)){document.write(\"<script src='$serviceBase/scripts/boot.js'></\"+\"script>\");}</script>\n"
			,"<script>if(!($checkScript)){$importScript}</script>";
	}
	private function compile($path){
		$litecode = $this->litecode;
		if($litecode==null){
			trigger_error("$litecode is not set.");
		}else if(!file_exists($litecode)){
			if(!file_exists(dirname($litecode))){
				mkdir(dirname($litecached));
			}
			if(!mkdir($litecode)){
				trigger_error("mkdir $litecode failed,please create it yourself,and set model 733.");
			}
			if(!is_writable($litecode)){//733 need
				//linux 下还需要  is_executable($litecode)
				trigger_error("$litecode is readonly,can not save compiled result");
			}
		}
		if( $this->getFileModified($path)){
			$serviceBase = $this->url;
			
			$compile_service = @$_SERVER['LITE_COMPILE_SERVICE'];
			if($compile_service){
				file_get_contents($compile_service.'?path='.$path);
				return ;
			}
			
			$this->loadJavaScriptClass('org.xidea.lite.web:WebCompiler');
			$config = realpath($this->root.'/WEB-INF/lite.xml');
			if($config){
				$config = file_get_contents($config);
			}
			$config = json_encode($config);
			echo "<script>"
				,"var LITE_WC = window.LITE_WC || new WebCompiler('$serviceBase/',$config || null);\n"
				,"try{\n"
				,"	LITE_WC.compile('$path');\n"
				,"}finally{\n"
				,"	LITE_WC.save();\n"
				,"}\n</script>";
			$litefile = $litecode.'/'.strtr($path,'/','^');
    		$phpfile = $litefile.'.php';
			if(file_exists($phpfile)){unlink($phpfile);}//.$phpfile.'<hr>';
			for($i=0;$i<10240 && !file_exists($phpfile);$i++){
				echo ' ';
				flush();
			}
			while(!file_exists($phpfile)){
				sleep(1);
			}
			echo "\n";
		}
		/*
		require_once($phpfile);
		if(!function_exists('lite_template'.str_replace(array('.','/','-','!','%'),'_',$path))){
			unlink($phpfile);
		}
		*/
	}
	public function getFileModified($path){
		$litefile = $this->litecode.'/'.strtr($path,'/','^');
    	$phpfile = $litefile.'.php';
		if(!file_exists($litefile)){
			return $litefile;
		}
		if(!file_exists($phpfile) || filesize($phpfile) == 0){
			return $phpfile;
		}
		$phptime = filemtime($phpfile);
		$lite = json_decode(file_get_contents($litefile),true);
		$resources = @$lite[0];
		if(is_array($resources)){
			foreach($resources as $spath){
				$file = $this->root.$spath;
				if(file_exists($file)){
					if(filemtime($file) > $phptime){
						return $file;
					}
				}else{
					return $file;
				}
			}
			return false;
		}
		return $litefile;
		
	}
}
if(realpath( $_SERVER['DOCUMENT_ROOT'].'/'. $_SERVER['SCRIPT_NAME'] ) == realpath(__FILE__)){
	//direct access from remote
	$service = new LiteService();
	$service->execute();
}else{//included
}
?>