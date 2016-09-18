<?php

namespace Api\MeteoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Api\MeteoBundle\Entity\City;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $cities = $this->getDoctrine()
            ->getRepository('ApiMeteoBundle:City')
            ->findAll();

        return $this->render('ApiMeteoBundle:Default:index.html.twig', array(
            'cities' => $cities
            ));
    }

    public function viewIdAction(City $city)
    {
        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?id='.$city->getCityId().'&cnt=5&APPID=cf28174e3958bebc50c5286ecb1951d2&lang=fr&units=metric';

        $content = file_get_contents($url);
        $json = json_decode($content, true);
        //var_dump($json['list'][0]);
        //die();

        $reponse =  $this->render('ApiMeteoBundle:Default:view.html.twig', array(
                'city'  =>  $city,
                'json'  =>  $json,
            ));
        $reponse->setSharedMaxAge(3600);

        return $reponse;
    }
    public function viewNameAction()
    {
        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?q='.$_POST['city'].'&cnt=5&APPID=cf28174e3958bebc50c5286ecb1951d2&lang=fr&units=metric';
        $ville = $_POST['city'];

        $content = file_get_contents($url);
        $json = json_decode($content, true);
       // var_dump($json);

        $reponse = $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'ville'  =>  $ville,
            'json'  =>  $json,
        ));
        $reponse->setSharedMaxAge(3600);

        return $reponse;
    }

    public function viewGeoAction($latitude, $longitude)
    {
        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?lat='.$latitude.'&lon='.$longitude.'&cnt=5&APPID=cf28174e3958bebc50c5286ecb1951d2&lang=fr&units=metric';

        $content = file_get_contents($url);
        $json = json_decode($content, true);
        $ville = $json['city']['name'];


        $reponse = $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'ville'  =>  $ville,
            'json'  =>  $json,
        ));
        $reponse->setSharedMaxAge(3600);

        return $reponse;
    }
}
