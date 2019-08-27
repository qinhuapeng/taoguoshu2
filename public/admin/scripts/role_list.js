angular.module('myApp').controller('RoleCtrl', function($scope, RoleService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 100;//每页显示条数
    $scope.jurisdictionList = [];
    function int_data()
    {
       RoleService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.list = response.data;
                $scope.data_loading = false;
            }else{
                layer.msg(response.msg);
            }
       });
    }
    int_data();

    //获取权限列表
    $scope.jurisdiction_list_get = function()
    {
        RoleService.jurisdiction_list_get().success(function (response) {
            $scope.jurisdictionList = response;
        });
    }
    $scope.jurisdiction_list_get();

    $scope.roleEditModel = function(roleinfo)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'roleEditModel.html',
            controller: 'roleEditCtrl',
            size: "lg",
            resolve: {
                roleinfo: function() { return angular.copy(roleinfo); },
                jurisdictionList: function() { return angular.copy($scope.jurisdictionList); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (roleinfo) {
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



    $scope.roleRemoveModel = function(row)
    {
        layer.confirm('您确定要删除角色吗？', {
          btn: ['确定','取消'] //按钮
        }, function(){
            RoleService.role_remove(row).success(function (response) {
                angular.forEach($scope.list, function(value,key,array){
                    if(value.id == response.data.id) {
                        array.splice(key, 1);
                    }
                });
                layer.msg('删除成功!');
            });
        }, function(){

        });
    }


}).service('RoleService', ['$http', function ($http) {

    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/admin/role_list';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

    var role_add = function (roleinfo,checkbox_data) {
        var url = '/api/admin/role_add';
        var data = {roleinfo:roleinfo,checkbox_data:checkbox_data};
        return $http.post(url, data);
    };

    var role_edit = function (roleinfo,checkbox_data) {
        var url = '/api/admin/role_edit';
        var data = {roleinfo:roleinfo,checkbox_data:checkbox_data};
        return $http.post(url, data);
    };

    var role_remove = function (roleinfo) {
        var url = '/api/admin/role_remove';
        var data = {roleinfo:roleinfo};
        return $http.post(url, data);
    };
    var jurisdiction_list_get = function () {
        var url = '/api/admin/jurisdiction_list';
        var data = {};
        return $http.post(url, data);
    };

    var role_jurisdictions_one = function (roleinfo) {
        var url = '/api/admin/role_jurisdictions_one';
        var data = {roleinfo:roleinfo};
        return $http.post(url, data);
    };

    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        role_add: function (roleinfo,checkbox_data) {
            return role_add(roleinfo,checkbox_data);
        },
        role_edit: function (roleinfo,checkbox_data) {
            return role_edit(roleinfo,checkbox_data);
        },
        role_remove: function (roleinfo) {
            return role_remove(roleinfo);
        },
        jurisdiction_list_get: function () {
            return jurisdiction_list_get();
        },
        role_jurisdictions_one:function(roleinfo){
            return role_jurisdictions_one(roleinfo);
        }
    };
}]);



angular.module('myApp').controller('roleEditCtrl', function ($scope, roleinfo,jurisdictionList,RoleService, $uibModalInstance) {
    $scope.jurisdictionData = [];
    $scope.jurisdictions = [];
    $scope.checkbox_data = [];
    $scope.my_checked = [];
    $scope.checkbox_flag = false;
    if(jurisdictionList.length == 0){
        layer.msg("加载失败，请重新打开");
    }

    if(jurisdictionList){
        $scope.jurisdictionData = jurisdictionList.data;
        $scope.jurisdictions = jurisdictionList.jurisdictions;
    }

    if(roleinfo){
        $scope.roleinfo = roleinfo;
        RoleService.role_jurisdictions_one(roleinfo).success(function (response) {
            $scope.my_checked = response.data;
            angular.forEach(response.data,function(value,key,array){
                angular.forEach($scope.jurisdictionData,function(val,k,arr){
                    if(val[0] !=undefined){
                        if(val[0].id == value.jurisdiction_id){
                            console.log();
                            $scope['checkbox_flag_'+val[0].id] = true;
                        }
                    }
                    if(val[1] !=undefined){
                        if(val[1].id == value.jurisdiction_id){
                            $scope['checkbox_flag_'+val[1].id] = true;
                        }
                    }
                    
                });
            });
        });
    }


    $scope.jurisdiction_checkbox = function(checkbox_flag){
        //$scope.checkbox_flag = !checkbox_flag;
        angular.forEach($scope.jurisdictions,function(value,key,array){
            $scope['checkbox_flag_'+value.id] = $scope.checkbox_flag;
        });
    }

    $scope.confirm = function()
    {   
        $(".checkbox_data tr input[type=checkbox]:checked").each(function(key,value){
            $scope.checkbox_data.push(parseInt($(this).val()));
        });
        $scope.save_spinner_display = true;
        if(!$scope.roleinfo.id){
            RoleService.role_add($scope.roleinfo,$scope.checkbox_data).success(function (response) {
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
            RoleService.role_edit($scope.roleinfo,$scope.checkbox_data).success(function (response) {
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