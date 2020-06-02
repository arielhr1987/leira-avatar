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
            showZoomer: false,
            viewport: {
                width: 200,
                height: 200,
                type: 'square'
            },
            boundary: {
                height: 500 * 2 / 3
            }
        },

        /**
         * Uploader. The input file
         */
        uploader: null,

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
                on('click', '.user-profile-picture img.avatar, .leira-avatar-open-editor', function () {
                    $('#leira-avatar-uploader').click();
                }).
                /**
                 * Bind file input change
                 */
                on('change', '#leira-avatar-uploader', function () {
                    LeiraAvatar.readFile(this);
                }).
                /**
                 * Save the image
                 */
                on('click', '.leira-avatar-save', function () {
                    LeiraAvatar.save();
                });

            if (!LeiraAvatar.isBootstrapDefined()) {
                /**
                 * Close modal
                 */
                $(document).on('click', '#leira-avatar-modal .close, .modal-backdrop', function () {
                    LeiraAvatar.hideModal();
                });
            }

            /**
             * Resize window
             */
            $(window).resize(function () {
                LeiraAvatar.updateCroppie();
            });

            /**
             * BS compatibility
             */
            $('#leira-avatar-modal').on('shown.bs.modal', function () {
                LeiraAvatar.updateCroppie();
            });
        },

        /**
         * Update croppie position
         */
        updateCroppie: function () {
            if (LeiraAvatar.img) {
                //only update if url is bind to croppie
                LeiraAvatar.croppie.croppie('bind');
            }
        },

        /**
         * Read the image selected by the user and open croppie modal
         * @param input
         */
        readFile: function (input) {

            if (input.files && input.files[0]) {
                LeiraAvatar.file = input.files[0];
                var reader = new FileReader();
                reader.onload = function (e) {

                    LeiraAvatar.showModal();

                    LeiraAvatar.img = e.target.result;
                    LeiraAvatar.croppie.croppie('bind', {
                        url: e.target.result
                    }).then(function () {
                        //console.log('jQuery bind complete');
                    });

                    //$(window).resize();
                };
                reader.readAsDataURL(input.files[0]);

            } else {
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
                data.append('action', 'bp_avatar_upload')
                data.append('file', file);
                // data.append('file', LeiraAvatar.file);
                var user = $('input[name="user_id"]').val();
                if (user) {
                    //only add user if present. The admin is editing some user avatar
                    data.append('user', user);
                }
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: data,
                    //dataType: "json",
                    processData: false,
                    contentType: false,
                })
                    .done(function (data, status, xhr) {
                        //we need to update the image url
                        if (data.result && data.url) {
                            //
                            $(window).trigger('leira-avatar.change', data);
                            $('img.avatar.leira-avatar-current-user').attr('src', data.url).attr('srcset', data.url);
                        }
                    }).always(function () {
                    LeiraAvatar.hideModal();
                });
            });
        },

        /**
         * Show cropper in the modal
         */
        toggleModal: function (visible) {

            if (!LeiraAvatar.isBootstrapDefined()) {
                /**
                 * Open modal
                 */
                $(document.body).toggleClass('modal-open', visible);//invalidate page scroll
                var backdrop = $('.modal-backdrop');
                if (backdrop.length > 0) {
                    //backdrop exist
                    if (!visible) {
                        backdrop.remove();
                    }
                } else {
                    if (visible) {
                        $(document.body).append('<div class="modal-backdrop fade show"></div>')
                    }
                }
                $('#leira-avatar-modal').toggle(visible);
            }
        },

        /**
         * Show modal
         */
        showModal: function () {
            if (LeiraAvatar.isBootstrapDefined()) {
                $('#leira-avatar-modal').modal('show')
            } else {
                LeiraAvatar.toggleModal(true)
            }
        },
        /**
         * Hide modal
         */
        hideModal: function () {
            if (LeiraAvatar.isBootstrapDefined()) {
                $('#leira-avatar-modal').modal('hide')
            } else {
                LeiraAvatar.toggleModal(false)
            }
        }
    };

    $(function () {
        LeiraAvatar.init();
    });

})(jQuery);
