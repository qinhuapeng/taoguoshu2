angular.module('myApp').controller('TreeBaseCtrl', function($scope, TreeBaseService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    $scope.base_status_map = [{'id':1,'name':"正常"},{'id':2,'name':"下线"}];
    $scope.is_delete_map = ['','正常','已下线'];
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }

    function int_data()
    {
       TreeBaseService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
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





    $scope.tree_base_status_change = function(row)
    {
        var msg = "您确定要下线该果树基地吗？";
        if(row.is_delete == 2){
            msg = "您确定要上线该果树基地吗？";
        }

        layer.confirm(msg, {
          btn: ['确定','取消'] //按钮
        }, function(){
            TreeBaseService.tree_base_remove(row).success(function (response) {
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

    

    $scope.treeBaseEditModel=function(baseinfo)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'treeBaseEditModel.html',
            controller: 'treeBaseEditModelCtrl',
            size: "lg",
            resolve: {
                baseinfo: function() { return angular.copy(baseinfo); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (baseinfo) {
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


    $scope.treeBaseImgUploadModel=function(baseinfo,type)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'treeBaseImgUploadModel.html',
            controller: 'treeBaseImgUploadCtrl',
            size: "lg",
            resolve: {
                baseinfo: function() { return angular.copy(baseinfo); },
                type: function() { return angular.copy(type); },
                list: function() { return $scope.list; },
            }
        });
        
        modalInstance.result.then(function (response) {
            console.log(response);
                angular.forEach($scope.list, function(value, key) {
                    if(response.base_id == value.id){
                        if(response.type == "plant"){
                            angular.forEach(response.img_array, function(val, k) {

                                $scope.list[key].plant_picture_list.push({'pic_path':val.pic_path,'id':val.id});
                            });
                            
                        }else{
                            angular.forEach(response.img_array, function(val, k) {
                                $scope.list[key].content_picture_list.push({'pic_path':val.pic_path,'id':val.id});
                            });
                        }
                        
                    }
                });
            
        });
    }


    $scope.showImg = function(imglist){
        var imgData = [];
        angular.forEach(imglist,function(value,key,array){
            imgData.push({
                "alt": "",//图片名
                "pid": key, //图片id
                "src": value.pic_path, //原图地址
                "thumb": value.pic_path //缩略图地址
            });
        });
        var json = {
          "title": "", //相册标题
          "id": 1, //相册id
          "start": 0, //初始显示的图片序号，默认0
          "data":imgData
        }
         layer.photos({
            photos: json
            ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
          });
    }

    $scope.base_sort = function(sort)
    {
        TreeBaseService.tree_base_sort(sort).success(function (response) {
            // var arr = [];
            // angular.forEach($scope.list, function(value,key,array){
            //     arr.push(value.sort);
            // });
            // arr.sort(function(a,b){return a>b?1:-1});
            
            angular.forEach($scope.list, function(value,key,array){
                angular.forEach($scope.list, function(value,key,array){
                    if(value.id == response.data.id) {
                        $scope.list[key] = response.data;
                    }
                });

            });
            
            layer.msg(response.msg);
        });
    }


}).service('TreeBaseService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/tree/tree_base';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

    var get_tree_base_one = function (base_id) {
        var url = '/api/tree/get_tree_base_one';
        var data = {base_id:base_id};
        return $http.post(url, data);
    };
    var tree_base_add = function (base,pic) {
        console.log("22222:"+JSON.stringify(pic));
        var url = '/api/tree/tree_base_add';
        var data = {base:base};
        var singlePic_logo = pic.singlePic_logo;
        var singlePic_full = pic.singlePic_full;
        //var multiplePic_info = pic.multiplePic_info;
        //var multiplePic_plant = pic.multiplePic_plant;

        if(singlePic_logo != undefined && singlePic_logo.file != undefined){
            data.singlePic_logo = {"data":singlePic_logo.data,"file_name":singlePic_logo.file.name,"file_size":singlePic_logo.file.size,"type":singlePic_logo.file.type};
        }
        if(singlePic_full != undefined && singlePic_full.file != undefined){
            data.singlePic_full = {"data":singlePic_full.data,"file_name":singlePic_full.file.name,"file_size":singlePic_full.file.size,"type":singlePic_full.file.type};
        }

        // data.multiplePic_info = [];
        // if (multiplePic_info != undefined && multiplePic_info.file != undefined) {
        //     for (var i = 0; i < multiplePic_info.data.length; i++) {
        //         data.multiplePic_info.push({'data':multiplePic_info.data[i],'file_name':multiplePic_info.file[i].name,'file_size':multiplePic_info.file[i].size,'type':multiplePic_info.file[i].type});
        //     }
        // }

        // data.multiplePic_plant = [];
        // //console.log("22222:"+JSON.stringify(multiplePic_plant));
        // if (multiplePic_plant != undefined && multiplePic_plant.file != undefined) {
        //     for (var i = 0; i < multiplePic_plant.data.length; i++) {
        //         data.multiplePic_plant.push({'data':multiplePic_plant.data[i],'file_name':multiplePic_plant.file[i].name,'file_size':multiplePic_plant.file[i].size,'type':multiplePic_plant.file[i].type});
        //     }
        // }
        return $http.post(url, data);
    };

    var tree_base_edit = function (base,pic) {
        var url = '/api/tree/tree_base_edit';
        var data = {base:base};
        var singlePic_logo = pic.singlePic_logo;
        var singlePic_full = pic.singlePic_full;
        //var multiplePic_info = pic.multiplePic_info;
        //var multiplePic_plant = pic.multiplePic_plant;
        if(singlePic_logo != undefined && singlePic_logo.file != undefined){
            data.singlePic_logo = {"data":singlePic_logo.data,"file_name":singlePic_logo.file.name,"file_size":singlePic_logo.file.size,"type":singlePic_logo.file.type};
        }
        if(singlePic_full != undefined && singlePic_full.file != undefined){
            data.singlePic_full = {"data":singlePic_full.data,"file_name":singlePic_full.file.name,"file_size":singlePic_full.file.size,"type":singlePic_full.file.type};
        }

        // data.multiplePic_info = [];
        // if (multiplePic_info != undefined && multiplePic_info.file != undefined) {
        //     for (var i = 0; i < multiplePic_info.data.length; i++) {
        //         data.multiplePic_info.push({'data':multiplePic_info.data[i],'file_name':multiplePic_info.file[i].name,'file_size':multiplePic_info.file[i].size,'type':multiplePic_info.file[i].type});
        //     }
        // }

        // data.multiplePic_plant = [];
        // if (multiplePic_plant != undefined && multiplePic_plant.file != undefined) {
        //     for (var i = 0; i < multiplePic_plant.data.length; i++) {
        //         data.multiplePic_plant.push({'data':multiplePic_plant.data[i],'file_name':multiplePic_plant.file[i].name,'file_size':multiplePic_plant.file[i].size,'type':multiplePic_plant.file[i].type});
        //     }
        // }
        return $http.post(url, data);
    };

    var tree_base_remove = function(base)
    {
        var url = '/api/tree/tree_base_remove';
        var data = {base:base};
        return $http.post(url, data);
    }

    var tree_base_img_add = function(base,type,multiplePic_plant)
    {
        var url = '/api/tree/tree_base_img_add';
        var data = {base:base,type:type};
        data.multiplePic_plant = [];
        if (multiplePic_plant != undefined && multiplePic_plant.file != undefined) {
            for (var i = 0; i < multiplePic_plant.data.length; i++) {
                data.multiplePic_plant.push({'data':multiplePic_plant.data[i],'file_name':multiplePic_plant.file[i].name,'file_size':multiplePic_plant.file[i].size,'type':multiplePic_plant.file[i].type});
            }
        }
        return $http.post(url, data);
    }

    var tree_base_img_remove = function(img)
    {
        var url = '/api/tree/tree_base_img_remove';
        var data = {img:img};
        return $http.post(url, data);
    }

    var tree_base_sort = function(base)
    {
        var url = '/api/tree/tree_base_sort';
        var data = {base:base};
        return $http.post(url, data);
    }

    
    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        get_tree_base_one: function (base_id) {
            return get_tree_base_one(base_id);
        },
        tree_base_add: function (base,pic) {
            return tree_base_add(base,pic);
        },
        tree_base_edit: function (base,pic) {
            return tree_base_edit(base,pic);
        },
        tree_base_remove: function (base) {
            return tree_base_remove(base);
        },
        tree_base_img_add: function (base,type,multiplePic_plant) {
            return tree_base_img_add(base,type,multiplePic_plant);
        },
        tree_base_img_remove: function (img) {
            return tree_base_img_remove(img);
        },
        tree_base_sort: function (base) {
            return tree_base_sort(base);
        },
    };
}]);

//图片上传Modal
angular.module('myApp').controller('treeBaseImgUploadCtrl', function ($scope, list,baseinfo,type,TreeBaseService, $uibModalInstance) {
    if(list){
        $scope.list = list;
    }
    if(baseinfo){
        $scope.baseinfo = baseinfo;
    }
    $scope.img_list = [];
    if(type){
        $scope.type = type;
        get_img_list(type);
    }

    
    function get_img_list(type){
        if(type == "plant"){
            $scope.img_list = $scope.baseinfo.plant_picture_list;
        }else{
            $scope.img_list = $scope.baseinfo.content_picture_list;
        }
    }

    $scope.remove_img = function(img){
        layer.confirm("确定要删除该图片吗？", {
          btn: ['确定','取消'] //按钮
        }, function(){
            TreeBaseService.tree_base_img_remove(img).success(function (response) {
                
                console.log(JSON.stringify($scope.list));
                if($scope.type == "plant"){
                    angular.forEach($scope.list, function(value,key,array){
                        if(value.id == $scope.baseinfo.id) {
                            angular.forEach(value.plant_picture_list, function(val,k,arr){
                                if(val.id == response.data.id){
                                   $scope.list[key].plant_picture_list.splice(k, 1); 
                                }
                            });
                        }
                    });
                }else{
                    angular.forEach($scope.list, function(value,key,array){
                        if(value.id == $scope.baseinfo.id) {
                            angular.forEach(value.content_picture_list, function(val,k,arr){
                                if(val.id == response.data.id){
                                   $scope.list[key].content_picture_list.splice(k, 1); 
                                }
                            });
                        }
                    });
                }

                angular.forEach($scope.img_list, function(value,key,array){
                    if(value.id == response.data.id) {
                        array.splice(key, 1);
                    }
                });
                layer.msg(response.msg);

            });
        }, function(){

        });
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;

        TreeBaseService.tree_base_img_add($scope.baseinfo,$scope.type,$scope.multiplePic_plant).success(function (response) {
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

//基地新增修改modal
angular.module('myApp').controller('treeBaseEditModelCtrl', function ($scope, baseinfo,TreeBaseService, $uibModalInstance) {
    if(baseinfo){
        $scope.baseinfo = baseinfo;
    }
    $scope.pic = [];

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        if(!$scope.baseinfo.id){
            TreeBaseService.tree_base_add($scope.baseinfo,$scope.pic).success(function (response) {
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
            TreeBaseService.tree_base_edit($scope.baseinfo,$scope.pic).success(function (response) {
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























































