(function ($) {
    'use strict';

    $(function () {
        var $form = $('form.page-form').first();
        var $fields = $form.find('.nok-correlation-ids');
        if (!$fields.length) return;

        var $firstCategory = $form.find('select[name^="kategori_ketidaksesuaian_"]').first();
        var endpoint = $firstCategory.attr('data-load-path');
        if (!endpoint) return;
        var $legacyCorrelations = $form.find('select[name^="korelasi_tag_"][data-load-select-options]');
        $legacyCorrelations.each(function () {
            $(this).data('amLegacyLoadTarget', $(this).attr('data-load-select-options')).removeAttr('data-load-select-options');
        });
        // Leave the original dropdowns usable if the category master cannot load.
        $.when($.getJSON(endpoint + '/1'), $.getJSON(endpoint + '/2')).done(function (fiveRResult, hseResult) {
            var categories = { '1': fiveRResult[0] || [], '2': hseResult[0] || [] };
            $fields.each(function () {
                var $correlationIds = $(this);
                var $box = $correlationIds.closest('.kendala-box');
                var $categoryIds = $box.find('.nok-category-ids').first();
                var $correlation = $box.find('select[name^="korelasi_tag_"]').first();
                var $category = $box.find('select[name^="kategori_ketidaksesuaian_"]').first();
                if (!$correlation.length || !$category.length) return;

                function readIds($input, fallback) {
                    var parsed;
                    try { parsed = JSON.parse($input.val() || '[]'); } catch (error) { parsed = []; }
                    return Array.isArray(parsed) && parsed.length ? parsed.map(String) : (fallback ? [String(fallback)] : []);
                }
                var initialCorrelations = readIds($correlationIds, $correlation.val());
                if (initialCorrelations.indexOf('5') >= 0) return; // Preserve legacy None records.
                var initialCategories = readIds($categoryIds, $category.val());
                $correlation.removeAttr('data-load-select-options').hide();
                $category.removeAttr('data-load-path').hide();
                $category.closest('.col-md-3').removeClass('col-md-3').addClass('col-md-12 mt-2');
                var $correlationChoices = $('<div class="nok-multi-correlations mt-2">');
                var $categoryChoices = $('<div class="nok-multi-categories mt-2">');
                $correlation.after($correlationChoices);
                $category.after($categoryChoices);

                ['1', '2', '3'].forEach(function (id) {
                    var label = id === '1' ? '5R' : (id === '2' ? 'HSE' : 'Productivity');
                    var $check = $('<input type="checkbox" class="mr-1">').val(id).prop('checked', initialCorrelations.indexOf(id) >= 0);
                    $correlationChoices.append($('<label class="d-inline-block mr-3">').append($check, document.createTextNode(label)));
                });

                function selected($container) {
                    return $container.find('input:checked').map(function () { return this.value; }).get();
                }
                function syncLegacy() {
                    var correlations = selected($correlationChoices);
                    var chosenCategories = selected($categoryChoices);
                    if (correlations.length === 1 && correlations[0] === '3') chosenCategories = ['8'];
                    $correlationIds.val(JSON.stringify(correlations.map(Number)));
                    $categoryIds.val(JSON.stringify(chosenCategories.map(Number)));
                    $correlation.val(correlations[0] || '');
                    var primaryCategories = categories[correlations[0]] || [];
                    var primary = chosenCategories.filter(function (id) {
                        return primaryCategories.some(function (option) { return String(option.value) === id; });
                    })[0] || (correlations.length === 1 && correlations[0] === '3' ? '8' : chosenCategories[0] || '');
                    if (primary && !$category.find('option[value="' + primary + '"]').length) $category.append($('<option>').val(primary).text(primary));
                    $category.val(primary);
                }
                function renderCategories() {
                    var before = selected($categoryChoices).concat(initialCategories);
                    initialCategories = [];
                    $categoryChoices.empty();
                    var correlations = selected($correlationChoices);
                    ['1', '2'].forEach(function (id) {
                        if (correlations.indexOf(id) < 0) return;
                        var $group = $('<div class="mb-1">').append($('<strong class="mr-2">').text(id === '1' ? '5R:' : 'HSE:'));
                        categories[id].forEach(function (option) {
                            var value = String(option.value);
                            var $check = $('<input type="checkbox" class="mr-1">').val(value).prop('checked', before.indexOf(value) >= 0);
                            $group.append($('<label class="d-inline-block mr-3">').append($check, document.createTextNode(option.label)));
                        });
                        $categoryChoices.append($group);
                    });
                    if (correlations.length === 1 && correlations[0] === '3') $categoryChoices.append($('<small class="text-muted">').text('Productivity saja: None diisi otomatis.'));
                    else if (!correlations.length) $categoryChoices.append($('<small class="text-muted">').text('Pilih Korelasi terlebih dahulu.'));
                    syncLegacy();
                }
                $correlationChoices.on('change', 'input', renderCategories);
                $categoryChoices.on('change', 'input[type="checkbox"]', function () {
                    if (this.checked) $(this).closest('div.mb-1').find('input[type="checkbox"]').not(this).prop('checked', false);
                    syncLegacy();
                });
                renderCategories();
            });
            $form[0].addEventListener('submit', function (event) {
                var error = '';
                $form.find('.kendala-box:visible').each(function () {
                    var $box = $(this);
                    var $choices = $box.find('.nok-multi-correlations');
                    if (!$choices.length) return;
                    var correlations = $choices.find('input:checked').map(function () { return this.value; }).get();
                    if (!correlations.length) { error = 'Pilih minimal satu Korelasi untuk setiap part NOK.'; return false; }
                    ['1', '2'].forEach(function (id) {
                        if (correlations.indexOf(id) < 0) return;
                        var validIds = (categories[id] || []).map(function (option) { return String(option.value); });
                        var count = $box.find('.nok-multi-categories input:checked').filter(function () { return validIds.indexOf(this.value) >= 0; }).length;
                        if (count !== 1) error = 'Pilih tepat satu kategori ketidaksesuaian untuk setiap korelasi 5R/HSE yang dicentang.';
                    });
                    if (error) return false;
                });
                if (error) { event.preventDefault(); event.stopImmediatePropagation(); alert(error); }
            }, true);
        }).fail(function () {
            $legacyCorrelations.each(function () {
                $(this).attr('data-load-select-options', $(this).data('amLegacyLoadTarget'));
                if ($(this).val()) $(this).trigger('change');
            });
        });
    });
})(jQuery);
