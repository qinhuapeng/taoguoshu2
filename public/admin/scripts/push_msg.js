angular.module('myApp').controller('PushmsgCtrl', function($scope, PushmsgService, $location, $routeParams , $uibModal,$cookieStore) {
	$scope.userid = $cookieStore.get('userid');
	$scope.usersinfo = {};
	$scope.users_list = function()
    {
        PushmsgService.users_list().success(function (response) {
            $scope.users_list = response.data;
        });
    }
    $scope.users_list();


	/*websocket notify start*/

	var socket = io('http://192.168.242.128:2120');
    // uid可以是自己网站的用户id，以便针对uid推送以及统计在线人数

    uid = $scope.userid;
    // socket连接后以uid登录
    socket.on('connect', function(){
    	socket.emit('login', uid);
    });
    // 后端推送来消息时
    socket.on('new_msg', function(msg){
        console.log("收到消息："+msg);
        notyfy({
            text: msg,
            type: 'success',
            dismissQueue: true,
            layout: 'bottomRight'
        });
        return false;
    });

    // 后端推送来在线数据时
    socket.on('update_online_count', function(online_stat){
        console.log(online_stat);
    });


	
	/*websocket notify end*/

	var notes = [];
	notes['success'] = "asdasdxcxvxcvxcccccccccccccvxcvsd在沙发上的发送到发送到防守打法水电费水电费说的发送到";

    $('.btn.runner').click(function () {
        var self = $(this);
        notyfy({
            text: notes[self.data('type')],
            type: self.data('type'),
            dismissQueue: true,
            layout: self.data('layout')
        });
        return false;
    });


    $scope.push_msg = function()
    {
    	//$scope.usersinfo = {};
    	if($scope.usersinfo.content == undefined || $scope.usersinfo.content == ""){
    		alert("推送消息不能为空");
    		return false;
    	}

    	PushmsgService.push_msg($scope.usersinfo).success(function(response){
	        if(response.ret == 999){
	            window.location = "/admin/login.html";
	        }else if(response.ret == 0){
	            //alert(response.msg);
	        }else{
	            //alert(response.msg);
	        }
	        $scope.btn_spinner_display = false;
	   });
    }


}).service('PushmsgService', ['$http', function ($http) {
	var users_list = function () {
        var url = '/api/websocket/users_list';
        var data = {};
        return $http.post(url, data);
    };

    var push_msg = function (usersinfo) {
        var url = '/api/websocket/push_msg';
        var data = {usersinfo:usersinfo};
        return $http.post(url, data);
    };

    return {
        users_list: function () {
            return users_list();
        },
        push_msg: function (usersinfo) {
            return push_msg(usersinfo);
        },
    };
}]);





