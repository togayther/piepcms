<?php
return array (
		"MENU" => array (
				array (
						"NAME" => "新年红包",
						"ICON" => "fa-paper-plane",
						"URL" => "#",
						"ID" => "",
						"GROUP" => array (1),
						"SUBS" => array(
								array(
										"NAME" => "用户列表",
										"ICON" => "fa-user",
										"URL" => "admin/redbag/user/index",
										"ID" => "",
										"GROUP" => array (1)
								),
								array(
										"NAME" => "新年红包",
										"ICON" => "fa-plane",
										"URL" => "admin/redbag/user/userbag",
										"ID" => "",
										"GROUP" => array (1)
								),
								array(
										"NAME" => "助力红包",
										"ICON" => "fa-ticket",
										"URL" => "admin/redbag/user/helpbag",
										"ID" => "",
										"GROUP" => array (1)
								)
						) 
				),
				array (
						"NAME" => "客户端",
						"ICON" => "fa-plug",
						"URL" => "#",
						"ID" => "",
						"GROUP" => array (1,2),
						"SUBS" => array(
								array(
										"NAME" => "常见问题",
										"ICON" => "fa-question-circle",
										"URL" => "admin/mobile/question/index",
										"ID" => "",
										"GROUP" => array (1,2)
								)
						) 
				),
				array (
						"NAME" => "数据分析平台",
						"ICON" => "fa-bar-chart-o",
						"URL" => "#",
						"ID" => "",
						"GROUP" => array (1,2),
						"SUBS" => array(
								array(
										"NAME"=>"数据汇总",
										"ICON"=>"fa-database",
										"URL"=>"admin/analysis/data/index",
										"ID"=>"",
										"GROUP"=>array(1,2)
								),
								array(
										"NAME" => "策划人管理",
										"ICON" => "fa-user",
										"URL" => "admin/analysis/user/index",
										"ID" => "",
										"GROUP" => array (1,2)
								),
								array(
										"NAME" => "渠道分类管理",
										"ICON" => "fa-building-o",
										"URL" => "admin/analysis/channel/type",
										"ID" => "",
										"GROUP" => array (1,2)
								),
								array(
										"NAME" => "渠道管理",
										"ICON" => "fa-building",
										"URL" => "admin/analysis/channel/index",
										"ID" => "",
										"GROUP" => array (1,2)
								),
								array(
										"NAME"=>"数据文件导入",
										"ICON"=>"fa-upload",
										"URL"=>"admin/analysis/data/import",
										"ID"=>"",
										"GROUP"=>array(1,2)
								),
								array(
										"NAME"=>"业绩数据",
										"ICON"=>"fa-database",
										"URL"=>"admin/analysis/performance/list",
										"ID"=>"",
										"GROUP"=>array(1,2)
								),
								array(
										"NAME"=>"业绩详细数据分析",
										"ICON"=>"fa-line-chart",
										"URL"=>"admin/analysis/performance/detail",
										"ID"=>"",
										"GROUP"=>array(1,2)
								),
								array(
										"NAME"=>"渠道数据",
										"ICON"=>"fa-database",
										"URL"=>"admin/analysis/channel/list",
										"ID"=>"",
										"GROUP"=>array(1,2)
								),
								array(
										"NAME"=>"渠道详细数据分析",
										"ICON"=>"fa-line-chart",
										"URL"=>"admin/analysis/channel/detail",
										"ID"=>"",
										"GROUP"=>array(1,2)
								)
						)
				),
				array (
						"NAME" => "自助服务",
						"ICON" => "fa-shopping-cart",
						"URL" => "#",
						"ID" => "",
						"GROUP" => array (1,2),
						"SUBS" => array(
								array(
										"NAME" => "图片服务",
										"ICON" => "fa-picture-o",
										"URL" => "admin/tools/image/index",
										"ID" => "",
										"GROUP" => array (1,2)
								),
								array(
										"NAME" => "我的待办",
										"ICON" => "fa-tags",
										"URL" => "admin/tools/todo/index",
										"ID" => "",
										"GROUP" => array (1,3)
								)
						)
				)
				
		) 
);