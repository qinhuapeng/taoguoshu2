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
            }else{
                $.modal.alert(response.msg);
            }
       });
    }
    int_data();

}).service('OverviewService', ['$http', function ($http) {
   	var getDada = function () {
        var url = '/api/home/overview';
        var data = {};
        return $http.post(url, data);
    };

    return {
        getDada: function () {
            return getDada();
        },
    };
}]);
