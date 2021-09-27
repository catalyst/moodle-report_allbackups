require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {

    $(document).ready(function() {

        var checkboxes = [];
        var url = '';

        $('#downloadallselected').on('click', function() {
            allbackups();
        });

        function allbackups() {
            $('.reportcheckbox:checkbox:checked').each(function () {
                checkboxes.push((this.checked ? $(this).data('id') : ""));
                
            });
            if(checkboxes.length <= 1) {
                url = '/report/allbackups/index.php?id=' + checkboxes[0];
            } else {
                url = '/report/allbackups/index.php?id[]=' + checkboxes[0];
                for (let i = 1; i < checkboxes.length; i++) {
                    url += '&id[]=' + checkboxes[i]; 
                }
                
            }

            window.open(url, '_self');
        }
    });
});