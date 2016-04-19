<?php
namespace Admin\Controller;;
use Think\Controller;

class BaseController extends Controller{
	
	public function __construct(){
		
		parent::__construct();
		
		//账户验证
		$this->_accountAuth();
		
		//菜单绑定
		$this->_accountMenu();
	}
	
	/*
	 * 管理员登录状态控制
	 * */
	private function _accountAuth(){
	
		$userName = session(C("SESSION_KEY.USERNAME"));
		$userId = session(C("SESSION_KEY.USERID"));
		$userGroup = session(C("SESSION_KEY.USERGROUP"));
		
		if (!isset($userName) || !isset($userGroup) || !isset($userId)) {
			
			$this->redirect("Admin/Account/Auth/index");
			exit();
		}
		$this->assign("userId",$userId);
		$this->assign("userName",$userName);
		$this->assign("userGroup",$userGroup);
	}
	
	/*
	 * 管理员菜单生成
	 * 最多两级菜单。无限级可以用递归
	 * */
	private function _accountMenu(){
		
		$userName = session(C("SESSION_KEY.USERNAME"));
		$menuCacheKey = "ADMIN_MENU_".$userName;
		//清除缓存
		S($menuCacheKey,null);
		$userMenus = S($menuCacheKey);
    	if(!$userMenus){
    		
    		$menuCfg = C("MENU");
    		$userGroup = session(C("SESSION_KEY.USERGROUP"));
    		$userGroup = (int)$userGroup;
    		
    		if ($menuCfg && count($menuCfg)>0){
    			foreach($menuCfg as $menu){
    				$menuGroup = $menu["GROUP"];
    				if (in_array($userGroup , $menuGroup)){
    					$menuItem = array(
    							"NAME" => $menu["NAME"],
    							"ICON" => $menu["ICON"],
    							"URL" => ($menu["URL"]=="#"?"#":U($menu["URL"])),
    							"ID" => $menu["ID"],
    							"SUBS" => array()
    					);
    						
    					$menuSubs = $menu["SUBS"];
    						
    					if($menuSubs && count($menuSubs)>0){
    						foreach($menuSubs as $subMenu){
    							$subMenuGroup = $subMenu["GROUP"];
    							if (in_array($userGroup , $subMenuGroup)){
    								$subMenuItem = array(
    										"NAME" => $subMenu["NAME"],
    										"ICON" => $subMenu["ICON"],
    										"URL" => ($subMenu["URL"]=="#"?"#":U($subMenu["URL"])),
    										"ID" => $subMenu["ID"],
    										"SUBS" => array()
    								);
    		
    								$menuItem["SUBS"][] = $subMenuItem;
    							}
    						}
    					}
    						
    					$userMenus[] = $menuItem;
    				}
    			}
    		}
    		
    		S($menuCacheKey, $userMenus, C("MENU_CACHE_TIME"));
    	}
		
		$this->assign("userMenu",$userMenus);
	}
	
	//获取jquery.table传入的参数
	protected function initCondition(){
		$condition["start"]=$_REQUEST["start"];
		$condition["pagesize"]=$_REQUEST["length"];
		$columns=$_REQUEST["columns"];
		$order=$_GET["order"];
		$orderColumnNum=$order[0]["column"];
		$orderSeq = $order[0]["dir"];
		$condition["orderSeq"] = preg_match("/^desc|asc$/",$orderSeq)?$orderSeq:'asc';
		$condition["orderName"] = $columns[$orderColumnNum]["data"];
		$search=$_REQUEST["search"];
		$key=$search["value"];
		$condition["searchvalue"]=$key;
		$searchColumns = array();
		$index=0;
		if($key){
			$key = trim($key);
			$key = str_replace(',', '|', $key);
			$key = str_replace('  ', '|', $key);
			$key = str_replace(' ', '|', $key);
			foreach($columns as $column){
				if($column["searchable"]==1){
					$searchColumns[$index++] = $column["data"];
				}
			}
		}
		$condition["condition"]=$searchColumns;
		return $condition;
	}
}