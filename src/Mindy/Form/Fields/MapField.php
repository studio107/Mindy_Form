<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 01/12/14 18:07
 */

namespace Mindy\Form\Fields;

use Mindy\Helper\JavaScript;

class MapField extends CharField
{
    public $lat = 'lat';

    public $lng = 'lng';

    public $center = [55.76, 37.64];

    public $zoom = 12;

    public function render()
    {
        $center = JavaScript::encode($this->center);

        $form = $this->getForm();
        $latField = $form->getField($this->lat);
        $lngField = $form->getField($this->lng);
        if ($latField->getValue() && $lngField->getValue()) {
            $center = JavaScript::encode([$latField->getValue(), $lngField->getValue()]);
        }

        $htmlPrefix = $this->getHtmlPrefix();
        $js = "<div id='" . $this->getHtmlId() . "map'></div>
        <script src='//api-maps.yandex.ru/2.1/?lang=ru_RU' type='text/javascript'></script>
        <script type='text/javascript'>
            function yandexMapInit() {
                var mapCollection = new ymaps.GeoObjectCollection({}, {
                   preset: 'twirl#redIcon'
                });

                var yandexMap = new ymaps.Map('" . $this->getHtmlId() . "map', {
                    center: " . $center . ",
                    zoom: " . $this->zoom . ",
                    controls: ['zoomControl', 'searchControl']
                });

                mapCollection.add(new ymaps.GeoObject({
                    geometry: {
                        type: 'Point',
                        coordinates: yandexMap.getCenter()
                    }
                }));
                yandexMap.geoObjects.add(mapCollection);

                yandexMap.events.add('click', function (e) {
                    var coords = e.get('coords');

                    $('#" . $htmlPrefix . $this->lat . "').val(coords[0].toPrecision(6));
                    $('#" . $htmlPrefix . $this->lng . "').val(coords[1].toPrecision(6));

                    mapCollection.removeAll();
                    mapCollection.add(new ymaps.GeoObject({
                        geometry: {
                            type: 'Point',
                            coordinates: coords
                        }
                    }));
                    yandexMap.geoObjects.add(mapCollection);
                });
            };

            ymaps.ready(yandexMapInit);
        </script>
        <style>
            #" . $this->getHtmlId() . "map {
                width: 100%;
                height: 350px;
                margin: 20px 0;
            }
        </style>";
        return parent::render() . $js;
    }
}
