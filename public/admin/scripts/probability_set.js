angular.module('myApp').controller('ProbabilitySetCtrl', function($scope, ProbabilitySetService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage = 20;//每页显示条数    
    function int_data()
    {
       ProbabilitySetService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.probability = response.data[0];
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
        ProbabilitySetService.probability_set_edit($scope.probability).success(function (response) {
            if(response.ret == 0) {
                layer.msg("修改成功");
            } else {
                layer.msg(response.msg);
            }
            $scope.save_spinner_display = false;
        });
    }


}).service('ProbabilitySetService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/setting/probability_set';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

   var probability_set_edit = function (probability) {
        var url = '/api/setting/probability_set_edit';
        var data = {probability:probability};
        return $http.post(url, data);
    };

    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        probability_set_edit: function (probability) {
            return probability_set_edit(probability);
        },
    };
}]);





