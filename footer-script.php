<!-- <script src="assets/js/jquery.min.js"></script>
<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js'></script> -->
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/sc-script.js"></script>

<script>
    $( document ).ready(function() {
        $('.navTrigger').click(function(){
            $(this).toggleClass('active');
        });

        $('.material-floating .form-control').on('focus blur', function (e) {
            $(this).parents('.material-floating .form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
        }).trigger('blur');

        $('[data-toggle="slide-collapse"]').on('click', function() {
            $('#slide-navbar-collapse').toggleClass('side_nave_in');
            $(".menu-overlay").toggleClass('overlay_in');
        });

        $(".menu-overlay").click(function(event) {
            $(".navbar-toggle").trigger("click");
            $(".menu-overlay").removeClass('overlay_in');
            $('.navTrigger').removeClass('active');
        });
    });
</script>