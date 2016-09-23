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

        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?id='.$city->getCityId().'&cnt=5&APPID='.$apiId.'&lang='.$langs.'&units='.$units;

        $name = $city;

        $fichiers = scandir($this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache');
        foreach ($fichiers as $fichier){
            parse_str($fichier);
            if(isset($v) && ($v === $city->getName())){
                $name = $fichier;
            }
        }

       // Reccupération de la route du cache
        $filename = $this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $name;

        //si le fichier existe
        if(file_exists($filename)){
            $file = unserialize(file_get_contents($filename));
            $now = new \DateTime();
            //si il a moins de 3 heures
            if($now->diff($file['date'])->h > 3){
                $content = file_get_contents($url);
                $json = json_decode($content, true);

                $lat = round($json['city']['coord']['lat'], 2);
                $lon = round($json['city']['coord']['lon'], 2);
                $this->createCache($city, $content, $lat, $lon);

            }//si + de 3h on le recrée
            else{
                $json = json_decode($file['content']);
            }
        }//si il n'existe pas on le crée
        else{
            $content = file_get_contents($url);
            $json = json_decode($content, true);

            $lat = round($json['city']['coord']['lat'], 2);
            $lon = round($json['city']['coord']['lon'], 2);
            $this->createCache($city, $content, $lat, $lon);
        }

        return $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'city'  =>  $city,
            'json'  =>  $json,
        ));
    }

    public function viewNameAction()
    {

        $ville = $_POST['city'];
        $apiId = $this->getParameter('api_keys');
        $units = $this->getParameter('units');
        $langs = $this->getParameter('langs');

        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?q='.$ville.'&cnt=5&APPID='.$apiId.'&lang='.$langs.'&units='.$units;

        $name = $ville;

        $fichiers = scandir($this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache');
        foreach ($fichiers as $fichier){
            parse_str($fichier);
            if(isset($v) && $v === $ville){
                $name = $fichier;
            }
        }

        // Reccupération de la route du cache
        $filename = $this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $name;

        if(file_exists($filename)){
            $file = unserialize(file_get_contents($filename));
            $now = new \DateTime();
            //si il a moins de 3 heures
            if($now->diff($file['date'])->h > 3){
                $content = file_get_contents($url);
                $json = json_decode($content, true);

                $lat = round($json['city']['coord']['lat'], 2);
                $lon = round($json['city']['coord']['lon'], 2);
                $this->createCache($ville, $content, $lat, $lon);
            }//si + de 3h on le recrée
            else{
                $json = json_decode($file['content']);
            }
        }//si il n'existe pas on le crée
        else{
            $content = file_get_contents($url);
            $json = json_decode($content, true);

            $lat = round($json['city']['coord']['lat'], 2);
            $lon = round($json['city']['coord']['lon'], 2);
            $this->createCache($ville, $content, $lat, $lon);
        }

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

        $latitudeClean = round($latitude, 2);
        $longitudeClean = round($longitude, 2);

        $url = 'http://api.openweathermap.org/data/2.5/forecast/daily?lat='.$latitudeClean.'&lon='.$longitudeClean.'&cnt=5&APPID='.$apiId.'&lang='.$langs.'&units='.$units;

        $name = 'qsdfboqsdf';

        $fichiers = scandir($this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache');
        foreach ($fichiers as $fichier){
            parse_str($fichier);
            if(isset($lat) && $lat >= ($latitudeClean - 0.01) && $lat <= ($latitudeClean + 0.01) && $lon >= ($longitudeClean - 0.01) && $lon <= ($longitudeClean + 0.01)){
                $name = $fichier;
            }
        }

        // Reccupération de la route du cache
        $filename = $this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $name;

        if(file_exists($filename)){
            $file = unserialize(file_get_contents($filename));
            $now = new \DateTime();
            //si il a moins de 3 heures
            if($now->diff($file['date'])->h > 3){
                $content = file_get_contents($url);
                $json = json_decode($content, true);

                $city = $json['city']['name'];
                $lat = round($json['city']['coord']['lat'], 2);
                $lon = round($json['city']['coord']['lon'], 2);
                $this->createCache($city, $content, $lat, $lon);
            }//si + de 3h on le recrée
            else{
                $json = json_decode($file['content']);
            }
        }//si il n'existe pas on le crée
        else{
            $content = file_get_contents($url);
            $json = json_decode($content, true);

            $city = $json['city']['name'];
            $lat = round($json['city']['coord']['lat'], 2);
            $lon = round($json['city']['coord']['lon'], 2);
            $this->createCache($city, $content, $lat, $lon);
        }

        $content = file_get_contents($url);
        $json = json_decode($content, true);
        $ville = $json['city']['name'];

        $reponse = $this->render('ApiMeteoBundle:Default:view.html.twig', array(
            'ville'  =>  $ville,
            'json'  =>  $json,
        ));

        return $reponse;
    }

    public function createCache($city, $content, $lat, $lon)
    {
        $filename = $this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'v=' . $city. '&lat=' . $lat . '&lon=' . $lon;
        $date = new \DateTime();
        $file = array(
            'date'      => $date,
            'content'   => $content
        );
        file_put_contents($filename, serialize($file));
        return $file;
    }

    public function updateeCache($city, $content)
    {
        $filename = $this->get('kernel')->getRootDir(). DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $city;
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
