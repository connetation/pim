require(['jquery', "jquery-ui/core", "jquery-ui/widget", 'TYPO3/CMS/Commerce/Libs/fancytree-2.20.0/jquery.fancytree-all.mod'], function($) {
    'use strict';

    $(function() {
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
         * TCA Category-Chooser
         */
        $('.tca-fancy-tree .fancy-tree-data').each(function() {
            var treeData = JSON.parse(this.innerHTML);

            var jqTree  = $(this).closest('.tca-fancy-tree');
            var inputId = jqTree.data('inputId');

            jqTree.fancytree({
                source: treeData,
                checkbox: true,
                toggleEffect: false,
                activeVisible: true,
                select: function(event, data) {
                    // Get a list of all selected nodes, and convert to a key array:
                    var selKeys = $.map(data.tree.getSelectedNodes(), function(node){
                        return node.data.uid;
                    });
                    $("#"+inputId).val(selKeys.join(","));
                },
                click: function(event, data) {
                    if (data.targetType == 'title' || data.targetType == 'icon'){
                        data.node.toggleSelected();
                    }
                }
            });
        });

    });
});