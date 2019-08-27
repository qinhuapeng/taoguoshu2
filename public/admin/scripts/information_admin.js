angular.module('myApp').controller('InformationAdminCtrl', function($scope, InformationAdminService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    // $scope.search.time = new Date().getFullYear()+"-" + (new Date().getMonth()+1) + "-" + new Date().getDate();
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }
    $scope.tree_catagory_list = [];
    $scope.tree_base_list = [];

    function int_data()
    {
       InformationAdminService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
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

    function tree_base_list()
    {
        InformationAdminService.tree_base_list().success(function (response) {
            $scope.tree_base_list = response;
        });
    }
    tree_base_list();

    function tree_catagory_list()
    {
        InformationAdminService.tree_catagory_list().success(function (response) {
            $scope.tree_catagory_list = response;
        });
    }
    tree_catagory_list();



    $scope.showImg = function(imglist){
        var imgData = [];
        console.log(imglist);
        angular.forEach(imglist,function(value,key,array){
            imgData.push({
                "alt": "",//图片名
                "pid": key, //图片id
                "src": value.data_path, //原图地址
                "thumb": value.data_path //缩略图地址
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

    $scope.showVideo = function(video){
        var html = '<video controls="controls" autoplay="autoplay"><source src="'+video+'" type="video/mp4" />浏览器不支持</video>'
        layer.open({
          type: 1,
          skin: 'layui-layer-video', //样式类名
          closeBtn: 0, //不显示关闭按钮
          anim: 2,
          shadeClose: true, //开启遮罩关闭
          content: html
        });
    }


}).service('InformationAdminService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/information/information_admin';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

    var tree_catagory_list = function () {
        var url = '/api/common/tree_catagory_list';
        var data = {};
        return $http.post(url, data);
    };
    var tree_base_list = function () {
        var url = '/api/common/tree_base_list';
        var data = {};
        return $http.post(url, data);
    };


    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        tree_catagory_list: function () {
            return tree_catagory_list();
        },
        tree_base_list: function () {
            return tree_base_list();
        },
    };
}]);
