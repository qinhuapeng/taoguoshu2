
angular.module('myApp').controller('TreeListCtrl', function($scope, TreeListService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    // $scope.search.time = new Date().getFullYear()+"-" + (new Date().getMonth()+1) + "-" + new Date().getDate();
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }

    $scope.status_list = [{'id':1,'name':"可偷取"},{'id':2,'name':"已被偷取"}];
    $scope.is_delete_list = [{'id':1,'name':"在线"},{'id':2,'name':"已经下线"}];
    $scope.is_delete_map = ['','在线','已经下线'];
    $scope.status_map = ['','可偷取','已被偷取'];
    function int_data()
    {
       TreeListService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
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


    //果树种类列表
    $scope.tree_catagory_list = [];
    function tree_catagory_list()
    {
        TreeListService.tree_catagory_list().success(function (response) {
            $scope.tree_catagory_list = response;
        });
    }
    tree_catagory_list();

    //果树基地列表
    $scope.tree_base_list = [];
    function tree_base_list()
    {
        TreeListService.tree_base_list().success(function (response) {
            $scope.tree_base_list = response;    
        });
    }
    tree_base_list();


    


    $scope.treeListEditModel = function(treelist)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'treeListEditModel.html',
            controller: 'treeListEditModelCtrl',
            size: "lg",
            resolve: {
                treelist: function() { return angular.copy(treelist); },
                tree_base_list: function() { return angular.copy($scope.tree_base_list); },
                tree_catagory_list: function() { return angular.copy($scope.tree_catagory_list); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (treelist) {
                angular.forEach($scope.list, function(value,key,array) {
                    if(response.id == value.id){
                        $scope.list[key] = response;
                        console.log(JSON.stringify($scope.list[key]));
                    }
                });
            } else {
                $scope.list.unshift(response);
            }


        });
    }


    $scope.tree_list_status_change = function(row)
    {
        var msg = "您确定要下线该果树吗？";
        if(row.is_delete == 2){
            msg = "您确定要上线该果树吗？";
        }

        layer.confirm(msg, {
          btn: ['确定','取消'] //按钮
        }, function(){
            TreeListService.tree_list_remove(row).success(function (response) {
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


    $scope.uploadModel = function(uploadinfo)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'uploadModel.html',
            controller: 'uploadModelCtrl',
            size: "md",
            resolve: {
                uploadinfo: function() { return angular.copy(uploadinfo); },
            }
        });
        
        modalInstance.result.then(function (response) {
            
        });
    }



}).service('TreeListService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/tree/tree_list';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

    var get_tree_list_one = function (base_id) {
        var url = '/api/tree/get_tree_list_one';
        var data = {base_id:base_id};
        return $http.post(url, data);
    };
    var tree_list_add = function (treelist,irrigation) {
        var url = '/api/tree/tree_list_add';
        var data = {treelist:treelist,irrigation:irrigation};
        return $http.post(url, data);
    };

    var tree_list_edit = function (treelist,irrigation) {
        var url = '/api/tree/tree_list_edit';
        var data = {treelist:treelist,irrigation:irrigation};
        return $http.post(url, data);
    };

    var tree_list_remove = function(tree)
    {
        var url = '/api/tree/tree_list_remove';
        var data = {tree:tree};
        return $http.post(url, data);
    };


    var tree_catagory_list = function()
    {
        var url = '/api/common/tree_catagory_list';
        var data = {};
        return $http.post(url, data);
    };

    var tree_base_list = function()
    {
        var url = '/api/common/tree_base_list';
        var data = {};
        return $http.post(url, data);
    };

    var tree_scale_list = function(type,treelist)
    {
        var url = '/api/tree/tree_scale_list';
        var data = {type:type,treelist:treelist};
        return $http.post(url, data);
    };

    var irrigation_set = function()
    {
        var url = '/api/setting/irrigation_set';
        var data = {};
        return $http.post(url, data);
    };

    var down_csv = function()
    {
        var url = '/api/tree/down_csv';
        var data = {};
        return $http.post(url, data);
    };

    var uploadcsv = function(singlePic)
    {
        var url = '/api/tree/uploadcsv';
        var data = {};
        if(singlePic != undefined && singlePic.file != undefined){
            data.singlePic = {"data":singlePic.data,"file_name":singlePic.file.name,"file_size":singlePic.file.size,"type":singlePic.file.type};
        }
        return $http.post(url, data);
    }

    
    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        get_tree_list_one: function (base_id) {
            return get_tree_list_one(base_id);
        },
        tree_list_add: function (treelist,irrigation) {
            return tree_list_add(treelist,irrigation);
        },
        tree_list_edit: function (treelist,irrigation) {
            return tree_list_edit(treelist,irrigation);
        },
        tree_list_remove: function (treelist) {
            return tree_list_remove(treelist);
        },
        tree_catagory_list: function () {
            return tree_catagory_list();
        },
        tree_base_list: function () {
            return tree_base_list();
        },
        tree_scale_list: function (type,treelist) {
            return tree_scale_list(type,treelist);
        },
        irrigation_set: function () {
            return irrigation_set();
        },
        down_csv: function () {
            return down_csv();
        },
        uploadcsv: function (singlePic) {
            return uploadcsv(singlePic);
        },
    };
}]);




