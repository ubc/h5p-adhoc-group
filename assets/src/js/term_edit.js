import '../css/term_edit.scss';

(function($){
    
    $( document ).ready(function() {
        $('#add_user').on('click', function( e ) {
            e.preventDefault();
            const email = $('#user-email').val();

            // Validate input
            var EmailRegex = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
            if ( ! email || ! EmailRegex.test(email) ) {
                setMessage( 'invalid', 'Email address is not valid.' );
                return;
            }
            
            clearMessage();

            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : ajaxurl,
                data : {
                    action: "ubc_h5p_add_user_to_group",
                    user_email : email,
                    term_id: ubc_h5p_adhocgroup.term_id,
                    nonce: ubc_h5p_adhocgroup.security_nonce
                },
                success: function(response) {
                    setMessage( response.data.status, response.data.message );
                    if(response.data.status === 'valid') {
                        window.location.reload();
                    }
                }
             });
        });  

        $('.delete_user').on('click', function( e ) {
            e.preventDefault();

            const username = $(this).closest('tr').find('td:first-child').html();
            if( ! confirm("Are you sure to remove " + username + ' from the group?' )) {
                return;
            }
           
            const user_id = $(this).attr('user_id');

            clearMessage();

            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : ajaxurl,
                data : {
                    action: "ubc_h5p_delete_user_from_group",
                    user_id : user_id,
                    term_id: ubc_h5p_adhocgroup.term_id,
                    nonce: ubc_h5p_adhocgroup.security_nonce
                },
                success: function(response) {
                    window.location.reload();
                }
             });
        });  

        function setMessage( status, message ) {
            $('#message').html( message ).attr('status', status );
        }

        function clearMessage() {
            $('#message').html( '' );
        }
    });

})(jQuery); 