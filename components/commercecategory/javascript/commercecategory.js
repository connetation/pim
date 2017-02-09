Ext.ns('TYPO3.Commercecategory');

TYPO3.Commercecategory.Navigator = Ext.extend(Ext.Panel, {
    id: 'typo3-commercecategory',

    initComponent: function() {
        this.items = [
            {
                id: 'commerce-navigation-top-panel',
                xtype: "panel",
                items: [
                    {
                        id: 'commerce-navigation-top-panel-top',
                        html: '<button type="button" id="commerce-navigation-refresh"><img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-refresh.svg" width="16" height="16"></button>',
                        xtype: "panel"
                    },
                    {
                        id: 'commerce-navigation-top-panel-bottom',
                        html: '<input id="commerce-nav-tree-filter" placeholder="Suche..." type="text" />',
                        xtype: "panel"
                    },
                ],
            },
            {
                id: 'commerce-navigation-tree-wrap',
                html: '<div id="commerce-navigation-tree"></div>',
                xtype: "panel"
            }
        ];
        TYPO3.Commercecategory.Navigator.superclass.initComponent.call(this);
    },


    afterRender: function() {
        require(['jquery', "jquery-ui/core", "jquery-ui/widget", 'TYPO3/CMS/Commerce/Libs/fancytree-2.20.0/jquery.fancytree-all.mod'], function($) {
            'use strict';

            /**
             * Helper-Function to load a url in the data-frame
             */
            function jumpTo(id) {
                var theUrl = top.currentSubScript + id;

                if (top.condensedMode) {
                    top.content.document.location = theUrl;
                } else {
                    parent.list_frame.document.location = theUrl;
                }
            }

            /**
             * Commerce Category/product Navigation
             */
            var onExpandCollaps = function(action, node) {
                if (node.data.uid && (node.data.type == "tx_commerce_categories" || node.data.type == "tx_commerce_products")) {
                    $.ajax({url: TYPO3.settings.ajaxUrls[
                        'CommerceTeam_Commerce_ProductTree::ajaxExpandCollapse'],
                        data: {action: action, table: node.data.type, uid: node.data.uid, treeId: 'commerceNavigationTree'}
                    });
                }
            }
            var filter = function(tree, search) {
                var search = search.trim();
                if (search.length >= 3) {
                    tree.filterNodes(search, {});
                } else {
                    tree.clearFilter();
                }
            }

            var tree = $('#commerce-navigation-tree').fancytree({
                source: {url: TYPO3.settings.ajaxUrls['CommerceTeam_Commerce_ProductTree::ajaxGetCategoryTreeData'], data: {treeId: 'commerceNavigationTree'}},
                toggleEffect: false,
                minExpandLevel: 2,
                extensions: ["filter", "clones"],
                quicksearch: true,
                filter: {
                    autoApply: true,   // Re-apply last filter if lazy data is loaded
                    autoExpand: true,  // Expand all branches that contain matches while filtered
                    counter: false,    // Show a badge with number of matching child nodes near parent icons
                    fuzzy: false,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
                    hideExpandedCounter: true,  // Hide counter badge if parent is expanded
                    hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
                    highlight: true,   // Highlight matches by wrapping inside <mark> tags
                    leavesOnly: false, // Match end nodes only
                    nodata: true,      // Display a 'no data' status node if result is empty
                    mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
                },
                tooltip: function(node) {
                    if (node.data.uid) {
                        return 'UID: ' + node.data.uid;
                    }
                },
                click: function(event, data) {
                    //console.log(data);
                    if (top.content.list_frame && data.targetType == 'title' || data.targetType == 'icon') {
                        var table = data.node.data.type;
                        var uid   = data.node.data.uid;

                        if (table == 'root') {
                          jumpTo('&id=' + data.tree.data.productPID + '&tx_commerce_commerce_commercecategory[parent]=');

                        } else if (data.targetType == 'title' || table == "tx_commerce_articles" || table == "tx_commerce_products") {
                            var url = data.tree.data.recordEdit
                                + '&edit['+table+']['+uid+']=edit'
                                + '&returnUrl=' + top.rawurlencode(top.content.list_frame.document.location.pathname + top.content.list_frame.document.location.search);
                            top.content.list_frame.location.href=url;

                        } else if (table == "tx_commerce_categories") {
                            jumpTo('&id=' + data.tree.data.productPID + '&tx_commerce_commerce_commercecategory[parent]=' + uid);
                        }
                    }
                },
                expand: function(event, data) {
                    onExpandCollaps('expand', data.node);
                },
                collapse: function(event, data) {
                    onExpandCollaps('collapse', data.node);
                },
                create: function() {
                    var tree = $(this).fancytree("getTree");
                    $("input#commerce-nav-tree-filter").keyup(function(e){
                        filter(tree, $(this).val());
                    }).focus();

                    $('#commerce-navigation-refresh').click(function() {
                        tree.reload();
                    })
                },
            });


        });
    },
});

TYPO3.ModuleMenu.App.registerNavigationComponent('typo3-commercecategory', function() {
    return new TYPO3.Commercecategory.Navigator();
});
