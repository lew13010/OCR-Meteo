<?php

namespace Api\MeteoBundle\Services;

use Api\MeteoBundle\Entity\City;

class Cache
{

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function existCache($ville, $url, $latitudeClean = null, $longitudeClean = null)
    {
        $name = 'qsdfboqsdf';

        $routeCache = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'myCache';

        if(!is_dir($routeCache))
        {
            mkdir($routeCache);
        }
        $fichiers = scandir($routeCache);

        if(null === $ville){
            foreach ($fichiers as $fichier){
                parse_str($fichier);
                if(isset($lat) && $lat >= ($latitudeClean - 0.01) && $lat <= ($latitudeClean + 0.01) && $lon >= ($longitudeClean - 0.01) && $lon <= ($longitudeClean + 0.01)){
                    $name = $fichier;
                }
            }
        }else{
            foreach ($fichiers as $fichier){
                parse_str($fichier);
                if(isset($v) && ($v === $ville)){
                    $name = $fichier;
                }
            }
        }
        $filename = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'myCache' . DIRECTORY_SEPARATOR . $name;

        if(file_exists($filename)) {
            $file = unserialize(file_get_contents($filename));
            $now = new \DateTime();
            //si il a moins de 3 heures
            if($now->diff($file['date'])->h > 3){
                $content = file_get_contents($url);
                $json = json_decode($content, true);
                if (null === $ville){
                    $ville = $json->city->name;
                }

                $lat = round($json['city']['coord']['lat'], 2);
                $lon = round($json['city']['coord']['lon'], 2);
                $this->createCache($ville, $content, $lat, $lon);

            }//si + de 3h on le recrée
            else{
                $json = json_decode($file['content']);
                if (null === $ville){
                    $ville = $json->city->name;
                }
            }
        }//si il n'existe pas on le crée
        else {
            $content = file_get_contents($url);
            $json = json_decode($content, true);
            if (null === $ville){
                $ville = $json['city']['name'];
            }

            $lat = round($json['city']['coord']['lat'], 2);
            $lon = round($json['city']['coord']['lon'], 2);
            $this->createCache($ville, $content, $lat, $lon);
        }

        return $json;
    }


    public function createCache($city, $content, $lat, $lon)
    {
        $filename = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'myCache' . DIRECTORY_SEPARATOR . 'v=' . $city . '&lat=' . $lat . '&lon=' . $lon;
        $date = new \DateTime();
        $file = array(
            'date'      => $date,
            'content'   => $content
        );
        file_put_contents($filename, serialize($file));
        return $file;
    }

    public function updateCache($city, $content)
    {
        $filename = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . $city;
        $date = new \DateTime();
        $file = file_get_contents($filename);

        $file = array(
            $city => array(
                'date'      => $date,
                'content'   => $content
            )
        );
        file_put_contents($filename, serialize($file));
        return $file;
    }
}