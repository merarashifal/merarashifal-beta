/* Niyati Admin Script */
jQuery(document).ready(function ($) {
    // Init Color Picker
    if ($.fn.wpColorPicker) {
        $('.niyati-color-field').wpColorPicker();
    }

    // Toggle Gemini Key Visibility
    $('#btn-toggle-gemini-key').on('click', function () {
        var input = $('#gemini_api_key');
        var isPwd = input.attr('type') === 'password';
        input.attr('type', isPwd ? 'text' : 'password');
        $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
    });

    // Toggle Vedic Key Visibility
    $('#btn-toggle-vedic-key').on('click', function () {
        var input = $('#vedic_api_key');
        var isPwd = input.attr('type') === 'password';
        input.attr('type', isPwd ? 'text' : 'password');
        $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
    });

    // Test Gemini API
    $('#btn-test-gemini').on('click', function () {
        var btn = $(this);
        var badge = $('#gemini-test-result');
        var apiKey = $('#gemini_api_key').val().trim();
        var model = $('#gemini_model').val();

        badge.removeClass('success error').addClass('loading').text('Testing connection to Gemini...');
        btn.prop('disabled', true);

        $.post(niyatiAdmin.ajax_url, {
            action: 'niyati_test_gemini',
            nonce: niyatiAdmin.nonce,
            api_key: apiKey,
            model: model
        }, function (res) {
            btn.prop('disabled', false);
            badge.removeClass('loading');
            if (res.success) {
                badge.addClass('success').text('✓ ' + res.data.message);
            } else {
                badge.addClass('error').text('✕ ' + (res.data.message || 'Connection failed'));
            }
        }).fail(function () {
            btn.prop('disabled', false);
            badge.removeClass('loading').addClass('error').text('✕ Server request failed');
        });
    });

    // Test VedicAstroAPI
    $('#btn-test-vedic').on('click', function () {
        var btn = $(this);
        var badge = $('#vedic-test-result');
        var apiKey = $('#vedic_api_key').val().trim();

        badge.removeClass('success error').addClass('loading').text('Testing VedicAstroAPI...');
        btn.prop('disabled', true);

        $.post(niyatiAdmin.ajax_url, {
            action: 'niyati_test_vedic',
            nonce: niyatiAdmin.nonce,
            api_key: apiKey
        }, function (res) {
            btn.prop('disabled', false);
            badge.removeClass('loading');
            if (res.success) {
                badge.addClass('success').text('✓ ' + res.data.message);
            } else {
                badge.addClass('error').text('✕ ' + (res.data.message || 'Connection failed'));
            }
        }).fail(function () {
            btn.prop('disabled', false);
            badge.removeClass('loading').addClass('error').text('✕ Server request failed');
        });
    });
});
