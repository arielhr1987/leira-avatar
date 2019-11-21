(function ($) {
    'use strict';

    var LeiraAvatar = {

        /**
         * Croppie instance
         */
        croppie: null,

        /**
         * Uploader
         */
        uploader: null,

        /**
         *
         */
        width: 500,

        /**
         *
         */
        height: 360,

        /**
         *
         */
        init: function () {

            /**
             * Add modal content div
             */
            var modalContent = [
                '<div class="" id="leira-avatar-modal-content" style="display: none">',
                '<div id="leira-avatar-croppie">',
                '</div>',
                '<input id="leira-avatar-uploader" type="file" accept="image/*" style="display: none"></div>',
                '</div>'
            ].join('\n');
            $(document.body).append(modalContent);

            /**
             * Create croppie instance
             */
            this.croppie = $('#leira-avatar-croppie').croppie({
                enableExif: true,
                showZoomer: false,
                viewport: {
                    width: 200,
                    height: 200,
                    type: 'square'
                },
                boundary: {
                    width: 500,
                    height: 360
                }
            });

            /**
             * Bind file input change
             */
            $('#leira-avatar-uploader').on('change', function () {
                LeiraAvatar.readFile(this);
            });

            /**
             * Handle click on avatar
             */
            $(document).on('click', '.user-profile-picture img.avatar', function () {
                $('#leira-avatar-uploader').click();
            });

            /**
             * Save
             */
            // $('.upload-result').on('click', function (ev) {
            //     LeiraAvatar.uploader.croppie('result', {
            //         type: 'canvas',
            //         size: 'viewport'
            //     }).then(function (resp) {
            //         // popupResult({
            //         //     src: resp
            //         // });
            //     });
            // });

            LeiraAvatar.position();

            $(window).resize(function () {
                LeiraAvatar.position();
            })
        },

        /**
         *
         * @param input
         */
        readFile: function (input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    //$('.upload-demo').addClass('ready');

                    LeiraAvatar.showModal();
                    //LeiraAvatar.croppie.toggle();
                    LeiraAvatar.croppie.croppie('bind', {
                        url: e.target.result
                    }).then(function () {
                        console.log('jQuery bind complete');
                    });

                };
                reader.readAsDataURL(input.files[0]);

            } else {
                console.log("Sorry - your browser doesn't support the FileReader API");
            }
        },

        /**
         * Show cropper in the modal
         */
        showModal: function () {
            /**
             * Open modal
             */
            var title = "Change your profile picture";
            var url = '?TB_inline&inlineId=leira-avatar-modal-content&width=' + LeiraAvatar.width + '&height=' + LeiraAvatar.height;
            tb_show(title, url, false);
            return false;
        },

        /**
         *
         */
        position: function () {
            var width = $(window).width(),
                //H = $(window).height() - ((500 < width) ? 60 : 20),
                H = 400,
                W = (500 < width) ? 500 : width - 20;

            LeiraAvatar.width = W;
            LeiraAvatar.height = H;

            var tbWindow = $('#TB_window');

            if (tbWindow.length) {
                tbWindow.width(W).height(H);
                //$('#TB_ajaxContent').width(W).height(H);
                tbWindow.css({
                    'margin-left': '-' + parseInt((W / 2), 10) + 'px'
                });
                if (typeof document.body.style.maxWidth !== 'undefined') {
                    // var top = ($(window).height() - 400) / 2;
                    // tbWindow.css({
                    //     'top': top + 'px',
                    //     'margin-top': '0'
                    // });
                }
            }
        }
    };

    $(function () {
        LeiraAvatar.init();
    });

})(jQuery);
