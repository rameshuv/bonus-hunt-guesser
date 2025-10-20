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

        function hasAllRequiredImages($form) {
                var missing = false;

                $form.find('.bhg-prize-image-field input[data-required="1"]').each(function () {
                        if (!$(this).val()) {
                                missing = true;
                                return false;
                        }
                });

                return !missing;
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

        $(document).on('submit', '.bhg-prize-form form', function (event) {
                var $form = $(this);
                if (!hasAllRequiredImages($form)) {
                        event.preventDefault();
                        var message = (typeof BHGPrizesL10n !== 'undefined' && BHGPrizesL10n.imagesRequired) ? BHGPrizesL10n.imagesRequired : 'Please select an image for every size before saving.';
                        window.alert(message);
                }
        });
})(jQuery);
