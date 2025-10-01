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
