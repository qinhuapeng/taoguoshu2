angular.module('myApp').controller('TreeCycleCtrl', function($scope, TreeCycleService, $location, $routeParams , $uibModal) {
    $scope.currentPage = 1;//当前页面
    $scope.itemsPerPage_list = [{'id':1,'name':20},{'id':2,'name':50},{'id':3,'name':100},{'id':4,'name':500},{'id':5,'name':1000}];
    $scope.itemsPerPage_index = 1;
    $scope.search = {'itemsPerPage':$scope.itemsPerPage_list[$scope.itemsPerPage_index-1].name};
    // $scope.search.time = new Date().getFullYear()+"-" + (new Date().getMonth()+1) + "-" + new Date().getDate();
    $scope.get_itemsPerPage_num = function(newValue,oldValue){
        $scope.search.itemsPerPage = $scope.itemsPerPage_list[newValue.id-1].name;
    }

    function int_data()
    {
       TreeCycleService.getDada($scope.search,$scope.currentPage,$scope.itemsPerPage).success(function(response){
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
        TreeCycleService.tree_catagory_list().success(function (response) {
            $scope.tree_catagory_list = response;
        });
    }
    tree_catagory_list();

    //果树基地列表
    $scope.tree_base_list = [];
    function tree_base_list()
    {
        TreeCycleService.tree_base_list().success(function (response) {
            $scope.tree_base_list = response;    
        });
    }
    tree_base_list();

    $scope.treeCycleEditModel = function(cycleinfo)
    {
        var modalInstance = $uibModal.open({
            templateUrl: 'treeCycleEditModel.html',
            controller: 'treeCycleEditModelCtrl',
            size: "lg",
            resolve: {
                cycleinfo: function() { return angular.copy(cycleinfo); },
                tree_base_list: function() { return angular.copy($scope.tree_base_list); },
                tree_catagory_list: function() { return angular.copy($scope.tree_catagory_list); },
            }
        });
        
        modalInstance.result.then(function (response) {
            if (cycleinfo) {
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

    $scope.irrigation_open_status_change = function(row)
    {
        var msg = "您确定要关闭特殊养护吗？";
        if(row.irrigation_open == 0){
            msg = "您确定要开启特殊养护吗吗？";
        }

        layer.confirm(msg, {
          btn: ['确定','取消'] //按钮
        }, function(){
            TreeCycleService.irrigation_open_status(row).success(function (response) {
                angular.forEach($scope.list, function(value,key,array){
                    if(value.id == response.data.id) {
                        $scope.list[key] = response.data;
                    }
                });
                layer.msg(response.msg);
            });
        }, function(){

        });
    }

}).service('TreeCycleService', ['$http', function ($http) {
    var getDada = function (search,currentPage,itemsPerPage) {
        var url = '/api/tree/tree_cycle';
        var data = {search:search,page:currentPage,itemsPerPage:itemsPerPage};
        return $http.post(url, data);
    };


    var tree_cycle_add = function (cycleinfo) {
        var url = '/api/tree/tree_cycle_add';
        var data = {cycleinfo:cycleinfo};
        return $http.post(url, data);
    };

    var tree_cycle_edit = function (cycleinfo) {
        var url = '/api/tree/tree_cycle_edit';
        var data = {cycleinfo:cycleinfo};
        return $http.post(url, data);
    };

    var tree_cycle_remove = function(cycleinfo)
    {
        var url = '/api/tree/tree_cycle_remove';
        var data = {cycleinfo:cycleinfo};
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

    var irrigation_open_status = function(cycleinfo)
    {
        var url = '/api/tree/irrigation_open_status';
        var data = {cycleinfo:cycleinfo};
        return $http.post(url, data);
    };



    return {
        getDada: function (search,currentPage,itemsPerPage) {
            return getDada(search,currentPage,itemsPerPage);
        },

        tree_cycle_add: function (cycleinfo) {
            return tree_cycle_add(cycleinfo);
        },
        tree_cycle_edit: function (cycleinfo) {
            return tree_cycle_edit(cycleinfo);
        },
        tree_cycle_remove: function (cycleinfo) {
            return tree_cycle_remove(cycleinfo);
        },
        tree_catagory_list: function () {
            return tree_catagory_list();
        },
        tree_base_list: function () {
            return tree_base_list();
        },
        irrigation_open_status: function (cycleinfo) {
            return irrigation_open_status(cycleinfo);
        },

    };
}]);




angular.module('myApp').controller('treeCycleEditModelCtrl', function ($scope, cycleinfo,tree_base_list,tree_catagory_list,TreeCycleService, $uibModalInstance) {
    // $scope.dateRangePicker = {
    //         date: {startDate:null, endDate: null},
    //         options: {
    //             opens: "left", //打开的方向，可选值有‘left‘/‘right‘/‘center‘
    //             drops: "down", //('down'/'up'）打开的方向
    //             pickerClasses: 'custom-display', //angular-daterangepicker extra
    //             buttonClasses: 'btn btn-sm',
    //             applyButtonClasses: 'btn-primary',
    //             cancelButtonClasses: 'btn-default',
    //             autoApply: false, //隐藏“应用”和“取消”按钮
    //             alwaysShowCalendars: false,
    //             showCustomRangeLabel: true,
    //             /*timePicker: true, //显示时间选择器
    //             timePicker24Hour: true, //24小时制
    //             timePickerSeconds: true,//是否显示秒
    //             minDate: "2017-01-06",
    //             maxDate: "2019-01-06",*/
    //             locale: { // 本地化
    //                 applyLabel: "确定",
    //                 cancelLabel: "取消",
    //                 fromLabel: "起始时间",
    //                 toLabel: "结束时间",
    //                 customRangeLabel: "自定义范围",
    //                 daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
    //                 monthNames: ["一月", "二月", "三月", "四月", "五月", "六月","七月", "八月", "九月", "十月", "十一月", "十二月"],
    //                 firstDay: 1,
    //                 separator: ' - ',
    //                 format: "YYYY-MM-DD", //will give you 2017-01-06
    //                 //format: "D-MMM-YY", //will give you 6-Jan-17
    //                 //format: "D-MMMM-YY", //will give you 6-January-17
    //             },
    //             ranges: {
    //                 "今天": [moment(), moment()],
    //                 "昨天": [moment().subtract("days", 1), moment().subtract("days", 1)],
    //                 '本周': [moment().startOf('week'), moment().endOf('week')],
    //                 "本月": [moment().startOf("month"), moment().endOf("month")],
    //                 "上月": [moment().subtract("month", 1).startOf("month"), moment().subtract("month", 1).endOf("month")],
    //                 '今年': [moment().startOf('year'), moment().endOf('year')],
    //                 "最近七天": [moment().subtract("days", 6), moment()],
    //                 "最近一个月": [moment().subtract("days", 29), moment()],
    //             },
    //             eventHandlers: {
    //                 'apply.daterangepicker': function(event, picker) { console.log('applied'); }
    //             }
    //         }
    //     };

    if(cycleinfo){
        $scope.cycleinfo = cycleinfo;
        $scope.cycleinfo.base_name = "";
        $scope.cycleinfo.catagory_name = "";
        $scope.dateRangePicker = { date: {startDate: cycleinfo.starttime, endDate: cycleinfo.endtime} };

    }else{
        $scope.cycleinfo = {};
        //$scope.cycleinfo.time = {startDate: '2019-01-01', endDate: '2019-10-10'};
    }
    if(tree_base_list){
        $scope.tree_base_list = tree_base_list;
    }
    if(tree_catagory_list){
        $scope.tree_catagory_list = tree_catagory_list;
    }

    $scope.confirm = function()
    {   
        $scope.save_spinner_display = true;
        $scope.cycleinfo.time = $scope.dateRangePicker.date;
        angular.forEach($scope.tree_base_list,function(value, key,array){
            if(value.id ==$scope.cycleinfo.base_id){
                $scope.cycleinfo.base_name = value.name;
            }
        });

        angular.forEach($scope.tree_catagory_list,function(value, key,array){
            if(value.id ==$scope.cycleinfo.catagory_id){
                $scope.cycleinfo.catagory_name = value.name;
            }
        });

        if(!$scope.cycleinfo.id){
            TreeCycleService.tree_cycle_add($scope.cycleinfo).success(function (response) {
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
            TreeCycleService.tree_cycle_edit($scope.cycleinfo).success(function (response) {
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




