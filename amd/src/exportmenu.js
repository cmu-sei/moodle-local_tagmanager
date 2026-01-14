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
define(['jquery', 'core/templates'], function($, Templates) {
    return {
        init: function() {
            $(document).ready(function() {
                // Collections table case: add Export per row.
                $('.tag-collections-table tbody tr').each(function() {
                    var row = $(this);

                    // Skip if export already present.
                    if (row.find('.export-collection').length) {
                        return;
                    }

                    // Look at the "lastcol" cell where actions live.
                    var actionCell = row.find('td.c4.lastcol');
                    if (!actionCell.length) {
                        return;
                    }

                    // Extract tc from the link in the name column (safer for both default & others).
                    var tcLink = row.find('td.c0 a[href*="manage.php?tc="]');
                    if (!tcLink.length) {
                        return;
                    }
                    var url = new URL(tcLink.attr('href'), window.location.origin);
                    var tc = url.searchParams.get('tc');
                    if (!tc) {
                        return;
                    }

                    var sesskey = M.cfg.sesskey;
                    var exportUrl = M.cfg.wwwroot + '/local/tagmanager/export.php?tc=' + tc + '&sesskey=' + sesskey;

                    // Render and insert Export icon at the end of the action cell.
                    Templates.render('local_tagmanager/export_action', {
                        exporturl: exportUrl
                    }).done(function(html, js) {
                        actionCell.append(html); // for default this will be the only icon
                        Templates.runTemplateJS(js);
                    }).fail(function(ex) {
                        console.error('Failed to render export link', ex);
                    });
                });
            });
        }
    };
});
