angular.module('myApp').controller('overviewCtrl', function($scope, OverviewService, $location, $routeParams , $uibModal) {
	// $scope.date=new Date().toLocaleTimeString();
	function int_data()
    {
       $scope.init_spinner_display = true;
       OverviewService.getDada().success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.order_list = response.order_list;
                $scope.order_count = response.order_count;
                $scope.customer_list = response.customer_list;
                $scope.customer_count = response.customer_count;
                $scope.product_list = response.product_list;
                $scope.product_count = response.product_count;
                $scope.user_count = response.user_count;
            }else{
                $.modal.alert(response.msg);
            }
       });
    }
    //int_data();

}).service('OverviewService', ['$http', function ($http) {
   	var getDada = function () {
        var url = '/api/overview';
        var data = {};
        return $http.post(url, data);
    };

    return {
        getDada: function () {
            return getDada();
        },
    };
}]);
