require(['jquery', "jquery-ui/core", "jquery-ui/widget", 'TYPO3/CMS/Commerce/Libs/fancytree-2.20.0/jquery.fancytree-all.mod'], function($) {
    'use strict';

    $('#commerce-access-tree').each(function(){
        var jqTree = $(this);

        var renderAccessIcons = function(field, perm) {
            return (
                '<span data-field="'+field+'" data-value="'+perm+'">'
                    + renderSingleAccesIcon(1,  perm, 'Show Category')
                    + renderSingleAccesIcon(16, perm, 'Edit Content')
                    + renderSingleAccesIcon(2,  perm, 'Edit Category')
                    + renderSingleAccesIcon(4,  perm, 'Delete Category')
                    + renderSingleAccesIcon(8,  perm, 'New Categories')
                + '</span>'
            );
        };

        var renderSingleAccesIcon = function(bit, perm, title) {
            //console.log(bit + ' & ' + perm + ' == ' + (bit&perm));
            if ((bit & perm) == bit) {
                return '<span data-bit-enabled data-bits="'+bit+'" title="'+title+'" class="t3-icon change-permission fa fa-check text-success"></span>';
            } else {
                return '<span data-bit-disabled data-bits="'+bit+'" title="'+title+'" class="t3-icon change-permission fa fa-times text-danger"></span>';
            }
        }

        var renderAccessCheckboxes = function(field, perm) {
            return (
                  renderSingleAccesCheckbox(field, 1,  perm, 'Show Category')
                + renderSingleAccesCheckbox(field, 16, perm, 'Edit Content')
                + renderSingleAccesCheckbox(field, 2,  perm, 'Edit Category')
                + renderSingleAccesCheckbox(field, 4,  perm, 'Delete Category')
                + renderSingleAccesCheckbox(field, 8,  perm, 'New Categories')
            );
        }

        var renderSingleAccesCheckbox = function(field, bit, perm, title) {
            var checked = (bit & perm) == bit ? 'checked ' : '';
            return '<label><input type="checkbox" name="'+field+'[]" value="'+bit+'" '+checked+'/> '+title+'</label>';
        }

        var getLockHTML = function(editlock) {
            return (
                '<a class="editlock btn btn-default"><span class="t3js-icon icon icon-size-small"><span class="icon-markup">'
                + (editlock
                    ? '<img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-lock.svg" width="16" height="16">'
                    : '<img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-unlock.svg" width="16" height="16">'
                  )
                + '</span></span></a>'
            );
        }

        var getBeUsername = function(uid) {
            if (tree.data.beUsers.hasOwnProperty(uid)) {
                return tree.data.beUsers[uid].username;
            }
        }

        var getBeGroupTitle = function(uid) {
            if (tree.data.beGroups.hasOwnProperty(uid)) {
                return tree.data.beGroups[uid].title;
            }
        }

        var renderRow = function(node) {
            if (node.data.hasOwnProperty('access')) {
                var $tdList = $(node.tr).find(">td");
                $tdList = $(node.tr).find(">td");

                if (!node.data.hasOwnProperty('accessNew')) {
                    node.data.accessNew = JSON.parse(JSON.stringify(node.data.access));
                }

                var access = node.data.access;

                $tdList.eq(1).attr('nowrap', "nowrap").html(renderAccessIcons('perms_user', access.perms_user) + ' ' + getBeUsername(access.perms_userid));
                $tdList.eq(2).attr('nowrap', "nowrap").html(renderAccessIcons('perms_group', access.perms_group) + ' ' + getBeGroupTitle(access.perms_groupid));
                $tdList.eq(3).attr('nowrap', "nowrap").html(renderAccessIcons('perms_everybody', access.perms_everybody));
                $tdList.eq(4).attr('nowrap', "nowrap").html(getLockHTML(access.editlock));
                $tdList.eq(5).attr('nowrap', "nowrap").html('&nbsp;');
            }
        };

        var renderRowActive = function(node) {
            if (node.data.hasOwnProperty('access')) {
                var $tdList = $(node.tr).find(">td");
                $tdList = $(node.tr).find(">td");

                var access = node.data.accessNew;

                var jqControll = $(
                    '<span id="o_9">'
                        + '<select name="recursive"><option value="0">This Category</option><option value="1">Recursive</option></select>'
                        + '<span class="btn-group">'
                            + '<a class="saveowner btn btn-default" title="Save">'
                                + '<span class="t3js-icon icon icon-size-small icon-state-default icon-actions-document-save">'
                                    + '<span class="icon-markup">'
                                        + '<img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-document-save.svg" width="16" height="16">'
                                    + '</span>'
                                + '</span>'
                            + '</a>'
                            + '<a class="restoreowner btn btn-default" title="Cancel">'
                                + '<span class="t3js-icon icon icon-size-small icon-state-default icon-actions-document-close">'
                                    + '<span class="icon-markup">'
                                        + '<img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-document-close.svg" width="16" height="16">'
                                    + '</span>'
                                + '</span>'
                            + '</a>'
                        + '</span>'
                    +'</span>'
                );

                renderPermsUser(access, $tdList.eq(1));
                renderPermsGroup(access, $tdList.eq(2));
                renderPermsEveryone(access, $tdList.eq(3));
                $tdList.eq(4).attr('nowrap', "nowrap").html('<input type="checkbox" name="lock" '+(access.editlock ? 'checked ' : '')+'/>');
                $tdList.eq(5).attr('nowrap', "nowrap").html(jqControll);

                $tdList.on('change' , 'input, select', function() {
                    node.data.accessNew.perms_userid = parseInt($tdList.find('[name="perms_userid"]').val());
                    node.data.accessNew.perms_user = 0;
                    $tdList.find('[name="perms_user[]"]:checked').each(function() {
                        node.data.accessNew.perms_user += parseInt($(this).val());
                    })

                    node.data.accessNew.perms_groupid = parseInt($tdList.find('[name="perms_groupid"]').val());
                    node.data.accessNew.perms_group = 0;
                    $tdList.find('[name="perms_group[]"]:checked').each(function() {
                        node.data.accessNew.perms_group += parseInt($(this).val());
                    })

                    node.data.accessNew.perms_everybody = 0;
                    $tdList.find('[name="perms_everybody[]"]:checked').each(function() {
                        node.data.accessNew.perms_everybody += parseInt($(this).val());
                    })

                    node.data.accessNew.editlock = $tdList.find('[name="lock"]').is(':checked') ? 1 : 0;
                });

                jqControll.find('.saveowner').click(function() {
                    var recursive = $tdList.find('[name="recursive"]').val();
                    jqTree.fancytree("disable");
                    $.ajax({
                        type: 'POST',
                        url: TYPO3.settings.ajaxUrls['CommerceTeam_Commerce_Access::ajaxSetAccess'],
                        data: {refKey: node.refKey, access: node.data.accessNew, recursive: recursive},
                    }).always(function() {
                        jqTree.fancytree("enable");
                        tree.reload($.ajax({
                            type: 'GET',
                            url: TYPO3.settings.ajaxUrls['CommerceTeam_Commerce_Access::ajaxGetAccessData'],
                            data: {treeConf: {showProducts: 0, expanded: true}},
                            contentType: 'application/json'
                        }));
                    })
                });
                jqControll.find('.restoreowner').click(function() {
                    node.data.accessNew = JSON.parse(JSON.stringify(node.data.access));
                    renderRowActive(node);
                });
            }
        };


        var renderPermsUser = function(access, jqTd) {
            var select = '<select class="on-active" name="perms_userid">';
            for (var uid in tree.data.beUsers) {
                if (uid == access.perms_userid){
                    select += '<option value="'+uid+'" selected>' + tree.data.beUsers[uid].username + '</option>';
                } else {
                    select += '<option value="'+uid+'">' + tree.data.beUsers[uid].username + '</option>';
                }
            }
            select += '</select>';

            jqTd.attr('nowrap', "nowrap").html(
                '<div class="on-active access-form">'
                    + select
                    + renderAccessCheckboxes('perms_user', access.perms_user)
                + '</div>'
                + '<div class="off-active">'
                    + renderAccessIcons('perms_user', access.perms_user)
                    + ' ' + getBeUsername(access.perms_userid)
                + '</div>'
            );
        }

        var renderPermsGroup = function(access, jqTd) {
            var select = '<select class="on-active" name="perms_groupid">';
            for (var uid in tree.data.beGroups) {
                if (uid == access.perms_groupid){
                    select += '<option value="'+uid+'" selected>' + tree.data.beGroups[uid].title + '</option>';
                } else {
                    select += '<option value="'+uid+'">' + tree.data.beGroups[uid].title + '</option>';
                }
            }
            select += '</select>';

            jqTd.attr('nowrap', "nowrap").html(
                '<div class="on-active access-form">'
                    + select
                    + renderAccessCheckboxes('perms_group', access.perms_group)
                + '</div>'
                + '<div class="off-active">'
                    + renderAccessIcons('perms_group', access.perms_group)
                    + ' ' + getBeUsername(access.perms_groupid)
                + '</div>'
            );
        }

        var renderPermsEveryone = function(access, jqTd) {
            jqTd.attr('nowrap', "nowrap").html(
                '<div class="on-active access-form">'
                    + renderAccessCheckboxes('perms_everybody', access.perms_everybody)
                + '</div>'
                + '<div class="off-active">'
                    + renderAccessIcons('perms_everybody', access.perms_everybody)
                + '</div>'
            );
        }


        var tree = jqTree.fancytree({
            source: $.ajax({
                type: 'GET',
                url: TYPO3.settings.ajaxUrls['CommerceTeam_Commerce_Access::ajaxGetAccessData'],
                data: {treeConf: {showProducts: 0, expanded: true}},
                contentType: 'application/json'
            }),
            toggleEffect: false,
            minExpandLevel: 2,
            extensions: ["table", "clones"],
            table: {
                indentation: 20,
                nodeColumnIdx: 0,
            },
            renderColumns: function(event, data) {
                renderRow(data.node);
            },
            tooltip: function(node) {
                if (node.data.uid) {
                    return 'UID: ' + node.data.uid;
                }
            },
            deactivate: function(event, data) {
                renderRow(data.node);
            },
            activate: function(event, data) {
                renderRowActive(data.node);
            }
        }).fancytree("getTree");
        //console.log(tree);

    });

});