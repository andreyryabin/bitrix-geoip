BX.ready(function () {

    //такой подход позволяет разместить несколько компонентов на странице не боясь дублирования обработчиков событий
    //и не привязываясь к id элементов

    let components = document.getElementsByClassName("geoip-component");
    for (let cIndex = 0; cIndex < components.length; cIndex++) {
        if (!components[cIndex].classList.contains('initialized')) {
            components[cIndex].classList.add('initialized');
            initialize(components[cIndex]);
        }
    }

    function initialize(component) {

        const $input = component.querySelectorAll('.geoip-text')[0];
        const $button = component.querySelectorAll('.geoip-btn')[0];
        const $result = component.querySelectorAll('.geoip-result')[0];

        BX.bind($button, 'click', function (e) {
            e.preventDefault();
            wait($button);

            BX.ajax.runComponentAction('geoip:form', 'check', {
                mode: 'class',
                data: {checkIp: $input.value},
            }).then(
                function (response) {
                    BX.adjust($result, {
                        html: response.data
                    });

                    stop($button);
                },
                function (response) {
                    BX.adjust($result, {
                        html: response.errors.map(item => item.message).join(', ')
                    });

                    stop($button);
                }
            );
        });
    }


    function wait($button) {
        BX.addClass($button, 'ui-btn-wait');
        BX.adjust($button, {props: {disabled: true}});
    }

    function stop($button) {
        BX.removeClass($button, 'ui-btn-wait');
        BX.adjust($button, {props: {disabled: false}});
    }


});
