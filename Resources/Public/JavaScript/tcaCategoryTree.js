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

			var isInRecursiveSelection = false;

			jqTree.fancytree({
				source: treeData,
				extensions: ["clones"],
				clones: {
					highlightClones: true
				},
				checkbox: true,
				toggleEffect: false,
				activeVisible: true,
				minExpandLevel: 2,
				select: function(event, data) {
					var cloneNodes = data.node.getCloneList();
					if (cloneNodes != null) {
						for (var idx = 0; idx < cloneNodes.length; idx++) {
							if (cloneNodes[idx].isSelected() != data.node.isSelected()) {
								isInRecursiveSelection = true;
								cloneNodes[idx].setSelected(data.node.isSelected())
							}
						}
					}

					if (isInRecursiveSelection) {
						isInRecursiveSelection = false;
					} else {
						// Get a list of all selected nodes, and convert to a key array:
						var seen = {};
						var selKeys = $.map(data.tree.getSelectedNodes(), function(node){
							if (!seen.hasOwnProperty(node.data.uid)) {
								seen[node.data.uid] = true;
								return node.data.uid;
							}
						});
						$("#"+inputId).val(selKeys.join(","));
					}
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
