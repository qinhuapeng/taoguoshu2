(function(angular) {
    'use strict';

    //路由
    angular.module('myApp', ['ngRoute', 'oc.lazyLoad', 'commonDirectives','rt.select2','ui.bootstrap', 'ngSanitize', 'ngCookies',"dndLists", "ngCsv","selector","daterangepicker"])
        .config(function($routeProvider, $locationProvider) {
            var version = 20180416;
            $routeProvider.
            when('/overview', {
                templateUrl: 'views/overview.html',
                controller: 'overviewCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/overview.js"])
                    }]
                }
            }).

            when('/users_list', {
                templateUrl: 'views/users_list.html',
                controller: 'UsersCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/users_list.js"
                                      ])
                    }]
                }
            }).

            when('/users_list_add', {
                templateUrl: 'views/users_list_add.html',
                controller: 'UsersCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/users_list.js",
                                      ])
                    }]
                }
            }).

            when('/users_list_role/:user_id', {
                templateUrl: 'views/users_list_role.html',
                controller: 'UsersCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/users_list.js",
                                      ])
                    }]
                }
            }).

            when('/users_list_pass/:user_id', {
                templateUrl: 'views/users_list_pass.html',
                controller: 'UsersCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/users_list.js",
                                      ])
                    }]
                }
            }).
            when('/jurisdiction_list', {
                templateUrl: 'views/jurisdiction_list.html',
                controller: 'JurisdictionCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/jurisdiction_list.js"])
                    }]
                }
            }).

            when('/role_list', {
                templateUrl: 'views/role_list.html',
                controller: 'RoleCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/role_list.js"])
                    }]
                }
            }).

            when('/role_list_edit/:role_id', {
                templateUrl: 'views/role_list_edit.html',
                controller: 'RoleCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/role_list.js"])
                    }]
                }
            }).

            when('/password', {
                templateUrl: 'views/password.html',
                controller: 'PasswordCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/password.js"])
                    }]
                }
            }).

            
            when('/push_msg', {
                templateUrl: 'views/push_msg.html',
                controller: 'PushmsgCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/push_msg.js"])
                    }]
                }
            }).

            when('/chat_room', {
                templateUrl: 'views/chat_room.html',
                controller: 'ChatroomCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/chat_room.js"])
                    }]
                }
            }).

            when('/probability_set', {
                templateUrl: 'views/probability_set.html',
                controller: 'ProbabilitySetCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/probability_set.js"])
                    }]
                }
            }).

            when('/irrigation_set', {
                templateUrl: 'views/irrigation_set.html',
                controller: 'IrrigationSetCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/irrigation_set.js"])
                    }]
                }
            }).

            when('/rechargeable_vouchers', {
                templateUrl: 'views/rechargeable_vouchers.html',
                controller: 'RechargeableVouchersCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/rechargeable_vouchers.js"])
                    }]
                }
            }).

            when('/irrigation_set_edit/:id', {
                templateUrl: 'views/irrigation_set_edit.html',
                controller: 'IrrigationSetEditCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/irrigation_set.js"])
                    }]
                }
            }).

            when('/customer_list', {
                templateUrl: 'views/customer_list.html',
                controller: 'CustomerListCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/customer_list.js"])
                    }]
                }
            }).


            when('/tree_catagory', {
                templateUrl: 'views/tree_catagory.html',
                controller: 'TreeCatagoryCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load([
                            "scripts/tree_catagory.js",
                            "/library/angular/angular-ueditor/ueditor.config.js",
                            "/library/angular/angular-ueditor/ueditor.all.js",
                            "/library/angular/angular-ueditor/angular-ueditor.min.js",
                            ])
                    }]
                }
            }).

            when('/tree_catagory_edit/:id', {
                templateUrl: 'views/tree_catagory_edit.html',
                controller: 'TreeCatagoryEditCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load([
                            "scripts/tree_catagory.js"
                            ])
                    }]
                }
            }).

            when('/tree_base', {
                templateUrl: 'views/tree_base.html',
                controller: 'TreeBaseCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/tree_base.js",
                            "/library/angular/angular-ueditor/ueditor.config.js",
                            "/library/angular/angular-ueditor/ueditor.all.js",
                            "/library/angular/angular-ueditor/angular-ueditor.min.js",])
                    }]
                }
            }).

            when('/tree_base_edit/:id', {
                templateUrl: 'views/tree_base_edit.html',
                controller: 'TreeBaseEditCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/tree_base.js",
                            "/library/angular/angular-ueditor/ueditor.config.js",
                            "/library/angular/angular-ueditor/ueditor.all.js",
                            "/library/angular/angular-ueditor/angular-ueditor.min.js"])
                    }]
                }
            }).

            when('/tree_cycle', {
                templateUrl: 'views/tree_cycle.html',
                controller: 'TreeCycleCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/tree_cycle.js"])
                    }]
                }
            }).

            when('/tree_list', {
                templateUrl: 'views/tree_list.html',
                controller: 'TreeListCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/tree_list.js"])
                    }]
                }
            }).

            when('/tree_list_edit/:id', {
                templateUrl: 'views/tree_list_edit.html',
                controller: 'TreeListEditCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/tree_list.js"])
                    }]
                }
            }).

            when('/information_set', {
                templateUrl: 'views/information_set.html',
                controller: 'InformationSetCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/information_set.js"])
                    }]
                }
            }).

            when('/information_requirement', {
                templateUrl: 'views/information_requirement.html',
                controller: 'InformationRequirementCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/information_requirement.js"])
                    }]
                }
            }).

            when('/information_admin', {
                templateUrl: 'views/information_admin.html',
                controller: 'InformationAdminCtrl',
                resolve: {
                    deps: ["$ocLazyLoad", function (a) {
                        return a.load(["scripts/information_admin.js"])
                    }]
                }
            }).

            otherwise({redirectTo: '/overview'});
        }
    ).controller("indexCtr", function($scope, $http, $filter, $cookieStore, $interval, indexService) {

        if ($cookieStore.get("username") == undefined) {
            window.location = "/admin/login.html";
            return;
        }
        angular.element(".sidebar-menu > li").on('click',function (el) {
            //alert($(this).find('ul').length);
            if($(this).find('ul').length == 0){
               //$(".sidebar-menu > li > a").removeClass("active");
               $(".sidebar-menu > li").removeClass("menu-open");
               $(".sidebar-menu > li > ul > li").removeClass("active");
               $(".sidebar-menu > li > ul").removeClass("active").slideUp(400);
               $(this).addClass("menu-open");
            }else{
                angular.element(".sidebar-menu li ul li").on('click',function (els) {
                    $(".sidebar-menu li ul li").removeClass("active");
                    $(".sidebar-menu li a").removeClass("active");
                    $(this).addClass('active');
                    //$(this).parent().parent().prev().eq(0).addClass('active');
                }); 
            }
        });


        indexService.check_login().success(function (data) {
            if (data.ret == 999) {
                window.location = "/admin/login.html";
                return;
            } else if (data.ret == 0) {
                $scope.menuslist = data.menuslist;
            } else {

            }
        });
        
        
    }).service('indexService', ['$http', function ($http) {
        var check_login = function () {
            var url = '/api/users/check_login';
            var data = {};
            return $http.get(url, data);
        };

        return {
            check_login: function () {
                return check_login();
            }
        };

    }]);


    //公共的directive
    angular.module('commonDirectives', []).
        directive('appUsername', ['$cookieStore', function($cookieStore) {
            return function(scope, elm, attrs) {
                elm.text($cookieStore.get("username"));
            };
        }]).


        directive('appRolename', ['$cookieStore', function($cookieStore) {
            return function(scope, elm, attrs) {
                elm.text($cookieStore.get("rolename"));
            };
        }]).

        directive('myAccess', [ 'removeElement', '$cookieStore', '$window', function (removeElement, $cookieStore, $window) {
            return{
                restrict: 'A',
                link: function (scope, element, attributes) {
                    var hasAccess = false;
                    
                    angular.forEach(JSON.parse($window.localStorage["access"] || '[]'), function (permission) {
                        if(attributes.myAccess == permission) {
                            hasAccess = true;
                        }
                    });

                    if (!hasAccess) {
                        angular.forEach(element.children(), function (child) {
                            removeElement(child);
                        });
                        removeElement(element);
                    }
                }
            }
        }]).
        directive('myDrop', [ 'removeElement', '$cookieStore', '$window', function (removeElement, $cookieStore, $window) {
            return{
                restrict: 'A',
                link: function (scope, element, attributes) {
                    var hasDrop = false;
                    angular.forEach(JSON.parse($window.localStorage["permissions"] || '[]'), function (permission) {
                        if(attributes.myDrop == permission) {
                            hasDrop = true;
                        }
                    });

                    if (hasDrop) {
                        angular.forEach(element.children(), function (child) {
                            removeElement(child);
                        });
                        removeElement(element);
                    }
                }
            }
        }]).
        directive('paraSection', ['$cookieStore', function($cookieStore) {
            return{
                restrict: 'A',
                link: function (scope, element, attributes) {
                    console.debug(element)
                }
            }
        }]).constant('removeElement', function(element){
            element && element.remove && element.remove();
        }).
        
        directive('myLaydate', function() {
        　　return {
        　　　　require: '?ngModel',
        　　　　restrict: 'A',
        　　　　scope: {
        　　　　　　ngModel: '='　
        　　　　},
        　　　　link: function(scope, element, attr, ngModel) {
        　　　　　　var _date = null,_config={};
        　　　　　　_config = {
        　　　　　　　　lang: 'ch',
        　　　　　　　　elem: element[0],
        　　　　　　　　btns:['clear','confirm'],
        　　　　　　　　format: !!attr.format ? attr.format : 'yyyy-MM-dd HH:mm:ss',
        　　　　　　　　range: attr.range,
                      type:'datetime',
        　　　　　　　　done: function(value, date, endDate) {
                          console.log(JSON.stringify(value));
        　　　　　　　　　　ngModel.$setViewValue(value);
        　　　　　　　　}
        　　　　　　};
        　　　　　　!!attr.typeDate && (_config.type = attr.typeDate);

        　　　　　　 _date = laydate.render(_config);
        　　　
        　　　　　　ngModel.$render = function() {
        　　　　　　　　element.val(ngModel.$viewValue || '');
        　　　　　　};
        　　　　}
        　　}
        })

        .directive('singleFileSelect', ['$window', function ($window) {
            return {
                restrict: 'A',
                require: 'ngModel',
                link: function (scope, el, attr, ctrl) {
                    var fileReader = new $window.FileReader();
                    var res = {};

                    fileReader.onload = function () {
                        res.data = fileReader.result;
                        ctrl.$setViewValue(res);

                        if ('fileLoaded' in attr) {
                            scope.$eval(attr['fileLoaded']);
                        }
                    };

                    fileReader.onprogress = function (event) {
                        if ('fileProgress' in attr) {
                            scope.$eval(attr['fileProgress'], {'$total': event.total, '$loaded': event.loaded});
                        }
                    };

                    fileReader.onerror = function () {
                        if ('fileError' in attr) {
                            scope.$eval(attr['fileError'], {'$error': fileReader.error});
                        }
                    };

                    var fileType = attr['singleFileSelect'];
                    el.bind('change', function (e) {
                        var fileName = e.target.files[0];

                        res.file = fileName;

                        if (fileType === '' || fileType === 'text') {
                            fileReader.readAsText(fileName);
                        } else if (fileType === 'data') {
                            fileReader.readAsDataURL(fileName);
                            //console.log(fileReader);
                        }

                    });
                }
            };
        }])

        .directive('multipleFileSelect', ['$window', function ($window) {
            return {
                restrict: 'A',
                require: 'ngModel',
                link: function (scope, el, attr, ctrl) {
                    var fileType = attr['multipleFileSelect'];
                    el.bind('change', function (e) {
                        var res = {
                            "file":[],
                            "data":[]
                        };

                        var fileLen = e.target.files.length;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
                        
                        for (var i=0;i<fileLen;i++) {
                            var fileReader = new $window.FileReader(); 
                            var fileName = e.target.files[i];
                            console.log(fileName);
                            //res.file = fileName;
                            res.file.push(fileName);
                            fileReader.readAsDataURL(fileName);

                            fileReader.onload = function (e) {
                                res.data.push(e.target.result);
                                ctrl.$setViewValue(res); 
                            };
                            
                              
                        }

                        
                    });
                }
            };
        }])

})(window.angular);
