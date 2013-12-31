jQuery(function() {
	// :first selector is optional if you have only one tabs on the page
	jQuery(".css-tabs:first").tabs(".css-panes:first > div");
});
jQuery(document).ready(function() {
	jQuery(".entry").show();
	jQuery('.bmlt_simple_meeting_one_meeting_name_td a').attr('target', '_blank');
	jQuery('.bmlt_simple_meeting_one_meeting_address_td a').attr('target', '_blank');
	jQuery('.bmlt_simple_format_table').prepend('<thead><th class="bmlt_simple_format_table_header">h1</th><th class="bmlt_simple_format_table_header">h1</th><th class="bmlt_simple_format_table_header">h1</th></thead>');
	jQuery('.bmlt_simple_meetings_table').prepend('<thead><th class="bmlt_simple_format_table_header">h0</th><th class="bmlt_simple_format_table_header">h1</th><th class="bmlt_simple_format_table_header">h2</th><th class="bmlt_simple_format_table_header">h3</th><th class="bmlt_simple_format_table_header">h5</th><th class="bmlt_simple_format_table_header">h4</th></thead>');
	jQuery('.bmlt_simple_format_table').dataTable( {
		"bPaginate": false,
		"bLengthChange": false,
		"bFilter": false,
		"bSort": true,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": 'Rlfrtip',
		"oColReorder": {
			"aiOrder": [ 0, 1, 2 ]
			}
	} );
	jQuery('.bmlt_simple_meetings_table').dataTable( {
		"bPaginate": false,
		"bLengthChange": false,
		"bFilter": false,
		"bSort": false,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": 'Rlfrtip',
		"oColReorder": {
			"aiOrder": [ 2, 1, 3, 4, 0, 5 ]
			}
	} );
});