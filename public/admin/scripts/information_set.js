angular.module('myApp').controller('InformationSetCtrl', function($scope, InformationSetCtrlSetService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 20;//每页显示条数    
    function int_data()
    {
       InformationSetCtrlSetService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.information = response.data[0];
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


    $scope.confirm = function()
    {
        $scope.save_spinner_display = true;
        InformationSetCtrlSetService.information_set_edit($scope.information).success(function (response) {
            if(response.ret == 0) {
                layer.msg("修改成功");
            } else {
                layer.msg(response.msg);
            }
            $scope.save_spinner_display = false;
        });
    }


}).service('InformationSetCtrlSetService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/information/information_set';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

   var information_set_edit = function (information) {
        var url = '/api/information/information_set_edit';
        var data = {information:information};
        return $http.post(url, data);
    };

    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        information_set_edit: function (information) {
            return information_set_edit(information);
        },
    };
}]);





