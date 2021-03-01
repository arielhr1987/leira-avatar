(function ($) {
    'use strict';

    window.LeiraAvatar = {

        /**
         * Croppie instance
         */
        croppie: null,

        /**
         * Croppie options
         */
        croppieOptions: {
            //enableExif: true,
            //enableResize: true,
            showZoomer: true,
            viewport: {
                width: 250,
                height: 250,
                type: 'square'
            },
            boundary: {
                height: 500 * 2 / 3
            }
        },

        /**
         * The input file
         */
        file: null,

        /**
         * The image selected by the user
         */
        img: null,

        /**
         * Modal width
         */
        width: 500,

        /**
         * Modal height
         */
        height: 360,

        /**
         * Check if bootstrap is installed in the page.
         */
        isBootstrapDefined: function () {
            return typeof window.bootstrap !== 'undefined';
        },

        /**
         *
         */
        init: function () {

            $(window).trigger('leira-avatar.init');

            /**
             * Create croppie instance
             */
            this.croppie = $('#leira-avatar-croppie').croppie(LeiraAvatar.croppieOptions);

            $(document).
                /**
                 * Handle click on avatar. Trigger input file click to open browser select image dialog
                 */
                on('click', '.user-profile-picture img.avatar, [data-leira-avatar="select"]', function (e) {
                    e.preventDefault();
                    $('#leira-avatar-uploader').click();
                }).
                /**
                 * Bind file input change
                 */
                on('change', '#leira-avatar-uploader', function () {
                    LeiraAvatar.readFile(this);
                }).
                /**
                 * Close the editor
                 */
                on('click', '[data-leira-avatar="close"]', function (e) {
                    e.preventDefault();
                    LeiraAvatar.closeModal();
                }).
                /**
                 * Delete the current avatar
                 */
                on('click', '[data-leira-avatar="delete"]', function (e) {
                    e.preventDefault();
                    LeiraAvatar.remove();
                    $(this).blur();//lose focus
                }).
                /**
                 * Save the image
                 */
                on('click', '[data-leira-avatar="save"]', function (e) {
                    e.preventDefault();
                    LeiraAvatar.save();
                });

            /**
             * After modal close reset input value,
             */
            $('body').on('thickbox:removed', function () {
                LeiraAvatar.file = null;
                LeiraAvatar.img = null;
                $('#leira-avatar-uploader').val(null);
            });

            /**
             * Resize window
             */
            $(window).resize(function () {
                LeiraAvatar.updateCroppie();
            });
        },

        /**
         * Update croppie position
         */
        updateCroppie: function () {
            if (LeiraAvatar.img) {
                //only update if url image exist
                LeiraAvatar.croppie.croppie('bind');
            }
        },

        /**
         * Read the image selected by the user and open croppie modal
         * @param input
         */
        readFile: function (input) {

            // TODO: format is correct
            if (input.files && input.files[0]) {
                LeiraAvatar.file = input.files[0];
                var reader = new FileReader();
                reader.onload = function (e) {

                    LeiraAvatar.showModal();

                    LeiraAvatar.img = e.target.result;
                    //LeiraAvatar.updateCroppie();
                    LeiraAvatar.croppie.croppie('bind', {
                        url: LeiraAvatar.img,
                        zoom: 0
                    }).then(function () {
                        //console.log('Croppie bind complete');
                    });
                };
                reader.readAsDataURL(input.files[0]);

            } else {
                //TODO: Upload directly the image
                console.log("Sorry - your browser doesn't support the FileReader API");
            }
        },

        /**
         * Save the image
         */
        save: function () {
            LeiraAvatar.croppie.croppie('result', {
                type: 'blob',
                size: 'viewport'
            }).then(function (resp) {
                var file = new File([resp], LeiraAvatar.file.name, {
                    type: resp.type,
                    lastModified: new Date()
                });

                var ajaxurl = window.ajaxurl || 'asdasd';
                var data = new FormData();
                data.append('action', 'leira_avatar_upload')
                data.append('file', file);
                data.append('_wpnonce', LeiraAvatarNonce);
                var user = $('input[name="user_id"]').val();
                if (user) {
                    //only add user if present. The admin is editing some user avatar
                    data.append('user', user);
                }
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: data,
                    processData: false,
                    contentType: false,
                }).done(function (data, status, xhr) {
                    //TODO: We need to update the image url
                    //TODO: Handle error from server
                    //TODO: Handle network errors
                    if (data.result && data.url) {
                        //
                        $(window).trigger('leira-avatar.change', data);
                        $('img.avatar.leira-avatar-current-user').attr('src', data.url).attr('srcset', data.url);
                    }
                }).always(function () {
                    LeiraAvatar.closeModal();
                });
            });
        },

        /**
         * Remove the current avatar
         */
        remove: function () {
            if (confirm('Are you sure you want to delete your current avatar')) {
                //TODO: implement ajax call to remove user avatar
            }
        },

        /**
         * Show modal
         */
        showModal: function () {
            tb_show('Edit Avatar', '#TB_inline?&inlineId=leira-avatar-modal-container');
        },
        /**
         * Hide modal
         */
        closeModal: function () {
            tb_remove()
        },
    };

    $(function () {
        LeiraAvatar.init();
    });

})(jQuery);
