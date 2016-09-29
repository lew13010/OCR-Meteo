<?php

namespace Api\MeteoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Api\MeteoBundle\Entity\City;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $cities = $this->getDoctrine()
            ->getRepository('ApiMeteoBundle:City')
            ->findAll();

        if(empty($cities)){
            $city = new city;
            $city->setName('Marseille');
            $city->setLatitude('43.3');
            $city->setLongitude('5.38');
            $city->setCityId('2995469');

            $city2 = new city;
            $city2->setName('Paris');
            $city2->setLatitude('48.85');
            $city2->setLongitude('2.35');
            $city2->setCityId('2988507');

            $city3 = new city;
            $city3->setName('Lyon');
            $city3->setLatitude('45.75');
            $city3->setLongitude('4.85');
            $city3->setCityId('2996944');

            $city4 = new city;
            $city4->setName('Toulouse');
            $city4->setLatitude('43.6');
            $city4->setLongitude('1.44');
            $city4->setCityId('2972315');

            $city5 = new city;
            $city5->setName('Brest');
            $city5->setLatitude('48.4');
            $city5->setLongitude('-4.48');
            $city5->setCityId('3030300');


            $em = $this->getDoctrine()->getManager();
            $em->persist($city);
            $em->persist($city2);
            $em->persist($city3);
            $em->persist($city4);
            $em->persist($city5);
            $em->flush();

            $cities = $this->getDoctrine()
                ->getRepository('ApiMeteoBundle:City')
                ->findAll();
        }

        return $this->render('ApiMeteoBundle:Default:index.html.twig', array(
            'cities' => $cities
            ));
    }

    public function viewIdAction(City $city)
    {
        $apiId = $this->getParameter('api_keys');
        $units = $this->getParameter('units');
        $langs = $this->getParameter('langs');
        $ville = $city->getName();
        $cache = $this->container->get('api_meteo.cache');
        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?id='.$city->getCityId().'&cnt=5&APPID='.$apiId.'&lang='.$langs.'&units='.$units;

        $json = $cache->existCache($ville, $url);

        return $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'ville'  =>  $ville,
            'json'  =>  $json,
        ));
    }

    public function viewNameAction()
    {
        $ville = $_POST['city'];
        $apiId = $this->getParameter('api_keys');
        $units = $this->getParameter('units');
        $langs = $this->getParameter('langs');
        $cache = $this->container->get('api_meteo.cache');
        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?q='.$ville.'&cnt=5&APPID='.$apiId.'&lang='.$langs.'&units='.$units;

        $json = $cache->existCache($ville, $url);

        $reponse = $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'ville'  =>  $ville,
            'json'  =>  $json,
        ));
        return $reponse;
    }

    public function viewGeoAction($latitude, $longitude)
    {
        $apiId = $this->getParameter('api_keys');
        $units = $this->getParameter('units');
        $langs = $this->getParameter('langs');
        $cache = $this->container->get('api_meteo.cache');
        $latitudeClean = round($latitude, 2);
        $longitudeClean = round($longitude, 2);
        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?lat='.$latitudeClean.'&lon='.$longitudeClean.'&cnt=5&APPID='.$apiId.'&lang='.$langs.'&units='.$units;

        $json = $cache->existCache(null, $url, $latitudeClean, $longitudeClean);
        if(is_array($json)){
            $ville = $json['city']['name'];

        }else{
            $ville = $json->city->name;
        }

        $reponse = $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'ville'  =>  $ville,
            'json'  =>  $json,
        ));

        return $reponse;
    }
}