angular.module('myApp').controller('uploadModelCtrl', function ($scope, uploadinfo,TreeListService, $uibModalInstance) {

    $scope.uploaddown = function()
    {
        TreeListService.down_csv().success(function (response) {
            if(response.ret == 0){
                window.open(response.data);
            }
        });
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        TreeListService.uploadcsv($scope.singlePic).success(function (response) {
            if(response.ret == 0) {
                var res = {};
                res = response.data;
                $uibModalInstance.close(res);
            } else {
                if(response.err_data.length == 0){
                    layer.msg(response.msg);
                }else{
                    $err = "";
                    angular.forEach(response.err_data,function(value,key,array){
                        $err += '<div class="col-sm-12">'+value+'</div>';
                    });
                    layer.open({
                      type: 1,
                      skin: 'layui-layer-demo', //样式类名
                      closeBtn: 0, //不显示关闭按钮
                      anim: 2,
                      shadeClose: true, //开启遮罩关闭
                      content: $err
                    });
                }
                
            }
            $scope.save_spinner_display = false;
        });
  
    }
    //cancel事件
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };

    
})









angular.module('myApp').controller('treeListEditModelCtrl', function ($scope, treelist,tree_base_list,tree_catagory_list,TreeListService, $uibModalInstance) {
    $scope.scale_list = [];
    console.log(JSON.stringify(treelist));
    function tree_scale_list($type,treelist)
    {
        TreeListService.tree_scale_list($type,treelist).success(function (response) {
            $scope.scale_list = response.data;   


            if(treelist){
                $scope.treelist = treelist;
                $scope.scale_h = treelist.scale_h;
                $scope.irrigation = [];
                angular.forEach(treelist.curing_proportion,function(value,key,array){
                    $scope.irrigation.push(value.num);
                });
                $scope.scale_w_list = [];
                angular.forEach($scope.scale_list,function(value,key,array){
                    if(value.id == treelist.base_id){
                        angular.forEach(value.scale_list,function(val,k,arr){
                            $scope.scale_w_list.push({'id':val.id,'num':val.num,'child':val.child});
                        });
                        $scope.treelist.scale_w = treelist.scale_w;
                    }
                });

            }
            if(tree_base_list){
                $scope.tree_base_list = tree_base_list;
            }
            if(tree_catagory_list){
                $scope.tree_catagory_list = tree_catagory_list;
            }


        });
    }
    if(treelist){
        $type = "edit";
    }else{
        $type = "add";
    }
    tree_scale_list($type,treelist);

    // if(scale_list){
    //     $scope.scale_list = scale_list;
    // }

    

    //获取养护产品
    $scope.irrigation_list = [];
    function irrigation_set()
    {
        TreeListService.irrigation_set().success(function (response) {
            $scope.irrigation_list = response.data;
        });
    }
    irrigation_set();

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        var all_num = 0;
        var ir = [];
        angular.forEach($scope.irrigation_list,function(value,key,array){
            angular.forEach($scope.irrigation,function(val,k,arr){
                if(k*1+1*1 == value.id){
                    if(value.type == 1){
                        if($.inArray(value.id, ir) == -1){
                            all_num  = all_num*1 + val*1;
                            ir.push(value.id);
                        } 
                    }
                }
            });
        });
        
        if(all_num != 100){
            layer.msg('养护比例错误！');
            $scope.save_spinner_display = false;
            return false;
        }else{
            $scope.irrigation_data = [];
            angular.forEach($scope.irrigation_list,function(value,key,array){
                $scope.irrigation_data.push({'id':value.id,'name':value.name,'type':value.type});
                angular.forEach($scope.irrigation,function(val,k,arr){
                    $scope.irrigation_data[key].num = arr[key];
                });
            });
        }
        

        if(!$scope.treelist.id){
            TreeListService.tree_list_add($scope.treelist,$scope.irrigation_data).success(function (response) {
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
            TreeListService.tree_list_edit($scope.treelist,$scope.irrigation_data).success(function (response) {
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


    

    $scope.get_address = function(newValue,oldValue)
    {
        $scope.scale_w_list = [];
        angular.forEach($scope.scale_list,function(value,key,array){
            if(value.id == newValue.id){
                angular.forEach(value.scale_list,function(val,k,arr){
                    $scope.scale_w_list.push({'id':val.id,'num':val.num,'child':val.child});
                });
            }
        });
    }

    $scope.get_address_h  = function(newValue,oldValue)
    {
        $scope.scale_h_list = [];
        
        angular.forEach($scope.scale_w_list,function(value,key,array){
            if(value.id == newValue.id){
                angular.forEach(value.child,function(val,k,arr){
                   $scope.scale_h_list.push({'id':val.id,'num':val.num}); 
                });
                
                $scope.treelist.scale_h = $scope.scale_h;
            }
        });
        

        //console.log(JSON.stringify($scope.scale_h_list));
    }
    
})
