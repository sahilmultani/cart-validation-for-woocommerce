/**
 * Cart Validation for WooCommerce - Conditional Rules admin.
 *
 * @package Cart_Validation_For_WooCommerce
 * @since 1.0.0
 */
(function ($) {
	'use strict';

	var cvfw = {
		/**
		 * Initialize.
		 */
		init: function () {
			this.initDatepicker();
			this.initSelect2();
			this.bindAddRule();
			this.bindConditionTypeChange();
			this.bindRemoveRule();
		},

		/**
		 * Init datepicker on start/end date fields.
		 */
		initDatepicker: function () {
			if ($.fn.datepicker) {
				$('.cvfw-datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					firstDay: parseInt(cvfw_rules_vars.firstDay, 10) || 0
				});
			}
		},

		/**
		 * Init Select2 on value dropdowns.
		 */
		initSelect2: function () {
			if (typeof $.fn.selectWoo === 'undefined') {
				return;
			}
			$('.cvfw-select2').each(function () {
				var $el = $(this);
				if ($el.hasClass('select2-hidden-accessible')) {
					return;
				}
				var isAjax = $el.hasClass('cvfw-ajax-search');
				var action = $el.data('action');
				var placeholder = $el.data('placeholder') || '';
				var opts = {
					placeholder: placeholder,
					allowClear: true,
					multiple: $el.prop('multiple'),
					width: '100%'
				};
				if (isAjax && action && cvfw_rules_vars.ajax_url) {
					opts.ajax = {
						url: cvfw_rules_vars.ajax_url,
						dataType: 'json',
						delay: 250,
						data: function (params) {
							return {
								action: action,
								security: cvfw_rules_vars.search_nonce,
								term: params.term || '',
								search: params.term || ''
							};
						},
						processResults: function (data) {
							var results = [];
							if (data && typeof data === 'object') {
								$.each(data, function (id, text) {
									results.push({ id: id, text: text });
								});
							}
							return { results: results };
						}
					};
				}
				$el.selectWoo(opts);
			});
		},

		/**
		 * Get next row index for new condition row.
		 */
		getNextRowIndex: function () {
			var max = -1;
			$('#cvfw-conditions-table .cvfw-condition-row').each(function () {
				var r = parseInt($(this).data('row'), 10);
				if (!isNaN(r) && r > max) {
					max = r;
				}
			});
			return max + 1;
		},

		/**
		 * Build value cell HTML for a condition type.
		 */
		buildValueCell: function (rowIndex, condition) {
			var name = 'cvfw_conditions[' + rowIndex + '][value][]';
			var vars = cvfw_rules_vars;
			var i18n = vars.i18n || {};
			var html = '';
			if (condition === 'shipping_country') {
				html = '<select name="' + name + '" class="cvfw-select2 cvfw-value-country" multiple="multiple" data-placeholder="' + (i18n.select_countries || '') + '">';
				$.each(vars.countries || {}, function (code, label) {
					html += '<option value="' + code + '">' + (label || code) + '</option>';
				});
				html += '</select>';
			} else if (condition === 'cart_contains_product') {
				html = '<select name="' + name + '" class="cvfw-select2 cvfw-value-product cvfw-ajax-search" data-action="cvfw_json_search_products" data-placeholder="' + (i18n.search_products || '') + '" multiple="multiple"></select>';
			} else if (condition === 'cart_contains_category') {
				html = '<select name="' + name + '" class="cvfw-select2 cvfw-value-category cvfw-ajax-search" data-action="cvfw_json_search_categories" data-placeholder="' + (i18n.search_categories || '') + '" multiple="multiple"></select>';
			} else if (condition === 'user_role') {
				html = '<select name="' + name + '" class="cvfw-select2 cvfw-value-user-role" multiple="multiple" data-placeholder="' + (i18n.select_roles || '') + '">';
				$.each(vars.roles || {}, function (code, label) {
					html += '<option value="' + code + '">' + (label || code) + '</option>';
				});
				html += '</select>';
			} else {
				html = '<input type="text" name="' + name + '" class="regular-text" placeholder="Value" />';
			}
			return html;
		},

		/**
		 * Build condition dropdown options from condition_types.
		 */
		buildConditionOptions: function (selected) {
			var types = cvfw_rules_vars.condition_types || {};
			var html = '';
			$.each(types, function (group, opts) {
				html += '<optgroup label="' + group + '">';
				$.each(opts, function (key, label) {
					html += '<option value="' + key + '"' + (key === selected ? ' selected' : '') + '>' + label + '</option>';
				});
				html += '</optgroup>';
			});
			return html;
		},

		/**
		 * Build operator dropdown options.
		 */
		buildOperatorOptions: function () {
			var ops = cvfw_rules_vars.operators || {};
			var html = '';
			$.each(ops, function (key, label) {
				html += '<option value="' + key + '">' + label + '</option>';
			});
			return html;
		},

		/**
		 * Add new condition row.
		 */
		appendRow: function (rowIndex, condition, operator) {
			condition = condition || 'shipping_country';
			operator = operator || 'is_equal_to';
			var types = cvfw_rules_vars.condition_types || {};
			var firstKey = null;
			$.each(types, function (g, opts) {
				$.each(opts, function (k) {
					if (firstKey === null) {
						firstKey = k;
					}
					return false;
				});
				return firstKey === null;
			});
			if (firstKey && !condition) {
				condition = firstKey;
			}
			var condOpts = this.buildConditionOptions(condition);
			var opOpts = this.buildOperatorOptions();
			var valueHtml = this.buildValueCell(rowIndex, condition);
			var row = '<tr class="cvfw-condition-row" data-row="' + rowIndex + '">' +
				'<td class="condition-type">' +
				'<select name="cvfw_conditions[' + rowIndex + '][condition]" class="cvfw-condition-type">' + condOpts + '</select>' +
				'</td>' +
				'<td class="condition-operator">' +
				'<select name="cvfw_conditions[' + rowIndex + '][operator]" class="cvfw-condition-operator">' + opOpts + '</select>' +
				'</td>' +
				'<td class="condition-value" data-condition="' + condition + '">' + valueHtml + '</td>' +
				'<td class="condition-actions">' +
				'<button type="button" class="button cvfw-remove-condition" title="' + (cvfw_rules_vars.i18n.delete || 'Delete') + '"><span class="dashicons dashicons-trash"></span></button>' +
				'</td></tr>';
			$('#cvfw-conditions-table .cvfw-no-conditions-row').before(row);
			cvfw.initSelect2();
			$('#cvfw_conditions_row_count').val(cvfw.getNextRowIndex());
			$('.cvfw-no-conditions-row').hide();
		},

		bindAddRule: function () {
			$('body').on('click', '#cvfw-add-condition', function () {
				var idx = cvfw.getNextRowIndex();
				cvfw.appendRow(idx, 'shipping_country', 'is_equal_to');
			});
		},

		bindConditionTypeChange: function () {
			$('body').on('change', '.cvfw-condition-type', function () {
				var $row = $(this).closest('tr.cvfw-condition-row');
				var rowIndex = $row.data('row');
				var condition = $(this).val();
				var $valueTd = $row.find('td.condition-value');
				$valueTd.attr('data-condition', condition);
				$valueTd.empty().append(cvfw.buildValueCell(rowIndex, condition));
				cvfw.initSelect2();
			});
		},

		bindRemoveRule: function () {
			$('body').on('click', '.cvfw-remove-condition', function () {
				var $row = $(this).closest('tr.cvfw-condition-row');
				$row.remove();
				if ($('#cvfw-conditions-table .cvfw-condition-row').length === 0) {
					$('.cvfw-no-conditions-row').show();
				}
				$('#cvfw_conditions_row_count').val($('#cvfw-conditions-table .cvfw-condition-row').length);
			});
		}
	};

	$(function () {
		if (typeof cvfw_rules_vars !== 'undefined') {
			cvfw.init();
		}
	});
})(jQuery);
