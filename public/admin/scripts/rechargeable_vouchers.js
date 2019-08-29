angular.module('myApp').controller('RechargeableVouchersCtrl', function($scope, RechargeableVouchersService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }
    $scope.is_delete_map = ['','正常','下线'];
    $scope.is_delete_list = [{'id':1,'name':'正常'},{'id':2,'name':'下线'}];

    function int_data()
    {
       RechargeableVouchersService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.list = response.data;
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


    $scope.rechargeableVouchersEditModel=function(rechargeableinfo)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'rechargeableVouchersEditModel.html',
            controller: 'rechargeableVouchersEditModelCtrl',
            size: "md",
            resolve: {
                rechargeableinfo: function() { return angular.copy(rechargeableinfo); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (rechargeableinfo) {
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


    $scope.status_change = function(row)
    {
        var msg = "您确定要下线该储值卷吗？";
        if(row.is_delete == 2){
            msg = "您确定要上线该储值卷吗？";
        }

        layer.confirm(msg, {
          btn: ['确定','取消'] //按钮
        }, function(){
            RechargeableVouchersService.rechargeable_vouchers_remove(row).success(function (response) {
                angular.forEach($scope.list, function(value,key,array){
                    if(value.id == response.data.id) {
                        if($scope.search.is_delete){
                            array.splice(key, 1);
                        }else{
                            $scope.list[key] = response.data;
                        }
                    }
                });
                layer.msg(response.msg);
            });
        }, function(){

        });
    }



    
}).service('RechargeableVouchersService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/setting/rechargeable_vouchers_list';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };
    
    var rechargeable_vouchers_add = function(rechargeableinfo)
    {
        var url = '/api/setting/rechargeable_vouchers_add';
        var data = {rechargeableinfo:rechargeableinfo};
        return $http.post(url, data);
    }
    var rechargeable_vouchers_edit = function(rechargeableinfo)
    {
        var url = '/api/setting/rechargeable_vouchers_edit';
        var data = {rechargeableinfo:rechargeableinfo};
        return $http.post(url, data);
    }
    var rechargeable_vouchers_remove = function(rechargeableinfo)
    {
        var url = '/api/setting/rechargeable_vouchers_remove';
        var data = {rechargeableinfo:rechargeableinfo};
        return $http.post(url, data);
    }

    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        rechargeable_vouchers_add: function (rechargeableinfo) {
            return rechargeable_vouchers_add(rechargeableinfo);
        },
        rechargeable_vouchers_edit: function (rechargeableinfo) {
            return rechargeable_vouchers_edit(rechargeableinfo);
        },
        rechargeable_vouchers_remove: function (rechargeableinfo) {
            return rechargeable_vouchers_remove(rechargeableinfo);
        },

    };
}]);


angular.module('myApp').controller('rechargeableVouchersEditModelCtrl', function ($scope, rechargeableinfo,RechargeableVouchersService, $uibModalInstance) {
    if(rechargeableinfo){
        $scope.rechargeableinfo = rechargeableinfo;
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        if(!$scope.rechargeableinfo.id){
            RechargeableVouchersService.rechargeable_vouchers_add($scope.rechargeableinfo).success(function (response) {
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
            RechargeableVouchersService.rechargeable_vouchers_edit($scope.rechargeableinfo).success(function (response) {
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