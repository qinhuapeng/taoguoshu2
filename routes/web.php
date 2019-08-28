<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//首页统计
Route::any('api/overview', 'HomeController@overview');

//管理员登录
Route::any('api/users/login', 'UsersController@login');
Route::any('api/users/logout', 'UsersController@logout');
Route::any('api/users/check_login', 'UsersController@check_login');

//管理员详情
Route::any('api/admin/admin_list', 'AdminController@admin_list');
Route::any('api/admin/admin_add', 'AdminController@admin_add');
Route::any('api/admin/admin_edit', 'AdminController@admin_edit');
Route::any('api/admin/admin_remove', 'AdminController@admin_remove');
Route::any('api/admin/admin_password', 'AdminController@admin_password');
Route::any('api/admin/admin_role_edit', 'AdminController@admin_role_edit');

Route::any('api/admin/role_list', 'AdminController@role_list');
Route::any('api/admin/role_add', 'AdminController@role_add');
Route::any('api/admin/role_edit', 'AdminController@role_edit');
Route::any('api/admin/role_remove', 'AdminController@role_remove');
Route::any('api/admin/jurisdiction_list', 'AdminController@jurisdiction_list');
Route::any('api/admin/role_select_jurisdiction', 'AdminController@role_select_jurisdiction');
Route::any('api/admin/role_add_jurisdiction', 'AdminController@role_add_jurisdiction');
Route::any('api/admin/update_password', 'AdminController@update_password');
Route::any('api/admin/role_list_get', 'Controller@role_list_get');
Route::any('api/admin/role_jurisdictions_one', 'AdminController@role_jurisdictions_one');



//基本配置
Route::any('api/setting/probability_set', 'SettingController@probability_set');
Route::any('api/setting/probability_set_edit', 'SettingController@probability_set_edit');
Route::any('api/setting/irrigation_set', 'SettingController@irrigation_set');
Route::any('api/setting/get_irrigation_set_one', 'SettingController@get_irrigation_set_one');
Route::any('api/setting/irrigation_set_edit', 'SettingController@irrigation_set_edit');
Route::any('api/setting/irrigation_set_add', 'SettingController@irrigation_set_add');
Route::any('api/setting/rechargeable_vouchers_list', 'SettingController@rechargeable_vouchers_list');
Route::any('api/setting/rechargeable_vouchers_add', 'SettingController@rechargeable_vouchers_add');
Route::any('api/setting/rechargeable_vouchers_edit', 'SettingController@rechargeable_vouchers_edit');
Route::any('api/setting/rechargeable_vouchers_remove', 'SettingController@rechargeable_vouchers_remove');


//我的果树
Route::any('api/tree/tree_catagory', 'TreeController@tree_catagory');
Route::any('api/tree/get_tree_catagory_one', 'TreeController@get_tree_catagory_one');
Route::any('api/tree/tree_catagory_add', 'TreeController@tree_catagory_add');
Route::any('api/tree/tree_catagory_edit', 'TreeController@tree_catagory_edit');
Route::any('api/tree/tree_catagory_remove', 'TreeController@tree_catagory_remove');
Route::any('api/tree/tree_base', 'TreeController@tree_base');
Route::any('api/tree/get_tree_base_one', 'TreeController@get_tree_base_one');
Route::any('api/tree/tree_base_add', 'TreeController@tree_base_add');
Route::any('api/tree/tree_base_edit', 'TreeController@tree_base_edit');
Route::any('api/tree/tree_base_remove', 'TreeController@tree_base_remove');
Route::any('api/tree/tree_base_img_add', 'TreeController@tree_base_img_add');
Route::any('api/tree/tree_base_img_remove', 'TreeController@tree_base_img_remove');
Route::any('api/tree/tree_base_sort', 'TreeController@tree_base_sort');
Route::any('api/tree/tree_cycle', 'TreeController@tree_cycle');
Route::any('api/tree/tree_cycle_add', 'TreeController@tree_cycle_add');
Route::any('api/tree/tree_cycle_edit', 'TreeController@tree_cycle_edit');
Route::any('api/tree/tree_cycle_remove', 'TreeController@tree_cycle_remove');
Route::any('api/tree/tree_list', 'TreeController@tree_list');
Route::any('api/tree/get_tree_lsit_one', 'TreeController@get_tree_lsit_one');
Route::any('api/tree/tree_list_add', 'TreeController@tree_list_add');
Route::any('api/tree/tree_list_edit', 'TreeController@tree_list_edit');
Route::any('api/tree/tree_list_remove', 'TreeController@tree_list_remove');
Route::any('api/tree/tree_scale_list', 'TreeController@tree_scale_list');
Route::any('api/tree/down_csv', 'TreeController@down_csv');
Route::any('api/tree/uploadcsv', 'TreeController@uploadcsv');

//用户列表
Route::any('api/customer/customer_list', 'CustomerController@customer_list');
Route::any('api/customer/customer_is_upload', 'CustomerController@customer_is_upload');
Route::any('api/customer/irrigation_nfo', 'CustomerController@irrigation_nfo');

//信息发布
Route::any('api/information/information_set', 'InformationController@information_set');
Route::any('api/information/information_set_edit', 'InformationController@information_set_edit');
Route::any('api/information/information_requirement_list', 'InformationController@information_requirement_list');
Route::any('api/information/information_requirement_shenhe', 'InformationController@information_requirement_shenhe');
Route::any('api/information/information_requirement_deliverGoods', 'InformationController@information_requirement_deliverGoods');
Route::any('api/information/information_admin', 'InformationController@information_admin');



//公用接口
Route::any('api/common/tree_catagory_list', 'Controller@tree_catagory_list');
Route::any('api/common/tree_base_list', 'Controller@tree_base_list');





/*前端*/
//微信
Route::any('api/wx/get_WX_config', 'WxController@get_WX_config');
