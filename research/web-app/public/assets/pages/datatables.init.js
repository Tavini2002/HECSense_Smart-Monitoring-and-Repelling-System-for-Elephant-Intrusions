/*
 Template Name: Zegva - Responsive Bootstrap 4 Admin Dashboard
 Author: Themesdesign
 Website: www.themesdesign.in
 File: Datatable js
 */

 $(document).ready(function() {
    // Custom search function for matching from the beginning
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var searchTerm = $('.dataTables_filter input').val().toLowerCase();
            if (!searchTerm) return true;

            for (var i = 0; i < data.length; i++) {
                if (data[i].toLowerCase().startsWith(searchTerm)) {
                    return true;
                }
            }
            return false;
        }
    );

    $('#datatable').DataTable({
        scrollX: true, // Enable horizontal scrolling
        fixedColumns: true // Optionally fix the leftmost columns
    });
    
});