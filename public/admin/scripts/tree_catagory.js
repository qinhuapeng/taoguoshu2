angular.module('myApp').controller('TreeCatagoryCtrl', function($scope, TreeCatagoryService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 20;//每页显示条数
    $scope.roleList = [];//角色列表初始化
    $scope.search = {};
    $scope.catagory_status_map = [{'id':1,'name':"正常"},{'id':2,'name':"下线"}];
    $scope.is_delete_map = ['','正常','已下线'];
    function int_data()
    {
       TreeCatagoryService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
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


    $scope.treeCatagoryEditModel = function(catagory)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'treeCatagoryEditModel.html',
            controller: 'TreeCatagoryEditCtrl',
            size: "lg",
            resolve: {
                catagory: function() { return angular.copy(catagory); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (catagory) {
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

    $scope.tree_catagory_status_change = function(row)
    {
        var msg = "您确定要下线该果树品种吗？";
        if(row.is_delete == 2){
            msg = "您确定要上线该果树品种吗？";
        }

        layer.confirm(msg, {
          btn: ['确定','取消'] //按钮
        }, function(){
            TreeCatagoryService.tree_catagory_remove(row).success(function (response) {
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



    $scope.catagory_summary = function(content){
        layer.open({
            type: 1,
            skin: 'layui-layer-rim', //加上边框
            area: ['400px', '600px'], //宽高
            content: '<span>'+content+'</span>'
        });

    }


}).service('TreeCatagoryService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/tree/tree_catagory';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

    var get_tree_catagory_one = function (catagory_id) {
        var url = '/api/tree/get_tree_catagory_one';
        var data = {catagory_id:catagory_id};
        return $http.post(url, data);
    };
    var tree_catagory_add = function (catagory,singlePic) {
        var url = '/api/tree/tree_catagory_add';
        var data = {catagory:catagory};
        if(singlePic != undefined && singlePic.file != undefined){
            data.singlePic = {"data":singlePic.data,"file_name":singlePic.file.name,"file_size":singlePic.file.size,"type":singlePic.file.type};
        }
        return $http.post(url, data);
    };

    var tree_catagory_edit = function (catagory,singlePic) {
        var url = '/api/tree/tree_catagory_edit';
        var data = {catagory:catagory};
        if(singlePic != undefined && singlePic.file != undefined){
            data.singlePic = {"data":singlePic.data,"file_name":singlePic.file.name,"file_size":singlePic.file.size,"type":singlePic.file.type};
        }
        return $http.post(url, data);
    };

    var tree_catagory_remove = function(catagory)
    {
        var url = '/api/tree/tree_catagory_remove';
        var data = {catagory:catagory};
        return $http.post(url, data);
    }


    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        get_tree_catagory_one: function (catagory_id) {
            return get_tree_catagory_one(catagory_id);
        },
        tree_catagory_add: function (catagory,singlePic) {
            return tree_catagory_add(catagory,singlePic);
        },
        tree_catagory_edit: function (catagory,singlePic) {
            return tree_catagory_edit(catagory,singlePic);
        },
        tree_catagory_remove: function (catagory) {
            return tree_catagory_remove(catagory);
        },
    };
}]);


angular.module('myApp').controller('TreeCatagoryEditCtrl', function ($scope, catagory,TreeCatagoryService, $uibModalInstance) {
    if(catagory){
        $scope.catagory = catagory;
    }

    

    $scope.confirm = function()
    {   
        //$scope.save_spinner_display = true;
        if(!$scope.catagory.id){
            TreeCatagoryService.tree_catagory_add($scope.catagory,$scope.singlePic).success(function (response) {
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
            TreeCatagoryService.tree_catagory_edit($scope.catagory,$scope.singlePic).success(function (response) {
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



