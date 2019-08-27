angular.module('myApp').controller('UsersCtrl', function($scope, UsersService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 20;//每页显示条数
    $scope.roleList = [];//角色列表初始化
    $scope.user_id = $routeParams.user_id;
    function int_data()
    {
       UsersService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.list = response.data.data;
                $scope.currentPage = response.data.current_page;
                $scope.itemsPerPage = response.data.per_page;
                $scope.totalItems = response.data.total;
                $scope.numPages = response.data.last_page;
            }else{
                layer.msg(response.msg);
            }
       });
    }
    int_data();
    $scope.pageChanged = function() 
    {
        int_data();
    };

    $scope.query_click = function()
    {   
        int_data();
    };

    //获取角色列表
    $scope.role_list_get = function()
    {
        UsersService.role_list_get().success(function (response) {
            $scope.roleList = response;
        });
    }
    $scope.role_list_get();



    $scope.adminEditModel = function (admininfo) {
        var modalInstance = $uibModal.open({
            templateUrl: 'adminEditModel.html',
            controller: 'adminEditCtrl',
            size: "md",
            resolve: {
                admininfo: function() { return angular.copy(admininfo); },
                roleList: function() { return angular.copy($scope.roleList); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (admininfo) {
                angular.forEach($scope.list, function(value, key) {
                    if(response.id == value.id){
                        $scope.list[key] = response;
                    }
                });
            } else {
                $scope.list.unshift(response);
            }
        });
    }


    
    $scope.adminRemoveModel = function(row)
    {
        layer.confirm('您确定要删除该账号？', {
          btn: ['确定','取消'] //按钮
        }, function(){
          UsersService.admin_remove(row).success(function (response) {
                angular.forEach($scope.list, function(value,key,array){
                    if(value.id == response.data.id) {
                        array.splice(key, 1);
                    }
                });
                layer.msg('删除成功');
            });
        }, function(){

        });
    }





    $scope.adminPasswordModel = function(row)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'adminPasswordModel.html',
            controller: 'adminPasswordCtrl',
            size: "md",
            resolve: {
                admininfo: function() { return angular.copy(row); },
            }
        });
        
        modalInstance.result.then(function (response) {
        }); 
    }


}).service('UsersService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/admin/admin_list';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

   var admin_add = function (admininfo) {
        var url = '/api/admin/admin_add';
        var data = {admininfo:admininfo};
        return $http.post(url, data);
    };

    var admin_edit = function (admininfo) {
        var url = '/api/admin/admin_edit';
        var data = {admininfo:admininfo};
        return $http.post(url, data);
    };

    var admin_remove = function(admininfo)
    {
        var url = '/api/admin/admin_remove';
        var data = {admininfo:admininfo};
        return $http.post(url, data);
    }

    var admin_password = function(admin)
    {
        var url = '/api/admin/admin_password';
        var data = {admin:admin};
        return $http.post(url, data);
    }

    var role_list_get = function()
    {
        var url = '/api/admin/role_list_get';
        var data = {};
        return $http.post(url, data);
    }


    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        admin_add: function (admininfo) {
            return admin_add(admininfo);
        },
        admin_edit: function (admininfo) {
            return admin_edit(admininfo);
        },
        admin_remove: function (admininfo) {
            return admin_remove(admininfo);
        },
        admin_password: function (admininfo,users) {
            return admin_password(admininfo,users);
        },
        role_list_get: function () {
            return role_list_get();
        },
 
        
    };
}]);



angular.module('myApp').controller('adminEditCtrl', function ($scope, admininfo, roleList,UsersService, $uibModalInstance) {
    if(admininfo){
        $scope.admininfo = admininfo;
    }
    if(roleList){
        $scope.roleList = roleList;
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        if(!$scope.admininfo.id){
            UsersService.admin_add($scope.admininfo).success(function (response) {
                if(response.ret == 0) {
                    var res = {};
                    res = response.data;
                    $uibModalInstance.close(res);
                } else {
                    layer.msg(response.msg);
                }
                $scope.save_spinner_display = false;
            });
        }else{
            UsersService.admin_edit($scope.admininfo).success(function (response) {
                if(response.ret == 0) {
                    var res = {};
                    res = response.data;
                    $uibModalInstance.close(res);
                } else {
                    layer.msg(response.msg);
                }
                $scope.save_spinner_display = false;
            });
        }
    }
    //cancel事件
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
    
})






angular.module('myApp').controller('adminPasswordCtrl', function ($scope, admininfo,UsersService, $uibModalInstance) {
    if(admininfo){
        $scope.admininfo = admininfo;
    }

    $scope.confirm = function()
    {   
        if($scope.admininfo.password1 != $scope.admininfo.password2){
            layer.msg('密码输入不一致');
            return false;
        }
        $scope.save_spinner_display = true;
        UsersService.admin_password($scope.admininfo).success(function (response) {
            if(response.ret == 0) {
                $uibModalInstance.close();
                layer.msg("密码修改成功");
            } else {
                layer.msg(response.msg);
            }
            $scope.btn_spinner_display = false;
        });
    }


    //cancel事件
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
    
})

