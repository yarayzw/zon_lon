<?php
namespace Admini\Model;

use Think\Model;

class ProductsModel extends Model {
	protected $tableName        =   'products';
	protected $patchValidate   =   true;
	protected $_validate            =   array(
			'title'=>array('title', 'require', '商品名称不能为空！'),
			'marketprice'=>array('marketprice', 'require', '市场价格不能为空！'),
			'terraceprice'=>array('terraceprice', 'require', '平台价格不能为空！'),
			'listorder'=>array('listorder', 'number', '排序格式不正确！'),
			'marketprice'=>array('marketprice', 'currency', '市场价格，格式不正确！'),
			'terraceprice'=>array('terraceprice', 'currency', '平台价格，格式不正确！'),
	);  // 自动验证定义
	
}
