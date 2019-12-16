<?php

namespace prophetkhalul\breadcrumbsmeta;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

class BreadcrumbsMeta extends Breadcrumbs
{
    public function __construct($config = [])
    {
        //Если не передали в опциях саму схему, то присваиваем ее
        if (!isset($config['options']['itemscope itemtype'])) {
            $config['options']['itemscope itemtype'] = 'http://schema.org/BreadcrumbList';
        }

        //Если не указан стиль, то присваиваем, так как при переопределении массива options(строка 14-17) значение class дефолтное удаляется
        if (!isset($config['options']['class'])) {
            $config['options']['class'] = 'breadcrumb';
        }


        //Если из layout вообще не передали homeLink, то присваиваем значения по-умолчанию
        if (!isset($config['homeLink']) || $config['homeLink'] === null) {
            $config['homeLink'] = [
                'label' => Yii::t('yii', 'Home'),
                'url'   => Yii::$app->homeUrl,
                'rel' => 'nofollow'
            ];
        }

        //Если в homeLink что-то есть, то присваиваем значения из массивов, если есть. После вызываем getHome
        if ($config['homeLink'] !== false) {
            $label = isset($config['homeLink']['label']) ? $config['homeLink']['label'] : null;
            $url = isset($config['homeLink']['url']) ? $config['homeLink']['url'] : (is_string($config['homeLink']) ? $config['homeLink'] : Yii::$app->homeUrl);
            //Чтобы не ставить rel нужно в вызове виджета в layouts указать 'rel'=>false для homeLink
            $rel = isset($config['homeLink']['rel']) ? ($config['homeLink']['rel'] === false ? null : $config['homeLink']['rel']) : 'nofollow';
            $config['homeLink'] = self::SchemeForHome($label, $url, $rel);
        }


        /*
         * Если не переданы данные из слоя для крошек, то получаем их из view напрямую и вызываем ф-цию
         * для формирования хлебных с учетом схемы
         */
        if (!isset($config['links'])){
            $config['links'] = isset($this->view->params['breadcrumbs']) ? $this->view->params['breadcrumbs'] : [];
            $config['links'] = self::SchemeForLinks($config['links']);
        }
        //Если массив links передан, то вызов ф-ция для создания схемы под крошки
        elseif(isset($config['links'])){
            $config['links'] = self::SchemeForLinks($config['links']);
        }



        parent::__construct($config);
    }


    //Ф-ция для формирования массива, который будет обрабатывать уже основной виджет крошек
    private static function SchemeForHome($label, $url, $rel)
    {
        $home = [
            'label' => $label,
            'url' => $url,
            'rel' => $rel,
            'template'  => self::Template($label, $url, $rel, 1)
        ];

        return $home;
    }

    //Ф-ция для формирования схемы с учетом текущей позиции ссылки в хлеб. крошек. home =2, так как отсчет идет с 1 и 1 присваивается ссылке на главную
    private static function SchemeForLinks($links, $home = 2)
    {
        //Если ссылки нет, то не формируем схему для хлебных
        if (empty($links)) {
            return [];
        }

        foreach ($links as $key => &$link) {
            if(is_array($link)) {
                //Формирование схемы для хлеб. крошки с ссылки
                $link['template'] = self::Template($link['label'], $link['url'], null, $key+$home);
            }
        }

        return $links;
    }


    //Ф-ция для формирование шаблона каждой ссылки на основе схемы
    private static function Template($label, $url, $rel, $key)
    {
        return '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">'
            . Html::a('<span itemprop="name">'.$label.'</span>', Url::to($url), ['itemprop'=>'item', 'rel' => $rel])
            . '<meta itemprop="position" content="'.$key.'" /></li>';
    }
}