require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {

    $(document).ready(function() {

        $('#downloadallselected').on('click', function() {
            downloadallbackups();
        });

        $('#deleteallselected').on('click', function() {
            deleteallbackups();
        });

        // Get the id's of the checkboxes selected and put them in the url.
        function downloadallbackups() {
            var url = '';
            var checkboxes = []
            $('.reportcheckbox:checkbox:checked').each(function () {
                checkboxes.push((this.checked ? $(this).data('id') : ""));
            });
            if(checkboxes.length == 1) {
                url = '/report/allbackups/index.php?downloadallselectedfiles=1' + '&id[]=' + checkboxes[0];
                window.open(url, '_self');
            } else if(checkboxes.length >= 1){
                url = '/report/allbackups/index.php?downloadallselectedfiles=1' + '&id[]=' + checkboxes[0];
                for (let i = 1; i < checkboxes.length; i++) {
                    url += '&id[]=' + checkboxes[i];
                }
                window.open(url, '_self');
            }
        }

        // Get the id's of the checkboxes selected and put them in the url.
        function deleteallbackups() {
            var url = '';
            var checkboxes = []
            $('.reportcheckbox:checkbox:checked').each(function () {
                checkboxes.push((this.checked ? $(this).data('id') : ""));
                
            });
            if(checkboxes.length == 1) {
                url = '/report/allbackups/index.php?deleteselectedfiles=1' + '&id[]=' + checkboxes[0];
                window.open(url, '_self');
            } else if(checkboxes.length >= 1){
                url = '/report/allbackups/index.php?deleteselectedfiles=1' + '&id[]=' + checkboxes[0];
                for (let i = 1; i < checkboxes.length; i++) {
                    url += '&id[]=' + checkboxes[i]; 
                }
                window.open(url, '_self');
            }
        }
    });
});