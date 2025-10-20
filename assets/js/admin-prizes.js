(function ($) {
        'use strict';

        function openMediaFrame(targetField) {
                var frame = wp.media({
                        title: BHGPrizesL10n.chooseImage,
                        button: { text: BHGPrizesL10n.chooseImage },
                        multiple: false
                });

                frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        setFieldValue(targetField, attachment.id, attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                });

                frame.open();
        }

        function setFieldValue(fieldId, attachmentId, previewUrl) {
                var input = $('#' + fieldId);
                if (!input.length) {
                        return;
                }

                input.val(attachmentId);
                var container = input.closest('.bhg-media-control');
                container.find('.bhg-media-preview').html(previewUrl ? '<img src="' + previewUrl + '" alt="" />' : '<span class="bhg-media-placeholder">' + BHGPrizesL10n.noImage + '</span>');
        }

        $(document).on('click', '.bhg-select-media', function (event) {
                event.preventDefault();
                var target = $(this).data('target');
                if (!target) {
                        return;
                }
                openMediaFrame(target);
        });

        $(document).on('click', '.bhg-clear-media', function (event) {
                event.preventDefault();
                var target = $(this).data('target');
                if (!target) {
                        return;
                }
                setFieldValue(target, '', '');
        });
})(jQuery);
