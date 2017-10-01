(function ($) {

    angular.module('ngMenu', ['ui.nestedSortable'])
            .config(function ($interpolateProvider, $qProvider) {
                $interpolateProvider.startSymbol('{%');
                $interpolateProvider.endSymbol('%}');
                
                $qProvider.errorOnUnhandledRejections(false);
            })
            .controller('MenuCtrl', function ($scope, $http) {
                $scope.menus = [];

                function reloadMenus() {
                    $http.get(menu_nested_url, {
                        params: {
                            lang: _lang,
                            group_id: group_id,
                            per_page: -1
                        }
                    }).then(function (result) {
                        $scope.menus = result.data;
                    }).catch(function (err) {
                        console.log(err);
                    });
                }
                reloadMenus();

                $scope.options = {
                };

                $scope.posts = [];
                $scope.posts_params = {'fields[]': ['pd.title', 'posts.id']};
                $http.get(api_url + 'posts', {
                    params: $scope.posts_params
                }).then(function (result) {
                    var data = result.data;
                    $scope.post_total = data.total;
                    $scope.post_per_page = data.per_page;
                    $scope.post_next_page_url = data.next_page_url;
                    $scope.post_prev_page_url = data.prev_page_url;
                    $scope.posts = data.data;
                });

                $scope.postGoUrl = function (url) {
                    if (url) {
                        $http.get(url, {
                            params: $scope.posts_params
                        }).then(function (data) {
                            $scope.posts = data.data;
                            $scope.post_next_page_url = data.next_page_url;
                            $scope.post_prev_page_url = data.prev_page_url;
                        });
                    }
                };

                $scope.pages = [];
                $http.get(api_url + 'pages', {
                    params: {'fields[]': ['pd.title', 'posts.id'], per_page: -1}
                }).then(function (results) {
                    $scope.pages = results.data;
                });

                $scope.cats = [];
                $http.get(api_url + 'cats', {
                    params: {
                        'fields[]': ['td.name', 'taxs.id'],
                        per_page: -1
                    }
                }).then(function (results) {
                    $scope.cats = results.data;
                });

                $scope.newMenus = [];
                $scope.newcustom = {};
                $scope.addMenu = function (item, type) {
                    var index = $scope.newMenus.indexOf(item);
                    if (index > -1) {
                        $scope.newMenus.splice(index, 1);
                    } else {
                        item.menu_type = type;
                        $scope.newMenus.push(item);
                    }
                };

                $scope.createMenus = function () {
                    if ($scope.newcustom.title) {
                        $scope.addMenu($scope.newcustom, 0);
                    }
                    if ($scope.newMenus.length > 0) {
                        $http.post(store_items_url, {
                            menuItems: $scope.newMenus,
                            group_id: group_id,
                            _token: _token,
                            lang: _lang
                        }).then(function (data) {
                            $scope.newcustom = {};
                            $scope.newMenus = [];
                            reloadMenus();
                            $('.list_types input[type="checkbox"]').prop('checked', false);
                            console.log(data);
                        }).catch(function (err) {
                            console.log(err);
                        });
                    }
                };

                $scope.removeMenu = function (item) {
                    $http.delete(remove_item_url, {
                        params: {id: item.id}
                    }).then(function (data) {
                        reloadMenus();
                    });
                };

                $scope.updateOrder = function (e) {
                    $http.post(order_items_url, {
                        menus: $scope.menus, 
                        _token: _token
                    }).then(function (data) {
                        console.log(data);
                    }).catch(function (err) {
                        e.preventDefault();
                    });
                };
            })
            .directive('ngMenuTarget', function ($http) {
                return {
                    link: function (scope, element, attrs) {
                        attrs.$observe('ngMenuTarget', function (id) {
                            $http.get(menu_type_url, {
                                params: {menu_id: id, lang: _lang}
                            }).then(function (results) {
                                var data = results.data;
                                var title = data.name || data.title;
                                element.html(title);
                                var mi_inner = element.closest('.mi-inner');
                                var elTitle = mi_inner.find('.item-title');
                                var elHandle = mi_inner.find('.handle');

                                if (elTitle.val() == '') {
                                    elTitle.val(title);
                                    elHandle.html(title);
                                }
                            }).catch(function (err) {
                                console.log(err);
                            });
                        });
                    }
                };
            })
            .directive('ngConfirm', function () {
                return{
                    priority: -10,
                    link: function (scope, element, attrs) {
                        element.bind('click', function (e) {
                            var cnfirm = confirm(attrs.ngConfirm);
                            if (!cnfirm) {
                                e.stopImmediatePropagation();
                                e.preventDefault;
                            }
                        });
                    }
                };
            })
            .filter('menutype', function () {
                return function (type) {
                    if (menu_type[type] != "undefined") {
                        return menu_type[type];
                    }
                    return 'N/A';
                };
            });

})(jQuery);


