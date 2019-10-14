(function($){

    WgsAdmin = {

    /**
     * Init
     */
    init: function() 
    {
        this._bind();
    },

    /**
     * Binds events
     */
    _bind: function()
    {
        $(document).ready( WgsAdmin._dom_ready );

        /* Remote Access */
        $( document ).on('click', '.wgs-remote-access-save-btn', WgsAdmin._remote_access );
        $( document ).on('click', '.wgs-copy-login-link-btn', WgsAdmin._copy_link );
        $( document ).on('click', '.wgs-regenerate-login-link-btn', WgsAdmin._regenerate_login_link );
        $( document ).on('change', '#wgs_remote_access', WgsAdmin._remote_access_dependency );
        
        /* Safe Mode */
        $( document ).on('click', '.wgs-troubleshoot-mode-save-btn', WgsAdmin._troubleshoot_mode );
        $( document ).on('change', '#wgs_troubleshoot_mode', WgsAdmin._troubleshoot_mode_dependency );

        /* Security Code */
        $( document ).on('click', '.wgs-security-code-save-btn', WgsAdmin._security_code );
    },

    _dom_ready: function(e) {
        
        /* Dependency */
        WgsAdmin._remote_access_dependency();
        WgsAdmin._troubleshoot_mode_dependency();

        /* Logs Area */
        var $logs = $('.wgs_debug_log_preview textarea');

        if ( $logs.length > 0 ) {
           
            $logs.each(function(e) {
                
                var textarea = this;
                
                textarea.scrollTop = textarea.scrollHeight;
            } );
        }
    },

    /* Remote Access */
    _remote_access: function(e) {

        e.preventDefault();

        var $this = $(this);

        $this.addClass( 'loading' );
        
        var $remote_access_cb = $( '#wgs_remote_access' );
            checked = 'disable';

        if ( $remote_access_cb.is(':checked') ) {
            checked = 'enable';
        }

        $.ajax({
            url: geeky_support.ajaxurl,
            data: {
                action: "wgs_save_remote_access",
                checked: checked,
                security: geeky_support.remote_access_nonce
            },
            dataType: 'json',
            type: 'POST',
            success: function ( data ) {
                
                $this.removeClass( 'loading' );
                $this.addClass( 'success' );

                location.reload();
            }
        });
    },
    _copy_link: function(e) {

        e.preventDefault();

        var copyText = $('#wgs_login_link');

        /* Select the text field */
        copyText.select();

        /* Copy the text inside the text field */
        document.execCommand("copy");
    },
    _regenerate_login_link: function(e) {
        
        e.preventDefault();

        var $this = $(this);

        $this.addClass( 'loading' );
        
        var $remote_access_cb = $( '#wgs_remote_access' );
            checked = 'disable';

        if ( $remote_access_cb.is(':checked') ) {
            checked = 'enable';
        }

        $.ajax({
            url: geeky_support.ajaxurl,
            data: {
                action: "wgs_regenerate_login_link",
                checked: checked,
                security: geeky_support.regenerate_login_link_nonce
            },
            dataType: 'json',
            type: 'POST',
            success: function ( data ) {
                
                $this.removeClass( 'loading' );
                $this.addClass( 'success' );

                location.reload();
            }
        });
    },
    _remote_access_dependency: function(e) {

        var $this = $('#wgs_remote_access');

        var plugins_wrapper = $( '#form-field-wgs_login_link' );
            
        plugins_wrapper.hide();

        if ( $this.is(':checked') ) {
            plugins_wrapper.show();
        }
    },

    /* Troubleshoot Mode */
    _troubleshoot_mode: function(e) {

        e.preventDefault();

        var $this = $(this);

        $this.addClass( 'loading' );

        var $troubleshoot_mode_cb = $( '#wgs_troubleshoot_mode' );
            checked = 'disable';

        if ( $troubleshoot_mode_cb.is(':checked') ) {
            checked = 'enable';
        }


        var current_theme_sel   = $('.wgs_troubleshoot_mode_theme:checked'),
            current_theme       = 'test';

        if ( current_theme.length > 0 ) {
            current_theme = current_theme_sel.val();
        }

        var troubleshoot_mode_plugins = {};

        $('.wgs_troubleshoot_mode_plugins').each(function(e) {
            
            var $this = $(this),
                slug = $this.val(),
                plugin_status = 'disable';

            if ( $this.is(':checked') ) {
                plugin_status = 'enable';
            }

            troubleshoot_mode_plugins[ slug ] = plugin_status; 
        });

        $.ajax({
            url: geeky_support.ajaxurl,
            data: {
                action: "wgs_save_troubleshoot_mode",
                checked: checked,
                theme: current_theme,
                plugins: troubleshoot_mode_plugins,
                security: geeky_support.troubleshoot_mode_nonce
            },
            dataType: 'json',
            type: 'POST',
            success: function ( data ) {
                $this.removeClass( 'loading' );
                $this.addClass( 'success' );
            }
        });
    },
    _troubleshoot_mode_dependency: function(e) {

        var $this = $('#wgs_troubleshoot_mode');

        var hide_show_wrapper = $( '.wgs_troubleshoot_mode_theme_plugin_wrap' );
            
        hide_show_wrapper.hide();

        if ( $this.is(':checked') ) {
            hide_show_wrapper.show();
        }
    },

    
    _security_code: function(e) {

        e.preventDefault();

        var $this = $(this);

        $this.addClass( 'loading' );

        var security_code = $( '#wgs_security_code' ).val();

        $.ajax({
            url: geeky_support.ajaxurl,
            data: {
                action: "wgs_save_security_code",
                security_code: security_code,
                security: geeky_support.security_code_nonce
            },
            dataType: 'json',
            type: 'POST',
            success: function ( data ) {
                $this.removeClass( 'loading' );
                $this.addClass( 'success' );
            }
        });
    },

    };

    /**
    * Initialization
    */
    WgsAdmin.init();

    

})(jQuery);


// var $temp = $("<input>");
//   $("body").append($temp);
//   $temp.val($(element).text()).select();
//   document.execCommand("copy");
//   $temp.remove();