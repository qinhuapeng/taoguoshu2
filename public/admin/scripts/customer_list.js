angular.module('myApp').controller('CustomerListCtrl', function($scope, CustomerListService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    // $scope.search.time = new Date().getFullYear()+"-" + (new Date().getMonth()+1) + "-" + new Date().getDate();
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }

    $scope.is_upload_list = [{'id':1,'name':'允许上传'},{'id':2,'name':'禁止上传'}];
    $scope.is_upload_map =['','允许上传','禁止上传'];
    function int_data()
    {
       CustomerListService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.list = response.data.data;
                $scope.currentPage = response.data.current_page;
                $scope.itemsPerPage = response.data.per_page;
                $scope.totalItems = response.data.total;
                $scope.numPages = response.data.last_page;
                $scope.init_spinner_display = false;
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


    $scope.isUploadModal = function(uploadinfo){
        var modalInstance = $uibModal.open({
            templateUrl: 'isUploadModal.html',
            controller: 'isUploadModalCtrl',
            size: "md",
            resolve: {
                uploadinfo: function() { return angular.copy(uploadinfo); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (uploadinfo) {
                angular.forEach($scope.list, function(value, key,array) {
                    if(response.id == value.id){
                        $scope.list[key] = response;
                    }
                });
            }
        });
    }

    $scope.irrigationModal =function(irrigation){
        var modalInstance = $uibModal.open({
            templateUrl: 'irrigationModal.html',
            controller: 'irrigationModalCtrl',
            size: "lg",
            resolve: {
                irrigation: function() { return angular.copy(irrigation); },
            }
        });
        
        modalInstance.result.then(function (response) {

        });
    }



}).service('CustomerListService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/customer/customer_list';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };
    var customer_is_upload = function (uploadinfo) {
        var url = '/api/customer/customer_is_upload';
        var data = {uploadinfo:uploadinfo};
        return $http.post(url, data);
    };

    var irrigation_nfo = function (irrigation) {
        var url = '/api/customer/irrigation_nfo';
        var data = {irrigation:irrigation};
        return $http.post(url, data);
    };
    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        customer_is_upload: function (uploadinfo) {
            return customer_is_upload(uploadinfo);
        }, 
        irrigation_nfo: function (irrigation) {
            return irrigation_nfo(irrigation);
        },        
    };
}]);



angular.module('myApp').controller('irrigationModalCtrl', function ($scope, irrigation,CustomerListService, $uibModalInstance) {
    $scope.int_loading = true;
    if(irrigation){
        $scope.irrigation = irrigation;
    }

    $scope.irrigationinfo = [];
    $scope.list = [];
    $scope.irrigation_list = [];
    $scope.irrigation_list_child = [];
    CustomerListService.irrigation_nfo($scope.irrigation).success(function (response) {
        if(response.ret == 0) {
            $scope.int_loading = false;
            $scope.list = response.data;
            $scope.active_flag = 0;
            if($scope.list.length > 0){
                $scope.irrigation_list = $scope.list[0].irrigation_list;
                $scope.irrigation_list_child = $scope.list[0].irrigation_list;
            }
            
        } else {
            layer.msg(response.msg);
        }
    });
    //cancel事件
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };


    $scope.get_tree_content = function(row){
        angular.forEach($scope.list,function(value, key,array){
            if(row.tree_id == value.tree_id){
                $scope.active_flag = key;
                $scope.irrigation_list = $scope.list[key].irrigation_list;
            }
        });
    }
    
})


angular.module('myApp').controller('isUploadModalCtrl', function ($scope, uploadinfo,CustomerListService, $uibModalInstance) {
    if(uploadinfo){
        $scope.uploadinfo = uploadinfo;
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        CustomerListService.customer_is_upload($scope.uploadinfo).success(function (response) {
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
