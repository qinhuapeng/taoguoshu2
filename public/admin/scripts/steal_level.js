angular.module('myApp').controller('StealLevelCtrl', function($scope, StealLevelSetService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 20;//每页显示条数
    $scope.init_spinner_display = true;
    $scope.list = [];
    $scope.data_loading = true;
    $scope.is_delete_list = [{'id':1,'name':"正常"},{'id':2,'name':"删除"}];
    $scope.is_delete_map = ['','正常','删除'];
    function int_data()
    {
       StealLevelSetService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.list = response.data;
            }else{
                $.modal.alert(response.msg);
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


    $scope.stealLevelEditModel = function (levelinfo) {
        var modalInstance = $uibModal.open({
            templateUrl: 'stealLevelEditModel.html',
            controller: 'stealLevelEditModelCtrl',
            size: "md",
            resolve: {
                levelinfo: function() { return angular.copy(levelinfo); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (levelinfo) {
                angular.forEach($scope.list, function(value, key) {
                    if(response.id == value.id){
                        $scope.list[key] = response;
                    }
                });
            }else{
                $scope.list.unshift(response);
            }
        });
    }

    $scope.steal_level_status_change = function(row)
    {
        var msg = "您确定要删除该等级吗？";
        if(row.is_delete == 2){
            msg = "您确定要恢复该等级吗？";
        }

        layer.confirm(msg, {
          btn: ['确定','取消'] //按钮
        }, function(){
            StealLevelSetService.steal_level_remove(row).success(function (response) {
                angular.forEach($scope.list, function(value,key,array){
                    if(value.id == response.data.id) {
                        $scope.list[key] = response.data;
                    }
                });
                layer.msg(response.msg);
            });
        }, function(){

        });
    }



}).service('StealLevelSetService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/setting/steal_level';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

   var steal_level_add = function (levelinfo) {
        var url = '/api/setting/steal_level_add';
        var data = {levelinfo:levelinfo};
        return $http.post(url, data);
    };

    var steal_level_edit = function (levelinfo,singlePic) {
        var url = '/api/setting/steal_level_edit';
        var data = {levelinfo:levelinfo};
        return $http.post(url, data);
    };
    var steal_level_remove = function (levelinfo,singlePic) {
        var url = '/api/setting/steal_level_remove';
        var data = {levelinfo:levelinfo};
        return $http.post(url, data);
    };

    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        steal_level_add: function (levelinfo) {
            return steal_level_add(levelinfo);
        },
        steal_level_edit: function (levelinfo) {
            return steal_level_edit(levelinfo);
        },
        steal_level_remove: function (levelinfo) {
            return steal_level_remove(levelinfo);
        },

    };
}]);



angular.module('myApp').controller('stealLevelEditModelCtrl', function($scope,levelinfo,StealLevelSetService, $uibModalInstance) {
    if(levelinfo){
        $scope.levelinfo = levelinfo;
    }

    $scope.confirm = function()
    {
        $scope.save_spinner_display = true;
        if(!$scope.levelinfo.id){
            StealLevelSetService.steal_level_add($scope.levelinfo).success(function (response) {
                if(response.ret == 0) {
                    var res = {};
                    res = response.data;
                    $uibModalInstance.close(res);
                    layer.msg("修改成功");
                } else {
                    layer.msg(response.msg);
                }
                $scope.save_spinner_display = false;
            });
        }else{
            StealLevelSetService.steal_level_edit($scope.levelinfo).success(function (response) {
                if(response.ret == 0) {
                    var res = {};
                    res = response.data;
                    $uibModalInstance.close(res);
                    layer.msg("修改成功");
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
});


