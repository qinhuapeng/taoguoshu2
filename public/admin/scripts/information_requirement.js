angular.module('myApp').controller('InformationRequirementCtrl', function($scope, InformationRequirementService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }
    $scope.shenhe_map = ['全部','审核','不需审核','审核通过','未通过'];
    $scope.shenhe_list = [{'id':1,'name':'审核'},{'id':2,'name':'不需审核'},{'id':3,'name':'审核通过'},{'id':4,'name':'未通过'}];
    
    $scope.type_list = [{'id':1,'name':'互换需求'},{'id':2,'name':'委托代售'}];
    $scope.type_map = ['全部','互换需求','委托代售'];

    $scope.transaction_status_list = [{'id':1,'name':'未发货'},{'id':2,'name':'已经发货'}];
    $scope.transaction_status_map = ['全部','未发货','已经发货'];

    function int_data()
    {
       InformationRequirementService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
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


    $scope.shenheModal = function(shenhe){
        var modalInstance = $uibModal.open({
            templateUrl: 'shenheModal.html',
            controller: 'shenheModalCtrl',
            size: "md",
            resolve: {
                shenhe: function() { return angular.copy(shenhe); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (shenhe) {
                angular.forEach($scope.list, function(value, key) {
                    if(response.id == value.id){
                        $scope.list[key] = response;
                    }
                });
            }
        });
    }

    $scope.fahuoModal = function(fahuo){
        var modalInstance = $uibModal.open({
            templateUrl: 'fahuoModal.html',
            controller: 'fahuoModalCtrl',
            size: "md",
            resolve: {
                fahuo: function() { return angular.copy(fahuo); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (fahuo) {
                angular.forEach($scope.list, function(value, key) {
                    if(response.id == value.id){
                        $scope.list[key] = response;
                    }
                });
            }
        });
    }


    
}).service('InformationRequirementService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/information/information_requirement_list';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };
    var information_requirement_shenhe = function (shenhe) {
        var url = '/api/information/information_requirement_shenhe';
        var data = {shenhe:shenhe};
        return $http.post(url, data);
    };
    var information_requirement_deliverGoods = function (fahuo) {
        var url = '/api/information/information_requirement_deliverGoods';
        var data = {fahuo:fahuo};
        return $http.post(url, data);
    };

    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        information_requirement_shenhe: function (shenhe) {
            return information_requirement_shenhe(shenhe);
        },
        information_requirement_deliverGoods: function (fahuo) {
            return information_requirement_deliverGoods(fahuo);
        },

    };
}]);


angular.module('myApp').controller('fahuoModalCtrl', function ($scope, fahuo,InformationRequirementService, $uibModalInstance) {
    if(fahuo){
        $scope.fahuo = fahuo;
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        InformationRequirementService.information_requirement_deliverGoods($scope.fahuo).success(function (response) {
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
    //cancel事件
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
    
})


angular.module('myApp').controller('shenheModalCtrl', function ($scope, shenhe,InformationRequirementService, $uibModalInstance) {
    if(shenhe){
        $scope.shenhe = shenhe;
        if($scope.shenhe.is_shenhe == 1 || $scope.shenhe.is_shenhe == 2){
            $scope.shenhe.is_shenhe = 4;
        }
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        InformationRequirementService.information_requirement_shenhe($scope.shenhe).success(function (response) {
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
    //cancel事件
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
    
})






