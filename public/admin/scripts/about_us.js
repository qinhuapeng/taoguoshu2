angular.module('myApp').controller('AboutUsCtrl', function($scope, AboutUsService, $location, $routeParams , $uibModal) {
    function int_data()
    {
       AboutUsService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
            $scope.init_spinner_display = false;
            if(response.ret == 999){
                window.location = "/admin/login.html";
            }else if(response.ret == 0){
                $scope.content = response.data[0].content;
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



    

    $scope.aboutUsModel=function(content)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'aboutUsModel.html',
            controller: 'aboutUsModelCtrl',
            size: "lg",
            resolve: {
                content: function() { return angular.copy(content); },
            }
        });
        
        modalInstance.result.then(function (response) {
            $scope.content = response;
        });
    }




}).service('AboutUsService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/setting/about_us';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };

    var about_us_edit = function(content)
    {
        var url = '/api/setting/about_us_edit';
        var data = {content:content};
        return $http.post(url, data);
    }

    
    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },
        about_us_edit: function (content) {
            return about_us_edit(content);
        },
    };
}]);


angular.module('myApp').controller('aboutUsModelCtrl', function ($scope, content,AboutUsService, $uibModalInstance) {
    if(content){
        $scope.content = content;
    }

    $scope.confirm = function()
    {   

        AboutUsService.about_us_edit($scope.content).success(function (response) {
            if(response.ret == 0) {
                var res = {};
                res = response.data.content;
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























































