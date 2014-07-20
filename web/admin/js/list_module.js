$(document).ready(function() {
    var selected_ids = [];
    $('.confirm_delete').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        var id_object = e.currentTarget.id.split('-').pop();

        if (confirm('You you really sure to delete these objects')) {
            selected_ids.push(id_object);
            deleteSelectedObject();
        }

        return false;
    });
    function deleteSelectedObject() {
        $.post(base_url + 'admin/'+module_name+'/delete.json/',
            {ids: selected_ids},
            function(response) {
                window.location.reload();
                selected_ids = [];
            }
        );
    }
});