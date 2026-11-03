(function () {
    'use strict';
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csp = document.querySelector('meta[name="csp-nonce"]');
    var token = csrf ? csrf.content : '';
    var nonce = csp ? csp.content : '';
    function local(url) { return new URL(url || location.href, location.href).origin === location.origin; }
    window.submitCsrfAction = function (url) {
        if (!local(url)) { throw new Error('Cross-origin action rejected'); }
        var form = document.createElement('form');
        form.method = 'post'; form.action = url;
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'csrf_token'; input.value = token;
        form.appendChild(input); document.body.appendChild(form); form.submit();
    };
    if (window.jQuery) {
        jQuery.ajaxPrefilter(function (options) {
            if (!local(options.url)) { return; }
            options.headers = options.headers || {};
            options.headers['X-CSRF-Token'] = token;
            options.headers['X-CSP-Nonce'] = nonce;
        });
        jQuery.ajaxSetup({converters: {'text script': function (text) {
            jQuery.globalEval(text, {nonce: nonce}); return text;
        }}});
        // Run after existing confirmation/modal handlers have had a chance to cancel.
        jQuery(function () {
            jQuery(document).on('click', 'a[data-csrf-action], #delete-record-modal-btn', function (event) {
                if (event.isDefaultPrevented()) { return; }
                event.preventDefault(); window.submitCsrfAction(this.href);
            });
        });
    }
    var originalFetch = window.fetch;
    if (originalFetch) {
        window.fetch = function (resource, init) {
            var url = resource instanceof Request ? resource.url : resource;
            if (local(url)) {
                init = Object.assign({}, init || {});
                var headers = new Headers(init.headers || (resource instanceof Request ? resource.headers : undefined));
                headers.set('X-CSRF-Token', token); headers.set('X-CSP-Nonce', nonce);
                init.headers = headers;
            }
            return originalFetch.call(this, resource, init);
        };
    }
}());
