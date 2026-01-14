// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//

/*
Tag Manager Plugin for Moodle

Copyright 2026 Carnegie Mellon University.

NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS" BASIS.
CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER INCLUDING, BUT NOT LIMITED TO,
WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY
DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.

Licensed under a GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007-style license, please see license.txt or contact permission@sei.cmu.edu for full terms.

[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution. Please see Copyright notice for non-US Government use and distribution.

This Software includes and/or makes use of Third-Party Software each subject to its own license.

DM26-0016
*/
define(['jquery', 'core/str', 'core/notification'], function($, Str, Notification) {

    function getSelectedIds() {
        const ids = $('[data-region="reportbuilder-table"] input[name="report-select-row[]"][data-toggle="slave"]:checked')
            .map(function() { return ($(this).val() || '').toString().trim(); })
            .get()
            .filter(v => /^\d+$/.test(v));
        return [...new Set(ids)];
    }

    function initExportSelected(tc) {
        const $combine = $('#tag-management-combine');
        if (!$combine.length) return;

        const $form = $combine.closest('form');
        if (!$form.length) return;

        if ($form.find('.local-tagmanager-exportselected').length) return;

        const $btn = $('<button/>', {
            type: 'button',
            class: 'btn btn-secondary local-tagmanager-exportselected',
            text: 'Export selected'
        });

        $btn.insertAfter($combine);

        let noneMsg = 'No tags selected';
        Str.get_strings([
            {key: 'exportselected', component: 'local_tagmanager'},
            {key: 'exportselectednone', component: 'local_tagmanager'}
        ]).then(function(strings) {
            $btn.text(strings[0]);
            noneMsg = strings[1];
        });

        $btn.on('click', function(e) {
            e.preventDefault();

            const ids = getSelectedIds();
            if (!ids.length) {
                Notification.addNotification({message: noneMsg, type: 'info'});
                return;
            }

            const url = M.cfg.wwwroot
                + '/local/tagmanager/export.php'
                + '?tc=' + encodeURIComponent(tc)
                + '&tagids=' + encodeURIComponent(ids.join(','))
                + '&sesskey=' + encodeURIComponent(M.cfg.sesskey);

            window.location.href = url;
        });
    }

    function initImportStandard(tc) {
        const $row = $('.d-flex.justify-content-between.mb-2').first();
        if (!$row.length) return;

        const $addStandard = $row.find('button[data-action="addstandardtag"]').first();
        if (!$addStandard.length) return;

        if ($row.find('.local-tagmanager-importstandard').length) return;

        // Right-side actions container.
        let $right = $row.find('.local-tagmanager-header-actions');
        if (!$right.length) {
            $right = $('<div/>', {
                class: 'd-flex align-items-center gap-2 local-tagmanager-header-actions'
            });

            $addStandard.detach().appendTo($right);
            $row.append($right);
            $row.addClass('align-items-center');
        }

        const $btn = $('<button/>', {
            type: 'button',
            class: 'btn btn-primary local-tagmanager-importstandard',
            html: '<i class="icon fa fa-plus fa-fw" aria-hidden="true"></i> Import standard tags'
        });

        $right.append($btn);

        $btn.on('click', function() {
            window.location.href =
                M.cfg.wwwroot + '/local/tagmanager/import.php?tc=' + encodeURIComponent(tc);
        });
    }

    return {
        init: function(tc) {
            $(function() {
                if (!tc) return;
                initExportSelected(tc);
                initImportStandard(tc);
            });
        }
    };
});
