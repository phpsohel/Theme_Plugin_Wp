(function ($) {
    $(function () {
        if (typeof window.the7ElementorSettingsCache === "undefined") {
            window.the7ElementorSettingsCache = {};
        }

        function getWidgetSettingsCache(widgetType, field) {
            if (!window.the7ElementorSettingsCache[widgetType]) {
                window.the7ElementorSettingsCache[widgetType] = {}
            }

            if (typeof field !== "undefined") {
                return window.the7ElementorSettingsCache[widgetType][field];
            }

            return window.the7ElementorSettingsCache[widgetType];
        }

        function setWidgetSettingsCache(widgetType, field, value) {
            if (!window.the7ElementorSettingsCache[widgetType]) {
                window.the7ElementorSettingsCache[widgetType] = {}
            }

            window.the7ElementorSettingsCache[widgetType][field] = value;
        }

        /**
         * @param string postType
         * @param string currentValue
         * @param object $taxonomySelect
         */
        function getTermsOptions(widgetType, taxonomy) {
            var terms = getWidgetSettingsCache(widgetType, "terms");
            var options = [];
            if (terms[taxonomy]) {
                options = terms[taxonomy];
            }

            return options;
        }

        /**
         * @param string postType
         * @param string currentValue
         * @param object $taxonomySelect
         */
        function getTaxonomiesOptions(widgetType, post_type) {
            var taxonomies = getWidgetSettingsCache(widgetType, "taxonomies");
            var options = [];
            if (taxonomies[post_type]) {
                options = taxonomies[post_type];
            }

            return options;
        }

        function appendOptionsTo($selectEl, options, currentValue) {
            var currentArray = window.Array.isArray(currentValue) ? currentValue : [currentValue];

            $selectEl.prop("disabled", true);
            $selectEl.empty();
            $selectEl.append(options.reduce(function (prev, cur, index) {
                var selected = "";
                if (currentArray.indexOf(cur.value) !== -1) {
                    selected = "selected";
                }

                return prev + "<option value=\"" + cur.value + "\"" + selected + ">" + cur.label + "</option>";
            }, ""));

            $selectEl.prop("disabled", false);
        }

        function fillTermsTaxonomy(model, $termsSelect, $taxonomySelect) {
            var widgetType = model.attributes.widgetType;

            appendOptionsTo($termsSelect, getTermsOptions(widgetType, model.getSetting("taxonomy")), model.getSetting("terms"));
            appendOptionsTo($taxonomySelect, getTaxonomiesOptions(widgetType, model.getSetting("post_type")), model.getSetting("taxonomy"));
        }

        function onEditSettings(changedModel, widgetModel) {
            if (!changedModel.attributes.panel) {
                return;
            }

            if (["content_section", "query_section"].indexOf(changedModel.attributes.panel.activeSection) === -1) {
                return;
            }

            setTimeout(function (model, panel) {
                var $postTypeSelect = panel.$el.find("[data-setting='post_type']");
                var $taxonomySelect = panel.$el.find("[data-setting='taxonomy']");
                var $termsSelect = panel.$el.find("[data-setting='terms']");

                // On post type change.
                $postTypeSelect.on("change", function () {
                    var widgetType = model.attributes.widgetType;
                    var taxonomies = getTaxonomiesOptions(widgetType, $(this).val());

                    if (!taxonomies[0]) {
                        return;
                    }

                    appendOptionsTo($taxonomySelect, taxonomies, null);
                    model.setSetting(taxonomies[0].value);
                    $taxonomySelect.trigger("change");
                });

                // On taxonomy change.
                $taxonomySelect.on("change", function () {
                    var widgetType = model.attributes.widgetType;

                    $termsSelect[0].options.length = 0;
                    appendOptionsTo($termsSelect, getTermsOptions(widgetType, $(this).val()), null);
                    model.setSetting("terms", []);
                });

                fillTermsTaxonomy(model, $termsSelect, $taxonomySelect);
            }, 350, this.model, this.panel);
        }

        elementor.hooks.addAction("panel/open_editor/widget", function (panel, model, view) {
            var $postTypeSelect = panel.$el.find("[data-setting='post_type']");
            var $taxonomySelect = panel.$el.find("[data-setting='taxonomy']");
            var $termsSelect = panel.$el.find("[data-setting='terms']");
            var widgetType = model.attributes.widgetType;

            if (!getWidgetSettingsCache(widgetType, "taxonomies") || !getWidgetSettingsCache(widgetType, "terms")) {
                var data = {
                    action: "the7_elements_get_widget_taxonomies",
                    _wpnonce: window.the7ElementsWidget._wpnonce
                };
                $.post(window.the7ElementsWidget.ajaxurl, data)
                    .done(function (response) {
                        if (!response) {
                            response = {};
                        }

                        setWidgetSettingsCache(widgetType, "taxonomies", response.taxonomies);
                        setWidgetSettingsCache(widgetType, "terms", response.terms);
                        fillTermsTaxonomy(model, $termsSelect, $taxonomySelect);
                    });
            } else {
                fillTermsTaxonomy(model, $termsSelect, $taxonomySelect);
            }

            // On post type change.
            $postTypeSelect.on("change", function () {
                var widgetType = model.attributes.widgetType;
                var taxonomies = getTaxonomiesOptions(widgetType, $(this).val());

                if (!taxonomies[0]) {
                    return;
                }

                appendOptionsTo($taxonomySelect, taxonomies, null);
                model.setSetting(taxonomies[0].value);
                $taxonomySelect.trigger("change");
            });

            // On taxonomy change.
            $taxonomySelect.on("change", function () {
                var widgetType = model.attributes.widgetType;

                $termsSelect[0].options.length = 0;
                appendOptionsTo($termsSelect, getTermsOptions(widgetType, $(this).val()), null);
                model.setSetting("terms", []);
            });

            elementor.channels.editor.off("change:editSettings", onEditSettings).on("change:editSettings", onEditSettings, {
                panel: panel,
                model: model
            });
        });

        elementor.on('preview:loaded', function () {
            elementor.addControlView('the7-query',

                elementor.modules.controls.Select2.extend({
                    cache: null,
                    isTitlesReceived: false,
                    getSelect2Placeholder: function getSelect2Placeholder() {
                        return {
                            id: '',
                            text: 'All',
                        };
                    },
                    getControlValueByName: function getControlValueByName(controlName) {
                        var name = this.model.get('group_prefix') + controlName;
                        return this.container.settings.get(name);
                    },
                    getQueryDataDeprecated: function getQueryDataDeprecated() {
                        return {
                            filter_type: this.model.get('filter_type'),
                            object_type: this.model.get('object_type'),
                            include_type: this.model.get('include_type'),
                            query: this.model.get('query')
                        };
                    },
                    getQueryData: function getQueryData() {
                        // Use a clone to keep model data unchanged:
                        var autocomplete = elementorCommon.helpers.cloneObject(this.model.get('autocomplete'));

                        if (_.isEmpty(autocomplete.query)) {
                            autocomplete.query = {};
                        } // Specific for Group_Control_Query


                        if ('cpt_tax' === autocomplete.object) {
                            autocomplete.object = 'tax';

                            if (_.isEmpty(autocomplete.query) || _.isEmpty(autocomplete.query.post_type)) {
                                autocomplete.query.post_type = this.getControlValueByName('post_type');
                            }
                        }

                        return {
                            autocomplete: autocomplete
                        };
                    },
                    getSelect2DefaultOptions: function getSelect2DefaultOptions() {
                        var self = this;
                        return jQuery.extend(elementor.modules.controls.Select2.prototype.getSelect2DefaultOptions.apply(this, arguments), {
                            ajax: {
                                transport: function transport(params, success, failure) {
                                    var bcFormat = !_.isEmpty(self.model.get('filter_type'));
                                    var data = {},
                                        action = 'the7_panel_posts_control_filter_autocomplete';

                                    if (bcFormat) {
                                        data = self.getQueryDataDeprecated();
                                        action = 'the7_panel_posts_control_filter_autocomplete_deprecated';
                                    } else {
                                        data = self.getQueryData();
                                    }

                                    data.q = params.data.q;
                                    return elementorCommon.ajax.addRequest(action, {
                                        data: data,
                                        success: success,
                                        error: failure
                                    });
                                },
                                data: function data(params) {
                                    return {
                                        q: params.term,
                                        page: params.page
                                    };
                                },
                                cache: true
                            },
                            escapeMarkup: function escapeMarkup(markup) {
                                return markup;
                            },
                            minimumInputLength: 1
                        });
                    },
                    getValueTitles: function getValueTitles() {
                        var self = this,
                            data = {},
                            bcFormat = !_.isEmpty(this.model.get('filter_type'));
                        var ids = this.getControlValue(),
                            action = 'the7_query_control_value_titles',
                            filterTypeName = 'autocomplete',
                            filterType = {};

                        if (bcFormat) {
                            filterTypeName = 'filter_type';
                            filterType = this.model.get(filterTypeName).object;
                            data.filter_type = filterType;
                            data.object_type = self.model.get('object_type');
                            data.include_type = self.model.get('include_type');
                            data.unique_id = '' + self.cid + filterType;
                            action = 'the7_query_control_value_titles_deprecated';
                        } else {
                            filterType = this.model.get(filterTypeName).object;
                            data.get_titles = self.getQueryData().autocomplete;
                            data.unique_id = '' + self.cid + filterType;
                        }

                        if (!ids || !filterType) {
                            return;
                        }

                        if (!_.isArray(ids)) {
                            ids = [ids];
                        }

                        elementorCommon.ajax.loadObjects({
                            action: action,
                            ids: ids,
                            data: data,
                            before: function before() {
                                self.addControlSpinner();
                            },
                            success: function success(ajaxData) {
                                self.isTitlesReceived = true;
                                self.model.set('options', ajaxData);
                                self.render();
                            }
                        });
                    },
                    addControlSpinner: function addControlSpinner() {
                        this.ui.select.prop('disabled', true);
                        this.$el.find('.elementor-control-title').after('<span class="elementor-control-spinner">&nbsp;<i class="eicon-spinner eicon-animation-spin"></i>&nbsp;</span>');
                    },
                    onReady: function onReady() {
                        this.ui.select.select2(this.getSelect2Options());
                        // Safari takes it's time to get the original select width
                        //setTimeout(elementor.modules.controls.Select2.prototype.onReady.bind(this));
                        if (!this.isTitlesReceived) {
                            this.getValueTitles();
                        }
                    }
                })
            );
        });
    });
})(jQuery);



