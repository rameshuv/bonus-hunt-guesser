(function($){
        var frame;

        function updatePreview(url) {
                var $preview = $('#bhg_badge_preview');
                if (url) {
                        $preview.html('<img src="' + url + '" alt="" />');
                } else {
                        $preview.empty();
                }
        }

        $(document).on('click', '#bhg_select_badge_image', function(e){
                e.preventDefault();

                if (frame) {
                        frame.open();
                        return;
                }

                frame = wp.media({
                        title: BHGAdminBadges.selectImage,
                        button: { text: BHGAdminBadges.selectImage },
                        multiple: false
                });

                frame.on('select', function(){
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#bhg_badge_image').val(attachment.id);
                        updatePreview(attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                });

                frame.open();
        });

        $(document).on('click', '#bhg_clear_badge_image', function(){
                $('#bhg_badge_image').val('');
                updatePreview('');
        });

        $(function(){
                var existing = $('#bhg_badge_preview img').attr('src');
                if (existing) {
                        updatePreview(existing);
                }
        });
})(jQuery);
