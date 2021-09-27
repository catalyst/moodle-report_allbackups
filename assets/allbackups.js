require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {

    $(document).ready(function() {

        $('#downloadallselected, #deleteallselected').on('click', function() {
            allbackups();
        });

        // Get the id's of the checkboxes selected and put them in the url.
        function allbackups() {
            var url = '';
            var checkboxes = []
            $('.reportcheckbox:checkbox:checked').each(function () {
                checkboxes.push((this.checked ? $(this).data('id') : ""));
                
            });
            if(checkboxes.length == 1) {
                url = '/report/allbackups/index.php?id[]=' + checkboxes[0];
                window.open(url, '_self');
            } else if(checkboxes.length >= 1){
                url = '/report/allbackups/index.php?id[]=' + checkboxes[0];
                for (let i = 1; i < checkboxes.length; i++) {
                    url += '&id[]=' + checkboxes[i]; 
                }
                window.open(url, '_self');
            }
        }
    });
});