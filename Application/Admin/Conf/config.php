<?php
return array(

		//二级目录控制器层
		'CONTROLLER_LEVEL' => 2,
		
		//加载菜单配置
		'LOAD_EXT_CONFIG' => 'menu,email',
		
		//管理后台Logo文字
		'LOGO_TEXT'=> 'PIEPCMS',
		
		//图片上传保存路径
		'IMAGE_UPLOAD_PATH'=> PUBLIC_PATH."Upload/Image/",
		
		//图片上传扩展名
		'IMAGE_UPLOAD_EXTS' => array('jpg', 'gif', 'png', 'jpeg','bmp'),
		
		//图片上传大小限制
		'IMAGE_UPLOAD_SIZE' => 1024000,
		
		//图片上传文件命名规则 ： com_create_guid, time, 自定义函数
		'IMAGE_UPLOAD_NAMETYPE'=> 'time',
		
		//图片首页显示图片数据
		'IMAGE_SHOW_COUNT'=>50,
		
		//我的待办状态值
		'TODO_STATUS'=>array(
			"NEW" => 1,
			"DONE" => 2,
			"DELETED" => 3
		),
		//session键名
		'SESSION_KEY'=>array(
			"USERNAME"=>"SESSION_USERNAME",
			"USERGROUP"=>"SESSION_USERGROUP",
			"USERID"=>"SESSION_USERID"
		),
		//cookie键名
		'COOKIE_KEY'=>array(
			"LOGIN_REMEMBER"=>"COOKIE_LOGIN_REMEMBER"
		)
);