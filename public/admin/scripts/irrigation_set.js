angular.module('myApp').controller('IrrigationSetCtrl', function($scope, IrrigationSetService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 20;//每页显示条数
    $scope.init_spinner_display = true;
    $scope.list = [];
    $scope.data_loading = true;
    function int_data()
    {
       IrrigationSetService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
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


    $scope.irrigationEditModel = function (irrigation) {
        var modalInstance = $uibModal.open({
            templateUrl: 'irrigationEditModel.html',
            controller: 'IrrigationSetEditCtrl',
            size: "md",
            resolve: {
                irrigation: function() { return angular.copy(irrigation); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (irrigation) {
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



}).service('IrrigationSetService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/setting/irrigation_set';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

   var irrigation_set_add = function (irrigation,singlePic) {
        var url = '/api/setting/irrigation_set_add';
        var data = {irrigation:irrigation};
        if(singlePic != undefined && singlePic.file != undefined){
            data.singlePic = {"data":singlePic.data,"file_name":singlePic.file.name,"file_size":singlePic.file.size,"type":singlePic.file.type};
        }
        return $http.post(url, data);
    };

    var irrigation_set_edit = function (irrigation,singlePic) {
        var url = '/api/setting/irrigation_set_edit';
        var data = {irrigation:irrigation};
        if(singlePic != undefined && singlePic.file != undefined){
            data.singlePic = {"data":singlePic.data,"file_name":singlePic.file.name,"file_size":singlePic.file.size,"type":singlePic.file.type};
        }
        return $http.post(url, data);
    };
    var get_irrigation_set_one = function (irrigation_id) {
        var url = '/api/setting/get_irrigation_set_one';
        var data = {irrigation_id:irrigation_id};
        return $http.post(url, data);
    };
    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        irrigation_set_add: function (irrigation,singlePic) {
            console.log(singlePic);
            return irrigation_set_add(irrigation,singlePic);
        },
        irrigation_set_edit: function (irrigation,singlePic) {
            console.log(singlePic);
            return irrigation_set_edit(irrigation,singlePic);
        },
        get_irrigation_set_one: function (irrigation_id) {
            return get_irrigation_set_one(irrigation_id);
        },
    };
}]);



angular.module('myApp').controller('IrrigationSetEditCtrl', function($scope,irrigation,IrrigationSetService, $uibModalInstance) {
    if(irrigation){
        $scope.irrigation = irrigation;
    }

    $scope.confirm = function()
    {
        $scope.save_spinner_display = true;
        if(!$scope.irrigation.id){
            IrrigationSetService.irrigation_set_add($scope.irrigation,$scope.singlePic).success(function (response) {
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
            IrrigationSetService.irrigation_set_edit($scope.irrigation,$scope.singlePic).success(function (response) {
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


