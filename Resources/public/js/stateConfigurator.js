var $stateConfigurator = function ($stateProvider, $extractedStates) {
    var paramTemplate = function (stateData) {
        var view = stateData.view;
        var state = stateData.state;
        var cache = stateData.cache;
        this.template = function ($stateParams) {
            var c = 0;
            if (view == state && !cache) {
                // if the state and viw is the same I will load for sure a fresh version of view
                c = 1;
            } else {
                for (var k in $stateParams) {
                    c++;
                }
            }
            if (c > 0) {
                $stateParams.nocache = Math.random();
            }
            return window.Routing.generate(view, $stateParams);
        }
    };
    for (var i in $extractedStates) {
        var dynamicTemplate = new paramTemplate($extractedStates[i]);
        $stateProvider.state($extractedStates[i].state, {
            url: $extractedStates[i].path,
            controller: $extractedStates[i].controller,
            controllerAs: $extractedStates[i].controllerAs ? $extractedStates[i].controllerAs : 'main',
            templateUrl: dynamicTemplate.template
        });
    }
};