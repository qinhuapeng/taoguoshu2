angular.module('myApp').controller('PasswordCtrl', function($scope, PasswordService, $location, $routeParams , $uibModal) {
	$scope.admininfo = {};
	$scope.confirm = function()
	{	
		if($scope.admininfo.password1 == undefined){
			return false;	
		}

		if($scope.admininfo.password1 != $scope.admininfo.password2){
			layer.msg("密码不一致！");
			return false;	
		}

	    $scope.save_spinner_display = true;
	    PasswordService.update_password($scope.admininfo).success(function(response){
	        if(response.ret == 999){
	            window.location = "/admin/login.html";
	        }else if(response.ret == 0){
	        	layer.msg(response.msg);
	        }else{
	            layer.msg(response.msg);
	        }
	        $scope.btn_spinner_display = false;
	   });
	}

}).service('PasswordService', ['$http', function ($http) {
    var update_password = function (admininfo) {
        var url = '/api/admin/update_password';
        var data = {admininfo:admininfo};
        return $http.post(url, data);
    };

    return {
        update_password: function (admininfo) {
            return update_password(admininfo);
        }
    
    };
}]);





