angular.module('NgSymfony.form', [])
    .constant('$formDispatcherEvents', {
        FORM_SUBMIT: 'FORM_SUBMIT',
        FORM_SUBMITTED: 'FORM_SUBMITTED'
    })
    .service('$formDispatcher', ['$q', '$rootScope', '$formDispatcherEvents', function ($q, $rootScope, $formDispatcherEvents) {
        var self = this;

        self.submit = function (form, id) {
            //console.log("$formDispatcher: Form submit", form);
            if (!id || id == "") {
                id = "main";
            }
            var $form = jQuery(form);

            var deferred = $q.defer();
            /* * Get all form values */
            var values = {};

            jQuery.each($form.serializeArray(), function (i, field) {
                // prima: values[field.name] = field.value;
                if (field.name.indexOf("[]") != -1) {
                    var nome = field.name.replace('[]', '');
                    if (!values[nome]) {
                        values[nome] = [];
                    }
                    values[nome].push(field.value);
                } else {
                    values[field.name] = field.value;
                }
            });

            $rootScope.$emit($formDispatcherEvents.FORM_SUBMIT, id);

            /* * Throw the form values to the server! */
            jQuery.ajax({
                type: $form.attr('method'), url: $form.attr('action'), data: values, success: function (response) {
                    //console.log("$formDispatcher: Ajax response success");
                    $rootScope.$emit($formDispatcherEvents.FORM_SUBMITTED, id, response, deferred);
                }, error: function (response) {
                    //console.log("$formDispatcher: Ajax response error");
                    $rootScope.$emit($formDispatcherEvents.FORM_SUBMITTED, null);
                    deferred.reject(response);
                }
            });

            return deferred.promise;
        };
    }])
    .directive('formListener', ['$rootScope', '$compile', '$formDispatcherEvents', '$state', function ($rootScope, $compile, $formDispatcherEvents, $state) {
        var handleResponse = function (response, deferred) {
            deferred.resolve(response);
            $state.go(response.state, response.parameters);
        };


        return {
            restrict: 'A',
            replace: false,
            scope: false,
            controller: function () {
            },
            link: function (scope, iElement, iAttrs) {
                //console.log("formListener: link element",iElement);
                var disabled = false;
                var listener_id;
                if (typeof(iAttrs.formListener) == "undefined" || !iAttrs.formListener || iAttrs.formListener == "") {
                    listener_id = "main";
                } else {
                    listener_id = iAttrs.formListener;
                }

                iElement.addClass('form-listener');

                $rootScope.$on($formDispatcherEvents.FORM_SUBMIT, function (evt, id) {
                    if (disabled)return;
                    if (id == listener_id) {
                        iElement.addClass('working');
                    }
                });
                $rootScope.$on($formDispatcherEvents.FORM_SUBMITTED, function (evt, id, response, deferred) {
                    if (disabled)return;
                    //console.log("FORM_SUBMITTED with ID",id);

                    if (id == null) {
                        iElement.removeClass('working');
                    }
                    if (id == listener_id) {
                        iElement.removeClass('working');
                        if (typeof response == 'string') {

                            // devo determinare se la risposta parte da un singolo nodo oppure sono più nodi
                            // Se sono più nodi li wrappo in un div
                            var testTemplate = jQuery('<div>' + response + '</div>');
                            var jqTemplate;
                            if (testTemplate.children().length > 1) {
                                //console.info("Contiene più elementi");
                                jqTemplate = testTemplate;
                            } else {
                                //console.info("Contiene 1 elemento");
                                jqTemplate = jQuery(response);
                                testTemplate = null;
                            }
                            //console.info(jqTemplate.html());
                            // todo aggiungere controllo su valore form-listenr per supportare più form
                            var attr = jqTemplate.attr('form-listener');
                            var html;
                            if (typeof attr !== typeof undefined && attr !== false) {
                                html = jqTemplate.html();
                                //console.log("Nodo form-listener trovato per primo");
                            } else {
                                var node = jqTemplate.find('[form-listener]');
                                console.info(node);
                                html = jQuery(node.get(0)).html();
                                //console.log("Scartato template fino al nodo form-listener.");
                            }
                            /*
                             Qualunque cosa succesa a questo punto la direttiva andrà a compilare il nuovo nodo.
                             Questo porterà ad avere due ascoltatori per gli stessi listener.
                             In pratica ci sarà un nuovo formListener che sostituirà questo, percio occorre disabilitare
                             questo listener
                             */
                            disabled = true;
                            iElement.html("");
                            iElement.html(html);
                            $compile(iElement)(scope);
                            deferred.resolve(response);
                        } else {
                            if (response.type && response.type == 'AngularUiRouterState') {
                                handleResponse(response, deferred);
                            } else {
                                deferred.resolve(response);
                            }
                        }
                    }
                });
            }
        }
    }]);